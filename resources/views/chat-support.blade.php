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
            width: 260px;
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

        .settings-btn:hover {
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
            padding-right: 60px;
        }

        .conversation-meta {
            font-size: 12px;
            color: #8e8ea0;
        }

        .conversation-actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
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

        .input-container {
            padding: 16px 24px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .input-form {
            display: flex;
            gap: 12px;
            max-width: 900px;
            margin: 0 auto;
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
                    <button class="settings-btn" onclick="openSettings()">
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

    <!-- Settings Modal -->
    <div class="modal-overlay" id="settingsModal" onclick="closeSettingsOnOverlay(event)">
        <div class="modal" onclick="event.stopPropagation()" style="max-width: 550px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </div>
                <button class="modal-close" onclick="closeSettings()">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                
                <!-- Chat Statistics Section -->
                <div class="settings-section">
                    <div class="settings-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Chat Statistics
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 12px;">
                        <div class="stat-card">
                            <div class="stat-value" id="statTotalConversations">-</div>
                            <div class="stat-label">Total Chats</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="statActiveConversations">-</div>
                            <div class="stat-label">Active</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="statArchivedConversations">-</div>
                            <div class="stat-label">Archived</div>
                        </div>
                    </div>
                </div>

                <!-- Memory Section -->
                <div class="settings-section">
                    <div class="settings-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>
                        AI Memory
                    </div>
                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px;">
                        The AI remembers important details about you to personalize your conversations and provide better support.
                    </p>
                    
                    <div class="memory-stats" id="memoryStats">
                        <div class="memory-stat" style="grid-column: span 2;">
                            <div class="memory-stat-value" id="totalMemories">-</div>
                            <div class="memory-stat-label">Total Memories</div>
                        </div>
                    </div>
                    
                    <div class="memory-list" id="memoryList">
                        <div style="text-align: center; color: #6b7280; padding: 20px;">Loading memories...</div>
                    </div>
                    
                    <div style="margin-top: 16px; display: flex; gap: 12px;">
                        <button class="btn btn-secondary" onclick="loadMemories()" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Refresh
                        </button>
                        <button class="btn btn-danger" onclick="clearAllMemories()" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Clear All
                        </button>
                    </div>
                </div>

                <!-- Data Management Section -->
                <div class="settings-section">
                    <div class="settings-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                        Data Management
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Archive All Chats</div>
                            <div class="settings-item-desc">Move all active conversations to archive</div>
                        </div>
                        <div class="settings-item-action">
                            <button class="btn btn-secondary" onclick="archiveAllConversations()">Archive All</button>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Delete All Archived</div>
                            <div class="settings-item-desc">Permanently delete all archived conversations</div>
                        </div>
                        <div class="settings-item-action">
                            <button class="btn btn-danger" onclick="deleteAllArchived()">Delete</button>
                        </div>
                    </div>
                </div>

                <!-- Account Section -->
                <div class="settings-section">
                    <div class="settings-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Account
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">{{ Auth::user()->name }}</div>
                            <div class="settings-item-desc">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="settings-item-action">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">View Profile</a>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Dashboard</div>
                            <div class="settings-item-desc">View your KPIs and insights</div>
                        </div>
                        <div class="settings-item-action">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Go to Dashboard</a>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="settings-section">
                    <div class="settings-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        About
                    </div>
                    <div style="font-size: 13px; color: #6b7280; line-height: 1.6;">
                        <p style="margin-bottom: 8px;"><strong>UniPulse Conversational Support</strong></p>
                        <p style="margin-bottom: 8px;">An AI-powered mental health support companion designed specifically for university students.</p>
                        <p style="color: #9ca3af; font-size: 12px;">Version 1.0.0</p>
                    </div>
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
                                <div class="conversation-meta">${formatDate(conv.last_message_at || conv.created_at)}</div>
                            </div>
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
                                <button class="action-btn" onclick="event.stopPropagation(); renameConversation(${conv.id}, '${conv.title.replace(/'/g, "\\'")}' )" title="Rename">
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

                    // Show counselor recommendations if any
                    if (data.response.counselor_recommendations && data.response.counselor_recommendations.length > 0) {
                        addCounselorCards(data.response.counselor_recommendations);
                    }

                    // Show crisis resources if any
                    if (data.response.crisis_resources && Object.keys(data.response.crisis_resources).length > 0) {
                        addCrisisResources(data.response.crisis_resources);
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

        // Archive all active conversations
        async function archiveAllConversations() {
            if (!confirm('Are you sure you want to archive all active conversations?')) {
                return;
            }
            
            try {
                const response = await fetch('/chat/conversations?status=active', {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success && data.conversations.length > 0) {
                    let archived = 0;
                    for (const conv of data.conversations) {
                        const res = await fetch(`/chat/conversation/${conv.id}/archive`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        if ((await res.json()).success) archived++;
                    }
                    
                    startNewConversation();
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'All Chats Archived', `${archived} conversation(s) moved to archive.`);
                } else {
                    showToast('error', 'No Active Chats', 'There are no active conversations to archive.');
                }
            } catch (error) {
                console.error('Archive all error:', error);
                showToast('error', 'Archive Failed', 'Something went wrong. Please try again.');
            }
        }

        // Delete all archived conversations
        async function deleteAllArchived() {
            if (!confirm('Are you sure you want to permanently delete ALL archived conversations? This cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch('/chat/conversations?status=archived', {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                
                if (data.success && data.conversations.length > 0) {
                    let deleted = 0;
                    for (const conv of data.conversations) {
                        const res = await fetch(`/chat/conversation/${conv.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        if ((await res.json()).success) deleted++;
                    }
                    
                    await loadConversations();
                    await loadChatStatistics();
                    showToast('success', 'Archived Chats Deleted', `${deleted} conversation(s) permanently deleted.`);
                } else {
                    showToast('error', 'No Archived Chats', 'There are no archived conversations to delete.');
                }
            } catch (error) {
                console.error('Delete all archived error:', error);
                showToast('error', 'Delete Failed', 'Something went wrong. Please try again.');
            }
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

        // Clear all memories
        async function clearAllMemories() {
            if (!confirm('Are you sure you want to clear ALL memories? This will remove everything the AI has learned about you. This action cannot be undone.')) {
                return;
            }

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
                    alert('Cleared ' + data.deleted_count + ' memories successfully.');
                    loadMemories(); // Refresh the list
                } else {
                    alert('Failed to clear memories: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Clear memories error:', error);
                alert('Failed to clear memories. Please try again.');
            }
        }

        // Delete single memory
        async function deleteMemory(memoryId) {
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
                } else {
                    alert('Failed to delete memory: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Delete memory error:', error);
                alert('Failed to delete memory. Please try again.');
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSettings();
            }
        });
    </script>
</body>
</html>