<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden">

                <!-- Header Section -->
                <div class="bg-gradient-to-r from-[#3182ce] to-blue-600 p-8 text-center">
                    <div class="inline-block p-3 bg-white/20 rounded-xl mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Weekly Check-In</h1>
                    <p class="text-blue-100">Your weekly wellbeing assessment helps us provide better support</p>
                </div>

                <form action="{{ route('weekly.checkin.submit') }}" method="POST" class="p-8 lg:p-12 space-y-10">
                    @csrf

                    <style>
                        /* Radio button styling */
                        input[type="radio"]:checked + span {
                            background-color: #3182ce !important;
                            color: white !important;
                            border-color: #3182ce !important;
                            box-shadow: 0 4px 6px -1px rgba(49, 130, 206, 0.3);
                        }
                        label:has(input[type="radio"]:checked) span {
                            background-color: #3182ce !important;
                            color: white !important;
                            border-color: #3182ce !important;
                        }
                    </style>

                    <!-------------------------------- EMOTIONAL & WELLBEING ------------------------------>
                    <div class="space-y-6">
                        <!-- Section Header -->
                        <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                            <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">🧠</div>
                            <h3 class="text-xl font-bold text-gray-800">Emotional & Mental Wellbeing</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            @php
                            $emotional = [
                                'This week, my overall mood was(1- Very Negative; 5- Very Positive) :' => 'mood',
                                'I have been feeling tense or unable to relax(1-Never; 5-Always)' => 'tense',
                                'I get overwhelmed easily by academic tasks(1-Never; 5-Always)' => 'overwhelmed',
                                'I have been worrying about many things lately(1-Not at all; 5-Very much)' => 'worry',
                                'I had trouble sleeping because of stress or thoughts(1-Strongly disagree; 5-Strongly agree)' => 'sleep_trouble',
                                'I would be open to talking to a mentor or counselor if I needed help (1-Not Open; 5-Very Open)' => 'openness_to_mentor',
                                'I know how to access mental health support if I needed it (1-Not Knowledgeable; 5-Very Knowledgeable)' => 'knowledge_of_support'
                            ];
                            @endphp

                            @foreach($emotional as $label => $name)
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">{{ $label }} <span class="text-red-500">*</span></label>
                                <div class="flex gap-3 justify-center flex-wrap">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="inline-flex items-center justify-center cursor-pointer transition-all duration-200 hover:shadow-md">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}" 
                                                   class="sr-only" required>
                                            <span class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white font-semibold text-gray-700 min-w-[3rem] text-center transition-all duration-200">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- SOCIAL INCLUSION ------------------------------>
                    <div class="space-y-6">
                        <!-- Section Header -->
                        <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                            <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">👥</div>
                            <h3 class="text-xl font-bold text-gray-800">Social Inclusion</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            @php
                            $social = [
                                'I feel connected to at least one friend or peer in university (1-Strongly disagree; 5-Strongly agree)' => 'peer_connection',
                                'How often did you interact with peers outside class this week? (1-not at all; 5-Daily)' => 'peer_interaction',
                                'I often feel left out or disconnected from others (1-Never; 5-Always)' => 'feel_left_out',
                                'I feel like I do not have anyone to talk to when I am struggling (1-Strongly disagree; 5-Strongly agree)' => 'no_one_to_talk',
                                'I felt I belonged to the university community (1-Strongly disagree; 5-Strongly agree)' => 'university_belonging',
                                'I had meaningful connections with peers this week (1-Strongly disagree; 5-Strongly agree)' => 'meaningful_connections'
                            ];
                            @endphp

                            @foreach($social as $label => $name)
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">{{ $label }} <span class="text-red-500">*</span></label>
                                <div class="flex gap-3 justify-center flex-wrap">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="inline-flex items-center justify-center cursor-pointer transition-all duration-200 hover:shadow-md">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="sr-only" required>
                                            <span class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white font-semibold text-gray-700 min-w-[3rem] text-center transition-all duration-200">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach

                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Which group activities did you participate in this week?</label>
                                <select name="group_participation" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200">
                                    <option value="">None</option>
                                    <option>Study Groups</option>
                                    <option>Social Events</option>
                                    <option>Clubs</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-------------------------------- MOTIVATION ------------------------------>
                    <div class="space-y-6">
                        <!-- Section Header -->
                        <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                            <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">🔥</div>
                            <h3 class="text-xl font-bold text-gray-800">Motivation</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            @php
                            $motivation = [
                                'I found my studies interesting and engaging this week (1- Strongly disagree; 5-Strongly agree)' => 'studies_interesting',
                                'I felt confident in my ability to succeed academically (1- Strongly disagree; 5-Strongly agree)' => 'academic_confidence',
                                'I was able to keep up with my academic workload this week (1- Strongly disagree; 5-Strongly agree)' => 'workload_management',
                            ];
                            @endphp

                            @foreach($motivation as $label => $name)
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">{{ $label }} <span class="text-red-500">*</span></label>
                                <div class="flex gap-3 justify-center flex-wrap">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="inline-flex items-center justify-center cursor-pointer transition-all duration-200 hover:shadow-md">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="sr-only" required>
                                            <span class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white font-semibold text-gray-700 min-w-[3rem] text-center transition-all duration-200">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- BURNOUT & DEPRESSION ------------------------------>
                    <div class="space-y-6">
                        <!-- Section Header -->
                        <div class="flex items-center gap-3 pb-4 border-b-2 border-blue-100">
                            <div class="w-10 h-10 bg-[#3182ce] rounded-lg flex items-center justify-center text-white text-xl">😞</div>
                            <h3 class="text-xl font-bold text-gray-800">Burnout & Depression</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            @php
                            $burnout = [
                                'I often feel like I have no energy or motivation (1- Never; 5-All the time)' => 'no_energy',
                                'I find little pleasure or enjoyment in things I used to like (1- Strongly disagree; 5-Strongly agree)' => 'low_pleasure',
                                'I have been feeling down, hopeless, or sad most of the time (1- Strongly disagree; 5-Strongly agree)' => 'feeling_down',
                                'I feel emotionally drained by my studies (1- Not at all; 5-Completely)' => 'emotionally_drained',
                                'I find it hard to stay focused on academic tasks (1- Strongly disagree; 5-Strongly agree)' => 'hard_to_stay_focused',
                                'I feel like I am just going through the motions without interest (1- Strongly disagree; 5-Strongly agree)' => 'just_through_motions' 
                            ];
                            @endphp  

                            @foreach($burnout as $label => $name)
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">{{ $label }} <span class="text-red-500">*</span></label>
                                <div class="flex gap-3 justify-center flex-wrap">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="inline-flex items-center justify-center cursor-pointer transition-all duration-200 hover:shadow-md">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="sr-only" required>
                                            <span class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white font-semibold text-gray-700 min-w-[3rem] text-center transition-all duration-200">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- SUBMIT ------------------------------>
                    <div class="flex justify-end pt-6 border-t-2 border-blue-100">
                        <button type="submit" class="group relative px-8 py-4 bg-[#3182ce] text-white rounded-xl font-bold text-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                            Submit Weekly Check-In
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>