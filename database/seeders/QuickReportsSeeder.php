<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuickReportsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting quick reports data seeding...');
        
        // Get users (excluding owner)
        $employees = DB::table('users')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users as u2')
                    ->whereColumn('u2.id', 'users.id')
                    ->where('u2.userRole', 'owner');
            })
            ->pluck('id')
            ->toArray();

        if (empty($employees)) {
            $this->command->info('No employees found.');
            return;
        }

        // Seed for September 2025 only (last month)
        $start = Carbon::parse('2025-09-01');
        $end = Carbon::parse('2025-09-30');
        
        $this->seedAttendance($employees, $start, $end);
        $this->seedSchedules($employees, $start, $end);
        
        $this->command->info('Quick seeding completed!');
    }

    private function seedAttendance($employees, $start, $end): void
    {
        $this->command->info('Seeding attendance...');
        
        $attendanceRecords = [];
        $date = $start->copy();
        
        while ($date->lte($end)) {
            if (!$date->isWeekend()) {
                foreach ($employees as $employeeId) {
                    // Varied performance by employee ID
                    $performance = $employeeId % 4;
                    $rand = rand(1, 100);
                    
                    // Determine status based on performance
                    if ($performance === 0 && $rand <= 95) { // Excellent
                        $status = rand(1, 10) <= 8 ? 'on_time' : 'late';
                    } elseif ($performance === 1 && $rand <= 85) { // Good
                        $status = rand(1, 10) <= 6 ? 'on_time' : 'late';
                    } elseif ($performance === 2 && $rand <= 75) { // Average
                        $status = rand(1, 10) <= 4 ? 'on_time' : 'late';
                    } elseif ($performance === 3 && $rand <= 60) { // Poor
                        $status = rand(1, 10) <= 3 ? 'on_time' : 'late';
                    } else {
                        $status = 'missed';
                    }
                    
                    if ($status === 'on_time') {
                        $signIn = sprintf('%02d:%02d:00', rand(7, 8), rand(0, 59));
                        $signOff = sprintf('%02d:%02d:00', rand(16, 18), rand(0, 59));
                        $notes = 'Regular attendance';
                    } elseif ($status === 'late') {
                        $signIn = sprintf('%02d:%02d:00', rand(8, 10), rand(0, 59));
                        $signOff = sprintf('%02d:%02d:00', rand(16, 18), rand(0, 59));
                        $notes = 'Traffic delay';
                    } else {
                        $signIn = null;
                        $signOff = null;
                        $notes = 'Absent';
                    }
                    
                    $attendanceRecords[] = [
                        'user_id' => $employeeId,
                        'date' => $date->toDateString(),
                        'status' => $status,
                        'sign_in_time' => $signIn,
                        'sign_off_time' => $signOff,
                        'notes' => $notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $date->addDay();
        }
        
        // Insert in large chunks
        foreach (array_chunk($attendanceRecords, 500) as $chunk) {
            DB::table('attendances')->insertOrIgnore($chunk);
        }
        
        $this->command->info('Added ' . count($attendanceRecords) . ' attendance records');
    }

    private function seedSchedules($employees, $start, $end): void
    {
        $this->command->info('Seeding schedules...');
        
        $scheduleRecords = [];
        $date = $start->copy();
        
        while ($date->lte($end)) {
            if (!$date->isWeekend()) {
                foreach ($employees as $employeeId) {
                    foreach (['morning', 'evening'] as $shiftType) {
                        $scheduleRecords[] = [
                            'user_id' => $employeeId,
                            'shift_type' => $shiftType,
                            'week_start' => $date->copy()->startOfWeek()->toDateString(),
                            'day' => $date->toDateString(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            $date->addDay();
        }
        
        // Insert schedules in chunks
        foreach (array_chunk($scheduleRecords, 500) as $chunk) {
            DB::table('shiftschedules')->insert($chunk);
        }
        
        $this->command->info('Added ' . count($scheduleRecords) . ' schedule records');
    }
}

