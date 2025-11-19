# 📚 Frontend Guiding Documentation

Dokumentasi lengkap untuk pengembangan frontend SmartDev LMS dengan integrasi Ngrok.

---

## 📋 Daftar File

### 🎯 Getting Started

#### **GETTING-STARTED.md** 🚀⭐
**MULAI DARI SINI!** Panduan singkat 5 langkah untuk memulai implementasi.

**Isi:**
- Quick overview (3 dashboard dalam 3.5 jam)
- Prerequisites checklist
- 5-step quick start (10 menit setup)
- Dimana meletakkan file (dengan contoh)
- Implementation order (Parent → Student → Instructor)
- Workflow untuk setiap dashboard
- Common issues & quick fix
- Tips & best practices
- Final checklist sebelum mulai coding

**Kapan menggunakan:** **PERTAMA KALI** sebelum mulai coding apapun! File ini akan guide kamu step-by-step dari 0 sampai jalan.

---

### 🚀 Installation & Path Guides

#### 0. **INSTALLATION-PATH-GUIDE.md** 📁⭐
**WAJIB DIBACA PERTAMA!** Panduan lengkap lokasi file dan instalasi.

**Isi:**
- Project root structure
- Exact file paths untuk semua dashboard (Parent, Instructor, Student)
- Folder creation commands (Windows & Linux)
- Step-by-step installation guide
- Verification checklist
- Common path mistakes & troubleshooting
- Implementation order (recommended)

**Kapan menggunakan:** PERTAMA KALI sebelum implementasi, untuk tahu dimana meletakkan setiap file.

#### 0b. **FILE-PATHS-SUMMARY.md** 📁🚀
Quick reference untuk semua file paths.

**Isi:**
- Quick list semua file paths
- Folder creation commands (copy-paste ready)
- File count summary
- Implementation phase map
- Where to copy code from (guide reference)
- Quick verification checklist

**Kapan menggunakan:** Referensi cepat untuk cek path file atau saat buat folder baru.

#### 0c. **WHERE-TO-PUT-FILES.md** 📁💡
Panduan visual sederhana dimana meletakkan setiap file.

**Isi:**
- Table format untuk semua file paths
- Parent Dashboard (11 files)
- Instructor Dashboard (17 files)
- Student Dashboard (13 files)
- Authentication (4 files)
- Copy-paste commands (Windows & Linux)
- Verification commands
- Common mistakes & troubleshooting
- Pro tips & workflow

**Kapan menggunakan:** Referensi super cepat saat mau copy path atau bingung taruh file dimana.

#### 0d. **file-paths-visual.html** 📁🌐
Interactive HTML guide dengan visual untuk file paths.

**Isi:**
- Tabbed interface (Parent, Instructor, Student, Auth, Commands)
- Statistics untuk setiap dashboard
- Copy button untuk setiap file path
- Visual file structure
- One-click copy commands untuk setup
- Verification commands
- Keyboard shortcuts (Ctrl+1-5 untuk switch tabs)

**Kapan menggunakan:** Buka di browser untuk navigasi visual yang mudah dan copy path dengan 1 klik!

---

### Parent Dashboard

#### 1. **PARENT-DASHBOARD-GUIDE.md** ⭐
File panduan lengkap dan komprehensif untuk membuat Parent Dashboard.

**Isi:**
- Overview dan tech stack
- Setup & configuration lengkap
- API integration dengan Ngrok
- Implementasi semua halaman:
  - Dashboard (statistics, charts, recent activities)
  - Students List (search, filter, sorting)
  - Student Detail (tabs: courses, grades, submissions)
  - Grades View (per student, per course)
  - Attendance Tracking
  - Announcements
  - Notifications
  - Certificates
- Base layout template
- API helper JavaScript
- Best practices

**Kapan menggunakan:** Untuk implementasi lengkap dari awal sampai selesai.

