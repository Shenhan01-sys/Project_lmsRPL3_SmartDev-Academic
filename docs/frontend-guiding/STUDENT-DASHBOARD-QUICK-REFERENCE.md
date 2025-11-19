# Student Dashboard - Quick Reference Guide

Quick reference for common tasks in the Student Dashboard frontend implementation.

---

## Table of Contents
1. [API Helper Setup](#api-helper-setup)
2. [Common API Calls](#common-api-calls)
3. [UI Components](#ui-components)
4. [Code Snippets](#code-snippets)

---

## API Helper Setup

### Initialize API Helper
```javascript
// Include in layout
<script src="{{ asset('js/student/api.js') }}"></script>

// Usage in pages
const data = await StudentAPI.getEnrolledCourses();
```

### Authentication Headers
```javascript
const headers = StudentAPI.getHeaders();
// Returns:
// {
//   'Content-Type': 'application/json',
//   'Accept': 'application/json',
//   'Authorization': 'Bearer {token}',
//   'ngrok-skip-browser-warning': 'true'
// }
```

---

## Common API Calls

### Dashboard Statistics
```javascript
// Get dashboard stats
const stats = await StudentAPI.getDashboardStats();
// Returns: { total_courses, pending_assignments, average_grade, attendance_rate }

// Get recent activities
const activities = await StudentAPI.getRecentActivities();
```

### Courses
```javascript
// Get enrolled courses
const courses = await StudentAPI.getEnrolledCourses();

// Get course detail
const course = await StudentAPI.getCourseDetail(courseId);

// Get course materials
const materials = await StudentAPI.getCourseMaterials(courseId);

// Get course progress
const progress = await StudentAPI.getCourseProgress(courseId);
```

### Assignments
```javascript
// Get all assignments (with filters)
const assignments = await StudentAPI.getAssignments({
    status: 'pending',  // pending, submitted, graded
    course_id: 123,
    limit: 10
});

// Get assignment detail
const assignment = await StudentAPI.getAssignmentDetail(assignmentId);

// Submit assignment
const formData = new FormData();
formData.append('submission_text', 'My submission...');
formData.append('file', fileInput.files[0]);

const result = await StudentAPI.submitAssignment(assignmentId, formData);

// Get submission detail
const submission = await StudentAPI.getSubmissionDetail(submissionId);
```

### Grades
```javascript
// Get all grades
const grades = await StudentAPI.getGrades();

// Get grades by course
const courseGrades = await StudentAPI.getGradesByCourse(courseId);

// Get grade statistics
const stats = await StudentAPI.getGradeStats();
// Returns: { average_grade, highest_grade, lowest_grade, total_graded }
```

### Attendance
```javascript
// Get attendance records
const attendance = await StudentAPI.getAttendanceRecords({
    course_id: 123,
    month: '2024-01'
});

// Mark attendance (for open sessions)
const result = await StudentAPI.markAttendance(sessionId, {
    notes: 'Present'
});

// Get attendance summary
const summary = await StudentAPI.getAttendanceSummary(courseId);
// Returns: { total_sessions, present, absent, late, attendance_rate }
```

### Certificates
```javascript
// Get all certificates
const certificates = await StudentAPI.getCertificates();

// Get certificate detail
const certificate = await StudentAPI.getCertificateDetail(certificateId);

// Download certificate
await StudentAPI.downloadCertificate(certificateId);
// Auto-downloads PDF file

// Verify certificate
const verification = await StudentAPI.verifyCertificate(certificateNumber);
```

### Notifications
```javascript
// Get notifications
const notifications = await StudentAPI.getNotifications();

// Get notification count
const count = await StudentAPI.getNotificationCount();
// Returns: { total_count, unread_count }

// Mark as read
await StudentAPI.markNotificationRead(notificationId);

// Mark all as read
await StudentAPI.markAllNotificationsRead();
```

### Profile
```javascript
// Update profile
const updated = await StudentAPI.updateProfile({
    name: 'John Doe',
    email: 'john@example.com',
    phone: '1234567890',
    address: '123 Main St'
});

// Change password
const result = await StudentAPI.changePassword({
    current_password: 'oldpass',
    new_password: 'newpass',
    new_password_confirmation: 'newpass'
});
```

---

## UI Components

### Status Badges
```html
<!-- Assignment Status -->
<span class="badge status-pending">Pending</span>
<span class="badge status-submitted">Submitted</span>
<span class="badge status-graded">Graded</span>
<span class="badge status-overdue">Overdue</span>
```

### Grade Display Circle
```html
<div class="grade-circle grade-a">A</div>
<div class="grade-circle grade-b">B</div>
<div class="grade-circle grade-c">C</div>
<div class="grade-circle grade-d">D</div>
```

### Progress Bar
```html
<div class="progress course-progress">
    <div class="progress-bar bg-success" role="progressbar" 
         style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
        75%
    </div>
</div>
```

### Loading Spinner
```html
<div class="spinner-container">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
```

### Course Card
```html
<div class="card course-card h-100">
    <div class="course-card-header">
        <h5 class="mb-1">Course Title</h5>
        <p class="mb-0 small">CS101</p>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="fas fa-user"></i> Instructor Name
        </p>
        <p class="small mb-3">Course description...</p>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small>Progress</small>
                <small>75%</small>
            </div>
            <div class="progress course-progress">
                <div class="progress-bar bg-success" style="width: 75%"></div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <a href="#" class="btn btn-primary btn-sm w-100">
            <i class="fas fa-arrow-right"></i> View Course
        </a>
    </div>
</div>
```

### File Upload Area
```html
<div class="file-upload-area" onclick="document.getElementById('fileInput').click()">
    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
    <p>Click to upload or drag and drop</p>
    <small class="text-muted">Maximum file size: 10MB</small>
    <input type="file" id="fileInput" class="d-none">
</div>
```

---

## Code Snippets

### Load and Display Courses
```javascript
async function loadCourses() {
    try {
        const courses = await StudentAPI.getEnrolledCourses();
        const container = document.getElementById('coursesContainer');
        
        if (!courses || courses.length === 0) {
            container.innerHTML = '<p class="text-muted">No courses found</p>';
            return;
        }

        container.innerHTML = courses.map(course => `
            <div class="col-md-4 mb-4">
                <div class="card course-card h-100">
                    <div class="course-card-header">
                        <h5>${course.title}</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress">
                            <div class="progress-bar" style="width: ${course.progress}%"></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="/student/courses/${course.id}" class="btn btn-primary btn-sm">
                            View Course
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load courses');
    }
}
```

### Submit Assignment with File Upload
```javascript
async function submitAssignment(assignmentId) {
    const form = document.getElementById('submissionForm');
    const formData = new FormData();
    
    // Add text content
    const text = document.getElementById('submissionText').value;
    formData.append('submission_text', text);
    
    // Add file if selected
    const fileInput = document.getElementById('fileInput');
    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }
    
    try {
        // Show loading
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        
        const result = await StudentAPI.submitAssignment(assignmentId, formData);
        
        alert('Assignment submitted successfully!');
        window.location.href = `/student/assignments/${assignmentId}`;
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit assignment: ' + error.message);
        
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Submit Assignment';
    }
}
```

### Display Grades Table
```javascript
async function loadGrades() {
    try {
        const grades = await StudentAPI.getGrades();
        const tbody = document.getElementById('gradesTableBody');
        
        if (!grades || grades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No grades yet</td></tr>';
            return;
        }

        tbody.innerHTML = grades.map(grade => `
            <tr>
                <td>${grade.course_title}</td>
                <td>${grade.assignment_title}</td>
                <td>
                    <div class="grade-circle grade-${getGradeClass(grade.grade)}">
                        ${grade.grade}
                    </div>
                </td>
                <td>${grade.percentage}%</td>
                <td>${formatDate(grade.graded_at)}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load grades');
    }
}

function getGradeClass(grade) {
    const num = parseFloat(grade);
    if (num >= 90) return 'a';
    if (num >= 80) return 'b';
    if (num >= 70) return 'c';
    return 'd';
}
```

### Attendance Calendar View
```javascript
async function loadAttendanceCalendar(courseId, month) {
    try {
        const records = await StudentAPI.getAttendanceRecords({
            course_id: courseId,
            month: month
        });
        
        const container = document.getElementById('attendanceCalendar');
        
        // Create calendar grid
        const daysInMonth = new Date(month + '-01').getDate();
        let html = '<div class="attendance-calendar">';
        
        // Day headers
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            html += `<div class="text-center fw-bold">${day}</div>`;
        });
        
        // Days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${month}-${String(day).padStart(2, '0')}`;
            const record = records.find(r => r.date === dateStr);
            
            let className = 'attendance-day bg-light';
            if (record) {
                if (record.status === 'present') className = 'attendance-day attendance-present';
                else if (record.status === 'absent') className = 'attendance-day attendance-absent';
                else if (record.status === 'late') className = 'attendance-day attendance-late';
            }
            
            html += `<div class="${className}">${day}</div>`;
        }
        
        html += '</div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load attendance calendar');
    }
}
```

### Filter Assignments by Status
```javascript
let allAssignments = [];

