# 👨‍🏫 Instructor Dashboard Frontend Guide

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
   - [My Courses](#2-my-courses-page)
   - [Course Detail](#3-course-detail-page)
   - [Assignments](#4-assignments-page)
   - [Submissions](#5-submissions-page)
   - [Grading](#6-grading-page)
   - [Attendance](#7-attendance-page)
   - [Students](#8-students-page)
   - [Announcements](#9-announcements-page)
   - [Certificates](#10-certificates-page)
5. [Common Components](#common-components)
6. [Best Practices](#best-practices)

---

## Overview

### 🎯 What We're Building

A comprehensive instructor dashboard for managing courses, students, assignments, and grading in SmartDev LMS using:
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
│   └── instructor.blade.php       # Base layout
└── instructor/
    ├── dashboard.blade.php        # Main dashboard
    ├── courses.blade.php          # My courses list
    ├── course-detail.blade.php    # Course detail & management
    ├── assignments.blade.php      # Assignments management
    ├── submissions.blade.php      # Review submissions
    ├── grading.blade.php          # Grading interface
    ├── attendance.blade.php       # Attendance management
    ├── students.blade.php         # Students in my courses
    ├── announcements.blade.php    # Create/manage announcements
    └── certificates.blade.php     # Generate certificates

public/js/instructor/
└── api.js                         # API helper functions

routes/
└── web.php                        # Web routes
```

---

## Setup & Configuration

### Step 1: Web Routes

**File:** `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

// Instructor Dashboard Routes
Route::prefix('instructor')->name('instructor.')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('instructor.dashboard');
    })->name('dashboard');

    Route::get('/courses', function () {
        return view('instructor.courses');
    })->name('courses');

    Route::get('/courses/{id}', function ($id) {
        return view('instructor.course-detail', ['courseId' => $id]);
    })->name('course.detail');

    Route::get('/assignments', function () {
        return view('instructor.assignments');
    })->name('assignments');

    Route::get('/submissions', function () {
        return view('instructor.submissions');
    })->name('submissions');

    Route::get('/grading', function () {
        return view('instructor.grading');
    })->name('grading');

    Route::get('/attendance', function () {
        return view('instructor.attendance');
    })->name('attendance');

    Route::get('/students', function () {
        return view('instructor.students');
    })->name('students');

    Route::get('/announcements', function () {
        return view('instructor.announcements');
    })->name('announcements');

    Route::get('/certificates', function () {
        return view('instructor.certificates');
    })->name('certificates');
});
```

### Step 2: Base Layout

**File:** `resources/views/layouts/instructor.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Instructor Dashboard</title>

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
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
                        <p class="text-white-50 small">Instructor Dashboard</p>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}"
                               href="{{ route('instructor.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.courses*') ? 'active' : '' }}"
                               href="{{ route('instructor.courses') }}">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.assignments') ? 'active' : '' }}"
                               href="{{ route('instructor.assignments') }}">
                                <i class="bi bi-journal-text me-2"></i> Assignments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.submissions') ? 'active' : '' }}"
                               href="{{ route('instructor.submissions') }}">
                                <i class="bi bi-file-earmark-check me-2"></i> Submissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.grading') ? 'active' : '' }}"
                               href="{{ route('instructor.grading') }}">
                                <i class="bi bi-star me-2"></i> Grading
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.attendance') ? 'active' : '' }}"
                               href="{{ route('instructor.attendance') }}">
                                <i class="bi bi-calendar-check me-2"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.students') ? 'active' : '' }}"
                               href="{{ route('instructor.students') }}">
                                <i class="bi bi-people me-2"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.announcements') ? 'active' : '' }}"
                               href="{{ route('instructor.announcements') }}">
                                <i class="bi bi-megaphone me-2"></i> Announcements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('instructor.certificates') ? 'active' : '' }}"
                               href="{{ route('instructor.certificates') }}">
                                <i class="bi bi-award me-2"></i> Certificates
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

        // Check pending submissions
        async function checkPendingSubmissions() {
            if (!API_TOKEN) return;
            
            try {
                const response = await fetch(API_BASE + '/submissions/pending-count', {
                    headers: {
                        'Authorization': 'Bearer ' + API_TOKEN,
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    // Update badge if needed
                    console.log('Pending submissions:', data.count);
                }
            } catch (error) {
                console.error('Error checking submissions:', error);
            }
        }

        // Initialize
        if (API_TOKEN) {
            checkPendingSubmissions();
            setInterval(checkPendingSubmissions, 120000); // Check every 2 minutes
        }
    </script>

    @yield('extra-js')
</body>
</html>
```

---

## API Integration

### Step 3: API Helper

**File:** `public/js/instructor/api.js`

```javascript
/**
 * Instructor Dashboard API Helper
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
        'ngrok-skip-browser-warning': 'true'
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
const InstructorAPI = {

    /**
     * Get current authenticated user
     */
    async getCurrentUser() {
        const res = await fetch(NGROK_BASE + '/api/user', {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== COURSES ====================
    
    /**
     * Get instructor's courses
     */
    async getCourses(instructorId) {
        const res = await fetch(`${API_BASE}/instructors/${instructorId}/courses`, {
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
     * Create new course
     */
    async createCourse(courseData) {
        const res = await fetch(`${API_BASE}/courses`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(courseData)
        });
        return handleResponse(res);
    },

    /**
     * Update course
     */
    async updateCourse(courseId, courseData) {
        const res = await fetch(`${API_BASE}/courses/${courseId}`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify(courseData)
        });
        return handleResponse(res);
    },

    /**
     * Delete course
     */
    async deleteCourse(courseId) {
        const res = await fetch(`${API_BASE}/courses/${courseId}`, {
            method: 'DELETE',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== ENROLLMENTS ====================

    /**
     * Get course enrollments
     */
    async getCourseEnrollments(courseId) {
        const res = await fetch(`${API_BASE}/courses/${courseId}/enrollments`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== ASSIGNMENTS ====================

    /**
     * Get course assignments
     */
    async getCourseAssignments(courseId) {
        const res = await fetch(`${API_BASE}/courses/${courseId}/assignments`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get all instructor's assignments
     */
    async getAllAssignments(page = 1) {
        const res = await fetch(`${API_BASE}/assignments?page=${page}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Create assignment
     */
    async createAssignment(assignmentData) {
        const res = await fetch(`${API_BASE}/assignments`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(assignmentData)
        });
        return handleResponse(res);
    },

    /**
     * Update assignment
     */
    async updateAssignment(assignmentId, assignmentData) {
        const res = await fetch(`${API_BASE}/assignments/${assignmentId}`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify(assignmentData)
        });
        return handleResponse(res);
    },

    /**
     * Delete assignment
     */
    async deleteAssignment(assignmentId) {
        const res = await fetch(`${API_BASE}/assignments/${assignmentId}`, {
            method: 'DELETE',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== SUBMISSIONS ====================

    /**
     * Get assignment submissions
     */
    async getAssignmentSubmissions(assignmentId) {
        const res = await fetch(`${API_BASE}/assignments/${assignmentId}/submissions`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Get submission detail
     */
    async getSubmission(submissionId) {
        const res = await fetch(`${API_BASE}/submissions/${submissionId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Grade submission
     */
    async gradeSubmission(submissionId, score, feedback) {
        const res = await fetch(`${API_BASE}/submissions/${submissionId}/grade`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ score, feedback })
        });
        return handleResponse(res);
    },

    // ==================== GRADING ====================

    /**
     * Get course grades
     */
    async getCourseGrades(courseId) {
        const res = await fetch(`${API_BASE}/grades/course/${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Create grade
     */
    async createGrade(gradeData) {
        const res = await fetch(`${API_BASE}/grades`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(gradeData)
        });
        return handleResponse(res);
    },

    /**
     * Update grade
     */
    async updateGrade(gradeId, gradeData) {
        const res = await fetch(`${API_BASE}/grades/${gradeId}`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify(gradeData)
        });
        return handleResponse(res);
    },

    /**
     * Get grade components for course
     */
    async getGradeComponents(courseId) {
        const res = await fetch(`${API_BASE}/grade-components/course/${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Create grade component
     */
    async createGradeComponent(componentData) {
        const res = await fetch(`${API_BASE}/grade-components`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(componentData)
        });
        return handleResponse(res);
    },

    // ==================== ATTENDANCE ====================

    /**
     * Get course attendance sessions
     */
    async getCourseAttendanceSessions(courseId) {
        const res = await fetch(`${API_BASE}/attendance/sessions/course/${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Create attendance session
     */
    async createAttendanceSession(sessionData) {
        const res = await fetch(`${API_BASE}/attendance/sessions`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(sessionData)
        });
        return handleResponse(res);
    },

    /**
     * Open attendance session
     */
    async openAttendanceSession(sessionId) {
        const res = await fetch(`${API_BASE}/attendance/sessions/${sessionId}/open`, {
            method: 'POST',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Close attendance session
     */
    async closeAttendanceSession(sessionId) {
        const res = await fetch(`${API_BASE}/attendance/sessions/${sessionId}/close`, {
            method: 'POST',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Mark student attendance
     */
    async markAttendance(sessionId, studentId, status) {
        const res = await fetch(`${API_BASE}/attendance/records`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                session_id: sessionId,
                student_id: studentId,
                status: status
            })
        });
        return handleResponse(res);
    },

    /**
     * Get session attendance records
     */
    async getSessionAttendance(sessionId) {
        const res = await fetch(`${API_BASE}/attendance/sessions/${sessionId}/records`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== ANNOUNCEMENTS ====================

    /**
     * Get course announcements
     */
    async getCourseAnnouncements(courseId) {
        const res = await fetch(`${API_BASE}/announcements/course/${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Create announcement
     */
    async createAnnouncement(announcementData) {
        const res = await fetch(`${API_BASE}/announcements`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(announcementData)
        });
        return handleResponse(res);
    },

    /**
     * Update announcement
     */
    async updateAnnouncement(announcementId, announcementData) {
        const res = await fetch(`${API_BASE}/announcements/${announcementId}`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify(announcementData)
        });
        return handleResponse(res);
    },

    /**
     * Delete announcement
     */
    async deleteAnnouncement(announcementId) {
        const res = await fetch(`${API_BASE}/announcements/${announcementId}`, {
            method: 'DELETE',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    /**
     * Publish announcement
     */
    async publishAnnouncement(announcementId) {
        const res = await fetch(`${API_BASE}/announcements/${announcementId}/publish`, {
            method: 'POST',
            headers: getHeaders()
        });
        return handleResponse(res);
    },

    // ==================== CERTIFICATES ====================

    /**
     * Generate certificate for student
     */
    async generateCertificate(studentId, courseId) {
        const res = await fetch(`${API_BASE}/certificates/generate`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                student_id: studentId,
                course_id: courseId
            })
        });
        return handleResponse(res);
    },

    /**
     * Bulk generate certificates
     */
    async bulkGenerateCertificates(courseId) {
        const res = await fetch(`${API_BASE}/certificates/bulk-generate`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ course_id: courseId })
        });
        return handleResponse(res);
    },

    /**
     * Get course certificates
     */
    async getCourseCertificates(courseId) {
        const res = await fetch(`${API_BASE}/certificates?course_id=${courseId}`, {
            headers: getHeaders()
        });
        return handleResponse(res);
    }
};

// Export for use in Blade templates
if (typeof window !== 'undefined') {
    window.InstructorAPI = InstructorAPI;
}
```

### API Endpoints Reference

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/user` | GET | Get current user |
| `/api/v1/instructors/{id}/courses` | GET | Get instructor's courses |
| `/api/v1/courses` | POST | Create course |
| `/api/v1/courses/{id}` | GET/PUT/DELETE | Course CRUD |
| `/api/v1/courses/{id}/enrollments` | GET | Get enrollments |
| `/api/v1/courses/{id}/assignments` | GET | Get course assignments |
| `/api/v1/assignments` | POST | Create assignment |
| `/api/v1/assignments/{id}` | GET/PUT/DELETE | Assignment CRUD |
| `/api/v1/assignments/{id}/submissions` | GET | Get submissions |
| `/api/v1/submissions/{id}/grade` | POST | Grade submission |
| `/api/v1/grades/course/{id}` | GET | Get course grades |
| `/api/v1/grades` | POST | Create grade |
| `/api/v1/grade-components/course/{id}` | GET | Get grade components |
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

## Pages Implementation

### 1. Dashboard Page

**File:** `resources/views/instructor/dashboard.blade.php`

```blade
@extends('layouts.instructor')

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
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Courses</h6>
                <h2 id="totalCourses" class="fw-bold text-primary">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Students</h6>
                <h2 id="totalStudents" class="fw-bold text-success">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Pending Submissions</h6>
                <h2 id="pendingSubmissions" class="fw-bold text-warning">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Assignments</h6>
                <h2 id="totalAssignments" class="fw-bold text-info">-</h2>
            </div>
        </div>
    </div>
</div>

{{-- My Courses --}}
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">My Courses</h5>
        <a href="{{ route('instructor.courses') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Course
        </a>
    </div>
    <div class="card-body">
        <div id="coursesLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data...</p>
        </div>
        <div id="coursesList" class="row g-3" style="display:none"></div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Enrollment Overview</h5>
            </div>
            <div class="card-body">
                <canvas id="enrollmentChart" style="max-height: 300px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Recent Submissions</h5>
            </div>
            <div class="card-body">
                <div id="recentSubmissions">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/instructor/api.js') }}"></script>
<script>
let instructorId = null;

async function loadDashboard() {
    try {
        const user = await InstructorAPI.getCurrentUser();
        instructorId = user.instructor?.id;

        if (!instructorId) throw new Error('Instructor data not found');

        const courses = await InstructorAPI.getCourses(instructorId);

        await updateStats(courses);
        renderCourses(courses);
        loadChart(courses);

    } catch (error) {
        console.error('Error:', error);
        alert('Gagal memuat data: ' + error.message);
    }
}

async function updateStats(courses) {
    let totalStudents = 0;
    let totalAssignments = 0;
    let pendingSubmissions = 0;

    for (const course of courses) {
        try {
            const enrollments = await InstructorAPI.getCourseEnrollments(course.id);
            totalStudents += enrollments.length;

            const assignments = await InstructorAPI.getCourseAssignments(course.id);
            totalAssignments += assignments.length;

            // Count pending submissions
            for (const assignment of assignments) {
                const submissions = await InstructorAPI.getAssignmentSubmissions(assignment.id);
                pendingSubmissions += submissions.filter(s => s.status === 'pending').length;
            }
        } catch (error) {
            console.error('Error fetching course data:', error);
        }
    }

    document.getElementById('totalCourses').textContent = courses.length;
    document.getElementById('totalStudents').textContent = totalStudents;
    document.getElementById('pendingSubmissions').textContent = pendingSubmissions;
    document.getElementById('totalAssignments').textContent = totalAssignments;
}

function renderCourses(courses) {
    const container = document.getElementById('coursesList');
    
    if (courses.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted text-center">Belum ada course.</p></div>';
        document.getElementById('coursesLoading').style.display = 'none';
        container.style.display = 'block';
        return;
    }

    container.innerHTML = courses.map(course => `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-2">${course.title}</h5>
                    <p class="text-muted small">${course.description?.substring(0, 80) || 'No description'}...</p>
                    <div class="mb-3">
                        <span class="badge bg-primary">${course.category || 'General'}</span>
                        <span class="badge bg-success ms-2">${course.status}</span>
                    </div>
                    <a href="/instructor/courses/${course.id}" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    `).join('');

    document.getElementById('coursesLoading').style.display = 'none';
    container.style.display = 'flex';
}

function loadChart(courses) {
    if (courses.length === 0) return;

    const ctx = document.getElementById('enrollmentChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: courses.map(c => c.title),
            datasets: [{
                label: 'Students Enrolled',
                data: courses.map(() => Math.floor(Math.random() * 50)), // TODO: Real data
                backgroundColor: '#0ea5e9',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection
```

---

### 2. My Courses Page

**File:** `resources/views/instructor/courses.blade.php`

```blade
@extends('layouts.instructor')

@section('title', 'My Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Courses</h2>
        <p class="text-muted mb-0">Manage your courses</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
        <i class="bi bi-plus-circle me-1"></i> Create Course
    </button>
</div>

{{-- Search and Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="🔍 Search courses..." onkeyup="filterCourses()">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter" onchange="filterCourses()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sortBy" onchange="sortCourses()">
                    <option value="title">Sort: Title</option>
                    <option value="date">Sort: Date</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Courses Grid --}}
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Loading courses...</p>
</div>

<div id="coursesList" class="row g-4" style="display:none"></div>

<div id="emptyState" class="card text-center py-5" style="display:none">
    <div class="card-body">
        <i class="bi bi-book fs-1 text-muted"></i>
        <p class="text-muted mt-3">No courses found</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
            Create Your First Course
        </button>
    </div>
</div>

{{-- Create Course Modal --}}
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCourseForm">
                    <div class="mb-3">
                        <label class="form-label">Course Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="Programming">Programming</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="Language">Language</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCourse()">
                    <i class="bi bi-check-circle me-1"></i> Create Course
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/instructor/api.js') }}"></script>
<script>
let allCourses = [];
let filteredCourses = [];

async function loadCourses() {
    try {
        const user = await InstructorAPI.getCurrentUser();
        const instructorId = user.instructor?.id;

        if (!instructorId) throw new Error('Instructor data not found');

        allCourses = await InstructorAPI.getCourses(instructorId);
        filteredCourses = [...allCourses];

        renderCourses();

    } catch (error) {
        console.error('Error:', error);
        showError('Failed to load courses: ' + error.message);
    }
}

function renderCourses() {
    const container = document.getElementById('coursesList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredCourses.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    container.style.display = 'flex';

    container.innerHTML = filteredCourses.map(course => `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="fw-bold mb-0">${course.title}</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/instructor/courses/${course.id}">
                                    <i class="bi bi-eye me-2"></i> View
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="editCourse(${course.id})">
                                    <i class="bi bi-pencil me-2"></i> Edit
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCourse(${course.id})">
                                    <i class="bi bi-trash me-2"></i> Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">${course.description?.substring(0, 100) || 'No description'}...</p>
                    <div class="mb-3">
                        <span class="badge bg-primary">${course.category || 'General'}</span>
                        <span class="badge bg-${course.status === 'active' ? 'success' : 'secondary'} ms-2">
                            ${course.status}
                        </span>
                    </div>
                    <a href="/instructor/courses/${course.id}" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-arrow-right me-1"></i> Manage Course
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

function filterCourses() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;

    filteredCourses = allCourses.filter(course => {
        const matchesSearch = course.title.toLowerCase().includes(searchQuery) ||
                            course.description?.toLowerCase().includes(searchQuery);
        const matchesStatus = statusFilter === 'all' || course.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    renderCourses();
}

function sortCourses() {
    const sortBy = document.getElementById('sortBy').value;

    if (sortBy === 'title') {
        filteredCourses.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortBy === 'date') {
        filteredCourses.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    renderCourses();
}

async function createCourse() {
    const form = document.getElementById('createCourseForm');
    const formData = new FormData(form);
    
    const courseData = {
        title: formData.get('title'),
        description: formData.get('description'),
        category: formData.get('category'),
        status: formData.get('status')
    };

    try {
        await InstructorAPI.createCourse(courseData);
        bootstrap.Modal.getInstance(document.getElementById('createCourseModal')).hide();
        form.reset();
        await loadCourses();
        alert('Course created successfully!');
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to create course: ' + error.message);
    }
}

async function deleteCourse(courseId) {
    if (!confirm('Are you sure you want to delete this course?')) return;

    try {
        await InstructorAPI.deleteCourse(courseId);
        await loadCourses();
        alert('Course deleted successfully!');
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete course: ' + error.message);
    }
}

function showError(message) {
    document.getElementById('loadingIndicator').innerHTML = 
        `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        </div>`;
}

document.addEventListener('DOMContentLoaded', loadCourses);
</script>
@endsection
```

---

## Common Components

### Modal Templates

#### Create Assignment Modal
```html
<div class="modal fade" id="createAssignmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm">
                    <div class="mb-3">
                        <label>Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label>Description *</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Due Date *</label>
                            <input type="datetime-local" class="form-control" name="due_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Max Score *</label>
                            <input type="number" class="form-control" name="max_score" value="100" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAssignment()">Create</button>
            </div>
        </div>
    </div>
</div>
```

### Grade Input Component
```html
<div class="grade-input">
    <label>Score</label>
    <div class="input-group">
        <input type="number" class="form-control" min="0" max="100" value="0">
        <span class="input-group-text">/100</span>
    </div>
    <label class="mt-2">Feedback</label>
    <textarea class="form-control" rows="3"></textarea>
</div>
```

---

## Best Practices

### 1. Always Validate Input
```javascript
function validateCourseData(data) {
    if (!data.title || data.title.trim() === '') {
        throw new Error('Title is required');
    }
    if (!data.description || data.description.trim() === '') {
        throw new Error('Description is required');
    }
    return true;
}
```

### 2. Handle Loading States
```javascript
function showLoading(elementId) {
    document.getElementById(elementId).innerHTML = 
        '<div class="spinner-border text-primary"></div>';
}

function hideLoading(elementId) {
    document.getElementById(elementId).style.display = 'none';
}
```

### 3. Use Confirmation Dialogs
```javascript
async function deleteCourse(courseId) {
    if (!confirm('Delete this course? This action cannot be undone.')) {
        return;
    }
    // Proceed with deletion
}
```

### 4. Show Success/Error Messages
```javascript
function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.getElementById('alerts').innerHTML = alertHtml;
}
```

---

## Next Steps

1. Implement remaining pages (Assignments, Submissions, Grading, Attendance, Students, Announcements, Certificates)
2. Add file upload handling for assignments
3. Add rich text editor for descriptions
4. Implement real-time updates for attendance
5. Add export functionality for grades
6. Add email notifications integration

---

**Last Updated:** 2024  
**Ngrok URL:** https://loraine-seminiferous-snappily.ngrok-free.dev  
**Version:** 1.0