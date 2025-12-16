# Student Dashboard - Frontend Implementation Guide

## Overview
Complete frontend implementation guide for the Student Dashboard in the Laravel LMS application. This guide covers all student-facing features including course enrollment, assignments, grades, attendance, and certificates.

---

## Table of Contents
1. [Base Configuration](#base-configuration)
2. [Layout Template](#layout-template)
3. [API Helper](#api-helper)
4. [Dashboard Pages](#dashboard-pages)
   - [Main Dashboard](#main-dashboard)
   - [My Courses](#my-courses)
   - [Course Detail](#course-detail)
   - [Assignments](#assignments)
   - [Submit Assignment](#submit-assignment)
   - [My Grades](#my-grades)
   - [My Attendance](#my-attendance)
   - [My Certificates](#my-certificates)
   - [Announcements](#announcements)
   - [Profile](#profile)
   - [Notifications](#notifications)
5. [Common Components](#common-components)
6. [Best Practices](#best-practices)

---

## Base Configuration

### Backend API Base URL
```javascript
const API_BASE_URL = 'https://loraine-seminiferous-snappily.ngrok-free.dev/api/v1';
```

### Authentication
- Token stored in `localStorage` with key: `auth_token`
- All API requests require Bearer token authentication
- Include `ngrok-skip-browser-warning: true` header for development

---

## Layout Template

### File: `resources/views/layouts/student.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Dashboard') - LMS</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/student/dashboard.css') }}" rel="stylesheet">
    
    @yield('extra-css')
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('student.dashboard') }}">
                <i class="fas fa-graduation-cap"></i> LMS Student
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger" id="notificationCount">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" 
                            aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <div id="notificationList">
                                <li><span class="dropdown-item-text">No new notifications</span></li>
                            </div>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="{{ route('student.notifications') }}">
                                View All
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <span id="studentName">Student</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="{{ route('student.profile') }}">
                                <i class="fas fa-user"></i> My Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" 
                               href="{{ route('student.dashboard') }}">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.courses*') ? 'active' : '' }}" 
                               href="{{ route('student.courses') }}">
                                <i class="fas fa-book"></i> My Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.assignments*') ? 'active' : '' }}" 
                               href="{{ route('student.assignments') }}">
                                <i class="fas fa-tasks"></i> Assignments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.grades') ? 'active' : '' }}" 
                               href="{{ route('student.grades') }}">
                                <i class="fas fa-chart-line"></i> My Grades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.attendance') ? 'active' : '' }}" 
                               href="{{ route('student.attendance') }}">
                                <i class="fas fa-calendar-check"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.certificates*') ? 'active' : '' }}" 
                               href="{{ route('student.certificates') }}">
                                <i class="fas fa-certificate"></i> Certificates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.announcements') ? 'active' : '' }}" 
                               href="{{ route('student.announcements') }}">
                                <i class="fas fa-bullhorn"></i> Announcements
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3 pb-2 mb-3">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- API Helper -->
    <script src="{{ asset('js/student/api.js') }}"></script>
    <!-- Common Scripts -->
    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }

        // Load user info and notifications on page load
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const user = await StudentAPI.getUserInfo();
                document.getElementById('studentName').textContent = user.name;
                
                await loadNotificationCount();
            } catch (error) {
                console.error('Error loading user info:', error);
            }
        });

        // Load notification count
        async function loadNotificationCount() {
            try {
                const data = await StudentAPI.getNotificationCount();
                const badge = document.getElementById('notificationCount');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading notification count:', error);
            }
        }

        // Auto-refresh notification count every 30 seconds
        setInterval(loadNotificationCount, 30000);
    </script>
    
    @yield('extra-js')
</body>
</html>
```

### File: `public/css/student/dashboard.css`

```css
/* Sidebar Styling */
.sidebar {
    position: fixed;
    top: 56px;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    overflow-y: auto;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
    padding: 0.75rem 1rem;
    border-left: 3px solid transparent;
}

.sidebar .nav-link:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.sidebar .nav-link.active {
    color: #0d6efd;
    background-color: #e7f1ff;
    border-left-color: #0d6efd;
}

.sidebar .nav-link i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

/* Main Content */
main {
    margin-top: 56px;
}

/* Notification Dropdown */
.notification-dropdown {
    min-width: 320px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e7f1ff;
}

/* Cards */
.dashboard-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid #0d6efd;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-card {
    border-radius: 10px;
    padding: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

/* Course Cards */
.course-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
}

.course-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

.course-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
}

.course-progress {
    height: 8px;
    border-radius: 4px;
}

/* Assignment Status Badges */
.status-pending {
    background-color: #ffc107;
    color: #000;
}

.status-submitted {
    background-color: #17a2b8;
    color: #fff;
}

.status-graded {
    background-color: #28a745;
    color: #fff;
}

.status-overdue {
    background-color: #dc3545;
    color: #fff;
}

/* Grade Display */
.grade-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    border: 4px solid;
}

.grade-a {
    background-color: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.grade-b {
    background-color: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
}

.grade-c {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.grade-d {
    background-color: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

/* Attendance Calendar */
.attendance-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.attendance-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    font-size: 0.9rem;
    font-weight: 500;
}

.attendance-present {
    background-color: #d4edda;
    color: #155724;
}

.attendance-absent {
    background-color: #f8d7da;
    color: #721c24;
}

.attendance-late {
    background-color: #fff3cd;
    color: #856404;
}

/* Loading Spinner */
.spinner-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}

/* File Upload Area */
.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.file-upload-area.dragover {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        position: relative;
        top: 0;
    }
    
    main {
        margin-top: 0;
    }
}
```

---

## API Helper

### File: `public/js/student/api.js`

```javascript
/**
 * Student API Helper
 * Handles all API requests for student dashboard
 */

const StudentAPI = {
    baseURL: 'https://loraine-seminiferous-snappily.ngrok-free.dev/api/v1',
    
    /**
     * Get authentication headers
     */
    getHeaders() {
        const token = localStorage.getItem('auth_token');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            'ngrok-skip-browser-warning': 'true'
        };
    },

    /**
     * Handle API response
     */
    async handleResponse(response) {
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
            throw new Error('Unauthorized');
        }
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'API request failed');
        }
        
        return data.data || data;
    },

    // ==================== User Info ====================
    
    /**
     * Get current user info
     */
    async getUserInfo() {
        const response = await fetch(`${this.baseURL}/user`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Dashboard ====================
    
    /**
     * Get dashboard statistics
     */
    async getDashboardStats() {
        const response = await fetch(`${this.baseURL}/students/dashboard/stats`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get recent activities
     */
    async getRecentActivities() {
        const response = await fetch(`${this.baseURL}/students/dashboard/activities`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Courses ====================
    
    /**
     * Get enrolled courses
     */
    async getEnrolledCourses() {
        const response = await fetch(`${this.baseURL}/students/courses`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get course details
     */
    async getCourseDetail(courseId) {
        const response = await fetch(`${this.baseURL}/courses/${courseId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get course materials/lessons
     */
    async getCourseMaterials(courseId) {
        const response = await fetch(`${this.baseURL}/courses/${courseId}/materials`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get course progress
     */
    async getCourseProgress(courseId) {
        const response = await fetch(`${this.baseURL}/students/courses/${courseId}/progress`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Assignments ====================
    
    /**
     * Get all assignments
     */
    async getAssignments(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.baseURL}/students/assignments?${params}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get assignment details
     */
    async getAssignmentDetail(assignmentId) {
        const response = await fetch(`${this.baseURL}/assignments/${assignmentId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Submit assignment
     */
    async submitAssignment(assignmentId, formData) {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${this.baseURL}/assignments/${assignmentId}/submit`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'ngrok-skip-browser-warning': 'true'
            },
            body: formData // FormData for file uploads
        });
        return this.handleResponse(response);
    },

    /**
     * Get submission details
     */
    async getSubmissionDetail(submissionId) {
        const response = await fetch(`${this.baseURL}/submissions/${submissionId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Grades ====================
    
    /**
     * Get all grades
     */
    async getGrades() {
        const response = await fetch(`${this.baseURL}/students/grades`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get grades by course
     */
    async getGradesByCourse(courseId) {
        const response = await fetch(`${this.baseURL}/students/grades/course/${courseId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get grade statistics
     */
    async getGradeStats() {
        const response = await fetch(`${this.baseURL}/students/grades/statistics`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Attendance ====================
    
    /**
     * Get attendance records
     */
    async getAttendanceRecords(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.baseURL}/students/attendance?${params}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Mark attendance (for open sessions)
     */
    async markAttendance(sessionId, data) {
        const response = await fetch(`${this.baseURL}/attendance/sessions/${sessionId}/mark`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        return this.handleResponse(response);
    },

    /**
     * Get attendance summary
     */
    async getAttendanceSummary(courseId) {
        const response = await fetch(`${this.baseURL}/students/attendance/course/${courseId}/summary`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Certificates ====================
    
    /**
     * Get student certificates
     */
    async getCertificates() {
        const response = await fetch(`${this.baseURL}/students/certificates`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get certificate detail
     */
    async getCertificateDetail(certificateId) {
        const response = await fetch(`${this.baseURL}/certificates/${certificateId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Verify certificate
     */
    async verifyCertificate(certificateNumber) {
        const response = await fetch(`${this.baseURL}/certificates/verify/${certificateNumber}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Download certificate
     */
    async downloadCertificate(certificateId) {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${this.baseURL}/certificates/${certificateId}/download`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `certificate-${certificateId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            throw new Error('Failed to download certificate');
        }
    },

    // ==================== Announcements ====================
    
    /**
     * Get active announcements
     */
    async getAnnouncements(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.baseURL}/announcements/active?${params}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get announcement detail
     */
    async getAnnouncementDetail(announcementId) {
        const response = await fetch(`${this.baseURL}/announcements/${announcementId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Notifications ====================
    
    /**
     * Get notifications
     */
    async getNotifications() {
        const response = await fetch(`${this.baseURL}/notifications`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Get notification count
     */
    async getNotificationCount() {
        const response = await fetch(`${this.baseURL}/notifications/counts`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Mark notification as read
     */
    async markNotificationRead(notificationId) {
        const response = await fetch(`${this.baseURL}/notifications/${notificationId}/read`, {
            method: 'PUT',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    /**
     * Mark all notifications as read
     */
    async markAllNotificationsRead() {
        const response = await fetch(`${this.baseURL}/notifications/read-all`, {
            method: 'PUT',
            headers: this.getHeaders()
        });
        return this.handleResponse(response);
    },

    // ==================== Profile ====================
    
    /**
     * Update profile
     */
    async updateProfile(data) {
        const response = await fetch(`${this.baseURL}/students/profile`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        return this.handleResponse(response);
    },

    /**
     * Change password
     */
    async changePassword(data) {
        const response = await fetch(`${this.baseURL}/students/change-password`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        return this.handleResponse(response);
    }
};
```

---

## Dashboard Pages

### Main Dashboard

#### File: `resources/views/student/dashboard.blade.php`

```blade
@extends('layouts.student')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4" id="statsContainer">
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Enrolled Courses</h6>
                        <h2 class="mb-0" id="totalCourses">-</h2>
                    </div>
                    <i class="fas fa-book stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Pending Assignments</h6>
                        <h2 class="mb-0" id="pendingAssignments">-</h2>
                    </div>
                    <i class="fas fa-tasks stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Average Grade</h6>
                        <h2 class="mb-0" id="averageGrade">-</h2>
                    </div>
                    <i class="fas fa-chart-line stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Attendance Rate</h6>
                        <h2 class="mb-0" id="attendanceRate">-</h2>
                    </div>
                    <i class="fas fa-calendar-check stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row">
    <!-- Active Courses -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-book"></i> Active Courses</h5>
                <a href="{{ route('student.courses') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div id="activeCoursesList">
                    <div class="spinner-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Activities</h5>
            </div>
            <div class="card-body">
                <div id="recentActivitiesList" style="max-height: 400px; overflow-y: auto;">
                    <div class="spinner-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Assignments -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Upcoming Assignments</h5>
                <a href="{{ route('student.assignments') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div id="upcomingAssignmentsList">
                    <div class="spinner-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });

    async function loadDashboardData() {
        try {
            // Load statistics
            await loadStats();
            
            // Load active courses
            await loadActiveCourses();
            
            // Load recent activities
            await loadRecentActivities();
            
            // Load upcoming assignments
            await loadUpcomingAssignments();
        } catch (error) {
            console.error('Error loading dashboard:', error);
            alert('Failed to load dashboard data. Please try again.');
        }
    }

    async function loadStats() {
        try {
            const stats = await StudentAPI.getDashboardStats();
            
            document.getElementById('totalCourses').textContent = stats.total_courses || 0;
            document.getElementById('pendingAssignments').textContent = stats.pending_assignments || 0;
            document.getElementById('averageGrade').textContent = stats.average_grade ? 
                stats.average_grade.toFixed(1) : '-';
            document.getElementById('attendanceRate').textContent = stats.attendance_rate ? 
                stats.attendance_rate.toFixed(1) + '%' : '-';
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    async function loadActiveCourses() {
        try {
            const courses = await StudentAPI.getEnrolledCourses();
            const container = document.getElementById('activeCoursesList');
            
            if (!courses || courses.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No active courses</p>';
                return;
            }

            // Show first 3 courses
            const activeCourses = courses.slice(0, 3);
            container.innerHTML = activeCourses.map(course => `
                <div class="card dashboard-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">${course.title}</h6>
                                <small class="text-muted">${course.instructor_name || 'Instructor'}</small>
                            </div>
                            <span class="badge bg-primary">${course.code || 'N/A'}</span>
                        </div>
                        <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${course.progress || 0}%" 
                                 aria-valuenow="${course.progress || 0}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Progress: ${course.progress || 0}%</small>
                            <a href="/student/courses/${course.id}" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading courses:', error);
            document.getElementById('activeCoursesList').innerHTML = 
                '<p class="text-danger text-center">Failed to load courses</p>';
        }
    }

    async function loadRecentActivities() {
        try {
            const activities = await StudentAPI.getRecentActivities();
            const container = document.getElementById('recentActivitiesList');
            
            if (!activities || activities.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No recent activities</p>';
                return;
            }

            container.innerHTML = activities.map(activity => `
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${getActivityIcon(activity.type)} text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 small">${activity.description}</p>
                        <small class="text-muted">${formatDate(activity.created_at)}</small>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading activities:', error);
            document.getElementById('recentActivitiesList').innerHTML = 
                '<p class="text-danger text-center">Failed to load activities</p>';
        }
    }

    async function loadUpcomingAssignments() {
        try {
            const assignments = await StudentAPI.getAssignments({ status: 'pending', limit: 5 });
            const container = document.getElementById('upcomingAssignmentsList');
            
            if (!assignments || assignments.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No upcoming assignments</p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${assignments.map(assignment => `
                                <tr>
                                    <td>${assignment.title}</td>
                                    <td>${assignment.course_title}</td>
                                    <td>${formatDate(assignment.due_date)}</td>
                                    <td>
                                        <span class="badge ${getStatusClass(assignment.status)}">
                                            ${assignment.status}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/student/assignments/${assignment.id}" 
                                           class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } catch (error) {
            console.error('Error loading assignments:', error);
            document.getElementById('upcomingAssignmentsList').innerHTML = 
                '<p class="text-danger text-center">Failed to load assignments</p>';
        }
    }

    function getActivityIcon(type) {
        const icons = {
            'assignment': 'tasks',
            'grade': 'chart-line',
            'attendance': 'calendar-check',
            'announcement': 'bullhorn',
            'course': 'book'
        };
        return icons[type] || 'circle';
    }

    function getStatusClass(status) {
        const classes = {
            'pending': 'status-pending',
            'submitted': 'status-submitted',
            'graded': 'status-graded',
            'overdue': 'status-overdue'
        };
        return classes[status] || 'bg-secondary';
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    function refreshDashboard() {
        location.reload();
    }
</script>
@endsection
```

### My Courses

#### File: `resources/views/student/courses/index.blade.php`

```blade
@extends('layouts.student')

@section('title', 'My Courses')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">My Courses</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <select class="form-select form-select-sm" id="statusFilter" onchange="filterCourses()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
</div>

<!-- Courses Grid -->
<div class="row" id="coursesContainer">
    <div class="col-12">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
    let allCourses = [];

    document.addEventListener('DOMContentLoaded', function() {
        loadCourses();
    });

    async function loadCourses() {
        try {
            allCourses = await StudentAPI.getEnrolledCourses();
            displayCourses(allCourses);
        } catch (error) {
            console.error('Error loading courses:', error);
            document.getElementById('coursesContainer').innerHTML = 
                '<div class="col-12"><div class="alert alert-danger">Failed to load courses</div></div>';
        }
    }

    function displayCourses(courses) {
        const container = document.getElementById('coursesContainer');
        
        if (!courses || courses.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> You are not enrolled in any courses yet.
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = courses.map(course => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card course-card h-100">
                    <div class="course-card-header">
                        <h5 class="mb-1">${course.title}</h5>
                        <p class="mb-0 small">${course.code || ''}</p>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-user"></i> ${course.instructor_name || 'Instructor'}
                        </p>
                        <p class="small mb-3">${course.description ? 
                            (course.description.substring(0, 100) + '...') : 
                            'No description available'}</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Progress</small>
                                <small>${course.progress || 0}%</small>
                            </div>
                            <div class="progress course-progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: ${course.progress || 0}%"></div>
                            </div>
                        </div>

                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <small class="text-muted d-block">Lessons</small>
                                <strong>${course.total_lessons || 0}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Assignments</small>
                                <strong>${course.total_assignments || 0}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Grade</small>
                                <strong>${course.grade || '-'}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="/student/courses/${course.id}" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-arrow-right"></i> View Course
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function filterCourses() {
        const status = document.getElementById('statusFilter').value;
        
        let filtered = allCourses;
        if (status) {
            filtered = allCourses.filter(course => course.status === status);
        }
        
        displayCourses(filtered);
    }
</script>
@endsection
```

### Course Detail

#### File: `resources/views/student/courses/show.blade.php`

```blade
@extends('layouts.student')

@section('title', 'Course Details')

@section('content')
<div class="mb-3">
    <a href="{{ route('student.courses') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<!-- Course Header -->
<div class="card mb-4" id="courseHeader">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Course Content Tabs -->
<ul class="nav nav-tabs mb-3" id="courseTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="materials-tab" data-bs-toggle="tab" 
                data-bs-target="#materials" type="button">
            <i class="fas fa-book"></i> Materials
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" 
                data-bs-target="#assignments" type="button">
            <i class="fas fa-tasks"></i> Assignments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="grades-tab" data-bs-toggle="tab" 
                data-bs-target="#grades" type="button">
            <i class="fas fa-chart-line"></i> Grades
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                data-bs-target="#attendance" type="button">
            <i class="fas fa-calendar-check"></i> Attendance
        </button>
    </li>
</ul>

<div class="tab-content" id="courseTabsContent">
    <!-- Materials Tab -->
    <div class="tab-pane fade show active" id="materials" role="tabpanel">
        <div class="card">
            <div class="card-body" id="materialsContent">
                <div class="spinner-container">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Tab -->
    <div class="tab-pane fade" id="assignments" role="tabpanel">
        <div class="card">
            <div class="card-body" id="assignmentsContent">
                <p class="text-muted">Click to load assignments...</p>
            </div>
        </div>
    </div>

    <!-- Grades Tab -->
    <div class="tab-pane fade" id="grades" role="tabpanel">
        <div class="card">
            <div class="card-body" id="gradesContent">
                <p class="text-muted">Click to load grades...</p>
            </div>
        </div>
    </div>

    <!-- Attendance Tab -->
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="card">
            <div class="card-body" id="attendanceContent">
                <p class="text-muted">Click to load attendance...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
    const courseId = {{ $courseId ?? 'window.location.pathname.split("/").pop()' }};
    let courseData = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadCourseDetails();
        loadMaterials();

        // Tab event listeners
        document.getElementById('assignments-tab').addEventListener('shown.bs.tab', loadCourseAssignments);
        document.getElementById('grades-tab').addEventListener('shown.bs.tab', loadCourseGrades);
        document.getElementById('attendance-tab').addEventListener('shown.bs.tab', loadCourseAttendance);
    });

    async function loadCourseDetails() {
        try {
            courseData = await StudentAPI.getCourseDetail(courseId);
            
            document.getElementById('courseHeader').innerHTML = `
                <div class="course-card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-1">${courseData.title}</h3>
                            <p class="mb-0">${courseData.code || ''}</p>
                        </div>
                        <span class="badge bg-light text-dark">${courseData.status || 'Active'}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-3">${courseData.description || 'No description'}</p>
                            <p class="mb-1">
                                <strong><i class="fas fa-user"></i> Instructor:</strong> 
                                ${courseData.instructor_name || 'N/A'}
                            </p>
                            <p class="mb-1">
                                <strong><i class="fas fa-calendar"></i> Duration:</strong> 
                                ${formatDate(courseData.start_date)} - ${formatDate(courseData.end_date)}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Course Progress</small>
                                    <small>${courseData.progress || 0}%</small>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: ${courseData.progress || 0}%"></div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Materials</small>
                                    <strong>${courseData.total_lessons || 0}</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Assignments</small>
                                    <strong>${courseData.total_assignments || 0}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            console.error('Error loading course details:', error);
            document.getElementById('courseHeader').innerHTML = 
                '<div class="alert alert-danger">Failed to load course details</div>';
        }
    }

    async function loadMaterials() {
        try {
            const materials = await StudentAPI.getCourseMaterials(courseId);
            const container = document.getElementById('materialsContent');
            
            if (!materials || materials.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No materials available yet</p>';
                return;
            }

            container.innerHTML = `
                <div class="accordion" id="materialsAccordion">
                    ${materials.map((material, index) => `
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" 
                                        type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#material${material.id}">
                                    <i class="fas fa-file-alt me-2"></i>
                                    ${material.title}
                                    ${material.is_completed ? 
                                        '<i class="fas fa-check-circle text-success ms-2"></i>' : ''}
                                </button>
                            </h2>
                            <div id="material${material.id}" 
                                 class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                                 data-bs-parent="#materialsAccordion">
                                <div class="accordion-body">
                                    <p>${material.description || 'No description'}</p>
                                    ${material.content ? `
                                        <div class="mb-3">
                                            <h6>Content:</h6>
                                            <div>${material.content}</div>
                                        </div>
                                    ` : ''}
                                    ${material.file_url ? `
                                        <a href="${material.file_url}" target="_blank" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> Download Material
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            console.error('Error loading materials:', error);
            document.getElementById('materialsContent').innerHTML = 
                '<p class="text-danger">Failed to load materials</p>';
        }
    }

    async function loadCourseAssignments() {
        const container = document.getElementById('assignmentsContent');
        container.innerHTML = '<div class="spinner-container"><div class="spinner-border"></div></div>';
        
        try {
            const assignments = await StudentAPI.getAssignments({ course_id: courseId });
            
            if (!assignments || assignments.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No assignments yet</p>';
                return;
            }

            container.innerHTML = `
                <div class="list-group">
                    ${assignments.map(assignment => `
                        <a href="/student/assignments/${assignment.id}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${assignment.title}</h6>
                                <span class="badge ${getStatusClass(assignment.status)}">
                                    ${assignment.status}
                                </span>
                            </div>
                            <p class="mb-1 small">${assignment.description ? 
                                (assignment.description.substring(0, 100) + '...') : 
                                'No description'}</p>
                            <small class="text-muted">
                                Due: ${formatDate(assignment.due_date)}
                                ${assignment.grade ? ` | Grade: ${assignment.grade}` : ''}
                            </small>
                        </a>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            console.error('Error loading assignments:', error);
            container.innerHTML = '<p class="text-danger">Failed to load assignments</p>';
        }
    }

    async function loadCourseGrades() {
        const container = document.getElementById('gradesContent');
        container.innerHTML = '<div class="spinner-container"><div class="spinner-border"></div></div>';
        
        try {
            const grades = await StudentAPI.getGradesByCourse(courseId);
            
            if (!grades || grades.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No grades available yet</p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Grade</th>
                                <th>Max Points</th>
                                <th>Percentage</th>
                                <th>Submitted</th>
                                <th>Graded</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${grades.map(grade => `
                                <tr>
                                    <td>${grade.assignment_title}</td>
                                    <td><strong>${grade.grade || '-'}</strong></td>
                                    <td>${grade.max_points || '-'}</td>
                                    <td>
                                        ${grade.percentage ? `
                                            <span class="badge ${getGradeColor(grade.percentage)}">
                                                ${grade.percentage}%
                                            </span>
                                        ` : '-'}
                                    </td>
                                    <td>${formatDate(grade.submitted_at)}</td>
                                    <td>${formatDate(grade.graded_at)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } catch (error) {
            console.error('Error loading grades:', error);
            container.innerHTML = '<p class="text-danger">Failed to load grades</p>';
        }
    }

    async function loadCourseAttendance() {
        const container = document.getElementById('attendanceContent');
        container.innerHTML = '<div class="spinner-container"><div class="spinner-border"></div></div>';
        
        try {
            const attendance = await StudentAPI.getAttendanceRecords({ course_id: courseId });
            
            if (!attendance || attendance.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No attendance records yet</p>';
                return;
            }

            // Calculate summary
            const total = attendance.length;
            const present = attendance.filter(a => a.status === 'present').length;
            const absent = attendance.filter(a => a.status === 'absent').length;
            const late = attendance.filter(a => a.status === 'late').length;
            const rate = ((present + (late * 0.5)) / total * 100).toFixed(1);

            container.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success">${present}</h3>
                                <small>Present</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger">${absent}</h3>
                                <small>Absent</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning">${late}</h3>
                                <small>Late</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary">${rate}%</h3>
                                <small>Attendance Rate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Notes</th>
                            </tr>
                        </thea