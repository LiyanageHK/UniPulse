from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score
import pymysql
import traceback
import os

app = Flask(__name__)

# ==============================
# DATABASE CONNECTION
# ==============================

def get_db_connection():
    """Create a MySQL database connection using environment variables or defaults."""
    return pymysql.connect(
        host=os.environ.get("DB_HOST", "127.0.0.1"),
        database=os.environ.get("DB_DATABASE", "peer_db"),
        user=os.environ.get("DB_USERNAME", "root"),
        password=os.environ.get("DB_PASSWORD", "root"),
        port=int(os.environ.get("DB_PORT", "3306")),
        charset="utf8mb4",
    )


def get_data_from_db():
    """Fetch student profiles from the database.
    
    Returns a tuple of (dataframe, user_ids_list).
    """
    conn = get_db_connection()

    query = """
        SELECT
            sp.user_id,
            sp.faculty,
            sp.al_stream,
            sp.learning_style,
            sp.intro_extro,
            sp.stress_level,
            sp.overwhelmed,
            sp.social_setting,
            sp.confidence,
            sp.group_comfort
        FROM student_profiles sp
        INNER JOIN users u ON u.id = sp.user_id
        ORDER BY sp.user_id
    """
    df = pd.read_sql(query, conn)
    conn.close()

    user_ids = df['user_id'].tolist()
    df = df.drop(columns=['user_id'])

    return df, user_ids


# ==============================
# PREPROCESSING
# ==============================

# Columns that are categorical (strings)
CATEGORICAL_COLS = ['faculty', 'al_stream', 'learning_style', 'stress_level', 'social_setting']

# Columns that are already numeric
NUMERIC_COLS = ['intro_extro', 'overwhelmed', 'confidence', 'group_comfort']

# Ordinal mapping for stress_level so distance is meaningful
STRESS_MAP = {'Low': 1, 'Moderate': 2, 'High': 3}


def preprocess_data(df):
    """
    Encode categorical features via label encoding and z-score normalise
    every column so KMeans sees real variance.
    """
    df = df.copy()
    df.fillna("Unknown", inplace=True)

    # --- Convert stress_level to ordinal number ---
    if 'stress_level' in df.columns:
        df['stress_level'] = df['stress_level'].map(STRESS_MAP).fillna(2).astype(float)

    # --- Label-encode remaining categoricals ---
    label_encoders = {}
    remaining_cats = [c for c in CATEGORICAL_COLS if c in df.columns and c != 'stress_level']
    for col in remaining_cats:
        le = LabelEncoder()
        df[col] = le.fit_transform(df[col].astype(str))
        label_encoders[col] = le

    # --- Ensure all numeric ---
    for col in NUMERIC_COLS:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)

    # --- Z-score standardisation (mean=0, std=1) ---
    scaler = StandardScaler()
    scaled = scaler.fit_transform(df)
    df_scaled = pd.DataFrame(scaled, columns=df.columns, index=df.index)

    return df_scaled


# ==============================
# PURPOSE WEIGHTS
# ==============================

WEIGHT_PROFILES = {
    # Default: balanced — no particular bias
    "default": {
        "faculty":        1.0,
        "al_stream":      1.0,
        "learning_style": 1.0,
        "confidence":     1.0,
        "intro_extro":    1.0,
        "stress_level":   1.0,
        "overwhelmed":    1.0,
        "social_setting": 1.0,
        "group_comfort":  1.0,
    },
    # Academic: match by faculty, stream, and learning style
    "academic": {
        "faculty":        3.5,
        "al_stream":      3.5,
        "learning_style": 3.0,
        "confidence":     1.5,
        "intro_extro":    1.0,
        "stress_level":   1.2,
        "overwhelmed":    1.0,
        "social_setting": 0.5,
        "group_comfort":  1.5,
    },
    # Hobby: shared activity style, social comfort, extroversion
    "hobby": {
        "faculty":        0.5,
        "al_stream":      0.3,
        "learning_style": 2.0,
        "confidence":     1.5,
        "intro_extro":    2.5,
        "stress_level":   1.0,
        "overwhelmed":    0.8,
        "social_setting": 2.5,
        "group_comfort":  3.0,
    },
    # Personality: deep personality trait compatibility
    "personality": {
        "faculty":        0.5,
        "al_stream":      0.3,
        "learning_style": 1.0,
        "confidence":     3.0,
        "intro_extro":    3.5,
        "stress_level":   2.5,
        "overwhelmed":    2.5,
        "social_setting": 2.0,
        "group_comfort":  2.5,
    },
}


def apply_weights(df, purpose="study"):
    """Multiply each feature column by the purpose-specific weight."""
    weights = WEIGHT_PROFILES.get(purpose, WEIGHT_PROFILES["default"])
    df = df.copy()
    for col in df.columns:
        if col in weights:
            df[col] = df[col] * weights[col]
    return df


# ==============================
# RUN KMEANS + BALANCE
# ==============================

