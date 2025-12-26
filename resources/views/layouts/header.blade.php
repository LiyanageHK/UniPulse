<nav id="site-header" class="bg-white shadow-sm transition-all duration-300">
    <style>
        /* header - animations removed for logo & brand text */
        #site-header.scrolled { box-shadow: 0 12px 30px rgba(2,6,23,0.08); transform: translateY(-2px); }

        /* brand */
        .brand-link { display:flex; align-items:center; gap:1rem; }
        .brand-logo { transition: none; transform: none; filter: none; }

        /* unified nav tab style (applies to desktop and mobile) */
        .nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .75rem;
            font-size: 1rem; /* same size for all tabs */
            line-height: 1.25rem;
            font-weight: 600; /* make weight consistent with Services */
            color: #374151; /* gray-700 */
            border-radius: 6px;
            transition: color .18s, background-color .12s;
            text-decoration: none;
        }
        .nav-link:hover { color: #2563eb; background-color: rgba(37,99,235,0.04); }
        .nav-link.active { color: #2563eb; }

        /* Login button style */
        .login-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .9rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #2563eb;
            background: transparent;
            border: 1px solid #2563eb;
            border-radius: 8px;
            box-shadow: none;
            transition: background .18s ease, color .12s ease, box-shadow .12s ease, transform .12s;
            text-decoration: none;
        }
        .login-btn:hover, .login-btn:focus {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(37,99,235,0.12);
            transform: translateY(-1px);
        }
        /* mobile login uses same look but full width */
        .login-btn.mobile { display:block; width:100%; text-align:center; padding:.6rem .75rem; }

        /* underline animation kept */
        .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -6px;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg,#2563eb,#06b6d4);
            border-radius: 6px;
            transition: width .28s cubic-bezier(.2,.9,.2,1);
        }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }

        /* services menu animation */
        .services-menu {
            opacity: 0; transform: translateY(-6px) scale(.98);
            transition: opacity .22s ease, transform .22s ease;
            pointer-events: none;
        }
        .services-menu.open { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

        /* mobile adjustments - keep same visual size */
        #mobile-menu { overflow: hidden; transition: max-height .32s ease, opacity .25s ease; max-height: 0; opacity: 0; }
        #mobile-menu.open { max-height: 420px; opacity: 1; }
        .nav-link.mobile { display: block; width: 100%; }

        /* accessible focus styles */
        button:focus, a:focus { outline: 3px solid rgba(37,99,235,0.12); outline-offset: 2px; }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-24 py-4">
            <!-- Brand -->
            <div class="flex items-center">
                <!-- non-clickable brand -->
                <div class="brand-link" aria-label="UniPulse" role="presentation">
                     <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse logo" class="brand-logo h-20 w-20 md:h-20 md:w-20 lg:h-24 lg:w-24 rounded-md">
                     <span class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-blue-700 tracking-tight">UniPulse</span>
                </div>
            </div>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="{{ url('/') }}" class="nav-link {{ Request::is('/') ? 'active' : '' }}">Home</a>

                <!-- Services dropdown -->
                <div class="relative">
                    <button id="services-toggle" type="button" class="nav-link" aria-expanded="false" aria-controls="services-menu">
                        <span>Services</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="services-menu" class="services-menu absolute right-0 mt-2 w-56 bg-white border rounded-md shadow-lg z-50" role="menu" aria-hidden="true">
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Student Profiling</a>
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Risk Detection</a>
                        <a href="{{ route('chat.info') }}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Conversational Support</a>

                        <a href="{{url('chat-support')}}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Peer Matching</a>

                    </div>
                </div>

                <a href="{{ url('/about') }}" class="nav-link {{ Request::is('about') ? 'active' : '' }}">About Us</a>
                <a href="{{ url('/contact') }}" class="nav-link {{ Request::is('contact') ? 'active' : '' }}">Contact Us</a>

                <!-- Login button (desktop) -->
                <a href="{{ route('login') }}" class="login-btn ml-2">Login</a>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="nav-toggle" aria-label="Toggle navigation" class="p-2 rounded-md text-gray-600 hover:bg-gray-100">
                    <svg id="nav-open-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile nav -->
    <div id="mobile-menu" class="md:hidden px-4 pb-4" aria-hidden="true">
        <div class="space-y-2">
            <a href="{{ url('/') }}" class="nav-link mobile {{ Request::is('/') ? 'active' : '' }}">Home</a>

            <!-- Mobile Services collapsible -->
            <div>
                <button id="mobile-services-toggle" class="nav-link mobile w-full flex justify-between items-center text-gray-700">
                    <span>Services</span>
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                    <div id="mobile-services-menu" class="hidden mt-1 pl-4">
                    <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Student Profiling</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Risk Detection</a>
                    <a href="{{ route('chat.info') }}" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Conversational Support</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Peer Matching</a>

                </div>
            </div>

            <a href="{{ url('/about') }}" class="nav-link mobile {{ Request::is('about') ? 'active' : '' }}">About Us</a>
            <a href="{{ url('/contact') }}" class="nav-link mobile {{ Request::is('contact') ? 'active' : '' }}">Contact Us</a>

            <!-- Login link (mobile) -->
            <a href="{{ route('login') }}" class="login-btn mobile mt-2">Login</a>
        </div>
    </div>

    <script>
        (function(){
            const btn = document.getElementById('nav-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            btn && btn.addEventListener('click', () => {
                const open = mobileMenu.classList.toggle('open');
                mobileMenu.setAttribute('aria-hidden', !open);
            });

            const mobileServicesToggle = document.getElementById('mobile-services-toggle');
            const mobileServicesMenu = document.getElementById('mobile-services-menu');
            mobileServicesToggle && mobileServicesToggle.addEventListener('click', () => mobileServicesMenu.classList.toggle('hidden'));

            const servicesToggle = document.getElementById('services-toggle');
            const servicesMenu = document.getElementById('services-menu');
            if (servicesToggle && servicesMenu) {
                servicesToggle.addEventListener('click', function(e){
                    e.stopPropagation();
                    const opened = servicesMenu.classList.toggle('open');
                    servicesMenu.setAttribute('aria-hidden', !opened);
                });
                servicesMenu.addEventListener('click', function(e){ e.stopPropagation(); });
                document.addEventListener('click', function(){ servicesMenu.classList.remove('open'); servicesMenu.setAttribute('aria-hidden','true'); });
                document.addEventListener('keydown', function(e){ if (e.key === 'Escape') servicesMenu.classList.remove('open'); });
            }

            // header sticky shadow on scroll
            const header = document.getElementById('site-header');
            const onScroll = () => { if (window.scrollY > 12) header.classList.add('scrolled'); else header.classList.remove('scrolled'); };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>
</nav>