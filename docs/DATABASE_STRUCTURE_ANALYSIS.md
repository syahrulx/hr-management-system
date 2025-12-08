# HR Management System - Complete Database Structure Analysis

## Overview
This HR Management System is built on Laravel and uses MySQL/MariaDB as its database. The system manages employees, attendance, payroll, shifts, leave requests, schedules, and performance metrics.

---

## 📊 Core Tables

### 1. **employees** (Main User Table)
**Purpose**: Stores all employee/user information for authentication and HR records.

**Columns**:
- `id` - Primary key (bigint, auto-increment)
- `name` - Employee full name (string)
- `normalized_name` - Normalized Arabic name (string, nullable)
- `phone` - Phone number (string, unique)
- `email` - Email address (string, unique)
- `national_id` - National ID number (string, unique)
- `email_verified_at` - Email verification timestamp (timestamp, nullable)
- `password` - Hashed password (string)
- `address` - Home address (string, nullable)
- `bank_acc_no` - Bank account number (string, nullable)
- `hired_on` - Hiring date (date)
- ~~`is_remote`~~ - **DROPPED** in migration 2024_07_05_000001
- `remember_token` - Laravel remember token (string, nullable)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Has many: `salaries`, `payrolls`, `evaluations`, `attendances`, `employeeShifts`, `leaves`, `clients`, `schedules`, `requests`
- Has one: `manager`
- Has many through: `shifts` (through `employee_shifts`)

**Notes**: 
- Uses Laravel Sanctum for authentication
- Uses Spatie Permission package for roles and permissions
- Activity logging enabled (Spatie Activity Log)

---

### 2. **employee_salaries**
**Purpose**: Tracks salary history for employees (supports salary changes over time).

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (nullable, cascade on delete)
- `currency` - Currency type (enum: 'EGP', 'USD', 'EUR', 'GBP', 'CAD', 'KWD', 'SAR', 'AED', 'MYR', 'IDR', default: 'MYR')
- `salary` - Salary amount (integer)
- `start_date` - When this salary becomes effective (date)
- `end_date` - When this salary ends (date, nullable) - NULL means current salary
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`

**Business Logic**:
- Active salary has `end_date = NULL`
- Multiple records per employee for salary history

---

### 3. **managers**
**Purpose**: Designates certain employees as managers.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`
- Belongs to: `department` (referenced but model/table not found)
- Belongs to: `branch` (referenced but model/table not found)

**⚠️ ISSUE**: References to `department` and `branch` exist in model but no corresponding tables/models found.

---

## 💰 Payroll & Compensation

### 4. **payrolls**
**Purpose**: Monthly payroll records for employees.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `currency` - Currency type (string)
- `base` - Base salary amount (decimal 10,2, default: 0)
- `performance_multiplier` - Performance bonus multiplier (decimal 10,2, default: 0)
- `total_additions` - Sum of all additions (decimal 10,2, default: 0)
- `total_deductions` - Sum of all deductions (decimal 10,2, default: 0)
- `total_payable` - Final amount to pay (decimal 10,2, default: 0)
- `due_date` - Payment due date (date)
- `is_reviewed` - Whether admin reviewed (boolean, default: false)
- `status` - Payment status (boolean, default: false) - False: Pending, True: Paid
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- Unique: `[employee_id, due_date]` - One payroll per employee per month

**Relationships**:
- Belongs to: `employee`
- Has many: `additions` (referenced but table migration not found)
- Has many: `deductions` (referenced but table migration not found)
- Has many: `employeeEvaluations` (referenced but table migration not found)

**⚠️ ISSUES**: 
- Models exist for `Addition`, `Deduction`, and `EmployeeEvaluation` with factories
- PayrollController references `$payroll->additions` and `$payroll->deductions` (marked as PLACEHOLDER CODE)
- No migrations found for these tables - **INCOMPLETE IMPLEMENTATION**

---

### 5. **additions** (Model exists, NO MIGRATION FOUND)
**Purpose**: Track various types of pay additions to payroll.

**Expected Columns** (based on factory):
- `id` - Primary key
- `payroll_id` - Foreign key to payrolls
- `rewards` - Reward amount (decimal)
- `incentives` - Incentive amount (decimal)
- `reimbursements` - Reimbursement amount (decimal)
- `shift_differentials` - Shift differential pay (decimal)
- `overtime` - Overtime pay (decimal)
- `commissions` - Commission amount (decimal)
- `due_date` - Date (date)
- `status` - Status (boolean)

