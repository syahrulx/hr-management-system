-- ============================================================================
-- HR MANAGEMENT SYSTEM - VERIFICATION QUERIES
-- ============================================================================
-- Database: PostgreSQL 15+
-- Generated: October 21, 2025
-- Purpose: Lab Exercise 2 - Query Requirements
-- ============================================================================

-- ============================================================================
-- QUERY 1: MULTI-TABLE JOIN (INNER JOIN)
-- Description: Show all attendances with employee names and shift details
-- Business Purpose: Daily attendance report with full context
-- ============================================================================

SELECT 
    a.attendance_id,
    u.name AS employee_name,
    u.user_role,
    s.shift_date,
    s.shift_type,
    a.status,
    a.clock_in_time,
    a.clock_out_time,
    CASE 
        WHEN a.clock_in_time IS NOT NULL AND a.clock_out_time IS NOT NULL
        THEN EXTRACT(HOUR FROM (a.clock_out_time - a.clock_in_time)) || ' hours'
        ELSE 'Incomplete'
    END AS hours_worked
FROM attendances a
INNER JOIN users u ON a.user_id = u.user_id
INNER JOIN shift_schedules s ON a.shift_id = s.shift_id
ORDER BY s.shift_date DESC, s.shift_type, u.name;

-- Expected Output: All attendance records with employee and shift information
-- Sample Result: 16 rows showing attendance details for October 2025


-- ============================================================================
-- QUERY 2: MULTI-TABLE JOIN (LEFT JOIN)
-- Description: Show all scheduled shifts with attendance status
-- Business Purpose: Identify which shifts have attendance records vs missing
-- ============================================================================

SELECT 
    s.shift_id,
    s.shift_date,
    s.shift_type,
    u.name AS assigned_employee,
    u.phone AS contact_number,
    COALESCE(a.status, 'NO ATTENDANCE') AS attendance_status,
    a.clock_in_time,
    a.clock_out_time,
    CASE 
        WHEN a.attendance_id IS NULL THEN 'Missing Attendance Record'
        WHEN a.status = 'missed' THEN 'Employee Did Not Show Up'
        WHEN a.clock_out_time IS NULL THEN 'Still Working'
        ELSE 'Complete'
    END AS record_status
FROM shift_schedules s
LEFT JOIN attendances a ON s.shift_id = a.shift_id
INNER JOIN users u ON s.user_id = u.user_id
WHERE s.shift_date >= '2025-10-21'
ORDER BY s.shift_date, s.shift_type;

-- Expected Output: All shifts (with or without attendance records)
-- Sample Result: 18 rows, some with NULL attendance indicating missing records


-- ============================================================================
-- QUERY 3: FILTERED QUERY WITH PARAMETERS (Date Range)
-- Description: Find all leave requests for a specific date range and status
-- Business Purpose: Monthly leave report for approved requests
-- Parameters: startDate = '2025-10-01', endDate = '2025-11-30', status = 1 (Approved)
-- ============================================================================

SELECT 
    lr.request_id,
    u.name AS employee_name,
    u.email,
    lr.type AS leave_type,
    lr.start_date,
    lr.end_date,
    CASE 
        WHEN lr.end_date IS NULL THEN 1
        ELSE (lr.end_date - lr.start_date + 1)
    END AS days_requested,
    lr.remark,
    CASE lr.status
        WHEN 0 THEN 'Pending'
        WHEN 1 THEN 'Approved'
        WHEN 2 THEN 'Rejected'
    END AS status_text,
    u.annual_leave_balance,
    u.sick_leave_balance,
    u.emergency_leave_balance
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.user_id
WHERE lr.start_date >= '2025-10-01'
  AND lr.start_date <= '2025-11-30'
  AND lr.status = 1
ORDER BY lr.start_date, u.name;

-- Expected Output: Approved leave requests in Oct-Nov 2025
-- Sample Result: 3 rows showing approved leaves with remaining balances


-- ============================================================================
-- QUERY 4: FILTERED QUERY WITH PARAMETERS (User-specific)
-- Description: Get attendance summary for a specific employee
-- Business Purpose: Employee performance review / monthly report card
-- Parameters: userID = 3, month = October 2025
-- ============================================================================

