"""
Linguistic feature extraction for social inclusion risk detection.
Computes sentiment, pronoun ratio, absolutist word score, and social withdrawal score.
"""

import re
from textblob import TextBlob


# First-person pronouns (case-insensitive matching)
FIRST_PERSON_PRONOUNS = {"i", "me", "my", "myself", "mine"}

# Absolutist words indicating black-and-white thinking
ABSOLUTIST_WORDS = {
    "always", "never", "nothing", "completely", "entirely", "absolutely",
    "everyone", "no one", "nobody", "everything", "all", "none",
    "totally", "perfectly", "impossible", "forever", "constant",
    "every", "any", "whole", "must", "certain", "definitely",
}

# Social withdrawal phrases and keywords
WITHDRAWAL_PHRASES = [
    "left out", "no friends", "no one to talk", "nobody to talk",
    "all alone", "cut off", "pushed away", "shut out",
    "don't belong", "dont belong", "do not belong",
    "no one cares", "nobody cares", "no one understands",
    "nobody understands", "feel invisible",
]
WITHDRAWAL_WORDS = {"alone", "isolated", "ignored", "excluded", "lonely", "withdrawn", "alienated"}


def compute_sentiment_score(text: str) -> float:
    """
    Compute a negativity-oriented sentiment score (0–1).
    0 = fully positive, 1 = fully negative.
    Uses TextBlob polarity which ranges from -1 (negative) to +1 (positive).
    """
    if not text.strip():
        return 0.0

    blob = TextBlob(text)
    polarity = blob.sentiment.polarity  # -1.0 to 1.0

    # Convert to 0–1 negative scale: -1 → 1.0, 0 → 0.5, +1 → 0.0
    negative_score = (1.0 - polarity) / 2.0
    return round(min(max(negative_score, 0.0), 1.0), 4)


def compute_pronoun_ratio(text: str) -> float:
    """
    Compute the ratio of first-person pronouns to total words.
    """
    words = re.findall(r'\b[a-zA-Z]+\b', text.lower())
    if not words:
        return 0.0

    pronoun_count = sum(1 for w in words if w in FIRST_PERSON_PRONOUNS)
    ratio = pronoun_count / len(words)
    return round(min(ratio, 1.0), 4)


def compute_absolutist_score(text: str) -> float:
    """
    Compute the ratio of absolutist words to total words.
    """
    words = re.findall(r'\b[a-zA-Z]+\b', text.lower())
    if not words:
        return 0.0

    absolutist_count = sum(1 for w in words if w in ABSOLUTIST_WORDS)
    score = absolutist_count / len(words)
    return round(min(score, 1.0), 4)


def compute_withdrawal_score(text: str) -> float:
    """
    Compute a social withdrawal score by detecting withdrawal-related
    phrases and individual keywords, normalized by total words.
    """
    text_lower = text.lower()
    words = re.findall(r'\b[a-zA-Z]+\b', text_lower)
    if not words:
        return 0.0

    total_words = len(words)
    hit_count = 0

    # Check multi-word phrases first
    for phrase in WITHDRAWAL_PHRASES:
        occurrences = text_lower.count(phrase)
        hit_count += occurrences * len(phrase.split())

    # Check single words
    for w in words:
        if w in WITHDRAWAL_WORDS:
            hit_count += 1

    score = hit_count / total_words
    return round(min(score, 1.0), 4)


def extract_all_features(text: str) -> dict:
    """
    Extract all linguistic features in one call.
    """
    return {
        "sentiment_score": compute_sentiment_score(text),
        "pronoun_ratio": compute_pronoun_ratio(text),
        "absolutist_score": compute_absolutist_score(text),
        "withdrawal_score": compute_withdrawal_score(text),
    }
