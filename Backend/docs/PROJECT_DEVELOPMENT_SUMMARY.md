# 📚 SmartDev Academic LMS - Project Development Summary

**Project Name:** SmartDev Academic Learning Management System  
**Repository:** SmartDev-Academic_RPL_Project  
**Branch:** hans (development)  
**Last Updated:** November 5, 2025  
**Status:** ✅ Backend Restructuring Complete | ⏭️ Ready for Testing Phase

---

## 📑 Table of Contents

1. [Project Overview](#project-overview)
2. [Initial Architecture](#initial-architecture)
3. [Problem Identification](#problem-identification)
4. [Database Restructuring](#database-restructuring)
5. [Backend Implementation](#backend-implementation)
6. [Certificate System Design](#certificate-system-design)
7. [Current Progress](#current-progress)
8. [Next Steps](#next-steps)
9. [Technical Documentation](#technical-documentation)

---

## 🎯 Project Overview

### **Objective**
Membangun Learning Management System (LMS) yang komprehensif untuk institusi pendidikan dengan fitur-fitur:
- Multi-role user management (Admin, Instructor, Student, Parent)
- Course & module management
- Assignment submission & grading system
- Certificate generation untuk course completion
- Parent monitoring system

### **Technology Stack**

#### **Backend:**
- **Framework:** Laravel 12.x
- **Database:** MySQL
- **Authentication:** Laravel Sanctum (API Token)
- **API Architecture:** RESTful API

#### **Key Features:**
- Role-based access control (RBAC)
- File upload & management
- Grading system dengan grade components
- Real-time enrollment tracking
- Certificate generation & verification

---

## 🏗️ Initial Architecture

### **Original Database Structure**

#### **Single Users Table Approach:**
```sql
users:
├─ id
├─ name, email, password
├─ role (ENUM: admin, instructor, student, parent)
├─ student_number       ← NULL untuk non-student
├─ instructor_code      ← NULL untuk non-instructor
├─ parent_relationship  ← NULL untuk non-parent
├─ enrollment_year
├─ specialization
└─ ... (banyak field yang tidak terpakai)
```

#### **Foreign Key Structure Lama:**
```
users (single table)
  ├─> courses.instructor_id
  ├─> enrollments.user_id
  └─> submissions.user_id
```

#### **Problems dengan Approach Ini:**
❌ Banyak kolom NULL (wasted space)  
❌ Sulit maintain - semua role dalam 1 tabel  
❌ Query lambat - harus filter by role  
❌ Tidak scalable - sulit tambah field per role  
❌ Data integrity rendah - FK tidak jelas  
❌ Confusing untuk developer baru  

---

## 🔍 Problem Identification

### **Major Issues Found:**

#### **1. Database Design Issues**
- **Masalah:** Single table untuk semua role
- **Impact:** 
  - 70% kolom NULL untuk setiap row
  - Query performance menurun
  - Sulit menambah field spesifik per role

#### **2. Foreign Key Ambiguity**
- **Masalah:** `instructor_id` merujuk ke `users.id`, padahal user bisa student/parent juga
- **Impact:**
  - Data integrity rendah
  - Mudah terjadi kesalahan assignment
  - Sulit tracking relasi

#### **3. Business Logic Complexity**
- **Masalah:** Harus cek `role` di setiap query
- **Impact:**
  - Code repetition tinggi
  - Banyak if-else berdasarkan role
  - Sulit maintain policies

#### **4. Scalability Concerns**
- **Masalah:** Tidak bisa scale per role
- **Impact:**
  - Sulit tambah fitur khusus student
  - Tidak bisa optimize query per role
  - Coupling tinggi antar role

---

## 🔧 Database Restructuring

### **Solution: Separate Tables per Role**

#### **New Architecture Design:**

```
users (Authentication Only)
  ├─ 1:1 → students (Student Profile)
  ├─ 1:1 → instructors (Instructor Profile)
  └─ 1:1 → parents (Parent Profile)
```

### **Detailed Table Structure:**

#### **1. `users` Table** (Core Authentication)
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'instructor', 'student', 'parent') NOT NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

Purpose: Hanya untuk autentikasi dan data dasar
Note: Admin tidak butuh profil extended
```

#### **2. `students` Table** (Student Extended Profile)
```sql
CREATE TABLE students (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    parent_id BIGINT NULL,
    student_number VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female') NULL,
    address TEXT NULL,
    emergency_contact_name VARCHAR(255) NULL,
    emergency_contact_phone VARCHAR(255) NULL,
    enrollment_year YEAR NULL,
    current_grade VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'graduated', 'dropped') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL
);

Purpose: Data mahasiswa lengkap
Key Features: Student number, academic info, emergency contacts
```

#### **3. `instructors` Table** (Instructor Extended Profile)
```sql
CREATE TABLE instructors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    instructor_code VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(255) NULL,
    specialization VARCHAR(255) NULL,
    education_level VARCHAR(255) NULL,
    experience_years INT NULL,
    bio TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Purpose: Data dosen/pengajar lengkap
Key Features: Instructor code, specialization, experience
```

#### **4. `parents` Table** (Parent Extended Profile)
```sql
CREATE TABLE parents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NULL,
    relationship ENUM('father', 'mother', 'guardian') NOT NULL DEFAULT 'father',
    occupation VARCHAR(255) NULL,
    address TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Purpose: Data orang tua/wali
Key Features: Relationship type, occupation, contact info
```

### **Foreign Key Migration:**

#### **Before:**
```
courses.instructor_id → users.id
enrollments.user_id → users.id
submissions.user_id → users.id
```

#### **After:**
```
courses.instructor_id → instructors.id
enrollments.student_id → students.id
submissions.student_id → students.id
students.parent_id → parents.id
```

### **Migration Files Created:**

1. **`2025_11_02_070456_create_students_table.php`**
   - Membuat tabel students dengan FK ke users & parents
   - Indexes: user_id, student_number, parent_id

2. **`2025_11_02_070719_create_instructors_table.php`**
   - Membuat tabel instructors dengan FK ke users
   - Indexes: user_id, instructor_code

3. **`2025_11_02_070836_create_parents_table.php`**
   - Drop old parents table (jika ada)
   - Create new parents table dengan FK ke users
   - Indexes: user_id

4. **`2025_11_02_071941_modify_courses_table_change_instructor_fk.php`**
   - Drop old FK courses.instructor_id → users
   - Add new FK courses.instructor_id → instructors
   - Set instructor_id nullable untuk existing data

5. **`2025_11_02_072022_modify_enrollments_table_change_to_student_id.php`**
   - Drop user_id column
   - Add student_id column dengan FK ke students
   - Truncate existing data (restructuring)

6. **`2025_11_02_072246_modify_submissions_table_change_to_student_id.php`**
   - Drop user_id column
   - Add student_id column dengan FK ke students
   - Truncate existing data (restructuring)

7. **`2025_11_02_131934_add_parent_fk_to_students_table.php`**
   - Add parent_id FK ke students setelah parents table created
   - ON DELETE SET NULL

### **Migration Execution:**
```bash
✅ All migrations ran successfully
✅ No errors
✅ Foreign keys properly constrained
✅ Indexes created
```

---

## 💻 Backend Implementation

### **1. Models Created/Updated**

#### **A. New Models:**

**`Student.php`**
```php
namespace App\Models;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'student_number', 'full_name', 'email',
        'phone', 'date_of_birth', 'gender', 'address',
        'emergency_contact_name', 'emergency_contact_phone',
        'enrollment_year', 'current_grade', 'status'
    ];

    // Relations
    public function user() { return $this->belongsTo(User::class); }
    public function parentProfile() { return $this->belongsTo(ParentModel::class, 'parent_id'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function submissions() { return $this->hasMany(Submission::class); }
    public function grades() { return $this->hasMany(Grade::class); }
    public function courses() { 
        return $this->belongsToMany(Course::class, 'enrollments');
    }
}
```

**`Instructor.php`**
```php
namespace App\Models;

class Instructor extends Model
{
    protected $fillable = [
        'user_id', 'instructor_code', 'full_name', 'email', 'phone',
        'specialization', 'education_level', 'experience_years', 
        'bio', 'status'
    ];

    // Relations
    public function user() { return $this->belongsTo(User::class); }
    public function courses() { return $this->hasMany(Course::class); }
    
    // Scopes
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }
}
```

**`ParentModel.php`**
```php
namespace App\Models;

class ParentModel extends Model
{
    protected $table = 'parents';
    
    protected $fillable = [
        'user_id', 'full_name', 'email', 'phone',
        'relationship', 'occupation', 'address'
    ];

    // Relations
    public function user() { return $this->belongsTo(User::class); }
    public function students() { return $this->hasMany(Student::class, 'parent_id'); }
    
    // Scopes
    public function scopeActiveStudents($query) {
        return $query->whereHas('students', function($q) {
            $q->where('status', 'active');
        });
    }
}
```

#### **B. Updated Models:**

**`User.php`**
```php
// Added Relations
public function student() { 
    return $this->hasOne(Student::class); 
}

public function instructor() { 
    return $this->hasOne(Instructor::class); 
}

public function parentProfile() { 
    return $this->hasOne(ParentModel::class); 
}

// Helper Methods
public function isAdmin() { return $this->role === 'admin'; }
public function isInstructor() { return $this->role === 'instructor'; }
public function isStudent() { return $this->role === 'student'; }
public function isParent() { return $this->role === 'parent'; }
```

**`Course.php`**
```php
// Updated Relation
public function instructor() {
    return $this->belongsTo(Instructor::class); // Changed from User::class
}
```

**`Enrollment.php`**
```php
// Updated Relation
public function student() {
    return $this->belongsTo(Student::class); // Changed from User::class
}
```

**`Submission.php`**
```php
// Updated Relation
public function student() {
    return $this->belongsTo(Student::class); // Changed from User::class
}
```

### **2. Controllers Created/Updated**

#### **A. New Controllers:**

**`StudentController.php`**
- **Methods:** index, store, show, update, destroy
- **Custom Methods:**
  - `enrollments($student)` - Get all student enrollments
  - `submissions($student)` - Get all student submissions
- **Features:**
  - Search & filter (name, student_number, status)
  - Create student dengan user account
  - Eager loading relations

**`InstructorController.php`**
- **Methods:** index, store, show, update, destroy
- **Custom Methods:**
  - `courses($instructor)` - Get all instructor courses
  - `activeCourses($instructor)` - Get active courses only
- **Features:**
  - Search & filter (name, instructor_code, specialization)
  - Create instructor dengan user account
  - Status management

**`ParentController.php`** (Completely Rewritten)
- **Methods:** index, store, show, update, destroy
- **Custom Methods:**
  - `students($parent)` - Get all children
  - `activeStudents($parent)` - Get active children only
- **Features:**
  - Create parent dengan user account
  - Link to students (children)

#### **B. Updated Controllers:**

**`AuthController.php`**
```php
// Updated register() method
- Now creates role-specific profile after user creation
- For student: creates students record
- For instructor: creates instructors record
- For parent: creates parents record

// Updated login() method
- Loads role-specific profile in response
- Returns user + profile data
```

**`CourseController.php`**
```php
// Validation Changes:
'instructor_id' => 'required|exists:instructors,id' // Changed from users

// Eager Loading Update:
$course->load('instructor.user', 'enrollments.student.user')
```

**`EnrollmentController.php`**
```php
// Validation Changes:
'student_id' => 'required|exists:students,id' // Changed from users

// Eager Loading Update:
$enrollments = Enrollment::with(['student.user', 'course.instructor'])->get()
```

**`SubmissionController.php`**
```php
// Major Changes:
- Auto-assign student_id from auth user's student profile
- Validation: student_id no longer required in request
- Authorization: Check if user has student profile

public function store(Request $request) {
    $user = $request->user();
    $student = $user->student;
    
    if (!$student) {
        return response()->json(['message' => 'Must be a student'], 403);
    }
    
    $validated['student_id'] = $student->id;
    // ...
}
```

**`GradeController.php`**
```php
// Validation Changes:
'student_id' => 'required|exists:students,id' // Changed from users

// Eager Loading Update:
$grade->load(['student:id,full_name,student_number', 'gradeComponent', 'grader'])
```

### **3. Services Updated**

**`GradingService.php`**
```php
// getCourseGradesSummary() Update:
$enrolledStudents = DB::table('enrollments')
    ->join('students', 'enrollments.student_id', '=', 'students.id')
    ->join('users', 'students.user_id', '=', 'users.id')
    ->where('enrollments.course_id', $courseId)
    ->select('students.id', 'students.full_name', 'students.email', 'students.student_number')
    ->get();
```

### **4. Policies Updated**

All 7 policies updated untuk authorization dengan struktur baru:

**`CoursePolicy.php`**
```php
// Before:
$course->instructor_id === $user->id

// After:
$user->instructor && $course->instructor_id === $user->instructor->id
```

**`SubmissionPolicy.php`**
```php
// Before:
$user->id === $submission->student_id

// After:
$user->student && $user->student->id === $submission->student_id
```

**`EnrollmentPolicy.php`**
```php
// Before:
$user->id === $enrollment->student_id

// After:
$user->student && $user->student->id === $enrollment->student_id
```

**`MaterialPolicy.php`**
```php
// Instructor Check Update:
$user->instructor && $material->courseModule->course->instructor_id === $user->instructor->id

// Student Enrollment Check Update:
if ($user->role === 'student' && $user->student) {
    $enrolledCourses = $user->student->enrollments()->pluck('course_id');
}

// Parent Check Update:
if ($user->role === 'parent' && $user->parentProfile) {
    $enrolledCourses = $user->parentProfile->students()
        ->with('enrollments')
        ->get()
        ->pluck('enrollments.*.course_id')
        ->flatten();
}
```

**`CourseModulePolicy.php`** - Similar updates seperti MaterialPolicy

**`GradePolicy.php`**
```php
public function view(User $user, Grade $grade): bool {
    return match($user->role) {
        'admin' => true,
        'instructor' => $user->instructor && $grade->gradeComponent->course->instructor_id === $user->instructor->id,
        'student' => $user->student && $grade->student_id === $user->student->id,
        'parent' => $user->parentProfile && $grade->student->parent_id === $user->parentProfile->id,
        default => false,
    };
}
```

### **5. Routes Updated**

**Total: 94 API Routes** registered dengan prefix `api/v1` dan middleware `auth:sanctum`

#### **New Resource Routes:**
```php
// Students Management
Route::apiResource('students', StudentController::class);
Route::get('students/{student}/enrollments', [StudentController::class, 'enrollments']);
Route::get('students/{student}/submissions', [StudentController::class, 'submissions']);

// Instructors Management
Route::apiResource('instructors', InstructorController::class);
Route::get('instructors/{instructor}/courses', [InstructorController::class, 'courses']);
Route::get('instructors/{instructor}/active-courses', [InstructorController::class, 'activeCourses']);

// Parents Management
Route::apiResource('parents', ParentController::class);
Route::get('parents/{parent}/students', [ParentController::class, 'students']);
Route::get('parents/{parent}/active-students', [ParentController::class, 'activeStudents']);
```

#### **Updated Existing Routes:**
- All existing routes remain functional
- Course, Enrollment, Submission, Grade routes unchanged
- User routes now specifically for admin management

---

## 🎓 Certificate System Design

### **Overview**
Certificate system dirancang untuk auto-generate sertifikat ketika student menyelesaikan course dengan passing grade.

### **Database Schema for Certificates:**

#### **1. `certificate_templates` Table**
```sql
CREATE TABLE certificate_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    course_id BIGINT NULL,  -- NULL = global template
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    template_type ENUM('default', 'course_specific') NOT NULL,
    background_image VARCHAR(255) NULL,
    layout_config JSON NULL,  -- Design configuration
    signature_image VARCHAR(255) NULL,
    signature_name VARCHAR(255) NULL,
    signature_title VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

Purpose: Store reusable certificate templates
Features:
- Global or course-specific templates
- Customizable background & signature
- JSON layout configuration for flexibility
```

#### **2. `certificates` Table**
```sql
CREATE TABLE certificates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id BIGINT UNIQUE NOT NULL,  -- One cert per enrollment
    certificate_number VARCHAR(255) UNIQUE NOT NULL,
    template_id BIGINT NULL,
    issued_date DATE NOT NULL,
    completion_date DATE NOT NULL,
    final_grade DECIMAL(5,2) NOT NULL,
    grade_letter ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    file_path VARCHAR(255) NULL,  -- PDF path
    status ENUM('active', 'revoked') NOT NULL DEFAULT 'active',
    revoked_at TIMESTAMP NULL,
    revoked_reason TEXT NULL,
    issued_by BIGINT NOT NULL,  -- User who issued
    qr_code TEXT NULL,  -- QR code for verification
    verification_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES certificate_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE RESTRICT
);

Purpose: Store issued certificates
Features:
- Unique certificate number for verification
- QR code integration
- Revocation support
- PDF storage
```

#### **3. `certificate_verifications` Table**
```sql
CREATE TABLE certificate_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    certificate_id BIGINT NOT NULL,
    verified_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verifier_ip VARCHAR(45) NULL,
    verifier_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
);

Purpose: Audit trail for certificate verifications
Features:
- Track who verified when
- IP & user agent logging
- Analytics support
```

### **Enrollment Table Enhancement:**
```sql
ALTER TABLE enrollments ADD COLUMN:
- status ENUM('active', 'completed', 'dropped') NOT NULL DEFAULT 'active'
- enrolled_date TIMESTAMP NULL
- completion_date TIMESTAMP NULL
- is_eligible_certificate BOOLEAN DEFAULT false
```

### **Courses Table Enhancement:**
```sql
ALTER TABLE courses ADD COLUMN:
- passing_grade DECIMAL(5,2) DEFAULT 70.00
- duration_hours INT NULL
```

### **Certificate Generation Flow:**

```
┌─────────────────────────────────────────────────┐
│ 1. STUDENT COMPLETES COURSE                     │
│    - All assignments submitted                   │
│    - All grades recorded                         │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ 2. SYSTEM CHECKS ELIGIBILITY                    │
│    ✓ Final grade >= passing_grade               │
│    ✓ All grade components complete (100% weight)│
│    ✓ No pending submissions                     │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ 3. UPDATE ENROLLMENT STATUS                     │
│    - status = 'completed'                       │
│    - completion_date = now()                    │
│    - is_eligible_certificate = true             │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ 4. GENERATE CERTIFICATE                         │
│    - Create certificate record                   │
│    - Generate unique certificate_number         │
│    - Calculate final_grade & grade_letter       │
│    - Select template (course or default)        │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ 5. GENERATE PDF                                 │
│    - Populate template with student data        │
│    - Add certificate number & QR code           │
│    - Generate PDF file                          │
│    - Save to storage (storage/certificates/)    │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ 6. CERTIFICATE READY                            │
│    - Student can view online                    │
│    - Student can download PDF                   │
│    - Anyone can verify using certificate_number │
│    - QR code scans to verification URL          │
└─────────────────────────────────────────────────┘
```

### **Certificate Verification Flow:**

```
User scans QR code / enters certificate number
    ↓
System checks certificates table
    ↓
    ├─> Found & Active
    │   └─> Display:
    │       - Student name
    │       - Course name
    │       - Completion date
    │       - Grade
    │       - Status: VALID ✓
    │       - Log verification
    │
    ├─> Found & Revoked
    │   └─> Display:
    │       - Status: REVOKED ✗
    │       - Revoked date
    │       - Reason
    │
    └─> Not Found
        └─> Display:
            - Status: INVALID ✗
            - Certificate not found
```

### **Planned API Endpoints for Certificates:**

```php
// Certificate Management
GET    /api/v1/certificates                    # List all certificates
POST   /api/v1/certificates/generate/{enrollment}  # Generate certificate
GET    /api/v1/certificates/{id}               # View certificate details
GET    /api/v1/certificates/{id}/download      # Download PDF
DELETE /api/v1/certificates/{id}/revoke        # Revoke certificate

// Public Verification
GET    /api/v1/certificates/verify/{number}    # Verify certificate by number
POST   /api/v1/certificates/verify             # Verify by number (POST)

// Template Management (Admin only)
GET    /api/v1/certificate-templates           # List templates
POST   /api/v1/certificate-templates           # Create template
GET    /api/v1/certificate-templates/{id}      # View template
PUT    /api/v1/certificate-templates/{id}      # Update template
DELETE /api/v1/certificate-templates/{id}      # Delete template
```

### **Certificate Models (To be Created):**

**`Certificate.php`**
```php
class Certificate extends Model
{
    protected $fillable = [
        'enrollment_id', 'certificate_number', 'template_id',
        'issued_date', 'completion_date', 'final_grade', 'grade_letter',
        'file_path', 'status', 'issued_by', 'qr_code', 'verification_url'
    ];

    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function template() { return $this->belongsTo(CertificateTemplate::class); }
    public function issuer() { return $this->belongsTo(User::class, 'issued_by'); }
    public function verifications() { return $this->hasMany(CertificateVerification::class); }
    
    // Auto-generate certificate number
    public static function boot() {
        parent::boot();
        static::creating(function ($certificate) {
            $certificate->certificate_number = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(10));
        });
    }
}
```

**`CertificateTemplate.php`**
```php
class CertificateTemplate extends Model
{
    protected $fillable = [
        'course_id', 'name', 'description', 'template_type',
        'background_image', 'layout_config', 'signature_image',
        'signature_name', 'signature_title', 'is_active'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'is_active' => 'boolean'
    ];

    public function course() { return $this->belongsTo(Course::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
}
```

### **Recommended Libraries for PDF Generation:**

1. **Laravel DomPDF** (Recommended)
   ```bash
   composer require barryvdh/laravel-dompdf
   ```
   - Easy to use
   - Good for simple templates
   - Pure PHP, no external dependencies

2. **Laravel Snappy / wkhtmltopdf**
   ```bash
   composer require barryvdh/laravel-snappy
   ```
   - Better output quality
   - Supports complex CSS
   - Requires wkhtmltopdf binary

3. **QR Code Generation:**
   ```bash
   composer require simplesoftwareio/simple-qrcode
   ```
   - Generate QR codes for verification

---

## 📊 Current Progress

### **Completion Status:**

```
┌──────────────────────────────────────────────────────────┐
│ Component                    Progress    Status          │
├──────────────────────────────────────────────────────────┤
│ Database Design              ████████████ 100%  ✅ Done  │
│ Migrations (7 files)         ████████████ 100%  ✅ Done  │
│ Models (7 files)             ████████████ 100%  ✅ Done  │
│ Controllers (10 files)       ████████████ 100%  ✅ Done  │
│ Services (1 file)            ████████████ 100%  ✅ Done  │
│ Policies (7 files)           ████████████ 100%  ✅ Done  │
│ Routes (94 total)            ████████████ 100%  ✅ Done  │
│ Certificate Design           ████████████ 100%  ✅ Done  │
├──────────────────────────────────────────────────────────┤
│ Backend Restructuring        ████████████ 100%  ✅       │
├──────────────────────────────────────────────────────────┤
│ Database Seeders             ░░░░░░░░░░░░   0%  ⏭️ Next  │
│ API Testing                  ░░░░░░░░░░░░   0%  ⏭️ Next  │
│ Certificate Implementation   ░░░░░░░░░░░░   0%  🔜 Soon  │
│ API Documentation            ░░░░░░░░░░░░   0%  🔜 Soon  │
│ Frontend Integration         ░░░░░░░░░░░░   0%  🔜 Soon  │
│ Deployment                   ░░░░░░░░░░░░   0%  🔜 Soon  │
├──────────────────────────────────────────────────────────┤
│ OVERALL PROGRESS             ████████░░░░  65%           │
└──────────────────────────────────────────────────────────┘
```

### **Files Modified Summary:**

#### **Migrations: 7 files**
- ✅ All created and executed successfully
- ✅ No rollback needed
- ✅ Foreign keys properly constrained

#### **Models: 7 files**
- ✅ 3 New: Student, Instructor, ParentModel
- ✅ 4 Updated: User, Course, Enrollment, Submission
- ✅ All relations properly defined

#### **Controllers: 10 files**
- ✅ 3 New: StudentController, InstructorController, ParentController (rewritten)
- ✅ 7 Updated: AuthController, CourseController, EnrollmentController, SubmissionController, GradeController, MaterialController, CourseModuleController
- ✅ All validations updated

#### **Services: 1 file**
- ✅ GradingService - Query joins updated

#### **Policies: 7 files**
- ✅ All updated untuk authorization dengan struktur baru
- ✅ Role-specific checks implemented

#### **Routes: 1 file**
- ✅ 94 API routes registered
- ✅ All with auth:sanctum middleware
- ✅ v1 prefix applied

### **Testing Status:**

```
Database Structure:
✅ All migrations successful
✅ Foreign keys working
✅ Indexes created
✅ No constraint violations

Code Quality:
✅ No compile errors
✅ No syntax errors
✅ All routes registered
✅ Cache cleared

Functional Testing:
❌ Not yet tested
❌ No seeders yet
❌ No API tests yet
```

---

## ⏭️ Next Steps

### **Phase 1: Database Seeding & Testing** 🔴 **HIGH PRIORITY**

#### **1. Create Seeders (Est: 2-3 hours)**
```bash
# Create seeder files
php artisan make:seeder UserSeeder
php artisan make:seeder InstructorSeeder
php artisan make:seeder StudentSeeder
php artisan make:seeder ParentSeeder
php artisan make:seeder CourseSeeder
php artisan make:seeder EnrollmentSeeder
php artisan make:seeder AssignmentSeeder
php artisan make:seeder SubmissionSeeder
php artisan make:seeder GradeComponentSeeder
php artisan make:seeder GradeSeeder

# Execute seeders
php artisan db:seed
```

**Seeder Content Plan:**
- **UserSeeder:** 1 admin, 5 instructors, 20 students, 10 parents
- **InstructorSeeder:** Link to users, assign codes & specializations
- **StudentSeeder:** Link to users & parents, assign student numbers
- **ParentSeeder:** Link to users, set relationships
- **CourseSeeder:** 10-15 courses dengan instructors
- **EnrollmentSeeder:** Enroll students ke berbagai courses
- **AssignmentSeeder:** 3-5 assignments per course
- **SubmissionSeeder:** Sample submissions dari students
- **GradeComponentSeeder:** Setup grading per course (Quiz 30%, UTS 30%, UAS 40%)
- **GradeSeeder:** Assign grades untuk students

#### **2. API Testing (Est: 3-4 hours)**

**Test Cases:**

**Authentication:**
```
✓ Register as student → creates user + student profile
✓ Register as instructor → creates user + instructor profile
✓ Register as parent → creates user + parent profile
✓ Login → returns user + role-specific profile
✓ Logout → invalidates token
```

**Student Management:**
```
✓ List students (with search & filter)
✓ Create student
✓ View student details (with enrollments)
✓ Update student info
✓ Delete student
✓ Get student enrollments
✓ Get student submissions
```

**Instructor Management:**
```
✓ List instructors
✓ Create instructor
✓ View instructor details
✓ Update instructor info
✓ Delete instructor
✓ Get instructor courses
✓ Get active courses
```

**Parent Management:**
```
✓ List parents
✓ Create parent
✓ View parent details
✓ Update parent info
✓ Delete parent
✓ Get parent's children
✓ Get active children
```

**Course & Enrollment:**
```
✓ Create course dengan instructor
✓ Enroll student ke course
✓ View course dengan instructor info
✓ List enrollments dengan student info
```

**Submission & Grading:**
```
✓ Student submit assignment
✓ Instructor grade submission
✓ Calculate final grade
✓ View student grades
```

**Authorization:**
```
✓ Admin can access all
✓ Instructor can only access their courses
✓ Student can only submit their assignments
✓ Parent can only view their children's data
```

**Tools:** Postman, Insomnia, atau Thunder Client

---

### **Phase 2: Certificate Implementation** 🟡 **MEDIUM PRIORITY**

#### **1. Database Migrations (Est: 1 hour)**
```bash
php artisan make:migration create_certificate_templates_table
php artisan make:migration create_certificates_table
php artisan make:migration create_certificate_verifications_table
php artisan make:migration add_certificate_fields_to_enrollments_table
php artisan make:migration add_certificate_fields_to_courses_table
```

#### **2. Models & Relations (Est: 1 hour)**
```bash
php artisan make:model Certificate
php artisan make:model CertificateTemplate
php artisan make:model CertificateVerification
```

#### **3. Controllers & Services (Est: 3-4 hours)**
```bash
php artisan make:controller API/CertificateController --api
php artisan make:controller API/CertificateTemplateController --api
```

Create Services:
- `CertificateService.php` - Business logic
- `CertificateGeneratorService.php` - PDF generation

#### **4. Install Dependencies**
```bash
composer require barryvdh/laravel-dompdf
composer require simplesoftwareio/simple-qrcode
```

#### **5. Testing (Est: 2 hours)**
- Generate certificate untuk completed enrollment
- Download PDF certificate
- Verify certificate by number
- Revoke certificate
- QR code scanning

---

### **Phase 3: Documentation** 🟡 **MEDIUM PRIORITY**

#### **1. API Documentation (Est: 2-3 hours)**

Create comprehensive API docs:
```
docs/
├─ API_AUTHENTICATION.md
├─ API_STUDENTS.md
├─ API_INSTRUCTORS.md
├─ API_PARENTS.md
├─ API_COURSES.md
├─ API_ENROLLMENTS.md
├─ API_ASSIGNMENTS.md
├─ API_SUBMISSIONS.md
├─ API_GRADES.md
├─ API_CERTIFICATES.md
└─ API_POSTMAN_COLLECTION.json
```

#### **2. Database Documentation (Est: 1 hour)**
- Complete ERD diagram
- Table relationships
- Index documentation
- Constraint documentation

#### **3. Development Guide (Est: 1 hour)**
- Setup instructions
- Environment configuration
- Migration guide
- Seeding guide

---

### **Phase 4: Frontend Integration** 🟢 **LOW PRIORITY**

#### **If Frontend Exists:**

**Update Required:**
1. Update API calls ke new endpoints
2. Update authentication flow
3. Update forms untuk create profiles
4. Update data display dengan relasi baru
5. Add certificate view/download UI

**Estimated Time:** 4-6 hours

---

### **Phase 5: Deployment** 🟢 **LOW PRIORITY**

#### **Deployment Preparation (Est: 3-4 hours)**

**Issues:**
- ❌ InfinityFree tidak support Laravel routing
- ✅ Perlu hosting alternatif

**Recommended Hosting:**
1. **Railway** (Recommended)
   - Free tier available
   - Good for Laravel
   - Easy deployment
   - Database included

2. **Heroku**
   - Popular choice
   - Good documentation
   - PostgreSQL support

3. **Digital Ocean**
   - More control
   - Affordable
   - Requires server management

4. **Vercel/Netlify** (Serverless)
   - For API only
   - Requires modifications

**Deployment Checklist:**
```
□ Setup production environment
□ Configure production database
□ Setup file storage (S3/Cloudinary)
□ Configure CORS
□ SSL certificate
□ Backup strategy
□ Error monitoring (Sentry)
□ Performance monitoring
```

---

## 📚 Technical Documentation

### **Database Entity Relationship Diagram (ERD)**

Full ERD available in PlantUML format in:
`docs/database/ERD.puml`

### **API Architecture**

```
┌─────────────────────────────────────────────────────────┐
│                     CLIENT                              │
│            (Mobile App / Web Frontend)                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ HTTP/HTTPS
                     │ JSON
                     ↓
┌─────────────────────────────────────────────────────────┐
│                 LARAVEL API                             │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  Routes (api.php)                                 │ │
│  │  - Authentication (/login, /register, /logout)   │ │
│  │  - Students API (/v1/students)                    │ │
│  │  - Instructors API (/v1/instructors)              │ │
│  │  - Parents API (/v1/parents)                      │ │
│  │  - Courses API (/v1/courses)                      │ │
│  │  - Certificates API (/v1/certificates)            │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
│  ┌────────────────▼──────────────────────────────────┐ │
│  │  Middleware                                       │ │
│  │  - auth:sanctum (Token Authentication)           │ │
│  │  - CORS                                           │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
│  ┌────────────────▼──────────────────────────────────┐ │
│  │  Controllers                                      │ │
│  │  - StudentController                              │ │
│  │  - InstructorController                           │ │
│  │  - ParentController                               │ │
│  │  - CourseController                               │ │
│  │  - CertificateController (planned)                │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
│  ┌────────────────▼──────────────────────────────────┐ │
│  │  Policies (Authorization)                         │ │
│  │  - Check user permissions per action              │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
│  ┌────────────────▼──────────────────────────────────┐ │
│  │  Services (Business Logic)                        │ │
│  │  - GradingService                                 │ │
│  │  - CertificateService (planned)                   │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
│  ┌────────────────▼──────────────────────────────────┐ │
│  │  Models (Eloquent ORM)                            │ │
│  │  - User, Student, Instructor, Parent              │ │
│  │  - Course, Enrollment, Submission, Grade          │ │
│  └────────────────┬──────────────────────────────────┘ │
│                   │                                     │
└───────────────────┼─────────────────────────────────────┘
                    │
                    ↓
┌─────────────────────────────────────────────────────────┐
│                   DATABASE                              │
│                 MySQL / MariaDB                         │
│                                                         │
│  Tables:                                                │
│  - users, students, instructors, parents                │
│  - courses, enrollments, course_modules                 │
│  - materials, assignments, submissions                  │
│  - grades, grade_components                             │
│  - certificates, certificate_templates (planned)        │
└─────────────────────────────────────────────────────────┘
```

### **Authentication Flow**

```
┌──────────┐
│  Client  │
└────┬─────┘
     │
     │ POST /api/register
     │ {name, email, password, role}
     ↓
┌────────────────┐
│ AuthController │
│  register()    │
└────┬───────────┘
     │
     │ 1. Create user record
     ↓
┌────────────┐
│ User Model │
└────┬───────┘
     │
     │ 2. Check role & create profile
     ↓
┌─────────────────────────────────────┐
│ If role = 'student'                 │
│   → Create Student record           │
│                                     │
│ If role = 'instructor'              │
│   → Create Instructor record        │
│                                     │
│ If role = 'parent'                  │
│   → Create Parent record            │
└─────────────────┬───────────────────┘
                  │
                  │ 3. Return response
                  ↓
              ┌────────┐
              │ Client │
              │ Receives user + profile data
              └────────┘

LOGIN:
┌──────────┐
│  Client  │
└────┬─────┘
     │
     │ POST /api/login
     │ {email, password}
     ↓
┌────────────────┐
│ AuthController │
│    login()     │
└────┬───────────┘
     │
     │ 1. Verify credentials
     │ 2. Generate token
     │ 3. Load role-specific profile
     ↓
┌────────────────────────────────────┐
│ Response:                          │
│ {                                  │
│   user: {...},                     │
│   profile: {student/instructor/..},│
│   token: "..."                     │
│ }                                  │
└────────────────────────────────────┘
```

### **Request/Response Examples**

#### **Register Student:**
```json
// Request
POST /api/register
{
  "name": "John Doe",
  "email": "john@student.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "student",
  "student_number": "STD001",
  "full_name": "John Doe",
  "phone": "081234567890",
  "date_of_birth": "2000-01-01",
  "gender": "male"
}

// Response
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@student.com",
    "role": "student"
  },
  "profile": {
    "id": 1,
    "user_id": 1,
    "student_number": "STD001",
    "full_name": "John Doe",
    "email": "john@student.com",
    "status": "active"
  },
  "token": "1|abc123..."
}
```

#### **Login:**
```json
// Request
POST /api/login
{
  "email": "john@student.com",
  "password": "password123"
}

// Response
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@student.com",
    "role": "student"
  },
  "student": {
    "id": 1,
    "student_number": "STD001",
    "full_name": "John Doe",
    "current_grade": "Semester 5",
    "status": "active"
  },
  "token": "2|def456..."
}
```

#### **Create Course (Instructor):**
```json
// Request
POST /api/v1/courses
Authorization: Bearer {token}
{
  "course_code": "CS101",
  "course_name": "Introduction to Programming",
  "description": "Basic programming concepts",
  "instructor_id": 1  // instructor.id, not user.id
}

// Response
{
  "id": 1,
  "course_code": "CS101",
  "course_name": "Introduction to Programming",
  "description": "Basic programming concepts",
  "instructor_id": 1,
  "instructor": {
    "id": 1,
    "instructor_code": "INS001",
    "full_name": "Dr. Jane Smith",
    "specialization": "Computer Science"
  }
}
```

#### **Enroll Student:**
```json
// Request
POST /api/v1/enrollments
Authorization: Bearer {token}
{
  "student_id": 1,  // student.id, not user.id
  "course_id": 1
}

// Response
{
  "id": 1,
  "student_id": 1,
  "course_id": 1,
  "status": "active",
  "enrolled_date": "2025-11-05T10:00:00Z",
  "student": {
    "id": 1,
    "student_number": "STD001",
    "full_name": "John Doe"
  },
  "course": {
    "id": 1,
    "course_code": "CS101",
    "course_name": "Introduction to Programming"
  }
}
```

---

## 🎯 Key Achievements

### **Architecture Improvements:**

1. ✅ **Separation of Concerns**
   - Each role has dedicated table
   - Clear data ownership
   - Better organization

2. ✅ **Data Integrity**
   - Proper foreign key constraints
   - Clear relationships
   - Cascade deletes configured

3. ✅ **Query Performance**
   - Smaller, focused tables
   - Better indexing
   - No more role filtering in joins

4. ✅ **Maintainability**
   - Clear model structure
   - Easy to understand
   - Simple to extend

5. ✅ **Scalability**
   - Easy to add role-specific features
   - Can optimize per role
   - Flexible for future changes

### **Code Quality:**

- ✅ No compile errors
- ✅ Consistent naming conventions
- ✅ Proper use of Eloquent relations
- ✅ Authorization implemented
- ✅ Validation comprehensive

### **Documentation:**

- ✅ Complete ERD
- ✅ Migration documented
- ✅ Code well-commented
- ✅ API structure clear

---

## 📝 Important Notes

### **Breaking Changes:**

⚠️ **This is a MAJOR restructuring** - Not backward compatible with old structure

**Impact:**
- Old API calls akan error karena validation changes
- Frontend perlu update semua API calls
- Existing data tidak bisa langsung migrate (need seeder)

### **Data Migration Strategy:**

Jika ada production data lama:

1. **Backup database** terlebih dahulu
2. **Extract data** dari users table:
   ```sql
   -- Students
   SELECT * FROM users WHERE role = 'student'
   
   -- Instructors
   SELECT * FROM users WHERE role = 'instructor'
   
   -- Parents
   SELECT * FROM users WHERE role = 'parent'
   ```
3. **Create migration script** untuk populate new tables
4. **Update foreign keys** di courses, enrollments, submissions
5. **Verify data integrity**
6. **Test thoroughly**

### **Testing Checklist:**

Before going to production:
```
□ All migrations tested
□ All models tested
□ All controllers tested
□ All policies tested
□ Authorization working
□ File uploads working
□ Email notifications working (if any)
□ Performance tested
□ Security tested
□ Load tested
```

---

## 🔗 Related Documents

- `docs/database/ERD.puml` - Complete ERD diagram
- `docs/api/` - API documentation (to be created)
- `README.md` - Project README
- `CHANGELOG.md` - Change log (to be created)

---

## 👥 Team & Contacts

**Developer:** Hans Gunawan  
**Project:** SmartDev Academic LMS  
**Repository:** https://github.com/Shenhan01-sys/SmartDev-Academic_RPL_Project  
**Branch:** hans (development)

---

## 📅 Timeline

- **November 2, 2025:** Database restructuring started
- **November 3, 2025:** Migration files created
- **November 3, 2025:** All migrations executed successfully
- **November 3-4, 2025:** Backend code updated (models, controllers, policies)
- **November 5, 2025:** Certificate system designed
- **November 5, 2025:** Documentation completed

**Next Milestone:** Testing Phase (Week of Nov 6-10, 2025)

---

## 🎓 Lessons Learned

1. **Database design is critical** - Better to get it right from the start
2. **Separation of concerns** improves maintainability significantly
3. **Proper foreign keys** enforce data integrity automatically
4. **Laravel migrations** make database changes trackable
5. **Policy-based authorization** keeps authorization logic centralized

---

## 🚀 Future Enhancements

Beyond certificate system:

1. **Real-time Notifications**
   - Pusher or WebSocket integration
   - Live grade updates
   - Assignment reminders

2. **Analytics Dashboard**
   - Student performance metrics
   - Instructor statistics
   - Course completion rates

3. **Discussion Forum**
   - Per-course forums
   - Q&A system
   - Peer interaction

4. **Video Conferencing**
   - Integrate Zoom/Google Meet
   - Virtual classrooms
   - Recorded sessions

5. **Mobile Application**
   - React Native / Flutter
   - Offline capability
   - Push notifications

6. **Advanced Reporting**
   - PDF reports
   - Excel exports
   - Custom report builder

---

**Document Version:** 1.0  
**Last Updated:** November 5, 2025  
**Status:** ✅ Complete & Up-to-date

---

*This document is a comprehensive summary of the SmartDev Academic LMS project development. For technical details, refer to the code and ERD documentation.*
