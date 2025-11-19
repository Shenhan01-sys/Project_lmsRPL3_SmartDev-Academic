# 🗺️ API Route Structure - SmartDev LMS

## 📋 Overview

API SmartDev LMS memiliki 2 kategori endpoint dengan prefix yang berbeda:

---

## 🔓 PUBLIC ENDPOINTS (Tanpa Prefix `/v1`)

Endpoint yang **TIDAK memerlukan authentication** atau endpoint auth:

### Base URL: `http://localhost:8000/api`

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | User login | ❌ No |
| POST | `/api/forgot-password` | Request password reset | ❌ No |
| POST | `/api/reset-password` | Reset password | ❌ No |
| POST | `/api/register-calon-siswa` | Student registration | ❌ No |

---

## 🔐 PROTECTED ENDPOINTS (Prefix `/v1`)

Endpoint yang **MEMERLUKAN authentication token**:

### Base URL: `http://localhost:8000/api/v1`

⚠️ **PENTING**: Semua endpoint di bawah ini HARUS menyertakan token!

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📚 RESOURCE ENDPOINTS (All with `/api/v1` prefix)

### 1. **User Management**
```
GET    /api/v1/users                    # List all users
POST   /api/v1/users                    # Create user
GET    /api/v1/users/{id}               # Get user by ID
PUT    /api/v1/users/{id}               # Update user
DELETE /api/v1/users/{id}               # Delete user
GET    /api/v1/instructors              # List instructors
```

### 2. **Students**
```
GET    /api/v1/students                 # List all students
POST   /api/v1/students                 # Create student
GET    /api/v1/students/{id}            # Get student by ID
PUT    /api/v1/students/{id}            # Update student
DELETE /api/v1/students/{id}            # Delete student
GET    /api/v1/students/{id}/enrollments  # Get student enrollments
GET    /api/v1/students/{id}/submissions  # Get student submissions
```

### 3. **Instructors**
```
GET    /api/v1/instructors              # List all instructors
POST   /api/v1/instructors              # Create instructor
GET    /api/v1/instructors/{id}         # Get instructor by ID
PUT    /api/v1/instructors/{id}         # Update instructor
DELETE /api/v1/instructors/{id}         # Delete instructor
```

### 4. **Parents**
```
GET    /api/v1/parents                  # List all parents
POST   /api/v1/parents                  # Create parent
GET    /api/v1/parents/{id}             # Get parent by ID
PUT    /api/v1/parents/{id}             # Update parent
DELETE /api/v1/parents/{id}             # Delete parent
```

### 5. **Courses**
```
GET    /api/v1/courses                  # List all courses
POST   /api/v1/courses                  # Create course
GET    /api/v1/courses/{id}             # Get course by ID
PUT    /api/v1/courses/{id}             # Update course
DELETE /api/v1/courses/{id}             # Delete course
```

### 6. **Course Modules**
```
GET    /api/v1/course-modules           # List all modules
POST   /api/v1/course-modules           # Create module
GET    /api/v1/course-modules/{id}      # Get module by ID
PUT    /api/v1/course-modules/{id}      # Update module
DELETE /api/v1/course-modules/{id}      # Delete module
GET    /api/v1/course-modules/browse    # Browse all modules (preview mode)
GET    /api/v1/course-modules/my-modules  # Get user's modules
```

### 7. **Materials**
```
GET    /api/v1/materials                # List all materials
POST   /api/v1/materials                # Create material
GET    /api/v1/materials/{id}           # Get material by ID
PUT    /api/v1/materials/{id}           # Update material
DELETE /api/v1/materials/{id}           # Delete material
GET    /api/v1/materials/browse         # Browse all materials (preview mode)
GET    /api/v1/materials/my-materials   # Get user's materials
```

### 8. **Assignments**
```
GET    /api/v1/assignments              # List all assignments
POST   /api/v1/assignments              # Create assignment
GET    /api/v1/assignments/{id}         # Get assignment by ID
PUT    /api/v1/assignments/{id}         # Update assignment
DELETE /api/v1/assignments/{id}         # Delete assignment
```

