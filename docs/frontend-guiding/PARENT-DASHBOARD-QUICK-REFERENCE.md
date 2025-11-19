# 📱 Parent Dashboard - Quick Reference Guide

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
├── layouts/parent.blade.php       # Base layout
└── parent/
    ├── dashboard.blade.php        # Main dashboard
    ├── students.blade.php         # Students list
    ├── student-detail.blade.php   # Student detail
    ├── grades.blade.php           # Grades view
    ├── attendance.blade.php       # Attendance
    ├── announcements.blade.php    # Announcements
    ├── notifications.blade.php    # Notifications
    └── certificates.blade.php     # Certificates

public/js/parent/api.js            # API helper
```

---

## 📡 API Endpoints Cheat Sheet

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
| `/api/v1/certificates?student_id={id}` | GET | Get certificates |

---

## 🎨 Layout Template (Base)

```blade
<!-- resources/views/layouts/parent.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Parent Dashboard</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar { background: linear-gradient(180deg, #6366f1, #8b5cf6); }
        .notification-badge { position: absolute; top: 5px; right: 5px; background: #ef4444; }
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
// public/js/parent/api.js
const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
const API_BASE = NGROK_BASE + '/api/v1';

function getHeaders() {
    return {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': 'true'
    };
}

const ParentAPI = {
    getCurrentUser: () => fetch(NGROK_BASE + '/api/user', { headers: getHeaders() }).then(r => r.json()),
    getStudents: (parentId) => fetch(`${API_BASE}/parents/${parentId}/students`, { headers: getHeaders() }).then(r => r.json()),
    getStudent: (id) => fetch(`${API_BASE}/students/${id}`, { headers: getHeaders() }).then(r => r.json()),
    getEnrollments: (id) => fetch(`${API_BASE}/students/${id}/enrollments`, { headers: getHeaders() }).then(r => r.json()),
    getGrades: (id) => fetch(`${API_BASE}/grades/student?student_id=${id}`, { headers: getHeaders() }).then(r => r.json()),
    getAttendanceSummary: (id) => fetch(`${API_BASE}/attendance/students/${id}/summary`, { headers: getHeaders() }).then(r => r.json()),
    getAnnouncements: (page) => fetch(`${API_BASE}/announcements/active?page=${page}`, { headers: getHeaders() }).then(r => r.json()),
    getNotifications: (page) => fetch(`${API_BASE}/notifications?page=${page}`, { headers: getHeaders() }).then(r => r.json()),
    getCertificates: (id) => fetch(`${API_BASE}/certificates?student_id=${id}`, { headers: getHeaders() }).then(r => r.json())
};

window.ParentAPI = ParentAPI;
```

---

## 📊 Dashboard Page Template

```blade
@extends('layouts.parent')
@section('title', 'Dashboard')

@section('content')
<h2>Dashboard</h2>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Anak</h6>
                <h2 id="totalStudents">-</h2>
            </div>
        </div>
    </div>
    <!-- More stat cards... -->
</div>

<!-- Students List -->
<div class="card">
    <div class="card-body">
        <div id="studentsList" class="row g-3"></div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
async function loadDashboard() {
    const user = await ParentAPI.getCurrentUser();
    const students = await ParentAPI.getStudents(user.parent.id);
    
    document.getElementById('totalStudents').textContent = students.length;
    
    document.getElementById('studentsList').innerHTML = students.map(s => `
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>${s.full_name}</h5>
                    <a href="/parent/students/${s.id}" class="btn btn-primary btn-sm">View</a>
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

## 👥 Students List Page

```blade
@extends('layouts.parent')
@section('title', 'Anak Saya')

@section('content')
<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="🔍 Cari nama..." onkeyup="filterStudents()">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter" onchange="filterStudents()">
                    <option value="all">Semua Status</option>
                    <option value="active">Active</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="studentsList" class="row g-4"></div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let allStudents = [];

async function loadStudents() {
    const user = await ParentAPI.getCurrentUser();
    allStudents = await ParentAPI.getStudents(user.parent.id);
    renderStudents(allStudents);
}

function filterStudents() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    
    const filtered = allStudents.filter(s => 
        s.full_name.toLowerCase().includes(query) &&
        (status === 'all' || s.status === status)
    );
    
    renderStudents(filtered);
}

function renderStudents(students) {
    document.getElementById('studentsList').innerHTML = students.map(s => `
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>${s.full_name}</h5>
                    <p class="text-muted">${s.enrollments?.length || 0} Courses</p>
                    <a href="/parent/students/${s.id}" class="btn btn-primary w-100">Detail</a>
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', loadStudents);
</script>
@endsection
```

---

## 📄 Student Detail Page

```blade
@extends('layouts.parent')
@section('title', 'Detail Anak')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center" id="studentProfile"></div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Bootstrap Tabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#courses">Courses</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#grades">Grades</button>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="courses">
                <div id="enrollmentsList"></div>
            </div>
            <div class="tab-pane fade" id="grades">
                <div id="gradesList"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
const studentId = {{ $studentId }};

async function loadDetail() {
    const student = await ParentAPI.getStudent(studentId);
    const enrollments = await ParentAPI.getEnrollments(studentId);
    const grades = await ParentAPI.getGrades(studentId);
    
    // Render profile
    document.getElementById('studentProfile').innerHTML = `
        <h4>${student.full_name}</h4>
        <p>${student.gender === 'male' ? 'Laki-laki' : 'Perempuan'}</p>
    `;
    
    // Render enrollments
    document.getElementById('enrollmentsList').innerHTML = enrollments.map(e => `
        <div class="card mb-2">
            <div class="card-body">
                <h6>${e.course?.title || 'N/A'}</h6>
                <span class="badge bg-${e.status === 'active' ? 'success' : 'secondary'}">${e.status}</span>
            </div>
        </div>
    `).join('');
    
    // Render grades
    const gradeData = grades.data || grades;
    document.getElementById('gradesList').innerHTML = gradeData.map(g => `
        <div class="mb-3">
            <strong>${g.grade_component?.name || 'N/A'}:</strong> ${g.score}
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', loadDetail);
</script>
@endsection
```

---

## 📈 Common Patterns

### Loading State
```javascript
document.getElementById('loading').style.display = 'block';
// Load data...
document.getElementById('loading').style.display = 'none';
```

### Error Handling
```javascript
try {
    const data = await ParentAPI.getStudents(parentId);
} catch (error) {
    console.error('Error:', error);
    alert('Gagal memuat data: ' + error.message);
}
```

### Chart.js Example
```javascript
const ctx = document.getElementById('myChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: students.map(s => s.full_name),
        datasets: [{
            data: students.map(s => s.enrollments?.length || 0),
            backgroundColor: ['#6366f1', '#8b5cf6', '#10b981']
        }]
    }
});
```

---

## ⚠️ Important Notes

1. **Always include ngrok-skip-browser-warning header** in all requests
2. **Store auth token in localStorage**: `localStorage.getItem('auth_token')`
3. **Handle 401 responses**: Redirect to login
4. **Use Bootstrap 5.3 classes** for consistent UI
5. **Include Chart.js CDN** for visualizations

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

See: `PARENT-DASHBOARD-GUIDE.md` for detailed implementation of all pages.

---

**Last Updated:** 2024  
**Ngrok URL:** https://loraine-seminiferous-snappily.ngrok-free.dev