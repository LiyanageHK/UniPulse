<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

class OnBoardingPoornimaController extends Controller
{
    public function onBoarding(Request $request)
    {
        $username = Auth::user()->name;
        return view('auth.on-boarding', compact("username"));
    }

    public function onBoardingSuccess(Request $request){
        $username = Auth::user()->name;
        return view('on-boarding-success', compact("username"));
    }
    public function onBoardingStore(Request $request)
    {
        $validated = $request->validate([
            'university' => 'required|string|max:255',
            'university_other' => 'nullable|string|max:255',
            'faculty' => 'required|string|max:255',
            'faculty_other' => 'nullable|string|max:255',
            'al_stream' => 'required|string|max:255',
            'al_stream_other' => 'nullable|string|max:255',
            'al_result_subject1' => 'required|in:A,B,C,S,F',
            'al_result_subject2' => 'required|in:A,B,C,S,F',
            'al_result_subject3' => 'required|in:A,B,C,S,F',
            'al_result_english' => 'required|in:A,B,C,S,F',
            'al_result_gk' => 'required|in:A,B,C,S,F',
            'learning_style' => 'required|in:Online,Physical,Hybrid',
            'confidence' => 'required|integer|min:1|max:5',
            'social_setting' => 'required|string|max:255',
            'intro_extro' => 'required|integer|min:1|max:10',
            'stress_level' => 'required|in:Low,Moderate,High',
            'group_comfort' => 'required|integer|min:1|max:5',
            'communication_methods' => 'required|array',
            'communication_methods.*' => 'string|max:255',
            'motivation' => 'required|string|max:255',
            'clear_goal' => 'required|integer|min:1|max:5',
            'top_interests' => 'required|array',
            'top_interests.*' => 'string|max:255',
            'hobbies' => 'required|array',
            'hobbies.*' => 'string|max:255',
            'living_arrangement' => 'required|string|max:255',
            'employed' => 'required|in:Yes,No',
            'overwhelmed' => 'required|integer|min:1|max:5',
            'struggle_connect' => 'required|integer|min:1|max:5',
            'ai_platform_support' => 'required|integer|min:1|max:5',
            'support_types' => 'nullable|array',
            'support_types.*' => 'string|max:255',
        ]);


        DB::beginTransaction();

        try {

            StudentProfile::create([
                'user_id' => Auth::id(), // assumes user is logged in
                'university' => $validated['university'],
                'university_other' => $request->input('university_other'),
                'faculty' => $validated['faculty'],
                'faculty_other' => $request->input('faculty_other'),
                'al_stream' => $validated['al_stream'],
                'al_stream_other' => $request->input('al_stream_other'),
                'al_result_subject1' => $validated['al_result_subject1'],
                'al_result_subject2' => $validated['al_result_subject2'],
                'al_result_subject3' => $validated['al_result_subject3'],
                'al_result_english' => $validated['al_result_english'],
                'al_result_gk' => $validated['al_result_gk'],
                'learning_style' => $validated['learning_style'],
                'confidence' => $validated['confidence'],
                'social_setting' => $validated['social_setting'],
                'intro_extro' => $validated['intro_extro'],
                'stress_level' => $validated['stress_level'],
                'group_comfort' => $validated['group_comfort'],
                'communication_methods' => json_encode($validated['communication_methods']),
                'motivation' => $validated['motivation'],
                'clear_goal' => $validated['clear_goal'],
                'top_interests' => json_encode($validated['top_interests']),
                'hobbies' => json_encode($validated['hobbies']),
                'living_arrangement' => $validated['living_arrangement'],
                'employed' => $validated['employed'],
                'overwhelmed' => $validated['overwhelmed'],
                'struggle_connect' => $validated['struggle_connect'],
                'ai_platform_support' => $validated['ai_platform_support'],
                'support_types' => json_encode($request->input('support_types', [])),
            ]);

            Auth::User()->update([
                'on_boarding_required' => false
            ]);

            DB::commit();

            if(true){
                return redirect()->route('on-boarding-success');
            }

            return redirect()->route('dashboard');


        } catch (\Exception $e) {

            DB::rollBack();
            return back()->withErrors([
                'error' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}
