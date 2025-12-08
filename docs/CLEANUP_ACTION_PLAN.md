# HR Management System - Cleanup Action Plan

## 🎯 Goal
Remove all payroll, evaluation, manager, client, organization, and non-essential system tables while keeping the site fully functional.

---

## 📋 TABLES TO REMOVE (and all related code)

### Payroll / Evaluation Module
- ❌ `payrolls` (has migration)
- ❌ `employee_salaries` (has migration)
- ❌ `additions` (NO migration, model exists)
- ❌ `deductions` (NO migration, model exists)
- ❌ `employee_evaluations` (NO migration, model exists)
- ❌ `metrics` (has migration)

### Manager / Client / Organization Module
- ❌ `clients` (has migration)
- ❌ `managers` (has migration)
- ❌ `departments` (NO migration, referenced in code)
- ❌ `branches` (NO migration, referenced in code)
- ❌ `globals` (has migration - organization settings)
- ❌ `calendars` (has migration - company calendar)
- ❌ `archived_employees` (has migration)

### System / Utility Tables
- ❌ `activity_log` (has migration - Spatie Activity Log)
- ❌ `jobs` (has migration - Laravel Queue)
- ❌ `failed_jobs` (has migration - Laravel Queue)

---

## ✅ TABLES TO KEEP

### Core HR + Authentication
- ✅ `employees`
- ✅ `roles`
- ✅ `permissions`
- ✅ `model_has_roles`
- ✅ `model_has_permissions`
- ✅ `role_has_permissions`
- ✅ `password_reset_tokens`
- ✅ `personal_access_tokens`

### Attendance and Leave
- ✅ `attendances`
- ✅ `employee_leaves`
- ✅ `requests`

### Shift Management
- ✅ `shifts`
- ✅ `employee_shifts`

### Custom Modules (USER-BUILT)
- ✅ `schedules`
- ✅ `tasks`

---

## 🗑️ FILES TO DELETE

### Migrations (12 files)
1. `2023_05_30_172331_create_payrolls_table.php`
2. `2023_05_30_101634_create_employee_salaries_table.php`
3. `2023_05_30_172343_create_metrics_table.php`
4. `2024_07_10_000000_create_clients_table.php`
5. `2023_05_30_104703_create_managers_table.php`
6. `2025_06_25_195437_create_globals_table.php`
7. `2023_07_11_043132_create_calendars_table.php`
8. `2023_08_01_122221_create_archived_employees_table.php`
9. `2023_07_31_211210_create_activity_log_table.php`
10. `2023_07_31_211211_add_event_column_to_activity_log_table.php`
11. `2023_07_31_211212_add_batch_uuid_column_to_activity_log_table.php`
12. `2023_07_24_101801_create_jobs_table.php`
13. `2019_08_19_000000_create_failed_jobs_table.php`

### Models (10 files)
1. `app/Models/Addition.php`
2. `app/Models/Deduction.php`
3. `app/Models/EmployeeEvaluation.php`
4. `app/Models/Metric.php`
5. `app/Models/Client.php`
6. `app/Models/Manager.php`
7. `app/Models/Globals.php`
8. `app/Models/Calendar.php`
9. `app/Models/ArchivedEmployee.php`
10. `app/Models/EmployeeSalary.php`

### Controllers (5 files)
1. `app/Http/Controllers/PayrollController.php`
2. `app/Http/Controllers/GlobalsController.php`
3. `app/Http/Controllers/CalendarController.php`
4. `app/Http/Controllers/MetricsController.php`
5. `app/Http/Controllers/LogsController.php`

### Pages/Views (Vue components - ~16 files)
1. `resources/js/Pages/Calendar/` (ALL - 5 files)
   - Calendar.vue
   - CalendarItemCreate.vue
   - CalendarItemEdit.vue
   - CalendarItems.vue
   - CalendarItemView.vue
2. `resources/js/Pages/Globals/` (ALL - 2 files)
   - Globals.vue
   - GlobalsEdit.vue
3. `resources/js/Pages/Metric/` (ALL - 4 files)
   - MetricCreate.vue
   - MetricEdit.vue
   - Metrics.vue
   - MetricView.vue
4. `resources/js/Pages/Employee/ArchievedEmployees.vue`
5. `resources/js/Pages/Log/Logs.vue`
6. `resources/js/Pages/Reports/Reports.vue`
7. `resources/js/Pages/Branch/` (folder - check if exists)
8. `resources/js/Pages/Department/` (folder - check if exists)

### Factories (5 files)
1. `database/factories/PayrollFactory.php`
2. `database/factories/AdditionFactory.php`
3. `database/factories/DeductionFactory.php`
4. `database/factories/MetricFactory.php`
5. (Check for others related to deleted modules)

### Services (2 files)
1. `app/Services/CalendarServices.php`
2. Check `ValidationServices.php` and `CommonServices.php` for methods to remove

### Mail Templates (2 files)
1. `app/Mail/PayrollEmail.php`
2. Check `resources/views/emails/` for payroll email templates

### Tasks/Jobs (1 file)
1. `app/Tasks/MonthlyPayrollsHandle.php`

---

## 🔧 FILES TO MODIFY

### Controllers

#### 1. `app/Http/Controllers/DashboardController.php`
**Lines to replace:**
- Line 33: `'salary' => auth()->user()->salary()` 
  → Replace with: `'salary' => "VALUE NEED FROM UNEXIST DB"`
- Line 34: `'payroll_day' => Globals::first()->payroll_day`
  → Replace with: `'payroll_day' => "VALUE NEED FROM UNEXIST DB"`
- Line 35: `"employee_stats" => auth()->user()->myStats()`
  → Need to update Employee model's myStats() method to remove Globals dependency
