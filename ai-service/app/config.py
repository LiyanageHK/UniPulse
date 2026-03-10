"""
Application configuration for the UniPulse AI Risk Detection Service.
"""

import os

# Model configuration
MODEL_PATH = os.getenv("MODEL_PATH", "./ml_modal")

# Server configuration
HOST = os.getenv("HOST", "0.0.0.0")
PORT = int(os.getenv("PORT", 8000))

# LRI formula: simple average of 4 components (0–1 scale)
# LRI = (P_model + S_negative + R_normalized + F_normalized) / 4

# Risk thresholds (0–1 scale)
RISK_THRESHOLDS = {
    "high": 0.6,
    "moderate": 0.3,
    "low": 0.0,
}
