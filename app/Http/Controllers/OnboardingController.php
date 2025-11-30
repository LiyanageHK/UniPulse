<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function step1()
    {
        return view('onboarding.step1');
    }

    public function storeStep1(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string',
        'university' => 'required|string',
        'faculty' => 'required|string',
        'al_stream' => 'required|string',

        // remove al_results from validation – we generate it manually
        'learning_style' => 'required|array',
        'transition_confidence' => 'required|integer',
        'social_preference' => 'required|string',
        'introvert_extrovert_scale' => 'required|integer',
        'stress_level' => 'required|string',
        'group_work_comfort' => 'required|integer',
        'communication_preferences' => 'required|array',

        // new — validate subject fields
        'al_subject_1' => 'required|string',
        'al_grade_1' => 'required|string',
        'al_subject_2' => 'required|string',
        'al_grade_2' => 'required|string',
        'al_subject_3' => 'required|string',
        'al_grade_3' => 'required|string',
        'al_subject_4' => 'nullable|string',
        'al_grade_4' => 'nullable|string',
        'al_subject_5' => 'nullable|string',
        'al_grade_5' => 'nullable|string',
    ]);

    // Build AL results JSON
    $al_results = [];
    for ($i = 1; $i <= 5; $i++) {
        $subject = $request->input("al_subject_$i");
        $grade   = $request->input("al_grade_$i");

        if ($subject && $grade) {
            $al_results["subject_$i"] = [
                "subject" => $subject,
                "grade" => $grade
            ];
        }
    }

    $user = auth()->user();

    $user->name = $data['name'];
    $user->university = $data['university'];
    $user->faculty = $data['faculty'];
    $user->al_stream = $data['al_stream'];
    $user->al_results = json_encode($al_results);

    // convert arrays to JSON
    $user->learning_style = json_encode($data['learning_style']);
    $user->communication_preferences = json_encode($data['communication_preferences']);

    $user->transition_confidence = $data['transition_confidence'];
    $user->social_preference = $data['social_preference'];
    $user->introvert_extrovert_scale = $data['introvert_extrovert_scale'];
    $user->stress_level = $data['stress_level'];
    $user->group_work_comfort = $data['group_work_comfort'];

    $user->save();

    return redirect()->route('onboarding.step2');
}


    public function step2()
    {
        return view('onboarding.step2');
    }

    public function storeStep2(Request $request)
    {
        $data = $request->validate([
            'primary_motivator' => 'required|string',
            'goal_clarity' => 'required|integer',
            'interests' => 'required|array',
            'hobbies' => 'required|array',
            'living_arrangement' => 'required|string',
            'is_employed' => 'required|integer',
            'overwhelm_level' => 'required|integer',
            'peer_struggle' => 'required|integer',
            'ai_openness' => 'required|integer',
            'preferred_support_types' => 'required|array',
        ]);

        $user = auth()->user();

        $user->primary_motivator = $data['primary_motivator'];
        $user->goal_clarity = $data['goal_clarity'];
        $user->interests = $data['interests'];
        $user->hobbies = $data['hobbies'];
        $user->living_arrangement = $data['living_arrangement'];
        $user->is_employed = $data['is_employed'];
        $user->overwhelm_level = $data['overwhelm_level'];
        $user->peer_struggle = $data['peer_struggle'];
        $user->ai_openness = $data['ai_openness'];
        $user->preferred_support_types = $data['preferred_support_types'];

        $user->onboarding_completed = true;
        $user->onboarding_completed_at = now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Welcome to UniPulse!');
    }
}
