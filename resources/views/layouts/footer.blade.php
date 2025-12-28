<!-- Footer Section -->
<footer class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 text-white pt-16 pb-8">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 pb-12">
            <!-- Brand Section -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-4 mb-6">
                    <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="h-20 w-20 rounded-xl shadow-lg hover:scale-105 transition-transform duration-300">
                    <div>
                        <h2 class="text-3xl font-extrabold text-white mb-1">UniPulse</h2>
                        <p class="text-blue-200 text-sm font-medium">Your Wellbeing Companion</p>
                    </div>
                </div>
                <p class="text-blue-100 mb-6 max-w-md leading-relaxed" style="text-align: justify;">
                    Empowering student wellbeing through innovative AI technology. Your trusted partner in mental health support, academic excellence, and personal growth.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="fas fa-link text-blue-300"></i>
                    Quick Links
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ url('/') }}" class="text-blue-100 hover:text-white hover:pl-2 transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-blue-400 group-hover:text-white"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/about') }}" class="text-blue-100 hover:text-white hover:pl-2 transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-blue-400 group-hover:text-white"></i>
                            About Us
                        </a>
                    </li>
                    <li class="relative">
                        <button id="footer-services-toggle" class="text-blue-100 hover:text-white hover:pl-2 transition-all duration-300 flex items-center gap-2 group w-full text-left">
                            <i class="fas fa-chevron-right text-xs text-blue-400 group-hover:text-white"></i>
                            <span>Services</span>
                            <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-300" id="footer-services-arrow"></i>
                        </button>
                        <ul id="footer-services-menu" class="hidden mt-2 ml-6 space-y-2 bg-white/5 backdrop-blur-sm rounded-lg p-3">
                            <li>
                                <a href="#" class="text-blue-200 hover:text-white transition-colors flex items-center gap-2 text-sm">
                                    <i class="fas fa-circle text-xs text-blue-400"></i>
                                    Student Profiling
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-blue-200 hover:text-white transition-colors flex items-center gap-2 text-sm">
                                    <i class="fas fa-circle text-xs text-blue-400"></i>
                                    Risk Detection
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('chat.info') }}" class="text-blue-200 hover:text-white transition-colors flex items-center gap-2 text-sm">
                                    <i class="fas fa-circle text-xs text-blue-400"></i>
                                    Conversational Support
                                </a>
                            </li>
                            <li>
                                <a href="{{url('chat-support')}}" class="text-blue-200 hover:text-white transition-colors flex items-center gap-2 text-sm">
                                    <i class="fas fa-circle text-xs text-blue-400"></i>
                                    Peer Matching
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ url('/contact') }}" class="text-blue-100 hover:text-white hover:pl-2 transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-blue-400 group-hover:text-white"></i>
                            Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="fas fa-address-book text-blue-300"></i>
                    Contact Us
                </h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3 group">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-map-marker-alt text-blue-300"></i>
                        </div>
                        <div>
                            <p class="text-blue-100 leading-relaxed">
                                No. 17, Uni Lane,<br>
                                Colombo 07, Sri Lanka
                            </p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-phone text-blue-300"></i>
                        </div>
                        <a href="tel:0112840971" class="text-blue-100 hover:text-white transition-colors">
                            +94 11 284 0971
                        </a>
                    </li>
                    <li class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-envelope text-blue-300"></i>
                        </div>
                        <a href="mailto:info@unipulse.com" class="text-blue-100 hover:text-white transition-colors">
                            info@unipulse.com
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="border-t border-white/20 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-blue-200 text-sm text-center md:text-left">
                    <p class="flex items-center gap-2 justify-center md:justify-start">
                        <i class="far fa-copyright"></i>
                        <span>2025 UniPulse. All rights reserved.</span>
                    </p>
                </div>
                <div class="flex items-center gap-6 text-sm text-blue-200">
                    <a href="{{ route('terms') }}" class="hover:text-white transition-colors">Terms of Service</a>
                    <span class="text-blue-400">•</span>
                    <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const servicesToggle = document.getElementById('footer-services-toggle');
            const servicesMenu = document.getElementById('footer-services-menu');
            const servicesArrow = document.getElementById('footer-services-arrow');

            if (servicesToggle && servicesMenu && servicesArrow) {
                servicesToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    servicesMenu.classList.toggle('hidden');
                    servicesArrow.classList.toggle('rotate-180');
                });
            }
        });
    </script>
</footer>