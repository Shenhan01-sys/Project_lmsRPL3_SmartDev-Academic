# 📱 Parent Dashboard Frontend Guide

**Version:** 1.0  
**Last Updated:** 2024  
**Backend API:** Ngrok Tunnel

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Setup & Configuration](#setup--configuration)
3. [API Integration](#api-integration)
4. [Pages Implementation](#pages-implementation)
   - [Dashboard](#1-dashboard-page)
   - [Students List](#2-students-list-page)
   - [Student Detail](#3-student-detail-page)
   - [Grades View](#4-grades-view-page)
   - [Attendance](#5-attendance-page)
   - [Announcements](#6-announcements-page)
   - [Notifications](#7-notifications-page)
   - [Certificates](#8-certificates-page)
5. [Common Components](#common-components)
6. [Best Practices](#best-practices)

---

## Overview

### 🎯 What We're Building

A modern parent dashboard for monitoring children's learning progress in SmartDev LMS using:
- **Laravel Blade** templates
- **Bootstrap 5.3** for UI
- **Chart.js** for data visualization
- **Vanilla JavaScript** for API calls
- **Ngrok** for backend API tunneling

### 🔗 Ngrok Configuration

**Base URL:** `https://loraine-seminiferous-snappily.ngrok-free.dev`

All API endpoints follow this pattern:
```
https://loraine-seminiferous-snappily.ngrok-free.dev/api/v1/{endpoint}
```

### 📁 Project Structure

```
resources/views/
├── layouts/
│   └── parent.blade.php          # Base layout
└── parent/
    ├── dashboard.blade.php       # Main dashboard
    ├── students.blade.php        # Students list
    ├── student-detail.blade.php  # Student detail
    ├── grades.blade.php          # Grades view
    ├── attendance.blade.php      # Attendance tracking
    ├── announcements.blade.php   # Announcements
    ├── notifications.blade.php   # Notifications
    └── certificates.blade.php    # Certificates

public/js/parent/
└── api.js                        # API helper functions

routes/
└── web.php                       # Web routes
```

---

## Setup & Configuration

### Step 1: Web Routes

**File:** `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

// Parent Dashboard Routes
Route::prefix('parent')->name('parent.')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('parent.dashboard');
    })->name('dashboard');

    Route::get('/students', function () {
        return view('parent.students');
    })->name('students');

    Route::get('/students/{id}', function ($id) {
        return view('parent.student-detail', ['studentId' => $id]);
    })->name('student.detail');

    Route::get('/grades', function () {
        return view('parent.grades');
    })->name('grades');

    Route::get('/attendance', function () {
        return view('parent.attendance');
    })->name('attendance');

    Route::get('/announcements', function () {
        return view('parent.announcements');
    })->name('announcements');

    Route::get('/notifications', function () {
        return view('parent.notifications');
    })->name('notifications');

    Route::get('/certificates', function () {
        return view('parent.certificates');
    })->name('certificates');
});
```

### Step 2: Base Layout

**File:** `resources/views/layouts/parent.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Parent Dashboard</title>

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
    </style>
    @yield('extra-css')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar --}}
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
                <div class="position-sticky">
                    <div class="text-center mb-4 pt-3">
                        <h4 class="text-white fw-bold">SmartDev LMS</h4>
                        <p class="text-white-50 small">Parent Dashboard</p>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"
                               href="{{ route('parent.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.students*') ? 'active' : '' }}"
                               href="{{ route('parent.students') }}">
                                <i class="bi bi-people me-2"></i> Anak Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.grades') ? 'active' : '' }}"
                               href="{{ route('parent.grades') }}">
                                <i class="bi bi-graph-up me-2"></i> Nilai
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.attendance') ? 'active' : '' }}"
                               href="{{ route('parent.attendance') }}">
                                <i class="bi bi-calendar-check me-2"></i> Kehadiran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.announcements') ? 'active' : '' }}"
                               href="{{ route('parent.announcements') }}">
                                <i class="bi bi-megaphone me-2"></i> Pengumuman
                            </a>
                        </li>
                        <li class="nav-item position-relative">
                            <a class="nav-link {{ request()->routeIs('parent.notifications') ? 'active' : '' }}"
                               href="{{ route('parent.notifications') }}">
                                <i class="bi bi-bell me-2"></i> Notifikasi
                                <span id="notifBadge" class="notification-badge d-none">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parent.certificates') ? 'active' : '' }}"
                               href="{{ route('parent.certificates') }}">
                                <i class="bi bi-award me-2"></i> Sertifikat
                            </a>
                        </li>
                    </ul>

                    <hr class="text-white-50 my-4">

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="event.preventDefault(); logout();">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            {{-- Main Content --}}
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    {{-- Base Config --}}
    <script>
        // Ngrok Configuration
        const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
        const API_BASE = NGROK_BASE + '/api/v1';
        const API_TOKEN = localStorage.getItem('auth_token');

        function logout() {
            if(confirm('Apakah Anda yakin ingin logout?')) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }

        // Check unread notifications
        async function checkNotifications() {
            if (!API_TOKEN) return;
            
            try {
                const response = await fetch(API_BASE + '/notifications/counts', {
                    headers: {
                        'Authorization': 'Bearer ' + API_TOKEN,
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    const badge = document.getElementById('notifBadge');
                    if (data.unread > 0) {
                        badge.textContent = data.unread;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                }
            } catch (error) {
                console.error('Error checking notifications:', error);
            }
        }

        // Initialize
        if (API_TOKEN) {
            checkNotifications();
            setInterval(checkNotifications, 60000); // Check every minute
        }
    </script>

    @yield('extra-js')
</body>
</html>
```

---

## API Integration

### Step 3: API Helper

**File:** `public/js/parent/api.js`

```javascript
/**
 * Parent Dashboard API Helper
 * Handles all API calls with Ngrok authentication
 */

const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev';
const API_BASE = NGROK_BASE + '/api/v1';

// Get token from localStorage
function getToken() {
    return localStorage.getItem('auth_token');
}

// Default headers with auth and ngrok bypass
function getHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${getToken()}`,
        'ngrok-skip-browser-warning': 'true' // Bypass ngrok browser warning
    };
}

// Handle response and errors
async function handleResponse(response) {
    if (!response.ok) {
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
            throw new Error('Unauthorized - redirecting to login');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
    }
    return await response.json();
}

// API Functions
const ParentAPI = {

    /**
     * Get current authenticated user
     */
    async getCurrentUser() {
        const res = await fetch(NGROK_BASE + '/api/user', {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get parent's students/children
     */
    async getStudents(parentId) {
        const res = await fetch(`${API_BASE}/parents/${parentId}/students`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student detail by ID
     */
    async getStudent(studentId) {
        const res = await fetch(`${API_BASE}/students/${studentId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student's enrollments
     */
    async getEnrollments(studentId) {
        const res = await fetch(`${API_BASE}/students/${studentId}/enrollments`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student's submissions
     */
    async getSubmissions(studentId) {
        const res = await fetch(`${API_BASE}/students/${studentId}/submissions`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student's grades
     */
    async getGrades(studentId) {
        const res = await fetch(`${API_BASE}/grades/student?student_id=${studentId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get course detail
     */
    async getCourse(courseId) {
        const res = await fetch(`${API_BASE}/courses/${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student attendance summary
     */
    async getAttendanceSummary(studentId, courseId = null) {
        let url = `${API_BASE}/attendance/students/${studentId}/summary`;
        if (courseId) url += `?course_id=${courseId}`;
        
        const res = await fetch(url, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get active announcements
     */
    async getAnnouncements(page = 1) {
        const res = await fetch(`${API_BASE}/announcements/active?page=${page}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get course announcements
     */
    async getCourseAnnouncements(courseId, page = 1) {
        const res = await fetch(`${API_BASE}/announcements/course/${courseId}?page=${page}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get user notifications
     */
    async getNotifications(page = 1) {
        const res = await fetch(`${API_BASE}/notifications?page=${page}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Mark notification as read
     */
    async markNotificationRead(notificationId) {
        const res = await fetch(`${API_BASE}/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Mark all notifications as read
     */
    async markAllNotificationsRead() {
        const res = await fetch(`${API_BASE}/notifications/read-all`, {
            method: 'POST',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get notification counts
     */
    async getNotificationCounts() {
        const res = await fetch(`${API_BASE}/notifications/counts`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get student certificates
     */
    async getCertificates(studentId) {
        const res = await fetch(`${API_BASE}/certificates?student_id=${studentId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Verify certificate by code
     */
    async verifyCertificate(code) {
        const res = await fetch(`${API_BASE}/certificates/verify/${code}`, {
            headers: {
                'Accept': 'application/json',
                'ngrok-skip-browser-warning': 'true'
            }
        });
        return handleResponse(res);
    }
};

// Export for use in Blade templates
if (typeof window !== 'undefined') {
    window.ParentAPI = ParentAPI;
}
```

### API Endpoints Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user` | Get current authenticated user |
| GET | `/api/v1/parents/{id}/students` | Get parent's children list |
| GET | `/api/v1/students/{id}` | Get student detail |
| GET | `/api/v1/students/{id}/enrollments` | Get student's enrollments |
| GET | `/api/v1/students/{id}/submissions` | Get student's submissions |
| GET | `/api/v1/grades/student?student_id={id}` | Get student's grades |
| GET | `/api/v1/attendance/students/{id}/summary` | Get student attendance summary |
| GET | `/api/v1/announcements/active` | Get active announcements |
| GET | `/api/v1/announcements/course/{id}` | Get course announcements |
| GET | `/api/v1/notifications` | Get user notifications |
| POST | `/api/v1/notifications/{id}/read` | Mark notification as read |
| POST | `/api/v1/notifications/read-all` | Mark all notifications as read |
| GET | `/api/v1/notifications/counts` | Get notification counts |
| GET | `/api/v1/certificates?student_id={id}` | Get student certificates |
| GET | `/api/v1/certificates/verify/{code}` | Verify certificate by code |

---

## Pages Implementation

### 1. Dashboard Page

**File:** `resources/views/parent/dashboard.blade.php`

```blade
@extends('layouts.parent')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Selamat datang kembali!</p>
    </div>
    <button class="btn btn-primary" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
</div>

{{-- Statistics Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Anak</p>
                        <h3 id="totalStudents" class="fw-bold mb-0">-</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Enrollments</p>
                        <h3 id="totalEnrollments" class="fw-bold mb-0">-</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-book fs-3 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Rata-rata Nilai</p>
                        <h3 id="avgGrade" class="fw-bold mb-0">-</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-graph-up fs-3 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Kehadiran Rata-rata</p>
                        <h3 id="avgAttendance" class="fw-bold mb-0">-</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-check fs-3 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Students List --}}
<div class="card mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Anak-Anak Saya</h5>
    </div>
    <div class="card-body">
        <div id="studentsLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat data...</p>
        </div>
        <div id="studentsList" class="row g-3" style="display:none"></div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Progress Overview</h5>
            </div>
            <div class="card-body">
                <canvas id="progressChart" style="max-height: 300px"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Recent Announcements</h5>
            </div>
            <div class="card-body">
                <div id="recentAnnouncements">
                    <p class="text-muted">Loading...</p>
                </div>
                <a href="{{ route('parent.announcements') }}" class="btn btn-sm btn-outline-primary mt-3">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let parentId = null;
let studentsData = [];

// Load dashboard data
async function loadDashboard() {
    try {
        // Get current user
        const user = await ParentAPI.getCurrentUser();
        parentId = user.parent ? user.parent.id : null;

        if (!parentId) {
            showError('Parent data not found');
            return;
        }

        // Get students
        const students = await ParentAPI.getStudents(parentId);
        studentsData = students;

        // Update statistics
        await updateStats(students);

        // Render students cards
        renderStudents(students);

        // Load chart
        loadChart(students);

        // Load recent announcements
        loadRecentAnnouncements();

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data: ' + error.message);
    }
}

// Update statistics
async function updateStats(students) {
    let totalEnrollments = 0;
    let totalGrades = 0;
    let gradeCount = 0;
    let totalAttendance = 0;
    let attendanceCount = 0;

    // Calculate enrollments
    students.forEach(s => {
        if (s.enrollments) totalEnrollments += s.enrollments.length;
    });

    // Get grades and attendance for each student
    for (const student of students) {
        try {
            // Get grades
            const gradesData = await ParentAPI.getGrades(student.id);
            if (gradesData.data && gradesData.data.length > 0) {
                gradesData.data.forEach(g => {
                    totalGrades += parseFloat(g.score);
                    gradeCount++;
                });
            }

            // Get attendance
            const attendanceData = await ParentAPI.getAttendanceSummary(student.id);
            if (attendanceData.summary && attendanceData.summary.length > 0) {
                attendanceData.summary.forEach(a => {
                    totalAttendance += a.percentage;
                    attendanceCount++;
                });
            }
        } catch (error) {
            console.error('Error fetching student data:', error);
        }
    }

    // Update UI
    document.getElementById('totalStudents').textContent = students.length;
    document.getElementById('totalEnrollments').textContent = totalEnrollments;
    document.getElementById('avgGrade').textContent = 
        gradeCount > 0 ? (totalGrades / gradeCount).toFixed(1) : 'N/A';
    document.getElementById('avgAttendance').textContent = 
        attendanceCount > 0 ? (totalAttendance / attendanceCount).toFixed(1) + '%' : 'N/A';
}

// Render students cards
function renderStudents(students) {
    const container = document.getElementById('studentsList');
    container.innerHTML = '';

    if (students.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted text-center">Belum ada data anak.</p></div>';
        document.getElementById('studentsLoading').style.display = 'none';
        container.style.display = 'block';
        return;
    }

    students.forEach(student => {
        const enrollCount = student.enrollments ? student.enrollments.length : 0;
        const statusBadge = student.status === 'active' 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">' + student.status + '</span>';

        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-person fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0 fw-bold">${student.full_name}</h5>
                                <small class="text-muted">
                                    <i class="bi bi-${student.gender === 'male' ? 'gender-male' : 'gender-female'}"></i>
                                    ${student.gender === 'male' ? 'Laki-laki' : 'Perempuan'}
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary">
                                <i class="bi bi-book me-1"></i> ${enrollCount} Course${enrollCount !== 1 ? 's' : ''}
                            </span>
                            ${statusBadge}
                        </div>
                        <a href="{{ url('parent/students') }}/${student.id}" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-eye me-1"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += card;
    });

    document.getElementById('studentsLoading').style.display = 'none';
    container.style.display = 'flex';
}

// Load chart
function loadChart(students) {
    if (students.length === 0) return;

    const ctx = document.getElementById('progressChart');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: students.map(s => s.full_name),
            datasets: [{
                label: 'Enrollments',
                data: students.map(s => s.enrollments ? s.enrollments.length : 0),
                backgroundColor: [
                    '#6366f1',
                    '#8b5cf6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#06b6d4'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Load recent announcements
async function loadRecentAnnouncements() {
    try {
        const data = await ParentAPI.getAnnouncements(1);
        const container = document.getElementById('recentAnnouncements');

        if (!data.data || data.data.length === 0) {
            container.innerHTML = '<p class="text-muted">Belum ada pengumuman.</p>';
            return;
        }

        let html = '<div class="list-group">';
        data.data.slice(0, 3).forEach(announcement => {
            const date = new Date(announcement.published_at).toLocaleDateString('id-ID');
            html += `
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <h6 class="mb-1">${announcement.title}</h6>
                        <small class="text-muted">${date}</small>
                    </div>
                    <p class="mb-1 small text-muted">${announcement.content.substring(0, 80)}...</p>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading announcements:', error);
        document.getElementById('recentAnnouncements').innerHTML = 
            '<p class="text-danger small">Gagal memuat pengumuman.</p>';
    }
}

// Show error
function showError(message) {
    document.getElementById('studentsLoading').innerHTML = 
        `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        </div>`;
}

// Initialize
document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection
```

---

### 2. Students List Page

**File:** `resources/views/parent/students.blade.php`

```blade
@extends('layouts.parent')

@section('title', 'Anak Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Anak-Anak Saya</h2>
        <p class="text-muted mb-0">Daftar dan informasi anak</p>
    </div>
</div>

{{-- Search and Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="🔍 Cari nama anak..." onkeyup="filterStudents()">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter" onchange="filterStudents()">
                    <option value="all">Semua Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sortBy" onchange="sortStudents()">
                    <option value="name">Urutkan: Nama</option>
                    <option value="enrollments">Urutkan: Enrollments</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Students Grid --}}
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">Memuat data anak...</p>
</div>

<div id="studentsList" class="row g-4" style="display:none"></div>

<div id="emptyState" class="card text-center py-5" style="display:none">
    <div class="card-body">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <p class="text-muted mt-3">Tidak ada anak yang ditemukan</p>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let allStudents = [];
let filteredStudents = [];

// Load students
async function loadStudents() {
    try {
        const user = await ParentAPI.getCurrentUser();
        const parentId = user.parent ? user.parent.id : null;

        if (!parentId) {
            throw new Error('Parent data not found');
        }

        allStudents = await ParentAPI.getStudents(parentId);
        filteredStudents = [...allStudents];

        renderStudents();

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('loadingIndicator').innerHTML = 
            `<div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Gagal memuat data: ${error.message}
            </div>`;
    }
}

// Render students
function renderStudents() {
    const container = document.getElementById('studentsList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredStudents.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    container.style.display = 'flex';
    container.innerHTML = '';

    filteredStudents.forEach(student => {
        const enrollCount = student.enrollments ? student.enrollments.length : 0;
        const statusClass = student.status === 'active' ? 'success' : 'secondary';

        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-person fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0 fw-bold">${student.full_name}</h5>
                                <small class="text-muted">
                                    <i class="bi bi-${student.gender === 'male' ? 'gender-male' : 'gender-female'}"></i>
                                    ${student.gender === 'male' ? 'Laki-laki' : 'Perempuan'}
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Tanggal Lahir</small>
                                <small>${new Date(student.birth_date).toLocaleDateString('id-ID')}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Enrollments</small>
                                <span class="badge bg-primary">${enrollCount}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge bg-${statusClass}">${student.status}</span>
                        </div>
                        
                        <a href="{{ url('parent/students') }}/${student.id}" 
                           class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-eye me-1"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += card;
    });
}

// Filter students
function filterStudents() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;

    filteredStudents = allStudents.filter(student => {
        const matchesSearch = student.full_name.toLowerCase().includes(searchQuery);
        const matchesStatus = statusFilter === 'all' || student.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    renderStudents();
}

// Sort students
function sortStudents() {
    const sortBy = document.getElementById('sortBy').value;

    if (sortBy === 'name') {
        filteredStudents.sort((a, b) => a.full_name.localeCompare(b.full_name));
    } else if (sortBy === 'enrollments') {
        filteredStudents.sort((a, b) => {
            const aCount = a.enrollments ? a.enrollments.length : 0;
            const bCount = b.enrollments ? b.enrollments.length : 0;
            return bCount - aCount;
        });
    }

    renderStudents();
}

// Initialize
document.addEventListener('DOMContentLoaded', loadStudents);
</script>
@endsection
```

---

### 3. Student Detail Page

**File:** `resources/views/parent/student-detail.blade.php`

```blade
@extends('layouts.parent')

@section('title', 'Detail Anak')

@section('content')
<div class="mb-4">
    <a href="{{ route('parent.students') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h2 class="fw-bold mb-0" id="studentName">Loading...</h2>
</div>

<div class="row g-4 mb-4">
    {{-- Student Profile Card --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-flex mb-3">
                    <i class="bi bi-person fs-1 text-primary"></i>
                </div>
                <h4 id="studentFullName" class="mb-2">-</h4>
                <p class="text-muted" id="studentGender">-</p>
                <hr>
                <div class="text-start">
                    <div class="mb-2">
                        <strong>Tanggal Lahir:</strong><br>
                        <span id="studentBirth">-</span>
                    </div>
                    <div>
                        <strong>Status:</strong><br>
                        <span id="studentStatus" class="badge bg-success">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Quick Stats</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Courses</span>
                    <strong id="totalCourses">-</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Avg Grade</span>
                    <strong id="avgGrade">-</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Attendance</span>
                    <strong id="avgAttendance">-</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Enrollments & Info --}}
    <div class="col-md-8">
        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" 
                        data-bs-target="#courses" type="button">
                    <i class="bi bi-book me-1"></i> Courses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="grades-tab" data-bs-toggle="tab" 
                        data-bs-target="#grades" type="button">
                    <i class="bi bi-star me-1"></i> Grades
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="submissions-tab" data-bs-toggle="tab" 
                        data-bs-target="#submissions" type="button">
                    <i class="bi bi-clipboard-check me-1"></i> Submissions
                </button>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="detailTabsContent">
            {{-- Courses Tab --}}
            <div class="tab-pane fade show active" id="courses" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div id="enrollmentsList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grades Tab --}}
            <div class="tab-pane fade" id="grades" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div id="gradesList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submissions Tab --}}
            <div class="tab-pane fade" id="submissions" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div id="submissionsList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
const studentId = {{ $studentId }};
let studentData = null;

async function loadStudentDetail() {
    try {
        // Get student data
        studentData = await ParentAPI.getStudent(studentId);

        // Update profile
        document.getElementById('studentName').textContent = studentData.full_name;
        document.getElementById('studentFullName').textContent = studentData.full_name;
        document.getElementById('studentGender').textContent = 
            studentData.gender === 'male' ? 'Laki-laki' : 'Perempuan';
        document.getElementById('studentBirth').textContent = 
            new Date(studentData.birth_date).toLocaleDateString('id-ID');
        document.getElementById('studentStatus').textContent = studentData.status;
        document.getElementById('studentStatus').className = 
            `badge bg-${studentData.status === 'active' ? 'success' : 'secondary'}`;

        // Load all data
        await Promise.all([
            loadEnrollments(),
            loadGrades(),
            loadSubmissions(),
            loadQuickStats()
        ]);

    } catch (error) {
        console.error('Error:', error);
        alert('Gagal memuat data anak: ' + error.message);
    }
}

async function loadEnrollments() {
    try {
        const enrollments = await ParentAPI.getEnrollments(studentId);
        const container = document.getElementById('enrollmentsList');

        if (!enrollments || enrollments.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">Belum ada enrollment</p>';
            return;
        }

        container.innerHTML = enrollments.map(e => `
            <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                <div>
                    <h6 class="mb-1">${e.course ? e.course.title : 'N/A'}</h6>
                    <small class="text-muted">
                        <i class="bi bi-calendar-event"></i>
                        Enrolled: ${new Date(e.enrolled_at).toLocaleDateString('id-ID')}
                    </small>
                </div>
                <span class="badge bg-${e.status === 'active' ? 'success' : 'secondary'}">
                    ${e.status}
                </span>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading enrollments:', error);
        document.getElementById('enrollmentsList').innerHTML = 
            '<p class="text-danger small">Gagal memuat data</p>';
    }
}

async function loadGrades() {
    try {
        const data = await ParentAPI.getGrades(studentId);
        const grades = data.data || data;
        const container = document.getElementById('gradesList');

        if (!grades || grades.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">Belum ada nilai</p>';
            return;
        }

        container.innerHTML = grades.map(g => `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${g.grade_component ? g.grade_component.name : 'N/A'}</h6>
                    <small class="text-muted">${g.feedback || 'No feedback'}</small>
                    ${g.enrollment && g.enrollment.course ? 
                        `<br><small class="text-muted"><i class="bi bi-book"></i> ${g.enrollment.course.title}</small>` 
                        : ''}
                </div>
                <span class="badge bg-primary fs-6">${g.score}</span>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading grades:', error);
        document.getElementById('gradesList').innerHTML = 
            '<p class="text-danger small">Gagal memuat data</p>';
    }
}

async function loadSubmissions() {
    try {
        const submissions = await ParentAPI.getSubmissions(studentId);
        const container = document.getElementById('submissionsList');

        if (!submissions || submissions.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">Belum ada submission</p>';
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Assignment</th>
                            <th>Tanggal Submit</th>
                            <th>Status</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${submissions.map(s => `
                            <tr>
                                <td>${s.assignment ? s.assignment.title : 'N/A'}</td>
                                <td>${new Date(s.submission_date).toLocaleDateString('id-ID')}</td>
                                <td><span class="badge bg-${getStatusColor(s.status)}">${s.status}</span></td>
                                <td><strong>${s.score || '-'}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error('Error loading submissions:', error);
        document.getElementById('submissionsList').innerHTML = 
            '<p class="text-danger small">Gagal memuat data</p>';
    }
}

async function loadQuickStats() {
    try {
        // Total courses
        const enrollments = await ParentAPI.getEnrollments(studentId);
        document.getElementById('totalCourses').textContent = enrollments.length;

        // Avg grade
        const gradesData = await ParentAPI.getGrades(studentId);
        const grades = gradesData.data || gradesData;
        if (grades.length > 0) {
            const sum = grades.reduce((acc, g) => acc + parseFloat(g.score), 0);
            const avg = (sum / grades.length).toFixed(1);
            document.getElementById('avgGrade').textContent = avg;
        } else {
            document.getElementById('avgGrade').textContent = 'N/A';
        }

        // Avg attendance
        const attendanceData = await ParentAPI.getAttendanceSummary(studentId);
        if (attendanceData.summary && attendanceData.summary.length > 0) {
            const sum = attendanceData.summary.reduce((acc, a) => acc + a.percentage, 0);
            const avg = (sum / attendanceData.summary.length).toFixed(1);
            document.getElementById('avgAttendance').textContent = avg + '%';
        } else {
            document.getElementById('avgAttendance').textContent = 'N/A';
        }

    } catch (error) {
        console.error('Error loading quick stats:', error);
    }
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'graded': 'success',
        'late': 'danger',
        'submitted': 'info'
    };
    return colors[status] || 'secondary';
}

// Initialize
document.addEventListener('DOMContentLoaded', loadStudentDetail);
</script>
@endsection
```

---

### 4. Grades View Page

**File:** `resources/views/parent/grades.blade.php`

```blade
@extends('layouts.parent')

@section('title', 'Nilai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Nilai</h2>
        <p class="text-muted mb-0">Lihat nilai semua anak</p>
    </div>
</div>

{{-- Student Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Pilih Anak</label>
                <select class="form-select" id="studentFilter" onchange="loadGrades()">
                    <option value="">Loading...</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Pilih Course</label>
                <select class="form-select" id="courseFilter" onchange="filterGrades()">
                    <option value="all">Semua Course</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Grades Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Rata-rata Nilai</h6>
                <h2 class="fw-bold text-primary" id="avgGrade">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Nilai Tertinggi</h6>
                <h2 class="fw-bold text-success" id="maxGrade">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Nilai Terendah</h6>
                <h2 class="fw-bold text-danger" id="minGrade">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Nilai</h6>
                <h2 class="fw-bold" id="totalGrades">-</h2>
            </div>
        </div>
    </div>
</div>

{{-- Grades List --}}
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Daftar Nilai</h5>
    </div>
    <div class="card-body">
        <div id="loadingIndicator" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data nilai...</p>
        </div>

        <div id="gradesList" style="display:none"></div>

        <div id="emptyState" style="display:none" class="text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="text-muted mt-3">Belum ada nilai</p>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let allGrades = [];
let filteredGrades = [];
let students = [];

async function initialize() {
    try {
        // Get user and students
        const user = await ParentAPI.getCurrentUser();
        const parentId = user.parent ? user.parent.id : null;

        if (!parentId) throw new Error('Parent data not found');

        students = await ParentAPI.getStudents(parentId);

        // Populate student filter
        const studentFilter = document.getElementById('studentFilter');
        studentFilter.innerHTML = students.map(s => 
            `<option value="${s.id}">${s.full_name}</option>`
        ).join('');

        // Load grades for first student
        if (students.length > 0) {
            await loadGrades();
        }

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data: ' + error.message);
    }
}

async function loadGrades() {
    const studentId = document.getElementById('studentFilter').value;
    if (!studentId) return;

    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('gradesList').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    try {
        const data = await ParentAPI.getGrades(studentId);
        allGrades = data.data || data;

        // Get unique courses for filter
        const courses = [...new Set(allGrades
            .filter(g => g.enrollment && g.enrollment.course)
            .map(g => JSON.stringify({
                id: g.enrollment.course.id, 
                title: g.enrollment.course.title
            }))
        )].map(c => JSON.parse(c));

        const courseFilter = document.getElementById('courseFilter');
        courseFilter.innerHTML = '<option value="all">Semua Course</option>' +
            courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('');

        filteredGrades = [...allGrades];
        renderGrades();
        updateStats();

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat nilai: ' + error.message);
    }
}

function filterGrades() {
    const courseId = document.getElementById('courseFilter').value;

    if (courseId === 'all') {
        filteredGrades = [...allGrades];
    } else {
        filteredGrades = allGrades.filter(g => 
            g.enrollment && g.enrollment.course && 
            g.enrollment.course.id == courseId
        );
    }

    renderGrades();
    updateStats();
}

function renderGrades() {
    const container = document.getElementById('gradesList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredGrades.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    container.style.display = 'block';

    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Component</th>
                        <th>Weight</th>
                        <th>Score</th>
                        <th>Feedback</th>
                        <th>Graded At</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredGrades.map(g => `
                        <tr>
                            <td>${g.enrollment && g.enrollment.course ? g.enrollment.course.title : 'N/A'}</td>
                            <td>${g.grade_component ? g.grade_component.name : 'N/A'}</td>
                            <td>${g.grade_component ? g.grade_component.weight + '%' : '-'}</td>