SELECT 
    u.user_id,
    u.name AS employee_name,
    u.user_role,
    u.hired_on,
    COUNT(a.attendance_id) AS total_shifts_worked,
    COUNT(CASE WHEN a.status = 'on_time' THEN 1 END) AS on_time_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) AS late_count,
    COUNT(CASE WHEN a.status = 'missed' THEN 1 END) AS missed_count,
    ROUND(
        (COUNT(CASE WHEN a.status = 'on_time' THEN 1 END)::NUMERIC / 
         NULLIF(COUNT(a.attendance_id), 0) * 100), 2
    ) AS on_time_percentage,
    COUNT(CASE WHEN a.clock_out_time IS NULL AND a.status != 'missed' THEN 1 END) AS incomplete_shifts
FROM users u
LEFT JOIN attendances a ON u.user_id = a.user_id
LEFT JOIN shift_schedules s ON a.shift_id = s.shift_id
WHERE u.user_id = 3
  AND (s.shift_date IS NULL OR 
       (EXTRACT(YEAR FROM s.shift_date) = 2025 AND 
        EXTRACT(MONTH FROM s.shift_date) = 10))
GROUP BY u.user_id, u.name, u.user_role, u.hired_on;

-- Expected Output: Performance summary for employee userID=3
-- Sample Result: 1 row showing Raj Kumar's October 2025 attendance statistics


-- ============================================================================
-- QUERY 5: INTEGRITY CHECK (Data Quality)
-- Description: Detect data integrity issues and business rule violations
-- Business Purpose: System health check and data quality audit
-- ============================================================================

-- 5a. Find attendances with conflicting leave (should be prevented by app logic)
SELECT 
    'Attendance on Approved Leave' AS issue_type,
    a.attendance_id AS record_id,
    u.name AS employee_name,
    s.shift_date AS attendance_date,
    lr.request_id AS conflicting_leave_id,
    lr.start_date AS leave_start,
    lr.end_date AS leave_end
FROM attendances a
INNER JOIN shift_schedules s ON a.shift_id = s.shift_id
INNER JOIN users u ON a.user_id = u.user_id
INNER JOIN leave_requests lr ON a.user_id = lr.user_id
WHERE lr.status = 1  -- Approved leaves
  AND s.shift_date >= lr.start_date
  AND (lr.end_date IS NULL OR s.shift_date <= lr.end_date)

UNION ALL

-- 5b. Find duplicate shift assignments (same employee, same day, multiple shifts)
SELECT 
    'Duplicate Shift Assignment' AS issue_type,
    s.shift_id AS record_id,
    u.name AS employee_name,
    s.shift_date AS attendance_date,
    NULL AS conflicting_leave_id,
    NULL AS leave_start,
    NULL AS leave_end
FROM shift_schedules s
INNER JOIN users u ON s.user_id = u.user_id
WHERE EXISTS (
    SELECT 1 
    FROM shift_schedules s2 
    WHERE s2.user_id = s.user_id 
      AND s2.shift_date = s.shift_date
      AND s2.shift_id != s.shift_id
)

UNION ALL

