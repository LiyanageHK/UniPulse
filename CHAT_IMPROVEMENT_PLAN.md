# UniPulse AI Chat — Improvement Plan

> Last updated: 2026-02-19
> Branch: `development_backup`

---

## Table of Contents

1. [Current System Problems](#1-current-system-problems)
2. [Core Behaviour Rules to Fix](#2-core-behaviour-rules-to-fix)
3. [RAG Improvements](#3-rag-improvements)
4. [Speed Improvements](#4-speed-improvements)
5. [Suicide / Red Flag Handling](#5-suicide--red-flag-handling)
6. [Phase 1 — Quick Wins](#6-phase-1--quick-wins-no-migrations)
7. [Phase 2 — Medium Effort](#7-phase-2--medium-effort)
8. [Phase 3 — Differentiating Features](#8-phase-3--differentiating-features)
9. [Migration Summary](#9-migration-summary)
10. [Implementation Order](#10-implementation-order)
11. [Verification Checklist](#11-verification-checklist)

---

## 1. Current System Problems

| Problem | Where | Impact |
|---------|-------|--------|
| `max_tokens: 250` limits every response to ~180 words | `config/services.php` | AI cannot give deep answers |
| System prompt says **"under 90 words"** — contradicts token limit increase | `AiChatService::getSystemPrompt()` line 352 | AI is artificially capped |
| `retrieveContext()` loads **ALL** user embeddings into PHP (`->get()`) | `RagRetrievalService.php` line 50 | Will crash at scale (50+ conversations) |
| `generateEmbedding()` called during request → adds ~500ms | `RagRetrievalService.php` line 40 | Slows every single message |
| `retrieveMemories()` makes a **second** embedding API call for the same query | `RagRetrievalService.php` line 214 | Doubles embedding API round-trips |
| RAG always loads memories AND conversation context even when not needed | `getSmartContext()` line 293 | Wasteful, slow |
| Conversation history loads ALL messages then slices in PHP | `AiChatService::buildPrompt()` line 295 | Inefficient DB query |
| No streaming — full response waits 5–10 seconds before appearing | `generateResponse()` line 438 | Terrible UX |
| AI suggests things without understanding the problem first | System prompt | Violates intended design |
| AI injects past conversation memories into new chats unprompted | `buildPrompt()` | Violates privacy/trust |
| Suicide/red flag — model still generates a response | `chat()` full flow | Dangerous: AI should not engage |
| `topCounselors` variable referenced but never defined | `AiChatService.php` line 521 | **PHP Fatal Error / Dead Code** |
| No follow-up tracking after a red flag | No implementation | Students at risk are not followed up |

---

## 2. Core Behaviour Rules to Fix

These are the 5 rules you specified. Each maps to a concrete code change.

---

### Rule 1 — Keep AI responses short

**Current problem:** System prompt says "under 90 words" but config has `max_tokens: 250`. Neither is consistently enforced. The AI sometimes gives long paragraphs.

**Fix in `AiChatService::getSystemPrompt()`:**

```
Replace:
  "Keep responses short (2-3 sentences, under 90 words)"

With:
  "Reply in 1–3 sentences maximum. Never write lists or paragraphs unless
   the student explicitly asks for them. One thought per message."
```

**Also set in `config/services.php`:**

```php
'max_tokens' => 150,   // was 250 — tighter cap forces brevity
'temperature' => 0.4,  // keep as-is
```

150 tokens ≈ 110 words — enough for a warm 2-sentence reply + one question.

---

### Rule 2 — Ask why first. Understand the exact problem before helping.

**Current state:** `require_clarification` exists in config and `buildPrompt()` adds a system message for the first 2 turns. But after turn 2, the AI is free to jump into advice.

**Fix in `AiChatService::buildPrompt()`:**

Extend the clarification enforcement beyond just the first 2 turns — make it conditional on whether the AI has received a specific answer:

```php
// Replace the current block at line 310:
if ($requireClarification && empty($crisisFlags)) {
    if ($assistantCount < $clarificationOnlyUntil) {
        // Early turns: ONLY ask why
        $messages[] = [
            'role' => 'system',
            'content' => 'Ask exactly one focused "why" or "what" question to understand
                          the specific situation. Do NOT give any advice or suggestions yet.
                          Keep to 1-2 short sentences.'
        ];
    } else {
        // Later turns: still probe before advising
        $messages[] = [
            'role' => 'system',
            'content' => 'Before suggesting any coping strategy or advice, confirm you
                          understand the specific situation. If unsure, ask one clarifying
                          question instead.'
        ];
    }
}
```

**Also update `getSystemPrompt()` guidelines:**

```
Replace:
  "Ask a direct why/what question to understand the exact problem before offering advice"
  "Do not give suggestions or solutions until you understand the specific situation"

With:
  "Your priority is understanding, not advising. Ask why. Ask what specifically happened.
   Only offer suggestions after the student has described their actual situation in their
   own words. If in doubt, ask — do not assume."
```

---

### Rule 3 — Do not suggest anything without knowing why

**This is an extension of Rule 2.** The system prompt already states it, but the AI can still drift toward suggestions after a few turns.

**Additional system prompt line to add:**

```
"If you are about to suggest a coping strategy, technique, or resource — stop. First ask
 yourself: do I know the specific cause of their distress? If not, ask that question
 instead of giving the suggestion."
```

This prompt-level guard is the most reliable way to enforce this without adding classification logic.

---

### Rule 4 — Do not use past chat data in new conversations without asking

**Current state:** `include_past_conversations` is `false` by default, and `buildPrompt()` adds:

```php
'Do not reference information from past conversations or memories unless the student
 explicitly asks you to.'
```

This is correct but incomplete. The RAG context (`getSmartContext()`) still injects **profile data** and **memories** into every new conversation by default, even without the student asking. The AI then "knows" things without the student realising why.

**Fix in `RagRetrievalService::getSmartContext()`:**

Split profile context from memory/past-conversation context:

```php
// Always include profile (name, university, faculty) — this is registration data
// NEVER include memories or past conversation embeddings unless explicitly triggered

$memories = collect(); // Default: always empty for new chat turns

if ($includePastConversations) {
    $memories = $this->retrieveMemories($user, $query, 10);
}
```

**Fix in `AiChatService::buildPrompt()`:**

Add a clearer guard instruction:

```php
$messages[] = [
    'role' => 'system',
    'content' => 'You are starting fresh. Do not mention, reference, or imply knowledge
                  of anything from previous conversations. Only use information the student
                  tells you in this conversation. If you recall something from before,
                  keep it silent unless the student brings it up first.'
];
```

This replaces the current weaker instruction at line 288–291.

---

### Rule 5 — Make AI responses faster

See **Section 4 — Speed Improvements** for the full breakdown. Summary:

- Reuse the query embedding (generate once, pass to both `retrieveContext` and `retrieveMemories`)
- Cap DB query with `->limit(150)` and `->where('importance_score', '>', 0.2)` before PHP cosine loop
- Load conversation history with `->take()` in DB query, not in PHP
- Reduce `max_tokens` to 150 (shorter response = faster generation)
- Remove `getConversationSummary()` from every request (it's rarely useful and adds a DB query)

---

## 3. RAG Improvements

### Problem 1: Full table scan on every request

**File:** `RagRetrievalService.php` line 50

```php
// CURRENT — loads everything:
$embeddings = ConversationEmbedding::where('user_id', $user->id)->get();

// IMPROVED — pre-filter in DB:
$embeddings = ConversationEmbedding::where('user_id', $user->id)
    ->where('importance_score', '>', 0.2)   // skip near-zero importance
    ->when(!$includePastConversations && $currentConversationId, function ($q) use ($currentConversationId) {
        $q->where(function ($q2) use ($currentConversationId) {
            $q2->where('type', ConversationEmbedding::TYPE_PROFILE)
               ->orWhere('conversation_id', $currentConversationId);
        });
    })
    ->orderBy('importance_score', 'desc')
    ->limit(150)    // cap: enough for any real user, prevents memory blowup
    ->get();
```

**Why this matters:** A user with 100 conversations could have 2000+ embeddings. Loading all into PHP and looping for cosine similarity is O(n) per message. With limit(150) pre-sorted by importance, we only compute similarity on the most relevant candidates.

---

### Problem 2: Double embedding API call per message

**File:** `RagRetrievalService::getSmartContext()` lines 283–295

`retrieveContext()` generates an embedding at line 40.
`retrieveMemories()` generates **another embedding** at line 214 for the same query string.

That is 2 API calls × ~200ms each = 400ms wasted per message.

**Fix:** Generate the embedding once and pass it through:

```php
// In getSmartContext():
$queryEmbedding = $this->embeddingService->generateEmbedding($query);

if (!$queryEmbedding) {
    return $this->emptyContext();
}

$ragContext = $this->retrieveContextWithEmbedding($user, $queryEmbedding, ...);
$memories = $includePastConversations
    ? $this->retrieveMemoriesWithEmbedding($user, $queryEmbedding, 10)
    : collect();
```

Refactor `retrieveContext()` → `retrieveContextWithEmbedding(embedding: array)` accepting a pre-generated embedding. Same for `retrieveMemories()`.

**Saving: ~200–400ms per request.**

---

### Problem 3: `getConversationSummary()` called on every request

**File:** `RagRetrievalService::getSmartContext()` lines 301–303

This runs a second DB query fetching the last 10 message embeddings of the current conversation on every single request. For most early-conversation turns, these embeddings don't even exist yet.

**Fix:** Remove this call from the hot path. The actual conversation history is already included via `buildPrompt()` from the `messages` table. The embedding-based summary is redundant.

```php
// Remove:
$recentContext = '';
if ($currentConversationId) {
    $recentContext = $this->getConversationSummary($currentConversationId);
}

// Replace with:
$recentContext = ''; // Conversation history handled by buildPrompt() directly
```

**Saving: 1 DB query per request.**

---

### Problem 4: Conversation history loaded inefficiently

**File:** `AiChatService::buildPrompt()` lines 295–306

```php
// CURRENT — loads ALL messages then slices in PHP:
$allMessages = $conversation->messages()->orderBy('created_at', 'asc')->get();
$recentMessages = $allMessages->slice(...);

// IMPROVED — slice in DB:
$historyLimit = (int) config('services.openai.chat.history_limit', 8);
$recentMessages = $conversation->messages()
    ->where('role', '!=', 'system')
    ->orderBy('created_at', 'desc')
    ->take($historyLimit)
    ->get()
    ->reverse();
```

This avoids loading 100+ messages into PHP to then discard 94 of them.

---

### Problem 5: Memory retrieval always runs even when not needed

When `includePastConversations = false` (the default for all new chats), `retrieveMemories()` should not be called at all. This is already guarded in `getSmartContext()` but the check should be verified to be strictly `false` not falsy.

No code change needed — just verify the condition at line 293 is `=== false` not just falsy.

---

## 4. Speed Improvements

Ranked by estimated time saving:

| Change | Estimated Saving | Difficulty |
|--------|-----------------|------------|
| Generate embedding once (Rule above) | ~200–400ms | Medium |
| Remove `getConversationSummary()` from hot path | ~50–100ms | Easy |
| DB-level pre-filter on embeddings (limit 150) | ~50–200ms | Easy |
| Load conversation history with `->take()` in DB | ~20–50ms | Easy |
| Reduce `max_tokens` to 150 (shorter = faster generation) | ~500–2000ms | Easy (config) |
| **Streaming responses** (tokens appear live) | Perceived: instant | Hard |

### Token limit for speed

```php
// config/services.php
'max_tokens' => 150,   // Short replies are faster to generate AND better UX
```

A 150-token response generates in ~1–2 seconds. A 500-token response takes 4–8 seconds. For a mental health support bot focused on asking questions (not writing essays), 150 is right.

### Streaming (Phase 2)

See Phase 2 — Feature 2.1. Streaming does not make the total response faster but the student sees the first word within ~1 second instead of waiting 5–8 seconds for the full response. This is the biggest perceived speed improvement.

---

## 5. Suicide / Red Flag Handling

### Current behaviour (PROBLEM)

When a red flag is detected:
1. Crisis detection runs ✓
2. **The AI still generates a full response** — the model is NOT blocked ✗
3. Crisis UI is shown alongside the AI response ✓

This is dangerous. The AI model generating a response after a suicide-risk detection may produce something harmful, dismissive, or inappropriate — even with the adjusted system prompt.

### Required new behaviour

When a **red flag** is detected:
1. Crisis detection runs ✓
2. **STOP — do not call `generateResponse()`** — return a hard-coded, human-reviewed safe response ✓
3. Show crisis UI (hotlines, counselor categories) ✓
4. **Create a follow-up task** to check on the student ✓
5. Lock the conversation from further AI responses until a counselor acknowledges ✓

### Implementation in `AiChatService::chat()`

```php
// After step 2 (crisis detection), add:
$redFlags = array_filter($crisisFlags, fn($f) => $f['severity'] === 'red');

if (!empty($redFlags)) {
    // Use safe, human-authored response — do NOT call generateResponse()
    $aiResponse = $this->getRedFlagSafeResponse();

    // Store the message
    $assistantMessage = Message::create([...]);

    // Create follow-up task
    $this->scheduleFollowUp($user, $conversation, 'red');

    // Create crisis alert
    foreach ($redFlags as $flag) { ... }

    // Return immediately — skip RAG, skip AI generation
    return $this->buildRedFlagResponse($aiResponse, $assistantMessage, $crisisFlags);
}
```

### Hard-coded safe responses (human-reviewed, not AI-generated)

```php
protected function getRedFlagSafeResponse(): string
{
    // DO NOT let the AI generate this. Use only human-reviewed text.
    return "I hear you, and I want you to know you matter. What you're feeling right now is real and it's serious — and you deserve real support, not just a chat. Please reach out to someone who can truly help you right now. I've shared some numbers below. You don't have to face this alone.";
}
```

### Follow-up system (new feature)

When a red flag is detected, create a scheduled follow-up:

**New DB table: `student_followups`**

```
id, user_id, conversation_id, severity, reason, due_at, status (pending/sent/acknowledged), counselor_notified_at
```

**Follow-up logic:**
- After red flag: schedule follow-up in 24 hours
- After yellow flag: schedule follow-up in 48 hours
- Follow-up sends a gentle check-in message at the start of the next time the student opens the chat
- Counselor dashboard shows all pending follow-ups

**Check-in message (shown when student returns after a red-flag session):**

```
"Hi [name]. I wanted to check in — how are you feeling today compared to our last conversation?
You don't have to share anything you're not comfortable with. I'm just here if you need to talk."
```

This transforms the system from reactive (crisis detection only) to proactive (ongoing support).

---

## 6. Phase 1 — Quick Wins (No Migrations)

### 1.1 — Fix the 5 Core Behaviour Rules
**Files:** `app/Services/AiChatService.php`, `config/services.php`

- Set `max_tokens: 150`
- Rewrite system prompt guidelines (Rules 1–4 above)
- Extend clarification enforcement beyond turn 2
- Strengthen "no past context in new chats" guard

### 1.2 — Fix RAG Speed (No Migration)
**Files:** `app/Services/RagRetrievalService.php`

- Generate embedding once, pass to both retrieve methods
- Add `->limit(150)->where('importance_score', '>', 0.2)` pre-filter
- Remove `getConversationSummary()` from hot path
- Fix conversation history DB query

### 1.3 — Fix Dead Code Bug
**File:** `app/Services/AiChatService.php` line 521

`$topCounselors` is referenced but never defined in `getCounselorRecommendationsByCategory()`. This causes a PHP fatal error if that method is ever called. Either complete the implementation or remove the method.

### 1.4 — Message Copy Button
**File:** `resources/views/chat-support.blade.php`

Add copy icon to assistant messages using `navigator.clipboard.writeText()` + existing `showToast()`.

### 1.5 — AI-Generated Conversation Titles
**File:** `app/Services/AiChatService.php`

Replace `generateConversationTitle()` (first 6 words) with a cheap secondary LLM call (max_tokens: 15, timeout: 5s). Fallback to word-slice on failure.

### 1.6 — Keyboard Shortcuts + Shift+Enter Fix
**File:** `resources/views/chat-support.blade.php`

- Fix Shift+Enter to add newline (currently Enter always sends, no newline option)
- Add `Ctrl/Cmd+N` → new conversation, `Ctrl/Cmd+K` → focus input
- Track `lastSentMessage`, use Up Arrow to recall in empty input

### 1.7 — Suggested Follow-Up Prompts
**Files:** `AiChatService.php`, `ChatSupportController.php`, `chat-support.blade.php`

After each AI response, return 3 context-aware prompt chips:
- Red flag → no chips
- Yellow flag → only help-seeking options ("I'm safe right now", "Yes I'd like to speak with a counselor")
- Normal → rotate from pool of 7 coping/reflection starters

---

## 7. Phase 2 — Medium Effort

### 2.1 — Streaming Responses (SSE)
**New route:** `POST /chat/message/stream`
**Files:** `AiChatService.php`, `ChatSupportController.php`, `routes/web.php`, `chat-support.blade.php`

**Architecture:**
1. Refactor `chat()` into: `prepareChat()` (crisis + RAG, runs synchronously), `streamResponse()` (API call with `stream: true`, yields tokens via callback), `saveStreamedResponse()`, `buildCrisisPayload()`
2. Controller returns `StreamedResponse` with `Content-Type: text/event-stream`
3. Events: `data: {"token": "word "}` per token, then `data: {"done": true, "message_id": ..., "crisis_response": ..., "suggested_prompts": [...]}`
4. Frontend: fetch with `response.body.getReader()`, updates bubble token by token
5. Add "Stop generating" button — calls `reader.cancel()`

**Critical:** Crisis detection MUST run before stream starts. Never stream AI text after a red flag.

### 2.2 — Regenerate Response
**New route:** `POST /chat/message/{id}/regenerate`
**Files:** `ChatSupportController.php`, `routes/web.php`, `chat-support.blade.php`

Logic: Find preceding user message → delete old assistant message + its embeddings → re-run `chat()`. Re-run crisis detection on original message.

### 2.3 — Message Feedback (Thumbs Up/Down)
**Migration:** Add `feedback` (tinyint), `feedback_reason` (text), `feedback_at` (timestamp) to `messages` table
**New route:** `POST /chat/message/{id}/feedback`

After thumbs down, show reason chips: "Not helpful", "Felt impersonal", "Too short", "Didn't understand me"

### 2.4 — Conversation Search
**New route:** `GET /chat/conversations/search?q=...`

LIKE query on `conversations.title` + `messages.content`. Search input in sidebar, debounced 300ms.

### 2.5 — Conversation Export (Markdown / TXT)
**New route:** `GET /chat/conversation/{id}/export?format=md`

Download formatted file. Always append crisis hotlines (1926, 1333) and disclaimer at bottom.

---

## 8. Phase 3 — Differentiating Features

### 3.1 — Student Follow-Up System (New — Mental Health Critical)
**Migration:** New `student_followups` table (user_id, conversation_id, severity, due_at, status)

- Red flag → schedule 24h follow-up
- Yellow flag → schedule 48h follow-up
- On next chat open after a follow-up is due: show gentle check-in message first
- Counselor dashboard shows all pending follow-ups

### 3.2 — Mood Check-In Before Chat
**Migration:** Add `initial_mood` (tinyint 1–5), `initial_mood_label` (varchar 50) to `conversations` table

On "New Chat": show mood selector ("Really Stressed" → "Great") before message input. Mood injected into system prompt for first AI response. Feeds counselor dashboard mood timeline.

**Framing rule:** Friendly labels only. No numeric scales shown to student.

### 3.3 — Conversation Pinning
**Migration:** Add `is_pinned` (boolean, default false) to `conversations` table
**New routes:** `POST /chat/conversation/{id}/pin` and `/unpin`

Pinned conversations appear first in sidebar with a pin badge.

### 3.4 — Dark Mode
**File:** `resources/views/chat-support.blade.php` only

CSS already uses custom properties in `:root`. Add `[data-theme="dark"]` override block. Toggle in Settings → General tab. Persist in `localStorage`.

### 3.5 — Wellness Insight Panel
**Migration:** New `wellness_insights` table (user_id, period_start, period_end, insights JSON, avg_mood, crisis_flags_count)

Weekly scheduled command aggregates moods, conversation topics, crisis flags into insights. Shown in Settings → new "Wellness" tab.

**Framing rule:** Empowering language only. "You've had 3 supportive conversations this week." Never: "You've been more depressed."

---

## 9. Migration Summary

| Migration | Table | Change | Phase |
|-----------|-------|--------|-------|
| `add_feedback_to_messages` | `messages` | `feedback`, `feedback_reason`, `feedback_at` | 2.3 |
| `add_pinned_to_conversations` | `conversations` | `is_pinned` boolean | 3.3 |
| `add_mood_to_conversations` | `conversations` | `initial_mood`, `initial_mood_label` | 3.2 |
| `create_student_followups` | `student_followups` | New table | 3.1 |
| `create_wellness_insights` | `wellness_insights` | New table | 3.5 |

---

## 10. Implementation Order

```
Week 1 — Fix Core Behaviour + RAG Speed (Phase 1)
  Day 1-2:  1.1 System prompt rewrite + config changes (Rules 1-5)
  Day 3:    1.2 RAG speed fixes (embedding reuse, DB limit)
  Day 3:    1.3 Fix dead code bug at line 521
  Day 4:    1.4 Copy button + 1.6 Keyboard shortcuts
  Day 5:    1.5 AI-generated titles + 1.7 Suggested prompts

Week 2 — Crisis & Suicide Handling (Section 5)
  Day 6-7:  Block AI after red flag, add safe hardcoded response
  Day 8-9:  Student follow-up system (migration + logic)
  Day 10:   Follow-up check-in UI in chat frontend

Week 3 — UX Features (Phase 2)
  Day 11:   2.4 Conversation search
  Day 12:   2.5 Conversation export
  Day 13:   2.3 Message feedback (migration)
  Day 14-15: 2.1 Streaming (most complex)

Week 4 — Streaming + Regenerate
  Day 16:   2.2 Regenerate response
  Day 17-18: Test streaming + regenerate end-to-end

Week 5-6 — Phase 3 Differentiators
  3.3 Pinning (easy migration)
  3.4 Dark mode (CSS work)
  3.2 Mood check-in (migration + UI + AI prompt)
  3.5 Wellness insights (migration + scheduled command)
```

---

## 11. Verification Checklist

### Core Behaviour Rules

- [ ] **Rule 1 (Short responses):** Send a complex question; AI responds in 1–3 sentences, no lists
- [ ] **Rule 2 (Ask why first):** In a new conversation, AI's first 3 responses should be clarifying questions only — no advice
- [ ] **Rule 3 (No suggestions without context):** Say "I'm feeling bad" — AI should ask what happened, not suggest breathing exercises
- [ ] **Rule 4 (No past context unprompted):** Start a new conversation; AI should not reference previous sessions. Say "remember when we talked about exams?" — only then should it activate past context
- [ ] **Rule 5 (Speed):** Average response time should be under 3 seconds for a 150-token response

### RAG

- [ ] Embedding API called **once** per message (check logs: should see 1 embedding call, not 2)
- [ ] With 100+ conversations, memory usage of request does not spike (check PHP memory peak)
- [ ] `getConversationSummary()` no longer appears in request logs

### Crisis / Suicide Handling

- [ ] Send a message with "kill myself" — AI should NOT generate a free-form response
- [ ] Safe hard-coded response appears immediately
- [ ] Crisis UI (hotlines, categories) shown
- [ ] `student_followups` row created with `due_at = now() + 24 hours`
- [ ] On next login after 24 hours, follow-up check-in message shown before input
- [ ] Counselor dashboard shows pending follow-up

### Speed

- [ ] Response time with RAG fix vs before: measure with `Log::info('response_time', ...)` timestamps
- [ ] DB query count per request reduced (check Laravel Debugbar or query logs)

### Other Features

- [ ] Copy button: click → paste matches AI message text
- [ ] Shift+Enter adds newline (does not send)
- [ ] Conversation title generated by AI (not first 6 words)
- [ ] Suggested prompts appear below AI message; clicking fills input
- [ ] Streaming: first token appears within ~1 second
- [ ] Regenerate: old message deleted from DB; new response appears
- [ ] Search: typing "exam" in sidebar returns matching conversations
- [ ] Export: download .md file with hotlines at bottom
- [ ] Dark mode persists after page reload

---

## Critical Files Reference

| File | Role |
|------|------|
| `app/Services/AiChatService.php` | Main orchestration, system prompt, response generation |
| `app/Services/RagRetrievalService.php` | RAG retrieval — all speed fixes go here |
| `config/services.php` | Token limits, temperature, history limit |
| `app/Http/Controllers/ChatSupportController.php` | All new API endpoints |
| `routes/web.php` | New routes for stream, regenerate, feedback, search, export, pin |
| `resources/views/chat-support.blade.php` | All frontend UI changes (~3900 lines) |
| `app/Models/Conversation.php` | Add `is_pinned`, `initial_mood` to `$fillable` |
| `app/Models/Message.php` | Add `feedback`, `feedback_reason` to `$fillable` |