**⚠️ STATUS**: Model and factory exist, but NO MIGRATION FILE FOUND - Table may not exist in database.

---

### 6. **deductions** (Model exists, NO MIGRATION FOUND)
**Purpose**: Track various types of pay deductions from payroll.

**Expected Columns** (based on factory):
- `id` - Primary key
- `payroll_id` - Foreign key to payrolls
- `income_tax` - Income tax amount (decimal)
- `social_security_contributions` - Social security (decimal)
- `health_insurance` - Health insurance (decimal)
- `retirement_plan` - Retirement contributions (decimal)
- `benefits` - Benefits deductions (decimal)
- `undertime` - Undertime deductions (decimal)
- `union_fees` - Union fees (decimal)
- `due_date` - Date (date)
- `status` - Status (boolean)

**⚠️ STATUS**: Model and factory exist, but NO MIGRATION FILE FOUND - Table may not exist in database.

---

### 7. **employee_evaluations** (Model exists, NO MIGRATION FOUND)
**Purpose**: Store employee performance evaluations based on metrics.

**Expected Columns** (based on model relationships):
- `id` - Primary key
- `employee_id` - Foreign key to employees
- `metric_id` - Foreign key to metrics
- `payroll_id` - Foreign key to payrolls
- (Additional columns unknown)

**Relationships**:
- Belongs to: `employee`
- Belongs to: `metric`
- Belongs to: `payroll`

**⚠️ STATUS**: Model exists, but NO MIGRATION FILE FOUND - Table may not exist in database.

---

### 8. **metrics**
**Purpose**: Define performance evaluation criteria and weights.

**Columns**:
- `id` - Primary key
- `criteria` - Evaluation criteria name (string, unique)
- `weight` - Importance weight (float, default: 1)
- `step` - Step increment (float, default: 0.05)
- `created_at`, `updated_at` - Timestamps

**Notes**: 
- Used for employee performance evaluations
- Weight determines how much this metric affects performance multiplier

---

## ⏰ Attendance & Shifts

### 9. **attendances**
**Purpose**: Daily attendance records for employees.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `date` - Attendance date (date)
- `status` - Attendance status (enum: 'on_time', 'late', 'missed', default: 'missed')
- `sign_in_time` - Clock-in time (time, nullable)
- `sign_off_time` - Clock-out time (time, nullable)
- `notes` - Additional notes (string, nullable)
- `is_manually_filled` - Manual entry flag (boolean, default: false)
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- Unique: `[employee_id, date]` - One attendance record per employee per day

**Relationships**:
- Belongs to: `employee`

**Business Logic**:
- Default status is 'missed' (absence)
- Manual entries tracked separately for audit purposes

---

### 10. **shifts**
**Purpose**: Define work shift templates (morning, evening, night, etc.).

**Columns**:
- `id` - Primary key
- `name` - Shift name (string, unique) - e.g., "Morning Shift"
- `start_time` - Shift start time (time)
- `end_time` - Shift end time (time)
- `shift_payment_multiplier` - Pay multiplier for this shift (float, default: 1) - e.g., 1.5 for night shift
- `description` - Shift description (string, nullable)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Has many through: `employees` (through `employee_shifts`)

**Business Logic**:
- Night shifts may have multiplier > 1 for differential pay
- Shift duration calculated from start_time to end_time

---

### 11. **employee_shifts**
**Purpose**: Assigns shifts to employees with date ranges (shift assignment history).

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `shift_id` - Foreign key to shifts (set null on delete, nullable)
- `start_date` - When employee starts this shift (date)
- `end_date` - When employee ends this shift (date, nullable) - NULL means current shift
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`
- Belongs to: `shift`

**Business Logic**:
- Active shift has `end_date = NULL`
- Tracks shift change history

---

## 📅 Scheduling & Tasks

### 12. **schedules**
**Purpose**: Weekly work schedules for employees.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `shift_type` - Type of shift (string) - e.g., 'morning', 'evening'
- `week_start` - Start of the week (Monday) (date)
- `day` - Specific day in the schedule (date)
- `submitted` - Whether schedule is submitted (boolean, default: false)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`
- Has many: `tasks`

