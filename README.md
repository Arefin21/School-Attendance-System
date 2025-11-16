# School Attendance System

A comprehensive school attendance management system built with Laravel 10+. This system provides REST API endpoints for managing students, recording attendance, and generating reports with advanced features like caching, events, and custom commands.

## Features

### Student Management
- Complete CRUD operations for students
- Student search and filtering by class/section
- Photo upload support
- Unique student ID validation
- RESTful API with Laravel Resources

### Attendance Module
- Single and bulk attendance recording
- Status tracking (Present, Absent, Late)
- Attendance notes and recorded_by tracking
- Duplicate prevention (one attendance per student per day)
- Query optimization with eager loading
- Multiple report types (daily, monthly)

### Advanced Features
- **Service Layer**: Business logic separation for clean architecture
- **Custom Artisan Command**: Generate monthly reports via CLI
- **Events & Listeners**: Attendance recording notifications
- **Redis Caching**: Performance optimization for statistics and reports
- **API Authentication**: Laravel Sanctum token-based auth
- **Comprehensive Testing**: Feature and Unit tests with PHPUnit

## Tech Stack

- **Backend**: Laravel 10+
- **Database**: MySQL/PostgreSQL
- **Cache**: Redis
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit
- **API**: RESTful with Resource transformers

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Redis (for caching)
- Node.js & NPM (for assets)

### Setup Instructions

1. **Clone the repository**
```bash
git clone <repository-url>
cd school-attendance-system
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure your `.env` file**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_attendance
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

5. **Run migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

This will create:
- An admin user (email: admin@school.com, password: password)
- 50 sample students
- 30 days of attendance records for each student

6. **Start the development server**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

7. **Run tests** (optional)
```bash
php artisan test
```

## API Documentation

### Authentication

#### Register
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@school.com",
    "password": "password"
}

Response:
{
    "user": { ... },
    "token": "1|xxxxx..."
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Students API

#### List Students (with filters)
```http
GET /api/students?search=John&class=5&section=A&per_page=15
```

#### Get Single Student
```http
GET /api/students/{id}
```

#### Create Student (Protected)
```http
POST /api/students
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "John Doe",
    "student_id": "STU1234",
    "class": "5",
    "section": "A",
    "photo": <file>
}
```

#### Update Student (Protected)
```http
PUT /api/students/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe Updated",
    "class": "6"
}
```

#### Delete Student (Protected)
```http
DELETE /api/students/{id}
Authorization: Bearer {token}
```

### Attendance API

#### List Attendance (with filters)
```http
GET /api/attendances?date=2025-11-16&status=present&student_id=1&per_page=20
```

#### Record Single Attendance (Protected)
```http
POST /api/attendances
Authorization: Bearer {token}
Content-Type: application/json

{
    "student_id": 1,
    "date": "2025-11-16",
    "status": "present",
    "note": "On time"
}
```

#### Bulk Attendance Recording (Protected)
```http
POST /api/attendances/bulk
Authorization: Bearer {token}
Content-Type: application/json

{
    "date": "2025-11-16",
    "attendances": [
        {
            "student_id": 1,
            "status": "present",
            "note": "On time"
        },
        {
            "student_id": 2,
            "status": "absent",
            "note": "Sick leave"
        },
        {
            "student_id": 3,
            "status": "late",
            "note": "Traffic"
        }
    ]
}
```

#### Get Today's Summary
```http
GET /api/attendances/today-summary

Response:
{
    "date": "2025-11-16",
    "total_students": 50,
    "present": 40,
    "absent": 8,
    "late": 2,
    "attendance_percentage": 80
}
```

#### Get Attendance Stats by Date
```http
GET /api/attendances/stats-by-date?date=2025-11-16

Response:
{
    "date": "2025-11-16",
    "total_students": 50,
    "present": 40,
    "absent": 8,
    "late": 2,
    "attendance_percentage": 80,
    "students": [...]
}
```

#### Get Monthly Report
```http
GET /api/attendances/monthly-report?year=2025&month=11&class=5

Response:
{
    "year": 2025,
    "month": 11,
    "class": "5",
    "total_students": 10,
    "students": [
        {
            "student_id": 1,
            "student_name": "John Doe",
            "class": "5",
            "section": "A",
            "total_days": 22,
            "present_count": 20,
            "absent_count": 1,
            "late_count": 1,
            "attendance_percentage": 90.91
        }
    ]
}
```

#### Update Attendance (Protected)
```http
PUT /api/attendances/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "present",
    "note": "Updated note"
}
```

#### Delete Attendance (Protected)
```http
DELETE /api/attendances/{id}
Authorization: Bearer {token}
```

## Custom Artisan Commands

### Generate Monthly Attendance Report

Generate a detailed monthly attendance report for a specific month and optional class.

```bash
# Generate report for all classes
php artisan attendance:generate-report 2025-11

