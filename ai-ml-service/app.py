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
    return pymysql.connect(
        host=os.environ.get("DB_HOST", "127.0.0.1"),
        database=os.environ.get("DB_DATABASE", "peer_db"),
        user=os.environ.get("DB_USERNAME", "root"),
        password=os.environ.get("DB_PASSWORD", "root"),
        port=int(os.environ.get("DB_PORT", "3306")),
        charset="utf8mb4",
    )


def get_data_from_db():

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
            sp.group_comfort,
            sp.top_interests,
            sp.communication_methods,
            COALESCE(wc.mood, 3)          AS weekly_mood,
            COALESCE(wc.feel_left_out, 3) AS weekly_feel_left_out
        FROM student_profiles sp
        INNER JOIN users u ON u.id = sp.user_id
        LEFT JOIN (
            SELECT wc1.user_id, wc1.mood, wc1.feel_left_out
            FROM weekly_checkins wc1
            INNER JOIN (
                SELECT user_id, MAX(week_start) AS latest_week
                FROM weekly_checkins
                GROUP BY user_id
            ) latest ON wc1.user_id = latest.user_id
                    AND wc1.week_start = latest.latest_week
        ) wc ON wc.user_id = sp.user_id
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
CATEGORICAL_COLS = ['faculty', 'al_stream',
                    'learning_style', 'stress_level', 'social_setting']

# Columns that are already numeric
NUMERIC_COLS = ['intro_extro', 'overwhelmed', 'confidence', 'group_comfort',
                'weekly_mood', 'weekly_feel_left_out']

# Ordinal mapping for stress_level so distance is meaningful
STRESS_MAP = {'Low': 1, 'Moderate': 2, 'High': 3}


def _parse_json_list(val):
    import json as _json
    if isinstance(val, list):
        return val
    try:
        parsed = _json.loads(val) if isinstance(
            val, str) and val not in ('', 'Unknown', 'unknown') else []
        return parsed if isinstance(parsed, list) else []
    except Exception:
        return []


