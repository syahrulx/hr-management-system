# HR Management System

> **Salam sejahtera, Dr. Su!**  
> This project was developed as part of my coursework. Thank you for your guidance and support throughout this journey.

🔗 **Live Demo**: [https://hr-management-system-production-aa74.up.railway.app/](https://hr-management-system-production-aa74.up.railway.app/)

---

## Tech Stack

| Layer      | Technology                    |
| ---------- | ----------------------------- |
| Backend    | Laravel 11 (PHP 8.2)          |
| Frontend   | Vue.js 3 + Inertia.js         |
| Styling    | Tailwind CSS                  |
| Database   | PostgreSQL                    |
| Deployment | Railway (Docker + FrankenPHP) |

---

## Project Structure

```
hr-management-system/
├── app/
│   ├── Http/
│   │   └── Controllers/          # All business logic controllers
│   │       ├── AttendanceController.php    # Clock in/out, late detection
│   │       ├── DashboardController.php     # Employee dashboard
│   │       ├── EmployeeController.php      # CRUD employees
│   │       ├── ProfileController.php       # User profile management
│   │       ├── ReportsController.php       # Attendance reports & export
│   │       ├── RequestController.php       # Leave request workflow
│   │       └── ScheduleController.php      # Shift assignment logic
│   └── Models/                   # Eloquent models
├── resources/
│   └── js/
│       └── Pages/                # Vue.js frontend pages
├── routes/
│   ├── web.php                   # Main application routes
│   └── auth.php                  # Authentication routes
└── database/
    ├── migrations/               # Database schema
    └── seeders/                  # Sample data seeder
```

---

## Business Logic Controllers

| Controller             | Purpose                                                                                                                       |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `AttendanceController` | Handles clock in/out with IP restriction, 15-minute late margin, and night shift "Cinderella" logic                           |
| `ScheduleController`   | Weekly shift assignment with 6-day limit, leave conflict detection, and auto-generated supervisor shifts                      |
| `RequestController`    | Leave request workflow with balance validation, 7-day advance rule for Annual Leave, and Emergency Leave same-day restriction |
| `ReportsController`    | Monthly attendance reports with present/late/absent statistics and CSV export                                                 |
| `EmployeeController`   | Employee CRUD with role-based access (Owner, Admin, Employee)                                                                 |

---

## Default Login Credentials

| Role     | Email                         | Password |
| -------- | ----------------------------- | -------- |
| Owner    | owner@myhrsolutions.my        | password |
| Admin    | ahmad.razif@myhrsolutions.my  | password |
| Employee | siti.noraini@myhrsolutions.my | password |

---

## Local Development

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed
php artisan migrate --seed

# Start development servers
php artisan serve
npm run dev
```

---

**Developed by Syahrul** | 2026
