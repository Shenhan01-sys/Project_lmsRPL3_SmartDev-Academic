# 📚 Parent Dashboard API Integration Guide

## 🎯 Overview

File `parent-dashboard-api.html` adalah implementasi dashboard untuk **Parent Portal** yang terintegrasi penuh dengan backend API SmartDev LMS. Dashboard ini menggunakan pola yang sama dengan `instructor-dashboard-complete.html`.

---

## 🔐 Authentication Flow

### 1. **Token-Based Authentication**
```javascript
// Token disimpan di localStorage
localStorage.setItem('auth_token', token);

// Header untuk setiap request
headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'ngrok-skip-browser-warning': 'true'
}
```

### 2. **Init Flow**
```
initApp() 
  → Check localStorage token
  → Fetch /api/user (validate token & get user info)
  → Validate role === 'parent'
  → loadParentProfile()
  → loadDashboard()
```

---

## 📡 API Endpoints Used

### **Authentication**
| Method | Endpoint | Purpose | Response |
|--------|----------|---------|----------|
| GET | `/api/user` | Get current authenticated user | `{ id, name, email, role, parentProfile }` |

### **Parent Profile**
| Method | Endpoint | Purpose | Controller |
|--------|----------|---------|------------|
| GET | `/api/v1/parents/{id}` | Get parent details | `ParentController@show` |
| GET | `/api/v1/parents/{id}/students` | Get parent's children with enrollments | `ParentController@students` |
| GET | `/api/v1/parents/{id}/active-students` | Get only active children | `ParentController@activeStudents` |

**Response Example** (`/parents/{id}/students`):
```json
[
  {
    "id": 1,
    "full_name": "Chelvin Dessan",
    "student_number": "123456",
    "current_grade": "XII RPL",
    "gender": "Laki-laki",
    "status": "active",
    "enrollments": [
      {
        "id": 1,
        "course_id": 5,
        "student_id": 1,
        "status": "active",
        "progress": 75.5,
        "course": {
          "id": 5,
          "course_code": "RPL301",
          "course_name": "Pemrograman Web Lanjut"
        }
      }
    ]
  }
]
```

### **Courses** (via original CourseController)
| Method | Endpoint | Purpose | Policy Check |
|--------|----------|---------|--------------|
| GET | `/api/v1/courses` | List all courses | `CoursePolicy@viewAny` |
| GET | `/api/v1/courses/{id}` | Get course detail | `CoursePolicy@view` (parent hanya bisa lihat course anaknya) |

**Policy Logic** (CoursePolicy.php):
```php
public function view(User $user, Course $course): bool
{
    if ($user->role === 'parent') {
        // Parent bisa lihat course yang anaknya ikuti
        return $user->parentProfile && 
               $user->parentProfile->students()
                   ->whereHas('enrollments', function($query) use ($course) {
                       $query->where('course_id', $course->id);
                   })->exists();
    }
}
```

### **Grades** (via original GradeController)
| Method | Endpoint | Purpose | Policy |
|--------|----------|---------|--------|
| GET | `/api/v1/grades` | List grades (filtered by policy) | `GradePolicy@viewAny` |

**Note**: Policy memastikan parent hanya bisa melihat nilai anak-anaknya sendiri.

### **Attendance** (via AttendanceRecordController)
| Method | Endpoint | Purpose | Policy |
|--------|----------|---------|--------|
| GET | `/api/v1/attendance-records` | List attendance records | `AttendanceRecordPolicy@viewAny` |

### **Announcements** (via AnnouncementController)
| Method | Endpoint | Purpose | Policy |
|--------|----------|---------|--------|
| GET | `/api/v1/announcements` | List public announcements | `AnnouncementPolicy@viewAny` |

**Query Parameters**:
- `status=published` - Only published announcements
- `per_page=10` - Pagination limit

### **Certificates** (via CertificateController)
| Method | Endpoint | Purpose | Policy |
|--------|----------|---------|--------|
| GET | `/api/v1/certificates` | List certificates | `CertificatePolicy@viewAny` |

---

## 🗂️ Data Flow Architecture

```
┌─────────────────────────────────────────────────────┐
│                  Parent Dashboard                    │
└─────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────┐
│              Authentication Layer                    │
│    localStorage.getItem('auth_token')               │
└─────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────┐
│                 API Gateway                          │
│         BASE_API + endpoint + Bearer Token          │
└─────────────────────────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        ▼               ▼               ▼
┌──────────────┐ ┌─────────────┐ ┌────────────┐
│ParentController CourceController GradeController
└──────────────┘ └─────────────┘ └────────────┘
        │               │               │
        ▼               ▼               ▼
┌──────────────────────────────────────────┐
│           Authorization Policies          │
│  • ParentPolicy                          │
│  • CoursePolicy (parent check)           │
│  • GradePolicy (student ownership)       │
│  • AttendanceRecordPolicy                │
└──────────────────────────────────────────┘
        │
        ▼
┌──────────────────────────────────────────┐
│           Database Models                 │
│  • ParentModel (students relationship)   │
│  • Student (enrollments, grades)         │
│  • Course, Grade, Attendance, etc        │
└──────────────────────────────────────────┘
```

