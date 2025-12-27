<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Academic & Demographic
            'university' => ['nullable', 'string', 'max:255'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'al_stream' => ['nullable', 'string', 'max:255'],
            'al_subject_1' => ['nullable', 'string', 'max:255'],
            'al_grade_1' => ['nullable', 'string', 'in:A,B,C,S,F'],
            'al_subject_2' => ['nullable', 'string', 'max:255'],
            'al_grade_2' => ['nullable', 'string', 'in:A,B,C,S,F'],
            'al_subject_3' => ['nullable', 'string', 'max:255'],
            'al_grade_3' => ['nullable', 'string', 'in:A,B,C,S,F'],
            'al_subject_4' => ['nullable', 'string', 'max:255'],
            'al_grade_4' => ['nullable', 'string', 'in:A,B,C,S,F'],
            'al_subject_5' => ['nullable', 'string', 'max:255'],
            'al_grade_5' => ['nullable', 'string', 'in:A,B,C,S,F'],
            'al_results' => ['nullable', 'array'],
            // Learning & Social
            'learning_style' => ['nullable', 'array'],
            'learning_style.*' => ['string', 'in:Online,Physical,Hybrid'],
            'transition_confidence' => ['nullable', 'integer', 'between:1,5'],
            'social_preference' => ['nullable', 'string', 'in:1-on-1,Small Groups,Large Groups,Online-only'],
            'introvert_extrovert_scale' => ['nullable', 'integer', 'between:1,10'],
            'stress_level' => ['nullable', 'string', 'in:Low,Moderate,High'],
            'group_work_comfort' => ['nullable', 'integer', 'between:1,5'],
            'communication_preferences' => ['nullable', 'array'],
            'communication_preferences.*' => ['string', 'in:Texts,In-person,Calls'],
            'living_arrangement' => ['nullable', 'string', 'in:Hostel,Boarding,Home,Other'],
            'is_employed' => ['nullable', 'integer', 'in:0,1'],
            // Interests & Lifestyle
            'primary_motivator' => ['nullable', 'string', 'max:255'],
            'goal_clarity' => ['nullable', 'integer', 'between:1,5'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'max:255'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*' => ['string', 'max:255'],

            // Wellbeing & Support
            'overwhelm_level' => ['nullable', 'integer', 'between:1,5'],
            'peer_struggle' => ['nullable', 'integer', 'between:1,5'],
            'ai_openness' => ['nullable', 'integer', 'between:1,5'],
            'preferred_support_types' => ['nullable', 'array'],
            'preferred_support_types.*' => ['string', 'in:Peer Matching,Counseling,Study Groups,Chatbot'],
        ];
    }
}
