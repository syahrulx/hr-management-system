# 📊 DCD vs Relational Schema Naming Conventions

**Complete Guide for Database Design Documentation**

---

## 🎯 **EXECUTIVE SUMMARY**

| Aspect | Domain Class Diagram (DCD) | Relational Schema |
|--------|---------------------------|-------------------|
| **Purpose** | Conceptual/Logical Model | Physical Implementation |
| **Audience** | Business stakeholders, analysts | Database developers, DBAs |
| **Naming Style** | Business-friendly (flexible) | Database-compliant (strict) |
| **Case Convention** | PascalCase or camelCase | snake_case (PostgreSQL/MySQL) |
| **Underscores** | Optional/Rare | Standard/Required |
| **Platform** | Platform-independent | Platform-specific |

---

## 📋 **TABLE OF CONTENTS**

1. [Understanding the Difference](#1-understanding-the-difference)
2. [Naming Conventions Compared](#2-naming-conventions-compared)
3. [Real-World Examples](#3-real-world-examples)
4. [Best Practices](#4-best-practices)
5. [Your HR System Examples](#5-your-hr-system-examples)
6. [Common Mistakes](#6-common-mistakes)
7. [Recommendations](#7-recommendations)

---

## 1. Understanding the Difference

### **Domain Class Diagram (DCD)**
- **What:** Conceptual model showing business entities and relationships
- **When:** Early design phase, requirements gathering
- **Who:** Business analysts, stakeholders, developers
- **Goal:** Communicate business logic clearly
- **Format:** UML Class Diagrams, ERD (Entity-Relationship Diagram)

### **Relational Schema**
- **What:** Physical database implementation
- **When:** Development and deployment
- **Who:** Database developers, DBAs
- **Goal:** Implement efficient, maintainable database
- **Format:** SQL DDL (CREATE TABLE statements)

---

## 2. Naming Conventions Compared

### **2.1 CLASS/TABLE NAMES**

#### **DCD (Domain Class Diagram)**

**Conventions:**
- ✅ **PascalCase** (Most common in UML)
- ✅ **Singular** form (represents one instance)
- ✅ **Business terminology**
- ✅ **Descriptive names**

**Examples:**
```
✅ User
✅ Employee
✅ LeaveRequest
✅ ShiftSchedule
✅ AttendanceRecord
✅ PayrollInformation
```

**Alternative (Less Common):**
```
⚠️ user (lowercase - sometimes seen)
⚠️ leave_request (snake_case - rare in DCD)
```

#### **Relational Schema (PostgreSQL/MySQL)**

**Conventions:**
- ✅ **snake_case** (Standard)
- ✅ **Plural** form (table holds multiple rows)
- ✅ **All lowercase**
- ✅ **Underscores** for word separation

**Examples:**
```sql
✅ users
✅ employees
✅ leave_requests
✅ shift_schedules
✅ attendance_records
✅ payroll_information
```

**Why snake_case?**
- Case-insensitive in most SQL databases
- Avoids quoting identifiers
- Industry standard (PostgreSQL, MySQL, SQLite)
- Better readability in SQL queries

---

### **2.2 ATTRIBUTE/COLUMN NAMES**

#### **DCD (Domain Class Diagram)**

**Conventions:**
- ✅ **camelCase** (Standard in UML)
- ✅ **PascalCase** (Also acceptable)
- ✅ **Descriptive business terms**

**Examples:**
```
Class: User
---
+ userID: Integer
+ firstName: String
+ lastName: String
+ emailAddress: String
+ dateOfBirth: Date
+ hiredOn: Date
+ annualLeaveBalance: Integer
```

**Alternative (PascalCase):**
```
+ UserID: Integer
+ FirstName: String
+ LastName: String
```

#### **Relational Schema**

**Conventions:**
- ✅ **snake_case** (Standard)
- ✅ **All lowercase**
- ✅ **Underscores** for word separation
- ✅ **Abbreviations avoided** (unless common: `id`, `pk`)

**Examples:**
```sql
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email_address VARCHAR(255),
    date_of_birth DATE,
    hired_on DATE,
    annual_leave_balance INTEGER
);
```

---

### **2.3 PRIMARY KEYS**

#### **DCD**

**Conventions:**
- ✅ **camelCase:** `userID`, `employeeID`
- ✅ **PascalCase:** `UserID`, `EmployeeID`
- ✅ **Simple:** `id` (context is clear from class)

**Examples:**
```
Class: User
---
+ id: Integer {PK}
```

OR

```
Class: User
---
+ userID: Integer {PK}
```

#### **Relational Schema**

**Conventions:**
- ✅ **snake_case:** `user_id`, `employee_id`
- ✅ **Standard suffix:** `_id`
- ✅ **Descriptive:** Includes table reference

**Examples:**
```sql
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    ...
);

CREATE TABLE employees (
    employee_id SERIAL PRIMARY KEY,
    ...
);

-- NOT recommended:
-- id INTEGER PRIMARY KEY  (ambiguous in queries)
```

**Why use `table_name_id` format?**
```sql
-- Clear and unambiguous in JOINs
SELECT 
    u.user_id,
    u.name,
    lr.leave_request_id,
    lr.start_date
FROM users u
JOIN leave_requests lr ON lr.user_id = u.user_id;

-- vs confusing:
-- lr.id = u.id  (What does 'id' mean?)
```

---

### **2.4 FOREIGN KEYS**

#### **DCD**

**Conventions:**
- ✅ **Reference by association** (relationship lines)
- ✅ **May not show FK attribute** (implied by association)
- ✅ **If shown:** camelCase/PascalCase

**Example:**
```
Class: LeaveRequest
---
+ requestID: Integer {PK}
+ type: String
+ startDate: Date
+ endDate: Date

[Association to User shown with line, not attribute]
```

OR (if FK is explicit):
```
Class: LeaveRequest
---
+ requestID: Integer {PK}
+ userID: Integer {FK}
+ type: String
```

#### **Relational Schema**

**Conventions:**
- ✅ **Explicit FK columns** (always shown)
- ✅ **snake_case**
- ✅ **References parent PK name**
- ✅ **FK constraints defined**

**Example:**
```sql
CREATE TABLE leave_requests (
    leave_request_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50),
    start_date DATE,
    end_date DATE,
    
    CONSTRAINT fk_leave_requests_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
);
```

---

### **2.5 RELATIONSHIP/ASSOCIATION NAMES**

#### **DCD**

**Conventions:**
- ✅ **Verb phrases** (describes relationship)
- ✅ **Business language**
- ✅ **Bidirectional labels** (both directions)

**Examples:**
```
User ──── "submits" ───> LeaveRequest
User <─── "submitted by" ── LeaveRequest

Employee ──── "assigned to" ───> ShiftSchedule
Employee <─── "assigned for" ── ShiftSchedule
```

#### **Relational Schema**

**Conventions:**
- ✅ **Foreign key constraints** (enforces relationship)
- ✅ **Join conditions** (in queries)
- ✅ **Bridge tables** (for many-to-many)

**Examples:**
```sql
-- One-to-Many: Implicit via FK
CREATE TABLE leave_requests (
    user_id INTEGER REFERENCES users(user_id)
);

-- Many-to-Many: Explicit bridge table
CREATE TABLE employee_projects (
    employee_id INTEGER REFERENCES employees(employee_id),
    project_id INTEGER REFERENCES projects(project_id),
    PRIMARY KEY (employee_id, project_id)
);
```

---

### **2.6 BRIDGE/JUNCTION TABLES**

#### **DCD**

**Conventions:**
- ✅ **May be hidden** (shown as M:N relationship line)
- ✅ **If shown:** Combination of class names

**Examples:**
```
Employee ────M:N──── Project

OR (explicit association class):

EmployeeProject (or ProjectAssignment)
---
+ employeeID: Integer {FK}
+ projectID: Integer {FK}
+ assignedDate: Date
+ role: String
```

#### **Relational Schema**

**Conventions:**
- ✅ **Always explicit** (must exist as table)
- ✅ **snake_case**
- ✅ **Plural + plural** or **singular_singular**
- ✅ **Composite PK** from both FKs

**Examples:**
```sql
-- Option 1: Plural + Plural
CREATE TABLE employees_projects (
    employee_id INTEGER REFERENCES employees(employee_id),
    project_id INTEGER REFERENCES projects(project_id),
    assigned_date DATE,
    role VARCHAR(50),
    PRIMARY KEY (employee_id, project_id)
);

-- Option 2: Singular + Singular (common)
CREATE TABLE employee_project (
    employee_id INTEGER,
    project_id INTEGER,
    PRIMARY KEY (employee_id, project_id)
);
```

---

## 3. Real-World Examples

### **Example 1: Simple Entity**

#### **DCD:**
```
┌─────────────────────────┐
│        Employee         │
├─────────────────────────┤
│ + employeeID: Integer   │
│ + firstName: String     │
│ + lastName: String      │
│ + email: String         │
│ + hiredOn: Date         │
│ + salary: Decimal       │
└─────────────────────────┘
```

#### **Relational Schema:**
```sql
CREATE TABLE employees (
    employee_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    hired_on DATE NOT NULL,
    salary NUMERIC(10, 2)
);
```

---

### **Example 2: One-to-Many Relationship**

#### **DCD:**
```
┌─────────────┐           ┌──────────────────┐
│    User     │           │   LeaveRequest   │
├─────────────┤           ├──────────────────┤
│ + userID    │ 1       * │ + requestID      │
│ + name      │───────────│ + type           │
│ + email     │  submits  │ + startDate      │
└─────────────┘           │ + endDate        │
                          │ + status         │
                          └──────────────────┘
```

#### **Relational Schema:**
```sql
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE leave_requests (
    request_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status INTEGER DEFAULT 0,
    
    CONSTRAINT fk_leave_request_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
);
```

---

### **Example 3: Many-to-Many Relationship**

#### **DCD:**
```
┌──────────────┐                ┌─────────────┐
│   Student    │                │   Course    │
├──────────────┤                ├─────────────┤
│ + studentID  │ *            * │ + courseID  │
│ + name       │────────────────│ + name      │
│ + email      │   enrolls in   │ + credits   │
└──────────────┘                └─────────────┘
```

#### **Relational Schema:**
```sql
CREATE TABLE students (
    student_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    credits INTEGER
);

-- Bridge table (required in relational model)
CREATE TABLE student_enrollments (
    student_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    grade VARCHAR(2),
    
    PRIMARY KEY (student_id, course_id),
    
    CONSTRAINT fk_enrollment_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id),
    
    CONSTRAINT fk_enrollment_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
);
```

---

## 4. Best Practices

### **4.1 For DCD (Domain Class Diagram)**

✅ **DO:**
- Use **PascalCase** for class names: `LeaveRequest`, `ShiftSchedule`
- Use **camelCase** for attributes: `firstName`, `emailAddress`
- Use **singular** class names: `User` (not `Users`)
- Use **business terminology**: `AnnualLeaveBalance` (not `ALB`)
- Show **relationships** with verb phrases: "submits", "assigned to"
- Include **multiplicities**: `1`, `*`, `0..1`, `1..*`
- Mark **primary keys**: `{PK}` or underline
- Mark **foreign keys**: `{FK}` (if shown explicitly)

❌ **DON'T:**
- Use snake_case: ~~`leave_request`~~
- Use abbreviations: ~~`empID`~~ (use `employeeID`)
- Use plural class names: ~~`Users`~~ (use `User`)
- Include implementation details: ~~`user_id_fk_idx`~~

---

### **4.2 For Relational Schema**

✅ **DO:**
- Use **snake_case** for everything: `leave_requests`, `user_id`
- Use **plural** table names: `users`, `employees`
- Use **explicit** primary keys: `user_id` (not just `id`)
- Use **consistent** FK naming: Match the referenced PK name
- Include **constraints**: `NOT NULL`, `UNIQUE`, `CHECK`, `FK`
- Define **indexes** for performance
- Add **comments** for documentation

❌ **DON'T:**
- Use camelCase: ~~`leaveRequests`~~
- Use mixed case: ~~`Leave_Requests`~~
- Use generic PK names: ~~`id`~~ (use `table_name_id`)
- Omit constraints
- Use reserved keywords: ~~`user`~~, ~~`order`~~ (add `s` or prefix)

---

## 5. Your HR System Examples

### **HR Management System - Complete Mapping**

| **DCD (Conceptual)** | **Relational Schema (Physical)** |
|---------------------|----------------------------------|
| **Class: User** | **Table: users** |
| `+ userID: Integer {PK}` | `user_id SERIAL PRIMARY KEY` |
| `+ name: String` | `name VARCHAR(255) NOT NULL` |
| `+ email: String` | `email VARCHAR(255) UNIQUE NOT NULL` |
| `+ password: String` | `password VARCHAR(255) NOT NULL` |
| `+ phone: String` | `phone VARCHAR(20)` |
| `+ address: String` | `address TEXT` |
| `+ icNumber: String` | `ic_number VARCHAR(20) UNIQUE` |
| `+ hiredOn: Date` | `hired_on DATE NOT NULL` |
| `+ userRole: String` | `user_role VARCHAR(20) NOT NULL` |
| `+ annualLeaveBalance: Integer` | `annual_leave_balance INTEGER DEFAULT 14` |
| `+ sickLeaveBalance: Integer` | `sick_leave_balance INTEGER DEFAULT 14` |
| `+ emergencyLeaveBalance: Integer` | `emergency_leave_balance INTEGER DEFAULT 7` |

| **DCD (Conceptual)** | **Relational Schema (Physical)** |
|---------------------|----------------------------------|
| **Class: ShiftSchedule** | **Table: shift_schedules** |
| `+ shiftID: Integer {PK}` | `shift_id SERIAL PRIMARY KEY` |
| `+ userID: Integer {FK}` | `user_id INTEGER NOT NULL REFERENCES users(user_id)` |
| `+ shiftDate: Date` | `shift_date DATE NOT NULL` |
| `+ shiftType: String` | `shift_type VARCHAR(20) NOT NULL CHECK (shift_type IN ('morning', 'evening'))` |

| **DCD (Conceptual)** | **Relational Schema (Physical)** |
|---------------------|----------------------------------|
| **Class: Attendance** | **Table: attendances** |
| `+ attendanceID: Integer {PK}` | `attendance_id SERIAL PRIMARY KEY` |
| `+ userID: Integer {FK}` | `user_id INTEGER NOT NULL REFERENCES users(user_id)` |
| `+ shiftID: Integer {FK}` | `shift_id INTEGER NOT NULL REFERENCES shift_schedules(shift_id)` |
| `+ clockInTime: Time` | `clock_in_time TIME` |
| `+ clockOutTime: Time` | `clock_out_time TIME` |
| `+ status: String` | `status VARCHAR(20) NOT NULL` |

| **DCD (Conceptual)** | **Relational Schema (Physical)** |
|---------------------|----------------------------------|
| **Class: LeaveRequest** | **Table: leave_requests** |
| `+ requestID: Integer {PK}` | `request_id SERIAL PRIMARY KEY` |
| `+ userID: Integer {FK}` | `user_id INTEGER NOT NULL REFERENCES users(user_id)` |
| `+ type: String` | `type VARCHAR(50) NOT NULL` |
| `+ startDate: Date` | `start_date DATE NOT NULL` |
| `+ endDate: Date` | `end_date DATE` |
| `+ status: Integer` | `status INTEGER DEFAULT 0 CHECK (status IN (0, 1, 2))` |
| `+ remark: String` | `remark TEXT` |

---

## 6. Common Mistakes

### **❌ Mistake 1: Using snake_case in DCD**

**Wrong:**
```
Class: leave_request
---
+ request_id: Integer
+ user_id: Integer
+ start_date: Date
```

**Correct:**
```
Class: LeaveRequest
---
+ requestID: Integer
+ userID: Integer
+ startDate: Date
```

---

### **❌ Mistake 2: Using camelCase in SQL**

**Wrong:**
```sql
CREATE TABLE leaveRequests (
    requestID SERIAL PRIMARY KEY,
    userID INTEGER,
    startDate DATE
);
```

**Correct:**
```sql
CREATE TABLE leave_requests (
    request_id SERIAL PRIMARY KEY,
    user_id INTEGER,
    start_date DATE
);
```

---

### **❌ Mistake 3: Inconsistent FK naming**

**Wrong:**
```sql
CREATE TABLE leave_requests (
    id SERIAL PRIMARY KEY,
    user INTEGER,  -- Should be user_id
    ...
);
```

**Correct:**
```sql
CREATE TABLE leave_requests (
    request_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(user_id),
    ...
);
```

---

### **❌ Mistake 4: Singular table names**

**Wrong (for Relational Schema):**
```sql
CREATE TABLE user (...);
CREATE TABLE leave_request (...);
```

**Correct:**
```sql
CREATE TABLE users (...);
CREATE TABLE leave_requests (...);
```

**Note:** DCD uses singular (User), but SQL tables use plural (users)

---

## 7. Recommendations

### **📋 For Academic/Professional Projects:**

#### **Option 1: Standard Approach (Recommended)**
- **DCD:** Use **PascalCase** for classes, **camelCase** for attributes
- **Relational Schema:** Use **snake_case** for everything, **plural** table names
- **Document the mapping** between DCD and schema

**Example Documentation:**
```markdown
## Domain-to-Relational Mapping

| DCD Class | Relational Table | Notes |
|-----------|------------------|-------|
| User | users | One user entity → multiple user records |
| LeaveRequest | leave_requests | Follows standard naming conventions |
| requestID | request_id | camelCase → snake_case transformation |
```

---

#### **Option 2: Consistent Approach (Alternative)**
- **Both DCD and Schema:** Use **snake_case** throughout
- **Pro:** No translation needed
- **Con:** Less "UML standard", less business-friendly

**When to use:** 
- Database-heavy projects
- When working directly with DBAs
- PostgreSQL-specific projects

---

#### **Option 3: Hybrid Approach (For ERD)**
- **ERD:** Can use **snake_case** (closer to implementation)
- **DCD:** Use **PascalCase/camelCase** (business view)
- **Schema:** Use **snake_case** (implementation)

**Use case:**
- When ERD and schema are closely aligned
- For implementation-focused documentation

---

### **🎓 For Your Lab Exercise:**

**Current State (After Refactoring):**
- ✅ Database uses **snake_case**
- ✅ Application code uses **snake_case**
- ✅ Documentation SQL uses **snake_case**

**Recommendation for DCD/ERD:**

**Option A - Follow UML Standards:**
```
DCD Classes:
- User (class name)
  + userID: Integer
  + icNumber: String
  + hiredOn: Date

Maps to SQL:
- users (table name)
  + user_id INTEGER
  + ic_number VARCHAR
  + hired_on DATE
```

**Option B - Match Database (What you currently have):**
```
DCD/ERD:
- users
  + user_id: Integer
  + ic_number: String
  + hired_on: Date

SQL (Same):
- users
  + user_id INTEGER
  + ic_number VARCHAR
  + hired_on DATE
```

**My Recommendation for Your Submission:**
- Use **Option B** (snake_case everywhere)
- **Why:** Your documentation already uses snake_case, and it demonstrates you understand PostgreSQL standards
- **Add a note:** Explain that you're using database-style naming in DCD for clarity

**Sample Note for Documentation:**
```markdown
## Naming Convention Note

This project uses **snake_case** naming convention throughout all
documentation layers (DCD, ERD, and Relational Schema) to maintain
consistency with PostgreSQL standards and eliminate translation overhead
between conceptual and physical models.

Alternative approach would use PascalCase in DCD with explicit mapping
to snake_case in the relational schema.
```

---

## 📚 **Summary Table**

| Aspect | DCD (Standard) | DCD (Your Project) | Relational Schema |
|--------|----------------|-------------------|-------------------|
| **Class/Table Names** | `LeaveRequest` | `leave_requests` | `leave_requests` |
| **Attributes/Columns** | `requestID` | `request_id` | `request_id` |
| **Primary Keys** | `id` or `requestID` | `request_id` | `request_id` |
| **Foreign Keys** | Implicit (lines) | `user_id` | `user_id` |
| **Cardinality** | `1`, `*`, `0..1` | `1`, `*`, `0..1` | Via FK constraints |
| **Case Style** | PascalCase/camelCase | snake_case | snake_case |
| **Number** | Singular | Plural | Plural |

---

## ✅ **Final Recommendation**

### **For Your HR Management System:**

Since you've already implemented **snake_case throughout** your application and documentation, I recommend **staying consistent**:

1. **Keep DCD/ERD in snake_case** (as you have)
2. **Keep Relational Schema in snake_case** (as you have)
3. **Add documentation** explaining your choice
4. **Highlight the benefit:** No translation layer needed

### **Sample Statement for Your Report:**

> *"This project adopts snake_case naming convention across all design layers—from Domain Class Diagrams to physical database implementation. This approach aligns with PostgreSQL best practices and eliminates the cognitive overhead of translating between camelCase (conceptual) and snake_case (physical) naming schemes. While traditional UML uses PascalCase for class names, our database-centric approach prioritizes implementation clarity and PostgreSQL standards compliance."*

---

**Your current approach is valid, professional, and well-suited for a database-heavy project! ✅**

