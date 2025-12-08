# 10 Comprehensive SQL Query Examples
## HR Management System - Complete Coverage

These queries demonstrate joins, aggregations, subqueries, filtering, and cover all tables and relationships.

---

## 1. User Profile with Leave Balances and Role
**Coverage**: `users` table, all core attributes, role-based filtering

```sql
SELECT 
    id,
    name,
    email,
    phone,
    national_id,
    userRole,
    hired_on,
    annual_leave_balance,
    sick_leave_balance,
    emergency_leave_balance,
    DATEDIFF(day, hired_on, CURRENT_DATE) AS days_employed,
    created_at
FROM users
WHERE deleted_at IS NULL
    AND userRole = 'staff'
ORDER BY hired_on DESC;
```

**Purpose**: Get all active staff members with their leave balances and tenure.

---

## 2. Monthly Attendance Report with Shift Information
**Coverage**: `users` + `attendances` + `shiftschedules` (3-way join), date filtering, aggregation

```sql
SELECT 
    u.id AS user_id,
    u.name AS employee_name,
    a.date,
    a.status,
    a.sign_in_time,
    a.sign_off_time,
    s.shift_type,
    s.week_start,
    CASE 
        WHEN a.is_manually_filled = true THEN 'Manual'
        ELSE 'Self Clock-in'
    END AS entry_method,
    a.notes
FROM attendances a
INNER JOIN users u ON a.user_id = u.id
LEFT JOIN shiftschedules s ON a.schedule_id = s.id
WHERE a.deleted_at IS NULL
    AND u.deleted_at IS NULL
    AND a.date >= '2024-10-01'
    AND a.date < '2024-11-01'
ORDER BY a.date DESC, u.name;
```

**Purpose**: Monthly attendance report showing who clocked in, their shift, and entry method.

---

## 3. Leave Requests with Approval Status and Duration
**Coverage**: `users` + `leave_requests` (join), date calculations, status decoding

```sql
SELECT 
    lr.id AS request_id,
    u.name AS employee_name,
    u.email,
    lr.type AS leave_type,
    lr.start_date,
    COALESCE(lr.end_date, lr.start_date) AS end_date,
    COALESCE(
        DATEDIFF(day, lr.start_date, lr.end_date) + 1,
        1
    ) AS total_days,
    CASE lr.status
        WHEN 0 THEN 'Pending'
        WHEN 1 THEN 'Approved'
        WHEN 2 THEN 'Rejected'
    END AS status_text,
    lr.message AS employee_message,
    lr.admin_response,
    lr.created_at AS requested_at,
    lr.updated_at AS reviewed_at
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.id
WHERE lr.deleted_at IS NULL
    AND u.deleted_at IS NULL
ORDER BY lr.created_at DESC;
```

**Purpose**: Complete leave request history with calculated duration and human-readable status.

---

## 4. Weekly Schedule Overview with Staff Assignments
**Coverage**: `shiftschedules` + `users` (join), grouping by week and day

```sql
SELECT 
    s.week_start,
    s.day,
    s.shift_type,
    u.name AS assigned_staff,
    u.phone,
    s.submitted AS is_week_locked,
    s.created_at AS assigned_at
FROM shiftschedules s
INNER JOIN users u ON s.user_id = u.id
WHERE s.deleted_at IS NULL
    AND u.deleted_at IS NULL
    AND s.week_start = '2024-10-07'
ORDER BY s.day, s.shift_type;
```

**Purpose**: View who is assigned to which shift for a specific week.

---

## 5. Attendance Performance Summary by Employee
**Coverage**: `users` + `attendances` (join), aggregation, grouping, percentages

```sql
SELECT 
    u.id,
    u.name,
    u.userRole,
    COUNT(a.id) AS total_attendances,
    SUM(CASE WHEN a.status = 'on_time' THEN 1 ELSE 0 END) AS on_time_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
    SUM(CASE WHEN a.status = 'missed' THEN 1 ELSE 0 END) AS missed_count,
    ROUND(
        (SUM(CASE WHEN a.status = 'on_time' THEN 1 ELSE 0 END)::DECIMAL / 
         NULLIF(COUNT(a.id), 0)) * 100, 
        2
    ) AS on_time_percentage
FROM users u
LEFT JOIN attendances a ON u.id = a.user_id 
    AND a.deleted_at IS NULL
    AND a.date >= CURRENT_DATE - INTERVAL '30 days'
WHERE u.deleted_at IS NULL
    AND u.userRole = 'staff'
GROUP BY u.id, u.name, u.userRole
ORDER BY on_time_percentage DESC;
```

