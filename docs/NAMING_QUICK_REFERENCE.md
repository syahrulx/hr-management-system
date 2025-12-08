# 📋 Naming Convention Quick Reference Card

## 🎯 **TL;DR - What You Need to Know**

### **Traditional Approach (UML Standard):**
```
DCD:  User.userID          →  SQL:  users.user_id
      LeaveRequest.type    →        leave_requests.type
      ShiftSchedule        →        shift_schedules
```
**Requires mapping documentation!**

### **Your Approach (Database-Centric):**
```
DCD:  users.user_id        →  SQL:  users.user_id
      leave_requests.type  →        leave_requests.type
      shift_schedules      →        shift_schedules
```
**No translation needed!** ✅

---

## ⚡ **Quick Conversion Rules**

### **PascalCase → snake_case:**
```
LeaveRequest        →  leave_requests
ShiftSchedule       →  shift_schedules
AttendanceRecord    →  attendance_records
```

### **camelCase → snake_case:**
```
userID              →  user_id
firstName           →  first_name
emailAddress        →  email_address
dateOfBirth         →  date_of_birth
annualLeaveBalance  →  annual_leave_balance
clockInTime         →  clock_in_time
```

---

## ✅ **Your HR System - Current State**

All using **snake_case** (PostgreSQL standard):

| Entity | Table | Primary Key | Sample Columns |
|--------|-------|-------------|----------------|
| User | `users` | `user_id` | `ic_number`, `hired_on`, `user_role` |
| Shift Schedule | `shift_schedules` | `shift_id` | `shift_date`, `shift_type` |
| Attendance | `attendances` | `attendance_id` | `clock_in_time`, `clock_out_time` |
| Leave Request | `leave_requests` | `request_id` | `start_date`, `end_date` |

**Status:** ✅ Fully consistent, production-ready!

---

## 📝 **For Your Documentation**

Add this note to explain your approach:

> **Naming Convention Note:**
> 
> This project uses **snake_case** naming convention throughout all design layers (DCD, ERD, and Relational Schema) to maintain consistency with PostgreSQL standards and eliminate translation overhead between conceptual and physical models.

---

## 🚀 **Bottom Line**

**Your current approach (snake_case everywhere) is:**
- ✅ Valid
- ✅ Professional
- ✅ PostgreSQL best practice
- ✅ Academically acceptable
- ✅ Error-resistant

**Keep it!** 🎉