---

## 🔑 Key Features Implemented

### ✅ **1. Dashboard Statistics**
- **Total Anak**: Count dari `/parents/{id}/students`
- **Total Enrollments**: Sum semua enrollments dari semua students
- **Rata-rata Nilai**: Calculated dari grades (TODO)
- **Kehadiran (%)**: Calculated dari attendance records (TODO)

### ✅ **2. Students List**
- Fetch dari `/parents/{id}/students`
- Display dengan card layout
- Filter by student name
- Show enrollment count per student

### ✅ **3. Student Detail View**
- Click "Lihat Detail" pada student card
- Show student info (NIS, gender, grade, status)
- Show enrolled courses dengan course details
- Show recent grades untuk student tersebut

### ✅ **4. Announcements**
- Public announcements dari `/announcements?status=published`
- Filtered untuk menampilkan yang relevant (global + course-specific)
- Dashboard widget menampilkan 2 announcement terbaru

### 🚧 **5. Features In Development**
- **Grades Detail**: Fetch dari `/grades` dengan filter `student_id`
- **Attendance Detail**: Fetch dari `/attendance-records` dengan student filter
- **Certificates**: Fetch dari `/certificates` untuk student

---

## 🛠️ Implementation Examples

### **Loading Students**
```javascript
async function loadStudents() {
    if (!currentParent) return;

    const container = document.getElementById('students-container');
    
    // Already loaded during dashboard init
    if (allStudents.length === 0) {
        container.innerHTML = '<div>Belum ada data anak</div>';
        return;
    }

    // Render student cards
    container.innerHTML = allStudents.map((s, idx) => {
        return `
        <div class="dashboard-card">
            <h3>${s.full_name}</h3>
            <p>${s.current_grade} | NIS: ${s.student_number}</p>
            <button onclick="showStudentDetail(${s.id})">
                Lihat Detail
            </button>
        </div>
        `;
    }).join('');
}
```

### **Student Detail with Enrollments**
```javascript
async function showStudentDetail(studentId) {
    const student = allStudents.find(s => s.id === studentId);
    if (!student) return;

    // Display student info
    document.getElementById('detail-student-name').innerText = student.full_name;
    
    // Display enrollments
    const enrollments = student.enrollments || [];
    const coursesHtml = enrollments.map(e => `
        <div class="course-card">
            <div>${e.course?.course_name}</div>
            <div>Status: ${e.status}</div>
            <div>Progress: ${e.progress}%</div>
        </div>
    `).join('');

    document.getElementById('detail-student-courses').innerHTML = coursesHtml;
    
    switchView('student-detail');
}
```

### **Loading Announcements**
```javascript
async function loadAnnouncements() {
    const res = await fetchApi('/announcements?status=published');
    const announcements = res?.data || [];
    
    const container = document.getElementById('announcements-full-list');
    container.innerHTML = announcements.map(a => `
        <div class="announcement-card">
            <span class="badge">${a.priority}</span>
            <h3>${a.title}</h3>
            <p>${a.content}</p>
            <small>${new Date(a.created_at).toLocaleDateString('id-ID')}</small>
        </div>
    `).join('');
}
```

---

## 🔒 Security & Authorization

### **Policy-Based Access Control**

1. **Parent hanya bisa akses data anak sendiri**:
   ```php
   // GradePolicy.php
   public function viewAny(User $user): bool
   {
       if ($user->role === 'parent') {
           // Filter handled in controller
           return true;
       }
   }
   ```

2. **Controller filtering by parent relationship**:
   ```php
   // GradeController.php
   public function index(Request $request)
   {
       if (auth()->user()->role === 'parent') {
           $studentIds = auth()->user()->parentProfile
               ->students()
               ->pluck('id');
           
           $query->whereHas('enrollment', function($q) use ($studentIds) {
               $q->whereIn('student_id', $studentIds);
           });
       }
   }
   ```

3. **Model Relationships**:
   ```php
   // ParentModel.php
   public function students(): HasMany
   {
       return $this->hasMany(Student::class, 'parent_id');
   }

   // Student.php
   public function enrollments(): HasMany
   {
       return $this->hasMany(Enrollment::class);
   }
   ```