def preprocess_data(df):

    from collections import Counter as _Counter
    df = df.copy()
    df.fillna("Unknown", inplace=True)

    # --- Convert stress_level to ordinal number ---
    if 'stress_level' in df.columns:
        df['stress_level'] = df['stress_level'].map(
            STRESS_MAP).fillna(2).astype(float)

    # --- Multi-hot encode top_interests (top 12 categories) ---
    if 'top_interests' in df.columns:
        parsed_interests = df['top_interests'].apply(_parse_json_list)
        all_cats = []
        for lst in parsed_interests:
            all_cats.extend(lst)
        top_cats = [c for c, _ in _Counter(all_cats).most_common(12)]
        for cat in top_cats:
            col = 'int_' + cat.lower().replace(' ', '_').replace('/',
                                                                '_').replace('-', '_')[:18]
            df[col] = parsed_interests.apply(
                lambda lst, c=cat: 1 if c in lst else 0)
        df.drop(columns=['top_interests'], inplace=True)

    # --- Multi-hot encode communication_methods (top 6) ---
    if 'communication_methods' in df.columns:
        parsed_comms = df['communication_methods'].apply(_parse_json_list)
        all_comms = []
        for lst in parsed_comms:
            all_comms.extend(lst)
        top_comms = [c for c, _ in _Counter(all_comms).most_common(6)]
        for comm in top_comms:
            col = 'comm_' + comm.lower().replace(' ', '_').replace('/',
                                                                '_').replace('-', '_')[:18]
            df[col] = parsed_comms.apply(
                lambda lst, c=comm: 1 if c in lst else 0)
        df.drop(columns=['communication_methods'], inplace=True)

    # --- Label-encode remaining categoricals ---
    label_encoders = {}
    remaining_cats = [
        c for c in CATEGORICAL_COLS if c in df.columns and c != 'stress_level']
    for col in remaining_cats:
        le = LabelEncoder()
        df[col] = le.fit_transform(df[col].astype(str))
        label_encoders[col] = le

    # --- Ensure all numeric ---
    for col in df.columns:
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
        "faculty":               1.0,
        "al_stream":             1.0,
        "learning_style":        1.5,
        "confidence":            1.0,
        "intro_extro":           1.5,
        "stress_level":          1.5,
        "overwhelmed":           1.5,
        "social_setting":        1.5,
        "group_comfort":         1.0,
        "weekly_mood":           1.5,
        "weekly_feel_left_out":  1.0,
    },
    # Academic study groups: match by faculty, stream, and learning style
    "academic": {
        "faculty":               3.5,
        "al_stream":             3.5,
        "learning_style":        3.0,
        "confidence":            1.5,
        "intro_extro":           1.0,
        "stress_level":          1.2,
        "overwhelmed":           1.0,
        "social_setting":        0.5,
        "group_comfort":         1.5,
        "weekly_mood":           0.8,
        "weekly_feel_left_out":  0.5,
    },
    # Hobby / interest groups: shared interests, social comfort, extroversion
    "hobby": {
        "faculty":               0.5,
        "al_stream":             0.3,
        "learning_style":        2.0,
        "confidence":            1.5,
        "intro_extro":           2.5,
        "stress_level":          1.0,
        "overwhelmed":           0.8,
        "social_setting":        2.5,
        "group_comfort":         3.0,
        "weekly_mood":           0.5,
        "weekly_feel_left_out":  0.3,
    },
    # Personality: deep personality trait compatibility
    "personality": {
        "faculty":               0.5,
        "al_stream":             0.3,
        "learning_style":        1.0,
        "confidence":            3.0,
        "intro_extro":           3.5,
        "stress_level":          2.5,
        "overwhelmed":           2.5,
        "social_setting":        2.0,
        "group_comfort":         2.5,
        "weekly_mood":           1.0,
        "weekly_feel_left_out":  0.8,
    },
    # Wellbeing support groups: prioritise emotional & stress alignment
    "wellbeing": {
        "faculty":               0.5,
        "al_stream":             0.3,
        "learning_style":        0.8,
        "confidence":            1.5,
        "intro_extro":           1.5,
        "stress_level":          3.5,
        "overwhelmed":           3.5,
        "social_setting":        1.5,
        "group_comfort":         2.0,
        "weekly_mood":           3.5,
        "weekly_feel_left_out":  3.5,
    },
    # Sports teams: extroversion, group comfort, social setting
    "sports": {
        "faculty":               0.3,
        "al_stream":             0.2,
        "learning_style":        0.5,
        "confidence":            2.5,
        "intro_extro":           3.0,
        "stress_level":          0.8,
        "overwhelmed":           0.5,
        "social_setting":        3.5,
        "group_comfort":         3.5,
        "weekly_mood":           1.0,
        "weekly_feel_left_out":  0.5,
    },
    # Social bonding teams: communication style, social setting, interests
    "social": {
        "faculty":               0.5,
        "al_stream":             0.3,
        "learning_style":        1.5,
        "confidence":            2.0,
        "intro_extro":           2.5,
        "stress_level":          1.0,
        "overwhelmed":           1.0,
        "social_setting":        3.0,
        "group_comfort":         3.0,
        "weekly_mood":           1.5,
        "weekly_feel_left_out":  1.0,
    },
}


