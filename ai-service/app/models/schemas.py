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
    lri_score: float = Field(..., ge=0.0, le=100.0)
    risk_level: str

    class Config:
        json_schema_extra = {
            "example": {
                "stress_probability": 0.72,
                "sentiment_score": 0.65,
                "pronoun_ratio": 0.12,
                "absolutist_score": 0.08,
                "withdrawal_score": 0.05,
                "lri_score": 48.35,
                "risk_level": "Medium",
            }
        }


class ErrorResponse(BaseModel):
    detail: str