### 9. **Submissions**
```
GET    /api/v1/submissions              # List all submissions
POST   /api/v1/submissions              # Create submission
GET    /api/v1/submissions/{id}         # Get submission by ID
PUT    /api/v1/submissions/{id}         # Update submission
DELETE /api/v1/submissions/{id}         # Delete submission
```

### 10. **Enrollments**
```
GET    /api/v1/enrollments              # List all enrollments
POST   /api/v1/enrollments              # Enroll student
GET    /api/v1/enrollments/{id}         # Get enrollment by ID
DELETE /api/v1/enrollments/{id}         # Unenroll student
```

### 11. **Grades**
```
POST   /api/v1/grades                   # Input single grade
POST   /api/v1/grades/bulk              # Bulk grade input
GET    /api/v1/grades/student           # Get student grades
                                         # Params: ?student_id={id}&course_id={id}
GET    /api/v1/grades/course            # Get course grade summary
                                         # Params: ?course_id={id}
PUT    /api/v1/grades/{id}              # Update grade
DELETE /api/v1/grades/{id}              # Delete grade
```

### 12. **Grade Components**
```
GET    /api/v1/grade-components         # List grade components
                                         # Params: ?course_id={id}
POST   /api/v1/grade-components         # Create component
GET    /api/v1/grade-components/{id}    # Get component by ID
PUT    /api/v1/grade-components/{id}    # Update component
DELETE /api/v1/grade-components/{id}    # Delete component
```

### 13. **Notifications**
```
GET    /api/v1/notifications                    # List notifications
GET    /api/v1/notifications/{id}               # Get notification
DELETE /api/v1/notifications/{id}               # Delete notification
POST   /api/v1/notifications/{id}/mark-read     # Mark as read
POST   /api/v1/notifications/{id}/mark-unread   # Mark as unread
POST   /api/v1/notifications/mark-all-read      # Mark all as read
GET    /api/v1/notifications/unread             # Get unread notifications
GET    /api/v1/notifications/read               # Get read notifications
GET    /api/v1/notifications/type/{type}        # Get by type
GET    /api/v1/notifications/unread-count       # Get unread count
POST   /api/v1/notifications/bulk-mark-read     # Bulk mark as read
POST   /api/v1/notifications/bulk-delete        # Bulk delete
POST   /api/v1/notifications/delete-all-read    # Delete all read
```

### 14. **Announcements**
```
GET    /api/v1/announcements                    # List announcements
POST   /api/v1/announcements                    # Create announcement
GET    /api/v1/announcements/{id}               # Get announcement
PUT    /api/v1/announcements/{id}               # Update announcement
DELETE /api/v1/announcements/{id}               # Delete announcement
POST   /api/v1/announcements/{id}/publish       # Publish announcement
POST   /api/v1/announcements/{id}/archive       # Archive announcement
GET    /api/v1/announcements/course/{courseId}  # Get course announcements
GET    /api/v1/announcements/global/list        # Get global announcements
GET    /api/v1/announcements/active/list        # Get active announcements
```

### 15. **Attendance Sessions**
```
GET    /api/v1/attendance-sessions                           # List sessions
POST   /api/v1/attendance-sessions                           # Create session
GET    /api/v1/attendance-sessions/{id}                      # Get session
PUT    /api/v1/attendance-sessions/{id}                      # Update session
DELETE /api/v1/attendance-sessions/{id}                      # Delete session
POST   /api/v1/attendance-sessions/{id}/open                 # Open session
POST   /api/v1/attendance-sessions/{id}/close                # Close session
POST   /api/v1/attendance-sessions/{id}/auto-mark-absent     # Auto mark absent
GET    /api/v1/attendance-sessions/{id}/summary              # Get summary
GET    /api/v1/attendance-sessions/course/{courseId}/all     # Get course sessions
GET    /api/v1/attendance-sessions/course/{courseId}/active  # Get active sessions
```

