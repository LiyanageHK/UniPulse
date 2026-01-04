<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Header Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Weekly Check-In Form</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                This weekly check-in helps us understand your wellbeing and provide timely support. Your responses will help us:
            </p>
            <div class="mt-6 grid md:grid-cols-2 gap-4 max-w-2xl mx-auto text-left">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Monitor your mental and emotional wellbeing</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Identify students who may need support</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Track your progress over time</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700">Connect you with appropriate resources</span>
                </div>
            </div>
            <div class="mt-6 inline-flex items-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="text-sm text-blue-800 font-medium">Your responses are completely confidential</span>
            </div>
            <p class="text-sm text-gray-500 mt-3">This should take about 3-5 minutes to complete. Please answer honestly.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
             <form method="POST" action="{{ route('survey-store') }}" class="px-8 py-6">
                @csrf
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-purple-200">
                        Emotional & Mental Wellbeing
                    </h2>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            This week, my overall mood was <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Very negative</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="overall_mood"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Very positive</span>
                        </div>
                        @error('overall_mood')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I felt supported by my peers/university this week <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="felt_supported"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('felt_supported')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            What emotion best describes your week? <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('emotion_description') border-red-500 @enderror"
                                name="emotion_description"
                                required>
                            <option value="">Choose...</option>
                            <option value="anxious" {{ old('emotion_description') == 'anxious' ? 'selected' : '' }}>Anxious</option>
                            <option value="calm" {{ old('emotion_description') == 'calm' ? 'selected' : '' }}>Calm</option>
                            <option value="lonely" {{ old('emotion_description') == 'lonely' ? 'selected' : '' }}>Lonely</option>
                            <option value="excited" {{ old('emotion_description') == 'excited' ? 'selected' : '' }}>Excited</option>
                            <option value="overwhelmed" {{ old('emotion_description') == 'overwhelmed' ? 'selected' : '' }}>Overwhelmed</option>
                            <option value="neutral" {{ old('emotion_description') == 'neutral' ? 'selected' : '' }}>Neutral</option>
                            <option value="other" {{ old('emotion_description') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('emotion_description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I had trouble sleeping because of stress or thoughts <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="trouble_sleeping"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('trouble_sleeping')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I found it hard to stay focused on academic tasks <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="hard_to_focus"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('hard_to_focus')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I would be open to talking to a mentor or counselor if I needed help <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="open_to_counselor"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('open_to_counselor')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I know how to access mental health support if I needed it <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="know_access_support"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('know_access_support')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I've been feeling tense or unable to relax <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Never</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="feeling_tense"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Always</span>
                        </div>
                        @error('feeling_tense')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I've been worrying about many things lately <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Not at all</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="worrying"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Very much</span>
                        </div>
                        @error('worrying')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-purple-200">
                        Social & Academic Behavior
                    </h2>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            How often did you interact with peers outside class this week? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Not at all</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="interact_peers"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Daily</span>
                        </div>
                        @error('interact_peers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I was able to keep up with my academic workload this week <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="keep_up_workload"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('keep_up_workload')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Which group activities did you participate in this week? <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="group_activities[]" value="study groups" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Study groups</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="group_activities[]" value="social events" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Social events</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="group_activities[]" value="clubs" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Clubs</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="group_activities[]" value="none" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">None</span>
                            </label>
                        </div>
                        @error('group_activities')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            What academic challenges did you face this week? <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="assignments" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Assignments</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="exams" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Exams</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="group work" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Group work</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="understanding lessons" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Understanding lessons</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="time management" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">Time management</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="academic_challenges[]" value="none" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-3 text-gray-700">None</span>
                            </label>
                            <div class="flex items-start p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <input type="checkbox" value="other" id="challenge_other_check" class="w-4 h-4 mt-1 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                       onchange="document.getElementById('challenge_other_input').disabled = !this.checked">
                                <div class="ml-3 flex-1">
                                    <label for="challenge_other_check" class="text-gray-700 cursor-pointer">Other:</label>
                                    <input type="text"
                                           id="challenge_other_input"
                                           name="academic_challenges_other"
                                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="Please specify"
                                           disabled>
                                </div>
                            </div>
                        </div>
                        @error('academic_challenges')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I often feel left out or disconnected from others <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Never</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="feel_left_out"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Always</span>
                        </div>
                        @error('feel_left_out')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I feel like I don't have anyone to talk to when I'm struggling <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="no_one_to_talk"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('no_one_to_talk')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-purple-200">
                        Depressive Feelings & Low Mood
                    </h2>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I often feel like I have no energy or motivation <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Never</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="no_energy"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">All the time</span>
                        </div>
                        @error('no_energy')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I find little pleasure or enjoyment in things I used to like <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="little_pleasure"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('little_pleasure')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I've been feeling down, hopeless, or sad most of the time <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Not at all</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="feeling_down"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Extremely</span>
                        </div>
                        @error('feeling_down')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-purple-200">
                        Disengagement & Burnout
                    </h2>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I feel emotionally drained by my studies <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Not at all</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="emotionally_drained"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Completely</span>
                        </div>
                        @error('emotionally_drained')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I feel like I'm just going through the motions without interest <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-gray-500 w-20 text-left">Strongly disagree</span>
                            <div class="flex gap-2 flex-1 justify-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer">
                                        <input type="radio"
                                               class="peer sr-only"
                                               name="going_through_motions"
                                               value="{{ $i }}"
                                               required>
                                        <div class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg text-gray-600 font-semibold peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 hover:border-purple-400 transition">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500 w-20 text-right">Strongly agree</span>
                        </div>
                        @error('going_through_motions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit"
                            class="w-full bg-purple-600 text-white px-6 py-4 rounded-lg font-semibold text-lg hover:bg-purple-700 transition shadow-md hover:shadow-lg">
                        Submit Check-In
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Need help? Contact us at <a href="mailto:support@wellbeinghub.com" class="text-purple-600 hover:underline">support@wellbeinghub.com</a></p>
        </div>
    </div>
</div>

<style>
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

    .accent-blue-600::-webkit-slider-thumb {
        background: #2563eb;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    .accent-blue-600::-moz-range-thumb {
        background: #2563eb;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    .accent-green-600::-webkit-slider-thumb {
        background: #16a34a;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
    }

    .accent-green-600::-moz-range-thumb {
        background: #16a34a;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
    }

    .accent-orange-600::-webkit-slider-thumb {
        background: #ea580c;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
    }

    .accent-orange-600::-moz-range-thumb {
        background: #ea580c;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
    }
</style>

</x-app-layout>