#### 2. **PARENT-DASHBOARD-QUICK-REFERENCE.md** 🚀
Quick reference guide untuk akses cepat ke kode yang sering digunakan.

**Isi:**
- Ngrok configuration
- API endpoints cheat sheet
- Code snippets untuk setiap page
- Common patterns (loading, error handling, charts)
- CDN links

**Kapan menggunakan:** Ketika butuh referensi cepat atau copy-paste code snippets.

#### 3. **ADDITIONAL-PAGES-IMPLEMENTATION.md** 📄
Implementasi lengkap untuk halaman tambahan Parent Dashboard.

**Isi:**
- Attendance Page (complete)
- Announcements Page (complete)
- Notifications Page (complete)
- Certificates Page (complete)

**Kapan menggunakan:** Untuk implementasi 4 halaman tambahan yang detail.

#### 4. **parent-dashboard-complete.html** 🌐
File HTML interaktif dengan navigation tabs untuk semua sections.

**Isi:**
- Interactive HTML guide
- Tabbed navigation
- Copy-paste ready code blocks
- Responsive design
- All-in-one reference

**Kapan menggunakan:** Untuk membuka di browser dan navigasi interaktif.

---

### Instructor Dashboard

#### 5. **INSTRUCTOR-DASHBOARD-GUIDE.md** ⭐
File panduan lengkap dan komprehensif untuk membuat Instructor Dashboard.

**Isi:**
- Overview dan tech stack
- Setup & configuration lengkap
- API integration dengan Ngrok
- Implementasi semua halaman:
  - Dashboard (statistics, courses overview)
  - My Courses (CRUD courses)
  - Course Detail (manage course)
  - Assignments (create, manage assignments)
  - Submissions (review, grade submissions)
  - Grading (input grades, grade components)
  - Attendance (create sessions, mark attendance)
  - Students (manage enrolled students)
  - Announcements (create, publish announcements)
  - Certificates (generate certificates)
- Base layout template
- API helper JavaScript
- Best practices

**Kapan menggunakan:** Untuk implementasi lengkap instructor dashboard dari awal.

#### 6. **INSTRUCTOR-DASHBOARD-QUICK-REFERENCE.md** 🚀
Quick reference guide untuk instructor dashboard.

**Isi:**
- Ngrok configuration
- API endpoints cheat sheet
- Code snippets untuk setiap page
- Common patterns (CRUD operations, grading, attendance)
- CDN links

**Kapan menggunakan:** Ketika butuh referensi cepat untuk instructor features.

#### 7. **instructor-dashboard-complete.html** 🌐
File HTML interaktif dengan navigation tabs untuk semua instructor sections.

**Isi:**
- Interactive HTML guide untuk instructor
- Tabbed navigation (12 sections)
- Copy-paste ready code blocks
- Responsive design
- Complete implementation examples

**Kapan menggunakan:** Untuk membuka di browser dan navigasi interaktif instructor dashboard.

---

### Student Dashboard

#### 8. **STUDENT-DASHBOARD-GUIDE.md** ⭐
File panduan lengkap dan komprehensif untuk membuat Student Dashboard.

**Isi:**
- Overview dan tech stack
- Setup & configuration lengkap
- API integration dengan Ngrok
- Implementasi semua halaman:
  - Dashboard (statistics, overview)
  - My Courses (view enrolled courses)
  - Course Detail (materials, assignments, grades)
  - Assignments (view, submit assignments)
  - My Grades (grade tracking, statistics)
  - My Attendance (attendance records, summary)
  - My Certificates (view, download certificates)
  - Profile (update profile, change password)
- Base layout template
- API helper JavaScript
- File upload handling
- Best practices

**Kapan menggunakan:** Untuk implementasi lengkap student dashboard dari awal.

#### 9. **STUDENT-DASHBOARD-QUICK-REFERENCE.md** 🚀
Quick reference guide untuk student dashboard.

