<x-app-layout title="Onboarding Step 1 - UniPulse">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#3182ce] text-white font-semibold shadow-lg">1</div>
                        <div class="w-24 h-1 bg-[#3182ce] mx-2"></div>
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-500 font-semibold">2</div>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-600 font-medium">Step 1 of 2</p>
            </div>

            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden">
                
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-[#3182ce] to-blue-600 p-8 text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Academic & Social Details</h1>
                    <p class="text-blue-100">Provide your academic and social details to personalise your experience</p>
                </div>

                <form method="POST" action="{{ route('onboarding.step1.store') }}" class="p-8 lg:p-12 space-y-8">
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
                        <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">📘</div>
                        <h4 class="text-xl font-bold text-gray-800">Academic & Demographic Details</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Full Name -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" value="{{ old('name', auth()->user()->name ?? '') }}" required
                                   class="mt-1 block w-full border-2 border-gray-200 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                            @error('name')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                        <!-- University -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">University <span class="text-red-500">*</span></label>
                            <select id="university" name="university" required
                                    class="mt-1 block w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                <option value="">Select University</option>
                                <option value="SLIIT" {{ old('university')=='SLIIT' ? 'selected' : '' }}>Sri Lanka Institute of Information Technology</option>
                                <option value="NSBM" {{ old('university')=='NSBM' ? 'selected' : '' }}>NSBM Green University</option>
                                <option value="IIT" {{ old('university')=='IIT' ? 'selected' : '' }}>Informatics Institute of Technology</option>
                                <option value="University of Colombo" {{ old('university')=='University of Colombo' ? 'selected' : '' }}>University of Colombo</option>
                                <option value="University of Sri Jayewardenepura" {{ old('university')=='University of Sri Jayewardenepura' ? 'selected' : '' }}>University of Sri Jayewardenepura</option>
                                <option value="University of Kelaniya" {{ old('university')=='University of Kelaniya' ? 'selected' : '' }}>University of Kelaniya</option>
                                <option value="Other" {{ old('university')=='Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('university')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                        <!-- Faculty -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Faculty <span class="text-red-500">*</span></label>
                            <select id="faculty" name="faculty" required
                                    class="mt-1 block w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                <option value="">Select Faculty</option>
                                <option value="Faculty of Computing" {{ old('faculty')=='Faculty of Computing' ? 'selected' : '' }}>Faculty of Computing</option>
                                <option value="Faculty of Engineering" {{ old('faculty')=='Faculty of Engineering' ? 'selected' : '' }}>Faculty of Engineering</option>
                                <option value="Faculty of Business" {{ old('faculty')=='Faculty of Business' ? 'selected' : '' }}>Faculty of Business</option>
                                <option value="Faculty of Science" {{ old('faculty')=='Faculty of Science' ? 'selected' : '' }}>Faculty of Science</option>
                                <option value="Faculty of Humanities" {{ old('faculty')=='Faculty of Humanities' ? 'selected' : '' }}>Faculty of Humanities & Social Sciences</option>
                                <option value="Other" {{ old('faculty')=='Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('faculty')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                        <!-- AL Stream -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">A/L Stream <span class="text-red-500">*</span></label>
                            <select id="al_stream" name="al_stream" required
                                    class="mt-1 block w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                <option value="">Select Stream</option>
                                <option value="Bio Science" {{ old('al_stream')=='Bio Science' ? 'selected' : '' }}>Bio Science</option>
                                <option value="Physical Science" {{ old('al_stream')=='Physical Science' ? 'selected' : '' }}>Physical Science</option>
                                <option value="Commerce" {{ old('al_stream')=='Commerce' ? 'selected' : '' }}>Commerce</option>
                                <option value="Arts" {{ old('al_stream')=='Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Other" {{ old('al_stream')=='Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('al_stream')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                        <!-- AL Subjects & Results -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">A/L Subjects & Results <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @for($i=1; $i<=5; $i++)
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 mb-2">Subject {{ $i }} {{ $i <= 3 ? '(Required)' : '(Optional)' }}</p>
                                    <div class="flex gap-2">
                                        <input type="text" name="al_subject_{{ $i }}" placeholder="Subject name" value="{{ old('al_subject_'.$i) }}" {{ $i <= 3 ? 'required' : '' }}
                                               class="flex-1 border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                        <select name="al_grade_{{ $i }}" {{ $i <= 3 ? 'required' : '' }}
                                                class="w-28 border-2 border-gray-200 rounded-lg px-3 py-2 bg-white text-sm font-semibold focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                            <option value="">Grade</option>
                                            @foreach(['A','B','C','S','F'] as $grade)
                                                <option value="{{ $grade }}" {{ old('al_grade_'.$i) == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('al_subject_'.$i) <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('al_grade_'.$i) <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Learning Style -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Preferred Learning Style <span class="text-red-500">*</span></label>
                            <div class="flex flex-wrap gap-3">
                                @foreach(['Physical','Online','Hybrid'] as $style)
                                <label class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:shadow-md {{ in_array($style, old('learning_style', [])) ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="checkbox" name="learning_style[]" value="{{ $style }}" class="sr-only" {{ in_array($style, old('learning_style', [])) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $style }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('learning_style')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                        <!-- Transition Confidence -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Confidence in Transitioning to University <span class="text-red-500">*</span></label>
                            <p class="text-xs text-gray-500 mb-3">1 - Not confident, 5 - Highly confident</p>
                            <div class="flex gap-3 justify-center">
                                @for($i=1; $i<=5; $i++)
                                    <label class="inline-flex items-center justify-center px-6 py-4 border-2 rounded-xl cursor-pointer w-20 text-center transition-all duration-200 hover:shadow-md {{ old('transition_confidence') == $i ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg scale-110' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                        <input type="radio" name="transition_confidence" id="confidence{{ $i }}" value="{{ $i }}" required class="hidden" {{ old('transition_confidence') == $i ? 'checked' : '' }}>
                                        <span class="text-lg font-bold">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                            @error('transition_confidence')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>

                    </div>

                    <div class="border-t-2 border-blue-100 pt-8"></div>

                    <!-- Section Header -->
                    <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                        <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">👥</div>
                        <h4 class="text-xl font-bold text-gray-800">Social & Personality Traits</h4>
                    </div>

                    <!-- Social Preference -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Social Preference <span class="text-red-500">*</span></label>
                        <div class="flex gap-3 flex-wrap">
                            @foreach(['1-on-1','Small Groups','Large Groups','Online-only'] as $p)
                                <label class="inline-flex items-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ old('social_preference') == $p ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="radio" name="social_preference" value="{{ $p }}" required class="hidden" {{ old('social_preference') == $p ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $p }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('social_preference')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <!-- Introvert-extrovert -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Introvert → Extrovert <span class="text-red-500">*</span></label>
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-medium text-gray-600 w-20">Introvert</span>
                                <input type="range" name="introvert_extrovert_scale" min="1" max="10" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#3182ce]" value="{{ old('introvert_extrovert_scale',5) }}" oninput="document.getElementById('introvertValue').textContent=this.value" required>
                                <span class="text-sm font-medium text-gray-600 w-20 text-right">Extrovert</span>
                                <div class="px-4 py-2 bg-[#3182ce] text-white rounded-lg font-bold shadow-md min-w-[3rem] text-center">
                                    <span id="introvertValue">{{ old('introvert_extrovert_scale',5) }}</span>
                                </div>
                            </div>
                        </div>
                        @error('introvert_extrovert_scale')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <!-- Stress Level -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Stress Level <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            @foreach(['Low','Moderate','High'] as $lvl)
                                <label class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ old('stress_level') == $lvl ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="radio" name="stress_level" value="{{ $lvl }}" required class="hidden" {{ old('stress_level') == $lvl ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $lvl }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('stress_level')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <!-- Group Work Comfort -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Comfort with Group Work <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-3">1 - Low, 5 - High</p>
                        <div class="flex gap-3 justify-center">
                            @for($i=1; $i<=5; $i++)
                                <label class="inline-flex items-center justify-center px-6 py-4 border-2 rounded-xl cursor-pointer w-20 text-center transition-all duration-200 hover:shadow-md {{ old('group_work_comfort') == $i ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg scale-110' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="radio" name="group_work_comfort" id="group{{ $i }}" value="{{ $i }}" required class="hidden" {{ old('group_work_comfort') == $i ? 'checked' : '' }}>
                                    <span class="text-lg font-bold">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        @error('group_work_comfort')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <!-- Communication Preferences -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Preferred Communication Methods <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            @foreach(['Texts','In-person','Calls'] as $method)
                                <label class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md {{ in_array($method, old('communication_preferences', [])) ? 'bg-[#3182ce] text-white border-[#3182ce] shadow-lg' : 'bg-white border-gray-200 hover:border-[#3182ce]' }}">
                                    <input type="checkbox" name="communication_preferences[]" value="{{ $method }}" class="sr-only" {{ in_array($method, old('communication_preferences', [])) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $method }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('communication_preferences')<p class="text-red-600 text-sm mt-1.5 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="group relative px-8 py-4 bg-[#3182ce] text-white rounded-xl font-bold text-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                            Continue to Next Step
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>