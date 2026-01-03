<?php

namespace Database\Seeders;

use App\Models\Counselor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CounselorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Complete counselor data based on provided documentation.
     */
    public function run(): void
    {
        // Clear existing counselors (disable FK checks temporarily)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Counselor::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $counselors = [
            // ============================================
            // Academic & Study Support
            // ============================================
            [
                'name' => 'Mrs. Bhagya Abeysinghe',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'hospital' => 'Durdans Hospital, Asiri Medical Hospital',
            ],
            [
                'name' => 'Dr. R. A. Ranjith Perera',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'hospital' => 'Asiri Medical Hospital',
            ],
            [
                'name' => 'Ms. Inoka Edirisinghe',
                'title' => 'Counselling Psychologist',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'hospital' => 'Lanka Hospitals / Hemas Hospitals (visiting)',
            ],

            // ============================================
            // Mental Health & Wellness
            // ============================================
            [
                'name' => 'Ms. Thulshara Dissanayake',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'hospital' => 'Asia Hospitals, Asiri Medical Hospital',
            ],
            [
                'name' => 'Dr. Chrishara Paranawithana',
                'title' => 'Clinical Psychologist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'Dr. Marcel De Roos',
                'title' => 'Psychologist / Psychotherapist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'hospital' => 'Nawaloka Hospital',
            ],
            [
                'name' => 'National Institute of Mental Health (NIMH)',
                'title' => 'Consultant Psychiatrists & Clinical Psychologists',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'hospital' => 'Angoda (Govt - referrals via private hospitals)',
            ],

            // ============================================
            // Social Integration & Peer Relationships
            // ============================================
            [
                'name' => 'Miss Nimethri Gunasekara',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_SOCIAL,
                'hospital' => 'Asia Hospitals',
            ],
            [
                'name' => 'Mrs. Bhagya Abeysinghe',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_SOCIAL,
                'hospital' => 'Durdans Hospital, Asiri Medical Hospital',
            ],
            [
                'name' => 'Ms. Dilrukshi Gamage',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_SOCIAL,
                'hospital' => 'Lanka Hospitals',
            ],

            // ============================================
            // Crisis & Emergency Intervention
            // ============================================
            [
                'name' => 'Sumithrayo Counselors',
                'title' => 'Trained Volunteers',
                'category' => Counselor::CATEGORY_CRISIS,
                'hospital' => 'Sumithrayo National Helpline',
            ],
            [
                'name' => 'On-call Psychological Counsellor',
                'title' => 'Hospital-appointed Psychologist',
                'category' => Counselor::CATEGORY_CRISIS,
                'hospital' => 'Any Hospital',
            ],
            [
                'name' => 'National Institute of Mental Health (NIMH)',
                'title' => 'Consultant Psychiatrists & Clinical Psychologists',
                'category' => Counselor::CATEGORY_CRISIS,
                'hospital' => 'Angoda (Govt - referrals via private hospitals)',
            ],

            // ============================================
            // Career Guidance & Future Planning
            // ============================================
            [
                'name' => 'Dr. Achini Ranasinghe',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_CAREER,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'Ms. Thulshara Dissanayake',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_CAREER,
                'hospital' => 'Asia Hospitals',
            ],
            [
                'name' => 'Mr. Arosha Perera',
                'title' => 'Career & Personal Development Counsellor',
                'category' => Counselor::CATEGORY_CAREER,
                'hospital' => 'The Caring Nest / Lanka Hospitals (visiting)',
            ],

            // ============================================
            // Relationship & Love Affairs
            // ============================================
            [
                'name' => 'Dr. Chrishara Paranawithana',
                'title' => 'Clinical Psychologist',
                'category' => Counselor::CATEGORY_RELATIONSHIP,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'Mrs. Bhagya Abeysinghe',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_RELATIONSHIP,
                'hospital' => 'Durdans Hospital',
            ],
            [
                'name' => 'Ms. Thilini Wijesooriya',
                'title' => 'Psychotherapist',
                'category' => Counselor::CATEGORY_RELATIONSHIP,
                'hospital' => 'Ninewells Hospital / Happy Mind',
            ],

            // ============================================
            // Family & Home-Related Issues
            // ============================================
            [
                'name' => 'Dr. Prasadi De Z. Jayathilaka',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_FAMILY,
                'hospital' => 'Asiri Medical Hospital',
            ],
            [
                'name' => 'Miss Nimethri Gunasekara',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_FAMILY,
                'hospital' => 'Asia Hospitals',
            ],
            [
                'name' => 'Dr. Mrs. Chamara Liyanage',
                'title' => 'Senior Psychological Counsellor',
                'category' => Counselor::CATEGORY_FAMILY,
                'hospital' => 'Nawinna Medicare Hospital',
            ],

            // ============================================
            // Physical Health & Lifestyle (Psych Focus)
            // ============================================
            [
                'name' => 'Dr. R. A. Ranjith Perera',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_PHYSICAL,
                'hospital' => 'Asiri Medical Hospital',
            ],
            [
                'name' => 'Dr. Achini Ranasinghe',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_PHYSICAL,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'HOPE Wellness Team',
                'title' => 'Psychologists & Counsellors',
                'category' => Counselor::CATEGORY_PHYSICAL,
                'hospital' => 'Kings Hospital (visiting wellness services)',
            ],

            // ============================================
            // Financial Wellness
            // ============================================
            [
                'name' => 'Dr. Chandrika A. Ismail',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'Mrs. Bhagya Abeysinghe',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'hospital' => 'Durdans Hospital',
            ],
            [
                'name' => 'Psychology Life Centre Team',
                'title' => 'Clinical & Counselling Psychologists',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'hospital' => 'Lanka Hospitals (visiting)',
            ],

            // ============================================
            // Extracurricular & Personal Development
            // ============================================
            [
                'name' => 'Ms. Thulshara Dissanayake',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_PERSONAL_DEVELOPMENT,
                'hospital' => 'Asia Hospitals',
            ],
            [
                'name' => 'Dr. Achini Ranasinghe',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_PERSONAL_DEVELOPMENT,
                'hospital' => 'Asiri Group',
            ],
            [
                'name' => 'Neranjala N. Mendis',
                'title' => 'Personal Development Counsellor / Life Learning Counselling',
                'category' => Counselor::CATEGORY_PERSONAL_DEVELOPMENT,
                'hospital' => 'Ninewells / Independent',
            ],
        ];

        foreach ($counselors as $counselor) {
            Counselor::create($counselor);
        }
    }
}