-- 5c. Find orphaned attendances (shiftID doesn't exist - should be impossible with FK)
SELECT 
    'Orphaned Attendance Record' AS issue_type,
    a.attendance_id AS record_id,
    'userID: ' || a.user_id AS employee_name,
    NULL AS attendance_date,
    NULL AS conflicting_leave_id,
    NULL AS leave_start,
    NULL AS leave_end
FROM attendances a
LEFT JOIN shift_schedules s ON a.shift_id = s.shift_id
WHERE s.shift_id IS NULL

UNION ALL

-- 5d. Find users with negative leave balances (should be prevented by CHECK constraint)
SELECT 
    'Negative Leave Balance' AS issue_type,
    u.user_id AS record_id,
    u.name AS employee_name,
    NULL AS attendance_date,
    NULL AS conflicting_leave_id,
    'Annual: ' || u.annual_leave_balance || 
    ', Sick: ' || u.sick_leave_balance || 
    ', Emergency: ' || u.emergency_leave_balance AS leave_start,
    NULL AS leave_end
FROM users u
WHERE u.annual_leave_balance < 0 
   OR u.sick_leave_balance < 0 
   OR u.emergency_leave_balance < 0

UNION ALL

-- 5e. Find leave requests with invalid date ranges (endDate < startDate)
SELECT 
    'Invalid Leave Date Range' AS issue_type,
    lr.request_id AS record_id,
    u.name AS employee_name,
    NULL AS attendance_date,
    NULL AS conflicting_leave_id,
    lr.start_date::TEXT AS leave_start,
    lr.end_date::TEXT AS leave_end
FROM leave_requests lr
INNER JOIN users u ON lr.user_id = u.user_id
WHERE lr.end_date IS NOT NULL 
  AND lr.end_date < lr.start_date

ORDER BY issue_type, record_id;

-- Expected Output: List of data integrity issues (should be 0 rows if system is healthy)
-- Sample Result: 0 rows = perfect data integrity, >0 rows = issues to investigate


-- ============================================================================
-- BONUS QUERY 6: AGGREGATE STATISTICS
-- Description: System-wide attendance statistics by employee and shift type
-- Business Purpose: Management dashboard / KPI tracking
-- ============================================================================

SELECT 
    u.name AS employee_name,
    u.user_role,
    COUNT(DISTINCT s.shift_date) AS days_scheduled,
    COUNT(a.attendance_id) AS attendance_records,
    COUNT(CASE WHEN s.shift_type = 'morning' THEN 1 END) AS morning_shifts,
    COUNT(CASE WHEN s.shift_type = 'evening' THEN 1 END) AS evening_shifts,
    COUNT(CASE WHEN a.status = 'on_time' THEN 1 END) AS on_time,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) AS late,
    COUNT(CASE WHEN a.status = 'missed' THEN 1 END) AS missed,
    ROUND(
        (COUNT(CASE WHEN a.status = 'on_time' THEN 1 END)::NUMERIC / 
         NULLIF(COUNT(a.attendance_id), 0) * 100), 1
    ) || '%' AS punctuality_rate,
    COUNT(lr.request_id) AS leave_requests,
    COUNT(CASE WHEN lr.status = 1 THEN 1 END) AS approved_leaves
FROM users u
LEFT JOIN shift_schedules s ON u.user_id = s.user_id
LEFT JOIN attendances a ON s.shift_id = a.shift_id
LEFT JOIN leave_requests lr ON u.user_id = lr.user_id
WHERE u.user_role IN ('employee', 'admin')
GROUP BY u.user_id, u.name, u.user_role
ORDER BY punctuality_rate DESC, u.name;

-- Expected Output: Performance summary for all employees
-- Sample Result: 3 rows showing comparative attendance statistics


-- ============================================================================
-- BONUS QUERY 7: TREND ANALYSIS
-- Description: Daily attendance trend for the month
-- Business Purpose: Identify patterns (e.g., more lates on Mondays)
-- ============================================================================

SELECT 
    s.shift_date,
    TO_CHAR(s.shift_date, 'Day') AS day_of_week,
    COUNT(DISTINCT s.shift_id) AS total_shifts,
    COUNT(a.attendance_id) AS attendance_records,
    COUNT(CASE WHEN a.status = 'on_time' THEN 1 END) AS on_time,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) AS late,
    COUNT(CASE WHEN a.status = 'missed' THEN 1 END) AS missed,
    COUNT(DISTINCT s.shift_id) - COUNT(a.attendance_id) AS missing_records,
    ROUND(
        (COUNT(CASE WHEN a.status = 'on_time' THEN 1 END)::NUMERIC / 
         NULLIF(COUNT(a.attendance_id), 0) * 100), 1
    ) AS on_time_rate
FROM shift_schedules s
LEFT JOIN attendances a ON s.shift_id = a.shift_id
WHERE s.shift_date >= '2025-10-01' 
  AND s.shift_date < '2025-11-01'
GROUP BY s.shift_date
ORDER BY s.shift_date;

-- Expected Output: Daily attendance statistics for October 2025
-- Sample Result: ~18 rows showing trends across the month


-- ============================================================================
-- END OF QUERIES
-- ============================================================================

