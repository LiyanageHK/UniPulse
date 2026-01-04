<x-guest-layout title="Student Profiling - UniPulse">

<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Student Profiling Form</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                This student profiling form helps us understand your unique needs and preferences as you begin your university journey. The information you provide will help us:
            </p>
            <div class="mt-6 grid md:grid-cols-2 gap-4 max-w-2xl mx-auto text-left">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Create your personalized student profile</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Connect you with compatible peers and resources</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Provide tailored support throughout your first year</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Help you build meaningful connections from day one</span>
                </div>
            </div>
            <div class="mt-6 inline-flex items-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="text-sm text-blue-800 font-medium">Your responses are completely confidential</span>
            </div>
            <p class="text-sm text-gray-500 mt-3">This should take about 2-3 minutes to complete. We appreciate your participation.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form action="{{ route('on-boarding-store') }}" method="POST" class="divide-y divide-gray-100">
                @csrf

                <!-- Academic & Demographic Details Section -->
                <div class="p-8 bg-gradient-to-r from-purple-50 to-transparent">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Academic & Demographic Details</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                                value="{{ $username }}"
                                disabled
                                placeholder="Enter your full name">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                University <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="university"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                <option value="">Select your university</option>
                                <option value="SLIIT">Sri Lanka Institute of Information Technology (SLIIT)</option>
                                <option value="NSBM">NSBM Green University</option>
                                <option value="IIT">Informatics Institute of Technology (IIT)</option>
                                <option value="Colombo">University of Colombo</option>
                                <option value="Jayewardenepura">University of Sri Jayewardenepura</option>
                                <option value="Kelaniya">University of Kelaniya</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Faculty <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="faculty"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                <option value="">Select your faculty</option>
                                <option value="Computing">Faculty of Computing</option>
                                <option value="Engineering">Faculty of Engineering</option>
                                <option value="Business">Faculty of Business</option>
                                <option value="Science">Faculty of Science</option>
                                <option value="Humanities">Faculty of Humanities & Social Sciences</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                AL Stream <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="al_stream"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                <option value="">Select your A/L stream</option>
                                <option value="Bio Science">Bio Science</option>
                                <option value="Physical Science">Physical Science</option>
                                <option value="Arts">Arts</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-4">
                                AL Results <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Subject 1</label>
                                    <select name="al_result_subject1" required class="w-full border-2 border-gray-300 rounded-lg p-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                        <option value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Subject 2</label>
                                    <select name="al_result_subject2" required class="w-full border-2 border-gray-300 rounded-lg p-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                        <option value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Subject 3</label>
                                    <select name="al_result_subject3" required class="w-full border-2 border-gray-300 rounded-lg p-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                        <option value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">English</label>
                                    <select name="al_result_english" required class="w-full border-2 border-gray-300 rounded-lg p-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                        <option value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">General Knowledge</label>
                                    <select name="al_result_gk" required class="w-full border-2 border-gray-300 rounded-lg p-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                        <option value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                What is your preferred learning style? <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-purple-500 transition-all has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50">
                                    <input type="radio" name="learning_style" value="Online" required class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full flex items-center justify-center peer-checked:border-purple-600">
                                            <div class="w-3 h-3 bg-purple-600 rounded-full hidden peer-checked:block"></div>
                                        </div>
                                        <span class="font-medium text-gray-700">Online</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-purple-500 transition-all has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50">
                                    <input type="radio" name="learning_style" value="Physical" class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full"></div>
                                        <span class="font-medium text-gray-700">Physical</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-purple-500 transition-all has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50">
                                    <input type="radio" name="learning_style" value="Hybrid" class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full"></div>
                                        <span class="font-medium text-gray-700">Hybrid</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                How confident are you in transitioning to university life? <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="confidence"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span class="text-left">Not confident<br>at all</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span class="text-right">Extremely<br>confident</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social & Personality Traits Section -->
                <div class="p-8 bg-gradient-to-r from-blue-50 to-transparent">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Social & Personality Traits</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Which social setting do you prefer most? <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="social_setting"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                <option value="">Select your preference</option>
                                <option value="1-on-1">1-on-1 interactions</option>
                                <option value="Small Groups">Small Groups</option>
                                <option value="Large Groups">Large Groups</option>
                                <option value="Online-only">Online-only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Where would you place yourself on the Introvert–Extrovert scale? <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="intro_extro"
                                min="1"
                                max="10"
                                step="1"
                                value="5"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Fully<br>Introvert</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>5</span>
                                <span>6</span>
                                <span>7</span>
                                <span>8</span>
                                <span>9</span>
                                <span>Fully<br>Extrovert</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                In general, how would you describe your usual level of stress or anxiety? <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="stress_level" value="Low" required class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full"></div>
                                        <span class="font-medium text-gray-700">Low</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="stress_level" value="Moderate" class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full"></div>
                                        <span class="font-medium text-gray-700">Moderate</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="stress_level" value="High" class="sr-only">
                                    <div class="flex items-center space-x-3 w-full">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full"></div>
                                        <span class="font-medium text-gray-700">High</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                How comfortable are you working in group activities? <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="group_comfort"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Not comfortable<br>at all</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>Very<br>comfortable</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Which communication methods do you prefer? (Select all that apply) <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="checkbox" name="communication_methods[]" value="Texts" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Texts</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="checkbox" name="communication_methods[]" value="Calls" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Calls</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="checkbox" name="communication_methods[]" value="In-person" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">In-person conversations</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interests & Lifestyle Section -->
                <div class="p-8 bg-gradient-to-r from-green-50 to-transparent">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Interests & Lifestyle</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                What motivates you most about university life? <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="motivation" value="Academic growth" required class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Academic growth</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="motivation" value="Career opportunities" class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Career opportunities</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="motivation" value="Friends and connections" class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Friends and connections</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="motivation" value="Experiences and exposure" class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Experiences and exposure</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="motivation" value="Other" class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Other</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                "I have a clear goal or purpose for my university journey." <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="clear_goal"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Strongly<br>disagree</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>Strongly<br>agree</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                What are your top interests? <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Sports" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Sports</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Arts" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Arts</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Technology" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Technology</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Reading" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Reading</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Social events" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Social events</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="top_interests[]" value="Other" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Other</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                What are your favorite hobbies or activities in your free time? <span class="text-red-500">*</span>
                            </label>
                            <div class="grid md:grid-cols-2 gap-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Reading" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Reading</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Watching Dramas" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Watching Dramas</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Sports" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Sports</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Painting" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Painting</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Traveling" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Traveling</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Volunteering" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Volunteering</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Gaming" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Gaming</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Listening to music" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Listening to music</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="hobbies[]" value="Other" class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Other</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                What is your current living arrangement? <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="living_arrangement"
                                required
                                class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none">
                                <option value="">Select your living arrangement</option>
                                <option value="Hostel">Hostel</option>
                                <option value="Home">Home</option>
                                <option value="Boarding place">Boarding place</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Are you currently employed? <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="employed" value="Yes" required class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">Yes</span>
                                </label>
                                <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="employed" value="No" class="w-5 h-5 text-green-600 focus:ring-2 focus:ring-green-500">
                                    <span class="ml-3 font-medium text-gray-700">No</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wellbeing & Support Needs Section -->
                <div class="p-8 bg-gradient-to-r from-orange-50 to-transparent">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Wellbeing & Support Needs</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                "I often feel overwhelmed or anxious." <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="overwhelmed"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Strongly<br>disagree</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>Strongly<br>agree</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                "I struggle to connect with peers." <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="struggle_connect"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Strongly<br>disagree</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>Strongly<br>agree</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                "I would use an AI platform for wellbeing support." <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="range"
                                name="ai_platform_support"
                                min="1"
                                max="5"
                                step="1"
                                value="3"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-600">
                            <div class="flex justify-between text-xs text-gray-600 mt-2 px-1">
                                <span>Strongly<br>disagree</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>Strongly<br>agree</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Which types of support would you find helpful? <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition-all has-[:checked]:border-orange-600 has-[:checked]:bg-orange-50">
                                    <input type="checkbox" name="support_types[]" value="Peer matching" class="w-5 h-5 text-orange-600 rounded focus:ring-2 focus:ring-orange-500">
                                    <span class="ml-3 font-medium text-gray-700">Peer matching</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition-all has-[:checked]:border-orange-600 has-[:checked]:bg-orange-50">
                                    <input type="checkbox" name="support_types[]" value="Counseling services" class="w-5 h-5 text-orange-600 rounded focus:ring-2 focus:ring-orange-500">
                                    <span class="ml-3 font-medium text-gray-700">Counseling services</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition-all has-[:checked]:border-orange-600 has-[:checked]:bg-orange-50">
                                    <input type="checkbox" name="support_types[]" value="Study groups" class="w-5 h-5 text-orange-600 rounded focus:ring-2 focus:ring-orange-500">
                                    <span class="ml-3 font-medium text-gray-700">Study groups</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition-all has-[:checked]:border-orange-600 has-[:checked]:bg-orange-50">
                                    <input type="checkbox" name="support_types[]" value="AI chatbot support" class="w-5 h-5 text-orange-600 rounded focus:ring-2 focus:ring-orange-500">
                                    <span class="ml-3 font-medium text-gray-700">AI chatbot support</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="p-8 bg-gray-50 text-center">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold py-4 px-12 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Submit Profile
                    </button>
                    <p class="text-sm text-gray-500 mt-4">Your information is secure and will help us provide personalized support</p>
                </div>
            </form>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Need help? Contact us at <a href="mailto:support@wellbeinghub.com" class="text-purple-600 hover:underline">support@wellbeinghub.com</a></p>
        </div>
    </div>
</div>

<style>
    /* Custom Radio Button Styling */
    input[type="radio"]:checked + div > div:first-child {
        border-color: #7c3aed;
    }

    input[type="radio"]:checked + div > div:first-child::after {
        content: '';
        display: block;
        width: 12px;
        height: 12px;
        background-color: #7c3aed;
        border-radius: 50%;
    }

    /* Range Slider Styling */
    input[type="range"]::-webkit-slider-thumb {
        appearance: none;
        width: 24px;
        height: 24px;
        background: #7c3aed;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
    }

    input[type="range"]::-moz-range-thumb {
        width: 24px;
        height: 24px;
        background: #7c3aed;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        border: none;
    }

    input[type="range"]:focus {
        outline: none;
    }
</style>

</x-guest-layout>
