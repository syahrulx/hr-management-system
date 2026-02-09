<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * HR Management System - Full Database Seeder
 * Matches: seed2026full.sql
 * 
 * Data Period: January 1 - February 8, 2026 (39 days)
 * Users: 6 (1 Owner, 1 Admin/Supervisor, 4 Employees)
 * Shifts: 104 | Attendances: 104 | Leave Requests: 5
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('leave_requests')->truncate();
        DB::table('attendances')->truncate();
        DB::table('shift_schedules')->truncate();
        DB::table('users')->truncate();

        $password = Hash::make('password');

        // ============================================================================
        // USERS (6 total)
        // ============================================================================
        DB::table('users')->insert([
            ['user_id' => 1, 'name' => 'Owner User', 'phone' => '60123456789', 'email' => 'owner@myhrsolutions.my', 'ic_number' => '800101015522', 'password' => $password, 'address' => '123 Business Tower, Kuala Lumpur', 'hired_on' => '2020-01-01', 'user_role' => 'owner', 'annual_leave_balance' => 0, 'sick_leave_balance' => 0, 'emergency_leave_balance' => 0],
            ['user_id' => 2, 'name' => 'Ahmad Razif Bin Ismail', 'phone' => '60123456790', 'email' => 'ahmad.razif@myhrsolutions.my', 'ic_number' => '900101145522', 'password' => $password, 'address' => '456 Admin Street, Petaling Jaya', 'hired_on' => '2021-03-15', 'user_role' => 'admin', 'annual_leave_balance' => 13, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 3, 'name' => 'Siti Noraini Binti Ahmad', 'phone' => '60123456791', 'email' => 'siti.noraini@myhrsolutions.my', 'ic_number' => '950515105530', 'password' => $password, 'address' => '789 Employee Lane, Shah Alam', 'hired_on' => '2022-06-01', 'user_role' => 'employee', 'annual_leave_balance' => 13, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 4, 'name' => 'Kumaran A/L Subramaniam', 'phone' => '60123456792', 'email' => 'kumaran@myhrsolutions.my', 'ic_number' => '880305125522', 'password' => $password, 'address' => '654 Tech Park, Cyberjaya', 'hired_on' => '2023-01-10', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 13, 'emergency_leave_balance' => 7],
            ['user_id' => 5, 'name' => 'Tan Mei Ling', 'phone' => '60123456793', 'email' => 'tan.meiling@myhrsolutions.my', 'ic_number' => '960712105522', 'password' => $password, 'address' => '987 Commerce Center, Bangsar', 'hired_on' => '2023-03-20', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 6, 'name' => 'Muhammad Danial Bin Hassan', 'phone' => '60123456794', 'email' => 'danial@myhrsolutions.my', 'ic_number' => '970825085566', 'password' => $password, 'address' => '321 Garden Heights, Subang Jaya', 'hired_on' => '2024-02-01', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
        ]);

        // ============================================================================
        // SHIFT_SCHEDULES (104 total)
        // ============================================================================
        $shifts = [
            // JANUARY EMPLOYEE SHIFTS (IDs 1-62)
            ['shift_id' => 1, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-01'],
            ['shift_id' => 2, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-01'],
            ['shift_id' => 3, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-02'],
            ['shift_id' => 4, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-02'],
            ['shift_id' => 5, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-03'],
            ['shift_id' => 6, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-03'],
            ['shift_id' => 7, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-04'],
            ['shift_id' => 8, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-01-04'],
            ['shift_id' => 9, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-05'],
            ['shift_id' => 10, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-05'],
            ['shift_id' => 11, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-06'],
            ['shift_id' => 12, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-06'],
            ['shift_id' => 13, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-07'],
            ['shift_id' => 14, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-01-07'],
            ['shift_id' => 15, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-08'],
            ['shift_id' => 16, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-08'],
            ['shift_id' => 17, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-09'],
            ['shift_id' => 18, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-09'],
            ['shift_id' => 19, 'user_id' => 6, 'shift_type' => 'morning', 'shift_date' => '2026-01-10'],
            ['shift_id' => 20, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-10'],
            ['shift_id' => 21, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-11'],
            ['shift_id' => 22, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-01-11'],
            ['shift_id' => 23, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-12'],
            ['shift_id' => 24, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-12'],
            ['shift_id' => 25, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-13'],
            ['shift_id' => 26, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-13'],
            ['shift_id' => 27, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-14'],
            ['shift_id' => 28, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-14'],
            ['shift_id' => 29, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-15'],
            ['shift_id' => 30, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-15'],
            ['shift_id' => 31, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-16'],
            ['shift_id' => 32, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-16'],
            ['shift_id' => 33, 'user_id' => 6, 'shift_type' => 'morning', 'shift_date' => '2026-01-17'],
            ['shift_id' => 34, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-17'],
            ['shift_id' => 35, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-18'],
            ['shift_id' => 36, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-01-18'],
            ['shift_id' => 37, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-19'],
            ['shift_id' => 38, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-19'],
            ['shift_id' => 39, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-20'],
            ['shift_id' => 40, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-20'],
            ['shift_id' => 41, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-21'],
            ['shift_id' => 42, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-21'],
            ['shift_id' => 43, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-22'],
            ['shift_id' => 44, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-22'],
            ['shift_id' => 45, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-23'],
            ['shift_id' => 46, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-23'],
            ['shift_id' => 47, 'user_id' => 6, 'shift_type' => 'morning', 'shift_date' => '2026-01-24'],
            ['shift_id' => 48, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-24'],
            ['shift_id' => 49, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-25'],
            ['shift_id' => 50, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-01-25'],
            ['shift_id' => 51, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-26'],
            ['shift_id' => 52, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-26'],
            ['shift_id' => 53, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-27'],
            ['shift_id' => 54, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-27'],
            ['shift_id' => 55, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-28'],
            ['shift_id' => 56, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-01-28'],
            ['shift_id' => 57, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-01-29'],
            ['shift_id' => 58, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-29'],
            ['shift_id' => 59, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-01-30'],
            ['shift_id' => 60, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-01-30'],
            ['shift_id' => 61, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-01-31'],
            ['shift_id' => 62, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-01-31'],
            // FEBRUARY EMPLOYEE SHIFTS (IDs 63-78)
            ['shift_id' => 63, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-02-01'],
            ['shift_id' => 64, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-02-01'],
            ['shift_id' => 65, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-02-02'],
            ['shift_id' => 66, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-02-02'],
            ['shift_id' => 67, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-02-03'],
            ['shift_id' => 68, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-02-03'],
            ['shift_id' => 69, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-02-04'],
            ['shift_id' => 70, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-02-04'],
            ['shift_id' => 71, 'user_id' => 4, 'shift_type' => 'morning', 'shift_date' => '2026-02-05'],
            ['shift_id' => 72, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2026-02-05'],
            ['shift_id' => 73, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2026-02-06'],
            ['shift_id' => 74, 'user_id' => 3, 'shift_type' => 'evening', 'shift_date' => '2026-02-06'],
            ['shift_id' => 75, 'user_id' => 6, 'shift_type' => 'morning', 'shift_date' => '2026-02-07'],
            ['shift_id' => 76, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2026-02-07'],
            ['shift_id' => 77, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2026-02-08'],
            ['shift_id' => 78, 'user_id' => 5, 'shift_type' => 'evening', 'shift_date' => '2026-02-08'],
            // SUPERVISOR SHIFTS (IDs 79-104)
            ['shift_id' => 79, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-01'],
            ['shift_id' => 80, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-02'],
            ['shift_id' => 81, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-05'],
            ['shift_id' => 82, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-06'],
            ['shift_id' => 83, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-07'],
            ['shift_id' => 84, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-08'],
            ['shift_id' => 85, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-09'],
            ['shift_id' => 86, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-12'],
            ['shift_id' => 87, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-13'],
            ['shift_id' => 88, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-14'],
            // Jan 15: SUPERVISOR LEAVE (no shift)
            ['shift_id' => 89, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-16'],
            ['shift_id' => 90, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-19'],
            ['shift_id' => 91, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-20'],
            ['shift_id' => 92, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-21'],
            ['shift_id' => 93, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-22'],
            ['shift_id' => 94, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-23'],
            ['shift_id' => 95, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-26'],
            ['shift_id' => 96, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-27'],
            ['shift_id' => 97, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-28'],
            ['shift_id' => 98, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-29'],
            ['shift_id' => 99, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-01-30'],
            ['shift_id' => 100, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-02-02'],
            ['shift_id' => 101, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-02-03'],
            ['shift_id' => 102, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-02-04'],
            ['shift_id' => 103, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-02-05'],
            ['shift_id' => 104, 'user_id' => 2, 'shift_type' => 'office', 'shift_date' => '2026-02-06'],
        ];
        DB::table('shift_schedules')->insert($shifts);

        // ============================================================================
        // ATTENDANCES (104 total)
        // ============================================================================
        $attendances = [
            // JANUARY EMPLOYEE ATTENDANCES (IDs 1-62)
            ['attendance_id' => 1, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:02:00', 'clock_out_time' => '15:00:00', 'shift_id' => 1],
            ['attendance_id' => 2, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 2],
            ['attendance_id' => 3, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 3],
            ['attendance_id' => 4, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:05:00', 'clock_out_time' => '00:00:00', 'shift_id' => 4],
            ['attendance_id' => 5, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:03:00', 'clock_out_time' => '15:00:00', 'shift_id' => 5],
            ['attendance_id' => 6, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 6],
            ['attendance_id' => 7, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 7],
            ['attendance_id' => 8, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 8],
            ['attendance_id' => 9, 'user_id' => 3, 'status' => 'late', 'clock_in_time' => '06:18:00', 'clock_out_time' => '15:00:00', 'shift_id' => 9], // 18 min late
            ['attendance_id' => 10, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 10],
            ['attendance_id' => 11, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 11],
            ['attendance_id' => 12, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 12],
            ['attendance_id' => 13, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:05:00', 'clock_out_time' => '15:00:00', 'shift_id' => 13],
            ['attendance_id' => 14, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 14],
            ['attendance_id' => 15, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 15],
            ['attendance_id' => 16, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 16],
            ['attendance_id' => 17, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 17],
            ['attendance_id' => 18, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 18],
            ['attendance_id' => 19, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 19],
            ['attendance_id' => 20, 'user_id' => 4, 'status' => 'late', 'clock_in_time' => '15:20:00', 'clock_out_time' => '00:00:00', 'shift_id' => 20], // 20 min late
            ['attendance_id' => 21, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 21],
            ['attendance_id' => 22, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 22],
            ['attendance_id' => 23, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 23],
            ['attendance_id' => 24, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 24],
            ['attendance_id' => 25, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 25],
            ['attendance_id' => 26, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 26],
            ['attendance_id' => 27, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 27],
            ['attendance_id' => 28, 'user_id' => 3, 'status' => 'late', 'clock_in_time' => '15:22:00', 'clock_out_time' => '00:00:00', 'shift_id' => 28], // 22 min late
            ['attendance_id' => 29, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 29],
            ['attendance_id' => 30, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 30],
            ['attendance_id' => 31, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 31],
            ['attendance_id' => 32, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 32],
            ['attendance_id' => 33, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 33],
            ['attendance_id' => 34, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 34],
            ['attendance_id' => 35, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 35],
            ['attendance_id' => 36, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 36],
            ['attendance_id' => 37, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 37],
            ['attendance_id' => 38, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 38],
            ['attendance_id' => 39, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 39],
            ['attendance_id' => 40, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 40],
            ['attendance_id' => 41, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 41],
            ['attendance_id' => 42, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 42],
            ['attendance_id' => 43, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 43],
            ['attendance_id' => 44, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 44],
            ['attendance_id' => 45, 'user_id' => 5, 'status' => 'late', 'clock_in_time' => '06:25:00', 'clock_out_time' => '15:00:00', 'shift_id' => 45], // 25 min late
            ['attendance_id' => 46, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 46],
            ['attendance_id' => 47, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 47],
            ['attendance_id' => 48, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 48],
            ['attendance_id' => 49, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 49],
            ['attendance_id' => 50, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 50],
            ['attendance_id' => 51, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 51],
            ['attendance_id' => 52, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 52],
            ['attendance_id' => 53, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 53],
            ['attendance_id' => 54, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 54],
            ['attendance_id' => 55, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 55],
            ['attendance_id' => 56, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 56],
            ['attendance_id' => 57, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 57],
            ['attendance_id' => 58, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 58],
            ['attendance_id' => 59, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 59],
            ['attendance_id' => 60, 'user_id' => 6, 'status' => 'late', 'clock_in_time' => '15:18:00', 'clock_out_time' => '00:00:00', 'shift_id' => 60], // 18 min late
            ['attendance_id' => 61, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 61],
            ['attendance_id' => 62, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 62],
            // FEBRUARY EMPLOYEE ATTENDANCES (IDs 63-78)
            ['attendance_id' => 63, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 63],
            ['attendance_id' => 64, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 64],
            ['attendance_id' => 65, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 65],
            ['attendance_id' => 66, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 66],
            ['attendance_id' => 67, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 67],
            ['attendance_id' => 68, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 68],
            ['attendance_id' => 69, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 69],
            ['attendance_id' => 70, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 70],
            ['attendance_id' => 71, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 71],
            ['attendance_id' => 72, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 72],
            ['attendance_id' => 73, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 73],
            ['attendance_id' => 74, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 74],
            ['attendance_id' => 75, 'user_id' => 6, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 75],
            ['attendance_id' => 76, 'user_id' => 4, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 76],
            ['attendance_id' => 77, 'user_id' => 3, 'status' => 'on_time', 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'shift_id' => 77],
            ['attendance_id' => 78, 'user_id' => 5, 'status' => 'on_time', 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'shift_id' => 78],
            // SUPERVISOR ATTENDANCES (IDs 79-104)
            ['attendance_id' => 79, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 79],
            ['attendance_id' => 80, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:02:00', 'clock_out_time' => '17:00:00', 'shift_id' => 80],
            ['attendance_id' => 81, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 81],
            ['attendance_id' => 82, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 82],
            ['attendance_id' => 83, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:05:00', 'clock_out_time' => '17:00:00', 'shift_id' => 83],
            ['attendance_id' => 84, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 84],
            ['attendance_id' => 85, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 85],
            ['attendance_id' => 86, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 86],
            ['attendance_id' => 87, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 87],
            ['attendance_id' => 88, 'user_id' => 2, 'status' => 'late', 'clock_in_time' => '09:20:00', 'clock_out_time' => '17:00:00', 'shift_id' => 88], // 20 min late
            // Jan 15: SUPERVISOR LEAVE (no attendance)
            ['attendance_id' => 89, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 89],
            ['attendance_id' => 90, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 90],
            ['attendance_id' => 91, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 91],
            ['attendance_id' => 92, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 92],
            ['attendance_id' => 93, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 93],
            ['attendance_id' => 94, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 94],
            ['attendance_id' => 95, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 95],
            ['attendance_id' => 96, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 96],
            ['attendance_id' => 97, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 97],
            ['attendance_id' => 98, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 98],
            ['attendance_id' => 99, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 99],
            ['attendance_id' => 100, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 100],
            ['attendance_id' => 101, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 101],
            ['attendance_id' => 102, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 102],
            ['attendance_id' => 103, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 103],
            ['attendance_id' => 104, 'user_id' => 2, 'status' => 'on_time', 'clock_in_time' => '09:00:00', 'clock_out_time' => '17:00:00', 'shift_id' => 104],
        ];
        DB::table('attendances')->insert($attendances);

        // ============================================================================
        // LEAVE_REQUESTS (5 total)
        // ============================================================================
        DB::table('leave_requests')->insert([
            ['request_id' => 1, 'user_id' => 3, 'type' => 'Annual Leave', 'start_date' => '2026-01-15', 'end_date' => '2026-01-15', 'status' => 1, 'remark' => 'Personal matters'],
            ['request_id' => 2, 'user_id' => 4, 'type' => 'Sick Leave', 'start_date' => '2026-01-22', 'end_date' => '2026-01-22', 'status' => 1, 'remark' => 'Medical appointment'],
            ['request_id' => 3, 'user_id' => 5, 'type' => 'Annual Leave', 'start_date' => '2026-01-10', 'end_date' => '2026-01-10', 'status' => 2, 'remark' => 'Trip - rejected'],
            ['request_id' => 4, 'user_id' => 6, 'type' => 'Emergency Leave', 'start_date' => '2026-01-28', 'end_date' => '2026-01-28', 'status' => 2, 'remark' => 'Family issue - rejected'],
            ['request_id' => 5, 'user_id' => 2, 'type' => 'Annual Leave', 'start_date' => '2026-01-15', 'end_date' => '2026-01-15', 'status' => 1, 'remark' => 'Personal day off'],
        ]);

        // Sync auto-increment sequences (PostgreSQL)
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'user_id'), (SELECT MAX(user_id) FROM users))");
        DB::statement("SELECT setval(pg_get_serial_sequence('shift_schedules', 'shift_id'), (SELECT MAX(shift_id) FROM shift_schedules))");
        DB::statement("SELECT setval(pg_get_serial_sequence('attendances', 'attendance_id'), (SELECT MAX(attendance_id) FROM attendances))");
        DB::statement("SELECT setval(pg_get_serial_sequence('leave_requests', 'request_id'), (SELECT MAX(request_id) FROM leave_requests))");
    }
}
