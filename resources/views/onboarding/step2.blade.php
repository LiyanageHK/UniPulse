<x-app-layout>
<div class="container py-5">
    <div class="col-lg-10 mx-auto">

        <div class="card shadow-lg rounded-4">
            <div class="card-header text-white text-center py-4" style="background:#3182ce">
                <h3>Step 2 of 2 — Lifestyle & Wellbeing</h3>
            </div>

            <div class="card-body p-5 bg-light">
                <form method="POST" action="{{ route('onboarding.step2.store') }}">
                    @csrf

                    <h4 class="text-primary fw-bold mb-3">🌱 Interests & Lifestyle</h4>

                    <div>
                    <label class="form-label">Primary Motivator for University Life *</label>
                    <select name="primary_motivator" class="form-select mb-3" required>
                        <option value="">Select</option>
                        <option>Academic growth</option>
                        <option>Career opportunities</option>
                        <option>Friends and connections</option>
                        <option>Experiences and exposure</option>
                    </select>
                    </div>

                    <label class="form-label">Goal Clarity (1- Strongly disagree ; 5- Strongly agree) *</label>
                    <div class="btn-group w-100 mb-4">
                        @for($i=1;$i<=5;$i++)
                            <input type="radio" class="btn-check" id="goal{{ $i }}" name="goal_clarity" value="{{ $i }}" required>
                            <label class="btn btn-outline-primary" for="goal{{ $i }}">{{ $i }}</label>
                        @endfor
                    </div>

                    <!-- Top Interests --> 
                     <div class="col-12 mt-3"> 
                        <label class="form-label fw-semibold">Top Interests *</label>
                        <br> @foreach(['Sports', 'Arts', 'Tech', 'Reading', 'Social Events', 'Other'] as $interests) 
                        <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox" name="interests[]" id="interests_{{ $interests }}" value="{{ $interests }}"> 
                        <label class="form-check-label" for="interest_{{ $interests }}">{{ $interests }}</label> 
                    </div> 
                    @endforeach 
                </div>

                <!-- Hobbies --> 
                     <div class="col-12 mt-3"> 
                        <label class="form-label fw-semibold">Hobbies *</label>
                        <br> @foreach(['Reading', 'Watching Dramas', 'Sports', 'Painting', 'Travelling', 'Volunteering', 'Gaming', 'Listening to Music'] as $hobbies) 
                        <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox" name="hobbies[]" id="hobbies_{{ $hobbies }}" value="{{ $hobbies }}"> 
                        <label class="form-check-label" for="hobbies_{{ $hobbies }}">{{ $hobbies }}</label> 
                    </div> 
                    @endforeach 
                </div>
                <!-- Living Arrangement & Employment --> 
                 <div class="col-md-6 mt-3"> 
                    <label class="form-label fw-semibold">Living Arrangement *</label><br> 
                    @foreach(['Hostel', 'Home', 'Boarding', 'Other'] as $living_arrangement) 
                    <div class="form-check form-check-inline"> 
                        <input class="form-check-input" type="radio" name="living_arrangement" id="living_{{ $living_arrangement }}" value="{{ $living_arrangement }}" required> 
                        <label class="form-check-label" for="living_{{ $living_arrangement }}">{{ $living_arrangement }}</label> 
                    </div> @endforeach 
                </div>

                <div class="col-md-6 mt-3"> 
                    <label class="form-label fw-semibold">Are you currently employed? *</label><br> 
                    <div class="form-check form-check-inline"> 
                        <input class="form-check-input" type="radio" name="is_employed" id="empYes" value="1" required> 
                        <label class="form-check-label" for="empYes">Yes</label> </div> <div class="form-check form-check-inline"> 
                            <input class="form-check-input" type="radio" name="is_employed" id="empNo" value="0"> 
                            <label class="form-check-label" for="empNo">No</label> 
                        </div> 
                    </div> 
                </div> 
            </div>

                    <hr>
                    <h4 class="text-primary fw-bold mb-3">💬 Wellbeing</h4>

                    @php
                        $questions = [
                            'I often feel overwhelmed or anxious.'=>'overwhelm_level',
                            'I struggle to connect with peers.'=>'peer_struggle',
                            'I would use an AI platform for wellbeing support.'=>'ai_openness'
                        ];
                    @endphp

                    @foreach($questions as $text => $name)
                        <label class="form-label">{{ $text }}</label>
                        <div class="btn-group w-100 mb-3">
                            @for($i=1;$i<=5;$i++)
                                <input type="radio" class="btn-check" id="{{ $name.$i }}" name="{{ $name }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary" for="{{ $name.$i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    @endforeach

                    <!-- Preferred Support --> 
                     <div class="col-12 mt-3"> 
                        <label class="form-label fw-semibold">Preferred Support Methods *</label><br> 
                        @foreach(['Peer Matching', 'Counseling', 'Study Groups', 'Chatbot'] as $preferred_support_types) 
                        <div class="form-check form-check-inline"> 
                            <input class="form-check-input" type="checkbox" name="preferred_support_types[]" id="preferred_support_types_{{ $preferred_support_types }}" value="{{ $preferred_support_types }}"> 
                            <label class="form-check-label" for="preferred_support_types_{{ $preferred_support_types }}">{{ $preferred_support_types }}</label> 
                        </div> 
                        @endforeach 
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary px-4">
                            Complete Profile →
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
</x-app-layout>
