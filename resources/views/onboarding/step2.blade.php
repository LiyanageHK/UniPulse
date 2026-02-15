<x-app-layout title="Onboarding Step 2 - UniPulse">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#3182ce] text-white font-semibold shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="w-24 h-1 bg-[#3182ce] mx-2"></div>
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#3182ce] text-white font-semibold shadow-lg">2</div>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-600 font-medium">Step 2 of 2 - Almost There!</p>
            </div>

            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden">
                
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-[#3182ce] to-blue-600 p-8 text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Lifestyle & Wellbeing</h1>
                    <p class="text-blue-100">Share more about your daily life so we can personalise support and recommendations</p>
                </div>

                <form method="POST" action="{{ route('onboarding.step2.store') }}" class="p-8 lg:p-12 space-y-8">
                    @csrf

                    <style>
                        /* Checkbox styling */
                        input[type="checkbox"]:checked + span {
                            color: white !important;
                        }
                        input[type="checkbox"]:checked ~ * {
                            background-color: #3182ce !important;
                            border-color: #3182ce !important;
                            color: white !important;
                        }
                        label:has(input[type="checkbox"]:checked) {
                            background-color: #3182ce !important;
                            border-color: #3182ce !important;
                            color: white !important;
                            box-shadow: 0 10px 15px -3px rgba(49, 130, 206, 0.3);
                        }
                        label:has(input[type="checkbox"]:checked) span {
                            color: white !important;
                        }

                        /* Radio button styling */
                        input[type="radio"]:checked + span {
                            color: white !important;
                        }
                        input[type="radio"]:checked ~ * {
                            background-color: #3182ce !important;
                            border-color: #3182ce !important;
                            color: white !important;
                        }
                        label:has(input[type="radio"]:checked) {
                            background-color: #3182ce !important;
                            border-color: #3182ce !important;
                            color: white !important;
                            box-shadow: 0 10px 15px -3px rgba(49, 130, 206, 0.3);
                        }
                        label:has(input[type="radio"]:checked) span {
                            color: white !important;
                        }
                    </style>

                    @if($errors->any())
                        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <strong class="font-semibold">There were errors:</strong>
                                    <ul class="mt-2 list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Section Header -->
                    <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                        <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">🌱</div>
                        <h4 class="text-xl font-bold text-gray-800">Interests & Lifestyle</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Motivator for University Life <span class="text-red-500">*</span></label>
                        <select name="primary_motivator" class="mt-1 block w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200" required>
                            <option value="">Select your primary motivator</option>
                            <option value="Academic growth" {{ old('primary_motivator')=='Academic growth' ? 'selected' : '' }}>Academic growth</option>
                            <option value="Career opportunities" {{ old('primary_motivator')=='Career opportunities' ? 'selected' : '' }}>Career opportunities</option>
                            <option value="Friends and connections" {{ old('primary_motivator')=='Friends and connections' ? 'selected' : '' }}>Friends and connections</option>
                            <option value="Experiences and exposure" {{ old('primary_motivator')=='Experiences and exposure' ? 'selected' : '' }}>Experiences and exposure</option>
                        </select>
                        @error('primary_motivator') <p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Goal Clarity <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-3">1 - Strongly disagree, 5 - Strongly agree</p>
                        <div class="flex gap-3 justify-center">
                            @for($i=1;$i<=5;$i++)
                                <label class="inline-flex items-center justify-center px-6 py-4 border-2 rounded-xl cursor-pointer w-20 text-center transition-all duration-200 hover:shadow-md {{ old('goal_clarity') == $i ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg scale-110' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="radio" class="hidden" id="goal{{ $i }}" name="goal_clarity" value="{{ $i }}" required {{ old('goal_clarity') == $i ? 'checked' : '' }}>
                                    <span class="text-lg font-bold">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        @error('goal_clarity') <p class="text-red-600 text-sm mt-1.5 flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Top Interests <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['Sports', 'Arts', 'Tech', 'Reading', 'Social Events', 'Other'] as $interests)
                                <label class="inline-flex items-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ in_array($interests, old('interests', [])) ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="checkbox" class="sr-only" name="interests[]" id="interests_{{ $interests }}" value="{{ $interests }}" {{ in_array($interests, old('interests', [])) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $interests }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('interests') <p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Hobbies <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['Reading', 'Watching Dramas', 'Sports', 'Painting', 'Travelling', 'Volunteering', 'Gaming', 'Listening to Music'] as $hobbies)
                                <label class="inline-flex items-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ in_array($hobbies, old('hobbies', [])) ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="checkbox" class="sr-only" name="hobbies[]" id="hobbies_{{ $hobbies }}" value="{{ $hobbies }}" {{ in_array($hobbies, old('hobbies', [])) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $hobbies }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('hobbies') <p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Living Arrangement <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(['Hostel', 'Home', 'Boarding', 'Other'] as $living_arrangement)
                                <label class="inline-flex items-center justify-center gap-2 px-4 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ old('living_arrangement') == $living_arrangement ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input class="hidden" type="radio" name="living_arrangement" id="living_{{ $living_arrangement }}" value="{{ $living_arrangement }}" required {{ old('living_arrangement') == $living_arrangement ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $living_arrangement }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('living_arrangement')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Are you currently employed? <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ old('is_employed') == '1' ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                <input class="hidden" type="radio" name="is_employed" id="empYes" value="1" required {{ old('is_employed') == '1' ? 'checked' : '' }}>
                                <span class="text-sm font-medium">Yes</span>
                            </label>
                            <label class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ old('is_employed') == '0' ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                <input class="hidden" type="radio" name="is_employed" id="empNo" value="0" {{ old('is_employed') == '0' ? 'checked' : '' }}>
                                <span class="text-sm font-medium">No</span>
                            </label>
                        </div>
                        @error('is_employed')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <div class="border-t-2 border-blue-100 pt-8"></div>

                    <!-- Section Header -->
                    <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                        <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">💬</div>
                        <h4 class="text-xl font-bold text-gray-800">Wellbeing</h4>
                    </div>

                    @php
                        $questions = [
                            'I often feel overwhelmed or anxious.'=>'overwhelm_level',
                            'I struggle to connect with peers.'=>'peer_struggle',
                            'I would use an AI platform for wellbeing support.'=>'ai_openness'
                        ];
                    @endphp

                    @foreach($questions as $text => $name)
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">{{ $text }} <span class="text-red-500">*</span></label>
                            <p class="text-xs text-gray-500 mb-3">1 - Strongly disagree, 5 - Strongly agree</p>
                            <div class="flex gap-3 justify-center">
                                @for($i=1;$i<=5;$i++)
                                    <label class="inline-flex items-center justify-center px-6 py-4 border-2 rounded-xl cursor-pointer w-20 text-center transition-all duration-200 hover:shadow-md {{ old($name) == $i ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg scale-110' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                        <input type="radio" class="hidden" id="{{ $name.$i }}" name="{{ $name }}" value="{{ $i }}" required {{ old($name) == $i ? 'checked' : '' }}>
                                        <span class="text-lg font-bold">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                            @error($name) <p class="text-red-600 text-sm mt-2 flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Preferred Support Methods <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach(['Peer Matching', 'Counseling', 'Study Groups', 'Chatbot'] as $preferred_support_types)
                                <label class="inline-flex items-center justify-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ in_array($preferred_support_types, old('preferred_support_types', [])) ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="checkbox" class="sr-only" name="preferred_support_types[]" id="preferred_support_types_{{ $preferred_support_types }}" value="{{ $preferred_support_types }}" {{ in_array($preferred_support_types, old('preferred_support_types', [])) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-center">{{ $preferred_support_types }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('preferred_support_types') <p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="group relative px-8 py-4 bg-[#3182ce] text-white rounded-xl font-bold text-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                            Complete Profile
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>