# 🔑 LOGIN CREDENTIALS

## 📧 All User Accounts & Passwords

**Default Password for ALL accounts**: `password`

---

## 👑 OWNER ROLE (1 Account)

| Name | Email | Password | Phone | Role |
|------|-------|----------|-------|------|
| Owner User | `owner@myhrsolutions.my` | `password` | 019-8888888 | **owner** |

**Login Redirect**: `/reports` (Analytics Dashboard)

---

## 👨‍💼 ADMIN ROLE (1 Account)

| Name | Email | Password | Phone | Role |
|------|-------|----------|-------|------|
| Siti Noraini Binti Ahmad | `siti.noraini@myhrsolutions.my` | `password` | 013-9876543 | **admin** |

**Login Redirect**: `/dashboard`

---

## 👤 EMPLOYEE ROLE (8 Accounts)

### Employee #1 - Ahmad Razif Bin Ismail
- **Email**: `ahmad.razif@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 012-3456789
- **Performance**: Excellent (95%+ attendance, 90%+ task completion)

### Employee #2 - Muhammad Faiz Bin Abdullah
- **Email**: `faiz.abdullah@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 014-2233445
- **Performance**: Good (85-90% attendance, 80-85% task completion)

### Employee #3 - Lim Wei Jie
- **Email**: `lim.weijie@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 016-5566778
- **Performance**: Average (75-80% attendance, 70-75% task completion)

### Employee #4 - Sarah Johnson
- **Email**: `sarah.johnson@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 017-9988776
- **Performance**: Excellent (Top Performer)

### Employee #5 - Raj Kumar
- **Email**: `raj.kumar@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 018-7766554
- **Performance**: Good (Consistent Performer)

### Employee #6 - Aisha Rahman
- **Email**: `aisha.rahman@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 019-5544332
- **Performance**: Average (Needs Improvement)

### Employee #7 - David Chen
- **Email**: `david.chen@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 011-3322110
- **Performance**: Poor (Struggling Employee)

### Employee #8 - Fatimah Ali
- **Email**: `fatimah.ali@myhrsolutions.my`
- **Password**: `password`
- **Phone**: 013-1122334
- **Performance**: Good (New but Performing Well)

---

## 📊 QUICK REFERENCE TABLE

| # | Email | Password | Role | Performance |
|---|-------|----------|------|-------------|
| 1 | `owner@myhrsolutions.my` | `password` | Owner | N/A |
| 2 | `siti.noraini@myhrsolutions.my` | `password` | Admin | N/A |
| 3 | `ahmad.razif@myhrsolutions.my` | `password` | Employee | Excellent ⭐⭐⭐ |
| 4 | `faiz.abdullah@myhrsolutions.my` | `password` | Employee | Good ⭐⭐ |
| 5 | `lim.weijie@myhrsolutions.my` | `password` | Employee | Average ⭐ |
| 6 | `sarah.johnson@myhrsolutions.my` | `password` | Employee | Excellent ⭐⭐⭐ |
| 7 | `raj.kumar@myhrsolutions.my` | `password` | Employee | Good ⭐⭐ |
| 8 | `aisha.rahman@myhrsolutions.my` | `password` | Employee | Average ⭐ |
| 9 | `david.chen@myhrsolutions.my` | `password` | Employee | Poor 💤 |
| 10 | `fatimah.ali@myhrsolutions.my` | `password` | Employee | Good ⭐⭐ |

---

## 🔐 SECURITY NOTES

⚠️ **IMPORTANT**: 
- These credentials are for **DEVELOPMENT/TESTING ONLY**
- All passwords are set to `password`
- **Change all passwords** before deploying to production
- Use strong, unique passwords in production environment
- Enable email verification in production

---

## 🧪 TESTING SCENARIOS

### Test Owner Access:
```
Email: owner@myhrsolutions.my
Password: password
Expected: Redirect to /reports with analytics dashboard
```

### Test Admin Access:
```
Email: siti.noraini@myhrsolutions.my
Password: password
Expected: Full system access with employee management
```

### Test Employee Access (High Performer):
```
Email: sarah.johnson@myhrsolutions.my
Password: password
Expected: Personal dashboard with high attendance/task scores
```

### Test Employee Access (Low Performer):
```
Email: david.chen@myhrsolutions.my
Password: password
Expected: Personal dashboard with lower scores
```

---

## 📝 HOW TO RESET DATABASE & RECREATE ACCOUNTS

```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Or just seed (keeps existing data)
php artisan db:seed

# Add report data (September 2025 data)
php artisan db:seed --class=QuickReportsSeeder
```

---

## 🎯 TOTAL ACCOUNTS

- **Owner**: 1 account
- **Admin**: 1 account  
- **Employees**: 8 accounts
- **Total**: 10 accounts

All using password: `password` ✅

