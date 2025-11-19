<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\ParentModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👨‍🎓 Creating Student profiles...');

        $parents = ParentModel::all();

        if ($parents->isEmpty()) {
            $this->command->warn('⚠️ No parents found! Running ParentSeeder first...');
            $this->call(ParentSeeder::class);
            $parents = ParentModel::all();
        }

        $studentNames = [
            ['name' => 'Ahmad Fauzi', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Siti Aminah', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Rizki Ramadhan', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Dewi Putri', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Andi Pratama', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Lina Marlina', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Budi Santoso', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Maya Kusuma', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Dimas Aditya', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Rina Wulandari', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Fajar Maulana', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Ayu Lestari', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Eko Prasetyo', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Wulan Sari', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Rafi Ahmad', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Putri Rahayu', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Yoga Pratama', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Sari Indah', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Arif Hidayat', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Dian Permata', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Galih Nugroho', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Intan Permatasari', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Hendra Setiawan', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Nisa Aulia', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Irfan Maulana', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Laila Nur', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Joko Susilo', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Kartika Dewi', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Kevin Saputra', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Linda Wijaya', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Malik Ibrahim', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Mira Anggita', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Nanda Pratama', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Olivia Marsha', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Pradana Putra', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Qorina Salsabila', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Rama Wijaya', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Safira Maharani', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Taufik Hidayat', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Ulfah Zahra', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Vino Bastian', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Widya Ningrum', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Xavier Anggara', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Yuni Shara', 'gender' => 'female', 'grade' => '12'],
            ['name' => 'Zaki Rahman', 'gender' => 'male', 'grade' => '10'],
            ['name' => 'Alya Putri', 'gender' => 'female', 'grade' => '10'],
            ['name' => 'Bagas Prasetyo', 'gender' => 'male', 'grade' => '11'],
            ['name' => 'Citra Kirana', 'gender' => 'female', 'grade' => '11'],
            ['name' => 'Daniel Sahrul', 'gender' => 'male', 'grade' => '12'],
            ['name' => 'Elsa Pitaloka', 'gender' => 'female', 'grade' => '12'],
        ];

        $currentYear = date('Y');
        $parentIndex = 0;

        foreach ($studentNames as $index => $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@student.com';

            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'student',
                'level' => 'SMA',
            ]);

            $studentNumber = 'STD' . $currentYear . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            $parent = $parents[$parentIndex];
            
            $age = 15;
            if ($data['grade'] == '11') $age = 16;
            if ($data['grade'] == '12') $age = 17;
            
            $birthYear = $currentYear - $age;
            $dateOfBirth = $birthYear . '-' . rand(1, 12) . '-' . rand(1, 28);

            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentNumber,
                'full_name' => $data['name'],
                'email' => $email,
                'phone' => '08' . rand(1000000000, 9999999999),
                'date_of_birth' => $dateOfBirth,
                'gender' => $data['gender'],
                'address' => fake()->address(),
                'emergency_contact_name' => $parent->full_name,
                'emergency_contact_phone' => $parent->phone,
                'parent_id' => $parent->id,
                'enrollment_year' => $currentYear - ($age - 15),
                'current_grade' => $data['grade'],
                'status' => 'active',
            ]);

            $this->command->info("✅ Created: {$student->full_name} ({$studentNumber}) - Grade {$data['grade']}");

            if (($index + 1) % 2 == 0 || ($index + 1) % 3 == 0) {
                $parentIndex++;
                if ($parentIndex >= $parents->count()) {
                    $parentIndex = 0;
                }
            }
        }

        $this->command->info("🎉 Successfully created " . count($studentNames) . " students!");
    }
}
