"""
LRI (Linguistic Risk Index) calculation service.

Formula:
    LRI = (P_model + S_negative + R_normalized + F_normalized) / 4

All inputs and output are on a 0–1 scale.

Risk Levels:
    0.0 – 0.3  → Low
    0.3 – 0.6  → Moderate
    0.6 – 1.0  → High
"""


RISK_THRESHOLDS = [
    (0.6, "High"),
    (0.3, "Moderate"),
    (0.0, "Low"),
]


def calculate_lri(
    stress_probability: float,
    sentiment_score: float,
    pronoun_ratio: float,
    absolutist_score: float,
) -> float:
    """
    Calculate the Linguistic Risk Index (0–1).
    All input factors should be in range [0, 1].

    LRI = (P_model + S_negative + R_normalized + F_normalized) / 4
    """
    lri = (stress_probability + sentiment_score + pronoun_ratio + absolutist_score) / 4.0
    return round(min(max(lri, 0.0), 1.0), 4)


def determine_risk_level(lri_score: float) -> str:
    """
    Determine risk level from LRI score (0–1 scale).
    """
    for threshold, level in RISK_THRESHOLDS:
        if lri_score >= threshold:
            return level
    return "Low"