def apply_weights(df, purpose="academic"):
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
            centroids[best_c] = X[np.where(new_labels == best_c)[
                0]].mean(axis=0)
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
    
    try:
        data = request.get_json()

        if not data or 'group_size' not in data:
            return jsonify({"error": "Missing required field: group_size"}), 400

        group_size = int(data['group_size'])
        # academic|sports|wellbeing|social
        purpose = data.get('purpose', 'academic')

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
        info["processed_variance"] = {
            c: round(float(v), 6) for c, v in var.items()}
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

    import json as _json

    def _parse_list(val):
        """Safely parse a JSON array field into a Python list."""
        if isinstance(val, list):
            return val
        try:
            parsed = _json.loads(val) if isinstance(val, str) and val else []
            return parsed if isinstance(parsed, list) else []
        except Exception:
            return []

    def _safe_float(val, default=0.0):
        try:
            return float(val) if val is not None else default
        except (TypeError, ValueError):
            return default

    # ── Purpose-specific weights (each row sums to 100) ───────────────────
    # Keys: interests, learning_style, faculty, al_stream, intro_extro,
    #       stress_level, overwhelmed, mood, feel_left_out, communication,
    #       social_setting
    PURPOSE_WEIGHTS = {
        'default':     dict(interests=15, learning_style=10, faculty=10, al_stream=10,
                            intro_extro=10, stress_level=10, overwhelmed=5,
                            mood=5, feel_left_out=5, communication=10, social_setting=10),
        'academic':    dict(interests=5,  learning_style=15, faculty=20, al_stream=20,
                            intro_extro=5,  stress_level=5,  overwhelmed=3,
                            mood=3, feel_left_out=4, communication=10, social_setting=10),
        'hobby':       dict(interests=30, learning_style=10, faculty=3,  al_stream=2,
                            intro_extro=10, stress_level=5,  overwhelmed=3,
                            mood=3, feel_left_out=4, communication=15, social_setting=15),
        'personality': dict(interests=10, learning_style=10, faculty=3,  al_stream=2,
                            intro_extro=20, stress_level=20, overwhelmed=7,
                            mood=8, feel_left_out=5, communication=10, social_setting=5),
        'wellbeing':   dict(interests=5,  learning_style=5,  faculty=3,  al_stream=2,
                            intro_extro=10, stress_level=15, overwhelmed=15,
                            mood=15, feel_left_out=10, communication=10, social_setting=10),
        'sports':      dict(interests=20, learning_style=5,  faculty=3,  al_stream=2,
                            intro_extro=5,  stress_level=5,  overwhelmed=3,
                            mood=3, feel_left_out=4, communication=20, social_setting=30),
        'social':      dict(interests=10, learning_style=5,  faculty=3,  al_stream=2,
                            intro_extro=10, stress_level=5,  overwhelmed=3,
                            mood=5, feel_left_out=7, communication=25, social_setting=25),
    }

    def _safe_str(val):
        
        if val is None or pd.isna(val):
            return None
        s = str(val).strip()
        if s.lower() in ['', 'none', 'null', 'nan']:
            return None
        return s

    def compute_score(my_p, other_p, my_c, other_c, p=None):
        w = PURPOSE_WEIGHTS.get(p or 'default', PURPOSE_WEIGHTS['default'])
        score = 0.0

        # ── 1. Interest & Hobby Match ───────────────────────────────────────
        mine_interests = _parse_list(my_p.get('top_interests'))
        other_interests = _parse_list(other_p.get('top_interests'))
        common = len(set(mine_interests) & set(other_interests))
        max_len = max(len(mine_interests), 1)
        score += (common / max_len) * w['interests']

        my_ls = _safe_str(my_p.get('learning_style'))
        if my_ls and my_ls == _safe_str(other_p.get('learning_style')):
            score += w['learning_style']

        # ── 2. Academic Compatibility ───────────────────────────────────────
        my_fac = _safe_str(my_p.get('faculty'))
        if my_fac and my_fac == _safe_str(other_p.get('faculty')):
            score += w['faculty']

        my_als = _safe_str(my_p.get('al_stream'))
        if my_als and my_als == _safe_str(other_p.get('al_stream')):
            score += w['al_stream']

        # ── 3. Personality Compatibility ────────────────────────────────────
        intro_diff = abs(_safe_float(my_p.get('intro_extro'), 5) -
                        _safe_float(other_p.get('intro_extro'), 5))
        if intro_diff <= 2:
            score += w['intro_extro']

        my_stress = _safe_str(my_p.get('stress_level'))
        if my_stress and my_stress == _safe_str(other_p.get('stress_level')):
            score += w['stress_level']

        # ── 4. Emotional & Wellbeing Alignment ─────────────────────────────
        ow_diff = abs(_safe_float(my_p.get('overwhelmed'), 3) -
                    _safe_float(other_p.get('overwhelmed'), 3))
        if ow_diff <= 1:
            score += w['overwhelmed']

        if my_c and other_c:
            mood_diff = abs(_safe_float(my_c.get('mood'), 0) -
                            _safe_float(other_c.get('mood'), 0))
            if mood_diff <= 1:
                score += w['mood']
            flo_diff = abs(_safe_float(my_c.get('feel_left_out'), 0) -
                        _safe_float(other_c.get('feel_left_out'), 0))
            if flo_diff <= 1:
                score += w['feel_left_out']

        # ── 5. Communication Preference ─────────────────────────────────────
        mine_comm = set(_parse_list(my_p.get('communication_methods')))
        other_comm = set(_parse_list(other_p.get('communication_methods')))
        if mine_comm and other_comm and (mine_comm & other_comm):
            score += w['communication']

        # ── 6. Social Preferences ───────────────────────────────────────────
        my_social = _safe_str(my_p.get('social_setting'))
        if my_social and my_social == _safe_str(other_p.get('social_setting')):
            score += w['social_setting']

        return round(score, 1)

    try:
        data = request.get_json()

        if not data or 'user_id' not in data or 'group_size' not in data:
            return jsonify({"error": "Missing required fields: user_id, group_size"}), 400

        target_user_id = int(data['user_id'])
        purpose = data.get('purpose', 'default')
        group_size = int(data['group_size'])

        if group_size < 2 or group_size > 20:
            return jsonify({"error": "group_size must be between 2 and 20"}), 400

        # ── Load all student profiles ───────────────────────────────────────
        conn = get_db_connection()
        profile_query = """
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
                sp.group_comfort,
                sp.top_interests,
                sp.communication_methods
            FROM student_profiles sp
            INNER JOIN users u ON u.id = sp.user_id
            ORDER BY sp.user_id
        """
        df_full = pd.read_sql(profile_query, conn)

        # ── Load latest weekly check-in per user (single query) ─────────────
        checkin_query = """
            SELECT wc.user_id, wc.mood, wc.feel_left_out
            FROM weekly_checkins wc
            INNER JOIN (
                SELECT user_id, MAX(week_start) AS latest_week
                FROM weekly_checkins
                GROUP BY user_id
            ) latest ON wc.user_id = latest.user_id
                    AND wc.week_start = latest.latest_week
        """
        checkin_df = pd.read_sql(checkin_query, conn)
        conn.close()

        if target_user_id not in df_full['user_id'].values:
            return jsonify({"error": f"No profile found for user_id={target_user_id}"}), 404

        # ── Build look-up dicts ─────────────────────────────────────────────
        checkins = {
            int(row['user_id']): {
                'mood':          row['mood'],
                'feel_left_out': row['feel_left_out'],
            }
            for _, row in checkin_df.iterrows()
        }

        profiles = {
            int(row['user_id']): row.to_dict()
            for _, row in df_full.iterrows()
        }

        my_profile = profiles[target_user_id]
        my_checkin = checkins.get(target_user_id)

        # ── Score every other student ───────────────────────────────────────
        candidates = []
        for uid, profile in profiles.items():
            if uid == target_user_id:
                continue
            other_checkin = checkins.get(uid)
            score = compute_score(my_profile, profile,
                                my_checkin, other_checkin, purpose)
            candidates.append({
                "user_id":         uid,
                "name":            profile.get('name', ''),
                "faculty":         profile.get('faculty', ''),
                "al_stream":       profile.get('al_stream', ''),
                "learning_style":  profile.get('learning_style', ''),
                "stress_level":    profile.get('stress_level', ''),
                "social_setting":  profile.get('social_setting', ''),
                "match_score":     score,
            })

        # Sort descending by score (best match first)
        candidates.sort(key=lambda x: x['match_score'], reverse=True)

        top_n = group_size - 1
        top_matches = candidates[:top_n]
        for rank, m in enumerate(top_matches, start=1):
            m['rank'] = rank

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