**Isi:**
- Ngrok configuration
- API endpoints cheat sheet
- Code snippets untuk setiap page
- Common patterns (course viewing, assignment submission, grade tracking)
- Helper functions
- UI components

**Kapan menggunakan:** Ketika butuh referensi cepat untuk student features.

#### 10. **student-dashboard-complete.html** 🌐
File HTML interaktif dengan navigation tabs untuk semua student sections.

**Isi:**
- Interactive HTML guide untuk student
- Tabbed navigation (11 sections)
- Copy-paste ready code blocks
- Responsive design
- Complete implementation examples
- File upload examples

**Kapan menggunakan:** Untuk membuka di browser dan navigasi interaktif student dashboard.

---

### Authentication & Registration

#### 11. **login-guide.html** 🔐
Panduan implementasi halaman login.

**Isi:**
- Login form dengan Laravel Blade
- Authentication dengan Sanctum
- Token storage di localStorage
- Error handling
- Redirect setelah login

#### 12. **regist-guide.html** 📝
Panduan implementasi halaman registrasi.

**Isi:**
- Registration form
- Validation
- Role selection (Student/Parent/Instructor)
- API integration
- Success/error handling

---

## 🔗 Ngrok Configuration

**Base URL:** `https://loraine-seminiferous-snappily.ngrok-free.dev`

### Penting!
Semua request ke API harus include header:
```javascript
{
    'ngrok-skip-browser-warning': 'true'
}
```

---

## 🎯 Quick Start

### 1. Setup Routes
```php
// routes/web.php
Route::prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', fn() => view('parent.dashboard'))->name('dashboard');
    Route::get('/students', fn() => view('parent.students'))->name('students');
    // ... more routes
});
```

### 2. Create API Helper
```bash
# Create directory
mkdir -p public/js/parent

# Copy api.js from guide
cp PARENT-DASHBOARD-GUIDE.md > extract api.js code
```

### 3. Create Base Layout
```bash
# Create directory
mkdir -p resources/views/layouts

# Create parent.blade.php from guide
```

### 4. Create Pages
```bash
# Create parent views directory
mkdir -p resources/views/parent

# Create all pages: dashboard, students, etc.
```

---

## 📁 Project Structure

```
lmsRPL3/
├── docs/
│   └── frontend-guiding/
│       ├── README.md                                    # This file
│       ├── PARENT-DASHBOARD-GUIDE.md                    # Parent complete guide
│       ├── PARENT-DASHBOARD-QUICK-REFERENCE.md          # Parent quick reference
│       ├── ADDITIONAL-PAGES-IMPLEMENTATION.md           # Parent additional pages
│       ├── parent-dashboard-complete.html               # Parent interactive HTML
│       ├── INSTRUCTOR-DASHBOARD-GUIDE.md                # Instructor complete guide
│       ├── INSTRUCTOR-DASHBOARD-QUICK-REFERENCE.md      # Instructor quick reference
│       ├── instructor-dashboard-complete.html           # Instructor interactive HTML
│       ├── login-guide.html                             # Login guide
│       └── regist-guide.html                            # Registration guide
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── parent.blade.php                   # Parent base layout
│       │   └── instructor.blade.php               # Instructor base layout
│       ├── parent/
│       │   ├── dashboard.blade.php                # Dashboard page
│       │   ├── students.blade.php                 # Students list
│       │   ├── student-detail.blade.php           # Student detail
│       │   ├── grades.blade.php                   # Grades view
│       │   ├── attendance.blade.php               # Attendance
│       │   ├── announcements.blade.php            # Announcements
│       │   ├── notifications.blade.php            # Notifications
│       │   └── certificates.blade.php             # Certificates
│       └── instructor/
│           ├── dashboard.blade.php                # Dashboard page
│           ├── courses.blade.php                  # My courses
│           ├── course-detail.blade.php            # Course management
│           ├── assignments.blade.php              # Assignments
│           ├── submissions.blade.php              # Review submissions
│           ├── grading.blade.php                  # Grading interface
│           ├── attendance.blade.php               # Attendance management
│           ├── students.blade.php                 # Students in courses
│           ├── announcements.blade.php            # Create announcements
│           └── certificates.blade.php             # Generate certificates
│
├── public/
│   └── js/
│       ├── parent/
│       │   └── api.js                             # Parent API helper
│       └── instructor/
│           └── api.js                             # Instructor API helper
│
└── routes/
    └── web.php                                     # Web routes
```