# Generate report for specific class
php artisan attendance:generate-report 2025-11 5
```

This will display a formatted table with:
- Student Name, ID, Class, Section
- Present, Absent, Late counts
- Attendance percentage

## Testing

The application includes comprehensive tests:

### Run all tests
```bash
php artisan test
```

### Run specific test suites
```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/StudentTest.php
```

### Test Coverage
- **StudentTest**: Student CRUD operations, validation, authentication
- **AttendanceTest**: Attendance recording, bulk operations, validation
- **AttendanceServiceTest**: Service layer business logic

## Redis Caching

The system uses Redis to cache frequently accessed data:

### Cached Data
- Today's attendance summary (1 hour TTL)
- Attendance stats by date (1 hour TTL)
- Monthly reports (1 day TTL)
- Student attendance statistics

### Cache Keys
- `attendance_today_{date}` - Today's summary
- `attendance_date_{date}` - Stats for specific date
- `attendance_monthly_report_{year}_{month}` - Monthly reports
- `attendance_stats_{student_id}` - Student statistics

### Cache Invalidation
Caches are automatically invalidated when:
- New attendance is recorded
- Existing attendance is updated
- Attendance is deleted

## Events & Listeners

### AttendanceRecorded Event
Dispatched whenever attendance is recorded or updated.

**Listeners:**
- `SendAttendanceNotification`: Logs attendance records (can be extended for email/SMS notifications)

## Database Structure

### Students Table
- `id` - Primary key
- `name` - Student full name
- `student_id` - Unique student identifier
- `class` - Student class/grade
- `section` - Class section
- `photo` - Photo URL (nullable)
- `timestamps`

### Attendances Table
- `id` - Primary key
- `student_id` - Foreign key to students
- `date` - Attendance date
- `status` - Enum: present, absent, late
- `note` - Optional note (nullable)
- `recorded_by` - Foreign key to users
- `timestamps`
- Unique constraint on (student_id, date)

## Test Credentials

**Admin User:**
- Email: `admin@school.com`
- Password: `password`

**Sample Students:**
- 50 students automatically created with IDs: STU1000-STU9999
- Classes: 1-10
- Sections: A-D

## Project Structure

```
school-attendance-system/
├── app/
│   ├── Console/Commands/
│   │   └── GenerateAttendanceReport.php
│   ├── Events/
│   │   └── AttendanceRecorded.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AttendanceController.php
│   │   │   ├── AuthController.php
│   │   │   └── StudentController.php
│   │   ├── Requests/
│   │   │   ├── BulkAttendanceRequest.php
│   │   │   ├── StoreAttendanceRequest.php
│   │   │   ├── StoreStudentRequest.php
│   │   │   └── UpdateStudentRequest.php
│   │   └── Resources/
│   │       ├── AttendanceResource.php
│   │       └── StudentResource.php
│   ├── Listeners/
│   │   └── SendAttendanceNotification.php
│   ├── Models/
│   │   ├── Attendance.php
│   │   ├── Student.php
│   │   └── User.php
│   └── Services/
│       └── AttendanceService.php
├── database/
│   ├── factories/
│   │   ├── AttendanceFactory.php
│   │   ├── StudentFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2025_11_16_132043_create_students_table.php
│   │   └── 2025_11_16_132050_create_attendances_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
└── tests/
    ├── Feature/
    │   ├── AttendanceTest.php
    │   └── StudentTest.php
    └── Unit/
        └── AttendanceServiceTest.php
```

## Architecture Patterns

### Service Layer Pattern
Business logic is separated into service classes (`AttendanceService`) to keep controllers thin and logic reusable.

### Repository Pattern (via Eloquent)
Models act as repositories with custom scopes for common queries.

### Resource Pattern
API responses are transformed using Laravel Resources for consistent formatting.

### Event-Driven Architecture
Key actions dispatch events that can trigger multiple listeners for extensibility.

## Performance Optimizations

1. **Eager Loading**: Relationships loaded efficiently to prevent N+1 queries
2. **Redis Caching**: Frequently accessed data cached with appropriate TTLs
3. **Query Scopes**: Reusable query builders on models
4. **Index**: Unique constraint on (student_id, date) for fast lookups
5. **Pagination**: All list endpoints support pagination

## Development Best Practices

- **SOLID Principles**: Service layer, single responsibility controllers
- **RESTful Design**: Standard HTTP verbs and status codes
- **Validation**: Form Request classes for input validation
- **Type Safety**: PHP type hints and return types
- **Testing**: Feature and unit tests for critical functionality
- **Clean Code**: Meaningful variable names, proper code organization

## API Response Format

### Success Response
```json
{
    "id": 1,
    "name": "John Doe",
    "student_id": "STU1234",
    "class": "5",
    "section": "A",
    "photo": "students/xyz.jpg",
    "created_at": "2025-11-16T10:00:00.000000Z",
    "updated_at": "2025-11-16T10:00:00.000000Z"
}
```

### Error Response
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "student_id": ["The student id has already been taken."]
    }
}
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions, please open an issue in the GitHub repository.
