<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Creating Instructor profiles...');

        $instructors = [
            ['name' => 'Dr. Ahmad Hidayat', 'email' => 'ahmad.hidayat@school.com', 'specialization' => 'Mathematics', 'education_level' => 'S3 (Doctoral)', 'experience_years' => 15],
            ['name' => 'Siti Nurhaliza, M.Pd', 'email' => 'siti.nurhaliza@school.com', 'specialization' => 'Physics', 'education_level' => 'S2 (Master)', 'experience_years' => 10],
            ['name' => 'Budi Santoso, S.Si', 'email' => 'budi.santoso@school.com', 'specialization' => 'Chemistry', 'education_level' => 'S1 (Bachelor)', 'experience_years' => 8],
            ['name' => 'Dewi Lestari, M.Pd', 'email' => 'dewi.lestari@school.com', 'specialization' => 'Biology', 'education_level' => 'S2 (Master)', 'experience_years' => 12],
            ['name' => 'Rudi Hartono, S.Pd', 'email' => 'rudi.hartono@school.com', 'specialization' => 'English', 'education_level' => 'S1 (Bachelor)', 'experience_years' => 7],
            ['name' => 'Maya Puspita, M.A', 'email' => 'maya.puspita@school.com', 'specialization' => 'Indonesian Language', 'education_level' => 'S2 (Master)', 'experience_years' => 9],
            ['name' => 'Eko Prasetyo, S.Kom', 'email' => 'eko.prasetyo@school.com', 'specialization' => 'Computer Science', 'education_level' => 'S1 (Bachelor)', 'experience_years' => 6],
            ['name' => 'Linda Wijaya, M.Pd', 'email' => 'linda.wijaya@school.com', 'specialization' => 'History', 'education_level' => 'S2 (Master)', 'experience_years' => 11],
            ['name' => 'Agus Setiawan, S.Sos', 'email' => 'agus.setiawan@school.com', 'specialization' => 'Geography', 'education_level' => 'S1 (Bachelor)', 'experience_years' => 5],
            ['name' => 'Rina Marlina, M.Sn', 'email' => 'rina.marlina@school.com', 'specialization' => 'Arts', 'education_level' => 'S2 (Master)', 'experience_years' => 8],
        ];

        foreach ($instructors as $index => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'instructor',
                'level' => null,
            ]);

            $instructorCode = 'INS' . date('Y') . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $instructor = Instructor::create([
                'user_id' => $user->id,
                'instructor_code' => $instructorCode,
                'full_name' => $data['name'],
                'email' => $data['email'],
                'phone' => '08' . rand(1000000000, 9999999999),
                'specialization' => $data['specialization'],
                'education_level' => $data['education_level'],
                'experience_years' => $data['experience_years'],
                'bio' => "Experienced {$data['specialization']} instructor with {$data['experience_years']} years of teaching experience.",
                'status' => 'active',
            ]);

            $this->command->info("✅ Created: {$instructor->full_name} ({$instructorCode})");
        }

        $this->command->info("🎉 Successfully created " . count($instructors) . " instructors!");
    }
}