---

## 📝 TODO: Features to Complete

### **1. Grades Implementation**
```javascript
async function loadGrades() {
    const studentFilter = document.getElementById('grades-student-filter').value;
    
    let endpoint = '/grades';
    if (studentFilter) {
        endpoint += `?student_id=${studentFilter}`;
    }
    
    const res = await fetchApi(endpoint);
    const grades = res?.data || [];
    
    // Render grade cards
    const container = document.getElementById('grades-container');
    container.innerHTML = grades.map(g => `
        <div class="grade-card">
            <div class="course">${g.course?.course_name}</div>
            <div class="score">${g.score}</div>
            <div class="student">${g.student?.full_name}</div>
        </div>
    `).join('');
}
```

### **2. Attendance Implementation**
```javascript
async function loadAttendance() {
    const studentFilter = document.getElementById('attendance-student-filter').value;
    
    let endpoint = '/attendance-records';
    if (studentFilter) {
        endpoint += `?student_id=${studentFilter}`;
    }
    
    const res = await fetchApi(endpoint);
    const records = res?.data || [];
    
    // Render attendance list
    const container = document.getElementById('attendance-list');
    container.innerHTML = records.map(a => `
        <div class="attendance-row">
            <span>${a.student?.full_name}</span>
            <span>${a.session?.name}</span>
            <span class="badge ${a.status === 'present' ? 'success' : 'danger'}">
                ${a.status}
            </span>
        </div>
    `).join('');
}
```

### **3. Certificates Implementation**
```javascript
async function loadCertificates() {
    const res = await fetchApi('/certificates');
    const certificates = res?.data || [];
    
    // Filter hanya certificates untuk anak-anak parent
    const studentIds = allStudents.map(s => s.id);
    const myCertificates = certificates.filter(c => 
        studentIds.includes(c.student_id)
    );
    
    const container = document.getElementById('certificates-list');
    container.innerHTML = myCertificates.map(c => `
        <div class="certificate-card">
            <h3>${c.title}</h3>
            <p>Student: ${c.student?.full_name}</p>
            <p>Date: ${new Date(c.issued_at).toLocaleDateString()}</p>
            <button onclick="downloadCertificate(${c.id})">
                <i class="bi bi-download"></i> Download
            </button>
        </div>
    `).join('');
}
```

---

## 🧪 Testing Guide

### **1. Get Auth Token**
```bash
# Login as parent user
curl -X POST https://portohansgunawan.my.id/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "parent@example.com",
    "password": "password123"
  }'

# Response:
# {
#   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
#   "token_type": "Bearer"
# }
```

### **2. Test Parent Endpoints**
```bash
# Get parent profile
curl https://portohansgunawan.my.id/api/v1/parents/1 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get parent's students
curl https://portohansgunawan.my.id/api/v1/parents/1/students \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### **3. Test Dashboard Access**
1. Open `parent-dashboard-api.html` di browser
2. Paste token yang didapat dari login
3. Dashboard akan load data parent dan students
4. Navigate ke different sections untuk test functionality

---

## 🎨 UI Components Reference

### **Color Scheme**
- Primary: `#4f46e5` (Indigo)
- Secondary: `#64748b` (Slate)
- Success: `#10b981` (Emerald)
- Warning: `#f59e0b` (Amber)
- Danger: `#ef4444` (Red)

### **Key CSS Classes**
- `.dashboard-card` - Main card container dengan hover effect
- `.nav-link-custom` - Sidebar navigation links
- `.grade-card` - Special gradient untuk grade cards
- `.fade-in` - Animation untuk page transitions

---

## 📌 Important Notes

1. **Parent dapat melihat**:
   - Semua students yang terkait dengan parent_id mereka
   - Semua courses yang students mereka ikuti (via enrollments)
   - Semua grades untuk students mereka
   - Semua attendance records untuk students mereka
   - Public announcements (global atau course-specific)

2. **Parent TIDAK dapat**:
   - Membuat/edit course
   - Membuat/edit assignment
   - Grading submissions
   - Manage attendance sessions
   - Melihat data student lain

3. **Authorization Flow**:
   ```
   User (parent role) 
     → Has parentProfile
       → Has students (via parent_id)
         → Has enrollments
           → Can view courses, grades, attendance for those enrollments
   ```

---

## 🚀 Deployment Notes

1. Update `apiUrl` input dengan production URL
2. Ensure CORS is configured properly di backend
3. SSL certificate harus valid untuk production
4. Token expiration handling perlu di-implement

---

**Created by**: AI Assistant  
**Date**: 2025-11-25  
**Version**: 1.0  
**Based on**: Instructor Dashboard Pattern
