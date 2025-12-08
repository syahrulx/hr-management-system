-- ============================================================================
-- HR MANAGEMENT SYSTEM - 10 COMPREHENSIVE SQL QUERIES (PostgreSQL/Supabase)
-- ============================================================================
-- Instructions: Copy each query individually
-- Execute in Supabase SQL Editor
-- Note: Supabase auth.users has duplicate columns (id, email, phone, etc.)
--       Use ::bigint or ::text casts to resolve ambiguity
-- ============================================================================

-- ============================================================================
-- QUERY 1: User Profile with Leave Balances and Role
-- Coverage: users table, all attributes, role filtering
-- ============================================================================
SELECT 
    id::bigint,
    name,
    email::text,
    phone::text,
    national_id,
    "userRole",
    hired_on,
    annual_leave_balance,
    sick_leave_balance,
    emergency_leave_balance,
    address,
    bank_acc_no,
    hired_on,
    CASE 
        WHEN hired_on IS NOT NULL THEN CURRENT_DATE - hired_on 
        ELSE NULL 
    END AS days_employed
FROM users
WHERE deleted_at::timestamp IS NULL
    AND "userRole" IN ('staff', 'admin', 'owner')
ORDER BY hired_on DESC NULLS LAST
LIMIT 50;


-- ============================================================================
-- QUERY 2: Monthly Attendance Report with Shift Information  
-- Coverage: users + attendances + shiftschedules (3-way join)
-- ============================================================================
SELECT 
    u.id::bigint AS user_id,
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
INNER JOIN users u ON a.user_id = u.id::bigint
LEFT JOIN shiftschedules s ON a.schedule_id = s.id
WHERE a.deleted_at IS NULL
    AND u.deleted_at::timestamp IS NULL
    AND a.date >= '2024-10-01'
    AND a.date < '2024-11-01'
ORDER BY a.date DESC, u.name
LIMIT 100;


-- ============================================================================
-- QUERY 3: Leave Requests with Approval Status and Duration
-- Coverage: users + leave_requests, date calculations, status decoding
-- ============================================================================
SELECT 
    lr.id AS request_id,
    u.name AS employee_name,
    u.email::text,
    lr.type AS leave_type,
    lr.start_date,
    COALESCE(lr.end_date, lr.start_date) AS end_date,
    COALESCE((lr.end_date - lr.start_date) + 1, 1) AS total_days,
    CASE lr.status
        WHEN 0 THEN 'Pending'
        WHEN 1 THEN 'Approved'
        WHEN 2 THEN 'Rejected'
        ELSE 'Unknown'
    END AS status_text,
    lr.message AS employee_message,
    lr.admin_response,
    lr.created_at AS requested_at,
    lr.updated_at AS reviewed_at
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.id::bigint
WHERE lr.deleted_at IS NULL
    AND u.deleted_at::timestamp IS NULL
ORDER BY lr.created_at DESC
LIMIT 100;


-- ============================================================================
-- QUERY 4: Weekly Schedule Overview with Staff Assignments
-- Coverage: shiftschedules + users, grouping by week and day
-- ============================================================================
SELECT 
    s.week_start,
    s.day,
    s.shift_type,
    u.name AS assigned_staff,
    u.phone::text,
    u.email::text,
    s.submitted AS is_week_locked,
    s.created_at AS assigned_at
FROM shiftschedules s
INNER JOIN users u ON s.user_id = u.id::bigint
WHERE s.deleted_at IS NULL
    AND u.deleted_at::timestamp IS NULL
    AND s.week_start >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY s.week_start, s.day, s.shift_type
LIMIT 100;


-- ============================================================================
-- QUERY 5: Attendance Performance Summary by Employee (Last 30 Days)
-- Coverage: users + attendances, aggregation, grouping, percentages
-- ============================================================================
SELECT 
    u.id::bigint,
    u.name,
    u."userRole",
    COUNT(a.id) AS total_attendances,
    SUM(CASE WHEN a.status = 'on_time' THEN 1 ELSE 0 END) AS on_time_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
    SUM(CASE WHEN a.status = 'missed' THEN 1 ELSE 0 END) AS missed_count,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
    ROUND(
        (SUM(CASE WHEN a.status = 'on_time' THEN 1 ELSE 0 END)::DECIMAL / 
         NULLIF(COUNT(a.id), 0)) * 100, 
        2
    ) AS on_time_percentage
