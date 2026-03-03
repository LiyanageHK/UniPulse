"""
Inference service: runs RoBERTa stress prediction and combines with
linguistic feature extraction to produce a full risk analysis.
"""

import torch
import logging
from app.services.model_loader import model, tokenizer, device
from app.services.linguistic_features import extract_all_features
from app.services.lri_service import calculate_lri, determine_risk_level

logger = logging.getLogger(__name__)


def predict_stress(text: str) -> float:
    """
    Run RoBERTa inference and return stress probability [0, 1].
    """
    inputs = tokenizer(text, return_tensors="pt", truncation=True, padding=True, max_length=512)
    inputs = {k: v.to(device) for k, v in inputs.items()}

    with torch.no_grad():
        outputs = model(**inputs)

    probs = torch.nn.functional.softmax(outputs.logits, dim=1)
    stress_probability = probs[0][1].item()
    return round(stress_probability, 4)


def full_analysis(text: str) -> dict:
    """
    Perform complete multi-factor analysis:
    1. Stress probability via RoBERTa
    2. Linguistic features (sentiment, pronouns, absolutist, withdrawal)
    3. LRI score & risk level
    """
    if not text or not text.strip():
        return {
            "stress_probability": 0.0,
            "sentiment_score": 0.0,
            "pronoun_ratio": 0.0,
            "absolutist_score": 0.0,
            "withdrawal_score": 0.0,
            "lri_score": 0.0,
            "risk_level": "Low",
        }

    try:
        # Step 1: RoBERTa stress prediction
        stress_prob = predict_stress(text)

        # Step 2: Linguistic features
        features = extract_all_features(text)

        # Step 3: Calculate LRI
        lri_score = calculate_lri(
            stress_probability=stress_prob,
            sentiment_score=features["sentiment_score"],
            pronoun_ratio=features["pronoun_ratio"],
            absolutist_score=features["absolutist_score"],
            withdrawal_score=features["withdrawal_score"],
        )

        # Step 4: Determine risk level
        risk_level = determine_risk_level(lri_score)

        result = {
            "stress_probability": stress_prob,
            "sentiment_score": features["sentiment_score"],
            "pronoun_ratio": features["pronoun_ratio"],
            "absolutist_score": features["absolutist_score"],
            "withdrawal_score": features["withdrawal_score"],
            "lri_score": lri_score,
            "risk_level": risk_level,
        }

        logger.info(f"Analysis complete — LRI: {lri_score}, Risk: {risk_level}")
        return result

    except Exception as e:
        logger.error(f"Analysis failed: {str(e)}", exc_info=True)
        raise