---

### 13. **tasks**
**Purpose**: Tasks assigned to specific schedule entries.

**Columns**:
- `id` - Primary key
- `schedule_id` - Foreign key to schedules (cascade on delete)
- `title` - Task title (string)
- `description` - Task description (text, nullable)
- `status` - Task status (string, default: 'pending')
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `schedule`

---

## 🏖️ Leave Management

### 14. **requests**
**Purpose**: Employee leave/time-off requests.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `type` - Leave type (enum: 'Annual Leave', 'Emergency Leave', 'Sick Leave')
- `start_date` - Leave start date (date)
- `end_date` - Leave end date (date, nullable)
- `message` - Employee's request message (text, nullable)
- `status` - Approval status (unsigned small int, default: 0) - 0: Pending, 1: Approved, 2: Rejected
- `admin_response` - Admin's response message (text, nullable)
- `is_seen` - Seen by employee (boolean, default: false)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`

---

### 15. **employee_leaves**
**Purpose**: Track leave balance for each employee by leave type.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `leave_type` - Type of leave (enum: 'Annual Leave', 'Emergency Leave', 'Sick Leave')
- `balance` - Remaining leave days (integer, nullable) - NULL for unlimited (sick leave)
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- Unique: `[employee_id, leave_type]` - One record per employee per leave type

**Relationships**:
- Belongs to: `employee`

**Business Logic**:
- NULL balance means unlimited (typically for sick leave)
- Balance decremented when leave is approved

---

## 👥 Client Management

### 16. **clients**
**Purpose**: Track clients associated with employees.

**Columns**:
- `id` - Primary key
- `employee_id` - Foreign key to employees (cascade on delete)
- `name` - Client name (string)
- `contact_info` - Client contact information (string, nullable)
- `created_at`, `updated_at` - Timestamps

**Relationships**:
- Belongs to: `employee`

---

## 📆 Calendar & Events

### 17. **calendars**
**Purpose**: Organization-wide calendar for holidays, meetings, and events.

**Columns**:
- `id` - Primary key
- `start_date` - Event start date (date)
- `end_date` - Event end date (date, nullable)
- `title` - Event title (string)
- `type` - Event type (enum: 'holiday', 'meeting', 'event', 'other')
- `created_at`, `updated_at` - Timestamps

**Business Logic**:
- Holidays affect attendance calculations
- Used in payroll calculations for working days

---

## ⚙️ System Configuration

### 18. **globals** (Singleton Table)
**Purpose**: Organization-wide settings and configuration (ONLY ONE ROW EXPECTED).

**Columns**:
- `id` - Primary key
- `organization_name` - Company name (string)
- `timezone` - Organization timezone (string, default: 'Africa/Cairo')
- `organization_address` - Company address (string)
- `absence_limit` - Maximum allowed absences (unsigned small int)
- `payroll_day` - Day of month for payroll (unsigned tiny int, default: 1)
- `weekend_off_days` - Weekend days (json, default: ['friday', 'saturday'])
- `email` - Organization email (string)
- `income_tax` - Default income tax percentage (float, default: 14)
- `is_ip_based` - IP-based attendance enabled (boolean, default: false)
- `ip` - Allowed IP addresses (json, nullable)
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- `chk_income_tax_range`: income_tax between 0 and 100

**Notes**:
- **SINGLETON TABLE** - Only one row should exist
- Contains all organization-wide settings

---

### 19. **archived_employees**
**Purpose**: Stores information about terminated/released employees.

**Columns**:
- `id` - Primary key
- `name` - Employee name (string)
- `phone` - Phone number (string)
- `email` - Email address (string)
- `national_id` - National ID (string, unique)
- `address` - Address (string, nullable)
- `bank_acc_no` - Bank account (string, nullable)
- `hired_on` - Hire date (date)
- `released_on` - Termination date (date)
- `was_remote` - Was remote worker (boolean, default: false)
- `created_at`, `updated_at` - Timestamps

**Business Logic**:
- When employee is terminated, record moved here
- Maintains historical employment records

---

## 🔐 Authentication & Authorization

### 20. **password_reset_tokens**
**Purpose**: Laravel password reset functionality.

**Columns**:
- `email` - Email address (string, primary key)
- `token` - Reset token (string)
- `created_at` - Token creation time (timestamp, nullable)

---

### 21. **personal_access_tokens**
**Purpose**: Laravel Sanctum API tokens.

**Columns**:
- `id` - Primary key
- `tokenable_type`, `tokenable_id` - Polymorphic relation (morphs)
- `name` - Token name (string)
- `token` - Token hash (string, 64 chars, unique)
- `abilities` - Token abilities/permissions (text, nullable)
- `last_used_at` - Last usage timestamp (timestamp, nullable)
- `expires_at` - Expiration timestamp (timestamp, nullable)
- `created_at`, `updated_at` - Timestamps

---

## 🔑 Permissions (Spatie Permission Package)

### 22. **permissions**
**Purpose**: Define system permissions.

**Columns**:
- `id` - Primary key
- `name` - Permission name (string)
- `guard_name` - Guard name (string)
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- Unique: `[name, guard_name]`

---

### 23. **roles**
**Purpose**: Define user roles.

**Columns**:
- `id` - Primary key
- `name` - Role name (string)
- `guard_name` - Guard name (string)
- `created_at`, `updated_at` - Timestamps

**Constraints**:
- Unique: `[name, guard_name]`

---

### 24. **model_has_permissions**
**Purpose**: Pivot table for polymorphic permission assignments.

**Columns**:
- `permission_id` - Foreign key to permissions (cascade on delete)
- `model_type` - Model class name (string)
- `model_id` - Model ID (morphs)

**Constraints**:
- Primary key: `[permission_id, model_id, model_type]`

---

### 25. **model_has_roles**
**Purpose**: Pivot table for polymorphic role assignments.

**Columns**:
- `role_id` - Foreign key to roles (cascade on delete)
- `model_type` - Model class name (string)
- `model_id` - Model ID (morphs)

**Constraints**:
- Primary key: `[role_id, model_id, model_type]`

---

### 26. **role_has_permissions**
**Purpose**: Pivot table linking roles to permissions.

**Columns**:
- `permission_id` - Foreign key to permissions (cascade on delete)
- `role_id` - Foreign key to roles (cascade on delete)

**Constraints**:
- Primary key: `[permission_id, role_id]`

---

## 📝 Activity Logging (Spatie Activity Log)

### 27. **activity_log**
**Purpose**: Track all system activities for audit trail.

**Columns**:
- `id` - Primary key
- `log_name` - Log category (string, nullable, indexed)
- `description` - Activity description (text)
- `subject_type`, `subject_id` - Subject of activity (morphs, nullable)
- `causer_type`, `causer_id` - Who caused the activity (morphs, nullable)
- `event` - Event type (string, nullable)
- `properties` - Additional data (json, nullable)
- `batch_uuid` - Batch identifier (uuid, nullable)
- `created_at`, `updated_at` - Timestamps

---

## 🔧 Queue & Jobs

### 28. **jobs**
**Purpose**: Laravel queue jobs table.

**Columns**:
- `id` - Primary key
- `queue` - Queue name (string, indexed)
- `payload` - Job payload (long text)
- `attempts` - Attempt count (unsigned tiny int)
- `reserved_at` - Reserved timestamp (unsigned int, nullable)
- `available_at` - Available timestamp (unsigned int)
- `created_at` - Creation timestamp (unsigned int)

---

### 29. **failed_jobs**
**Purpose**: Failed queue jobs for debugging.

**Columns**:
- `id` - Primary key
- `uuid` - Unique identifier (string, unique)
- `connection` - Connection name (text)
- `queue` - Queue name (text)
- `payload` - Job payload (long text)
- `exception` - Exception details (long text)
- `failed_at` - Failure timestamp (timestamp, default: current)

---

## 🔍 Database Structure Issues & Recommendations

### ⚠️ Critical Issues

1. **Missing Migrations**:
   - `additions` table - Model and factory exist, no migration
   - `deductions` table - Model and factory exist, no migration
   - `employee_evaluations` table - Model exists, no migration
   - `payrolls` table - Migration exists but NO Payroll model found in app/Models

2. **Orphaned References**:
   - `Manager` model references `Department` and `Branch` models/tables that don't exist
   - `Employee` model has `department()` relationship but no Department model/table

3. **Incomplete Implementation**:
   - PayrollController has placeholder code for additions/deductions (lines 104-106)
   - Comments indicate "MUCH MORE WORK NEEDED HERE"

### ✅ Recommendations

1. **Create Missing Migrations**:
   ```php
   // Create additions table migration
   // Create deductions table migration
   // Create employee_evaluations table migration
   ```

2. **Create Missing Models**:
   ```php
   // app/Models/Payroll.php
   // app/Models/Department.php (if needed)
   // app/Models/Branch.php (if needed)
   ```

3. **Clean Up Orphaned Code**:
   - Remove Department/Branch references OR implement them fully
   - Complete the payroll additions/deductions functionality

4. **Consider Adding**:
   - `departments` table if organizational structure is needed
   - `branches` table if multi-location support is needed
   - Indexes on frequently queried foreign keys
   - Soft deletes on critical tables for data retention

---

## 📊 Entity Relationship Summary

```
employees (CENTRAL TABLE)
├── employee_salaries (1:M) - Salary history
├── employee_shifts (1:M) - Shift assignments
│   └── shifts (M:1)
├── attendances (1:M) - Daily attendance
├── payrolls (1:M) - Monthly payroll
│   ├── additions (1:M) ⚠️ Missing table
│   ├── deductions (1:M) ⚠️ Missing table
│   └── employee_evaluations (1:M) ⚠️ Missing table
│       └── metrics (M:1)
├── requests (1:M) - Leave requests
├── employee_leaves (1:M) - Leave balances
├── schedules (1:M) - Weekly schedules
│   └── tasks (1:M) - Schedule tasks
├── clients (1:M) - Client assignments
└── manager (1:1) - Manager designation
    ├── department (M:1) ⚠️ Missing table/model
    └── branch (M:1) ⚠️ Missing table/model