### 16. **Attendance Records**
```
POST   /api/v1/attendance-records/check-in/{sessionId}       # Student check-in
POST   /api/v1/attendance-records/sick-leave/{sessionId}     # Request sick leave
POST   /api/v1/attendance-records/permission/{sessionId}     # Request permission
POST   /api/v1/attendance-records/mark/{sessionId}/{enrollmentId}  # Mark attendance
POST   /api/v1/attendance-records/{recordId}/approve         # Approve request
POST   /api/v1/attendance-records/{recordId}/reject          # Reject request
POST   /api/v1/attendance-records/bulk-mark/{sessionId}      # Bulk mark attendance
GET    /api/v1/attendance-records/session/{sessionId}/records  # Get session records
GET    /api/v1/attendance-records/student/{studentId}/course/{courseId}/history  # Student history
GET    /api/v1/attendance-records/student/{studentId}/course/{courseId}/stats    # Student stats
GET    /api/v1/attendance-records/course/{courseId}/needs-review  # Records needing review
```

### 17. **Certificates**
```
GET    /api/v1/certificates             # List certificates
POST   /api/v1/certificates             # Create certificate
GET    /api/v1/certificates/{id}        # Get certificate
PUT    /api/v1/certificates/{id}        # Update certificate
DELETE /api/v1/certificates/{id}        # Delete certificate
```

---

## 🔐 AUTHENTICATED ENDPOINTS (No `/v1` prefix)

These are under `auth:sanctum` but NOT in `/v1` group:

### User & Auth Management
```
GET    /api/user                        # Get authenticated user
POST   /api/logout                      # Logout user
POST   /api/change-password             # Change password
```

### Registration (Authenticated)
```
POST   /api/upload-documents            # Upload registration documents
GET    /api/registration-status         # Get registration status
```

### File Uploads (Authenticated)
```
POST   /api/upload/profile-photo        # Upload profile photo
POST   /api/upload/material/{materialId}     # Upload material file
POST   /api/upload/assignment/{assignmentId} # Upload assignment file
POST   /api/upload/submission/{submissionId} # Upload submission file
DELETE /api/upload/file                 # Delete file
GET    /api/upload/file-info            # Get file info
```

---

## 📊 SUMMARY TABLE

| Category | Prefix | Auth Required | Example |
|----------|--------|---------------|---------|
| **Public Auth** | `/api` | ❌ No | `/api/login` |
| **Protected Resources** | `/api/v1` | ✅ Yes | `/api/v1/courses` |
| **User Management** | `/api` | ✅ Yes | `/api/user` |
| **File Uploads** | `/api/upload` | ✅ Yes | `/api/upload/profile-photo` |

---

## 🎯 QUICK REFERENCE

### Login Flow:
```
1. POST /api/login
   → Get token

2. Use token in all subsequent requests:
   Authorization: Bearer YOUR_TOKEN

3. Access protected endpoints:
   GET /api/v1/courses
   POST /api/v1/attendance-sessions
   etc.
```

### URL Pattern:
```
✅ CORRECT:
- http://localhost:8000/api/login
- http://localhost:8000/api/v1/courses
- http://localhost:8000/api/v1/attendance-sessions
- http://localhost:8000/api/user

❌ WRONG:
- http://localhost:8000/api/courses (missing /v1)
- http://localhost:8000/api/v1/login (has /v1, should not)
- http://localhost:8000/v1/courses (missing /api)
```

---

## 🔍 HOW TO VERIFY

Check all routes:
```bash
php artisan route:list | grep api
```

Check specific resource:
```bash
php artisan route:list | grep attendance-sessions
```

Check authentication middleware:
```bash
php artisan route:list | grep "auth:sanctum"
```

---

## ✅ CHECKLIST FOR API CALLS

- [ ] Using correct base URL: `/api/v1/` for resources
- [ ] Using `/api/` for login/auth endpoints
- [ ] Token included in header: `Authorization: Bearer TOKEN`
- [ ] Content-Type: `application/json`
- [ ] Accept: `application/json`
- [ ] Valid JSON in request body
- [ ] All required fields provided
- [ ] Foreign keys exist in database

---

## 📚 RESOURCES

- **API Documentation**: http://localhost:8000/api/documentation
- **OpenAPI JSON**: http://localhost:8000/docs/api-docs.json
- **Route List Command**: `php artisan route:list`

---

**Last Updated**: December 18, 2024  
**Version**: 1.0.0  
**Status**: Production Ready