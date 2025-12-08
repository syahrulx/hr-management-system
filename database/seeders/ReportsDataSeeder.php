<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;

class ReportsDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get all existing users (excluding owner)
        $employees = User::where('userRole', '!=', 'owner')->get();

        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run the main seeder first.');
            return;
        }

        $this->command->info('Adding comprehensive data for ' . $employees->count() . ' employees...');

        // Add attendance data for the last month
        $this->seedAttendanceData($employees);
        
        // Task data seeding removed

        $this->command->info('Reports data seeding completed successfully!');
    }

    private function seedAttendanceData($employees): void
    {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();

        // Get all existing attendance records for the date range
        $existingAttendances = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(function($attendance) {
                return $attendance->user_id . '_' . $attendance->date;
            })
            ->toArray();

        $attendanceData = [];
        
        foreach ($employees as $employee) {
            $date = $start->copy();
            
            while ($date->lte($end)) {
                // Skip weekends
                if (!$date->isWeekend()) {
                    $key = $employee->id . '_' . $date->toDateString();
                    
                    // Check if attendance already exists
                    if (!in_array($key, $existingAttendances)) {
                        $attendanceStatus = $this->getAttendanceStatus($employee);
                        
                        if ($attendanceStatus !== 'missed') {
                            $signInTime = $this->getSignInTime($attendanceStatus);
                            $signOffTime = $this->getSignOffTime($attendanceStatus);
                            
                            $attendanceData[] = [
                                'user_id' => $employee->id,
                                'date' => $date->toDateString(),
                                'status' => $attendanceStatus,
                                'sign_in_time' => $signInTime,
                                'sign_off_time' => $signOffTime,
                                'notes' => $this->getAttendanceNotes($attendanceStatus),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            $attendanceData[] = [
                                'user_id' => $employee->id,
                                'date' => $date->toDateString(),
                                'status' => 'missed',
                                'sign_in_time' => null,
                                'sign_off_time' => null,
                                'notes' => 'Absent - No show',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                $date->addDay();
            }
        }

        // Bulk insert all attendance records
        if (!empty($attendanceData)) {
            Attendance::insert($attendanceData);
            $this->command->info("Added " . count($attendanceData) . " attendance records in bulk");
        } else {
            $this->command->info("No new attendance records to add");
        }
    }


    private function getAttendanceStatus($employee): string
    {
        // Create diverse performance patterns based on employee ID
        $employeeId = $employee->id;
        
        // Different performance profiles based on employee
        switch ($employeeId % 4) {
            case 0: // Excellent performer
                $rand = rand(1, 100);
                if ($rand <= 95) {
                    return rand(1, 10) <= 8 ? 'on_time' : 'late';
                }
                return 'missed';
                
            case 1: // Good performer
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    return rand(1, 10) <= 6 ? 'on_time' : 'late';
                }
                return 'missed';
                
            case 2: // Average performer
                $rand = rand(1, 100);
                if ($rand <= 75) {
                    return rand(1, 10) <= 4 ? 'on_time' : 'late';
                }
                return 'missed';
                
            case 3: // Poor performer
                $rand = rand(1, 100);
                if ($rand <= 60) {
                    return rand(1, 10) <= 3 ? 'on_time' : 'late';
                }
                return 'missed';
                
            default:
                return rand(1, 10) <= 7 ? 'on_time' : 'late';
        }
    }

    private function getSignInTime($status): string
    {
        switch ($status) {
            case 'on_time':
                return sprintf('%02d:%02d:00', rand(7, 8), rand(0, 59));
            case 'late':
                return sprintf('%02d:%02d:00', rand(8, 10), rand(0, 59));
            default:
                return '08:00:00';
        }
    }

    private function getSignOffTime($status): string
    {
        return sprintf('%02d:%02d:00', rand(16, 18), rand(0, 59));
    }

    private function getAttendanceNotes($status): string
    {
        $notes = [
            'on_time' => ['Regular attendance', 'On time as usual', 'Good attendance'],
            'late' => ['Traffic delay', 'Personal emergency', 'Overslept', 'Transportation issue'],
            'missed' => ['Sick leave', 'Personal emergency', 'No show']
        ];
        
        return $notes[$status][array_rand($notes[$status])];
    }

    
}
