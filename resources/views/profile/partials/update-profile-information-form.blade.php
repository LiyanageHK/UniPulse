

    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Edit Profile
        </h2>
    </x-slot>

    <div class="pt-2 pb-6">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="w-full bg-white shadow-lg rounded-2xl p-12 lg:p-16 space-y-10">

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))
                    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm w-full">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ERROR MESSAGES --}}
                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm w-full">
                        <strong>There were errors:</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-10">
                    @csrf
                    @method('patch')

                    @php
                        $alResults = is_array($user->al_results) ? $user->al_results : json_decode($user->al_results ?? '[]', true);
                        $learningStyle = is_array($user->learning_style) ? $user->learning_style : json_decode($user->learning_style ?? '[]', true);
                        $communication = is_array($user->communication_preferences) ? $user->communication_preferences : json_decode($user->communication_preferences ?? '[]', true);
                        $interests = is_array($user->interests) ? $user->interests : json_decode($user->interests ?? '[]', true);
                        $hobbies = is_array($user->hobbies) ? $user->hobbies : json_decode($user->hobbies ?? '[]', true);
                        $preferredSupport = is_array($user->preferred_support_types) ? $user->preferred_support_types : json_decode($user->preferred_support_types ?? '[]', true);
                    @endphp


                    <!-- ================= SECTION 1 — Academic Profile ================= -->
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-800">📚 Academic & Demographic</h3>
                            <p class="text-sm text-gray-500 mt-1">Your academic details help us tailor your platform experience.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Full Name</label>
                                <input type="text" name="name"
                                       value="{{ old('name',$user->name) }}"
                                       class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Email</label>
                                    <input type="email" name="email"
                                           value="{{ old('email',$user->email) }}"
                                           class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                            {{-- University --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">University</label>
                                <select name="university"
                                        class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select University</option>
                                    @foreach(['SLIIT'=>'SLIIT','NSBM'=>'NSBM','IIT'=>'IIT','University of Colombo','University of Kelaniya','Other'] as $key=>$val)
                                        <option value="{{ is_string($key)?$key:$val }}"
                                            {{ old('university',$user->university)==(is_string($key)?$key:$val)?'selected':'' }}>
                                            {{ $val }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Faculty --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Faculty</label>
                                <select name="faculty" class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Faculty</option>
                                    @foreach(['Faculty of Computing','Faculty of Engineering','Faculty of Business','Faculty of Science'] as $f)
                                        <option value="{{ $f }}" {{ old('faculty',$user->faculty)==$f?'selected':'' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- AL Stream --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">A/L Stream</label>
                                <select name="al_stream" class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Stream</option>
                                    @foreach(['Bio Science','Physical Science','Commerce','Arts','Other'] as $s)
                                        <option value="{{ $s }}" {{ old('al_stream',$user->al_stream)==$s?'selected':'' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        {{-- AL subjects --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">A/L Subjects & Grades</label>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @for($i=1;$i<=5;$i++)
                                    @php
                                        $key="subject_$i";
                                        $subject = $alResults[$key]['subject'] ?? '';
                                        $grade = $alResults[$key]['grade'] ?? '';
                                    @endphp

                                    <div class="flex gap-3 items-center">
                                        <input type="text"
                                               name="al_subject_{{ $i }}"
                                               placeholder="Subject {{ $i }}"
                                               value="{{ $subject }}"
                                               class="w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">

                                        <select name="al_grade_{{ $i }}"
                                                class="w-28 border rounded-lg px-2 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Grade</option>
                                            @foreach(['A','B','C','S','F'] as $g)
                                                <option value="{{ $g }}" {{ $grade==$g?'selected':'' }}>{{ $g }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endfor
                            </div>
                        </div>

                    </div>


                    <!-- ================= SECTION 2 — Learning & Social ================= -->
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">🧭 Learning & Social</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Learning Style --}}
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Preferred Learning Style</label>
                                <div class="flex flex-wrap gap-3 mt-2">
                                    @foreach(['Online','Physical','Hybrid'] as $ls)
                                        <label class="flex items-center gap-2 px-3 py-1 rounded-lg border cursor-pointer
                                            {{ in_array($ls,$learningStyle)?'bg-indigo-50 border-indigo-300':'bg-white' }}">
                                            <input type="checkbox" name="learning_style[]" value="{{ $ls }}" {{ in_array($ls,$learningStyle)?'checked':'' }}>
                                            <span class="text-sm">{{ $ls }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Transition Confidence --}}
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Confidence transitioning to university</label>
                                <div class="flex gap-3 mt-2">
                                    @for($i=1;$i<=5;$i++)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="transition_confidence" value="{{ $i }}"
                                                   {{ $user->transition_confidence==$i?'checked':'' }}>
                                            <span class="px-3 py-1 rounded border">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            {{-- Social setting --}}
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Preferred Social Setting</label>
                                <div class="flex flex-wrap gap-3 mt-3">
                                    @foreach(['1-on-1','Small Groups','Large Groups','Online-only'] as $p)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="social_preference" value="{{ $p }}"
                                                {{ $user->social_preference==$p?'checked':'' }}>
                                            <span class="text-sm">{{ $p }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Introvert scale --}}
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Introvert — Extrovert (1–10)</label>
                                <input
                                    type="range"
                                    name="introvert_extrovert_scale"
                                    min="1"
                                    max="10"
                                    value="{{ $user->introvert_extrovert_scale ?? 5 }}"
                                    class="w-full accent-blue-600 mt-3">
                            </div>

                            <div>
    <label class="text-sm font-semibold text-gray-700">Stress Level</label>
    <div class="flex gap-4 mt-2">
        @foreach(['Low','Moderate','High'] as $level)
            <label class="flex items-center gap-2">
                <input type="radio" name="stress_level" value="{{ $level }}"
                    {{ $user->stress_level == $level ? 'checked' : '' }}>
                <span>{{ $level }}</span>
            </label>
        @endforeach
    </div>
</div>

<div>
    <label class="text-sm font-semibold text-gray-700">Comfort with Group Work (1–5)</label>
    <div class="flex gap-3 mt-2">
        @for($i=1;$i<=5;$i++)
            <label class="flex items-center gap-2">
                <input type="radio" name="group_work_comfort" value="{{ $i }}"
                    {{ $user->group_work_comfort == $i ? 'checked' : '' }}>
                <span class="px-3 py-1 border rounded">{{ $i }}</span>
            </label>
        @endfor
    </div>
</div>

<div class="md:col-span-2">
    <label class="text-sm font-semibold text-gray-700">Preferred Communication Methods</label>
    <div class="flex flex-wrap gap-3 mt-2">
        @foreach(['Texts','In-person','Calls'] as $method)
            <label class="flex items-center gap-2 px-3 py-1 rounded-lg border
                {{ in_array($method,$communication) ? 'bg-blue-50 border-blue-300' : '' }}">
                <input type="checkbox" name="communication_preferences[]" value="{{ $method }}"
                    {{ in_array($method,$communication) ? 'checked' : '' }}>
                {{ $method }}
            </label>
        @endforeach
    </div>
</div>


                        </div>

                    </div>


                    <!-- ================= SECTION 3 — Interests & Lifestyle ================= -->
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">🌱 Interests & Lifestyle</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Motivator --}}
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Primary Motivator</label>
                                <select name="primary_motivator"
                                        class="mt-2 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select</option>
                                    @foreach(['Academic growth','Career opportunities','Friends and connections','Experiences and exposure','Other'] as $m)
                                        <option value="{{ $m }}" {{ $user->primary_motivator==$m?'selected':'' }}>
                                            {{ $m }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        {{-- Goal clarity --}}
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Goal Clarity (1–5)</label>
                            <div class="flex gap-3 mt-2">
                                @for($i=1;$i<=5;$i++)
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="goal_clarity" value="{{ $i }}"
                                            {{ $user->goal_clarity==$i?'checked':'' }}>
                                        <span class="px-3 py-1 rounded border">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>


                        {{-- Interests --}}
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Top Interests</label>
                            <div class="flex flex-wrap gap-3 mt-3">
                                @foreach(['Sports','Arts','Technology','Reading','Social events','Other'] as $it)
                                    <label class="flex items-center gap-2 px-3 py-1 rounded-lg border cursor-pointer
                                        {{ in_array($it,$interests)?'bg-yellow-50 border-yellow-300':'bg-white' }}">
                                        <input type="checkbox" name="interests[]" value="{{ $it }}" {{ in_array($it,$interests)?'checked':'' }}>
                                        <span class="text-sm">{{ $it }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Hobbies --}}
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Hobbies</label>
                            <div class="flex flex-wrap gap-3 mt-3">
                                @foreach(['Reading','Watching Dramas','Sports','Painting','Travelling','Volunteering','Gaming','Listening to music','Other'] as $h)
                                    <label class="flex items-center gap-2 px-3 py-1 rounded-lg border cursor-pointer
                                        {{ in_array($h,$hobbies)?'bg-purple-50 border-purple-300':'bg-white' }}">
                                        <input type="checkbox" name="hobbies[]" value="{{ $h }}" {{ in_array($h,$hobbies)?'checked':'' }}>
                                        <span class="text-sm">{{ $h }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
    <label class="text-sm font-semibold text-gray-700">Living Arrangement</label>
    <div class="flex gap-4 mt-2">
        @foreach(['Hostel','Home','Boarding','Other'] as $place)
            <label class="flex items-center gap-2">
                <input type="radio" name="living_arrangement" value="{{ $place }}"
                    {{ $user->living_arrangement == $place ? 'checked' : '' }}>
                {{ $place }}
            </label>
        @endforeach
    </div>
</div>

<div>
    <label class="text-sm font-semibold text-gray-700">Currently Employed?</label>
    <div class="flex gap-6 mt-2">
        <label class="flex items-center gap-2">
            <input type="radio" name="is_employed" value="1"
                {{ $user->is_employed == 1 ? 'checked' : '' }}>
            Yes
        </label>

        <label class="flex items-center gap-2">
            <input type="radio" name="is_employed" value="0"
                {{ $user->is_employed == 0 ? 'checked' : '' }}>
            No
        </label>
    </div>
</div>


                    </div>


                    <!-- ================= SECTION 4 — Wellbeing & Support ================= -->
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-xl space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-800">💬 Wellbeing & Support</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            @php
                                $wellQuestions=[
                                    'I often feel overwhelmed or anxious.'=>'overwhelm_level',
                                    'I struggle to connect with peers.'=>'peer_struggle',
                                    'I would use an AI platform for wellbeing support.'=>'ai_openness'
                                ];
                            @endphp

                            @foreach($wellQuestions as $label=>$name)
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">{{ $label }}</label>
                                    <div class="flex gap-3 mt-2">
                                        @for($i=1;$i<=5;$i++)
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="{{ $name }}" value="{{ $i }}"
                                                    {{ $user->{$name}==$i?'checked':'' }}>
                                                <span class="px-3 py-1 rounded border">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        {{-- Support types --}}
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Preferred Support Types</label>
                            <div class="flex flex-wrap gap-3 mt-3">
                                @foreach(['Peer Matching','Counseling','Study Groups','Chatbot'] as $pst)
                                    <label class="flex items-center gap-2 px-3 py-1 rounded-lg border cursor-pointer
                                        {{ in_array($pst,$preferredSupport)?'bg-green-50 border-green-300':'bg-white' }}">
                                        <input type="checkbox" name="preferred_support_types[]" value="{{ $pst }}"
                                            {{ in_array($pst,$preferredSupport)?'checked':'' }}>
                                        <span class="text-sm">{{ $pst }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>



                    <!-- ================= SUBMIT AREA ================= -->
                    <div class="flex justify-end gap-4 pt-4 border-t">
                        <a href="{{ route('profile.show') }}"
                           class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-6 py-2 rounded-lg bg-blue-600 text-white shadow-md hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