FROM users u
LEFT JOIN attendances a ON u.id::bigint = a.user_id 
    AND a.deleted_at IS NULL
    AND a.date >= CURRENT_DATE - INTERVAL '30 days'
WHERE u.deleted_at::timestamp IS NULL
    AND u."userRole" IN ('staff', 'admin')
GROUP BY u.id::bigint, u.name, u."userRole"
ORDER BY on_time_percentage DESC NULLS LAST
LIMIT 50;


-- ============================================================================
-- QUERY 6: Pending Leave Requests Dashboard (Admin View)
-- Coverage: leave_requests + users, subquery for conflict detection
-- ============================================================================
SELECT 
    lr.id,
    u.name AS employee_name,
    u.email::text,
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance,
    lr.type,
    lr.start_date,
    lr.end_date,
    COALESCE((lr.end_date - lr.start_date) + 1, 1) AS days_requested,
    lr.message,
    lr.created_at,
    CURRENT_DATE - lr.created_at::date AS days_pending,
    (
        SELECT COUNT(*)
        FROM shiftschedules s
        WHERE s.user_id = lr.user_id
            AND s.deleted_at IS NULL
            AND s.day >= lr.start_date
            AND s.day <= COALESCE(lr.end_date, lr.start_date)
    ) AS conflicting_shifts
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.id::bigint
WHERE lr.status = 0
    AND lr.deleted_at IS NULL
    AND u.deleted_at::timestamp IS NULL
ORDER BY lr.created_at ASC
LIMIT 50;


-- ============================================================================
-- QUERY 7: Attendance for Evening Shift (Last 7 Days)
-- Coverage: attendances + shiftschedules + users (3-way join)
-- ============================================================================
SELECT 
    a.date,
    u.name AS employee_name,
    s.shift_type,
    a.sign_in_time,
    a.sign_off_time,
    a.status,
    CASE 
        WHEN a.sign_in_time IS NOT NULL AND a.sign_off_time IS NOT NULL 
        THEN ROUND(EXTRACT(EPOCH FROM (a.sign_off_time - a.sign_in_time)) / 3600, 2)
        ELSE NULL
    END AS hours_worked
FROM attendances a
INNER JOIN shiftschedules s ON a.schedule_id = s.id
INNER JOIN users u ON a.user_id = u.id::bigint
WHERE s.shift_type = 'evening'
    AND a.deleted_at IS NULL
    AND s.deleted_at IS NULL
    AND u.deleted_at::timestamp IS NULL
    AND a.date >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY a.date DESC, a.sign_in_time;


-- ============================================================================
-- QUERY 8: Users Without Any Attendance Records (Inactive Staff)
-- Coverage: users + attendances (LEFT JOIN with NULL check)
-- ============================================================================
SELECT 
    u.id::bigint,
    u.name,
    u.email::text,
    u.phone::text,
    u."userRole",
    u.hired_on,
    CASE 
        WHEN u.hired_on IS NOT NULL THEN CURRENT_DATE - u.hired_on
        ELSE NULL
    END AS days_since_hired,
    u.created_at::timestamp AS account_created
FROM users u
LEFT JOIN attendances a ON u.id::bigint = a.user_id 
    AND a.deleted_at IS NULL
WHERE u.deleted_at::timestamp IS NULL
    AND u."userRole" = 'staff'
    AND a.id IS NULL
ORDER BY u.hired_on NULLS LAST
LIMIT 50;