**Purpose**: Performance report showing attendance statistics for the last 30 days.

---

## 6. Pending Leave Requests Dashboard (Admin View)
**Coverage**: `leave_requests` + `users` (join), subquery for conflict detection

```sql
SELECT 
    lr.id,
    u.name AS employee_name,
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance,
    lr.type,
    lr.start_date,
    lr.end_date,
    COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1) AS days_requested,
    lr.message,
    lr.created_at,
    DATEDIFF(day, lr.created_at, CURRENT_TIMESTAMP) AS days_pending,
    -- Check if they have scheduled shifts during leave period
    (
        SELECT COUNT(*)
        FROM shiftschedules s
        WHERE s.user_id = lr.user_id
            AND s.deleted_at IS NULL
            AND s.day >= lr.start_date
            AND s.day <= COALESCE(lr.end_date, lr.start_date)
    ) AS conflicting_shifts
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.id
WHERE lr.status = 0
    AND lr.deleted_at IS NULL
    AND u.deleted_at IS NULL
ORDER BY lr.created_at ASC;
```

**Purpose**: Admin dashboard showing pending leave requests with balance info and shift conflicts.

---

## 7. Attendance for Night Shift (Shift-Specific Report)
**Coverage**: `attendances` + `shiftschedules` + `users` (3-way join), shift filtering

```sql
SELECT 
    a.date,
    u.name AS employee_name,
    s.shift_type,
    a.sign_in_time,
    a.sign_off_time,
    a.status,
    CASE 
        WHEN a.sign_in_time IS NOT NULL AND a.sign_off_time IS NOT NULL 
        THEN EXTRACT(EPOCH FROM (a.sign_off_time::time - a.sign_in_time::time)) / 3600
        ELSE NULL
    END AS hours_worked
FROM attendances a
INNER JOIN shiftschedules s ON a.schedule_id = s.id
INNER JOIN users u ON a.user_id = u.id
WHERE s.shift_type = 'evening'
    AND a.deleted_at IS NULL
    AND s.deleted_at IS NULL
    AND u.deleted_at IS NULL
    AND a.date >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY a.date DESC, a.sign_in_time;
```

**Purpose**: Report all evening shift attendances for the last 7 days with hours worked.

---

## 8. Users Without Any Attendance Records (Inactive Staff)
**Coverage**: `users` + `attendances` (LEFT JOIN with NULL check), subquery

```sql
SELECT 
    u.id,
    u.name,
    u.email,
    u.phone,
    u.userRole,
    u.hired_on,
    DATEDIFF(day, u.hired_on, CURRENT_DATE) AS days_since_hired,
    u.created_at AS account_created
FROM users u
LEFT JOIN attendances a ON u.user_id = a.user_id 
    AND a.deleted_at IS NULL
WHERE u.deleted_at IS NULL
    AND u.userRole = 'staff'
    AND a.id IS NULL
ORDER BY u.hired_on;
```

**Purpose**: Find staff who have never clocked in (potential onboarding issue).

---

## 9. Leave Balance Verification with Approved Leave Count
**Coverage**: `users` + `leave_requests` (join with aggregation), balance reconciliation

