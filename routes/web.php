<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['role:admin|owner', 'auth']], function () {

    Route::get('employees/find', [\App\Http\Controllers\EmployeeController::class, 'find'])->name('employees.find');
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    // shifts resource removed; using per-day shifts via shiftschedules
    Route::resource('requests', \App\Http\Controllers\RequestController::class);



    //Weekly Schedule
    Route::get('schedule', [\App\Http\Controllers\ScheduleController::class, 'admin'])->name('schedule.admin');
    Route::get('schedule/weekly', [\App\Http\Controllers\ScheduleController::class, 'weekly'])->name('schedule.weekly');

    // Schedule API routes
    Route::post('schedule/assign', [\App\Http\Controllers\ScheduleController::class, 'assign'])->name('schedule.assign');
    Route::get('schedule/week', [\App\Http\Controllers\ScheduleController::class, 'week'])->name('schedule.week');
    Route::post('schedule/reset', [\App\Http\Controllers\ScheduleController::class, 'reset'])->name('schedule.reset');

    Route::post('schedule/submit-week', [\App\Http\Controllers\ScheduleController::class, 'submitWeek'])->name('schedule.submit-week');



    // Reports route
    Route::get('reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');

    // New route
    Route::get('schedule/day', [\App\Http\Controllers\ScheduleController::class, 'day'])->name('schedule.day');

});


// Logged
Route::group(['middleware' => ['auth']], function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('dashboard/payroll-day', [\App\Http\Controllers\DashboardController::class, 'updatePayrollDay'])->middleware('role:owner')->name('dashboard.updatePayrollDay');

    Route::get('my-profile', [\App\Http\Controllers\EmployeeController::class, 'showMyProfile'])->name('my-profile');

    // Attendance Routes for Dashboard (Moved here for shared access)
    Route::post('attendance/sign-in', [\App\Http\Controllers\AttendanceController::class, 'dashboardSignInAttendance'])->name('attendance.dashboardSignIn');
    Route::post('attendance/sign-off', [\App\Http\Controllers\AttendanceController::class, 'dashboardSignOffAttendance'])->name('attendance.dashboardSignOff');
    Route::resource('requests', \App\Http\Controllers\RequestController::class)->only(['index', 'show', 'create', 'store']);
    Route::get('my-schedule', [\App\Http\Controllers\ScheduleController::class, 'employee'])
        ->middleware('role:employee')
        ->name('schedule.employee');
    Route::get('my-schedule/week', [\App\Http\Controllers\ScheduleController::class, 'myWeek'])->name('my-schedule.week');



});

// Redirect authenticated users to the dashboard
Route::redirect('/', '/dashboard')->middleware('auth');

// Language Switching
Route::get('language/{language}', function ($language) {
    Session()->put('locale', $language);
    return redirect()->back();
})->name('language');

require __DIR__ . '/auth.php';
