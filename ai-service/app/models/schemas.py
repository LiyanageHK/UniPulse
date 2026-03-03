from pydantic import BaseModel, Field
from typing import Optional


class TextRequest(BaseModel):
    text: str = Field(..., min_length=1, description="The journal text to analyze")


class AnalysisResponse(BaseModel):
    stress_probability: float = Field(..., ge=0.0, le=1.0)
    sentiment_score: float = Field(..., ge=0.0, le=1.0)
    pronoun_ratio: float = Field(..., ge=0.0, le=1.0)
    absolutist_score: float = Field(..., ge=0.0, le=1.0)
    withdrawal_score: float = Field(..., ge=0.0, le=1.0)
    lri_score: float = Field(..., ge=0.0, le=1.0)
    risk_level: str
    debug: Optional[dict] = None

    class Config:
        json_schema_extra = {
            "example": {
                "stress_probability": 0.90,
                "sentiment_score": 0.94,
                "pronoun_ratio": 1.0,
                "absolutist_score": 0.80,
                "withdrawal_score": 0.05,
                "lri_score": 0.91,
                "risk_level": "High",
            }
        }


class ErrorResponse(BaseModel):
    detail: str