async function loadAssignments() {
    try {
        allAssignments = await StudentAPI.getAssignments();
        displayAssignments(allAssignments);
    } catch (error) {
        console.error('Error:', error);
    }
}

function filterAssignments(status) {
    const filtered = status ? 
        allAssignments.filter(a => a.status === status) : 
        allAssignments;
    displayAssignments(filtered);
}

function displayAssignments(assignments) {
    const container = document.getElementById('assignmentsContainer');
    
    if (!assignments || assignments.length === 0) {
        container.innerHTML = '<p class="text-muted">No assignments found</p>';
        return;
    }

    container.innerHTML = `
        <div class="list-group">
            ${assignments.map(assignment => `
                <a href="/student/assignments/${assignment.id}" 
                   class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <h6>${assignment.title}</h6>
                        <span class="badge ${getStatusClass(assignment.status)}">
                            ${assignment.status}
                        </span>
                    </div>
                    <small class="text-muted">Due: ${formatDate(assignment.due_date)}</small>
                </a>
            `).join('')}
        </div>
    `;
}
```

### Notification Dropdown
```javascript
async function loadNotifications() {
    try {
        const notifications = await StudentAPI.getNotifications();
        const container = document.getElementById('notificationList');
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = '<li><span class="dropdown-item-text">No notifications</span></li>';
            return;
        }

        container.innerHTML = notifications.slice(0, 5).map(notif => `
            <li>
                <a class="dropdown-item notification-item ${notif.read_at ? '' : 'unread'}" 
                   href="#" onclick="markAsRead(${notif.id})">
                    <div class="small">
                        <strong>${notif.title}</strong><br>
                        ${notif.message}
                    </div>
                    <small class="text-muted">${formatDate(notif.created_at)}</small>
                </a>
            </li>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
    }
}

async function markAsRead(notificationId) {
    try {
        await StudentAPI.markNotificationRead(notificationId);
        loadNotifications();
        loadNotificationCount();
    } catch (error) {
        console.error('Error:', error);
    }
}

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
        console.error('Error:', error);
    }
}
```

### Helper Functions
```javascript
// Format date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Get status badge class
function getStatusClass(status) {
    const classes = {
        'pending': 'status-pending',
        'submitted': 'status-submitted',
        'graded': 'status-graded',
        'overdue': 'status-overdue'
    };
    return classes[status] || 'bg-secondary';
}

// Get grade color
function getGradeColor(percentage) {
    if (percentage >= 90) return 'bg-success';
    if (percentage >= 80) return 'bg-info';
    if (percentage >= 70) return 'bg-warning';
    return 'bg-danger';
}

// Truncate text
function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Show loading state
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
}

// Show error message
function showError(containerId, message) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> ${message}
        </div>
    `;
}

// Confirm action
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
```

### File Upload Handler
```javascript
function setupFileUpload() {
    const uploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');

    // Click to upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // File selected
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            displayFileInfo(file);
        }
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            fileInput.files = e.dataTransfer.files;
            displayFileInfo(file);
        }
    });
}

