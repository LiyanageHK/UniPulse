import logging
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.routes import router

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="UniPulse AI Risk Detection Service",
    description="Multi-factor social inclusion risk detection using RoBERTa and linguistic analysis",
    version="2.0.0",
)

# CORS middleware for Laravel communication
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000", "http://localhost"],
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(router)


@app.on_event("startup")
def startup_event():
    logger.info("UniPulse AI Risk Detection Service started successfully")
    # Model is loaded at import time via model_loader.py
    from app.services.model_loader import model  # noqa: F401
    logger.info("RoBERTa model loaded and ready")