def run_kmeans(df, group_size):
    """KMeans with iterative split/merge post-processing.
    1. KMeans with k = n // group_size.
    2. Iteratively split any cluster > max_size into sub-groups.
    3. Merge clusters < min_size into nearest neighbour (respecting max_size).
    Returns ``(labels_list, final_k, silhouette)``.
    """
    n = len(df)
    if n == 0:
        return [], 0, 0.0

    X = df.values

    k = max(2, n // group_size)
    k = min(k, n - 1)
    km = KMeans(n_clusters=k, random_state=42, n_init=20, max_iter=500)
    raw_labels = km.fit_predict(X)

    labels = _split_and_merge(X, raw_labels, group_size)

    n_unique = len(set(labels))
    sil = 0.0
    if n_unique >= 2 and n > n_unique:
        sil = silhouette_score(X, labels)

    return labels, n_unique, round(sil, 4)


def _split_and_merge(X, labels, group_size):
    """Split big clusters, merge small clusters, renumber."""
    from collections import Counter

    min_size = max(2, int(np.ceil(group_size * 0.5)))
    max_size = int(np.ceil(group_size * 1.5))

    new_labels = np.array(labels, dtype=int)
    next_id = int(new_labels.max()) + 1

    # --- Iterative split until no cluster exceeds max_size ---
    for _round in range(5):  # safety limit
        counts = Counter(new_labels.tolist())
        oversized = [c for c, cnt in counts.items() if cnt > max_size]
        if not oversized:
            break
        for cid in oversized:
            idxs = np.where(new_labels == cid)[0]
            cnt = len(idxs)
            sub_k = max(2, int(np.ceil(cnt / group_size)))
            sub_km = KMeans(n_clusters=sub_k, random_state=42, n_init=10)
            sub_labs = sub_km.fit_predict(X[idxs])
            for i, idx in enumerate(idxs):
                if sub_labs[i] == 0:
                    new_labels[idx] = cid
                else:
                    new_labels[idx] = next_id + sub_labs[i] - 1
            next_id += sub_k - 1

    # --- Merge undersized clusters ---
    counts = Counter(new_labels.tolist())
    cids = sorted(counts.keys())
    centroids = {c: X[np.where(new_labels == c)[0]].mean(axis=0) for c in cids}

    tiny = sorted([c for c, cnt in counts.items() if cnt < min_size],
                  key=lambda c: counts[c])

    for cid in tiny:
        if counts[cid] == 0:
            continue
        best_c, best_d = None, float('inf')
        for other in cids:
            if other == cid or counts[other] == 0:
                continue
            if counts[other] + counts[cid] > max_size:
                continue
            d = np.linalg.norm(centroids[cid] - centroids[other])
            if d < best_d:
                best_d = d
                best_c = other
        if best_c is not None:
            idxs = np.where(new_labels == cid)[0]
            new_labels[idxs] = best_c
            counts[best_c] += counts[cid]
            centroids[best_c] = X[np.where(new_labels == best_c)[0]].mean(axis=0)
            counts[cid] = 0

    # --- Renumber 0..m-1 ---
    used = sorted(set(new_labels.tolist()))
    remap = {old: new for new, old in enumerate(used)}
    return [remap[a] for a in new_labels.tolist()]


# ==============================
# API ROUTE
# ==============================

@app.route('/run-clustering', methods=['POST'])
def cluster_students():
    """API endpoint for clustering students into peer groups."""

    try:
        data = request.get_json()

        if not data or 'group_size' not in data:
            return jsonify({"error": "Missing required field: group_size"}), 400

        group_size = int(data['group_size'])
        purpose = data.get('purpose', 'study')

        if group_size < 2 or group_size > 10:
            return jsonify({"error": "group_size must be between 2 and 10"}), 400

        # Step 1: Get onboarding data with user IDs
        df, user_ids = get_data_from_db()

        if len(df) < 4:
            return jsonify({
                "error": f"Not enough student profiles to cluster (found {len(df)}, need at least 4)"
            }), 400

        # Step 2: Preprocess
        df_processed = preprocess_data(df)

        # Step 3: Apply purpose-specific weights
        df_weighted = apply_weights(df_processed, purpose)

        # Step 4: Run KMeans
        clusters, k, silhouette = run_kmeans(df_weighted, group_size)

        response = {
            "total_students": len(user_ids),
            "group_size": group_size,
            "purpose": purpose,
            "clusters_count": k,
            "silhouette_score": round(silhouette, 4),
            "user_ids": user_ids,
            "cluster_assignments": clusters,
        }

        return jsonify(response)

    except pymysql.Error as e:
        traceback.print_exc()
        return jsonify({"error": f"Database error: {str(e)}"}), 500
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": f"Clustering failed: {str(e)}"}), 500


# ==============================
# DEBUG ENDPOINT
# ==============================

@app.route('/debug', methods=['GET'])
def debug_data():
    """Return preprocessed feature stats for debugging."""
    try:
        df, user_ids = get_data_from_db()

        info = {
            "raw_shape": list(df.shape),
            "raw_columns": list(df.columns),
            "raw_nunique": {c: int(df[c].nunique()) for c in df.columns},
            "raw_sample": df.head(3).to_dict(orient="records"),
        }

        df_proc = preprocess_data(df)
        info["processed_shape"] = list(df_proc.shape)
        info["processed_columns"] = list(df_proc.columns)

        # Variance per column – zero variance = useless feature
        var = df_proc.var()
        info["processed_variance"] = {c: round(float(v), 6) for c, v in var.items()}
        info["zero_variance_cols"] = [c for c, v in var.items() if v < 1e-10]

        # Distinct rows
        info["distinct_rows"] = int(df_proc.drop_duplicates().shape[0])

        return jsonify(info)
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500


# ==============================
# FIND MY GROUP ENDPOINT
# ==============================

@app.route('/find-my-group', methods=['POST'])
def find_my_group():
    """Return the top N best-matching students for a specific user.

    Request JSON:
        user_id    (int)  – the logged-in student's user ID
        purpose    (str)  – 'study' | 'sports' | 'social'
        group_size (int)  – how many members to include (including the user)

    Response JSON:
        user_id       – the queried user
        purpose
        group_size
        matches       – list of {user_id, match_score, rank}   (best first)
                        match_score is 0-100 (100 = identical profile)
    """
    try:
        data = request.get_json()

        if not data or 'user_id' not in data or 'group_size' not in data:
            return jsonify({"error": "Missing required fields: user_id, group_size"}), 400

        target_user_id = int(data['user_id'])
        purpose        = data.get('purpose', 'study')
        group_size     = int(data['group_size'])

        if group_size < 2 or group_size > 20:
            return jsonify({"error": "group_size must be between 2 and 20"}), 400

        # --- load all profiles ---
        conn = get_db_connection()
        query = """
            SELECT
                sp.user_id,
                u.name,
                sp.faculty,
                sp.al_stream,
                sp.learning_style,
                sp.intro_extro,
                sp.stress_level,
                sp.overwhelmed,
                sp.social_setting,
                sp.confidence,
                sp.group_comfort
            FROM student_profiles sp
            INNER JOIN users u ON u.id = sp.user_id
            ORDER BY sp.user_id
        """
        df_full = pd.read_sql(query, conn)
        conn.close()

        if target_user_id not in df_full['user_id'].values:
            return jsonify({"error": f"No profile found for user_id={target_user_id}"}), 404

        user_ids = df_full['user_id'].tolist()
        names    = df_full['name'].tolist()
        faculties = df_full['faculty'].tolist()
        streams   = df_full['al_stream'].tolist()
        learning_styles = df_full['learning_style'].tolist()
        stress_levels   = df_full['stress_level'].tolist()
        social_settings = df_full['social_setting'].tolist()

        # drop non-feature columns before preprocessing
        df_features = df_full.drop(columns=['user_id', 'name'])

        # --- preprocess + weight ---
        df_proc    = preprocess_data(df_features)
        df_weighted = apply_weights(df_proc, purpose)
        X = df_weighted.values

        # --- find target user row ---
        target_idx = user_ids.index(target_user_id)
        target_vec = X[target_idx]

        # --- compute euclidean distance to every other student ---
        distances = np.linalg.norm(X - target_vec, axis=1)  # shape (n,)

        # Normalise distances to a 0-100 match score
        # distance=0 → score=100, distance=max → score=0
        max_dist = distances.max()
        if max_dist > 0:
            match_scores = (1 - distances / max_dist) * 100
        else:
            match_scores = np.full(len(distances), 100.0)

        # Build sorted list (exclude the target user themselves)
        candidates = []
        for i, uid in enumerate(user_ids):
            if uid == target_user_id:
                continue
            candidates.append({
                "user_id":       int(uid),
                "name":          names[i],
                "faculty":       faculties[i],
                "al_stream":     streams[i],
                "learning_style": learning_styles[i],
                "stress_level":  stress_levels[i],
                "social_setting": social_settings[i],
                "match_score":   round(float(match_scores[i]), 1),
                "distance":      round(float(distances[i]), 6),
            })

        candidates.sort(key=lambda x: x['distance'])

        # Return top (group_size - 1) matches
        top_n = group_size - 1
        top_matches = candidates[:top_n]

        for rank, m in enumerate(top_matches, start=1):
            m['rank'] = rank
            del m['distance']  # internal detail, not needed by client

        return jsonify({
            "user_id":    target_user_id,
            "purpose":    purpose,
            "group_size": group_size,
            "matches":    top_matches,
        })

    except pymysql.Error as e:
        traceback.print_exc()
        return jsonify({"error": f"Database error: {str(e)}"}), 500
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": f"Find-my-group failed: {str(e)}"}), 500


if __name__ == "__main__":
    app.run(debug=True)