```sql
SELECT 
    u.id,
    u.name,
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance,
    -- Calculate total approved days per type
    SUM(
        CASE 
            WHEN lr.type = 'Annual Leave' AND lr.status = 1 
            THEN COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1)
            ELSE 0
        END
    ) AS annual_leave_used,
    SUM(
        CASE 
            WHEN lr.type = 'Sick Leave' AND lr.status = 1 
            THEN COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1)
            ELSE 0
        END
    ) AS sick_leave_used,
    SUM(
        CASE 
            WHEN lr.type = 'Emergency Leave' AND lr.status = 1 
            THEN COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1)
            ELSE 0
        END
    ) AS emergency_leave_used,
    -- Calculate expected balances (assuming initial: 15 annual, 10 sick, 5 emergency)
    (15 - SUM(
        CASE 
            WHEN lr.type = 'Annual Leave' AND lr.status = 1 
            THEN COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1)
            ELSE 0
        END
    )) AS expected_annual_balance
FROM users u
LEFT JOIN leave_requests lr ON u.id = lr.user_id 
    AND lr.deleted_at IS NULL
WHERE u.deleted_at IS NULL
    AND u.userRole = 'staff'
GROUP BY u.id, u.name, u.annual_leave_balance, u.sick_leave_balance, u.emergency_leave_balance
HAVING u.annual_leave_balance != (15 - SUM(
    CASE 
        WHEN lr.type = 'Annual Leave' AND lr.status = 1 
        THEN COALESCE(DATEDIFF(day, lr.start_date, lr.end_date) + 1, 1)
        ELSE 0
    END
))
ORDER BY u.name;
```

**Purpose**: Audit leave balances to find discrepancies between stored balance and calculated usage.

---

## 10. Complete User Activity Overview (All Relationships)
**Coverage**: `users` + `attendances` + `leave_requests` + `shiftschedules` (all tables joined)

```sql
SELECT 
    u.id,
    u.name,
    u.email,
    u.userRole,
    u.hired_on,
    -- Attendance stats
    COUNT(DISTINCT a.id) AS total_attendances,
    MAX(a.date) AS last_attendance_date,
    -- Leave stats
    COUNT(DISTINCT lr.id) AS total_leave_requests,
    SUM(CASE WHEN lr.status = 0 THEN 1 ELSE 0 END) AS pending_leaves,
    SUM(CASE WHEN lr.status = 1 THEN 1 ELSE 0 END) AS approved_leaves,
    -- Schedule stats
    COUNT(DISTINCT s.id) AS total_shift_assignments,
    COUNT(DISTINCT CASE WHEN s.day >= CURRENT_DATE THEN s.id END) AS upcoming_shifts,
    -- Leave balances
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance
FROM users u
LEFT JOIN attendances a ON u.id = a.user_id 
    AND a.deleted_at IS NULL
LEFT JOIN leave_requests lr ON u.id = lr.user_id 
    AND lr.deleted_at IS NULL
LEFT JOIN shiftschedules s ON u.id = s.user_id 
    AND s.deleted_at IS NULL
WHERE u.deleted_at IS NULL
    AND u.userRole = 'staff'
GROUP BY 
    u.id, u.name, u.email, u.userRole, u.hired_on,
    u.annual_leave_balance, u.sick_leave_balance, u.emergency_leave_balance
ORDER BY u.name;
```

**Purpose**: Complete overview of each employee's activity across all modules (attendance, leave, schedules).

---

## Notes for Supabase Execution

1. **DATEDIFF**: Replace with PostgreSQL syntax:
   ```sql
   -- Instead of: DATEDIFF(day, start_date, end_date)
   -- Use: (end_date - start_date)
   ```

2. **INTERVAL**: PostgreSQL native, works as-is:
   ```sql
   CURRENT_DATE - INTERVAL '30 days'
   ```

3. **CASE WHEN**: Standard SQL, works in both MySQL and PostgreSQL

4. **COALESCE**: Standard SQL, works in both

5. **Soft Deletes**: All queries include `deleted_at IS NULL` filters

---

## Query Coverage Summary

| Query # | Tables Used | Features Demonstrated |
|---------|-------------|----------------------|
| 1 | users | Basic SELECT, filtering, date calculations |
| 2 | users, attendances, shiftschedules | 3-way JOIN, date range filtering |
| 3 | users, leave_requests | JOIN, CASE WHEN, date calculations |
| 4 | users, shiftschedules | JOIN, week-based filtering |
| 5 | users, attendances | Aggregation, GROUP BY, percentages |
| 6 | users, leave_requests, shiftschedules | Subquery, conflict detection |
| 7 | users, attendances, shiftschedules | 3-way JOIN, time calculations |
| 8 | users, attendances | LEFT JOIN with NULL check |
| 9 | users, leave_requests | Complex aggregation, HAVING clause |
| 10 | users, attendances, leave_requests, shiftschedules | Multiple LEFT JOINs, comprehensive stats |

**Coverage**: ✅ All 4 core tables, ✅ All relationships, ✅ All common operations