function displayFileInfo(file) {
    const fileInfo = document.getElementById('fileInfo');
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    
    fileInfo.innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-file"></i> ${file.name} (${fileSize} MB)
            <button type="button" class="btn-close float-end" 
                    onclick="clearFile()"></button>
        </div>
    `;
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('fileInfo').innerHTML = '';
}
```

---

## Route Definitions

Add these routes to `routes/web.php`:

```php
Route::middleware(['auth:sanctum'])->prefix('student')->name('student.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // Courses
    Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
    Route::get('/courses/{id}', [StudentController::class, 'courseDetail'])->name('courses.show');
    
    // Assignments
    Route::get('/assignments', [StudentController::class, 'assignments'])->name('assignments');
    Route::get('/assignments/{id}', [StudentController::class, 'assignmentDetail'])->name('assignments.show');
    Route::post('/assignments/{id}/submit', [StudentController::class, 'submitAssignment'])->name('assignments.submit');
    
    // Grades
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
    
    // Attendance
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
    
    // Certificates
    Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
    Route::get('/certificates/{id}', [StudentController::class, 'certificateDetail'])->name('certificates.show');
    
    // Announcements
    Route::get('/announcements', [StudentController::class, 'announcements'])->name('announcements');
    
    // Profile
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    
    // Notifications
    Route::get('/notifications', [StudentController::class, 'notifications'])->name('notifications');
});
```

---

## Best Practices

1. **Always handle errors gracefully**
   ```javascript
   try {
       const data = await StudentAPI.someMethod();
       // Process data
   } catch (error) {
       console.error('Error:', error);
       showError('container', 'Failed to load data. Please try again.');
   }
   ```

2. **Show loading states**
   ```javascript
   showLoading('container');
   const data = await StudentAPI.getData();
   displayData(data);
   ```

3. **Validate before submission**
   ```javascript
   if (!form.checkValidity()) {
       form.reportValidity();
       return;
   }
   ```

4. **Provide user feedback**
   ```javascript
   const result = await StudentAPI.submitData(data);
   alert('Success!');
   // or use toast notifications
   ```

5. **Use debouncing for search**
   ```javascript
   let searchTimeout;
   searchInput.addEventListener('input', (e) => {
       clearTimeout(searchTimeout);
       searchTimeout = setTimeout(() => {
           performSearch(e.target.value);
       }, 300);
   });
   ```

---

## Common Endpoints Reference

| Feature | Method | Endpoint | Description |
|---------|--------|----------|-------------|
| Dashboard Stats | GET | `/api/v1/students/dashboard/stats` | Get overview statistics |
| Enrolled Courses | GET | `/api/v1/students/courses` | List all enrolled courses |
| Course Detail | GET | `/api/v1/courses/{id}` | Get course details |
| Course Materials | GET | `/api/v1/courses/{id}/materials` | Get course materials/lessons |
| Assignments | GET | `/api/v1/students/assignments` | List assignments (with filters) |
| Submit Assignment | POST | `/api/v1/assignments/{id}/submit` | Submit assignment with file |
| Student Grades | GET | `/api/v1/students/grades` | Get all grades |
| Attendance Records | GET | `/api/v1/students/attendance` | Get attendance records |
| Certificates | GET | `/api/v1/students/certificates` | Get student certificates |
| Download Certificate | GET | `/api/v1/certificates/{id}/download` | Download certificate PDF |
| Notifications | GET | `/api/v1/notifications` | Get notifications |
| Mark as Read | PUT | `/api/v1/notifications/{id}/read` | Mark notification as read |

---

For complete implementation details, see [STUDENT-DASHBOARD-GUIDE.md](./STUDENT-DASHBOARD-GUIDE.md)