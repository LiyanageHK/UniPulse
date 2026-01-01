<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conversational Support - UniPulse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite / Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <!-- Marked.js for Markdown Rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        body { margin: 0; font-family: 'Figtree', sans-serif; }

        /* Theme Colors */
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: rgba(37, 99, 235, 0.1);
            --secondary: #06b6d4;
            --sidebar-bg: #1e3a5f;
            --sidebar-hover: #2d4a6f;
            --sidebar-active: #3d5a7f;
            --sidebar-border: #3d5a7f;
        }

        /* Markdown Styles for AI Responses */
        .message-content p {
            margin: 0 0 12px 0;
        }

        .message-content p:last-child {
            margin-bottom: 0;
        }

        .message-content strong {
            font-weight: 600;
            color: #111827;
        }

        .message-content em {
            font-style: italic;
        }

        .message-content ul, .message-content ol {
            margin: 8px 0 12px 0;
            padding-left: 24px;
        }

        .message-content li {
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .message-content code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.9em;
            color: var(--primary);
        }

        .message-content pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 12px 0;
        }

        .message-content pre code {
            background: none;
            color: inherit;
            padding: 0;
        }

        .message-content h1, .message-content h2, .message-content h3 {
            margin: 16px 0 8px 0;
            font-weight: 600;
            color: #111827;
        }

        .message-content h1 { font-size: 1.5em; }
        .message-content h2 { font-size: 1.25em; }
        .message-content h3 { font-size: 1.1em; }

        .message-content blockquote {
            border-left: 4px solid var(--primary);
            margin: 12px 0;
            padding: 8px 16px;
            background: #f9fafb;
            color: #4b5563;
        }

        .message-content hr {
            border: none;
            height: 1px;
            background: #e5e7eb;
            margin: 16px 0;
        }

        .message-content a {
            color: var(--primary);
            text-decoration: underline;
        }

        .message-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .message-content th, .message-content td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }

        .message-content th {
            background: #f9fafb;
            font-weight: 600;
        }
        
        /* Layout */
        .chat-page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .chat-container {
            display: flex;
            flex: 1;
            background: #f7f7f8;
            overflow: hidden;
        }

        /* Sidebar */
        .conversation-sidebar {
            width: 300px;
            background: var(--sidebar-bg);
            color: white;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--sidebar-border);
        }

        .sidebar-header {
            padding: 12px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .new-chat-btn {
            width: 100%;
            padding: 12px 16px;
            background: transparent;
            border: 1px solid var(--primary);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .new-chat-btn:hover {
            background: var(--sidebar-hover);
        }

        .sidebar-tabs {
            display: flex;
            margin-top: 12px;
            gap: 4px;
        }

        .sidebar-tab {
            flex: 1;
            padding: 8px 12px;
            background: transparent;
            border: none;
            color: #8e8ea0;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .sidebar-tab:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-tab.active {
            background: var(--primary);
            color: white;
        }

        .action-btn.archive:hover {
            background: #f59e0b;
            color: white;
        }

        .action-btn.restore:hover {
            background: #10b981;
            color: white;
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--sidebar-border);
        }

        .settings-btn {
            width: 100%;
            padding: 10px 16px;
            max-width: 150px;
            background: transparent;
            border: none;
            color: #ececf1;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            transition: background 0.2s;
        }
         .sidebar-settings-btn {
            width: 100%;
            padding: 10px 16px;
            background: transparent;
            border: none;
            color: #ececf1;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .sidebar-settings-btn:hover {
            background: var(--sidebar-hover);
        }

        .settings-icon {
            width: 18px;
            height: 18px;
        }

        .conversation-item {
            padding: 12px;
            margin-bottom: 4px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
            color: #ececf1;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .conversation-item:hover {
            background: var(--sidebar-hover);
        }

        .conversation-item:hover .conversation-actions {
            opacity: 1;
        }

        .conversation-item.active {
            background: var(--sidebar-active);
        }

        .conversation-content {
            flex: 1;
        }

        .conversation-title {
            font-weight: 500;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-meta {
            font-size: 12px;
            color: #8e8ea0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .conversation-actions {
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .action-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: var(--sidebar-hover);
            color: #ececf1;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: var(--primary);
        }

        .action-btn.delete:hover {
            background: #dc2626;
            color: white;
        }

        .action-btn svg {
            width: 14px;
            height: 14px;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .chat-header {
            padding: 16px 24px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #f3f4f6;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
        }

        .logout-btn:hover {
            background: #e5e7eb;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .message {
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
            animation: messageSlideIn 0.3s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        @keyframes messageSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Staggered animation delay for loaded messages */
        .message:nth-child(1) { animation-delay: 0s; }
        .message:nth-child(2) { animation-delay: 0.05s; }
        .message:nth-child(3) { animation-delay: 0.1s; }
        .message:nth-child(4) { animation-delay: 0.15s; }
        .message:nth-child(5) { animation-delay: 0.2s; }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 600;
            font-size: 14px;
        }

        .message.user .message-avatar {
            background: var(--primary);
            color: white;
        }

        .message.assistant .message-avatar {
            background: var(--secondary);
            color: white;
        }

        /* AI Avatar pulse animation */
        .message.assistant.typing .message-avatar {
            animation: avatarPulse 1.5s ease-in-out infinite;
        }

        @keyframes avatarPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(6, 182, 212, 0); }
        }

        .message-content {
            flex: 1;
            line-height: 1.6;
            color: #374151;
        }

        /* Smooth text appearance */
        .message.assistant .message-content {
            animation: contentFadeIn 0.5s ease-out;
        }

        @keyframes contentFadeIn {
            from {
                opacity: 0.5;
            }
            to {
                opacity: 1;
            }
        }

        .message-time {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
            opacity: 0;
            animation: fadeIn 0.3s ease-out 0.2s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .counselor-cards {
            margin-top: 16px;
            padding: 16px;
            background: #eff6ff;
            border-left: 4px solid var(--primary);
            border-radius: 6px;
        }

        .counselor-cards-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .counselor-cards-title svg {
            width: 20px;
            height: 20px;
        }

        .counselor-card {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .counselor-name {
            font-weight: 600;
            color: #111827;
        }

        .counselor-title {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0;
        }

        .counselor-contact {
            font-size: 13px;
            color: #4b5563;
            margin-top: 8px;
        }

        .crisis-resources {
            margin-top: 16px;
            padding: 16px;
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
        }

        .crisis-resources-title {
            font-weight: 600;
            color: #7f1d1d;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .crisis-resources-title svg {
            width: 20px;
            height: 20px;
        }

        .resource-item {
            margin-bottom: 8px;
            font-size: 14px;
            color: #991b1b;
        }

        /* Crisis Category Buttons */
        .crisis-category-buttons {
            margin-top: 16px;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .crisis-category-header {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .crisis-category-header svg {
            color: #2563eb;
        }

        .category-buttons-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .category-btn {
            padding: 12px 16px;
            background: #f8fafc;
            color: #1e40af;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            text-align: left;
        }

        .category-btn:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        /* Crisis Hotlines */
        .crisis-hotlines {
            margin-top: 12px;
            padding: 16px;
            background: #fff;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 8px;
        }

        .crisis-hotlines-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .crisis-hotlines-title svg {
            color: #dc2626;
        }

        .hotlines-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .hotline-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .hotline-number {
            font-weight: 600;
            color: #dc2626;
            background: #fef2f2;
            padding: 4px 10px;
            border-radius: 4px;
            font-family: monospace;
        }

        .hotline-name {
            color: #374151;
        }

        /* Escalation Message (YELLOW) */
        .crisis-escalation {
            margin-top: 16px;
            padding: 16px;
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border: 1px solid #fde047;
            border-radius: 12px;
        }

        .escalation-message {
            font-size: 14px;
            color: #854d0e;
            margin-bottom: 12px;
        }

        .escalation-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-yes {
            padding: 10px 16px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-yes:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .btn-no {
            padding: 10px 16px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-no:hover {
            background: #e5e7eb;
        }

        /* Support Offer (BLUE) */
        .crisis-support-offer {
            margin-top: 16px;
            padding: 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
        }

        .support-offer-message {
            font-size: 14px;
            color: #1e40af;
            margin-bottom: 10px;
        }

        .support-offer-btn {
            padding: 8px 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .support-offer-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Counselor Cards in Chat */
        .counselor-cards-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .counselor-card-chat {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .counselor-card-chat .counselor-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .counselor-card-chat .counselor-title {
            color: #2563eb;
            font-size: 12px;
            margin-top: 2px;
        }

        .counselor-card-chat .counselor-location {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .input-container {
            padding: 16px 0;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .input-form {
            display: flex;
            gap: 12px;
            width: 100%;
            padding: 0 16px;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            resize: none;
            font-family: inherit;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .send-btn {
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .send-btn:hover {
            background: var(--primary-hover);
        }

        .send-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .send-btn svg {
            width: 18px;
            height: 18px;
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #111827;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 24px;
        }

        .example-prompts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            max-width: 700px;
            width: 100%;
        }

        .example-prompt {
            padding: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .example-prompt:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .example-prompt-title {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        /* Settings Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .modal-close:hover {
            color: #111827;
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            max-height: 60vh;
        }

        .settings-section {
            margin-bottom: 24px;
        }

        .settings-section:last-child {
            margin-bottom: 0;
        }

        .settings-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .settings-item:last-child {
            margin-bottom: 0;
        }

        .settings-item-info {
            flex: 1;
        }

        .settings-item-label {
            font-weight: 500;
            color: #111827;
            margin-bottom: 2px;
        }

        .settings-item-desc {
            font-size: 13px;
            color: #6b7280;
        }

        .settings-item-action {
            margin-left: 16px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .memory-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 12px;
        }

        .memory-stat {
            background: #f0f9ff;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        .memory-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0369a1;
        }

        .memory-stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .memory-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 12px;
        }

        .memory-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 12px;
            background: #f9fafb;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .memory-category {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--primary);
            font-weight: 600;
        }

        .memory-value {
            color: #374151;
            margin-top: 2px;
        }

        .memory-delete {
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            font-size: 16px;
            padding: 0 4px;
        }

        /* Counselor Directory Styles */
        .counselor-category {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .counselor-category-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            cursor: pointer;
            transition: background 0.2s;
        }

        .counselor-category-header:hover {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        }

        .counselor-category-title {
            flex: 1;
            font-weight: 600;
            color: #1e40af;
            font-size: 14px;
        }

        .counselor-category-count {
            font-size: 12px;
            color: #6b7280;
            background: #fff;
            padding: 2px 8px;
            border-radius: 12px;
        }

        .counselor-list {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #fafafa;
        }

        .counselor-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }

        .counselor-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .counselor-title {
            color: #2563eb;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .counselor-location {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .counselor-contact {
            color: #374151;
            font-size: 12px;
            margin-top: 4px;
        }

        .counselor-online-badge {
            display: inline-block;
            background: #dcfce7;
            color: #16a34a;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            margin-top: 8px;
            font-weight: 500;
        }

        /* Stat Cards for Settings */
        .stat-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            gap: 8px;
        }

        .star-rating .star {
            font-size: 28px;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        .star-rating .star:hover,
        .star-rating .star.active {
            color: #fbbf24;
            transform: scale(1.15);
        }

        .star-rating .star.active {
            color: #f59e0b;
        }

        /* ChatGPT-style Settings Modal with Blue Gradient Theme */
        .settings-modal {
            max-width: 700px !important;
            width: 90vw;
            max-height: 85vh;
            overflow: hidden;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .settings-modal .modal-header {
            background: linear-gradient(135deg, rgba(26, 53, 84, 0.9) 0%, rgba(13, 31, 51, 0.9) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            padding: 16px 20px;
        }

        .settings-modal .modal-title {
            color: #fff;
            font-weight: 600;
        }

        .settings-modal .modal-close {
            color: rgba(255, 255, 255, 0.7);
        }

        .settings-modal .modal-close:hover {
            color: #fff;
        }

        .settings-container {
            display: flex;
            min-height: 450px;
            max-height: calc(85vh - 60px);
        }

        .settings-tabs {
            width: 200px;
            background: linear-gradient(180deg, #1e3a5f 0%, #0f2744 100%);
            border-right: 1px solid #1a3554;
            padding: 16px 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
        }

        .settings-tab {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            text-align: left;
            transition: all 0.2s;
        }

        .settings-tab svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .settings-tab:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .settings-tab.active {
            background: rgba(59, 130, 246, 0.3);
            color: #fff;
            border: 1px solid rgba(59, 130, 246, 0.5);
        }

        .settings-content {
            flex: 1;
            padding: 24px 28px;
            overflow-y: auto;
            background: linear-gradient(135deg, #f0f7ff 0%, #e8f4ff 100%);
        }

        .settings-panel {
            display: none;
        }

        .settings-panel.active {
            display: block;
        }

        .settings-panel-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e3a5f;
            margin: 0 0 20px 0;
        }

        .settings-group {
            margin-bottom: 28px;
        }

        .settings-group:last-child {
            margin-bottom: 0;
        }

        .settings-group-title {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .settings-group-desc {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .settings-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .settings-row:last-child {
            border-bottom: none;
        }

        .settings-row-info {
            flex: 1;
        }

        .settings-row-label {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }

        .settings-row-desc {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }

        .settings-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 20px;
            min-width: 120px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: 1px solid rgba(59, 130, 246, 0.5);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            flex-shrink: 0;
        }

        .settings-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .settings-btn.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-color: rgba(239, 68, 68, 0.5);
            color: #fff;
        }

        .settings-btn.danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .settings-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }

        .settings-actions .settings-btn {
            width: 100%;
            max-width: 100%;
            min-width: auto;
            padding: 12px 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .stat-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #1e40af;
        }

        .stat-text {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .memory-count {
            font-size: 14px;
            color: #1e3a5f;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .memory-count span {
            color: #1e40af;
            font-weight: 700;
        }

        .memory-list-container {
            max-height: 200px;
            overflow-y: auto;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 12px;
        }

        .memory-loading {
            text-align: center;
            color: #6b7280;
            padding: 20px;
        }

        .about-info {
            text-align: center;
            padding: 20px 0;
        }

        .about-logo {
            margin-bottom: 16px;
        }

        .about-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 8px 0;
        }

        .about-desc {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 12px 0;
        }

        .about-version {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Responsive for smaller screens */
        @media (max-width: 640px) {
            .settings-modal {
                width: 95vw;
                max-height: 90vh;
            }
            
            .settings-container {
                flex-direction: column;
            }
            
            .settings-tabs {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding: 12px;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .settings-tab {
                flex-shrink: 0;
                padding: 10px 14px;
            }
            
            .settings-content {
                padding: 20px 16px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
        }

        /* Success Toast Notification */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: toastSlideIn 0.4s ease-out forwards;
            min-width: 280px;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .toast-icon.success {
            color: #10b981;
        }

        .toast-icon.error {
            color: #ef4444;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        .toast-message {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }

        .toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            line-height: 1;
        }

        .toast-close:hover {
            color: #374151;
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast.hiding {
            animation: toastSlideOut 0.3s ease-in forwards;
        }

        @keyframes toastSlideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }

        /* Data Control Tabs */
        .data-control-tab.active {
            color: #2563eb !important;
            border-bottom-color: #2563eb !important;
        }

        .data-control-tab:hover {
            color: #2563eb;
        }

        .data-control-panel {
            display: none;
        }

        /* Zen Zone Styles */
        .zen-zone-container {
            display: flex;
            min-height: 400px;
        }

        .zen-tabs {
            width: 180px;
            background: linear-gradient(180deg, #1e3a5f 0%, #0f2744 100%);
            border-right: 1px solid #1a3554;
            padding: 16px 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .zen-tab {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            text-align: left;
            transition: all 0.2s;
        }

        .zen-tab:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .zen-tab.active {
            background: rgba(59, 130, 246, 0.3);
            color: #fff;
            border: 1px solid rgba(59, 130, 246, 0.5);
        }

        .zen-content {
            flex: 1;
            background: #fff;
            padding: 24px;
            overflow-y: auto;
        }

        .zen-panel {
            display: none;
            height: 100%;
        }

        .zen-panel.active {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Bubble Pop Game */
        .bubble-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 12px;
            border: 1px solid #bae6fd;
        }

        .bubble {
            width: 50px;
            height: 50px;
            background: radial-gradient(circle at 30% 30%, #60a5fa, #2563eb);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2), inset -2px -2px 5px rgba(0,0,0,0.2);
            transition: transform 0.1s;
            position: relative;
        }

        .bubble:active {
            transform: scale(0.9);
        }

        .bubble::after {
            content: '';
            position: absolute;
            top: 20%;
            left: 20%;
            width: 12px;
            height: 6px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            transform: rotate(-45deg);
        }

        .bubble.popped {
            background: radial-gradient(circle at 30% 30%, #bfdbfe, #93c5fd);
            transform: scale(0.9);
            box-shadow: inset 2px 2px 5px rgba(0,0,0,0.1);
            animation: popCheck 0.3s ease-out;
        }

        .bubble.popped::after {
            display: none;
        }
        
        @keyframes popCheck {
            0% { transform: scale(0.9); }
            50% { transform: scale(1.1); }
            100% { transform: scale(0.9); }
        }

        /* Breathing Exercise */
        .breathing-circle {
            width: 150px;
            height: 150px;
            background: #60a5fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(96, 165, 250, 0.5);
            animation: breathe 8s infinite ease-in-out;
            margin: 40px 0;
            position: relative;
        }

        .breathing-instruction {
            font-size: 20px;
            color: #1e3a5f;
            font-weight: 600;
            margin-top: 20px;
            min-height: 30px;
        }

        .breathing-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border: 2px solid #93c5fd;
            border-radius: 50%;
            animation: breatheRing 8s infinite ease-in-out;
            z-index: -1;
        }

        @keyframes breathe {
            0%, 100% { transform: scale(1); background: #60a5fa; }
            40% { transform: scale(1.5); background: #3b82f6; }
            60% { transform: scale(1.5); background: #3b82f6; } /* Hold */
        }
        
        @keyframes breatheRing {
             0%, 100% { width: 150px; height: 150px; opacity: 0.5; }
             40% { width: 250px; height: 250px; opacity: 0.2; }
             60% { width: 250px; height: 250px; opacity: 0.2; }
        }

        .data-control-panel.active {
            display: block;
        }

        .relax-btn {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            margin-left: 8px;
            transition: background-color 0.2s;
        }

        .relax-btn:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="chat-page-wrapper">
        @include('layouts.navigation')
        
        <div class="chat-container">
            <!-- Sidebar -->
            <div class="conversation-sidebar">
                <div class="sidebar-header">
                    <button class="new-chat-btn" onclick="startNewConversation()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>New Chat</span>
                    </button>
                    <div class="sidebar-tabs">
                        <button class="sidebar-tab active" id="activeTab" onclick="switchTab('active')">Active</button>
                        <button class="sidebar-tab" id="archivedTab" onclick="switchTab('archived')">Archived</button>
                    </div>
                </div>
                <div class="conversation-list" id="conversationList">
                    <!-- Conversations will be loaded here -->
                </div>
                <div class="sidebar-footer">
                    <button class="sidebar-settings-btn" onclick="openCounselorsModal()">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <span>Counselors</span>
                    </button>
                    <button class="sidebar-settings-btn" onclick="openZenZoneModal()">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                        <span>Relax Your Mind</span>
                    </button>
                    <button class="sidebar-settings-btn" onclick="openSettings()">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span>Settings</span>
                    </button>
                </div>
            </div>

        <!-- Main Chat Area -->
        <div class="chat-main">

            <div class="messages-container" id="messagesContainer">
                <div class="empty-state" id="emptyState">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 32px; height: 32px; display: inline; vertical-align: middle; margin-right: 8px; color: var(--primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 10-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 013.15 0v1.5m-3.15 0l-.075 5.925m3.075.75a2.25 2.25 0 01-2.25 2.25H6.75a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0022.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 007.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75z" />
                        </svg>
                        Hello, {{ Auth::user()->name }}!
                    </h2>
                    <p>How can I support you today?</p>
                    <div class="example-prompts">
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m feeling stressed about upcoming exams')">
                            <div class="example-prompt-title">Academic Stress</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I need help managing my time better')">
                            <div class="example-prompt-title">Time Management</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m having trouble sleeping')">
                            <div class="example-prompt-title">Sleep Issues</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I want to talk about my career goals')">
                            <div class="example-prompt-title">Career Guidance</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m feeling unmotivated to study')">
                            <div class="example-prompt-title">Study Motivation</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m finding it hard to make friends at university')">
                            <div class="example-prompt-title">Social Connections</div>
                        </div>
                    </div>
                </div>

                <!-- Messages will appear here -->
                <div id="messagesContent" style="display: none;"></div>

                <div class="typing-indicator" id="typingIndicator">
                    <div class="message-avatar" style="background: var(--secondary); color: white;">AI</div>
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>

            <div class="input-container">
                <form class="input-form" onsubmit="sendMessage(event)">
                    <textarea 
                        class="message-input" 
                        id="messageInput" 
                        placeholder="Type your message here..." 
                        rows="1"
                        onkeydown="handleKeyPress(event)"
                    ></textarea>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Settings Modal (ChatGPT-style Tabbed Layout) -->
    <div class="modal-overlay" id="settingsModal" onclick="closeSettingsOnOverlay(event)">
        <div class="modal settings-modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title">Settings</div>
                <button class="modal-close" onclick="closeSettings()">&times;</button>
            </div>
            <div class="settings-container">
                <!-- Settings Tabs (Left Side) -->
                <div class="settings-tabs">
                    <button class="settings-tab active" onclick="switchSettingsTab('general')" data-tab="general">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        General
                    </button>
                    <button class="settings-tab" onclick="switchSettingsTab('personalization')" data-tab="personalization">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>
                        Personalization
                    </button>
                    <button class="settings-tab" onclick="switchSettingsTab('data')" data-tab="data">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                        Data Controls
                    </button>
                </div>

                <!-- Settings Content (Right Side) -->
                <div class="settings-content">
                    <!-- General Tab -->
                    <div class="settings-panel active" id="panel-general">
                        <h3 class="settings-panel-title">General</h3>
                        
                        <div class="settings-group">
                            <div class="settings-group-title">Chat Statistics</div>
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <span class="stat-number" id="statTotalConversations">-</span>
                                    <span class="stat-text">Total Chats</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number" id="statActiveConversations">-</span>
                                    <span class="stat-text">Active</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number" id="statArchivedConversations">-</span>
                                    <span class="stat-text">Archived</span>
                                </div>
                            </div>
                        </div>

                        <div class="settings-group">
                            <div class="settings-group-title">Account</div>
                            <div class="settings-row">
                                <div class="settings-row-info">
                                    <div class="settings-row-label">{{ Auth::user()->name }}</div>
                                    <div class="settings-row-desc">{{ Auth::user()->email }}</div>
                                </div>
                                <a href="{{ route('profile.show') }}" class="settings-btn">View Profile</a>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-info">
                                    <div class="settings-row-label">Dashboard</div>
                                    <div class="settings-row-desc">View your KPIs and insights</div>
                                </div>
                                <a href="{{ route('dashboard') }}" class="settings-btn">Open Dashboard</a>
                            </div>
                        </div>
                    </div>

                    <!-- Personalization Tab (Memory) -->
                    <div class="settings-panel" id="panel-personalization">
                        <h3 class="settings-panel-title">Personalization</h3>
                        
                        <div class="settings-group">
                            <div class="settings-group-title">AI Memory</div>
                            <p class="settings-group-desc">The AI remembers important details about you to personalize conversations.</p>
                            
                            <div class="memory-count">
                                <span id="totalMemories">0</span> memories stored
                            </div>
                            
                            <div class="memory-list-container" id="memoryList">
                                <div class="memory-loading">Loading memories...</div>
                            </div>
                            
                            <div class="settings-actions">
                                <button class="settings-btn" onclick="loadMemories()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Refresh
                                </button>
                                <button class="settings-btn danger" onclick="showClearMemoryModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Clear All Memories
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Data Controls Tab -->
                    <div class="settings-panel" id="panel-data">
                        <h3 class="settings-panel-title">Data Controls</h3>
                        
                        <!-- Sub-tabs for Active/Archived Chats -->
                        <div class="data-control-tabs" style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
                            <button class="data-control-tab active" onclick="switchDataControlTab('active')" data-tab="active" style="background: none; border: none; padding: 12px 24px; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;">
                                Active Chats
                            </button>
                            <button class="data-control-tab" onclick="switchDataControlTab('archived')" data-tab="archived" style="background: none; border: none; padding: 12px 24px; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;">
                                Archived Chats
                            </button>
                        </div>

                        <!-- Active Chats Panel -->
                        <div class="data-control-panel active" id="data-panel-active">
                            <div class="settings-group">
                                <div class="settings-group-title">CHAT HISTORY</div>
                                <div class="settings-row">
                                    <div class="settings-row-info">
                                        <div class="settings-row-label">Archive All Chats</div>
                                        <div class="settings-row-desc">Move all active conversations to archive</div>
                                    </div>
                                    <button class="settings-btn" onclick="showArchiveAllModal()">Archive All</button>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-row-info">
                                        <div class="settings-row-label">Delete All Active Chats</div>
                                        <div class="settings-row-desc">Permanently delete all active conversations</div>
                                    </div>
                                    <button class="settings-btn danger" onclick="showDeleteActiveModal()">Delete</button>
                                </div>
                            </div>
                        </div>

                        <!-- Archived Chats Panel -->
                        <div class="data-control-panel" id="data-panel-archived">
                            <div class="settings-group">
                                <div class="settings-group-title">CHAT HISTORY</div>
                                <div class="settings-row">
                                    <div class="settings-row-info">
                                        <div class="settings-row-label">Unarchive All Chats</div>
                                        <div class="settings-row-desc">Move all archived conversations back to active</div>
                                    </div>
                                    <button class="settings-btn" onclick="showUnarchiveAllModal()">Unarchive All</button>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-row-info">
                                        <div class="settings-row-label">Delete All Archived</div>
                                        <div class="settings-row-desc">Permanently delete all archived conversations</div>
                                    </div>
                                    <button class="settings-btn danger" onclick="showDeleteArchivedModal()">Delete</button>
                                </div>
                            </div>
                        </div>

                        <div class="settings-group">
                            <div class="settings-group-title">Privacy</div>
                            <p class="settings-group-desc">Your data is encrypted and stored securely. We never share your personal information or conversation history with third parties.</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Counselors Modal -->
    <div class="modal-overlay" id="counselorsModal" onclick="closeCounselorsModal(event)">
        <div class="modal settings-modal" onclick="event.stopPropagation()" style="max-width: 600px; max-height: 80vh; background: white; border: none;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    Professional Counselors
                </div>
                <button class="modal-close" onclick="closeCounselorsModal(event)">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: 60vh; padding: 20px;">
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 16px;">
                    Browse our directory of qualified counselors and mental health professionals by category.
                </p>
                <div id="counselorCategoriesModal" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Categories will be loaded here -->
                    <div style="text-align: center; padding: 20px; color: #6b7280;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 32px; height: 32px; margin: 0 auto 8px; animation: spin 1s linear infinite;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Loading counselors...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zen Zone Modal -->
    <div class="modal-overlay" id="zenZoneModal" onclick="closeZenZoneModal(event)">
        <div class="modal settings-modal" onclick="event.stopPropagation()" style="max-width: 700px; max-height: 85vh;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                    </svg>
                    Relax Your Mind
                </div>
                <button class="modal-close" onclick="closeZenZoneModal(event)">&times;</button>
            </div>
            <div class="zen-zone-container">
                <!-- Zen Tabs (Left Side) -->
                <div class="zen-tabs">
                    <button class="zen-tab active" onclick="switchZenTab('bubble')" data-zen-tab="bubble">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />

                        </svg>
                        Stress Poppers
                    </button>
                    <button class="zen-tab" onclick="switchZenTab('breathe')" data-zen-tab="breathe">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        Breathe
                    </button>
                </div>

                <!-- Zen Content (Right Side) -->
                <div class="zen-content">
                    <!-- Bubble Pop Tab -->
                    <div class="zen-panel active" id="zen-panel-bubble">
                        <h3 style="margin-bottom: 20px; color: #1f2937;">Pop the stress away!</h3>
                        <div class="bubble-grid" id="bubbleGrid">
                            <!-- Bubbles generated by JS -->
                        </div>
                         <button class="settings-btn" onclick="resetBubbles()" style="margin-top: 20px;">
                            Reset Bubbles
                        </button>
                    </div>

                    <!-- Breathe Tab -->
                    <div class="zen-panel" id="zen-panel-breathe">
                        <div class="breathing-circle">
                             <div class="breathing-ring"></div>
                             <span id="breathingText">Inhale</span>
                        </div>
                        <div class="breathing-instruction" id="breathingInstruction">
                            Breathe in deeply...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive All Confirmation Modal -->
    <div class="modal-overlay" id="archiveAllModal" onclick="closeArchiveAllModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #2563eb;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Archive All Chats
                </div>
                <button class="modal-close" onclick="closeArchiveAllModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to archive <strong>all active conversations</strong>?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    All your current chats will be moved to the archive. You can restore them later from the Archived tab.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeArchiveAllModal(event)">Cancel</button>
                    <button class="btn btn-primary" onclick="confirmArchiveAll()" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                        Yes, Archive All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Archived Confirmation Modal -->
    <div class="modal-overlay" id="deleteArchivedModal" onclick="closeDeleteArchivedModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete All Archived
                </div>
                <button class="modal-close" onclick="closeDeleteArchivedModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to <strong>permanently delete ALL archived conversations</strong>?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    This action <strong>cannot be undone</strong>. All archived chats will be permanently removed.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeDeleteArchivedModal(event)">Cancel</button>
                    <button class="btn btn-danger" onclick="confirmDeleteArchived()" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Yes, Delete All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unarchive All Confirmation Modal -->
    <div class="modal-overlay" id="unarchiveAllModal" onclick="closeUnarchiveAllModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0 3-3m-3 3-3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Unarchive All Chats
                </div>
                <button class="modal-close" onclick="closeUnarchiveAllModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to restore <strong>all archived conversations</strong>?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    All archived chats will be moved back to your active conversations.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeUnarchiveAllModal(event)">Cancel</button>
                    <button class="btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border: 1px solid rgba(16, 185, 129, 0.5);" onclick="confirmUnarchiveAll()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px; flex-shrink: 0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0 3-3m-3 3-3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                        Yes, Unarchive All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Active Confirmation Modal -->
    <div class="modal-overlay" id="deleteActiveModal" onclick="closeDeleteActiveModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete All Active Chats
                </div>
                <button class="modal-close" onclick="closeDeleteActiveModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to <strong>permanently delete ALL active conversations</strong>?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    This action <strong>cannot be undone</strong>. All your current active chats will be permanently removed.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeDeleteActiveModal(event)">Cancel</button>
                    <button class="btn btn-danger" onclick="confirmDeleteActive()" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Yes, Delete All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Memory Confirmation Modal -->
    <div class="modal-overlay" id="clearMemoryModal" onclick="closeClearMemoryModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    Clear All Memories
                </div>
                <button class="modal-close" onclick="closeClearMemoryModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to clear <strong>ALL memories</strong>?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    This will remove everything the AI has learned about you. This action <strong>cannot be undone</strong>.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeClearMemoryModal(event)">Cancel</button>
                    <button class="btn btn-danger" onclick="confirmClearMemories()" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Yes, Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Single Memory Confirmation Modal -->
    <div class="modal-overlay" id="deleteMemoryModal" onclick="closeDeleteMemoryModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete Memory
                </div>
                <button class="modal-close" onclick="closeDeleteMemoryModal(event)">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;">
                    Are you sure you want to delete this memory?
                </p>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 16px;">
                    The AI will no longer use this information to personalize your conversations.
                </p>
                <input type="hidden" id="deleteMemoryId">
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="closeDeleteMemoryModal(event)">Cancel</button>
                    <button class="btn btn-danger" onclick="confirmDeleteMemory()" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rename Modal -->
    <div class="modal-overlay" id="renameModal" onclick="closeRenameModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                    Rename Conversation
                </div>
                <button class="modal-close" onclick="closeRenameModalBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="renameInput" class="message-input" style="width: 100%; margin-bottom: 16px;" placeholder="Enter new title...">
                <input type="hidden" id="renameConvId">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeRenameModalBtn()">Cancel</button>
                    <button class="btn btn-primary" onclick="submitRename()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal" onclick="closeDeleteModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete Conversation
                </div>
                <button class="modal-close" onclick="closeDeleteModalBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #374151; margin-bottom: 16px;">Are you sure you want to delete this conversation? All messages will be permanently removed.</p>
                <p style="color: #dc2626; font-weight: 500; margin-bottom: 16px;">This cannot be undone.</p>
                <input type="hidden" id="deleteConvId">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeDeleteModalBtn()">Cancel</button>
                    <button class="btn btn-danger" onclick="submitDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal-overlay" id="archiveModal" onclick="closeArchiveModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #f59e0b;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Archive Conversation
                </div>
                <button class="modal-close" onclick="closeArchiveModalBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #374151; margin-bottom: 16px;">Move this conversation to your archive? You can restore it anytime from the Archived tab.</p>
                <input type="hidden" id="archiveConvId">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeArchiveModalBtn()">Cancel</button>
                    <button class="btn" style="background: #f59e0b; color: white;" onclick="submitArchive()">Archive</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unarchive Confirmation Modal -->
    <div class="modal-overlay" id="unarchiveModal" onclick="closeUnarchiveModal(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0 3-3m-3 3-3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Restore Conversation
                </div>
                <button class="modal-close" onclick="closeUnarchiveModalBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #374151; margin-bottom: 16px;">Restore this conversation to your active chats? You can continue the conversation after restoring.</p>
                <input type="hidden" id="unarchiveConvId">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeUnarchiveModalBtn()">Cancel</button>
                    <button class="btn" style="background: #10b981; color: white;" onclick="submitUnarchive()">Restore</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        let currentConversationId = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Auto-resize textarea
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });

        // Show toast notification
        function showToast(type, title, message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const iconSvg = type === 'success' 
                ? `<svg class="toast-icon success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                   </svg>`
                : `<svg class="toast-icon error" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                   </svg>`;
            
            toast.innerHTML = `
                ${iconSvg}
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="closeToast(this)">&times;</button>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Close toast manually
        function closeToast(btn) {
            const toast = btn.closest('.toast');
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }

        // Handle Enter key
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(event);
            }
        }

        // Load conversations on page load
        window.addEventListener('DOMContentLoaded', () => {
            // Just load the conversation list in sidebar, don't open any conversation
            loadConversations();
            
            // Make sure we start with empty state (no conversation loaded)
            currentConversationId = null;
            document.getElementById('emptyState').style.display = 'flex';
            document.getElementById('messagesContent').style.display = 'none';
        });

        let currentTab = 'active';

        // Switch between Active and Archived tabs
        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('activeTab').classList.toggle('active', tab === 'active');
            document.getElementById('archivedTab').classList.toggle('active', tab === 'archived');
            loadConversations();
        }

        // Load conversation list
        async function loadConversations() {
            try {
                const response = await fetch(`/chat/conversations?status=${currentTab}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                const listContainer = document.getElementById('conversationList');
                if (data.success && data.conversations.length > 0) {
                    listContainer.innerHTML = data.conversations.map(conv => `
                        <div class="conversation-item ${conv.id === currentConversationId ? 'active' : ''}" 
                             data-conversation-id="${conv.id}">
                            <div class="conversation-content" onclick="loadConversation(${conv.id})">
                                <div class="conversation-title">${conv.title}</div>
                                <div class="conversation-meta">
                                    <span class="conversation-date">${formatDate(conv.last_message_at || conv.created_at)}</span>
                                    <div class="conversation-actions">
                                        ${currentTab === 'active' ? `
                                        <button class="action-btn archive" onclick="event.stopPropagation(); archiveConversation(${conv.id})" title="Archive">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        </button>
                                        ` : `
                                        <button class="action-btn restore" onclick="event.stopPropagation(); unarchiveConversation(${conv.id})" title="Restore">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0 3-3m-3 3-3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        </button>
                                        `}
                                        <button class="action-btn" onclick="event.stopPropagation(); renameConversation(${conv.id}, '${conv.title.replace(/'/g, "\\'")}')" title="Rename">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button class="action-btn delete" onclick="event.stopPropagation(); deleteConversation(${conv.id})" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    const emptyMessage = currentTab === 'active' ? 'No conversations yet' : 'No archived conversations';
                    listContainer.innerHTML = `<div style="padding: 16px; color: #8e8ea0; font-size: 13px; text-align: center;">${emptyMessage}</div>`;
                }
            } catch (error) {
                console.error('Failed to load conversations:', error);
            }
        }

        // Archive conversation - open modal
        function archiveConversation(convId) {
            document.getElementById('archiveConvId').value = convId;
            document.getElementById('archiveModal').classList.add('active');
        }

        // Close archive modal on overlay click
        function closeArchiveModal(event) {
            if (event.target.id === 'archiveModal') {
                closeArchiveModalBtn();
            }
        }

        // Close archive modal
        function closeArchiveModalBtn() {
            document.getElementById('archiveModal').classList.remove('active');
        }

        // Submit archive
        async function submitArchive() {
            const convId = document.getElementById('archiveConvId').value;
            try {
                const response = await fetch(`/chat/conversation/${convId}/archive`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    closeArchiveModalBtn();
                    if (currentConversationId == convId) {
                        startNewConversation();
                    }
                    await loadConversations();
                    showToast('success', 'Archived Successfully', 'Your conversation has been moved to archive.');
                } else {
                    showToast('error', 'Archive Failed', data.error || 'Unknown error occurred.');
                }
            } catch (error) {
                console.error('Archive error:', error);
                showToast('error', 'Archive Failed', 'Something went wrong. Please try again.');
            }
        }

        // Unarchive conversation - open modal
        function unarchiveConversation(convId) {
            document.getElementById('unarchiveConvId').value = convId;
            document.getElementById('unarchiveModal').classList.add('active');
        }

        // Close unarchive modal on overlay click
        function closeUnarchiveModal(event) {
            if (event.target.id === 'unarchiveModal') {
                closeUnarchiveModalBtn();
            }
        }

        // Close unarchive modal
        function closeUnarchiveModalBtn() {
            document.getElementById('unarchiveModal').classList.remove('active');
        }

        // Submit unarchive
        async function submitUnarchive() {
            const convId = document.getElementById('unarchiveConvId').value;
            try {
                const response = await fetch(`/chat/conversation/${convId}/unarchive`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    closeUnarchiveModalBtn();
                    await loadConversations();
                    showToast('success', 'Restored Successfully', 'Your conversation is now active again.');
                } else {
                    showToast('error', 'Restore Failed', data.error || 'Unknown error occurred.');
                }
            } catch (error) {
                console.error('Unarchive error:', error);
                showToast('error', 'Restore Failed', 'Something went wrong. Please try again.');
            }
        }

        // Rename conversation - open modal
        function renameConversation(convId, currentTitle) {
            document.getElementById('renameConvId').value = convId;
            document.getElementById('renameInput').value = currentTitle;
            document.getElementById('renameModal').classList.add('active');
            document.getElementById('renameInput').focus();
        }

        // Close rename modal on overlay click
        function closeRenameModal(event) {
            if (event.target.id === 'renameModal') {
                closeRenameModalBtn();
            }
        }

        // Close rename modal
        function closeRenameModalBtn() {
            document.getElementById('renameModal').classList.remove('active');
        }

        // Submit rename
        async function submitRename() {
            const convId = document.getElementById('renameConvId').value;
            const newTitle = document.getElementById('renameInput').value.trim();
            
            if (!newTitle) {
                return;
            }

            try {
                const response = await fetch(`/chat/conversation/${convId}/rename`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ title: newTitle })
                });

                const data = await response.json();

                if (data.success) {
                    closeRenameModalBtn();
                    await loadConversations();
                    showToast('success', 'Renamed Successfully', 'Your conversation has been renamed.');
                    
                    if (currentConversationId == convId) {
                        document.querySelectorAll('.conversation-item').forEach(item => {
                            if (item.dataset.conversationId == convId) {
                                item.classList.add('active');
                            }
                        });
                    }
                } else {
                    showToast('error', 'Rename Failed', data.error || 'Unknown error occurred.');
                }
            } catch (error) {
                console.error('Rename error:', error);
                showToast('error', 'Rename Failed', 'Something went wrong. Please try again.');
            }
        }

        // Delete conversation - open modal
        function deleteConversation(convId) {
            document.getElementById('deleteConvId').value = convId;
            document.getElementById('deleteModal').classList.add('active');
        }

        // Close delete modal on overlay click
        function closeDeleteModal(event) {
            if (event.target.id === 'deleteModal') {
                closeDeleteModalBtn();
            }
        }

        // Close delete modal
        function closeDeleteModalBtn() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Submit delete
        async function submitDelete() {
            const convId = document.getElementById('deleteConvId').value;

            try {
                const response = await fetch(`/chat/conversation/${convId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    closeDeleteModalBtn();
                    
                    if (currentConversationId == convId) {
                        startNewConversation();
                    }
                    
                    await loadConversations();
                    showToast('success', 'Deleted Successfully', 'Your conversation has been removed.');
                } else {
                    showToast('error', 'Delete Failed', data.error || 'Unknown error occurred.');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('error', 'Delete Failed', 'Something went wrong. Please try again.');
            }
        }

        // Start new conversation
        function startNewConversation() {
            // Reset conversation ID - next message will create new conversation
            currentConversationId = null;
            console.log('New Chat clicked - conversation ID reset');
            
            // Show empty state
            document.getElementById('emptyState').style.display = 'flex';
            document.getElementById('messagesContent').style.display = 'none';
            document.getElementById('messagesContent').innerHTML = '';
            document.getElementById('messageInput').value = '';
            
            // Remove active class from all conversations
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Focus on input
            document.getElementById('messageInput').focus();
        }

        // Send example message
        function sendExampleMessage(message) {
            document.getElementById('messageInput').value = message;
            sendMessage(new Event('submit'));
        }

        // Send message
        async function sendMessage(event) {
            event.preventDefault();

            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            // Hide empty state
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('messagesContent').style.display = 'block';

            // Add user message to UI
            addMessageToUI('user', message, new Date());

            // Clear input
            input.value = '';
            input.style.height = 'auto';

            // Show typing indicator
            document.getElementById('typingIndicator').classList.add('active');

            try {
                let response;
                
                console.log('Sending message. Current conversation ID:', currentConversationId); // Debug
                
                if (currentConversationId) {
                    // Send to existing conversation
                    console.log('Sending to existing conversation:', currentConversationId); // Debug
                    response = await fetch('/chat/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            conversation_id: currentConversationId,
                            message: message
                        })
                    });
                } else {
                    // Start new conversation
                    console.log('Starting new conversation'); // Debug
                    response = await fetch('/chat/conversation/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            initial_message: message,
                            topic: 'General Support'
                        })
                    });
                }

                const data = await response.json();

                if (data.success) {
                    // Set conversation ID if new
                    if (data.conversation) {
                        currentConversationId = data.conversation.id;
                        console.log('Set conversation ID to:', currentConversationId); // Debug
                    }

                    // Add AI response to UI
                    addMessageToUI('assistant', data.response.message, new Date());

                    // Handle crisis response based on severity type
                    if (data.response.crisis_response) {
                        const crisisResponse = data.response.crisis_response;
                        
                        if (crisisResponse.type === 'crisis_red') {
                            // RED: Show category buttons + hotlines (no duplicate resources)
                            addCategoryButtons(crisisResponse.categories);
                            addCrisisHotlines(crisisResponse.hotlines);
                        } else if (crisisResponse.type === 'crisis_yellow') {
                            // YELLOW: Show escalation message with support offer
                            addEscalationMessage(crisisResponse);
                        } else if (crisisResponse.type === 'crisis_blue') {
                            // BLUE: Show optional support offer
                            addSupportOffer(crisisResponse);
                        }
                    }

                    // Reload conversation list (but don't change current view)
                    await loadConversations();
                    
                    // Re-highlight current conversation after sidebar refresh
                    document.querySelectorAll('.conversation-item').forEach(item => {
                        item.classList.remove('active');
                        if (item.dataset.conversationId == currentConversationId) {
                            item.classList.add('active');
                        }
                    });
                } else {
                    addMessageToUI('system', 'Error: ' + (data.error || 'Failed to send message'), new Date());
                }
            } catch (error) {
                console.error('Send message error:', error);
                addMessageToUI('system', 'Failed to send message. Please try again.', new Date());
            } finally {
                document.getElementById('typingIndicator').classList.remove('active');
                sendBtn.disabled = false;
                input.focus();
            }
        }

        // Load conversation
        async function loadConversation(convId) {
            currentConversationId = convId;

            try {
                const response = await fetch(`/chat/conversation/${convId}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('emptyState').style.display = 'none';
                    const messagesContent = document.getElementById('messagesContent');
                    messagesContent.style.display = 'block';
                    messagesContent.innerHTML = '';

                    data.messages.forEach(msg => {
                        addMessageToUI(msg.role, msg.content, new Date(msg.created_at));
                        
                        // Restore crisis response UI if present in metadata
                        if (msg.role === 'assistant' && msg.metadata && msg.metadata.crisis_response) {
                            const crisisResponse = msg.metadata.crisis_response;
                            
                            if (crisisResponse.type === 'crisis_red') {
                                addCategoryButtons(crisisResponse.categories);
                                addCrisisHotlines(crisisResponse.hotlines);
                            } else if (crisisResponse.type === 'crisis_yellow') {
                                addEscalationMessage(crisisResponse);
                            } else if (crisisResponse.type === 'crisis_blue') {
                                addSupportOffer(crisisResponse);
                            }
                        }
                    });

                    // Update active conversation in sidebar
                    document.querySelectorAll('.conversation-item').forEach(item => {
                        item.classList.remove('active');
                        if (item.dataset.conversationId == convId) {
                            item.classList.add('active');
                        }
                    });

                    console.log('Loaded conversation:', convId); // Debug
                }
            } catch (error) {
                console.error('Load conversation error:', error);
            }
        }

        // Add message to UI
        function addMessageToUI(role, content, timestamp) {
            const messagesContent = document.getElementById('messagesContent');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;

            const avatar = role === 'user' ? '{{ substr(Auth::user()->name, 0, 1) }}' : 'AI';
            
            // Parse markdown for assistant messages, simple newlines for user
            let formattedContent;
            if (role === 'assistant' && typeof marked !== 'undefined') {
                formattedContent = marked.parse(content);
            } else {
                formattedContent = content.replace(/\n/g, '<br>');
            }
            
            messageDiv.innerHTML = `
                <div class="message-avatar">${avatar}</div>
                <div class="message-content">
                    ${formattedContent}
                    <div class="message-time">${formatTime(timestamp)}</div>
                </div>
            `;

            messagesContent.appendChild(messageDiv);
            scrollToBottom();
        }

        // Add counselor cards
        function addCounselorCards(counselors) {
            const messagesContent = document.getElementById('messagesContent');
            const cardsDiv = document.createElement('div');
            cardsDiv.className = 'counselor-cards';

            cardsDiv.innerHTML = `
                <div class="counselor-cards-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    Recommended Counselors
                </div>
                ${counselors.map(c => `
                    <div class="counselor-card">
                        <div class="counselor-name">${c.name}</div>
                        <div class="counselor-title">${c.title}</div>
                        <div class="counselor-contact">
                            ${c.email ? `<span style="display: flex; align-items: center; gap: 4px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg> ${c.email}</span>` : ''}
                            ${c.phone ? `<br><span style="display: flex; align-items: center; gap: 4px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg> ${c.phone}</span>` : ''}
                            ${c.office_location ? `<br><span style="display: flex; align-items: center; gap: 4px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg> ${c.office_location}</span>` : ''}
                        </div>
                    </div>
                `).join('')}
            `;

            messagesContent.appendChild(cardsDiv);
            scrollToBottom();
        }

        // Add crisis resources
        function addCrisisResources(resources) {
            const messagesContent = document.getElementById('messagesContent');
            const resourcesDiv = document.createElement('div');
            resourcesDiv.className = 'crisis-resources';

            let content = '<div class="crisis-resources-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>Crisis Support Resources</div>';
            
            if (resources.hotlines) {
                for (const [name, number] of Object.entries(resources.hotlines)) {
                    content += `<div class="resource-item"><strong>${name}:</strong> ${number}</div>`;
                }
            }

            resourcesDiv.innerHTML = content;
            messagesContent.appendChild(resourcesDiv);
            scrollToBottom();
        }

        // Add category buttons for RED crisis response
        function addCategoryButtons(categories) {
            const messagesContent = document.getElementById('messagesContent');
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'crisis-category-buttons';
            
            let content = '<div class="crisis-category-header"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> Professional Support Categories</div>';
            content += '<div class="category-buttons-grid">';
            
            categories.forEach(cat => {
                const color = cat.color || '#3b82f6';
                content += `<button class="category-btn" style="border-left: 4px solid ${color}; --cat-color: ${color};" onclick="showCounselorsForCategory('${cat.key}', '${cat.label}', '${color}')" onmouseover="this.style.background='${color}'; this.style.color='white'; this.style.borderColor='${color}';" onmouseout="this.style.background='#f8fafc'; this.style.color='${color}'; this.style.borderColor='#e2e8f0'; this.style.borderLeftColor='${color}';">${cat.label}</button>`;
            });
            
            content += '</div>';
            buttonsDiv.innerHTML = content;
            messagesContent.appendChild(buttonsDiv);
            scrollToBottom();
        }

        // Add crisis hotlines for RED crisis response
        function addCrisisHotlines(hotlines) {
            const messagesContent = document.getElementById('messagesContent');
            const hotlinesDiv = document.createElement('div');
            hotlinesDiv.className = 'crisis-hotlines';
            
            let content = '<div class="crisis-hotlines-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg> Emergency Hotlines (24/7)</div>';
            content += '<div class="hotlines-list">';
            
            hotlines.forEach(h => {
                content += `<div class="hotline-item"><span class="hotline-number">${h.number}</span><span class="hotline-name">${h.name}</span></div>`;
            });
            
            content += '</div>';
            hotlinesDiv.innerHTML = content;
            messagesContent.appendChild(hotlinesDiv);
            scrollToBottom();
        }

        // Add escalation message for YELLOW crisis response
        function addEscalationMessage(crisisResponse) {
            const messagesContent = document.getElementById('messagesContent');
            const escalationDiv = document.createElement('div');
            escalationDiv.className = 'crisis-escalation';
            
            let content = '<div class="escalation-message">Would you like to talk to a professional counselor? I can show you available support options.</div>';
            content += '<div class="escalation-buttons">';
            content += '<button class="btn-yes" onclick="showAllCategoryButtons()">Yes, show me counselor options</button>';
            content += '<button class="btn-no" onclick="continueChat()">No, I\'d like to continue chatting</button>';
            content += '</div>';
            
            escalationDiv.innerHTML = content;
            messagesContent.appendChild(escalationDiv);
            scrollToBottom();
        }

        // Add support offer for BLUE crisis response
        function addSupportOffer(crisisResponse) {
            const messagesContent = document.getElementById('messagesContent');
            const offerDiv = document.createElement('div');
            offerDiv.className = 'crisis-support-offer';
            
            let content = `<div class="support-offer-message">If you are feeling overwhelmed, you can try some relaxation exercises to help you feel better.</div>`;
            content += '<div class="support-offer-actions" style="margin-top: 10px;">';
            content += '<button class="relax-btn" onclick="openZenZoneModal()" style="margin-left: 0;">Relax Your Mind</button>';
            content += '</div>';
            
            offerDiv.innerHTML = content;
            messagesContent.appendChild(offerDiv);
            scrollToBottom();
        }

        // Show counselors for a specific category
        async function showCounselorsForCategory(categoryKey, categoryLabel, categoryColor) {
            const color = categoryColor || '#3b82f6';
            try {
                const response = await fetch(`/chat/counselors/${categoryKey}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                if (data.success && data.counselors.length > 0) {
                    displayCounselorsInChat(data.counselors, data.label, color);
                } else {
                    addMessageToUI('system', `No counselors currently available for ${categoryLabel}. Please try another category.`, new Date());
                }
            } catch (error) {
                console.error('Error fetching counselors:', error);
                addMessageToUI('system', 'Failed to load counselors. Please try again.', new Date());
            }
        }

        // Display counselors in chat
        function displayCounselorsInChat(counselors, categoryLabel, categoryColor) {
            const messagesContent = document.getElementById('messagesContent');
            const counselorsDiv = document.createElement('div');
            counselorsDiv.className = 'counselor-cards';
            const color = categoryColor || '#3b82f6';
            
            // Create light background version of color (10% opacity)
            const lightBg = color + '15';
            const veryLightBg = color + '08';
            
            // Set container style with light background
            counselorsDiv.style.cssText = `background: ${veryLightBg}; border: 1px solid ${color}25; border-radius: 12px; padding: 16px; margin-top: 12px;`;
            
            let content = `<div class="counselor-cards-title" style="color: ${color}; border-left: 4px solid ${color}; padding-left: 12px; margin-bottom: 12px;">${categoryLabel} Counselors:</div>`;
            
            counselors.forEach(c => {
                content += `
                    <div class="counselor-card-chat" style="border-left: 4px solid ${color}; background: ${lightBg}; margin-bottom: 10px; padding: 12px; border-radius: 8px;">
                        <div class="counselor-name" style="color: ${color}; font-weight: 600; font-size: 14px;">${c.name}</div>
                        <div class="counselor-title" style="color: #4b5563; font-size: 12px; margin-top: 4px;">${c.title}</div>
                        <div class="counselor-location" style="color: #6b7280; font-size: 12px; margin-top: 4px;">${c.office_location}</div>
                    </div>
                `;
            });
            
            counselorsDiv.innerHTML = content;
            messagesContent.appendChild(counselorsDiv);
            scrollToBottom();
        }

        // Show all category buttons (when user clicks "Yes" on YELLOW/BLUE)
        async function showAllCategoryButtons() {
            const categories = [
                {key: 'academic', label: 'Academic & Study Support', color: '#3b82f6'},
                {key: 'mental_health', label: 'Mental Health & Wellness', color: '#8b5cf6'},
                {key: 'social', label: 'Social & Peer Relationships', color: '#06b6d4'},
                {key: 'crisis', label: 'Crisis & Emergency', color: '#ef4444'},
                {key: 'career', label: 'Career Guidance', color: '#f59e0b'},
                {key: 'relationship', label: 'Relationship Support', color: '#ec4899'},
                {key: 'family', label: 'Family & Home Issues', color: '#10b981'},
                {key: 'physical', label: 'Physical Health', color: '#14b8a6'},
                {key: 'financial', label: 'Financial Wellness', color: '#84cc16'},
                {key: 'personal_development', label: 'Personal Development', color: '#6366f1'},
            ];
            addCategoryButtons(categories);
        }

        // Continue chat (when user clicks "No" on YELLOW/BLUE)
        function continueChat() {
            addMessageToUI('assistant', "I understand. I'm here whenever you need me. Feel free to share more about what's on your mind, or we can talk about something else.", new Date());
        }

        // Scroll to bottom
        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));

            if (days === 0) return 'Today';
            if (days === 1) return 'Yesterday';
            if (days < 7) return `${days} days ago`;
            return date.toLocaleDateString();
        }

        // Format time
        function formatTime(date) {
            return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // ==================== Settings Functions ====================

        // Open settings modal
        function openSettings() {
            document.getElementById('settingsModal').classList.add('active');
            loadMemories();
            loadChatStatistics();
        }

        // Load chat statistics
        async function loadChatStatistics() {
            try {
                // Load all conversations to get stats
                const [activeRes, archivedRes] = await Promise.all([
                    fetch('/chat/conversations?status=active', { headers: { 'X-CSRF-TOKEN': csrfToken } }),
                    fetch('/chat/conversations?status=archived', { headers: { 'X-CSRF-TOKEN': csrfToken } })
                ]);
                
                const activeData = await activeRes.json();
                const archivedData = await archivedRes.json();
                
                const activeCount = activeData.success ? activeData.conversations.length : 0;
                const archivedCount = archivedData.success ? archivedData.conversations.length : 0;
                
                document.getElementById('statTotalConversations').textContent = activeCount + archivedCount;
                document.getElementById('statActiveConversations').textContent = activeCount;
                document.getElementById('statArchivedConversations').textContent = archivedCount;
            } catch (error) {
                console.error('Failed to load chat statistics:', error);
            }
        }

        // Show archive all confirmation modal
        function showArchiveAllModal() {
            document.getElementById('archiveAllModal').classList.add('active');
        }

        // Close archive all modal
        function closeArchiveAllModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('archiveAllModal').classList.remove('active');
        }

        // Confirm and archive all conversations
        async function confirmArchiveAll() {
            closeArchiveAllModal();
            
            try {
                const response = await fetch('/chat/conversations/archive-all', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success) {
                    startNewConversation();
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'All Chats Archived', `${data.archived_count} conversation(s) moved to archive.`);
                } else {
                    showToast('error', 'Archive Failed', data.error || 'Something went wrong.');
                }
            } catch (error) {
                console.error('Archive all error:', error);
                showToast('error', 'Archive Failed', 'Something went wrong. Please try again.');
            }
        }

        // Archive all active conversations (legacy - now uses modal)
        async function archiveAllConversations() {
            showArchiveAllModal();
        }

        // Switch between Active and Archived tabs in Data Controls
        function switchDataControlTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.data-control-tab').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.tab === tab);
            });
            
            // Update panels
            document.getElementById('data-panel-active').classList.toggle('active', tab === 'active');
            document.getElementById('data-panel-archived').classList.toggle('active', tab === 'archived');
        }

        // Show unarchive all confirmation modal
        function showUnarchiveAllModal() {
            document.getElementById('unarchiveAllModal').classList.add('active');
        }

        // Close unarchive all modal
        function closeUnarchiveAllModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('unarchiveAllModal').classList.remove('active');
        }

        // Confirm and unarchive all conversations
        async function confirmUnarchiveAll() {
            closeUnarchiveAllModal();
            
            try {
                const response = await fetch('/chat/conversations/unarchive-all', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success) {
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'All Chats Restored', `${data.unarchived_count} conversation(s) restored to active.`);
                } else {
                    showToast('error', 'Restore Failed', data.error || 'Something went wrong.');
                }
            } catch (error) {
                console.error('Unarchive all error:', error);
                showToast('error', 'Restore Failed', 'Something went wrong. Please try again.');
            }
        }

        // Show delete active modal
        function showDeleteActiveModal() {
            document.getElementById('deleteActiveModal').classList.add('active');
        }

        // Close delete active modal
        function closeDeleteActiveModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('deleteActiveModal').classList.remove('active');
        }

        // Confirm and delete all active conversations
        async function confirmDeleteActive() {
            closeDeleteActiveModal();
            
            try {
                const response = await fetch('/chat/conversations/delete-active', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success) {
                    startNewConversation();
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'All Active Chats Deleted', `${data.deleted_count} conversation(s) permanently deleted.`);
                } else {
                    showToast('error', 'Delete Failed', data.error || 'Something went wrong.');
                }
            } catch (error) {
                console.error('Delete active error:', error);
                showToast('error', 'Delete Failed', 'Something went wrong. Please try again.');
            }
        }

        // Show delete archived confirmation modal
        function showDeleteArchivedModal() {
            document.getElementById('deleteArchivedModal').classList.add('active');
        }

        // Close delete archived modal
        function closeDeleteArchivedModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('deleteArchivedModal').classList.remove('active');
        }

        // Confirm and delete all archived conversations
        async function confirmDeleteArchived() {
            closeDeleteArchivedModal();
            
            try {
                const response = await fetch('/chat/conversations/delete-archived', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success) {
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'Archived Chats Deleted', `${data.deleted_count} conversation(s) permanently deleted.`);
                } else {
                    showToast('error', 'Delete Failed', data.error || 'Something went wrong.');
                }
            } catch (error) {
                console.error('Delete all archived error:', error);
                showToast('error', 'Delete Failed', 'Something went wrong. Please try again.');
            }
        }

        // Delete all archived conversations (legacy - now uses modal)
        async function deleteAllArchived() {
            showDeleteArchivedModal();
        }

        // Close settings modal
        function closeSettings() {
            document.getElementById('settingsModal').classList.remove('active');
        }

        // Close settings when clicking overlay
        function closeSettingsOnOverlay(event) {
            if (event.target.id === 'settingsModal') {
                closeSettings();
            }
        }

        // Switch between settings tabs (ChatGPT-style)
        function switchSettingsTab(tabName) {
            // Remove active from all tabs
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all panels
            document.querySelectorAll('.settings-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            
            // Activate the selected tab
            document.querySelector(`.settings-tab[data-tab="${tabName}"]`).classList.add('active');
            
            // Activate the selected panel
            document.getElementById(`panel-${tabName}`).classList.add('active');
            
            // Load data for the selected tab if needed
            if (tabName === 'personalization') {
                loadMemories();
            }
        }

        // Open counselors modal
        function openCounselorsModal() {
            document.getElementById('counselorsModal').classList.add('active');
            loadCounselors();
        }

        // Close counselors modal
        function closeCounselorsModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('counselorsModal').classList.remove('active');
        }

        // Get color for category label
        function getCategoryColor(label) {
            const lowerLabel = label.toLowerCase();
            if (lowerLabel.includes('academic')) return '#3b82f6'; // Blue
            if (lowerLabel.includes('career')) return '#f59e0b'; // Amber
            if (lowerLabel.includes('crisis') || lowerLabel.includes('emergency')) return '#ef4444'; // Red
            if (lowerLabel.includes('family') || lowerLabel.includes('home')) return '#10b981'; // Emerald
            if (lowerLabel.includes('financial')) return '#84cc16'; // Lime
            if (lowerLabel.includes('mental')) return '#8b5cf6'; // Violet
            if (lowerLabel.includes('personal')) return '#6366f1'; // Indigo
            if (lowerLabel.includes('physical')) return '#14b8a6'; // Teal
            if (lowerLabel.includes('relationship')) return '#ec4899'; // Pink
            if (lowerLabel.includes('social')) return '#06b6d4'; // Cyan
            return '#6b7280'; // Gray default
        }

        // Load counselors by category
        async function loadCounselors() {
            const container = document.getElementById('counselorCategoriesModal');
            
            try {
                const response = await fetch('/chat/counselors', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                if (data.success && data.categories.length > 0) {
                    container.innerHTML = data.categories.map(cat => {
                        const color = getCategoryColor(cat.label);
                        const bgStyle = `background-color: ${color}10`; // very light opacity
                        const borderStyle = `border-left: 4px solid ${color}`;
                        const textStyle = `color: ${color}`;
                        
                        return `
                        <div class="counselor-category" style="margin-bottom: 12px;">
                            <div class="counselor-category-header" onclick="toggleCounselorCategory(this)" 
                                 style="${bgStyle}; ${borderStyle}; padding: 12px 16px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="counselor-category-title" style="font-weight: 600; font-size: 15px; color: #1f2937;">${cat.label}</span>
                                    <span class="counselor-category-count" style="font-size: 12px; padding: 2px 8px; border-radius: 12px; background: white; ${textStyle}; font-weight: 500;">
                                        ${cat.counselors.length} counselor(s)
                                    </span>
                                </div>
                                <svg class="counselor-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px; transition: transform 0.2s; color: #6b7280;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                            <div class="counselor-list" style="display: none; padding-top: 8px;">
                                ${cat.counselors.map(c => `
                                    <div class="counselor-card" style="margin-left: 4px; border-left: 3px solid ${color}; background-color: ${color}08; padding: 12px 16px; margin-bottom: 8px; border-radius: 0 6px 6px 0;">
                                        <div class="counselor-name" style="font-weight: 600; color: #111827;">${c.name}</div>
                                        <div class="counselor-title" style="font-size: 13px; color: #4b5563; margin-bottom: 2px;">${c.title}</div>
                                        <div class="counselor-location" style="font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 12px; height: 12px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                            </svg>
                                            ${c.office_location}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `}).join('');
                } else {
                    container.innerHTML = '<div style="text-align: center; padding: 20px; color: #6b7280;">No counselors available at this time.</div>';
                }
            } catch (error) {
                console.error('Load counselors error:', error);
                container.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc2626;">Failed to load counselors. Please try again.</div>';
            }
        }

        // Toggle counselor category accordion
        function toggleCounselorCategory(header) {
            const list = header.nextElementSibling;
            const chevron = header.querySelector('.counselor-chevron');
            
            if (list.style.display === 'none') {
                list.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            } else {
                list.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Load memories
        async function loadMemories() {
            const memoryList = document.getElementById('memoryList');
            memoryList.innerHTML = '<div style="text-align: center; color: #6b7280; padding: 20px;">Loading memories...</div>';

            try {
                const response = await fetch('/chat/memories', {
                    headers: { 
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    // Update stats
                    document.getElementById('totalMemories').textContent = data.stats?.total_memories || 0;

                    // Render memory list
                    if (data.memories && data.memories.length > 0) {
                        memoryList.innerHTML = data.memories.map(memory => `
                            <div class="memory-item" data-memory-id="${memory.id}">
                                <div style="flex: 1;">
                                    <div class="memory-category">${memory.category_name || memory.category}</div>
                                    <div class="memory-value">${memory.memory_value}</div>
                                </div>
                                <button class="memory-delete" onclick="deleteMemory(${memory.id})" title="Delete">×</button>
                            </div>
                        `).join('');
                    } else {
                        memoryList.innerHTML = '<div style="text-align: center; color: #6b7280; padding: 20px;">No memories saved yet. Chat with the AI to build your memory.</div>';
                    }
                } else {
                    memoryList.innerHTML = '<div style="text-align: center; color: #dc2626; padding: 20px;">Failed to load memories</div>';
                }
            } catch (error) {
                console.error('Load memories error:', error);
                memoryList.innerHTML = '<div style="text-align: center; color: #dc2626; padding: 20px;">Error loading memories</div>';
            }
        }

        // Show clear memory confirmation modal
        function showClearMemoryModal() {
            document.getElementById('clearMemoryModal').classList.add('active');
        }

        // Close clear memory modal
        function closeClearMemoryModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('clearMemoryModal').classList.remove('active');
        }

        // Confirm and clear all memories
        async function confirmClearMemories() {
            closeClearMemoryModal();
            
            try {
                const response = await fetch('/chat/memories/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Memories Cleared', 'Cleared ' + data.deleted_count + ' memories successfully.');
                    loadMemories();
                } else {
                    showToast('error', 'Clear Failed', data.error || 'Failed to clear memories.');
                }
            } catch (error) {
                console.error('Clear memories error:', error);
                showToast('error', 'Error', 'Failed to clear memories. Please try again.');
            }
        }

        // Clear all memories (legacy function - now uses modal)
        async function clearAllMemories() {
            showClearMemoryModal();
        }

        // OPEN delete single memory modal
        function deleteMemory(memoryId) {
            document.getElementById('deleteMemoryId').value = memoryId;
            document.getElementById('deleteMemoryModal').classList.add('active');
        }

        // CLOSE delete single memory modal
        function closeDeleteMemoryModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('deleteMemoryModal').classList.remove('active');
        }

        // CONFIRM and delete single memory
        async function confirmDeleteMemory() {
            const memoryId = document.getElementById('deleteMemoryId').value;
            closeDeleteMemoryModal();

            try {
                const response = await fetch(`/chat/memory/${memoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    // Remove from UI
                    const memoryItem = document.querySelector(`[data-memory-id="${memoryId}"]`);
                    if (memoryItem) {
                        memoryItem.remove();
                    }
                    // Update count
                    const totalEl = document.getElementById('totalMemories');
                    const currentCount = parseInt(totalEl.textContent) || 0;
                    totalEl.textContent = Math.max(0, currentCount - 1);
                    
                    showToast('success', 'Memory Deleted', 'Memory deleted successfully.');
                } else {
                    showToast('error', 'Delete Failed', data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Delete memory error:', error);
                showToast('error', 'Delete Failed', 'Failed to delete memory. Please try again.');
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSettings();
            }
        });
        /* Zen Zone Logic */
        function openZenZoneModal() {
            document.getElementById('zenZoneModal').classList.add('active');
            initBubbles();
            startBreathingExercise();
        }

        function closeZenZoneModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('zenZoneModal').classList.remove('active');
            stopBreathingExercise();
        }

        function switchZenTab(tabId) {
            // Update Tab Buttons
            document.querySelectorAll('.zen-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`.zen-tab[data-zen-tab="${tabId}"]`).classList.add('active');

            // Update Panels
            document.querySelectorAll('.zen-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`zen-panel-${tabId}`).classList.add('active');
        }

        /* Bubble Pop Game */
        function initBubbles() {
            const grid = document.getElementById('bubbleGrid');
            if (grid.children.length === 0) {
                // Generate 30 bubbles
                for (let i = 0; i < 30; i++) {
                    const bubble = document.createElement('div');
                    bubble.className = 'bubble';
                    bubble.onclick = function() { popBubble(this); };
                    grid.appendChild(bubble);
                }
            }
        }

        function popBubble(element) {
            if (!element.classList.contains('popped')) {
                element.classList.add('popped');
            }
        }

        function resetBubbles() {
            const bubbles = document.querySelectorAll('.bubble');
            bubbles.forEach(b => {
                b.classList.remove('popped');
            });
        }

        /* Breathing Exercise */
        let breathingInterval;
        
        function startBreathingExercise() {
            const textElement = document.getElementById('breathingText');
            const instructElement = document.getElementById('breathingInstruction');
            
            if (!textElement || !instructElement) return;

            // Clear any existing interval
            if (breathingInterval) clearInterval(breathingInterval);
            
            // Reset animation sync
             const circle = document.querySelector('.breathing-circle');
             const ring = document.querySelector('.breathing-ring');
             
             // Restart CSS animations
             circle.style.animation = 'none';
             ring.style.animation = 'none';
             circle.offsetHeight; /* trigger reflow */
             circle.style.animation = 'breathe 8s infinite ease-in-out';
             ring.style.animation = 'breatheRing 8s infinite ease-in-out';

            // Initial State
            updateBreathingText(0);

            let time = 0;
            breathingInterval = setInterval(() => {
                time = (time + 1) % 8; // 8 second cycle
                updateBreathingText(time);
            }, 1000);
        }

        function updateBreathingText(second) {
            const textElement = document.getElementById('breathingText');
            const instructElement = document.getElementById('breathingInstruction');

            // Cycle: Inhale (3s) -> Hold (2s) -> Exhale (3s)
            if (second < 3) {
                textElement.innerText = "Inhale";
                instructElement.innerText = "Breathe in deeply...";
            } else if (second < 5) {
                textElement.innerText = "Hold";
                instructElement.innerText = "Hold your breath...";
            } else {
                textElement.innerText = "Exhale";
                instructElement.innerText = "Breadth out slowly...";
            }
        }

        function stopBreathingExercise() {
            if (breathingInterval) clearInterval(breathingInterval);
        }
    </script>
</body>
</html>
