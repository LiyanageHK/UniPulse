from fastapi import APIRouter, HTTPException
from app.models.schemas import TextRequest, AnalysisResponse
from app.services.inference_service import predict_stress, full_analysis
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


@router.post("/predict", response_model=AnalysisResponse)
def predict(request: TextRequest):
    """
    Perform full multi-factor analysis on journal text.
    Returns stress probability, linguistic features, LRI score, and risk level.
    """
    try:
        result = full_analysis(request.text)
        return result
    except Exception as e:
        logger.error(f"Prediction failed: {str(e)}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Analysis failed: {str(e)}")


@router.get("/health")
def health_check():
    """Health check endpoint."""
    return {"status": "healthy", "service": "UniPulse AI Risk Detection"}