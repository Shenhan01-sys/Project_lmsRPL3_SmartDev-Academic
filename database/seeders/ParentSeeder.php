<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👨‍👩‍👧‍👦 Creating Parent profiles...');

        $parentNames = [
            ['full_name' => 'Bambang Suryanto', 'email' => 'bambang.suryanto@parent.com', 'relationship' => 'Father', 'occupation' => 'Businessman'],
            ['full_name' => 'Sri Handayani', 'email' => 'sri.handayani@parent.com', 'relationship' => 'Mother', 'occupation' => 'Teacher'],
            ['full_name' => 'Darmawan Wijaya', 'email' => 'darmawan.wijaya@parent.com', 'relationship' => 'Father', 'occupation' => 'Engineer'],
            ['full_name' => 'Nurul Azizah', 'email' => 'nurul.azizah@parent.com', 'relationship' => 'Mother', 'occupation' => 'Doctor'],
            ['full_name' => 'Hendra Gunawan', 'email' => 'hendra.gunawan@parent.com', 'relationship' => 'Father', 'occupation' => 'Lawyer'],
            ['full_name' => 'Ratna Sari', 'email' => 'ratna.sari@parent.com', 'relationship' => 'Mother', 'occupation' => 'Accountant'],
            ['full_name' => 'Agung Prabowo', 'email' => 'agung.prabowo@parent.com', 'relationship' => 'Father', 'occupation' => 'Government Officer'],
            ['full_name' => 'Lina Kusuma', 'email' => 'lina.kusuma@parent.com', 'relationship' => 'Mother', 'occupation' => 'Entrepreneur'],
            ['full_name' => 'Dedi Susanto', 'email' => 'dedi.susanto@parent.com', 'relationship' => 'Father', 'occupation' => 'Architect'],
            ['full_name' => 'Wati Rahayu', 'email' => 'wati.rahayu@parent.com', 'relationship' => 'Mother', 'occupation' => 'Nurse'],
            ['full_name' => 'Andi Saputra', 'email' => 'andi.saputra@parent.com', 'relationship' => 'Father', 'occupation' => 'IT Manager'],
            ['full_name' => 'Rina Permata', 'email' => 'rina.permata@parent.com', 'relationship' => 'Mother', 'occupation' => 'Pharmacist'],
            ['full_name' => 'Budi Hartanto', 'email' => 'budi.hartanto@parent.com', 'relationship' => 'Father', 'occupation' => 'Bank Manager'],
            ['full_name' => 'Sari Dewi', 'email' => 'sari.dewi@parent.com', 'relationship' => 'Mother', 'occupation' => 'Designer'],
            ['full_name' => 'Yanto Prasetyo', 'email' => 'yanto.prasetyo@parent.com', 'relationship' => 'Father', 'occupation' => 'Consultant'],
            ['full_name' => 'Maya Anggraini', 'email' => 'maya.anggraini@parent.com', 'relationship' => 'Mother', 'occupation' => 'Marketing Manager'],
            ['full_name' => 'Rizki Firmansyah', 'email' => 'rizki.firmansyah@parent.com', 'relationship' => 'Father', 'occupation' => 'Sales Director'],
            ['full_name' => 'Dwi Lestari', 'email' => 'dwi.lestari@parent.com', 'relationship' => 'Mother', 'occupation' => 'HR Manager'],
            ['full_name' => 'Fajar Ramadhan', 'email' => 'fajar.ramadhan@parent.com', 'relationship' => 'Father', 'occupation' => 'Professor'],
            ['full_name' => 'Indah Permatasari', 'email' => 'indah.permatasari@parent.com', 'relationship' => 'Mother', 'occupation' => 'Journalist'],
        ];

        foreach ($parentNames as $index => $data) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'parent',
                'level' => null,
            ]);

            $parent = ParentModel::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => '08' . rand(1000000000, 9999999999),
                'relationship' => $data['relationship'],
                'occupation' => $data['occupation'],
                'address' => fake()->address(),
            ]);

            $this->command->info("✅ Created: {$parent->full_name} ({$parent->relationship} - {$parent->occupation})");
        }

        $this->command->info("🎉 Successfully created " . count($parentNames) . " parents!");
    }
}