---

## 🛠️ Tech Stack

- **Laravel Blade** - Template engine
- **Bootstrap 5.3** - UI framework
- **Chart.js 4.x** - Data visualization
- **Bootstrap Icons 1.11** - Icon set
- **Vanilla JavaScript** - No jQuery needed
- **Fetch API** - HTTP requests
- **Ngrok** - Backend tunneling

---

## 🎭 Dashboard Types

### 👨‍👩‍👧‍👦 Parent Dashboard
**Focus:** Monitoring children's progress
- View children's courses and enrollments
- Check grades and submissions
- Monitor attendance
- View announcements and notifications
- Access certificates

### 👨‍🏫 Instructor Dashboard
**Focus:** Course and student management
- Create and manage courses
- Create assignments and grade submissions
- Manage attendance sessions
- Create announcements
- Generate certificates for students
- View enrolled students

---

### 🎓 Student Dashboard
- **Main Dashboard:** Personal statistics, enrolled courses overview, pending assignments, recent activities
- **My Courses:** View all enrolled courses with progress tracking
- **Course Detail:** Access course materials, assignments, grades, and attendance per course
- **Assignments:** View assignments, submit work with file upload, track submission status
- **My Grades:** View all grades, grade statistics, performance tracking
- **My Attendance:** Attendance records, attendance summary, mark attendance
- **My Certificates:** View earned certificates, download certificates, verify certificates
- **Profile Management:** Update personal information, change password

## 📊 Features Implemented

### Parent Dashboard
✅ Overview statistics (total anak, enrollments, avg grades, attendance)  
✅ Students list dengan cards  
✅ Doughnut chart untuk enrollments  
✅ Recent announcements  
✅ Real-time notification badge  
✅ Responsive design  

### Students Management
✅ Search by name  
✅ Filter by status  
✅ Sort by name/enrollments  
✅ Student detail dengan tabs  
✅ Courses, grades, submissions view  

### Grades & Progress
✅ View grades per student  
✅ Filter by course  
✅ Statistics (avg, max, min)  
✅ Grade components display  

### Attendance Tracking
✅ Attendance summary per course  
✅ Present/absent/sick/permission counts  
✅ Attendance percentage  

### Communication
✅ View announcements (global & per-course)  
✅ Real-time notifications  
✅ Mark as read functionality  
✅ Unread count badge  

### Certificates
✅ View certificates  
✅ Download certificates  
✅ Verify certificate by code  

### Instructor Dashboard
✅ Course CRUD operations  
✅ View enrolled students  
✅ Create and manage assignments  
✅ Review and grade submissions  
✅ Input grades with grade components  
✅ Create and manage attendance sessions  
✅ Mark attendance (present/absent/sick/permission)  
✅ Create and publish announcements  
✅ Generate certificates (single & bulk)  
✅ View course statistics  

---

### Student Dashboard Features
- **Course Access:** View enrolled courses and materials
- **Assignment Submission:** Submit assignments with file upload support
- **Grade Tracking:** Track grades and academic performance
- **Attendance Monitoring:** View attendance records and statistics
- **Certificate Management:** Access and download earned certificates
- **Notifications:** Real-time notifications for important updates
- **Profile Management:** Update personal information and settings

