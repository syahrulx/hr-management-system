<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        LeaveRequest::truncate();
        Attendance::truncate();
        Schedule::truncate();
        User::truncate();

        // Reset sequences
        DB::statement("SELECT setval(pg_get_serial_sequence('users','user_id'), 1, false)");
        DB::statement("SELECT setval(pg_get_serial_sequence('shift_schedules','shift_id'), 1, false)");
        DB::statement("SELECT setval(pg_get_serial_sequence('attendances','attendance_id'), 1, false)");
        DB::statement("SELECT setval(pg_get_serial_sequence('leave_requests','request_id'), 1, false)");

        $this->seedUsers();
        $this->seedShiftSchedules();
        $this->seedAttendances();
        $this->seedLeaveRequests();

        $this->command->info('✅ Database seeded successfully!');
    }

    private function seedUsers(): void
    {
        $this->command->info('Seeding users...');

        $users = [
            // Owner Account
            ['user_id' => 1, 'name' => 'Owner User', 'email' => 'owner@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456789', 'ic_number' => '800101015522', 'address' => '123 Business Tower, Kuala Lumpur', 'hired_on' => '2020-01-01', 'user_role' => 'owner', 'annual_leave_balance' => 0, 'sick_leave_balance' => 0, 'emergency_leave_balance' => 0],
            // Admin Account
            ['user_id' => 2, 'name' => 'Ahmad Razif Bin Ismail', 'email' => 'ahmad.razif@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456790', 'ic_number' => '900101145522', 'address' => '456 Admin Street, Petaling Jaya', 'hired_on' => '2021-03-15', 'user_role' => 'admin', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            // Employees
            ['user_id' => 3, 'name' => 'Siti Noraini Binti Ahmad', 'email' => 'siti.noraini@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456791', 'ic_number' => '950515105522', 'address' => '789 Employee Lane, Shah Alam', 'hired_on' => '2022-06-01', 'user_role' => 'employee', 'annual_leave_balance' => 11, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 4, 'name' => 'Sarah Johnson', 'email' => 'sarah.johnson@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456792', 'ic_number' => '920824085522', 'address' => '321 Garden Avenue, Subang Jaya', 'hired_on' => '2022-08-15', 'user_role' => 'employee', 'annual_leave_balance' => 11, 'sick_leave_balance' => 13, 'emergency_leave_balance' => 7],
            ['user_id' => 5, 'name' => 'Kumaran A/L Subramaniam', 'email' => 'kumaran@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456793', 'ic_number' => '880305125522', 'address' => '654 Tech Park, Cyberjaya', 'hired_on' => '2023-01-10', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 6, 'name' => 'Tan Mei Ling', 'email' => 'tan.meiling@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456794', 'ic_number' => '960712105522', 'address' => '987 Commerce Center, Bangsar', 'hired_on' => '2023-03-20', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 7, 'name' => 'Ahmad Faiz Bin Rahman', 'email' => 'ahmad.faiz@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456007', 'ic_number' => '900411045522', 'address' => '12 Jalan Seri, Kota Kemuning', 'hired_on' => '2021-11-01', 'user_role' => 'employee', 'annual_leave_balance' => 11, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 8, 'name' => 'Nurul Aisyah Binti Osman', 'email' => 'nurul.aisyah@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456008', 'ic_number' => '930618095522', 'address' => '34 Taman Impian, Klang', 'hired_on' => '2022-02-20', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 0],
            ['user_id' => 9, 'name' => 'Rajesh A/L Kumar', 'email' => 'rajesh.kumar@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456009', 'ic_number' => '870727135522', 'address' => '56 Jalan Teknologi, Putrajaya', 'hired_on' => '2020-09-10', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 10, 'name' => 'Chong Mei Yee', 'email' => 'chong.meiyee@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456010', 'ic_number' => '940901115522', 'address' => '78 Bukit Indah, Johor Bahru', 'hired_on' => '2023-05-05', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 6],
            ['user_id' => 11, 'name' => 'Mohd Zulkifli Bin Ali', 'email' => 'zulkifli.ali@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456011', 'ic_number' => '810303125522', 'address' => '90 Jalan Sultan, Kota Bharu', 'hired_on' => '2020-07-01', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 12, 'name' => 'Farah Binti Iskandar', 'email' => 'farah.iskandar@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456012', 'ic_number' => '950201095522', 'address' => '101 Taman Maju, Kuantan', 'hired_on' => '2022-12-01', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 13, 'name' => 'Suresh A/L Raman', 'email' => 'suresh.raman@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456013', 'ic_number' => '890909125522', 'address' => '202 Jalan Bunga, Ipoh', 'hired_on' => '2020-04-20', 'user_role' => 'employee', 'annual_leave_balance' => 11, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 14, 'name' => 'Aisyah Binti Mohd Noor', 'email' => 'aisyah.noor@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456014', 'ic_number' => '930330055522', 'address' => '303 Jalan Pantai, Kuala Terengganu', 'hired_on' => '2021-08-10', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 4],
            ['user_id' => 15, 'name' => 'Hafiz Bin Mohamed', 'email' => 'hafiz.mohamed@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456015', 'ic_number' => '880808125522', 'address' => '404 Jalan Seri, Malacca', 'hired_on' => '2023-02-14', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 16, 'name' => 'Lim Siew Lin', 'email' => 'lim.siewlin@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456016', 'ic_number' => '910515095522', 'address' => '505 Jalan Meru, Klang', 'hired_on' => '2022-04-01', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 13, 'emergency_leave_balance' => 7],
            ['user_id' => 17, 'name' => 'Guna A/L Muthu', 'email' => 'guna.muthu@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456017', 'ic_number' => '860606125522', 'address' => '606 Lorong Hijau, Penang', 'hired_on' => '2022-11-11', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 18, 'name' => 'Maya Binti Abdullah', 'email' => 'maya.abdullah@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456018', 'ic_number' => '970707105522', 'address' => '707 Jalan Sutera, Seremban', 'hired_on' => '2023-06-01', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 6],
            ['user_id' => 19, 'name' => 'Joel Tan', 'email' => 'joel.tan@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456019', 'ic_number' => '990101095522', 'address' => '808 Jalan Emas, Sungai Petani', 'hired_on' => '2024-01-02', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
            ['user_id' => 20, 'name' => 'Lina Binti Harun', 'email' => 'lina.harun@myhrsolutions.my', 'password' => bcrypt('password'), 'phone' => '60123456020', 'ic_number' => '920212125522', 'address' => '909 Taman Melur, Kota Kinabalu', 'hired_on' => '2024-05-20', 'user_role' => 'employee', 'annual_leave_balance' => 14, 'sick_leave_balance' => 14, 'emergency_leave_balance' => 7],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✓ Created ' . count($users) . ' users');
    }

    private function seedShiftSchedules(): void
    {
        $this->command->info('Seeding shift schedules...');

        $schedules = [
            // September 2025
            ['shift_id' => 1, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-09-01'],
            ['shift_id' => 2, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-09-01'],
            ['shift_id' => 3, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-09-02'],
            ['shift_id' => 4, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-09-02'],
            ['shift_id' => 5, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-09-03'],
            ['shift_id' => 6, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-09-03'],
            ['shift_id' => 7, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-09-04'],
            ['shift_id' => 8, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-09-04'],
            ['shift_id' => 9, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-09-05'],
            ['shift_id' => 10, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-09-05'],
            // October 2025
            ['shift_id' => 11, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-01'],
            ['shift_id' => 12, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-01'],
            ['shift_id' => 13, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-02'],
            ['shift_id' => 14, 'user_id' => 7, 'shift_type' => 'evening', 'shift_date' => '2025-10-02'],
            ['shift_id' => 15, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-03'],
            ['shift_id' => 16, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-03'],
            ['shift_id' => 17, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-04'],
            ['shift_id' => 18, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-04'],
            ['shift_id' => 19, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-05'],
            ['shift_id' => 20, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-05'],
            ['shift_id' => 21, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-06'],
            ['shift_id' => 22, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-06'],
            ['shift_id' => 23, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-07'],
            ['shift_id' => 24, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-07'],
            ['shift_id' => 25, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-08'],
            ['shift_id' => 26, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-08'],
            ['shift_id' => 27, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-09'],
            ['shift_id' => 28, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-09'],
            ['shift_id' => 29, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-10'],
            ['shift_id' => 30, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-10'],
            ['shift_id' => 31, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-11'],
            ['shift_id' => 32, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-11'],
            ['shift_id' => 33, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-12'],
            ['shift_id' => 34, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-12'],
            ['shift_id' => 35, 'user_id' => 18, 'shift_type' => 'evening', 'shift_date' => '2025-10-13'],
            ['shift_id' => 36, 'user_id' => 14, 'shift_type' => 'evening', 'shift_date' => '2025-10-14'],
            ['shift_id' => 37, 'user_id' => 18, 'shift_type' => 'evening', 'shift_date' => '2025-10-15'],
            ['shift_id' => 38, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-16'],
            ['shift_id' => 39, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-16'],
            ['shift_id' => 40, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-17'],
            ['shift_id' => 41, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-17'],
            ['shift_id' => 42, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-18'],
            ['shift_id' => 43, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-18'],
            ['shift_id' => 44, 'user_id' => 5, 'shift_type' => 'morning', 'shift_date' => '2025-10-19'],
            ['shift_id' => 45, 'user_id' => 6, 'shift_type' => 'evening', 'shift_date' => '2025-10-19'],
            ['shift_id' => 46, 'user_id' => 3, 'shift_type' => 'morning', 'shift_date' => '2025-10-20'],
            ['shift_id' => 47, 'user_id' => 4, 'shift_type' => 'evening', 'shift_date' => '2025-10-20'],
        ];

        foreach ($schedules as $scheduleData) {
            Schedule::create($scheduleData);
        }

        $this->command->info('✓ Created ' . count($schedules) . ' shift schedules');
    }

    private function seedAttendances(): void
    {
        $this->command->info('Seeding attendances...');

        $attendances = [
            // September 2025
            ['attendance_id' => 1, 'user_id' => 3, 'shift_id' => 1, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 2, 'user_id' => 4, 'shift_id' => 2, 'clock_in_time' => '15:10:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 3, 'user_id' => 5, 'shift_id' => 3, 'clock_in_time' => '06:12:00', 'clock_out_time' => '15:05:00', 'status' => 'on_time'],
            ['attendance_id' => 4, 'user_id' => 6, 'shift_id' => 4, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 5, 'user_id' => 3, 'shift_id' => 5, 'clock_in_time' => '06:07:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 6, 'user_id' => 4, 'shift_id' => 6, 'clock_in_time' => '15:20:00', 'clock_out_time' => '00:00:00', 'status' => 'late'],
            ['attendance_id' => 7, 'user_id' => 5, 'shift_id' => 7, 'clock_in_time' => null, 'clock_out_time' => null, 'status' => 'missed'],
            ['attendance_id' => 8, 'user_id' => 6, 'shift_id' => 8, 'clock_in_time' => '15:14:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 9, 'user_id' => 3, 'shift_id' => 9, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 10, 'user_id' => 4, 'shift_id' => 10, 'clock_in_time' => '15:18:00', 'clock_out_time' => '00:00:00', 'status' => 'late'],
            // October 2025
            ['attendance_id' => 11, 'user_id' => 3, 'shift_id' => 11, 'clock_in_time' => '06:05:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 12, 'user_id' => 4, 'shift_id' => 12, 'clock_in_time' => '15:10:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 13, 'user_id' => 5, 'shift_id' => 13, 'clock_in_time' => '06:03:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 14, 'user_id' => 7, 'shift_id' => 14, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 15, 'user_id' => 3, 'shift_id' => 15, 'clock_in_time' => '06:17:00', 'clock_out_time' => '15:00:00', 'status' => 'late'],
            ['attendance_id' => 16, 'user_id' => 4, 'shift_id' => 16, 'clock_in_time' => '15:05:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 17, 'user_id' => 5, 'shift_id' => 17, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 18, 'user_id' => 6, 'shift_id' => 18, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 19, 'user_id' => 3, 'shift_id' => 19, 'clock_in_time' => '06:10:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 20, 'user_id' => 4, 'shift_id' => 20, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 21, 'user_id' => 5, 'shift_id' => 21, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 22, 'user_id' => 6, 'shift_id' => 22, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 23, 'user_id' => 3, 'shift_id' => 23, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 24, 'user_id' => 4, 'shift_id' => 24, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 25, 'user_id' => 5, 'shift_id' => 25, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 26, 'user_id' => 6, 'shift_id' => 26, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 27, 'user_id' => 3, 'shift_id' => 27, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 28, 'user_id' => 4, 'shift_id' => 28, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 29, 'user_id' => 5, 'shift_id' => 29, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 30, 'user_id' => 6, 'shift_id' => 30, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 31, 'user_id' => 3, 'shift_id' => 31, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 32, 'user_id' => 4, 'shift_id' => 32, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 33, 'user_id' => 5, 'shift_id' => 33, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 34, 'user_id' => 6, 'shift_id' => 34, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 35, 'user_id' => 18, 'shift_id' => 35, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 36, 'user_id' => 14, 'shift_id' => 36, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 37, 'user_id' => 18, 'shift_id' => 37, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 38, 'user_id' => 3, 'shift_id' => 38, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 39, 'user_id' => 4, 'shift_id' => 39, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 40, 'user_id' => 5, 'shift_id' => 40, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 41, 'user_id' => 6, 'shift_id' => 41, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 42, 'user_id' => 3, 'shift_id' => 42, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 43, 'user_id' => 4, 'shift_id' => 43, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 44, 'user_id' => 5, 'shift_id' => 44, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 45, 'user_id' => 6, 'shift_id' => 45, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
            ['attendance_id' => 46, 'user_id' => 3, 'shift_id' => 46, 'clock_in_time' => '06:00:00', 'clock_out_time' => '15:00:00', 'status' => 'on_time'],
            ['attendance_id' => 47, 'user_id' => 4, 'shift_id' => 47, 'clock_in_time' => '15:00:00', 'clock_out_time' => '00:00:00', 'status' => 'on_time'],
        ];

        foreach ($attendances as $attendanceData) {
            Attendance::create($attendanceData);
        }

        $this->command->info('✓ Created ' . count($attendances) . ' attendances');
    }

    private function seedLeaveRequests(): void
    {
        $this->command->info('Seeding leave requests...');

        $leaveRequests = [
            ['request_id' => 1, 'user_id' => 3, 'leave_type' => 'annual', 'start_date' => '2025-09-10', 'end_date' => '2025-09-12', 'reason' => 'Family vacation', 'status' => 1],
            ['request_id' => 2, 'user_id' => 4, 'leave_type' => 'sick', 'start_date' => '2025-09-15', 'end_date' => '2025-09-15', 'reason' => 'Medical appointment', 'status' => 1],
            ['request_id' => 3, 'user_id' => 5, 'leave_type' => 'annual', 'start_date' => '2025-09-20', 'end_date' => '2025-09-22', 'reason' => 'Personal matters', 'status' => 0],
            ['request_id' => 4, 'user_id' => 6, 'leave_type' => 'emergency', 'start_date' => '2025-09-25', 'end_date' => '2025-09-25', 'reason' => 'Family emergency', 'status' => 1],
            ['request_id' => 5, 'user_id' => 7, 'leave_type' => 'annual', 'start_date' => '2025-10-01', 'end_date' => '2025-10-03', 'reason' => 'Wedding ceremony', 'status' => 1],
            ['request_id' => 6, 'user_id' => 8, 'leave_type' => 'sick', 'start_date' => '2025-10-05', 'end_date' => '2025-10-05', 'reason' => 'Flu symptoms', 'status' => 2],
            ['request_id' => 7, 'user_id' => 9, 'leave_type' => 'annual', 'start_date' => '2025-10-10', 'end_date' => '2025-10-12', 'reason' => 'Holiday trip', 'status' => 0],
            ['request_id' => 8, 'user_id' => 10, 'leave_type' => 'emergency', 'start_date' => '2025-10-15', 'end_date' => '2025-10-15', 'reason' => 'Home emergency', 'status' => 1],
            ['request_id' => 9, 'user_id' => 11, 'leave_type' => 'annual', 'start_date' => '2025-10-20', 'end_date' => '2025-10-22', 'reason' => 'Rest and relaxation', 'status' => 1],
            ['request_id' => 10, 'user_id' => 12, 'leave_type' => 'sick', 'start_date' => '2025-10-25', 'end_date' => '2025-10-25', 'reason' => 'Doctor visit', 'status' => 0],
            ['request_id' => 11, 'user_id' => 13, 'leave_type' => 'annual', 'start_date' => '2025-11-01', 'end_date' => '2025-11-03', 'reason' => 'Family gathering', 'status' => 1],
            ['request_id' => 12, 'user_id' => 14, 'leave_type' => 'emergency', 'start_date' => '2025-11-05', 'end_date' => '2025-11-06', 'reason' => 'Urgent family matter', 'status' => 1],
            ['request_id' => 13, 'user_id' => 15, 'leave_type' => 'annual', 'start_date' => '2025-11-10', 'end_date' => '2025-11-12', 'reason' => 'Personal trip', 'status' => 0],
            ['request_id' => 14, 'user_id' => 16, 'leave_type' => 'sick', 'start_date' => '2025-11-15', 'end_date' => '2025-11-15', 'reason' => 'Medical checkup', 'status' => 1],
            ['request_id' => 15, 'user_id' => 17, 'leave_type' => 'annual', 'start_date' => '2025-11-20', 'end_date' => '2025-11-22', 'reason' => 'Vacation', 'status' => 2],
            ['request_id' => 16, 'user_id' => 18, 'leave_type' => 'emergency', 'start_date' => '2025-11-25', 'end_date' => '2025-11-25', 'reason' => 'Family issue', 'status' => 0],
            ['request_id' => 17, 'user_id' => 19, 'leave_type' => 'annual', 'start_date' => '2025-12-01', 'end_date' => '2025-12-03', 'reason' => 'Year-end break', 'status' => 1],
            ['request_id' => 18, 'user_id' => 20, 'leave_type' => 'sick', 'start_date' => '2025-12-05', 'end_date' => '2025-12-05', 'reason' => 'Health issue', 'status' => 0],
            ['request_id' => 19, 'user_id' => 3, 'leave_type' => 'annual', 'start_date' => '2025-12-10', 'end_date' => '2025-12-12', 'reason' => 'Christmas preparation', 'status' => 1],
            ['request_id' => 20, 'user_id' => 4, 'leave_type' => 'emergency', 'start_date' => '2025-12-15', 'end_date' => '2025-12-15', 'reason' => 'Urgent matter', 'status' => 1],
        ];

        foreach ($leaveRequests as $leaveRequestData) {
            LeaveRequest::create($leaveRequestData);
        }

        $this->command->info('✓ Created ' . count($leaveRequests) . ' leave requests');
    }
}
