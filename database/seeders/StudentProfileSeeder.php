<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentProfileSeeder extends Seeder
{
    /**
     * Seed users and student_profiles from the CSV export
     * of the "Student Profiling Form (Responses)" Google Form.
     */
    public function run(): void
    {
        $csvPath = __DIR__ . '/student_profiles.csv';

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $this->command->error("Could not open CSV file.");
            return;
        }

        // Read and skip header row
        $header = fgetcsv($handle);

        if ($header === false) {
            $this->command->error("CSV file is empty.");
            fclose($handle);
            return;
        }

        $imported  = 0;
        $skipped   = 0;
        $errors    = 0;
        $row       = 1; // header was row 0

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                // Skip rows that don't have enough columns
                if (count($data) < 26) {
                    $this->command->warn("Row {$row}: Not enough columns (" . count($data) . "), skipping.");
                    $skipped++;
                    continue;
                }

                // --- Map CSV columns (0-indexed) ---
                // 0: Timestamp
                // 1: Name
                // 2: University
                // 3: Faculty
                // 4: AL Stream
                // 5: AL Results [Subject 1]
                // 6: AL Results [Subject 2]
                // 7: AL Results [Subject 3]
                // 8: AL Results [English]
                // 9: AL Results [General Knowledge]
                // 10: Preferred learning style
                // 11: Confidence in transitioning (1-5)
                // 12: Social setting preference
                // 13: Introvert-Extrovert scale (1-10)
                // 14: Stress level
                // 15: Group comfort (1-5)
                // 16: Communication methods
                // 17: Motivation
                // 18: Clear goal (1-5)
                // 19: Top interests
                // 20: Hobbies
                // 21: Living arrangement
                // 22: Employed (Yes/No)
                // 23: Overwhelmed (1-5)
                // 24: Struggle to connect (1-5)
                // 25: AI platform support (1-5)
                // 26: Support types (optional)

                $name          = trim($data[1] ?? '');
                $university    = trim($data[2] ?? '');
                $faculty       = trim($data[3] ?? '');
                $alStream      = trim($data[4] ?? '');
                $alSubject1    = $this->normalizeGrade($data[5] ?? '');
                $alSubject2    = $this->normalizeGrade($data[6] ?? '');
                $alSubject3    = $this->normalizeGrade($data[7] ?? '');
                $alEnglish     = $this->normalizeGrade($data[8] ?? '');
                $alGk          = $this->normalizeGrade($data[9] ?? '');
                $learningStyle = $this->normalizeLearningStyle($data[10] ?? '');
                $confidence    = $this->clamp((int) ($data[11] ?? 3), 1, 5);
                $socialSetting = trim($data[12] ?? '');
                $introExtro    = $this->clamp((int) ($data[13] ?? 5), 1, 10);
                $stressLevel   = $this->normalizeStress($data[14] ?? '');
                $groupComfort  = $this->clamp((int) ($data[15] ?? 3), 1, 5);
                $commMethods   = $this->parseCsvList($data[16] ?? '');
                $motivation    = trim($data[17] ?? '');
                $clearGoal     = $this->clamp((int) ($data[18] ?? 3), 1, 5);
                $interests     = $this->parseCsvList($data[19] ?? '');
                $hobbies       = $this->parseCsvList($data[20] ?? '');
                $livingArr     = trim($data[21] ?? '');
                $employed      = $this->normalizeYesNo($data[22] ?? 'No');
                $overwhelmed   = $this->clamp((int) ($data[23] ?? 3), 1, 5);
                $struggle      = $this->clamp((int) ($data[24] ?? 3), 1, 5);
                $aiSupport     = $this->clamp((int) ($data[25] ?? 3), 1, 5);
                $supportTypes  = $this->parseCsvList($data[26] ?? '');

                if (empty($name)) {
                    $this->command->warn("Row {$row}: Empty name, skipping.");
                    $skipped++;
                    continue;
                }

                // Generate email from name
                $email = $this->generateEmail($name, $row);

                // Check if user already exists by email
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $this->command->warn("Row {$row}: User '{$name}' ({$email}) already exists, skipping.");
                    $skipped++;
                    continue;
                }

                try {
                    // --- Create User ---
                    $user = User::create([
                        'name'                     => $name,
                        'email'                    => $email,
                        'password'                 => Hash::make('password123'),
                        'university'               => $university,
                        'faculty'                  => $faculty,
                        'al_stream'                => $alStream,
                        'al_results'               => [
                            'subject1' => $alSubject1,
                            'subject2' => $alSubject2,
                            'subject3' => $alSubject3,
                            'english'  => $alEnglish,
                            'gk'       => $alGk,
                        ],
                        'learning_style'           => [$learningStyle],
                        'transition_confidence'    => $confidence,
                        'social_preference'        => $socialSetting,
                        'introvert_extrovert_scale'=> $introExtro,
                        'stress_level'             => $stressLevel,
                        'group_work_comfort'       => $groupComfort,
                        'communication_preferences'=> $commMethods,
                        'primary_motivator'        => Str::limit($motivation, 255),
                        'goal_clarity'             => $clearGoal,
                        'interests'                => $interests,
                        'hobbies'                  => $hobbies,
                        'living_arrangement'       => $livingArr,
                        'is_employed'              => $employed === 'Yes',
                        'overwhelm_level'          => $overwhelmed,
                        'peer_struggle'            => $struggle,
                        'ai_openness'              => $aiSupport,
                        'preferred_support_types'  => $supportTypes,
                        'onboarding_completed'     => true,
                        'onboarding_completed_at'  => now(),
                        'on_boarding_required'      => false,
                    ]);

                    // --- Create StudentProfile ---
                    StudentProfile::create([
                        'user_id'               => $user->id,
                        'university'            => $university,
                        'faculty'               => $faculty,
                        'al_stream'             => $alStream,
                        'al_result_subject1'    => $alSubject1,
                        'al_result_subject2'    => $alSubject2,
                        'al_result_subject3'    => $alSubject3,
                        'al_result_english'     => $alEnglish,
                        'al_result_gk'          => $alGk,
                        'learning_style'        => $learningStyle,
                        'confidence'            => $confidence,
                        'social_setting'        => $socialSetting,
                        'intro_extro'           => $introExtro,
                        'stress_level'          => $stressLevel,
                        'group_comfort'         => $groupComfort,
                        'communication_methods' => $commMethods,
                        'motivation'            => Str::limit($motivation, 255),
                        'clear_goal'            => $clearGoal,
                        'top_interests'         => $interests,
                        'hobbies'               => $hobbies,
                        'living_arrangement'    => $livingArr,
                        'employed'              => $employed,
                        'overwhelmed'           => $overwhelmed,
                        'struggle_connect'      => $struggle,
                        'ai_platform_support'   => $aiSupport,
                        'support_types'         => $supportTypes,
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $this->command->error("Row {$row} ({$name}): " . $e->getMessage());
                    $errors++;
                }
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info("=== Import Complete ===");
            $this->command->info("Imported: {$imported}");
            $this->command->info("Skipped:  {$skipped}");
            $this->command->info("Errors:   {$errors}");
            $this->command->info("Total users now: " . User::count());
            $this->command->info("Total profiles now: " . StudentProfile::count());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Seeder failed, all changes rolled back: " . $e->getMessage());
        }

        fclose($handle);
    }

    /**
     * Normalize AL grade to valid enum: A, B, C, S, F
     */
    private function normalizeGrade(string $value): string
    {
        $value = strtoupper(trim($value));

        return in_array($value, ['A', 'B', 'C', 'S', 'F']) ? $value : 'C';
    }

    /**
     * Normalize learning style to a single valid enum: Online, Physical, Hybrid
     * CSV may contain multiple comma-separated values like "Online, Physical, Hybrid"
     */
    private function normalizeLearningStyle(string $value): string
    {
        $value  = trim($value);
        $styles = array_map('trim', explode(',', $value));

        // If multiple styles selected, prefer Hybrid
        if (count($styles) > 1) {
            return 'Hybrid';
        }

        $style = ucfirst(strtolower($styles[0]));

        return in_array($style, ['Online', 'Physical', 'Hybrid']) ? $style : 'Hybrid';
    }

    /**
     * Normalize stress level to valid enum.
     */
    private function normalizeStress(string $value): string
    {
        $value = trim($value);
        $lower = strtolower($value);

        if (str_contains($lower, 'low')) return 'Low';
        if (str_contains($lower, 'high')) return 'High';
        return 'Moderate';
    }

    /**
     * Normalize Yes/No value.
     */
    private function normalizeYesNo(string $value): string
    {
        return strtolower(trim($value)) === 'yes' ? 'Yes' : 'No';
    }

    /**
     * Parse a comma-separated list from a CSV cell into an array.
     */
    private function parseCsvList(string $value): array
    {
        if (empty(trim($value))) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $value))
        ));
    }

    /**
     * Clamp a value between min and max.
     */
    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    /**
     * Generate a unique email from the student name.
     */
    private function generateEmail(string $name, int $row): string
    {
        $slug = Str::slug($name, '.');

        if (empty($slug)) {
            $slug = 'student.' . $row;
        }

        return $slug . '@unipulse.student.lk';
    }
}
