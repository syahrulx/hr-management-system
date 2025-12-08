# 🔐 ROLES AND PERMISSIONS DOCUMENTATION

## Overview
The HR Management System has **3 roles** with different access levels and sidebar menu permissions.

---

## 📋 ROLES SUMMARY

### 1. **ADMIN** 👨‍💼
**Description**: Full system administrator with access to all employee management features

**Sidebar Menu Access**:
- ✅ Dashboard
- ✅ Employees (full CRUD)
- ✅ Requests (manage all employee requests)
- ✅ Schedule (admin schedule management)
- ✅ Attendance (view and manage all attendance)

**Route Access**:
- All employee management routes (create, read, update, delete)
- All shift management routes
- All request management routes (view, edit, delete all requests)
- All attendance routes (view any date, delete, create)
- Schedule management:
  - Admin schedule view
  - Weekly schedule
  - Assign schedules to employees
  - Reset schedules
  - Assign tasks
  - Submit weekly schedules
- Task management:
  - View all tasks
  - Create tasks
  - Delete tasks
- Reports (shared with owner)
- Profile management
- Dashboard

---

### 2. **OWNER** 👑
**Description**: Business owner with oversight capabilities and reporting access

**Sidebar Menu Access**:
- ✅ Dashboard
- ✅ Reports (analytics and performance dashboard)
- ✅ Employees (view employee list)
- ✅ Requests (view all requests)

**Route Access**:
- All routes that admin can access (middleware: `role:admin|owner`)
- **Special Focus**: Reports dashboard with:
  - Employee statistics
  - Attendance rates
  - Task completion metrics
  - Performance rankings
- Same access as admin for:
  - Employee management
  - Shift management
  - Request management
  - Attendance management
  - Schedule management
  - Task management
- Profile management
- Dashboard

**Login Redirect**: Redirects to `/reports` instead of `/dashboard`

---

### 3. **EMPLOYEE** 👤
**Description**: Regular employee with self-service access

**Sidebar Menu Access**:
- ✅ My Dashboard (personal dashboard)
- ✅ My Profile (view own profile)
- ✅ My Requests (with notification badge)
- ✅ My Schedule (view own schedule)
- ✅ My Attendance (sign in/out, view own attendance)

**Route Access**:
- **My Profile**: View own employee profile
- **My Requests**: 
  - View own requests
  - Create new requests
  - View request details
  - (Cannot edit or delete requests)
- **My Schedule**:
  - View own weekly schedule
  - View assigned tasks
  - Get week schedule data
- **My Attendance**:
  - View own attendance dashboard
  - Sign in for the day
  - Sign off for the day
  - View own attendance statistics
- **My Tasks**:
  - View tasks for specific day
  - Update task status (mark complete/incomplete)
- Profile management
- Dashboard

**Restrictions**:
- Cannot access other employees' data
- Cannot manage system-wide resources
- Cannot view admin or owner features
- Cannot access reports

---

## 🔒 MIDDLEWARE PROTECTION

### Route Groups:

1. **Admin + Owner Routes** (`role:admin|owner`)
   - All employee CRUD operations
   - All shift management
   - All request management
   - All attendance management
   - All schedule management
   - All task management
   - Reports dashboard

2. **Authenticated Routes** (`auth`)
   - Profile management
   - Dashboard access
   - My profile (employee-specific)
   - My requests (employee view-only + create)
   - My schedule (with `role:employee` middleware)
   - My attendance
   - My tasks

3. **Employee-Only Routes** (`role:employee`)
   - My schedule view
   - View task details

---

## 📊 SIDEBAR MENU BREAKDOWN

### Admin Sidebar (5 items):
```
1. Dashboard → dashboard.index
2. Employees → employees.index (+ create, show, find, edit)
3. Requests → requests.index (+ create, show, edit)
4. Schedule → schedule.admin
5. Attendance → attendances.index (+ create, show, attendance.dashboard)
```

### Owner Sidebar (4 items):
```
1. Dashboard → dashboard.index
2. Reports → reports.index (⭐ Owner-specific feature)
3. Employees → employees.index (+ create, show, find, edit)
4. Requests → requests.index (+ create, show, edit)
```

### Employee Sidebar (5 items):
```
1. My Dashboard → dashboard.index
2. My Profile → my-profile
3. My Requests → requests.index [with badge counter] (+ show, create)
4. My Schedule → schedule.employee
5. My Attendance → attendance.dashboard
```

---

## 🎯 KEY FEATURES BY ROLE

### Admin Can:
- ✅ Manage all employees (hire, edit, view)
- ✅ Approve/reject all requests
- ✅ View and edit any attendance
- ✅ Create and manage schedules for all employees
- ✅ Assign and delete tasks
- ✅ View system-wide dashboard
- ✅ Access reports

### Owner Can:
- ✅ View comprehensive reports and analytics
- ✅ See employee performance rankings
- ✅ View attendance rates and task completion
- ✅ All admin capabilities (full system access)
- ✅ **Specialized reporting dashboard** with:
  - Summary cards (employees, attendance rate, top performer)
  - Staff attendance breakdown (present, late, absent)
  - Task completion charts
  - Performance ranking table

### Employee Can:
- ✅ View own profile and attendance
- ✅ Submit leave/request applications
- ✅ View own schedule and tasks
- ✅ Sign in and sign out for attendance
- ✅ Update own task status
- ✅ View own dashboard with personal stats
- ❌ Cannot access other employees' data
- ❌ Cannot manage system resources
- ❌ Cannot approve requests

---

## 🔄 LOGIN REDIRECTS

- **Admin**: `/dashboard` (via RouteServiceProvider)
- **Owner**: `/reports` (via AuthenticatedSessionController & RouteServiceProvider)
- **Employee**: `/dashboard` (default)

---

## 📁 RELATED FILES

### Seeders:
- `database/seeders/DatabaseSeeder.php` - Creates all 3 roles

### Controllers:
- `app/Http/Controllers/ReportsController.php` - Owner's reporting dashboard
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Login redirects
- `app/Providers/RouteServiceProvider.php` - Post-auth redirects

### Frontend:
- `resources/js/Layouts/AuthenticatedLayout.vue` - Sidebar menu (lines 56-131)

### Routes:
- `routes/web.php` - All route definitions with middleware

---

## 🆕 RECENTLY ADDED FEATURES

1. **Owner Role** - Added as a business oversight role
2. **Reports Dashboard** - Owner-exclusive analytics page with:
   - Real-time statistics from database
   - Performance metrics
   - Employee rankings
   - Optimized bulk queries (3 queries total, < 1 second load time)

---

## ⚠️ IMPORTANT NOTES

1. **Owner has same access as Admin** but focuses on reporting/analytics
2. **Employee role is most restricted** - self-service only
3. **No permission-based access control** - only role-based
4. **All routes use Spatie Laravel Permission** package for role checking
5. **Middleware combinations**:
   - `role:admin|owner` - Admin OR Owner
   - `role:employee` - Employee only
   - `auth` - Any authenticated user

---

## 📈 FUTURE CONSIDERATIONS

- Consider adding permission-based access control for granular control
- Add more roles if needed (e.g., HR Manager, Team Lead)
- Implement role hierarchy
- Add audit logging for admin/owner actions

