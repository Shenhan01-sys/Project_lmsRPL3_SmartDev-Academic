# 📁 Installation & File Path Guide - LMS Frontend

Panduan lengkap lokasi file dan folder yang harus dibuat untuk implementasi LMS Frontend.

---

## 📋 Table of Contents
1. [Project Root Structure](#project-root-structure)
2. [Parent Dashboard Paths](#parent-dashboard-paths)
3. [Instructor Dashboard Paths](#instructor-dashboard-paths)
4. [Student Dashboard Paths](#student-dashboard-paths)
5. [Authentication Paths](#authentication-paths)
6. [Step-by-Step Installation](#step-by-step-installation)

---

## 📂 Project Root Structure

**Base Directory:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3`

```
lmsRPL3/
├── app/
├── bootstrap/
├── config/
├── database/
├── docs/                         ← Documentation folder (sudah ada)
│   └── frontend-guiding/        ← Guide files (sudah ada)
├── public/                       ← Public assets
│   ├── css/                     ← CSS files
│   └── js/                      ← JavaScript files
├── resources/
│   └── views/                   ← Blade templates
│       ├── layouts/             ← Layout templates
│       ├── auth/                ← Authentication views
│       ├── parent/              ← Parent dashboard views
│       ├── instructor/          ← Instructor dashboard views
│       └── student/             ← Student dashboard views
├── routes/
│   └── web.php                  ← Web routes
└── storage/
```

---

## 🎯 Parent Dashboard Paths

### 1. Layout Template
**Path:** `resources/views/layouts/parent.blade.php`

**Jika folder `layouts` belum ada:**
```bash
# Buat folder layouts
mkdir resources/views/layouts
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\layouts\parent.blade.php`

### 2. CSS File
**Path:** `public/css/parent/dashboard.css`

**Jika folder `css/parent` belum ada:**
```bash
# Buat folder structure
mkdir public/css
mkdir public/css/parent
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\css\parent\dashboard.css`

### 3. JavaScript API Helper
**Path:** `public/js/parent/api.js`

**Jika folder `js/parent` belum ada:**
```bash
# Buat folder structure
mkdir public/js
mkdir public/js/parent
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\js\parent\api.js`

### 4. Blade View Files

#### Main Dashboard
**Path:** `resources/views/parent/dashboard.blade.php`

**Jika folder `parent` belum ada:**
```bash
mkdir resources/views/parent
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\dashboard.blade.php`

#### Students Page
**Path:** `resources/views/parent/students/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/parent/students
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\students\index.blade.php`

#### Student Detail
**Path:** `resources/views/parent/students/show.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\students\show.blade.php`

#### Grades Page
**Path:** `resources/views/parent/grades.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\grades.blade.php`

#### Attendance Page
**Path:** `resources/views/parent/attendance.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\attendance.blade.php`

#### Announcements Page
**Path:** `resources/views/parent/announcements.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\announcements.blade.php`

#### Notifications Page
**Path:** `resources/views/parent/notifications.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\notifications.blade.php`

#### Certificates Page
**Path:** `resources/views/parent/certificates.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\parent\certificates.blade.php`

### 5. Parent Dashboard Complete Structure
```
resources/views/
└── parent/
    ├── dashboard.blade.php          ← Main dashboard
    ├── students/
    │   ├── index.blade.php          ← Students list
    │   └── show.blade.php           ← Student detail
    ├── grades.blade.php             ← Grades page
    ├── attendance.blade.php         ← Attendance page
    ├── announcements.blade.php      ← Announcements page
    ├── notifications.blade.php      ← Notifications page
    └── certificates.blade.php       ← Certificates page

public/
├── css/parent/
│   └── dashboard.css                ← Custom CSS
└── js/parent/
    └── api.js                       ← API Helper
```

---

## 👨‍🏫 Instructor Dashboard Paths

### 1. Layout Template
**Path:** `resources/views/layouts/instructor.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\layouts\instructor.blade.php`

### 2. CSS File
**Path:** `public/css/instructor/dashboard.css`

**Buat folder jika belum ada:**
```bash
mkdir public/css/instructor
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\css\instructor\dashboard.css`

### 3. JavaScript API Helper
**Path:** `public/js/instructor/api.js`

**Buat folder jika belum ada:**
```bash
mkdir public/js/instructor
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\js\instructor\api.js`

### 4. Blade View Files

#### Main Dashboard
**Path:** `resources/views/instructor/dashboard.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\dashboard.blade.php`

#### Courses - List
**Path:** `resources/views/instructor/courses/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/courses
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\courses\index.blade.php`

#### Courses - Create
**Path:** `resources/views/instructor/courses/create.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\courses\create.blade.php`

#### Courses - Edit
**Path:** `resources/views/instructor/courses/edit.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\courses\edit.blade.php`

#### Courses - Detail
**Path:** `resources/views/instructor/courses/show.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\courses\show.blade.php`

#### Assignments - List
**Path:** `resources/views/instructor/assignments/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/assignments
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\assignments\index.blade.php`

#### Assignments - Create
**Path:** `resources/views/instructor/assignments/create.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\assignments\create.blade.php`

#### Assignments - Submissions
**Path:** `resources/views/instructor/assignments/submissions.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\assignments\submissions.blade.php`

#### Grading Page
**Path:** `resources/views/instructor/grading/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/grading
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\grading\index.blade.php`

#### Attendance - List
**Path:** `resources/views/instructor/attendance/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/attendance
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\attendance\index.blade.php`

#### Attendance - Create Session
**Path:** `resources/views/instructor/attendance/create.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\attendance\create.blade.php`

#### Students Page
**Path:** `resources/views/instructor/students/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/students
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\students\index.blade.php`

#### Announcements - List
**Path:** `resources/views/instructor/announcements/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/announcements
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\announcements\index.blade.php`

#### Announcements - Create
**Path:** `resources/views/instructor/announcements/create.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\announcements\create.blade.php`

#### Certificates Page
**Path:** `resources/views/instructor/certificates/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/instructor/certificates
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\instructor\certificates\index.blade.php`

### 5. Instructor Dashboard Complete Structure
```
resources/views/
└── instructor/
    ├── dashboard.blade.php
    ├── courses/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── assignments/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── submissions.blade.php
    ├── grading/
    │   └── index.blade.php
    ├── attendance/
    │   ├── index.blade.php
    │   └── create.blade.php
    ├── students/
    │   └── index.blade.php
    ├── announcements/
    │   ├── index.blade.php
    │   └── create.blade.php
    └── certificates/
        └── index.blade.php

public/
├── css/instructor/
│   └── dashboard.css
└── js/instructor/
    └── api.js
```

---

## 🎓 Student Dashboard Paths

### 1. Layout Template
**Path:** `resources/views/layouts/student.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\layouts\student.blade.php`

### 2. CSS File
**Path:** `public/css/student/dashboard.css`

**Buat folder jika belum ada:**
```bash
mkdir public/css/student
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\css\student\dashboard.css`

### 3. JavaScript API Helper
**Path:** `public/js/student/api.js`

**Buat folder jika belum ada:**
```bash
mkdir public/js/student
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\js\student\api.js`

### 4. Blade View Files

#### Main Dashboard
**Path:** `resources/views/student/dashboard.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\dashboard.blade.php`

#### Courses - List
**Path:** `resources/views/student/courses/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student/courses
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\courses\index.blade.php`

#### Courses - Detail
**Path:** `resources/views/student/courses/show.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\courses\show.blade.php`

#### Assignments - List
**Path:** `resources/views/student/assignments/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student/assignments
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\assignments\index.blade.php`

#### Assignments - Detail & Submit
**Path:** `resources/views/student/assignments/show.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\assignments\show.blade.php`

#### Grades Page
**Path:** `resources/views/student/grades/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student/grades
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\grades\index.blade.php`

#### Attendance Page
**Path:** `resources/views/student/attendance/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student/attendance
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\attendance\index.blade.php`

#### Certificates Page
**Path:** `resources/views/student/certificates/index.blade.php`

**Buat folder:**
```bash
mkdir resources/views/student/certificates
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\certificates\index.blade.php`

#### Profile Page
**Path:** `resources/views/student/profile.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\profile.blade.php`

#### Notifications Page
**Path:** `resources/views/student/notifications.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\student\notifications.blade.php`

### 5. Student Dashboard Complete Structure
```
resources/views/
└── student/
    ├── dashboard.blade.php
    ├── courses/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── assignments/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── grades/
    │   └── index.blade.php
    ├── attendance/
    │   └── index.blade.php
    ├── certificates/
    │   └── index.blade.php
    ├── profile.blade.php
    └── notifications.blade.php

public/
├── css/student/
│   └── dashboard.css
└── js/student/
    └── api.js
```

---

## 🔐 Authentication Paths

### 1. Login Page
**Path:** `resources/views/auth/login.blade.php`

**Buat folder jika belum ada:**
```bash
mkdir resources/views/auth
```

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\auth\login.blade.php`

### 2. Register Page
**Path:** `resources/views/auth/register.blade.php`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\resources\views\auth\register.blade.php`

### 3. Authentication CSS
**Path:** `public/css/auth.css`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\css\auth.css`

### 4. Authentication JavaScript
**Path:** `public/js/auth.js`

**Lokasi lengkap:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\public\js\auth.js`

---

## 🚀 Step-by-Step Installation

### Step 1: Buat Folder Structure

Jalankan perintah berikut di terminal dari root project:

```bash
# Masuk ke project root
cd C:\Users\hansg\OneDrive\Desktop\lmsRPL3

# Buat folder layouts
mkdir -p resources/views/layouts

# Buat folder auth
mkdir -p resources/views/auth

# Buat folder parent dengan subfolders
mkdir -p resources/views/parent/students

# Buat folder instructor dengan subfolders
mkdir -p resources/views/instructor/courses
mkdir -p resources/views/instructor/assignments
mkdir -p resources/views/instructor/grading
mkdir -p resources/views/instructor/attendance
mkdir -p resources/views/instructor/students
mkdir -p resources/views/instructor/announcements
mkdir -p resources/views/instructor/certificates

# Buat folder student dengan subfolders
mkdir -p resources/views/student/courses
mkdir -p resources/views/student/assignments
mkdir -p resources/views/student/grades
mkdir -p resources/views/student/attendance
mkdir -p resources/views/student/certificates

# Buat folder CSS
mkdir -p public/css/parent
mkdir -p public/css/instructor
mkdir -p public/css/student

# Buat folder JavaScript
mkdir -p public/js/parent
mkdir -p public/js/instructor
mkdir -p public/js/student
```

### Step 2: Copy Files dari Guide

#### Parent Dashboard

1. **Layout Template**
   - Buka: `PARENT-DASHBOARD-GUIDE.md`
   - Cari: Section "Layout Template"
   - Copy code ke: `resources/views/layouts/parent.blade.php`

2. **CSS**
   - Buka: `PARENT-DASHBOARD-GUIDE.md`
   - Cari: Section "CSS Styling"
   - Copy code ke: `public/css/parent/dashboard.css`

3. **API Helper**
   - Buka: `PARENT-DASHBOARD-GUIDE.md`
   - Cari: Section "API Helper"
   - Copy code ke: `public/js/parent/api.js`

4. **Dashboard Page**
   - Buka: `PARENT-DASHBOARD-GUIDE.md`
   - Cari: Section "Main Dashboard Page"
   - Copy code ke: `resources/views/parent/dashboard.blade.php`

5. **Students Pages**
   - Copy code untuk students list ke: `resources/views/parent/students/index.blade.php`
   - Copy code untuk student detail ke: `resources/views/parent/students/show.blade.php`

6. **Other Pages**
   - Grades → `resources/views/parent/grades.blade.php`
   - Attendance → `resources/views/parent/attendance.blade.php`
   - Announcements → `resources/views/parent/announcements.blade.php`
   - Certificates → `resources/views/parent/certificates.blade.php`
   - Notifications → `resources/views/parent/notifications.blade.php`

#### Instructor Dashboard

1. **Layout Template**
   - Buka: `INSTRUCTOR-DASHBOARD-GUIDE.md`
   - Copy layout ke: `resources/views/layouts/instructor.blade.php`

2. **CSS**
   - Copy CSS ke: `public/css/instructor/dashboard.css`

3. **API Helper**
   - Copy API helper ke: `public/js/instructor/api.js`

4. **Dashboard & Pages**
   - Dashboard → `resources/views/instructor/dashboard.blade.php`
   - Courses (index, create, edit, show) → `resources/views/instructor/courses/`
   - Assignments (index, create, submissions) → `resources/views/instructor/assignments/`
   - Grading → `resources/views/instructor/grading/index.blade.php`
   - Attendance (index, create) → `resources/views/instructor/attendance/`
   - Students → `resources/views/instructor/students/index.blade.php`
   - Announcements (index, create) → `resources/views/instructor/announcements/`
   - Certificates → `resources/views/instructor/certificates/index.blade.php`

#### Student Dashboard

1. **Layout Template**
   - Buka: `STUDENT-DASHBOARD-GUIDE.md`
   - Copy layout ke: `resources/views/layouts/student.blade.php`

2. **CSS**
   - Copy CSS ke: `public/css/student/dashboard.css`

3. **API Helper**
   - Copy API helper ke: `public/js/student/api.js`

4. **Dashboard & Pages**
   - Dashboard → `resources/views/student/dashboard.blade.php`
   - Courses (index, show) → `resources/views/student/courses/`
   - Assignments (index, show) → `resources/views/student/assignments/`
   - Grades → `resources/views/student/grades/index.blade.php`
   - Attendance → `resources/views/student/attendance/index.blade.php`
   - Certificates → `resources/views/student/certificates/index.blade.php`
   - Profile → `resources/views/student/profile.blade.php`
   - Notifications → `resources/views/student/notifications.blade.php`

### Step 3: Setup Routes

Edit file: `routes/web.php`

**Lokasi:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\routes\web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Parent Dashboard Routes
Route::middleware(['auth:sanctum'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [ParentController::class, 'students'])->name('students');
    Route::get('/students/{id}', [ParentController::class, 'studentDetail'])->name('students.show');
    Route::get('/grades', [ParentController::class, 'grades'])->name('grades');
    Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
    Route::get('/announcements', [ParentController::class, 'announcements'])->name('announcements');
    Route::get('/certificates', [ParentController::class, 'certificates'])->name('certificates');
    Route::get('/notifications', [ParentController::class, 'notifications'])->name('notifications');
});

// Instructor Dashboard Routes
Route::middleware(['auth:sanctum'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
    
    // Courses
    Route::get('/courses', [InstructorController::class, 'courses'])->name('courses');
    Route::get('/courses/create', [InstructorController::class, 'createCourse'])->name('courses.create');
    Route::get('/courses/{id}', [InstructorController::class, 'courseDetail'])->name('courses.show');
    Route::get('/courses/{id}/edit', [InstructorController::class, 'editCourse'])->name('courses.edit');
    
    // Assignments
    Route::get('/assignments', [InstructorController::class, 'assignments'])->name('assignments');
    Route::get('/assignments/create', [InstructorController::class, 'createAssignment'])->name('assignments.create');
    Route::get('/assignments/{id}/submissions', [InstructorController::class, 'submissions'])->name('assignments.submissions');
    
    // Grading
    Route::get('/grading', [InstructorController::class, 'grading'])->name('grading');
    
    // Attendance
    Route::get('/attendance', [InstructorController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/create', [InstructorController::class, 'createAttendance'])->name('attendance.create');
    
    // Students
    Route::get('/students', [InstructorController::class, 'students'])->name('students');
    
    // Announcements
    Route::get('/announcements', [InstructorController::class, 'announcements'])->name('announcements');
    Route::get('/announcements/create', [InstructorController::class, 'createAnnouncement'])->name('announcements.create');
    
    // Certificates
    Route::get('/certificates', [InstructorController::class, 'certificates'])->name('certificates');
});

// Student Dashboard Routes
Route::middleware(['auth:sanctum'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // Courses
    Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
    Route::get('/courses/{id}', [StudentController::class, 'courseDetail'])->name('courses.show');
    
    // Assignments
    Route::get('/assignments', [StudentController::class, 'assignments'])->name('assignments');
    Route::get('/assignments/{id}', [StudentController::class, 'assignmentDetail'])->name('assignments.show');
    
    // Grades
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
    
    // Attendance
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
    
    // Certificates
    Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
    
    // Profile
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    
    // Notifications
    Route::get('/notifications', [StudentController::class, 'notifications'])->name('notifications');
});
```

### Step 4: Create Controllers

Buat controller files di: `app/Http/Controllers/`

**Lokasi:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\app\Http\Controllers\`

```bash
# Buat controllers menggunakan artisan
php artisan make:controller ParentController
php artisan make:controller InstructorController
php artisan make:controller StudentController
php artisan make:controller AuthController
```

### Step 5: Verify Installation

Checklist untuk memastikan semua file sudah di tempat yang benar:

#### ✅ Layouts
- [ ] `resources/views/layouts/parent.blade.php`
- [ ] `resources/views/layouts/instructor.blade.php`
- [ ] `resources/views/layouts/student.blade.php`

#### ✅ CSS Files
- [ ] `public/css/parent/dashboard.css`
- [ ] `public/css/instructor/dashboard.css`
- [ ] `public/css/student/dashboard.css`
- [ ] `public/css/auth.css`

#### ✅ JavaScript Files
- [ ] `public/js/parent/api.js`
- [ ] `public/js/instructor/api.js`
- [ ] `public/js/student/api.js`
- [ ] `public/js/auth.js`

#### ✅ Parent Views
- [ ] `resources/views/parent/dashboard.blade.php`
- [ ] `resources/views/parent/students/index.blade.php`
- [ ] `resources/views/parent/students/show.blade.php`
- [ ] `resources/views/parent/grades.blade.php`
- [ ] `resources/views/parent/attendance.blade.php`
- [ ] `resources/views/parent/announcements.blade.php`
- [ ] `resources/views/parent/certificates.blade.php`
- [ ] `resources/views/parent/notifications.blade.php`

#### ✅ Instructor Views
- [ ] `resources/views/instructor/dashboard.blade.php`
- [ ] `resources/views/instructor/courses/index.blade.php`
- [ ] `resources/views/instructor/courses/create.blade.php`
- [ ] `resources/views/instructor/courses/edit.blade.php`
- [ ] `resources/views/instructor/courses/show.blade.php`
- [ ] `resources/views/instructor/assignments/index.blade.php`
- [ ] `resources/views/instructor/assignments/create.blade.php`
- [ ] `resources/views/instructor/assignments/submissions.blade.php`
- [ ] `resources/views/instructor/grading/index.blade.php`
- [ ] `resources/views/instructor/attendance/index.blade.php`
- [ ] `resources/views/instructor/attendance/create.blade.php`
- [ ] `resources/views/instructor/students/index.blade.php`
- [ ] `resources/views/instructor/announcements/index.blade.php`
- [ ] `resources/views/instructor/announcements/create.blade.php`
- [ ] `resources/views/instructor/certificates/index.blade.php`

#### ✅ Student Views
- [ ] `resources/views/student/dashboard.blade.php`
- [ ] `resources/views/student/courses/index.blade.php`
- [ ] `resources/views/student/courses/show.blade.php`
- [ ] `resources/views/student/assignments/index.blade.php`
- [ ] `resources/views/student/assignments/show.blade.php`
- [ ] `resources/views/student/grades/index.blade.php`
- [ ] `resources/views/student/attendance/index.blade.php`
- [ ] `resources/views/student/certificates/index.blade.php`
- [ ] `resources/views/student/profile.blade.php`
- [ ] `resources/views/student/notifications.blade.php`

#### ✅ Auth Views
- [ ] `resources/views/auth/login.blade.php`
- [ ] `resources/views/auth/register.blade.php`

#### ✅ Routes & Controllers
- [ ] `routes/web.php` updated
- [ ] `app/Http/Controllers/ParentController.php` created
- [ ] `app/Http/Controllers/InstructorController.php` created
- [ ] `app/Http/Controllers/StudentController.php` created
- [ ] `app/Http/Controllers/AuthController.php` created

---

## 📝 Quick Commands Cheatsheet

```bash
# Masuk ke project root
cd C:\Users\hansg\OneDrive\Desktop\lmsRPL3

# Buat semua folder sekaligus (Windows PowerShell)
New-Item -Path "resources/views/layouts" -ItemType Directory -Force
New-Item -Path "resources/views/auth" -ItemType Directory -Force
New-Item -Path "resources/views/parent/students" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/courses" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/grading" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/students" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/announcements" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/certificates" -ItemType Directory -Force
New-Item -Path "resources/views/student/courses" -ItemType Directory -Force
New-Item -Path "resources/views/student/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/student/grades" -ItemType Directory -Force
New-Item -Path "resources/views/student/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/student/certificates" -ItemType Directory -Force
New-Item -Path "public/css/parent" -ItemType Directory -Force
New-Item -Path "public/css/instructor" -ItemType Directory -Force
New-Item -Path "public/css/student" -ItemType Directory -Force
New-Item -Path "public/js/parent" -ItemType Directory -Force
New-Item -Path "public/js/instructor" -ItemType Directory -Force
New-Item -Path "public/js/student" -ItemType Directory -Force

# Buat controllers
php artisan make:controller ParentController
php artisan make:controller InstructorController
php artisan make:controller StudentController
php artisan make:controller AuthController

# Clear cache Laravel
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run Laravel server
php artisan serve
```

---

## 🎯 Implementation Order (Recommended)

1. **Setup folder structure** (5 minutes)
2. **Create layout templates** (15 minutes)
   - parent.blade.php
   - instructor.blade.php
   - student.blade.php
3. **Create CSS files** (10 minutes)
4. **Create API helpers** (15 minutes)
5. **Setup routes** (10 minutes)
6. **Create controllers** (10 minutes)
7. **Implement Parent Dashboard** (30 minutes)
8. **Implement Instructor Dashboard** (45 minutes)
9. **Implement Student Dashboard** (45 minutes)
10. **Test each dashboard** (30 minutes)

**Total Time: ~3.5 hours**

---

## 🔥 Common Issues & Solutions

### Issue 1: "Class not found" error
**Solution:** 
```bash
composer dump-autoload
php artisan cache:clear
```

### Issue 2: "View not found" error
**Solution:** Verify path exactly matches. Laravel is case-sensitive!
```
✅ resources/views/parent/dashboard.blade.php
❌ resources/views/Parent/dashboard.blade.php
```

### Issue 3: Assets (CSS/JS) not loading
**Solution:** 
1. Check if files exist in `public/` folder
2. Use correct path: `{{ asset('css/parent/dashboard.css') }}`
3. Clear browser cache (Ctrl + F5)

### Issue 4: Routes not working
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
php artisan route:list  # Verify routes exist
```

---

## 📞 Need Help?

Jika ada error atau path tidak jelas:

1. **Verify exact path** dengan command:
   ```bash
   # Windows
   dir resources\views\parent
   
   # Check if file exists
   Test-Path "resources/views/parent/dashboard.blade.php"
   ```

2. **Check file content** untuk memastikan tidak kosong:
   ```bash
   # Windows PowerShell
   Get-Content "resources/views/parent/dashboard.blade.php" | Measure-Object -Line
   ```

3. **Compare with guide** - buka interactive HTML guide di browser untuk referensi

---

## ✅ Final Checklist

Setelah semua file dibuat, test dengan:

1. [ ] Jalankan `php artisan serve`
2. [ ] Buka browser: `http://localhost:8000`
3. [ ] Test login page: `http://localhost:8000/login`
4. [ ] Test parent dashboard: `http://localhost:8000/parent/dashboard`
5. [ ] Test instructor dashboard: `http://localhost:8000/instructor/dashboard`
6. [ ] Test student dashboard: `http://localhost:8000/student/dashboard`
7. [ ] Check browser console untuk error JavaScript
8. [ ] Check Laravel log: `storage/logs/laravel.log`
9. [ ] Test API calls dengan token di localStorage
10. [ ] Verify CSS loading dengan Inspect Element

---

**🎉 Selamat! Frontend LMS sudah siap digunakan!**

Untuk detail implementasi, lihat guide files:
- `PARENT-DASHBOARD-GUIDE.md`
- `INSTRUCTOR-DASHBOARD-GUIDE.md`
- `STUDENT-DASHBOARD-GUIDE.md`

Atau buka interactive HTML guides di browser untuk navigasi yang lebih mudah!