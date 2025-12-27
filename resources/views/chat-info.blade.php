<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conversational Support - UniPulse</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { margin: 0; font-family: 'Figtree', sans-serif; background: #f9fafb; }
        .info-container { max-width: 900px; margin: 0 auto; padding: 60px 20px; }
        .info-header { text-align: center; margin-bottom: 60px; }
        .info-header h1 { font-size: 48px; font-weight: 700; color: #111827; margin-bottom: 16px; }
        .info-header p { font-size: 20px; color: #6b7280; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 48px; }
        .feature-card { background: white; padding: 32px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .feature-icon { font-size: 32px; margin-bottom: 16px; }
        .feature-title { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 8px; }
        .feature-desc { color: #6b7280; line-height: 1.6; }
        .cta-section { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 48px; border-radius: 16px; text-align: center; color: white; }
        .cta-title { font-size: 32px; font-weight: 700; margin-bottom: 16px; }
        .cta-desc { font-size: 18px; margin-bottom: 32px; opacity: 0.9; }
        .cta-button { display: inline-block; padding: 16px 48px; background: white; color: #6366f1; font-size: 18px; font-weight: 600; border-radius: 8px; text-decoration: none; transition: transform 0.2s; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="info-container">
        <div class="info-header">
            <h1>💬 Conversational Support</h1>
            <p>24/7 AI-powered support for your university journey</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <div class="feature-title">AI-Powered Chat</div>
                <div class="feature-desc">Get instant support from our intelligent AI counselor, trained to understand student challenges and provide helpful guidance.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <div class="feature-title">Personalized Support</div>
                <div class="feature-desc">Our AI remembers your profile and conversation history to provide contextually relevant and personalized advice.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <div class="feature-title">Crisis Detection</div>
                <div class="feature-desc">Advanced crisis detection ensures you get the right resources when you need them most, with automatic counselor matching.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <div class="feature-title">Academic Guidance</div>
                <div class="feature-desc">Get help with study strategies, time management, exam stress, and navigating university life successfully.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🌟</div>
                <div class="feature-title">Mental Wellbeing</div>
                <div class="feature-desc">Talk about stress, anxiety, or any challenges. Our AI provides empathetic support and connects you to professional help.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Private & Secure</div>
                <div class="feature-desc">Your conversations are confidential and secure. We prioritize your privacy and wellbeing above all else.</div>
            </div>
        </div>

        <div class="cta-section">
            <div class="cta-title">Ready to get started?</div>
            <div class="cta-desc">Join thousands of students getting support through UniPulse</div>
            @auth
                <a href="{{ route('chat.support') }}" class="cta-button">Open Chat →</a>
            @else
                <a href="{{ route('login') }}" class="cta-button">Login to Chat →</a>
            @endauth
        </div>

        <div style="margin-top: 48px; text-align: center; color: #9ca3af; font-size: 14px;">
            <p>Need immediate help? Call 1333 (National Mental Health Helpline) or 011-2682535 (Sumithrayo)</p>
        </div>
    </div>
</body>
</html>
