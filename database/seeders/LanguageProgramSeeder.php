<?php

namespace Database\Seeders;

use App\Models\LanguageProgram;
use Illuminate\Database\Seeder;

class LanguageProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'code' => 'en',
                'locale_code' => 'EN-GB',
                'name' => 'English',
                'title' => 'English Mastery',
                'description' => 'Build practical fluency for school, work, and international exams.',
                'full_description' => 'Master English through structured levels, guided speaking practice, and exam-oriented coaching. Learners can focus on conversation, academic writing, or certification preparation based on their goals.',
                'flag_url' => 'https://flagcdn.com/w80/gb.png',
                'sort_order' => 1,
                'is_active' => true,
                'certifications' => [
                    ['name' => 'Cambridge', 'exams' => ['KET', 'PET', 'FCE', 'CAE', 'CPE']],
                    ['name' => 'IELTS', 'exams' => ['Academic', 'General Training']],
                    ['name' => 'TOEFL', 'exams' => ['iBT']],
                ],
            ],
            [
                'code' => 'es',
                'locale_code' => 'ES-ES',
                'name' => 'Spanish',
                'title' => 'Spanish Immersion',
                'description' => 'Progress through real-life communication and DELE-focused training.',
                'full_description' => 'Develop confident Spanish communication with balanced grammar, speaking, and listening modules. Tracks are available for travel, academic use, and official certification pathways.',
                'flag_url' => 'https://flagcdn.com/w80/es.png',
                'sort_order' => 2,
                'is_active' => true,
                'certifications' => [
                    ['name' => 'DELE', 'exams' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']],
                    ['name' => 'SIELE', 'exams' => ['SIELE Global']],
                ],
            ],
            [
                'code' => 'de',
                'locale_code' => 'DE-DE',
                'name' => 'German',
                'title' => 'Business German',
                'description' => 'Professional communication and technical German for modern workplaces.',
                'full_description' => 'Learn German with a practical focus on business communication, professional writing, and real workplace scenarios while preserving full CEFR progression.',
                'flag_url' => 'https://flagcdn.com/w80/de.png',
                'sort_order' => 3,
                'is_active' => true,
                'certifications' => [
                    ['name' => 'Goethe', 'exams' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']],
                    ['name' => 'TestDaF', 'exams' => ['Academic']],
                ],
            ],
            [
                'code' => 'fr',
                'locale_code' => 'FR-FR',
                'name' => 'French',
                'title' => 'French Excellence',
                'description' => 'Learn French for study, mobility, and professional communication.',
                'full_description' => 'Our French pathway combines communication practice with exam preparation for learners targeting study-abroad or career opportunities in francophone environments.',
                'flag_url' => 'https://flagcdn.com/w80/fr.png',
                'sort_order' => 4,
                'is_active' => true,
                'certifications' => [
                    ['name' => 'DELF', 'exams' => ['A1', 'A2', 'B1', 'B2']],
                    ['name' => 'DALF', 'exams' => ['C1', 'C2']],
                ],
            ],
            [
                'code' => 'it',
                'locale_code' => 'IT-IT',
                'name' => 'Italian',
                'title' => 'Italian Pathway',
                'description' => 'Develop fluent Italian through conversation-first classroom practice.',
                'full_description' => 'This track supports learners interested in Italian language for culture, study, or relocation, with progressive speaking labs and guided grammar mastery.',
                'flag_url' => 'https://flagcdn.com/w80/it.png',
                'sort_order' => 5,
                'is_active' => true,
                'certifications' => [
                    ['name' => 'CELI', 'exams' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']],
                ],
            ],
        ];

        foreach ($programs as $program) {
            LanguageProgram::query()->updateOrCreate(
                ['code' => $program['code']],
                $program
            );
        }
    }
}