- Line 38: `"total_clients" => auth()->user()->clients()->count()`
  → Replace with: `"total_clients" => 0`
- Remove import: `use App\Models\Globals;`

#### 2. `app/Http/Controllers/ReportsController.php`
**Action: DELETE ENTIRE FILE** (Reports depend on payroll and client data)

#### 3. `app/Http/Controllers/EmployeeController.php`
- Line 5: Remove `use App\Models\ArchivedEmployee;`
- Line 6: Remove `use App\Models\Department;`
- Lines 58-85: Delete `archivedIndex()` method
- Check `store()` and `update()` methods for salary/manager/department references
- Check `show()` method for deleted data

#### 4. `app/Http/Controllers/AttendanceController.php`
- Check for Globals, Calendar dependencies

### Models

#### 1. `app/Models/Employee.php` (MAJOR CHANGES)
**Remove these methods:**
- `salaries()` relationship (line 62-65)
- `salary()` method (lines 68-75)
- `payrolls()` relationship (lines 79-82)
- `evaluations()` relationship (lines 87-90)
- `department()` relationship (lines 94-97)
- `manages()` relationship (lines 102-104)
- `clients()` relationship (lines 286-289)
- `myStats()` method (lines 171-247) - needs heavy modification to remove Globals dependency
- `getYearStats()` method (lines 158-170) - needs modification
- `monthHours()` method (lines 249-279) - needs modification

**Keep these methods:**
- `attendances()`
- `shifts()`
- `employeeShifts()`
- `activeShift()`
- `activeShiftPeriod()`
- `getAttended()` and related attendance methods
- `leaves()` relationship

#### 2. `app/Models/Shift.php`
- Remove any references to payroll multiplier in calculations

#### 3. `app/Models/Attendance.php`
- Check for any manager/globals references

#### 4. `app/Models/Request.php`
- Should be fine, check for any admin notifications referencing managers

### Routes

#### `routes/web.php`
**Remove these routes:**
- Lines 22: `employees/archived` route
- Lines 25: `metrics` resource
- Lines 28-30: All payroll routes
- Lines 36-39: All globals routes
- Lines 42: logs route
- Lines 44-47: All calendar routes
- Lines 69: reports route
- Line 84: payrolls index/show for non-admin
- Line 85: calendar route for logged users

### Services

#### 1. `app/Services/CommonServices.php`
**Check and modify:**
- Methods that use `Globals` model
- Methods that use `Calendar` model
- Replace with hardcoded defaults or remove

#### 2. `app/Services/ValidationServices.php`
**Remove methods:**
- Any validation for payroll, metrics, globals, calendar

#### 3. `app/Services/EmployeeServices.php`
**Check and modify:**
- Methods dealing with salary, manager, department

### Views/Pages

#### 1. `resources/js/Pages/Dashboard.vue`
**Replace values from deleted data:**
- Any display of salary → "VALUE NEED FROM UNEXIST DB"
- Any display of payroll_day → "VALUE NEED FROM UNEXIST DB"
- Any display of client count → 0
- Any display of organization name/settings → "VALUE NEED FROM UNEXIST DB"

#### 2. `resources/js/Pages/Employee/EmployeeView.vue`
- Check for salary, manager, department, evaluation displays

#### 3. `resources/js/Pages/Employee/EmployeeEdit.vue` / `EmployeeCreate.vue`
- Remove salary fields
- Remove manager/department fields

#### 4. Navigation components
- Remove menu items for: Payroll, Metrics, Globals, Calendar, Logs, Reports

### Configuration Files

#### 1. `config/activitylog.php`
- Can delete this file or leave as is (unused)

#### 2. `composer.json`
- Check if `spatie/laravel-activitylog` can be removed
- Check if any other packages are only used for deleted modules

### Email Templates

#### 1. `resources/views/emails/payroll.blade.php`
- DELETE this file

### Seeders

#### 1. `database/seeders/DatabaseSeeder.php`
- Remove any seeding for deleted tables

#### 2. `database/seeders/StarterSeeder.php`
- Check and remove references to Globals, Metrics, etc.

---

## 🔄 REPLACEMENT STRATEGY

### When code references deleted data:
```php
// OLD CODE:
$employee->salary()
$employee->manager->name
$employee->clients()->count()
Globals::first()->payroll_day

// NEW CODE:
"VALUE NEED FROM UNEXIST DB"
"VALUE NEED FROM UNEXIST DB"
0
"VALUE NEED FROM UNEXIST DB"
```

### In Vue components:
```vue
<!-- OLD -->
<span>{{ employee.salary }}</span>

<!-- NEW -->
<span>VALUE NEED FROM UNEXIST DB</span>
```

---

## ✅ VERIFICATION CHECKLIST

After cleanup:
- [ ] All migrations run successfully: `php artisan migrate:fresh`
- [ ] No import errors in controllers/models
- [ ] Application boots: `php artisan serve`
- [ ] Dashboard loads without errors
- [ ] Employee list loads
- [ ] Attendance system works
- [ ] Leave request system works
- [ ] Schedule/Task system works
- [ ] No 500 errors on any kept routes
- [ ] Authentication works

---

## 📊 FINAL DATABASE STRUCTURE

After cleanup, you will have **11 tables**:

### Authentication & Authorization (6)
1. employees
2. password_reset_tokens
3. personal_access_tokens
4. permissions
5. roles
6. model_has_permissions
7. model_has_roles
8. role_has_permissions

### Core HR Functionality (5)
9. attendances
10. employee_leaves
11. requests
12. shifts
13. employee_shifts
14. schedules
15. tasks

---

**Total tables removed**: 14  
**Total tables kept**: 15  
**Estimated files to delete**: ~50  
**Estimated files to modify**: ~15


