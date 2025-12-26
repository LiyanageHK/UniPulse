<?php

namespace Database\Seeders;

use App\Models\Counselor;
use Illuminate\Database\Seeder;

class CounselorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $counselors = [
            // Colombo - Mental Health Counselors
            [
                'name' => 'Dr. Nimalka Perera',
                'title' => 'Licensed Clinical Psychologist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['depression', 'anxiety', 'suicide_prevention', 'crisis_intervention', 'mental_health'],
                'bio' => 'Over 15 years of experience in student mental health and crisis intervention.',
                'email' => 'nimalka.perera@unipulse.edu',
                'phone' => '+94 11 234 5678',
                'office_location' => 'Student Wellness Center, Building A, Room 201',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => 'University of Colombo',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/nperera',
            ],
            [
                'name' => 'Sanduni Fernando',
                'title' => 'Mental Health Counselor',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['stress_management', 'anxiety', 'self_harm', 'mental_health'],
                'bio' => 'Specializes in working with university students facing academic and personal challenges.',
                'email' => 'sanduni.fernando@unipulse.edu',
                'phone' => '+94 11 234 5679',
                'office_location' => 'Student Wellness Center, Building A, Room 203',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => 'University of Colombo',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 10:00 AM - 6:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/sfernando',
            ],

            // Kandy - Mental Health Counselors
            [
                'name' => 'Dr. Rajitha Silva',
                'title' => 'Psychiatrist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['depression', 'mood_disorders', 'suicide_prevention', 'mental_health'],
                'bio' => 'Board-certified psychiatrist specializing in adolescent and young adult mental health.',
                'email' => 'rajitha.silva@unipulse.edu',
                'phone' => '+94 81 234 5678',
                'office_location' => 'Medical Center, 2nd Floor',
                'city' => 'Kandy',
                'region' => 'Central Province',
                'university' => 'University of Peradeniya',
                'is_available' => true,
                'availability_schedule' => ['Tuesday-Saturday: 8:00 AM - 4:00 PM'],
                'offers_online' => false,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Kavindi Wickramasinghe',
                'title' => 'Counseling Psychologist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['anxiety', 'stress_management', 'social_support', 'mental_health'],
                'bio' => 'Experienced in helping students manage stress and build resilience.',
                'email' => 'kavindi.w@unipulse.edu',
                'phone' => '+94 81 234 5679',
                'office_location' => 'Student Affairs Office',
                'city' => 'Kandy',
                'region' => 'Central Province',
                'university' => 'University of Peradeniya',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/kwwickramasinghe',
            ],

            // Galle - Mental Health Counselors
            [
                'name' => 'Asanka Jayawardena',
                'title' => 'Mental Health Counselor',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['crisis_intervention', 'depression', 'hopelessness', 'mental_health'],
                'bio' => 'Dedicated to providing immediate support for students in crisis.',
                'email' => 'asanka.j@unipulse.edu',
                'phone' => '+94 91 234 5678',
                'office_location' => 'Campus Counseling Services',
                'city' => 'Galle',
                'region' => 'Southern Province',
                'university' => 'University of Ruhuna',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 8:30 AM - 4:30 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/ajayawardena',
            ],

            // Colombo - Academic Counselors
            [
                'name' => 'Prof. Chaminda Rathnayake',
                'title' => 'Senior Academic Advisor',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'specializations' => ['academic_counseling', 'stress_management', 'study_skills'],
                'bio' => 'Helps students navigate academic challenges and develop effective study strategies.',
                'email' => 'chaminda.r@unipulse.edu',
                'phone' => '+94 11 234 5680',
                'office_location' => 'Academic Affairs, Building B, Room 105',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => 'University of Colombo',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/crathnayake',
            ],
           
            // Jaffna - Mental Health Counselor
            [
                'name' => 'Dr. Priya Navaratnam',
                'title' => 'Clinical Psychologist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['trauma', 'anxiety', 'depression', 'cultural_counseling', 'mental_health'],
                'bio' => 'Culturally sensitive counseling for students from diverse backgrounds.',
                'email' => 'priya.n@unipulse.edu',
                'phone' => '+94 21 234 5678',
                'office_location' => 'Health Services Building',
                'city' => 'Jaffna',
                'region' => 'Northern Province',
                'university' => 'University of Jaffna',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 8:00 AM - 4:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/pnavaratnam',
            ],

            // Online-only Crisis Counselor
            [
                'name' => 'Shanaka Gunawardena',
                'title' => 'Crisis Intervention Specialist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => ['crisis_intervention', 'suicide_prevention', 'emergency_support', 'mental_health'],
                'bio' => '24/7 crisis support specialist available for immediate intervention.',
                'email' => 'crisis@unipulse.edu',
                'phone' => '+94 77 123 4567 (24/7 Hotline)',
                'office_location' => 'Online Services Only',
                'city' => 'All Cities',
                'region' => 'Nationwide',
                'university' => 'UniPulse Network',
                'is_available' => true,
                'availability_schedule' => ['24/7 availability'],
                'offers_online' => true,
                'online_booking_url' => 'https://crisis.unipulse.edu/immediate-help',
            ],

            // Career Counselors
            [
                'name' => 'Dilani Herath',
                'title' => 'Career Development Counselor',
                'category' => Counselor::CATEGORY_CAREER,
                'specializations' => ['career_planning', 'job_search', 'interview_preparation'],
                'bio' => 'Assists students with career planning and job placement.',
                'email' => 'dilani.herath@unipulse.edu',
                'phone' => '+94 11 234 5681',
                'office_location' => 'Career Services Center',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => 'University of Colombo',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 10:00 AM - 6:00 PM'],
                'offers_online' => true,
                'online_booking_url' => 'https://book.unipulse.edu/counselor/dherath',
            ],

            // Financial Aid Counselor
            [
                'name' => 'Ruwan De Silva',
                'title' => 'Financial Aid Advisor',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'specializations' => ['scholarships', 'financial_planning', 'student_loans'],
                'bio' => 'Helps students navigate financial aid and scholarship opportunities.',
                'email' => 'ruwan.desilva@unipulse.edu',
                'phone' => '+94 11 234 5682',
                'office_location' => 'Financial Aid Office, Administration Building',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => 'University of Colombo',
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => false,
                'online_booking_url' => null,
            ],
        ];

        foreach ($counselors as $counselor) {
            Counselor::create($counselor);
        }
    }
}
