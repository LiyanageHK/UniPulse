<x-app-layout>
<div class="container py-5">
    <div class="col-lg-10 mx-auto">

        <div class="card shadow-lg rounded-4">
            <div class="card-header text-white text-center py-4" style="background:#3182ce">
                <h3>Step 1 of 2 — Academic & Social Details</h3>
            </div>

            <div class="card-body p-5 bg-light">
                <form method="POST" action="{{ route('onboarding.step1.store') }}">
                    @csrf

                    <h4 class="text-primary fw-bold mb-5">📘 Academic & Demographic Details</h4>

                    <div class="row g-3">

                        <!-- Full Name -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="Enter your full name" required>
                        </div>

                        <!-- University -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">University *</label>
                            <select class="form-select form-select-lg" id="university" name="university" required>
                                <option value="">Select University</option>
                                <option value="SLIIT">Sri Lanka Institute of Information Technology</option>
                                <option value="NSBM">NSBM Green University</option>
                                <option value="IIT">Informatics Institute of Technology</option>
                                <option value="University of Colombo">University of Colombo</option>
                                <option value="University of Sri Jayewardenepura">University of Sri Jayewardenepura</option>
                                <option value="University of Kelaniya">University of Kelaniya</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Faculty -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Faculty *</label>
                            <select class="form-select form-select-lg" id="faculty" name="faculty" required>
                                <option value="">Select Faculty</option>
                                <option value="Faculty of Computing">Faculty of Computing</option>
                                <option value="Faculty of Engineering">Faculty of Engineering</option>
                                <option value="Faculty of Business">Faculty of Business</option>
                                <option value="Faculty of Science">Faculty of Science</option>
                                <option value="Faculty of Humanities">Faculty of Humanities & Social Sciences</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- AL Stream -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold">A/L Stream *</label>
                            <select class="form-select form-select-lg" id="al_stream" name="al_stream" required>
                                <option value="">Select Stream</option>
                                <option value="Bio Science">Bio Science</option>
                                <option value="Physical Science">Physical Science</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Arts">Arts</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- AL Subjects & Results -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-semibold">A/L Subjects & Results *</label>

                            <div class="row g-3 align-items-center">

                                @for($i=1; $i<=5; $i++)
                                <div class="col-md-4 d-flex gap-2">
                                    <input type="text" class="form-control" name="al_subject_{{ $i }}" placeholder="Subject {{ $i }}" {{ $i <= 3 ? 'required' : '' }}>
                                    
                                    <select class="form-select w-25" name="al_grade_{{ $i }}" {{ $i <= 3 ? 'required' : '' }}>
                                        <option value="">Grade</option>
                                        @foreach(['A','B','C','S','F'] as $grade)
                                            <option>{{ $grade }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endfor

                            </div>
                        </div>

                        <!-- Learning Style -->
                        <div class="col-12 mt-4">
                            <label class="form-label fw-semibold">Preferred Learning Style *</label>

                            <div class="d-flex flex-wrap gap-3 mt-2">
                                @foreach(['Physical','Online','Hybrid'] as $style)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="learning_style[]" value="{{ $style }}">
                                    <label class="form-check-label">{{ $style }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Transition Confidence -->
                        <div class="col-12 mt-4">
                            <label class="form-label fw-semibold">
                                Confidence in Transitioning to University (1 - Not confident, 5 - Highly confident) *
                            </label>

                            <div class="btn-group mt-2" role="group">
                                @for($i=1; $i<=5; $i++)
                                    <input type="radio" class="btn-check" name="transition_confidence" id="confidence{{ $i }}" value="{{ $i }}" required>
                                    <label class="btn btn-outline-primary py-4 mx-4" for="confidence{{ $i }}">{{ $i }}</label>
                                @endfor
                            </div>
                        </div>

                    </div>

                    <hr class="my-4">

                    <!-- SOCIAL & PERSONALITY -->
                    <h4 class="text-primary fw-bold mb-3">👥 Social & Personality Traits</h4>

                    <!-- Social Preference -->
                    <label class="form-label">Preferred Social Setting *</label><br>
                    @foreach(['1-on-1','Small Groups','Large Groups','Online-only'] as $p)
                        <label class="me-3">
                            <input type="radio" name="social_preference" value="{{ $p }}" required> {{ $p }}
                        </label>
                    @endforeach

                    <!-- Introvert-extrovert -->
                    <div class="mt-4 mb-4">
                        <label class="form-label">Introvert → Extrovert *</label>
                        <input type="range" name="introvert_extrovert_scale" min="1" max="10" class="form-range" required>
                    </div>

                    <!-- Stress Level -->
                    <label class="form-label">Stress Level *</label><br>
                    @foreach(['Low','Moderate','High'] as $lvl)
                        <label class="me-3">
                            <input type="radio" name="stress_level" value="{{ $lvl }}" required> {{ $lvl }}
                        </label>
                    @endforeach

                    <!-- Group Work Comfort -->
                    <div class="col-md-6 mt-4">
                        <label class="form-label fw-semibold mt-2">Comfort with Group Work (1 - Low, 5 - High) *</label>

                        <div class="btn-group w-100 mt-2" role="group">
                            @for($i=1; $i<=5; $i++)
                                <input type="radio" class="btn-check" name="group_work_comfort" id="group{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary" for="group{{ $i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    </div>

                    <!-- Communication Preferences -->
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold">Preferred Communication Methods *</label><br>

                        @foreach(['Texts','In-person','Calls'] as $method)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="communication_preferences[]" value="{{ $method }}">
                            <label class="form-check-label">{{ $method }}</label>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary px-4">Continue →</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
</x-app-layout>
