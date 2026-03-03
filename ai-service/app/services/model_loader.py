from transformers import RobertaTokenizer, RobertaForSequenceClassification
import torch

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

MODEL_PATH = "./saved_model"

tokenizer = RobertaTokenizer.from_pretrained(MODEL_PATH)
model = RobertaForSequenceClassification.from_pretrained(MODEL_PATH)

model.to(device)
model.eval()