globals (SINGLETON) - Organization settings
calendars (STANDALONE) - Company calendar
archived_employees (ARCHIVE) - Terminated employees

AUTHENTICATION & AUTHORIZATION:
├── password_reset_tokens
├── personal_access_tokens
├── permissions
├── roles
├── model_has_permissions
├── model_has_roles
└── role_has_permissions

SYSTEM TABLES:
├── activity_log (Audit trail)
├── jobs (Queue)
└── failed_jobs (Failed queue jobs)
```

---

## 🎯 System Purpose by Module

### HR Core
- Employee management and profiles
- Salary history tracking
- Archival of terminated employees

### Time & Attendance
- Shift management and scheduling
- Daily attendance tracking
- Work hour calculations

### Payroll
- Monthly payroll processing
- Performance-based compensation
- Additions and deductions (partial)
- Tax calculations

### Leave Management
- Leave request workflow
- Leave balance tracking
- Multiple leave types

### Scheduling
- Weekly schedule management
- Task assignment per shift
- Schedule submission workflow

### Client Management
- Employee-client relationships
- Contact information

### System Configuration
- Organization-wide settings
- Timezone and calendar settings
- Absence policies
- IP-based attendance

### Audit & Security
- Activity logging
- Role-based access control
- Permission management
- API token authentication

---

## 📈 Statistics

- **Total Tables**: 29
- **Core Business Tables**: 16
- **System/Framework Tables**: 13
- **Missing Tables**: 3 (additions, deductions, employee_evaluations)
- **Missing Models**: 4 (Payroll, Department, Branch, + others)
- **Enum Fields**: 6 tables use enum types
- **JSON Fields**: 3 (globals.weekend_off_days, globals.ip, activity_log.properties)
- **Polymorphic Relations**: 4 (activity_log, personal_access_tokens, permissions, roles)
- **Unique Constraints**: 9 composite unique constraints
- **Foreign Keys**: 26 foreign key relationships

---

## 🔧 Technology Stack

- **Framework**: Laravel (PHP)
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Permission Package
- **Activity Log**: Spatie Activity Log
- **Queue**: Laravel Queue (database driver)

---

**Document Generated**: October 9, 2025  
**Analysis Date**: Based on migration files and models as of latest commit  
**Status**: ⚠️ Database has incomplete implementations - See Issues section

---

*This analysis is based on migration files in `database/migrations/` and model files in `app/Models/`. Some tables may exist in the database but not have corresponding models, or vice versa.*

