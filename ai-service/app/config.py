"""
Application configuration for the UniPulse AI Risk Detection Service.
"""

import os

# Model configuration
MODEL_PATH = os.getenv("MODEL_PATH", "./saved_model")

# Server configuration
HOST = os.getenv("HOST", "0.0.0.0")
PORT = int(os.getenv("PORT", 8000))

# LRI Weight configuration
LRI_WEIGHTS = {
    "stress_probability": 0.40,
    "sentiment_score": 0.20,
    "pronoun_ratio": 0.15,
    "absolutist_score": 0.15,
    "withdrawal_score": 0.10,
}

# Risk thresholds
RISK_THRESHOLDS = {
    "critical": 80,
    "high": 60,
    "medium": 30,
    "low": 0,
}
