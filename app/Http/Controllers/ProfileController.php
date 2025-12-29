<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show read-only profile view
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show edit profile form
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    $validated = $request->validated();

    // --- A/L results build ---
    $alResults = [];
    for ($i = 1; $i <= 5; $i++) {
        $subjectKey = "al_subject_{$i}";
        $gradeKey = "al_grade_{$i}";
        $subject = $validated[$subjectKey] ?? null;
        $grade = $validated[$gradeKey] ?? null;

        if (!empty($subject) || !empty($grade)) {
            $alResults["subject_{$i}"] = [
                'subject' => $subject ?? '',
                'grade' => $grade ?? '',
            ];
        }
    }

    $validated['al_results'] = $alResults;

    // Remove temporary form-only fields
    for ($i = 1; $i <= 5; $i++) {
        unset($validated["al_subject_{$i}"], $validated["al_grade_{$i}"]);
    }

    // --- FIX MISSING ARRAYS ---
    $validated['learning_style'] = $request->learning_style ?? [];
    $validated['communication_preferences'] = $request->communication_preferences ?? [];
    $validated['interests'] = $request->interests ?? [];
    $validated['hobbies'] = $request->hobbies ?? [];
    $validated['preferred_support_types'] = $request->preferred_support_types ?? [];

    // --- FIX BOOLEAN ---
    if ($request->has('is_employed')) {
        $validated['is_employed'] = (int) $request->is_employed;
    } else {
        // If checkbox is not present, explicitly set to false
        $validated['is_employed'] = 0;
    }

    // --- FINAL SAVE ---
    $user->fill($validated);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    return Redirect::route('profile.show')->with('status', 'Profile updated successfully.');
}


    /**
     * Show password change page
     */
    public function password(): View
    {
        return view('profile.password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Log out the user after password change
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with success message
        return redirect()->route('login')->with('status', 'Password changed successfully. Please log in again.');
    }

    /**
     * Delete account
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