## 🔌 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/user` | GET | Get current user |
| `/api/v1/parents/{id}/students` | GET | Get children list |
| `/api/v1/students/{id}` | GET | Get student detail |
| `/api/v1/students/{id}/enrollments` | GET | Get enrollments |
| `/api/v1/students/{id}/submissions` | GET | Get submissions |
| `/api/v1/grades/student?student_id={id}` | GET | Get grades |
| `/api/v1/attendance/students/{id}/summary` | GET | Get attendance |
| `/api/v1/announcements/active` | GET | Get announcements |
| `/api/v1/notifications` | GET | Get notifications |
| `/api/v1/notifications/{id}/read` | POST | Mark as read |
| `/api/v1/notifications/counts` | GET | Get notification counts |
| `/api/v1/certificates?student_id={id}` | GET | Get student certificates |

### Student Endpoints
```javascript
// Dashboard
GET /api/v1/students/dashboard/stats
GET /api/v1/students/dashboard/activities

// Courses
GET /api/v1/students/courses
GET /api/v1/courses/{id}
GET /api/v1/courses/{id}/materials
GET /api/v1/students/courses/{id}/progress

// Assignments
GET /api/v1/students/assignments
GET /api/v1/assignments/{id}
POST /api/v1/assignments/{id}/submit
GET /api/v1/submissions/{id}

// Grades
GET /api/v1/students/grades
GET /api/v1/students/grades/course/{id}
GET /api/v1/students/grades/statistics

// Attendance
GET /api/v1/students/attendance
POST /api/v1/attendance/sessions/{id}/mark
GET /api/v1/students/attendance/course/{id}/summary

// Certificates
GET /api/v1/students/certificates
GET /api/v1/certificates/{id}
GET /api/v1/certificates/{id}/download
GET /api/v1/certificates/verify/{number}

// Profile
PUT /api/v1/students/profile
POST /api/v1/students/change-password
```

### Instructor Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/instructors/{id}/courses` | GET | Get instructor's courses |
| `/api/v1/courses` | POST | Create course |
| `/api/v1/courses/{id}` | GET/PUT/DELETE | Course CRUD |
| `/api/v1/courses/{id}/enrollments` | GET | Get enrollments |
| `/api/v1/courses/{id}/assignments` | GET | Course assignments |
| `/api/v1/assignments` | POST | Create assignment |
| `/api/v1/assignments/{id}` | PUT/DELETE | Update/Delete assignment |
| `/api/v1/assignments/{id}/submissions` | GET | Get submissions |
| `/api/v1/submissions/{id}/grade` | POST | Grade submission |
| `/api/v1/grades/course/{id}` | GET | Course grades |
| `/api/v1/grades` | POST | Create grade |
| `/api/v1/grade-components/course/{id}` | GET | Grade components |
| `/api/v1/grade-components` | POST | Create component |
| `/api/v1/attendance/sessions` | POST | Create session |
| `/api/v1/attendance/sessions/{id}/open` | POST | Open session |
| `/api/v1/attendance/sessions/{id}/close` | POST | Close session |
| `/api/v1/attendance/records` | POST | Mark attendance |
| `/api/v1/announcements` | POST | Create announcement |
| `/api/v1/announcements/{id}/publish` | POST | Publish announcement |
| `/api/v1/certificates/generate` | POST | Generate certificate |
| `/api/v1/certificates/bulk-generate` | POST | Bulk generate |

---

## 💡 Tips & Best Practices

### 1. Always Include Ngrok Header
```javascript
headers: {
    'ngrok-skip-browser-warning': 'true'
}
```

### 2. Handle Authentication
```javascript
if (response.status === 401) {
    localStorage.removeItem('auth_token');
    window.location.href = '/login';
}
```

### 3. Use Loading States
```javascript
// Show loading
document.getElementById('loading').style.display = 'block';

// Hide after data loaded
document.getElementById('loading').style.display = 'none';
```

### 4. Error Handling
```javascript
try {
    const data = await ParentAPI.getStudents(parentId);
} catch (error) {
    console.error('Error:', error);
    showError('Gagal memuat data: ' + error.message);
}
```

