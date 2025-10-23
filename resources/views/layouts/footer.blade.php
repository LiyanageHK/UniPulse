<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flame & Crust Pizzeria - Footer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f8f8;
        }
        .footer-bg {
            background-color: #2b6cb0;
        }
        .text-gold {
            color: #ffffffff;
        }
        .border-gold {
            border-color: #ffffffff;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-end">
    <!-- Footer Section -->
    <footer class="footer-bg text-white pt-12 pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Top Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-8">
                <!-- Brand Description -->
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold mb-4 text-gold">UniPulse</h2>
                    <p class="text-gray-300 mb-6 max-w-md">
                        Deliciously delivered! We're a delivery-only pizzeria powered by smart tech to bring you fresh flavors, fast service, and seamless online ordering.
                    </p>

                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-gold">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Menu</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-gold">Contact Us</h3>
                    <ul class="space-y-3">
                        <li class="text-gray-300 flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-gold"></i>
                            <span>No. 17, Oven Lane, Calombo 07, Sri Lanka</span>
                        </li>
                        <li class="text-gray-300 flex items-center">
                            <i class="fas fa-phone mr-3 text-gold"></i>
                            <span>QJH-2845965</span>
                        </li>
                        <li class="text-gray-300 flex items-center">
                            <i class="fas fa-envelope mr-3 text-gold"></i>
                            <span>info@flameandcrustpizzeria.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section with Border -->
            <div class="border-t border-gold pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-sm text-gray-300">
                        © 2025 Flame & Crust Pizzeria. All rights reserved.
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors text-sm">Privacy Policy</a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors text-sm">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>