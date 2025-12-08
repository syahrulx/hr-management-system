-- ============================================================================
-- HR MANAGEMENT SYSTEM - DATABASE SCHEMA
-- ============================================================================
-- Database: PostgreSQL
-- Naming Convention: snake_case (PostgreSQL standard)
-- Last Updated: October 22, 2025
-- ============================================================================

-- ============================================================================
-- TABLE: users
-- Description: Stores employee and owner user accounts
-- ============================================================================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    ic_number VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    hired_on DATE NOT NULL,
    user_role VARCHAR(20) NOT NULL CHECK (user_role IN ('owner', 'admin', 'employee')),
    annual_leave_balance INTEGER NOT NULL DEFAULT 14 CHECK (annual_leave_balance >= 0),
    sick_leave_balance INTEGER NOT NULL DEFAULT 14 CHECK (sick_leave_balance >= 0),
    emergency_leave_balance INTEGER NOT NULL DEFAULT 7 CHECK (emergency_leave_balance >= 0)
);

-- Indexes for users table
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_ic_number ON users(ic_number);
CREATE INDEX idx_users_user_role ON users(user_role);

-- ============================================================================
-- TABLE: shift_schedules
-- Description: Stores daily shift assignments for employees
-- ============================================================================
CREATE TABLE shift_schedules (
    shift_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    shift_type VARCHAR(20) NOT NULL CHECK (shift_type IN ('morning', 'evening')),
    shift_date DATE NOT NULL,
    
    -- Foreign Key Constraints
    CONSTRAINT fk_shift_schedules_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,
    
    -- Unique constraint: one user cannot have duplicate shifts on the same date
    CONSTRAINT unique_user_shift_date
        UNIQUE (user_id, shift_date)
);

-- Indexes for shift_schedules table
CREATE INDEX idx_shift_schedules_user_id ON shift_schedules(user_id);
CREATE INDEX idx_shift_schedules_shift_date ON shift_schedules(shift_date);
CREATE INDEX idx_shift_schedules_date_type ON shift_schedules(shift_date, shift_type);

-- ============================================================================
-- TABLE: attendances
-- Description: Stores employee attendance records linked to shift schedules
-- ============================================================================
CREATE TABLE attendances (
    attendance_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    shift_id INTEGER NOT NULL,
    clock_in_time TIME,
    clock_out_time TIME,
    status VARCHAR(20) NOT NULL CHECK (status IN ('on_time', 'late', 'missed')),
    
    -- Foreign Key Constraints
    CONSTRAINT fk_attendances_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,
    
    CONSTRAINT fk_attendances_shift
        FOREIGN KEY (shift_id)
        REFERENCES shift_schedules(shift_id)
        ON DELETE CASCADE,
    
    -- Unique constraint: one user cannot have duplicate attendance for the same shift
    CONSTRAINT unique_user_shift_attendance
        UNIQUE (user_id, shift_id)
);

-- Indexes for attendances table
CREATE INDEX idx_attendances_user_id ON attendances(user_id);
CREATE INDEX idx_attendances_shift_id ON attendances(shift_id);
CREATE INDEX idx_attendances_status ON attendances(status);

-- ============================================================================
-- TABLE: leave_requests
-- Description: Stores employee leave/absence requests
-- ============================================================================
CREATE TABLE leave_requests (
    request_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL CHECK (type IN ('Annual Leave', 'Sick Leave', 'Emergency Leave')),
    start_date DATE NOT NULL,
    end_date DATE,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    remark TEXT,
    
    -- Foreign Key Constraints
    CONSTRAINT fk_leave_requests_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,
    
    -- Check constraint: end_date must be >= start_date if provided
    CONSTRAINT check_leave_dates
        CHECK (end_date IS NULL OR end_date >= start_date)
);

-- Indexes for leave_requests table
CREATE INDEX idx_leave_requests_user_id ON leave_requests(user_id);
CREATE INDEX idx_leave_requests_status ON leave_requests(status);
CREATE INDEX idx_leave_requests_type ON leave_requests(type);
CREATE INDEX idx_leave_requests_start_date ON leave_requests(start_date);

-- ============================================================================
-- COMMENTS FOR DOCUMENTATION
-- ============================================================================

COMMENT ON TABLE users IS 'Stores employee and owner user accounts with leave balances';
COMMENT ON COLUMN users.user_id IS 'Primary key - unique user identifier';
COMMENT ON COLUMN users.ic_number IS 'Malaysian IC number (identification card)';
COMMENT ON COLUMN users.user_role IS 'User role: owner, admin, or employee';
COMMENT ON COLUMN users.annual_leave_balance IS 'Remaining annual leave days';
COMMENT ON COLUMN users.sick_leave_balance IS 'Remaining sick leave days';
COMMENT ON COLUMN users.emergency_leave_balance IS 'Remaining emergency leave days';

COMMENT ON TABLE shift_schedules IS 'Stores daily shift assignments for employees';
COMMENT ON COLUMN shift_schedules.shift_id IS 'Primary key - unique shift identifier';
COMMENT ON COLUMN shift_schedules.shift_type IS 'Type of shift: morning or evening';
COMMENT ON COLUMN shift_schedules.shift_date IS 'Date of the shift assignment';

COMMENT ON TABLE attendances IS 'Stores employee attendance records linked to shifts';
COMMENT ON COLUMN attendances.attendance_id IS 'Primary key - unique attendance record identifier';
COMMENT ON COLUMN attendances.shift_id IS 'Foreign key to shift_schedules - links attendance to specific shift';
COMMENT ON COLUMN attendances.clock_in_time IS 'Time when employee clocked in';
COMMENT ON COLUMN attendances.clock_out_time IS 'Time when employee clocked out';
COMMENT ON COLUMN attendances.status IS 'Attendance status: on_time, late, or missed';

COMMENT ON TABLE leave_requests IS 'Stores employee leave and absence requests';
COMMENT ON COLUMN leave_requests.request_id IS 'Primary key - unique request identifier';
COMMENT ON COLUMN leave_requests.type IS 'Type of leave: Annual, Sick, or Emergency';
COMMENT ON COLUMN leave_requests.status IS 'Request status: 0=Pending, 1=Approved, 2=Rejected';
COMMENT ON COLUMN leave_requests.remark IS 'Optional note or reason for leave request';

-- ============================================================================
-- SCHEMA NOTES
-- ============================================================================
-- 1. All table and column names use snake_case (PostgreSQL standard)
-- 2. All timestamps removed (no created_at, updated_at, deleted_at)
-- 3. Soft deletes removed (no deleted_at columns)
-- 4. Attendance date is derived from shift_schedules.shift_date via shift_id FK
-- 5. All foreign keys have ON DELETE CASCADE for referential integrity
-- 6. Check constraints enforce valid enum values
-- 7. Unique constraints prevent duplicate data
-- 8. Indexes added for performance on frequently queried columns
-- ============================================================================
