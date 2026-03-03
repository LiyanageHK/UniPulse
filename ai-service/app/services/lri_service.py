"""
LRI (Linguistic Risk Index) calculation service.

Weighted formula:
    LRI = (0.4 × stress_probability
         + 0.2 × sentiment_score
         + 0.15 × pronoun_ratio
         + 0.15 × absolutist_score
         + 0.1 × withdrawal_score) × 100

Risk Levels:
    LRI < 30  → Low
    30 ≤ LRI < 60 → Medium
    60 ≤ LRI < 80 → High
    LRI ≥ 80 → Critical
"""


# Weight configuration
WEIGHTS = {
    "stress_probability": 0.40,
    "sentiment_score": 0.20,
    "pronoun_ratio": 0.15,
    "absolutist_score": 0.15,
    "withdrawal_score": 0.10,
}

RISK_THRESHOLDS = [
    (80, "Critical"),
    (60, "High"),
    (30, "Medium"),
    (0, "Low"),
]


def calculate_lri(
    stress_probability: float,
    sentiment_score: float,
    pronoun_ratio: float,
    absolutist_score: float,
    withdrawal_score: float,
) -> float:
    """
    Calculate the Linguistic Risk Index (0–100).
    All input factors should be in range [0, 1].
    """
    raw = (
        WEIGHTS["stress_probability"] * stress_probability
        + WEIGHTS["sentiment_score"] * sentiment_score
        + WEIGHTS["pronoun_ratio"] * pronoun_ratio
        + WEIGHTS["absolutist_score"] * absolutist_score
        + WEIGHTS["withdrawal_score"] * withdrawal_score
    )

    lri = raw * 100.0
    return round(min(max(lri, 0.0), 100.0), 2)


def determine_risk_level(lri_score: float) -> str:
    """
    Determine risk level from LRI score.
    """
    for threshold, level in RISK_THRESHOLDS:
        if lri_score >= threshold:
            return level
    return "Low"