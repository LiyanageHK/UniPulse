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
                'specializations' => [],
                'bio' => 'Experienced psychological counsellor specializing in academic and study support.',
                'email' => 'bhagya.abeysinghe@durdans.lk',
                'phone' => null,
                'office_location' => 'Durdans Hospital, Asiri Medical Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Dr. R. A. Ranjith Perera',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'specializations' => [],
                'bio' => 'Qualified psychologist providing academic and study support counseling.',
                'email' => 'ranjith.perera@asiri.lk',
                'phone' => null,
                'office_location' => 'Asiri Medical Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 8:30 AM - 4:30 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Ms. Inoka Edirisinghe',
                'title' => 'Counselling Psychologist',
                'category' => Counselor::CATEGORY_ACADEMIC,
                'specializations' => [],
                'bio' => 'Counselling psychologist with expertise in academic stress and study motivation.',
                'email' => 'inoka.edirisinghe@lankahospitals.lk',
                'phone' => null,
                'office_location' => 'Lanka Hospitals / Hemas Hospitals (visiting)',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Saturday: 9:00 AM - 6:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Mental Health & Wellness
            // ============================================
            [
                'name' => 'Ms. Thulshara Dissanayake',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => [],
                'bio' => 'Psychological counsellor specializing in mental health and wellness support.',
                'email' => 'thulshara.dissanayake@asiahospitals.lk',
                'phone' => null,
                'office_location' => 'Asia Hospitals, Asiri Medical Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Dr. Chrishara Paranawithana',
                'title' => 'Clinical Psychologist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => [],
                'bio' => 'Clinical psychologist providing comprehensive mental health support.',
                'email' => 'chrishara.paranawithana@asiri.lk',
                'phone' => null,
                'office_location' => 'Asiri Group',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 8:00 AM - 4:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Dr. Marcel De Roos',
                'title' => 'Psychologist / Psychotherapist',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => [],
                'bio' => 'Experienced psychologist and psychotherapist for mental health and wellness.',
                'email' => 'marcel.deroos@nawaloka.lk',
                'phone' => null,
                'office_location' => 'Nawaloka Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'National Institute of Mental Health (NIMH)',
                'title' => 'Consultant Psychiatrists & Clinical Psychologists',
                'category' => Counselor::CATEGORY_MENTAL_HEALTH,
                'specializations' => [],
                'bio' => 'Government mental health facility with referrals via private hospitals.',
                'email' => 'info@nimh.gov.lk',
                'phone' => '+94 11 257 8234',
                'office_location' => 'Angoda (Govt – referrals via private hospitals)',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 8:00 AM - 4:00 PM'],
                'offers_online' => false,
                'online_booking_url' => null,
            ],

            // ============================================
            // Social Integration & Peer Relationships
            // ============================================
            [
                'name' => 'Miss Nimethri Gunasekara',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_SOCIAL,
                'specializations' => [],
                'bio' => 'Psychological counsellor specializing in social integration and peer relationships.',
                'email' => 'nimethri.gunasekara@asiahospitals.lk',
                'phone' => null,
                'office_location' => 'Asia Hospitals',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Ms. Dilrukshi Gamage',
                'title' => 'Psychological Counsellor',
                'category' => Counselor::CATEGORY_SOCIAL,
                'specializations' => [],
                'bio' => 'Psychological counsellor helping students with social support and peer connections.',
                'email' => 'dilrukshi.gamage@lankahospitals.lk',
                'phone' => null,
                'office_location' => 'Lanka Hospitals',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Crisis & Emergency Intervention
            // ============================================
            [
                'name' => 'Sumithrayo Counselors',
                'title' => 'Trained Volunteers',
                'category' => Counselor::CATEGORY_CRISIS,
                'specializations' => [],
                'bio' => 'Sumithrayo is a suicide prevention helpline providing 24/7 crisis support.',
                'email' => 'help@sumithrayo.org',
                'phone' => '+94 11 268 2535',
                'office_location' => 'Sumithrayo National Helpline',
                'city' => 'All Cities',
                'region' => 'Nationwide',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['24/7 availability'],
                'offers_online' => true,
                'online_booking_url' => 'https://sumithrayo.org',
            ],
            [
                'name' => 'On-call Psychological Counsellor',
                'title' => 'Hospital-appointed Psychologist',
                'category' => Counselor::CATEGORY_CRISIS,
                'specializations' => [],
                'bio' => 'Hospital on-call psychological support available for emergency crisis intervention.',
                'email' => null,
                'phone' => null,
                'office_location' => 'Any Hospital',
                'city' => 'All Cities',
                'region' => 'Nationwide',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['24/7 Emergency Services'],
                'offers_online' => false,
                'online_booking_url' => null,
            ],

            // ============================================
            // Career Guidance & Future Planning
            // ============================================
            [
                'name' => 'Dr. Achini Ranasinghe',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_CAREER,
                'specializations' => [],
                'bio' => 'Psychologist specializing in career guidance and future planning.',
                'email' => 'achini.ranasinghe@asiri.lk',
                'phone' => null,
                'office_location' => 'Asiri Group',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Mr. Arosha Perera',
                'title' => 'Career & Personal Development Counsellor',
                'category' => Counselor::CATEGORY_CAREER,
                'specializations' => [],
                'bio' => 'Career and personal development counsellor supporting students with future planning.',
                'email' => 'arosha.perera@caringnest.lk',
                'phone' => null,
                'office_location' => 'The Caring Nest / Lanka Hospitals (visiting)',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Relationship & Love Affairs
            // ============================================
            [
                'name' => 'Ms. Thilini Wijesooriya',
                'title' => 'Psychotherapist',
                'category' => Counselor::CATEGORY_RELATIONSHIP,
                'specializations' => [],
                'bio' => 'Psychotherapist specializing in relationship and love affair counseling.',
                'email' => 'thilini.wijesooriya@ninewells.lk',
                'phone' => null,
                'office_location' => 'Ninewells Hospital / Happy Mind',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Family & Home-Related Issues
            // ============================================
            [
                'name' => 'Dr. Prasadi De Z. Jayathilaka',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_FAMILY,
                'specializations' => [],
                'bio' => 'Psychologist specializing in family and home-related issues.',
                'email' => 'prasadi.jayathilaka@asiri.lk',
                'phone' => null,
                'office_location' => 'Asiri Medical Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Dr. Mrs. Chamara Liyanage',
                'title' => 'Senior Psychological Counsellor',
                'category' => Counselor::CATEGORY_FAMILY,
                'specializations' => [],
                'bio' => 'Senior psychological counsellor for family and home-related counseling.',
                'email' => 'chamara.liyanage@nawinna.lk',
                'phone' => null,
                'office_location' => 'Nawinna Medicare Hospital',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Physical Health & Lifestyle (Psych Focus)
            // ============================================
            [
                'name' => 'HOPE Wellness Team',
                'title' => 'Psychologists & Counsellors',
                'category' => Counselor::CATEGORY_PHYSICAL,
                'specializations' => [],
                'bio' => 'Team of psychologists and counsellors focusing on physical health and lifestyle.',
                'email' => 'wellness@hopewellness.lk',
                'phone' => null,
                'office_location' => 'Kings Hospital (visiting wellness services)',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Financial Wellness
            // ============================================
            [
                'name' => 'Dr. Chandrika A. Ismail',
                'title' => 'Psychologist',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'specializations' => [],
                'bio' => 'Psychologist specializing in financial wellness and stress management.',
                'email' => 'chandrika.ismail@asiri.lk',
                'phone' => null,
                'office_location' => 'Asiri Group',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
            [
                'name' => 'Psychology Life Centre Team',
                'title' => 'Clinical & Counselling Psychologists',
                'category' => Counselor::CATEGORY_FINANCIAL,
                'specializations' => [],
                'bio' => 'Team of clinical and counselling psychologists for financial wellness support.',
                'email' => 'info@psychologylife.lk',
                'phone' => null,
                'office_location' => 'Lanka Hospitals (visiting)',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],

            // ============================================
            // Extracurricular & Personal Development
            // ============================================
            [
                'name' => 'Neranjala N. Mendis',
                'title' => 'Personal Development Counsellor / Life Learning Counselling',
                'category' => Counselor::CATEGORY_PERSONAL_DEVELOPMENT,
                'specializations' => [],
                'bio' => 'Personal development counsellor specializing in life learning and extracurricular activities.',
                'email' => 'neranjala.mendis@ninewells.lk',
                'phone' => null,
                'office_location' => 'Ninewells / Independent',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'university' => null,
                'is_available' => true,
                'availability_schedule' => ['Monday-Friday: 9:00 AM - 5:00 PM'],
                'offers_online' => true,
                'online_booking_url' => null,
            ],
        ];

        foreach ($counselors as $counselor) {
            Counselor::create($counselor);
        }
    }
}
