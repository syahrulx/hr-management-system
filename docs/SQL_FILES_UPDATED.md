# ✅ SQL DOCUMENTATION FILES UPDATED

**Date:** October 22, 2025  
**Updated Files:** `schema.sql`, `seed.sql`, and Word document

---

## 📋 **WHAT WAS UPDATED**

### **1. schema.sql** ✅
**Complete database schema with all current changes:**

#### **Tables Defined:**
1. ✅ **users** - User accounts (owner, admin, employee)
2. ✅ **shift_schedules** - Daily shift assignments  
3. ✅ **attendances** - Attendance records linked to shifts
4. ✅ **leave_requests** - Leave/absence requests

#### **Key Features:**
- ✅ **snake_case naming** throughout (PostgreSQL standard)
- ✅ **All constraints defined**:
  - Primary Keys (`SERIAL PRIMARY KEY`)
  - Foreign Keys (`REFERENCES` with `ON DELETE CASCADE`)
  - Check Constraints (`CHECK` for enum values)
  - Unique Constraints (`UNIQUE` for data integrity)
- ✅ **Performance indexes** on frequently queried columns
- ✅ **Table and column comments** for documentation
- ✅ **No timestamps** (created_at, updated_at removed)
- ✅ **No soft deletes** (deleted_at removed)

#### **Column Highlights:**
```sql
users:
  - user_id (PK)
  - ic_number (Malaysian IC, UNIQUE)
  - user_role (CHECK: owner/admin/employee)
  - annual_leave_balance, sick_leave_balance, emergency_leave_balance

shift_schedules:
  - shift_id (PK)
  - user_id (FK → users)
  - shift_date (DATE)
  - shift_type (CHECK: morning/evening)

attendances:
  - attendance_id (PK)
  - user_id (FK → users)
  - shift_id (FK → shift_schedules)
  - clock_in_time, clock_out_time (TIME)
  - status (CHECK: on_time/late/missed)
  - UNIQUE(user_id, shift_id)

leave_requests:
  - request_id (PK)
  - user_id (FK → users)
  - type (CHECK: Annual/Sick/Emergency)
  - start_date, end_date
  - status (0=Pending, 1=Approved, 2=Rejected)
  - remark (TEXT)
```

---

### **2. seed.sql** ✅
**Realistic sample data for testing:**

#### **Data Included:**
- ✅ **6 Users:**
  - 1 Owner (owner@myhrsolutions.my)
  - 1 Admin (ahmad.razif@myhrsolutions.my)
  - 4 Employees (Malaysian names with IC numbers)
  
- ✅ **22 Shift Schedules:**
  - Covering September & October 2025
  - Mix of morning and evening shifts
  - Realistic distribution

- ✅ **22 Attendance Records:**
  - On-time attendances: 16
  - Late attendances: 4
  - Missed attendances: 2
  - Proper clock-in/clock-out times

- ✅ **7 Leave Requests:**
  - Pending: 3
  - Approved: 3
  - Rejected: 1
  - Various leave types (Annual, Sick, Emergency)

#### **Special Features:**
- ✅ Realistic Malaysian IC numbers
- ✅ Malaysian phone numbers (format: 601234xxxxx)
- ✅ Bcrypt hashed passwords (all accounts use: 'password')
- ✅ Proper foreign key relationships
- ✅ Sequence resets for proper ID continuation
- ✅ TRUNCATE CASCADE for clean data reset

---

### **3. Domain_Model_Verification_Lab_Exercise_2.docx** ✅
**Word document regenerated with:**
- ✅ Updated schema.sql embedded
- ✅ Updated seed.sql embedded
- ✅ All snake_case naming
- ✅ Ready for academic submission

---

## 🔄 **CHANGES FROM PREVIOUS VERSION**

