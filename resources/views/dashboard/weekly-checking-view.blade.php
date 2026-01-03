<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden px-8 py-6">
                <!-- Emotional & Mental Wellbeing -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-purple-200 pb-2">
                        Emotional & Mental Wellbeing - {{ $checking->created_at }}
                    </h2>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q1) Overall mood:</p>
                        <p class="text-gray-900">{{ $checking->overall_mood }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q2) Felt supported:</p>
                        <p class="text-gray-900">{{ $checking->felt_supported }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q3) Emotion describing week:</p>
                        <p class="text-gray-900">{{ ucfirst($checking->emotion_description) }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q4) Trouble sleeping:</p>
                        <p class="text-gray-900">{{ $checking->trouble_sleeping }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q5) Hard to focus:</p>
                        <p class="text-gray-900">{{ $checking->hard_to_focus }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q6) Open to counselor:</p>
                        <p class="text-gray-900">{{ $checking->open_to_counselor }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q7) Know how to access support:</p>
                        <p class="text-gray-900">{{ $checking->know_access_support }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q8) Feeling tense:</p>
                        <p class="text-gray-900">{{ $checking->feeling_tense }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q9) Worrying:</p>
                        <p class="text-gray-900">{{ $checking->worrying }}/5</p>
                    </div>
                </div>

                <!-- Social & Academic Behavior -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-purple-200 pb-2">
                        Social & Academic Behavior
                    </h2>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q10) Interaction with peers:</p>
                        <p class="text-gray-900">{{ $checking->interact_peers }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q11) Keeping up with workload:</p>
                        <p class="text-gray-900">{{ $checking->keep_up_workload }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q12) Group activities:</p>
                        <p class="text-gray-900">
                            {{ implode(', ', json_decode($checking->group_activities, true)) }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q13) Academic challenges:</p>
                        <p class="text-gray-900">
                            {{ implode(', ', json_decode($checking->academic_challenges, true)) }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q14) Feeling left out:</p>
                        <p class="text-gray-900">{{ $checking->feel_left_out }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q15) No one to talk to:</p>
                        <p class="text-gray-900">{{ $checking->no_one_to_talk }}/5</p>
                    </div>
                </div>

                <!-- Depressive Feelings & Low Mood -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-purple-200 pb-2">
                        Depressive Feelings & Low Mood
                    </h2>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q16) No energy/motivation:</p>
                        <p class="text-gray-900">{{ $checking->no_energy }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q17) Little pleasure:</p>
                        <p class="text-gray-900">{{ $checking->little_pleasure }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q18) Feeling down/hopeless:</p>
                        <p class="text-gray-900">{{ $checking->feeling_down }}/5</p>
                    </div>
                </div>

                <!-- Disengagement & Burnout -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-purple-200 pb-2">
                        Disengagement & Burnout
                    </h2>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q19) Emotionally drained:</p>
                        <p class="text-gray-900">{{ $checking->emotionally_drained }}/5</p>
                    </div>

                    <div class="mb-4">
                        <p class="font-semibold text-gray-700">Q20) Going through motions:</p>
                        <p class="text-gray-900">{{ $checking->going_through_motions }}/5</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