-- ============================================================================
-- QUERY 9: Leave Balance Verification with Approved Leave Usage
-- Coverage: users + leave_requests, aggregation, balance reconciliation
-- ============================================================================
SELECT 
    u.id::bigint,
    u.name,
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance,
    COALESCE(SUM(
        CASE 
            WHEN lr.type = 'Annual Leave' AND lr.status = 1 
            THEN COALESCE((lr.end_date - lr.start_date) + 1, 1)
            ELSE 0
        END
    ), 0) AS annual_leave_used,
    COALESCE(SUM(
        CASE 
            WHEN lr.type = 'Sick Leave' AND lr.status = 1 
            THEN COALESCE((lr.end_date - lr.start_date) + 1, 1)
            ELSE 0
        END
    ), 0) AS sick_leave_used,
    COALESCE(SUM(
        CASE 
            WHEN lr.type = 'Emergency Leave' AND lr.status = 1 
            THEN COALESCE((lr.end_date - lr.start_date) + 1, 1)
            ELSE 0
        END
    ), 0) AS emergency_leave_used,
    (15 - COALESCE(SUM(
        CASE 
            WHEN lr.type = 'Annual Leave' AND lr.status = 1 
            THEN COALESCE((lr.end_date - lr.start_date) + 1, 1)
            ELSE 0
        END
    ), 0)) AS expected_annual_balance,
    u.annual_leave_balance - (15 - COALESCE(SUM(
        CASE 
            WHEN lr.type = 'Annual Leave' AND lr.status = 1 
            THEN COALESCE((lr.end_date - lr.start_date) + 1, 1)
            ELSE 0
        END
    ), 0)) AS balance_discrepancy
FROM users u
LEFT JOIN leave_requests lr ON u.id::bigint = lr.user_id 
    AND lr.deleted_at IS NULL
WHERE u.deleted_at::timestamp IS NULL
    AND u."userRole" IN ('staff', 'admin')
GROUP BY u.id::bigint, u.name, u.annual_leave_balance, u.sick_leave_balance, u.emergency_leave_balance
ORDER BY u.name
LIMIT 50;


-- ============================================================================
-- QUERY 10: Complete User Activity Overview (All Tables)
-- Coverage: users + attendances + leave_requests + shiftschedules
-- ============================================================================
SELECT 
    u.id::bigint,
    u.name,
    u.email::text,
    u."userRole",
    u.hired_on,
    -- Attendance stats
    COUNT(DISTINCT a.id) AS total_attendances,
    MAX(a.date) AS last_attendance_date,
    -- Leave stats
    COUNT(DISTINCT lr.id) AS total_leave_requests,
    COALESCE(SUM(CASE WHEN lr.status = 0 THEN 1 ELSE 0 END), 0) AS pending_leaves,
    COALESCE(SUM(CASE WHEN lr.status = 1 THEN 1 ELSE 0 END), 0) AS approved_leaves,
    COALESCE(SUM(CASE WHEN lr.status = 2 THEN 1 ELSE 0 END), 0) AS rejected_leaves,
    -- Schedule stats
    COUNT(DISTINCT s.id) AS total_shift_assignments,
    COUNT(DISTINCT CASE WHEN s.day >= CURRENT_DATE THEN s.id END) AS upcoming_shifts,
    -- Leave balances
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance
FROM users u
LEFT JOIN attendances a ON u.id::bigint = a.user_id 
    AND a.deleted_at IS NULL
LEFT JOIN leave_requests lr ON u.id::bigint = lr.user_id 
    AND lr.deleted_at IS NULL
LEFT JOIN shiftschedules s ON u.id::bigint = s.user_id 
    AND s.deleted_at IS NULL
WHERE u.deleted_at::timestamp IS NULL
    AND u."userRole" IN ('staff', 'admin')
GROUP BY 
    u.id::bigint, u.name, u.email::text, u."userRole", u.hired_on,
    u.annual_leave_balance, u.sick_leave_balance, u.emergency_leave_balance
ORDER BY u.name
LIMIT 50;


-- ============================================================================
-- END OF QUERIES
-- Key Fixes for Supabase:
-- 1. Cast ambiguous columns: id::bigint, email::text, phone::text
-- 2. Use deleted_at::timestamp for soft delete checks
-- 3. Handle NULL values with NULLS LAST, COALESCE
-- 4. Added LIMIT to all queries for performance
-- 5. Use INTERVAL for date arithmetic instead of DATEDIFF
-- ============================================================================