### 5. Use Bootstrap Utilities
```html
<!-- Instead of custom CSS -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Content -->
</div>
```

---

## 🎨 UI Components

### Statistics Card
```html
<div class="card">
    <div class="card-body text-center">
        <h6 class="text-muted mb-2">Label</h6>
        <h2 class="fw-bold text-primary">Value</h2>
    </div>
</div>
```

### Student Card
```html
<div class="card">
    <div class="card-body">
        <h5 class="fw-bold">Student Name</h5>
        <p class="text-muted">Info</p>
        <a href="#" class="btn btn-primary w-100">Action</a>
    </div>
</div>
```

### Loading Spinner
```html
<div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">Memuat data...</p>
</div>
```

---

## 🐛 Troubleshooting

### Problem: Ngrok warning page muncul
**Solution:** Tambahkan header `ngrok-skip-browser-warning: true`

### Problem: 401 Unauthorized
**Solution:** Check token di localStorage, pastikan format Bearer token benar

### Problem: CORS error
**Solution:** Pastikan backend sudah setup CORS dengan benar

### Problem: Data tidak muncul
**Solution:** 
1. Check console untuk error
2. Verify API endpoint benar
3. Check response di Network tab
4. Verify data structure

### Problem: Chart tidak render
**Solution:** 
1. Pastikan Chart.js CDN loaded
2. Check canvas element ada
3. Verify data format benar

---

## 📞 Support

Jika menemukan masalah atau butuh bantuan:

**Parent Dashboard:**
1. **Check dokumentasi lengkap:** `PARENT-DASHBOARD-GUIDE.md`
2. **Quick reference:** `PARENT-DASHBOARD-QUICK-REFERENCE.md`
3. **Additional pages:** `ADDITIONAL-PAGES-IMPLEMENTATION.md`
4. **Interactive guide:** Buka `parent-dashboard-complete.html` di browser

**Instructor Dashboard:**
1. **Check dokumentasi lengkap:** `INSTRUCTOR-DASHBOARD-GUIDE.md`
2. **Quick reference:** `INSTRUCTOR-DASHBOARD-QUICK-REFERENCE.md`

**API Documentation:**
1. **API documentation:** `docs/api/API-DOCUMENTATION.md`
2. **Business logic:** `docs/api/BUSINESS-LOGIC.md`

---

## 🔄 Update Log

**Version 1.0** (Current)
- ✅ Complete parent dashboard implementation (8 pages)
- ✅ Complete instructor dashboard implementation (10 pages)
- ✅ Ngrok integration for both dashboards
- ✅ Bootstrap 5.3 UI
- ✅ Chart.js visualization
- ✅ Real-time notifications
- ✅ Interactive HTML guides
- ✅ Quick reference guides
- ✅ Additional pages implementation guide

---

## 📝 Notes

- Dokumentasi ini mencakup **Parent Dashboard** dan **Instructor Dashboard**
- Untuk **Student Dashboard**, akan dibuat dokumentasi terpisah
- Backend API sudah tersedia dan accessible via Ngrok
- Frontend menggunakan Laravel Blade (server-side rendering)
- Authentication menggunakan Laravel Sanctum token
- Semua dashboard menggunakan struktur dan pattern yang konsisten

---

## 🚀 Next Steps

1. ✅ ~~Implement Parent Dashboard~~ (DONE)
2. ✅ ~~Implement Instructor Dashboard~~ (DONE)
3. Implement Student Dashboard
4. Add real-time features dengan WebSocket
5. Add offline support dengan Service Worker
6. Add PWA capabilities
7. Optimize performance
8. Add automated tests
9. Add file upload functionality
10. Add rich text editor for content

---

**Dibuat untuk:** SmartDev LMS Project  
**Last Updated:** 2024  
**Ngrok URL:** https://loraine-seminiferous-snappily.ngrok-free.dev  
**Version:** 1.0