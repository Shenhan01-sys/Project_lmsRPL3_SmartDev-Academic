# 👨‍🏫 Instructor Dashboard - Quick Reference Guide

**Backend API (Ngrok):** `https://loraine-seminiferous-snappily.ngrok-free.dev`

---

## 🚀 Quick Start

### 1. Ngrok Configuration

```javascript
// Base configuration - Add to all pages
const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
const API_BASE = NGROK_BASE + '/api/v1';

// Headers with Ngrok bypass
const headers = {
    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
    'Accept': 'application/json',
    'ngrok-skip-browser-warning': 'true' // Important!
};
```

### 2. File Structure

```
resources/views/
├── layouts/instructor.blade.php       # Base layout
└── instructor/
    ├── dashboard.blade.php            # Main dashboard
    ├── courses.blade.php              # My courses
    ├── course-detail.blade.php        # Course management
    ├── assignments.blade.php          # Assignments
    ├── submissions.blade.php          # Review submissions
    ├── grading.blade.php              # Grading interface
    ├── attendance.blade.php           # Attendance
    ├── students.blade.php             # Students
    ├── announcements.blade.php        # Announcements
    └── certificates.blade.php         # Certificates

public/js/instructor/api.js            # API helper
```

---

## 📡 API Endpoints Cheat Sheet

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/user` | GET | Get current user |
| `/api/v1/instructors/{id}/courses` | GET | Get my courses |
| `/api/v1/courses` | POST | Create course |
| `/api/v1/courses/{id}` | GET/PUT/DELETE | Course CRUD |
| `/api/v1/courses/{id}/enrollments` | GET | Get enrollments |
| `/api/v1/courses/{id}/assignments` | GET | Course assignments |
| `/api/v1/assignments` | POST | Create assignment |
| `/api/v1/assignments/{id}` | PUT/DELETE | Update/Delete |
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
| `/api/v1/announcements/{id}/publish` | POST | Publish |
| `/api/v1/certificates/generate` | POST | Generate certificate |
| `/api/v1/certificates/bulk-generate` | POST | Bulk generate |

---

## 🎨 Layout Template (Base)

```blade
<!-- resources/views/layouts/instructor.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Instructor Dashboard</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar { background: linear-gradient(180deg, #0ea5e9, #8b5cf6); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 sidebar">
                <!-- Sidebar content -->
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <script>
        const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
        const API_BASE = NGROK_BASE + '/api/v1';
    </script>
    
    @yield('extra-js')
</body>
</html>
```

---

## 🔌 API Helper (api.js)

```javascript
// public/js/instructor/api.js
const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
const API_BASE = NGROK_BASE + '/api/v1';

function getHeaders() {
    return {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': 'true'
    };
}

const InstructorAPI = {
    // Auth
    getCurrentUser: () => fetch(NGROK_BASE + '/api/user', { headers: getHeaders() }).then(r => r.json()),
    
    // Courses
    getCourses: (instructorId) => fetch(`${API_BASE}/instructors/${instructorId}/courses`, { headers: getHeaders() }).then(r => r.json()),
    getCourse: (id) => fetch(`${API_BASE}/courses/${id}`, { headers: getHeaders() }).then(r => r.json()),
    createCourse: (data) => fetch(`${API_BASE}/courses`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    updateCourse: (id, data) => fetch(`${API_BASE}/courses/${id}`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    deleteCourse: (id) => fetch(`${API_BASE}/courses/${id}`, { method: 'DELETE', headers: getHeaders() }).then(r => r.json()),
    
    // Enrollments
    getCourseEnrollments: (courseId) => fetch(`${API_BASE}/courses/${courseId}/enrollments`, { headers: getHeaders() }).then(r => r.json()),
    
    // Assignments
    getCourseAssignments: (courseId) => fetch(`${API_BASE}/courses/${courseId}/assignments`, { headers: getHeaders() }).then(r => r.json()),
    createAssignment: (data) => fetch(`${API_BASE}/assignments`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    updateAssignment: (id, data) => fetch(`${API_BASE}/assignments/${id}`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    deleteAssignment: (id) => fetch(`${API_BASE}/assignments/${id}`, { method: 'DELETE', headers: getHeaders() }).then(r => r.json()),
    
    // Submissions
    getAssignmentSubmissions: (assignmentId) => fetch(`${API_BASE}/assignments/${assignmentId}/submissions`, { headers: getHeaders() }).then(r => r.json()),
    gradeSubmission: (id, score, feedback) => fetch(`${API_BASE}/submissions/${id}/grade`, { method: 'POST', headers: getHeaders(), body: JSON.stringify({ score, feedback }) }).then(r => r.json()),
    
    // Grading
    getCourseGrades: (courseId) => fetch(`${API_BASE}/grades/course/${courseId}`, { headers: getHeaders() }).then(r => r.json()),
    createGrade: (data) => fetch(`${API_BASE}/grades`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    getGradeComponents: (courseId) => fetch(`${API_BASE}/grade-components/course/${courseId}`, { headers: getHeaders() }).then(r => r.json()),
    
    // Attendance
    createAttendanceSession: (data) => fetch(`${API_BASE}/attendance/sessions`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    openAttendanceSession: (id) => fetch(`${API_BASE}/attendance/sessions/${id}/open`, { method: 'POST', headers: getHeaders() }).then(r => r.json()),
    closeAttendanceSession: (id) => fetch(`${API_BASE}/attendance/sessions/${id}/close`, { method: 'POST', headers: getHeaders() }).then(r => r.json()),
    markAttendance: (sessionId, studentId, status) => fetch(`${API_BASE}/attendance/records`, { method: 'POST', headers: getHeaders(), body: JSON.stringify({ session_id: sessionId, student_id: studentId, status }) }).then(r => r.json()),
    
    // Announcements
    createAnnouncement: (data) => fetch(`${API_BASE}/announcements`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) }).then(r => r.json()),
    publishAnnouncement: (id) => fetch(`${API_BASE}/announcements/${id}/publish`, { method: 'POST', headers: getHeaders() }).then(r => r.json()),
    
    // Certificates
    generateCertificate: (studentId, courseId) => fetch(`${API_BASE}/certificates/generate`, { method: 'POST', headers: getHeaders(), body: JSON.stringify({ student_id: studentId, course_id: courseId }) }).then(r => r.json()),
    bulkGenerateCertificates: (courseId) => fetch(`${API_BASE}/certificates/bulk-generate`, { method: 'POST', headers: getHeaders(), body: JSON.stringify({ course_id: courseId }) }).then(r => r.json())
};

window.InstructorAPI = InstructorAPI;
```

---

## 📊 Dashboard Page Template

```blade
@extends('layouts.instructor')
@section('title', 'Dashboard')

@section('content')
<h2>Dashboard</h2>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Courses</h6>
                <h2 id="totalCourses">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Students</h6>
                <h2 id="totalStudents">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Pending Submissions</h6>
                <h2 id="pendingSubmissions">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Assignments</h6>
                <h2 id="totalAssignments">-</h2>
            </div>
        </div>
    </div>
</div>

<!-- Courses List -->
<div class="card">
    <div class="card-body">
        <div id="coursesList" class="row g-3"></div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/instructor/api.js') }}"></script>
<script>
async function loadDashboard() {
    const user = await InstructorAPI.getCurrentUser();
    const courses = await InstructorAPI.getCourses(user.instructor.id);
    
    document.getElementById('totalCourses').textContent = courses.length;
    
    document.getElementById('coursesList').innerHTML = courses.map(c => `
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>${c.title}</h5>
                    <a href="/instructor/courses/${c.id}" class="btn btn-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection
```

---

## 📚 My Courses Page

```blade
@extends('layouts.instructor')
@section('title', 'My Courses')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h2>My Courses</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-circle"></i> Create Course
    </button>
</div>

<div id="coursesList" class="row g-4"></div>

<!-- Create Modal -->
<div class="modal fade" id="createModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Create Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="courseForm">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select class="form-select" name="category">
                            <option value="Programming">Programming</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Science">Science</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCourse()">Create</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/instructor/api.js') }}"></script>
<script>
async function loadCourses() {
    const user = await InstructorAPI.getCurrentUser();
    const courses = await InstructorAPI.getCourses(user.instructor.id);
    
    document.getElementById('coursesList').innerHTML = courses.map(c => `
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>${c.title}</h5>
                    <p class="text-muted">${c.description?.substring(0, 100)}...</p>
                    <span class="badge bg-primary">${c.category}</span>
                    <a href="/instructor/courses/${c.id}" class="btn btn-sm btn-primary w-100 mt-3">
                        Manage
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

async function createCourse() {
    const form = document.getElementById('courseForm');
    const formData = new FormData(form);
    
    const data = {
        title: formData.get('title'),
        description: formData.get('description'),
        category: formData.get('category'),
        status: 'active'
    };
    
    await InstructorAPI.createCourse(data);
    bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
    loadCourses();
}

document.addEventListener('DOMContentLoaded', loadCourses);
</script>
@endsection
```

---

## 📝 Create Assignment

```javascript
async function createAssignment() {
    const formData = new FormData(document.getElementById('assignmentForm'));
    
    const data = {
        course_id: courseId,
        title: formData.get('title'),
        description: formData.get('description'),
        due_date: formData.get('due_date'),
        max_score: parseInt(formData.get('max_score'))
    };
    
    try {
        await InstructorAPI.createAssignment(data);
        alert('Assignment created successfully!');
        loadAssignments();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
```

---

## ✅ Grade Submission

```javascript
async function gradeSubmission(submissionId) {
    const score = document.getElementById('score').value;
    const feedback = document.getElementById('feedback').value;
    
    if (!score || score < 0 || score > 100) {
        alert('Please enter a valid score (0-100)');
        return;
    }
    
    try {
        await InstructorAPI.gradeSubmission(submissionId, parseFloat(score), feedback);
        alert('Submission graded successfully!');
        loadSubmissions();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
```

---

## 📅 Create Attendance Session

```javascript
async function createSession() {
    const data = {
        course_id: courseId,
        session_date: document.getElementById('sessionDate').value,
        topic: document.getElementById('topic').value
    };
    
    try {
        const session = await InstructorAPI.createAttendanceSession(data);
        await InstructorAPI.openAttendanceSession(session.id);
        alert('Attendance session created and opened!');
        loadSessions();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
```

---

## 📊 Common Patterns

### Loading State
```javascript
document.getElementById('loading').style.display = 'block';
// Load data...
document.getElementById('loading').style.display = 'none';
```

### Error Handling
```javascript
try {
    const data = await InstructorAPI.getCourses(instructorId);
} catch (error) {
    console.error('Error:', error);
    alert('Failed to load data: ' + error.message);
}
```

### Confirmation Dialog
```javascript
if (!confirm('Are you sure you want to delete this?')) {
    return;
}
// Proceed with deletion
```

### Success Message
```javascript
function showSuccess(message) {
    const alert = `
        <div class="alert alert-success alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.getElementById('alerts').innerHTML = alert;
}
```

---

## 📈 Chart Example

```javascript
// Bar chart for student grades
const ctx = document.getElementById('gradesChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: students.map(s => s.name),
        datasets: [{
            label: 'Grade',
            data: students.map(s => s.grade),
            backgroundColor: '#0ea5e9'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
```

---

## ⚠️ Important Notes

1. **Always include ngrok-skip-browser-warning header** in all requests
2. **Store auth token in localStorage**: `localStorage.getItem('auth_token')`
3. **Handle 401 responses**: Redirect to login
4. **Validate input before submitting**
5. **Show loading states for better UX**
6. **Use confirmation dialogs for destructive actions**
7. **Provide feedback for all actions**

---

## 🔗 CDN Links

```html
<!-- Bootstrap 5.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
```

---

## 📚 For Complete Guide

See: `INSTRUCTOR-DASHBOARD-GUIDE.md` for detailed implementation of all pages.

---

**Last Updated:** 2024  
**Ngrok URL:** https://loraine-seminiferous-snappily.ngrok-free.dev