### **Removed Fields:**
| Table | Removed Columns | Reason |
|-------|----------------|--------|
| All tables | `created_at`, `updated_at`, `deleted_at` | Timestamps removed |
| users | `salary`, `payroll_day`, `bank_acc_no` | Unused fields deleted |
| leave_requests | `is_seen`, `admin_response` | Unused fields deleted |
| leave_requests | `reason` | Renamed to `remark` |

### **Renamed Columns (camelCase → snake_case):**
| Old Name | New Name |
|----------|----------|
| `userID` | `user_id` |
| `shiftID` | `shift_id` |
| `attendanceID` | `attendance_id` |
| `requestID` | `request_id` |
| `icNumber` | `ic_number` |
| `hiredOn` | `hired_on` |
| `userRole` | `user_role` |
| `annualLeaveBalance` | `annual_leave_balance` |
| `sickLeaveBalance` | `sick_leave_balance` |
| `emergencyLeaveBalance` | `emergency_leave_balance` |
| `shiftDate` | `shift_date` |
| `shiftType` | `shift_type` |
| `clockInTime` | `clock_in_time` |
| `clockOutTime` | `clock_out_time` |
| `startDate` | `start_date` |
| `endDate` | `end_date` |

### **Renamed Tables:**
| Old Name | New Name |
|----------|----------|
| `shiftschedules` | `shift_schedules` |

---

## 📊 **DATA STATISTICS**

### **schema.sql:**
- Lines: ~150
- Tables: 4
- Constraints: 15+
- Indexes: 12
- Comments: 20+

### **seed.sql:**
- Lines: ~150
- Total Records: 57
- Users: 6
- Shifts: 22
- Attendances: 22
- Leave Requests: 7

---

## ✅ **VERIFICATION**

### **To Load Schema:**
```sql
psql -U your_username -d your_database -f docs/schema.sql
```

### **To Load Seed Data:**
```sql
psql -U your_username -d your_database -f docs/seed.sql
```

### **To Verify:**
```sql
-- Check table counts
SELECT 'users' AS table_name, COUNT(*) FROM users
UNION ALL SELECT 'shift_schedules', COUNT(*) FROM shift_schedules
UNION ALL SELECT 'attendances', COUNT(*) FROM attendances
UNION ALL SELECT 'leave_requests', COUNT(*) FROM leave_requests;

-- Expected output:
-- users: 6
-- shift_schedules: 22
-- attendances: 22
-- leave_requests: 7
```

---

## 🎯 **COMPLIANCE**

### **Naming Conventions:**
- ✅ **PostgreSQL Standard:** All snake_case
- ✅ **Consistent:** Throughout schema and data
- ✅ **Documented:** Comments explain all tables/columns

### **Best Practices:**
- ✅ **Referential Integrity:** All FKs with CASCADE
- ✅ **Data Validation:** CHECK constraints on enums
- ✅ **Performance:** Indexes on query columns
- ✅ **Documentation:** Inline comments and external docs

### **Academic Standards:**
- ✅ **Complete Schema:** All tables, constraints, indexes
- ✅ **Sample Data:** Realistic, sufficient for testing
- ✅ **Verified Queries:** Tested and working
- ✅ **Professional Format:** Clean, organized, commented

---

## 📚 **FILES LOCATION**

```
/docs/
  ├── schema.sql                                    ✅ Updated
  ├── seed.sql                                      ✅ Updated
  ├── queries.sql                                   ✅ Already updated
  ├── DomainModelVerification.md                   ✅ Already updated
  └── Domain_Model_Verification_Lab_Exercise_2.docx ✅ Regenerated
```

---

## ✅ **STATUS: READY FOR SUBMISSION**

All SQL documentation files are now:
- ✅ Up to date with current database schema
- ✅ Using proper snake_case naming
- ✅ Fully documented and commented
- ✅ Tested and verified working
- ✅ Ready for academic submission

---

**Updated by:** AI Assistant  
**Date:** October 22, 2025  
**Verified:** All files tested and working

