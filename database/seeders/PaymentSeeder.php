<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $calonSiswa = User::where('role', 'calon_siswa')->get();

        if ($students->isEmpty() && $calonSiswa->isEmpty()) {
            $this->command->warn('No students or calon siswa found. Please run StudentSeeder and StudentRegistrationSeeder first.');
            return;
        }

        // 1. Seed Registration Payments for Calon Siswa
        foreach ($calonSiswa as $user) {
            // 80% have paid registration fee
            if (rand(1, 100) <= 80) {
                Payment::create([
                    'user_id' => $user->id,
                    'payment_type' => 'registration_fee',
                    'amount' => 500000, // Rp 500.000
                    'status' => 'paid',
                    'due_date' => Carbon::now()->subDays(rand(1, 30)),
                    'paid_date' => Carbon::now()->subDays(rand(0, 29)),
                    'payment_method' => ['bank_transfer', 'credit_card', 'ewallet'][rand(0, 2)],
                    'transaction_id' => 'TRX-' . strtoupper(Str::random(10)),
                ]);
            } else {
                // 20% pending
                Payment::create([
                    'user_id' => $user->id,
                    'payment_type' => 'registration_fee',
                    'amount' => 500000,
                    'status' => 'pending',
                    'due_date' => Carbon::now()->addDays(rand(1, 7)),
                    'payment_method' => 'bank_transfer',
                    'transaction_id' => 'TRX-' . strtoupper(Str::random(10)),
                ]);
            }
        }

        // 2. Seed Tuition Payments for Active Students
        foreach ($students as $student) {
            // Create 3-6 months of tuition history
            $months = rand(3, 6);
            
            for ($i = 0; $i < $months; $i++) {
                $date = Carbon::now()->subMonths($i);
                
                Payment::create([
                    'user_id' => $student->id,
                    'payment_type' => 'tuition_fee',
                    'amount' => 1500000, // Rp 1.500.000
                    'status' => 'paid',
                    'due_date' => $date->copy()->startOfMonth()->addDays(10),
                    'paid_date' => $date->copy()->startOfMonth()->addDays(rand(1, 15)),
                    'payment_method' => ['bank_transfer', 'virtual_account'][rand(0, 1)],
                    'transaction_id' => 'SPP-' . strtoupper(Str::random(10)),
                ]);
            }

            // Create current month payment (maybe pending)
            if (rand(1, 100) <= 30) {
                Payment::create([
                    'user_id' => $student->id,
                    'payment_type' => 'tuition_fee',
                    'amount' => 1500000,
                    'status' => 'pending',
                    'due_date' => Carbon::now()->endOfMonth(),
                    'payment_method' => 'virtual_account',
                    'transaction_id' => 'SPP-' . strtoupper(Str::random(10)),
                ]);
            }
        }

        $this->command->info('Payments seeded successfully!');
        $this->command->info('Total payments: ' . Payment::count());
    }
}
