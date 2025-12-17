<div align="center">

# 🎓 SmartDev Academic LMS

### Comprehensive Learning Management System for Educational Institutions

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

[Features](#-features) • [Installation](#-installation) • [API Documentation](#-api-documentation) • [Usage](#-usage) • [Contributing](#-contributing)

![SmartDev Academic Banner](https://via.placeholder.com/1200x300/4F46E5/FFFFFF?text=SmartDev+Academic+LMS)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
  - [Backend Setup](#backend-setup)
  - [Frontend Setup](#frontend-setup)
- [Configuration](#-configuration)
- [Database](#-database)
- [API Documentation](#-api-documentation)
- [User Roles](#-user-roles)
- [Usage Guide](#-usage-guide)
- [Screenshots](#-screenshots)
- [Deployment](#-deployment)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

---

## 🎯 Overview

**SmartDev Academic LMS** adalah sistem manajemen pembelajaran (Learning Management System) berbasis web yang dirancang khusus untuk institusi pendidikan. Sistem ini menyediakan platform terintegrasi untuk mengelola pembelajaran, penugasan, penilaian, dan monitoring akademik dengan antarmuka yang modern dan responsif.

### 🌟 Why SmartDev Academic?

- ✅ **Multi-Role System** - Mendukung 4 peran pengguna dengan dashboard khusus
- ✅ **Complete Academic Management** - Dari pendaftaran hingga sertifikasi
- ✅ **Modern UI/UX** - Interface yang intuitif dengan TailwindCSS
- ✅ **Secure & Scalable** - Laravel Sanctum authentication & RESTful API
- ✅ **Real-time Updates** - Notifikasi dan statistik real-time
- ✅ **Comprehensive Documentation** - Dokumentasi lengkap untuk developer

---

## ✨ Features

### 🔐 Authentication & Authorization
- [x] Multi-role authentication (Admin, Instructor, Student, Parent)
- [x] Laravel Sanctum token-based authentication
- [x] Role-based access control (RBAC)
- [x] Secure password hashing & validation
- [x] Password reset functionality
- [x] Session management & token revocation

### 📝 Student Registration System
- [x] Multi-step registration process
  - **Step 1**: Personal information form
  - **Step 2**: Document upload (KTP, KK, Ijazah, Photo)
- [x] Drag & drop file upload with preview
- [x] Real-time form validation
- [x] Admin approval/rejection workflow
- [x] Registration status tracking

### 👨‍💼 Admin Dashboard
- [x] Registration management (approve/reject)
- [x] User management (CRUD operations)
- [x] Statistical analytics with charts
- [x] Course & enrollment overview
- [x] Student risk analysis
- [x] Certificate analytics
- [x] Financial reports
- [x] System monitoring

### 👨‍🎓 Student Dashboard
- [x] Enrolled courses display
- [x] Course materials access
- [x] Assignment submission system
- [x] Grade viewing with breakdown
- [x] Attendance records
- [x] Certificate display
- [x] Notification center
- [x] Course announcements

### 👨‍🏫 Instructor Dashboard
- [x] Course management (create, edit, view)
- [x] Module & material management
- [x] Assignment creation & management
- [x] Submission review & grading
- [x] Bulk grading functionality
- [x] Attendance tracking
- [x] Student enrollment overview
- [x] Announcement creation
- [x] Performance analytics

### 👨‍👩‍👧 Parent Dashboard
- [x] Multi-child monitoring
- [x] Academic performance overview
- [x] Grade reports with charts
- [x] Attendance monitoring
- [x] Assignment tracking
- [x] Payment history
- [x] Child selector interface

### 📚 Course Management
- [x] Course CRUD operations
- [x] Module organization
- [x] Material upload & management
- [x] Assignment creation with due dates
- [x] Enrollment management
- [x] Course announcements
- [x] Certificate generation

### 📊 Grading System
- [x] Configurable grade components
- [x] Weighted grading calculation
- [x] Individual & bulk grade entry
- [x] Grade history tracking
- [x] Final grade calculation
- [x] Grade reports for students & parents

### 🔔 Notification System
- [x] Database notifications
- [x] Notification center UI
- [x] Mark as read functionality
- [x] Notification types:
  - Registration approval/rejection
  - Assignment graded
  - New announcements
  - Course enrollment
  - Certificate issued

### 📁 File Management
- [x] Secure file upload with validation
- [x] Multiple file type support (PDF, DOC, images)
- [x] File size validation (max 2MB)
- [x] Storage organization
- [x] Public URL generation
- [x] File preview functionality

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12.x | PHP Framework |
| **PHP** | 8.2+ | Programming Language |
| **MySQL** | 8.0+ | Database |
| **Laravel Sanctum** | 4.2 | API Authentication |
| **Composer** | 2.x | Dependency Manager |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel Blade** | - | Template Engine |
| **TailwindCSS** | 4.0 | CSS Framework |
| **Vanilla JavaScript** | ES6+ | Frontend Logic |
| **Chart.js** | 4.x | Data Visualization |
| **SweetAlert2** | 11.x | Beautiful Alerts |
| **Vite** | 7.x | Build Tool |

### Additional Tools
- **PlantUML** - Database & flow diagrams
- **Swagger/OpenAPI** - API documentation
- **Faker** - Test data generation

---

## 💻 System Requirements

### Minimum Requirements
```
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.x
- npm >= 9.x
- MySQL >= 8.0 or MariaDB >= 10.6
- Apache/Nginx web server
- 2GB RAM
- 5GB disk space
```

### Recommended Requirements
```
- PHP 8.3+
- MySQL 8.0+
- 4GB+ RAM
- SSD storage
- HTTPS/SSL certificate
```

### PHP Extensions Required
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick
```

---

## 🚀 Installation

### Quick Start

```bash
# Clone the repository
git clone https://github.com/your-username/smartdev-academic.git
cd smartdev-academic

# Install backend dependencies
cd Backend
composer install

# Install frontend dependencies
cd ../Frontend
composer install
npm install
```

---

### Backend Setup

#### 1️⃣ Navigate to Backend Directory
```bash
cd Backend
```

#### 2️⃣ Install PHP Dependencies
```bash
composer install
```

#### 3️⃣ Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4️⃣ Configure `.env` File
```env
# Application
APP_NAME="SmartDev Academic"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartdev_academic
DB_USERNAME=root
DB_PASSWORD=

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000

# File Storage
FILESYSTEM_DISK=public

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### 5️⃣ Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE smartdev_academic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed

# Create storage symlink
php artisan storage:link
```

#### 6️⃣ Set File Permissions
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (Run as Administrator in PowerShell)
icacls storage /grant Users:(OI)(CI)F /T
icacls bootstrap/cache /grant Users:(OI)(CI)F /T
```

#### 7️⃣ Start Development Server
```bash
php artisan serve
# Server running at http://127.0.0.1:8000
```

---

### Frontend Setup

#### 1️⃣ Navigate to Frontend Directory
```bash
cd Frontend
```

#### 2️⃣ Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 3️⃣ Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

#### 4️⃣ Configure `.env` File
```env
APP_NAME="SmartDev Academic Frontend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:3000

# API Configuration (Backend URL)
API_BASE_URL=http://localhost:8000/api
```

#### 5️⃣ Build Assets
```bash
# Development mode with hot reload
npm run dev

# Production build
npm run build
```

#### 6️⃣ Start Frontend Server
```bash
php artisan serve --port=3000
# Server running at http://127.0.0.1:3000
```

---

## ⚙️ Configuration

### API Configuration

Update API base URL in frontend JavaScript files:

**File**: `Frontend/public/js/app.js`
```javascript
const API_BASE_URL = 'http://localhost:8000/api';
const API_VERSION = 'v1';
```

### CORS Configuration

**File**: `Backend/config/cors.php`
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Sanctum Configuration

**File**: `Backend/config/sanctum.php`
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000')),
```

---

## 🗄️ Database

### Entity Relationship Diagram (ERD)

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│    users    │──────│   students   │──────│   parents   │
└─────────────┘      └──────────────┘      └─────────────┘
       │                    │                      │
       │                    │                      │
       ▼                    ▼                      ▼
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│ instructors │      │ enrollments  │      │   courses   │
└─────────────┘      └──────────────┘      └─────────────┘
       │                    │                      │
       │                    └──────────┬───────────┘
       │                               │
       ▼                               ▼
┌─────────────┐              ┌──────────────┐
│assignments  │              │ submissions  │
└─────────────┘              └──────────────┘
       │                               │
       └───────────┬───────────────────┘
                   │
                   ▼
           ┌──────────────┐
           │    grades    │
           └──────────────┘
```

### Database Tables

<details>
<summary><b>📊 Click to view all tables (19 tables)</b></summary>

#### Core Tables
1. **users** - Base authentication table
2. **students** - Student profiles
3. **instructors** - Instructor profiles
4. **parents** - Parent profiles
5. **student_registrations** - Registration workflow

#### Academic Tables
6. **courses** - Course catalog
7. **course_modules** - Course content organization
8. **materials** - Learning materials
9. **assignments** - Course assignments
10. **enrollments** - Student-course relationships
11. **submissions** - Assignment submissions
12. **grades** - Student grades
13. **grade_components** - Grading criteria

#### Supporting Tables
14. **attendance_sessions** - Attendance tracking
15. **attendance_records** - Individual attendance
16. **certificates** - Course completion certificates
17. **announcements** - Notifications & announcements
18. **notifications** - User notifications
19. **payments** - Payment tracking

</details>

### Sample Database Commands

```bash
# Fresh migration (WARNING: Deletes all data)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Seed specific seeder
php artisan db:seed --class=StudentSeeder
```

---

## 📡 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication

All protected endpoints require Bearer token authentication:

```bash
Authorization: Bearer {your-token-here}
```

---

### 🔓 Public Endpoints (No Authentication)

#### 1. User Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student"
  },
  "token": "1|abc123xyz..."
}
```

#### 2. Student Registration (Step 1)
```http
POST /api/register-calon-siswa
Content-Type: application/json

{
  "full_name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "phone": "081234567890",
  "birth_date": "2000-01-15",
  "gender": "male",
  "address": "Jl. Contoh No. 123, Jakarta"
}
```

**Response:**
```json
{
  "message": "Registration step 1 successful",
  "registration_token": "abc123...",
  "next_step": "upload-documents"
}
```

#### 3. Forgot Password
```http
POST /api/forgot-password
Content-Type: application/json

{
  "email": "user@example.com"
}
```

#### 4. Reset Password
```http
POST /api/reset-password
Content-Type: application/json

{
  "email": "user@example.com",
  "token": "reset-token",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

---

### 🔒 Protected Endpoints (Require Authentication)

#### User Management

##### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

##### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

##### Change Password
```http
POST /api/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "OldPass123!",
  "password": "NewPass123!",
  "password_confirmation": "NewPass123!"
}
```

---

#### Student Registration (Step 2)

##### Upload Documents
```http
POST /api/upload-documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "ktp": (file),
  "kartu_keluarga": (file),
  "ijazah": (file),
  "photo": (file)
}
```

##### Check Registration Status
```http
GET /api/registration-status
Authorization: Bearer {token}
```

---

#### Admin - Registration Management

##### Get All Registrations
```http
GET /api/v1/registrations?status=pending
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` - Filter by status (pending, approved, rejected)
- `page` - Page number
- `per_page` - Items per page

##### Get Registration Detail
```http
GET /api/v1/registrations/{id}
Authorization: Bearer {token}
```

##### Approve Registration
```http
POST /api/v1/registrations/{id}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
  "approval_notes": "Documents verified successfully"
}
```

##### Reject Registration
```http
POST /api/v1/registrations/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "rejection_reason": "Invalid documents"
}
```

---

#### Course Management

##### Get All Courses
```http
GET /api/v1/courses
Authorization: Bearer {token}
```

##### Create Course
```http
POST /api/v1/courses
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "CS101",
  "name": "Introduction to Programming",
  "description": "Learn programming basics",
  "instructor_id": 1,
  "credits": 3,
  "capacity": 30,
  "schedule": "Mon-Wed 10:00-12:00",
  "start_date": "2025-01-15",
  "end_date": "2025-05-15"
}
```

##### Get Course Detail
```http
GET /api/v1/courses/{id}
Authorization: Bearer {token}
```

##### Update Course
```http
PUT /api/v1/courses/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Course Name",
  "description": "Updated description"
}
```

##### Delete Course
```http
DELETE /api/v1/courses/{id}
Authorization: Bearer {token}
```

---

#### Enrollment Management

##### Enroll Student
```http
POST /api/v1/enrollments
Authorization: Bearer {token}
Content-Type: application/json

{
  "student_id": 1,
  "course_id": 5
}
```

##### Get Student Enrollments
```http
GET /api/v1/students/{student_id}/enrollments
Authorization: Bearer {token}
```

---

#### Assignment Management

##### Get Course Assignments
```http
GET /api/v1/assignments?course_id=5
Authorization: Bearer {token}
```

##### Create Assignment
```http
POST /api/v1/assignments
Authorization: Bearer {token}
Content-Type: application/json

{
  "course_id": 5,
  "title": "Programming Assignment 1",
  "description": "Create a calculator program",
  "due_date": "2025-02-01 23:59:59",
  "max_score": 100,
  "weight": 20
}
```

---

#### Submission Management

##### Submit Assignment
```http
POST /api/v1/submissions
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "assignment_id": 10,
  "enrollment_id": 25,
  "submission_text": "My solution...",
  "file": (file)
}
```

##### Get Student Submissions
```http
GET /api/v1/students/{student_id}/submissions
Authorization: Bearer {token}
```

---

#### Grading System

##### Grade Submission
```http
POST /api/v1/grades
Authorization: Bearer {token}
Content-Type: application/json

{
  "enrollment_id": 25,
  "grade_component_id": 1,
  "score": 85,
  "notes": "Good work!"
}
```

##### Bulk Grade Entry
```http
POST /api/v1/grades/bulk
Authorization: Bearer {token}
Content-Type: application/json

{
  "grades": [
    {
      "enrollment_id": 25,
      "grade_component_id": 1,
      "score": 85
    },
    {
      "enrollment_id": 26,
      "grade_component_id": 1,
      "score": 90
    }
  ]
}
```

##### Get Student Grades
```http
GET /api/v1/grades/student?student_id=1&course_id=5
Authorization: Bearer {token}
```

---

#### Dashboard Statistics

##### Registration Statistics
```http
GET /api/v1/stats/registrations
Authorization: Bearer {token}
```

**Response:**
```json
{
  "total": 150,
  "pending": 25,
  "approved": 120,
  "rejected": 5,
  "monthly_trend": [...]
}
```

##### User Distribution
```http
GET /api/v1/stats/users
Authorization: Bearer {token}
```

##### Academic Performance
```http
GET /api/v1/stats/academic
Authorization: Bearer {token}
```

##### Financial Statistics
```http
GET /api/v1/stats/finance
Authorization: Bearer {token}
```

##### Summary Statistics
```http
GET /api/v1/stats/summary
Authorization: Bearer {token}
```

---

### 📊 API Response Format

#### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

#### HTTP Status Codes
| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## 👥 User Roles

### 1. 👨‍💼 Admin
**Capabilities:**
- ✅ Manage all users (CRUD)
- ✅ Approve/reject student registrations
- ✅ Manage courses and instructors
- ✅ View all statistics and reports
- ✅ Manage system settings
- ✅ Generate certificates
- ✅ Monitor system activity

**Default Login:**
```
Email: admin@smartdev.com
Password: admin123
```

---

### 2. 👨‍🏫 Instructor
**Capabilities:**
- ✅ Manage assigned courses
- ✅ Create modules and materials
- ✅ Create and manage assignments
- ✅ Grade student submissions
- ✅ Track student attendance
- ✅ Create announcements
- ✅ View course statistics
- ❌ Cannot access other instructors' courses

**Default Login:**
```
Email: instructor@smartdev.com
Password: instructor123
```

---

### 3. 👨‍🎓 Student
**Capabilities:**
- ✅ View enrolled courses
- ✅ Access course materials
- ✅ Submit assignments
- ✅ View grades and feedback
- ✅ Track attendance
- ✅ View certificates
- ✅ Receive notifications
- ❌ Cannot access unenrolled courses

**Default Login:**
```
Email: student@smartdev.com
Password: student123
```

---

### 4. 👨‍👩‍👧 Parent
**Capabilities:**
- ✅ Monitor multiple children
- ✅ View academic performance
- ✅ Track attendance
- ✅ View grades and reports
- ✅ View payment history
- ✅ Receive notifications
- ❌ Cannot modify academic data

**Default Login:**
```
Email: parent@smartdev.com
Password: parent123
```

---

## 📖 Usage Guide

### For Students

#### 1. Registration Process

**Step 1: Create Account**
1. Navigate to registration page
2. Fill personal information:
   - Full Name
   - Email
   - Password (min 8 characters)
   - Phone number
   - Birth date
   - Gender
   - Address
3. Click "Next" to proceed

**Step 2: Upload Documents**
1. Upload required documents:
   - KTP (ID Card)
   - Kartu Keluarga (Family Card)
   - Ijazah (Certificate)
   - Photo (Passport size)
2. Each file max 2MB (PDF, JPG, PNG)
3. Click "Submit Registration"

**Step 3: Wait for Approval**
- Admin will review your documents
- Check email for approval notification
- Login after approval

#### 2. Accessing Courses

1. Login to student dashboard
2. View enrolled courses
3. Click course to access:
   - Course materials
   - Assignments
   - Announcements
   - Grades

#### 3. Submitting Assignments

1. Go to "Assignments" tab
2. Click on assignment
3. Upload your work (file or text)
4. Click "Submit"
5. Track submission status

---

### For Instructors

#### 1. Creating a Course

1. Login to instructor dashboard
2. Click "Create Course"
3. Fill course details:
   - Course code
   - Course name
   - Description
   - Credits
   - Schedule
   - Start/End date
4. Click "Save"

#### 2. Adding Course Materials

1. Select your course
2. Go to "Materials" tab
3. Click "Add Material"
4. Upload file or add link
5. Organize by module

#### 3. Grading Submissions

1. Go to "Submissions" tab
2. Click on student submission
3. Review work
4. Enter score and feedback
5. Click "Save Grade"

---

### For Parents

#### 1. Monitoring Children

1. Login to parent dashboard
2. Select child from dropdown
3. View academic overview:
   - Current courses
   - Recent grades
   - Attendance summary
   - Upcoming assignments

#### 2. Viewing Reports

1. Go to "Reports" section
2. Select report type:
   - Grade reports
   - Attendance reports
   - Progress reports
3. Download or print

---

### For Admins

#### 1. Approving Registrations

1. Login to admin dashboard
2. Go to "Registrations" tab
3. Click "Pending" filter
4. Review registration details
5. Preview uploaded documents
6. Click "Approve" or "Reject"
7. Add notes (optional)

#### 2. Managing Users

1. Go to "Users" section
2. Select user type (Students/Instructors/Parents)
3. Perform actions:
   - Create new user
   - Edit user details
   - Deactivate user
   - Reset password

---

## 📸 Screenshots

<details>
<summary><b>🖼️ Click to view screenshots</b></summary>

### Landing Page
![Landing Page](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Landing+Page)

### Login Page
![Login](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Login+Page)

### Student Registration
![Registration](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Student+Registration)

### Admin Dashboard
![Admin Dashboard](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Admin+Dashboard)

### Student Dashboard
![Student Dashboard](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Student+Dashboard)

### Instructor Dashboard
![Instructor Dashboard](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Instructor+Dashboard)

### Parent Dashboard
![Parent Dashboard](https://via.placeholder.com/800x450/4F46E5/FFFFFF?text=Parent+Dashboard)

</details>

---

## 🚢 Deployment

### Production Deployment Checklist

#### 1. Environment Setup
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate secure key
php artisan key:generate
```

#### 2. Optimize Application
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize composer autoload
composer install --optimize-autoloader --no-dev
```

#### 3. Database Migration
```bash
# Run migrations on production
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed --class=AdminSeeder
```

#### 4. Storage Setup
```bash
# Create storage symlink
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 5. Web Server Configuration

**Apache (.htaccess)**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name smartdev-academic.com;
    root /var/www/smartdev-academic/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 6. SSL Configuration (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d smartdev-academic.com -d www.smartdev-academic.com

# Auto-renewal
sudo certbot renew --dry-run
```

#### 7. Cron Jobs (Laravel Scheduler)
```bash
# Add to crontab
crontab -e

# Add this line:
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

#### 8. Queue Worker (Background Jobs)
```bash
# Using Supervisor
sudo apt install supervisor

# Create config file: /etc/supervisor/conf.d/smartdev-worker.conf
[program:smartdev-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smartdev-worker:*
```

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage

# Run parallel tests
php artisan test --parallel
```

### Test Structure
```
tests/
├── Feature/
│   ├── AuthTest.php
│   ├── RegistrationTest.php
│   ├── CourseTest.php
│   ├── EnrollmentTest.php
│   └── GradingTest.php
└── Unit/
    ├── UserTest.php
    ├── CourseTest.php
    └── GradeCalculationTest.php
```

### Example Test Case
```php
public function test_student_can_submit_assignment()
{
    $student = User::factory()->create(['role' => 'student']);
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->student->id,
        'course_id' => $course->id
    ]);
    $assignment = Assignment::factory()->create(['course_id' => $course->id]);

    $response = $this->actingAs($student)
        ->postJson('/api/v1/submissions', [
            'assignment_id' => $assignment->id,
            'enrollment_id' => $enrollment->id,
            'submission_text' => 'My submission'
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Submission created successfully']);
}
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Storage Symlink Not Working
```bash
# Error: Storage symlink already exists
rm public/storage
php artisan storage:link
```

#### 2. Permission Denied on Storage
```bash
# Linux/Mac
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Windows (Run as Administrator)
icacls storage /grant Users:(OI)(CI)F /T
```

#### 3. CORS Issues
```bash
# Clear config cache
php artisan config:clear

# Check CORS settings in config/cors.php
# Ensure frontend URL is in allowed_origins
```

#### 4. Database Connection Failed
```bash
# Check .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartdev_academic
DB_USERNAME=root
DB_PASSWORD=your_password

# Test connection
php artisan migrate:status
```

#### 5. 500 Internal Server Error
```bash
# Enable debug mode temporarily
APP_DEBUG=true

# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear
```

#### 6. Token Mismatch Error
```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear

# Regenerate app key
php artisan key:generate
```

#### 7. File Upload Not Working
```bash
# Check storage permissions
ls -la storage/app/public

# Recreate symlink
php artisan storage:link

# Check upload_max_filesize in php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

```
feat: new feature
fix: bug fix
docs: documentation changes
style: code style changes
refactor: code refactoring
test: adding tests
chore: maintenance tasks
```

### Code Style

```bash
# Run Laravel Pint for code formatting
./vendor/bin/pint

# Run PHP CodeSniffer
./vendor/bin/phpcs
```

---

## 📄 License

This project is licensed under the **MIT License**.

```
MIT License

Copyright (c) 2025 SmartDev Academic

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 🗺️ Roadmap

### Version 2.1.0 (Q1 2025)
- [ ] Real-time notifications with WebSocket
- [ ] Advanced analytics dashboard
- [ ] Email notification system
- [ ] PDF report generation
- [ ] Mobile responsive improvements

### Version 2.2.0 (Q2 2025)
- [ ] Video upload for course materials
- [ ] Live streaming for classes
- [ ] Quiz/Exam module with auto-grading
- [ ] Discussion forum per course
- [ ] Calendar integration

### Version 3.0.0 (Q3 2025)
- [ ] Mobile app (iOS & Android)
- [ ] AI-powered recommendation system
- [ ] Video conferencing integration
- [ ] Plagiarism detection
- [ ] Multi-language support

---

## 📊 Project Statistics

![GitHub Stars](https://img.shields.io/github/stars/your-username/smartdev-academic?style=social)
![GitHub Forks](https://img.shields.io/github/forks/your-username/smartdev-academic?style=social)
![GitHub Issues](https://img.shields.io/github/issues/your-username/smartdev-academic)
![GitHub Pull Requests](https://img.shields.io/github/issues-pr/your-username/smartdev-academic)

```
Lines of Code       : 50,000+
Database Tables     : 19
API Endpoints       : 100+
Documentation Pages : 50+
Test Coverage       : 80%+
```

---

<div align="center">

### ⭐ If you find this project useful, please consider giving it a star!

**Made with ❤️ by SmartDev Academic Team**

[⬆ Back to Top](#-smartdev-academic-lms)

---

© 2025 SmartDev Academic. All Rights Reserved.

</div>
