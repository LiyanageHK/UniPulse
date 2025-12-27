<x-app-layout>
    <div class="pt-2 pb-6">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="w-full bg-white shadow-lg rounded-2xl p-8 lg:p-12 space-y-10">

                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Weekly Check-In</h1>
                    <p class="text-gray-600 mt-2">Your weekly wellbeing assessment helps us provide better support</p>
                </div>

                <form action="{{ route('weekly.checkin.submit') }}" method="POST" class="space-y-10">
                    @csrf

                    <!-------------------------------- EMOTIONAL & WELLBEING ------------------------------>
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">🧠 Emotional & Mental Wellbeing</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">{{ $label }}</label>
                                <div class="flex gap-3">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}" 
                                                   class="text-blue-600 focus:ring-blue-500" required>
                                            <span class="px-3 py-1 rounded border bg-white">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- SOCIAL INCLUSION ------------------------------>
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">👥 Social Inclusion</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">{{ $label }}</label>
                                <div class="flex gap-3">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="text-blue-600 focus:ring-blue-500" required>
                                            <span class="px-3 py-1 rounded border bg-white">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Which group activities did you participate in  this week?</label>
                                <select name="group_participation" class="w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">None</option>
                                    <option>Study Groups</option>
                                    <option>Social Events</option>
                                    <option>Clubs</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-------------------------------- MOTIVATION ------------------------------>
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">🔥 Motivation</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @php
                            $motivation = [
                                'I found my studies interesting and engaging this week (1- Strongly disagree; 5-Strongly agree)' => 'studies_interesting',
                                'I felt confident in my ability to succeed academically (1- Strongly disagree; 5-Strongly agree)' => 'academic_confidence',
                                'I was able to keep up with my academic workload this week (1- Strongly disagree; 5-Strongly agree)' => 'workload_management',
                            ];
                            @endphp

                            @foreach($motivation as $label => $name)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">{{ $label }}</label>
                                <div class="flex gap-3">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="text-blue-600 focus:ring-blue-500" required>
                                            <span class="px-3 py-1 rounded border bg-white">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- BURNOUT & DEPRESSION ------------------------------>
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">😞 Burnout & Depression</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">{{ $label }}</label>
                                <div class="flex gap-3">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                   class="text-blue-600 focus:ring-blue-500" required>
                                            <span class="px-3 py-1 rounded border bg-white">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-------------------------------- SUBMIT ------------------------------>
                    <div class="flex justify-end pt-6 border-t">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                            Submit Weekly Check-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>