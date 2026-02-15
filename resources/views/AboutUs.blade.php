<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>About Us - UniPulse</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/UP.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* simple entrance animations */
        .fade-up {
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .6s cubic-bezier(.2,.9,.2,1), transform .6s cubic-bezier(.2,.9,.2,1);
            will-change: opacity, transform;
        }
        .fade-up.in-view { opacity: 1; transform: translateY(0); }

        /* stagger helper */
        .stagger > * { opacity: 0; transform: translateY(8px); }
        .stagger.in-view > * {
            opacity: 1; transform: translateY(0);
        }
        .stagger > * { transition: opacity .45s ease, transform .45s ease; }
        .stagger > *:nth-child(1){ transition-delay:.05s }
        .stagger > *:nth-child(2){ transition-delay:.12s }
        .stagger > *:nth-child(3){ transition-delay:.18s }
        .stagger > *:nth-child(4){ transition-delay:.24s }

        /* card hover */
        .card-hover { transition: transform .18s ease, box-shadow .18s ease; }
        .card-hover:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 18px 36px rgba(2,6,23,0.08); }

        /* hero image subtle scale on view */
        .hero-img { transform: scale(.98); transition: transform .6s ease; }
        .hero-img.in-view { transform: scale(1); }

        /* blue hero gradient used on service pages */
        .hero-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            position: relative;
        }
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.35;
        }
        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* team avatar pop */
        .avatar { transition: transform .28s ease, box-shadow .2s ease; }
        .avatar:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 10px 22px rgba(2,6,23,0.08); }

        /* keep accessibility focus visible */
        a:focus, button:focus { outline: 3px solid rgba(37,99,235,0.12); outline-offset: 3px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    {{-- header (single include) --}}
    @include('layouts.header')

    <!-- Full-width Hero (blue gradient with wave)  -->
    <section class="hero-gradient text-white py-20 md:py-28 relative overflow-hidden fade-up" data-animate>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="md:flex md:items-center md:gap-10">
                <div class="md:flex-1">
                    <div class="inline-block mb-4">
                        <span class="px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20">About UniPulse</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-4">About UniPulse</h1>
                    <p class="text-blue-100 mb-6 leading-relaxed">
                        UniPulse connects students, educators and support staff through data-driven tools and intelligent peer-matching.
                        We combine analytics, conversational support and early risk detection to help students succeed.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-md font-semibold shadow-sm hover:bg-blue-50">Get Started</a>
                    </div>
                </div>

                <div class="md:w-1/3 mt-6 md:mt-0">
                    <img src="{{ asset('images/aboutUsImage.jpg') }}" alt="UniPulse illustration" class="w-full rounded-lg hero-img">
                </div>
            </div>
        </div>

        <!-- Wave Separator -->
        <div class="absolute bottom-0 left-0 w-full">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- removed: original in-main hero (now full-width above) -->

        <!-- What we do -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 fade-up" data-animate>What we do</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 stagger" data-animate>
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
                    <h3 class="text-lg font-semibold mb-2">Student Profiling</h3>
                    <p class="text-gray-600 text-sm">Aggregate academic, engagement and behavioural signals to build actionable student profiles.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
                    <h3 class="text-lg font-semibold mb-2">Risk Detection</h3>
                    <p class="text-gray-600 text-sm">Early identification of students who need intervention so support can be timely and precise.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
                    <h3 class="text-lg font-semibold mb-2">Conversational Support</h3>
                    <p class="text-gray-600 text-sm">Intelligent chat and guidance to help students navigate academic and wellbeing questions.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
                    <h3 class="text-lg font-semibold mb-2">Peer Matching</h3>
                    <p class="text-gray-600 text-sm">Match students by skills, goals and preferences to form effective study partners and peer support groups.</p>
                </div>
            </div>
        </section> <!-- end What we do -->

        <!-- Key Features Summary (SVG icons matching UI) -->
        <section class="mb-10 fade-up" data-animate>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Key Features</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
                <!-- Data-Driven Analytics -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="10" width="4" height="11" rx="1"></rect>
                            <rect x="9" y="6" width="4" height="15" rx="1"></rect>
                            <rect x="15" y="2" width="4" height="19" rx="1"></rect>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Data‑Driven Analytics</div>
                </div>

                <!-- AI-powered Conversational Support -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7A8.38 8.38 0 014 11.5 8.5 8.5 0 1119.5 6" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">AI‑powered Conversational Support</div>
                </div>

                <!-- Early Risk Alert System -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Early Risk Alert System</div>
                </div>

                <!-- Peer Collaboration Tools -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 010 7.75"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Peer Collaboration Tools</div>
                </div>

                <!-- Privacy & Compliance -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                            <path d="M7 11V7a5 5 0 0110 0v4"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Privacy & Compliance</div>
                </div>

                <!-- Easy Integration With LMS -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06A2 2 0 014.3 17.88l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82L4.21 6.3A2 2 0 016.04 3.47l.06.06a1.65 1.65 0 001.82.33H8.21A1.65 1.65 0 0010 3.9V3a2 2 0 014 0v.9c.26.1.5.28.66.5"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Easy Integration With LMS</div>
                </div>

                <!-- Accessible Anytime, Anywhere -->
                <div class="flex flex-col items-center text-center p-3">
                    <div class="mb-2 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="7" y="2" width="10" height="20" rx="2"></rect>
                            <path d="M11 18h2"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">Accessible Anytime, Anywhere</div>
                </div>
            </div>
        </section>

        <!-- Vision & Mission (simple split, no cards) -->
        <section class="mb-10 py-8 bg-gradient-to-r from-blue-50 to-white rounded-lg fade-up" data-animate>
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Vision & Mission</h2>

                <div class="md:flex md:items-start md:gap-12">
                    <div class="md:w-1/2 text-left">
                        <h3 class="text-xl font-semibold text-blue-700 mb-2">Our Vision</h3>
                        <p class="text-gray-600 leading-relaxed">
                            To empower every student to achieve their full academic and personal potential through connected,
                            ethical and intelligent support — delivered by data, guided by humans.
                        </p>
                    </div>

                    <div class="md:w-1/2 mt-6 md:mt-0 text-left">
                        <h3 class="text-xl font-semibold text-blue-700 mb-2">Our Mission</h3>
                        <p class="text-gray-600 leading-relaxed">
                            UniPulse builds privacy-first tools that combine analytics, conversational guidance and peer matching
                            to identify needs early, personalise support and strengthen student communities.
                        </p>
                        <ul class="mt-4 text-gray-600 list-disc list-inside space-y-1">
                            <li>Deliver actionable student insights for timely interventions.</li>
                            <li>Foster peer connections that improve learning outcomes.</li>
                            <li>Respect privacy and use data ethically.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team / Values -->
        <section class="mb-14">
            <h2 class="text-2xl font-bold mb-6 fade-up" data-animate>Our Values & Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm fade-up" data-animate>
                    <h4 class="font-semibold mb-2">Our Values</h4>
                    <ul class="text-gray-600 list-disc list-inside space-y-2">
                        <li>Student-first design</li>
                        <li>Data privacy & ethics</li>
                        <li>Evidence-driven outcomes</li>
                        <li>Collaborative partnerships</li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm fade-up" data-animate>
                    <h4 class="font-semibold mb-4">Core Team</h4>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold avatar">HL</div>
                            <div>
                                <div class="font-semibold text-gray-900">Hiruni Liyanage</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold avatar">HP</div>
                            <div>
                                <div class="font-semibold text-gray-900">Hirushima Pathiraja</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold avatar">PF</div>
                            <div>
                                <div class="font-semibold text-gray-900">Piumi Fonseka</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold avatar">HP</div>
                            <div>
                                <div class="font-semibold text-gray-900">Hiruni Poornima</div>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
        </section>

        <!-- How It Works — 3‑Step Flow -->
        <section class="mb-10 fade-up" data-animate>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">How It Works — 3 Step Flow</h2>

            <div class="how-flow flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="step bg-white p-6 rounded-lg shadow-sm text-center flex-1 card-hover">
                    <div class="mx-auto mb-3 w-12 h-12 flex items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <!-- Collect icon -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h18M3 12h12M3 17h18"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Collect</h3>
                    <p class="text-sm text-gray-600">Academic & engagement data gathered from LMS and interactions.</p>
                </div>

                <div class="hidden md:block text-blue-300">
                    <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h18"></path>
                        <path d="M15 6l6 6-6 6"></path>
                    </svg>
                </div>

                <div class="step bg-white p-6 rounded-lg shadow-sm text-center flex-1 card-hover">
                    <div class="mx-auto mb-3 w-12 h-12 flex items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <!-- Analyse icon -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18"></path>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Analyse</h3>
                    <p class="text-sm text-gray-600">AI & analytics identify needs, trends and early risks.</p>
                </div>

                <div class="hidden md:block text-blue-300">
                    <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h18"></path>
                        <path d="M15 6l6 6-6 6"></path>
                    </svg>
                </div>

                <div class="step bg-white p-6 rounded-lg shadow-sm text-center flex-1 card-hover">
                    <div class="mx-auto mb-3 w-12 h-12 flex items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <!-- Support icon -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10v6a2 2 0 0 1-2 2H8l-5 2V6a2 2 0 0 1 2-2h3"></path>
                            <path d="M7 8h.01"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Support</h3>
                    <p class="text-sm text-gray-600">Actionable insights, guidance and peer‑matching for students.</p>
                </div>
            </div>

            <style>
                /* small layout tweaks for the flow */
                .how-flow .step { min-width: 200px; }
                @media (min-width: 768px) {
                    .how-flow { align-items: stretch; }
                    .how-flow .step { text-align: center; display: flex; flex-direction: column; justify-content: center; }
                }
            </style>
        </section>
    </main>

    {{-- footer (single include) --}}
    @include('layouts.footer')

    <script>
        // IntersectionObserver to add in-view classes
        (function(){
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        // for stagger containers add class immediately so children animate with delay
                        if (entry.target.classList.contains('stagger')) {
                            entry.target.classList.add('in-view');
                        }
                        // unobserve once visible
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });

            document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));

            // small parallax for hero image on scroll (subtle)
            const heroImg = document.querySelector('.hero-img');
            if (heroImg) {
                window.addEventListener('scroll', () => {
                    const rect = heroImg.getBoundingClientRect();
                    const windowH = window.innerHeight;
                    const visible = Math.max(0, Math.min(1, (windowH - rect.top) / (windowH + rect.height)));
                    // translate and scale subtly
                    heroImg.style.transform = `translateY(${(1 - visible) * 6}px) scale(${0.98 + visible * 0.02})`;
                }, { passive: true });
            }
        })();
    </script>
</body>
</html>
