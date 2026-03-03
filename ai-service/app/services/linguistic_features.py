"""
Linguistic feature extraction for social inclusion risk detection.
Computes sentiment, pronoun ratio, absolutist word score, and social withdrawal score.
"""

import re
from nltk.sentiment.vader import SentimentIntensityAnalyzer

# Initialize VADER once
_vader = SentimentIntensityAnalyzer()


# First-person (self-referential) pronouns
SELF_PRONOUNS = {"i", "me", "my", "myself", "mine"}

# Group / collective pronouns
GROUP_PRONOUNS = {"we", "us", "our", "ours", "ourselves"}

# Absolutist words indicating black-and-white thinking
ABSOLUTIST_WORDS = {
    "always", "never", "nothing", "completely", "entirely", "absolutely",
    "everyone", "no one", "nobody", "everything", "all", "none",
    "totally", "perfectly", "impossible", "forever", "constant", "constantly",
    "every", "any", "whole", "must", "certain", "definitely", "ever",
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
    Compute a negativity-oriented sentiment score (0–1) using VADER.
    VADER's compound score ranges from -1 (most negative) to +1 (most positive).
    Convert: S_negative = (1 - compound) / 2
    So -1 → 1.0, 0 → 0.5, +1 → 0.0
    """
    if not text.strip():
        return 0.0

    scores = _vader.polarity_scores(text)
    compound = scores['compound']  # -1.0 to 1.0

    # Convert to 0–1 negative scale
    negative_score = (1.0 - compound) / 2.0
    return round(min(max(negative_score, 0.0), 1.0), 4)


def compute_pronoun_ratio(text: str) -> float:
    """
    Compute a self-referential pronoun score (0–1).
    Formula: self_count / (self_count + group_count)
    If all pronouns are self-referential (no "we/us/our"), returns 1.0.
    If no pronouns at all, returns 0.0.
    """
    words = re.findall(r'\b[a-zA-Z]+\b', text.lower())
    if not words:
        return 0.0

    self_count = sum(1 for w in words if w in SELF_PRONOUNS)
    group_count = sum(1 for w in words if w in GROUP_PRONOUNS)

    total_pronouns = self_count + group_count
    if total_pronouns == 0:
        return 0.0

    ratio = self_count / total_pronouns
    return round(min(max(ratio, 0.0), 1.0), 4)


def compute_absolutist_score(text: str) -> float:
    """
    Compute the proportion of sentences that contain at least one absolutist word.
    Sentence-based normalization gives more meaningful scores than word-ratio.
    E.g. 4 out of 5 sentences containing absolutist words → 0.80.
    """
    # Split text into sentences
    sentences = re.split(r'[.!?]+', text)
    sentences = [s.strip() for s in sentences if s.strip()]

    if not sentences:
        return 0.0

    sentences_with_absolutist = 0
    for sentence in sentences:
        words = re.findall(r'\b[a-zA-Z]+\b', sentence.lower())
        if any(w in ABSOLUTIST_WORDS for w in words):
            sentences_with_absolutist += 1

    score = sentences_with_absolutist / len(sentences)
    return round(min(max(score, 0.0), 1.0), 4)


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
