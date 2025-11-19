# 🚀 SmartDev LMS - API Documentation

## 📋 Overview

SmartDev LMS (Learning Management System) API menggunakan RESTful architecture dengan Laravel Sanctum untuk authentication.

---

## ⚠️ PENTING - BASE URL

API menggunakan prefix **`/api/v1/`** bukan `/api/`

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication URL (tanpa v1)
```
http://localhost:8000/api/login
http://localhost:8000/api/logout
```

---

## 🔐 Authentication

### 1. Login

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

### 2. Gunakan Token

Untuk semua endpoint yang protected, tambahkan header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Contoh dengan cURL:**
```bash
curl -X GET http://localhost:8000/api/v1/courses \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json"
```

**Contoh dengan JavaScript:**
```javascript
fetch('http://localhost:8000/api/v1/courses', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN_HERE',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})
```

### 3. Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📚 API Endpoints

### Courses
```
GET    /api/v1/courses              # List all courses
POST   /api/v1/courses              # Create new course
GET    /api/v1/courses/{id}         # Get course by ID
PUT    /api/v1/courses/{id}         # Update course
DELETE /api/v1/courses/{id}         # Delete course
```

### Assignments
```
GET    /api/v1/assignments          # List all assignments
POST   /api/v1/assignments          # Create assignment
GET    /api/v1/assignments/{id}     # Get assignment by ID
PUT    /api/v1/assignments/{id}     # Update assignment
DELETE /api/v1/assignments/{id}     # Delete assignment
```

### Submissions
```
GET    /api/v1/submissions          # List submissions
POST   /api/v1/submissions          # Submit assignment
GET    /api/v1/submissions/{id}     # Get submission
PUT    /api/v1/submissions/{id}     # Update submission
DELETE /api/v1/submissions/{id}     # Delete submission
```

### Enrollments
```
GET    /api/v1/enrollments          # List enrollments
POST   /api/v1/enrollments          # Enroll student in course
GET    /api/v1/enrollments/{id}     # Get enrollment
DELETE /api/v1/enrollments/{id}     # Unenroll student
```

### Grades
```
POST   /api/v1/grades               # Input single grade
POST   /api/v1/grades/bulk          # Bulk grade input
GET    /api/v1/grades/student       # Get student grades
GET    /api/v1/grades/course        # Get course grade summary
PUT    /api/v1/grades/{id}          # Update grade
DELETE /api/v1/grades/{id}          # Delete grade
```

### Grade Components
```
GET    /api/v1/grade-components     # List grade components
POST   /api/v1/grade-components     # Create component
GET    /api/v1/grade-components/{id} # Get component
PUT    /api/v1/grade-components/{id} # Update component
DELETE /api/v1/grade-components/{id} # Delete component
```

### Notifications
```
GET    /api/v1/notifications                  # List notifications
GET    /api/v1/notifications/{id}             # Get notification
DELETE /api/v1/notifications/{id}             # Delete notification
POST   /api/v1/notifications/{id}/mark-read   # Mark as read
POST   /api/v1/notifications/mark-all-read    # Mark all as read
GET    /api/v1/notifications/unread           # Get unread
GET    /api/v1/notifications/unread-count     # Get unread count
```

### Announcements
```
GET    /api/v1/announcements                  # List announcements
POST   /api/v1/announcements                  # Create announcement
GET    /api/v1/announcements/{id}             # Get announcement
PUT    /api/v1/announcements/{id}             # Update announcement
DELETE /api/v1/announcements/{id}             # Delete announcement
POST   /api/v1/announcements/{id}/publish     # Publish
POST   /api/v1/announcements/{id}/archive     # Archive
```

### Attendance Sessions
```
GET    /api/v1/attendance-sessions                # List sessions
POST   /api/v1/attendance-sessions                # Create session
GET    /api/v1/attendance-sessions/{id}           # Get session
PUT    /api/v1/attendance-sessions/{id}           # Update session
DELETE /api/v1/attendance-sessions/{id}           # Delete session
POST   /api/v1/attendance-sessions/{id}/open      # Open session
POST   /api/v1/attendance-sessions/{id}/close     # Close session
```

### Attendance Records
```
POST   /api/v1/attendance-sessions/{sessionId}/check-in    # Student check-in
POST   /api/v1/attendance-sessions/{sessionId}/sick-leave  # Request sick leave
POST   /api/v1/attendance-sessions/{sessionId}/permission  # Request permission
POST   /api/v1/attendance-records/bulk-mark                # Bulk mark attendance
GET    /api/v1/attendance-sessions/{sessionId}/records     # Get session records
```

### Materials
```
GET    /api/v1/materials              # List materials
POST   /api/v1/materials              # Create material
GET    /api/v1/materials/{id}         # Get material
PUT    /api/v1/materials/{id}         # Update material
DELETE /api/v1/materials/{id}         # Delete material
```

### Course Modules
```
GET    /api/v1/course-modules         # List modules
POST   /api/v1/course-modules         # Create module
GET    /api/v1/course-modules/{id}    # Get module
PUT    /api/v1/course-modules/{id}    # Update module
DELETE /api/v1/course-modules/{id}    # Delete module
```

---

## 🧪 Testing API

### Method 1: Swagger UI (Recommended)

1. Buka browser: **http://localhost:8000/api/documentation**
2. Klik tombol **"Authorize"** (kanan atas)
3. Masukkan: `Bearer YOUR_TOKEN_HERE`
4. Klik **"Authorize"**
5. Test endpoint langsung di interface

### Method 2: Insomnia / Postman

**Import Collection:**
1. Buka Insomnia/Postman
2. Import → From URL
3. Paste: **http://localhost:8000/docs/api-docs.json**
4. Done!

**Setup Environment:**
```json
{
  "base_url": "http://localhost:8000",
  "token": "YOUR_TOKEN_HERE"
}
```

**Use in requests:**
- URL: `{{base_url}}/api/v1/courses`
- Header: `Authorization: Bearer {{token}}`

### Method 3: cURL

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

**Get Courses:**
```bash
curl -X GET http://localhost:8000/api/v1/courses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Create Attendance Session:**
```bash
curl -X POST http://localhost:8000/api/v1/attendance-sessions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": 1,
    "session_name": "Week 1 Attendance",
    "status": "open",
    "deadline": "2024-12-31T23:59:59Z"
  }'
```

---

## 📝 Request Examples

### Create Course
```bash
POST /api/v1/courses
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "course_name": "Web Development 101",
  "description": "Learn web development basics",
  "instructor_id": 1,
  "credits": 3,
  "max_students": 30
}
```

### Create Assignment
```bash
POST /api/v1/assignments
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "course_id": 1,
  "title": "Week 1 Project",
  "description": "Build a simple website",
  "due_date": "2024-12-31T23:59:59Z",
  "max_score": 100
}
```

### Submit Assignment
```bash
POST /api/v1/submissions
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "assignment_id": 1,
  "file_path": "/uploads/submission.pdf",
  "notes": "My submission"
}
```

### Input Grade
```bash
POST /api/v1/grades
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "student_id": 1,
  "grade_component_id": 1,
  "score": 85.5,
  "max_score": 100,
  "notes": "Good work!"
}
```

### Create Attendance Session
```bash
POST /api/v1/attendance-sessions
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "course_id": 1,
  "session_name": "Week 1 Attendance",
  "status": "open",
  "deadline": "2024-12-31T23:59:59Z",
  "start_time": "2024-12-18T10:00:00Z",
  "end_time": "2024-12-18T12:00:00Z"
}
```

---

## 📊 Response Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 204 | No Content | Resource deleted |
| 400 | Bad Request | Invalid request |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Access denied |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

---

## 🔍 Error Handling

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Add valid Bearer token to Authorization header

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```
**Solution**: Check user role/permissions

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "deadline": ["The deadline must be a date after now."]
  }
}
```
**Solution**: Fix validation errors according to error messages

### 404 Not Found
```json
{
  "message": "No query results for model."
}
```
**Solution**: Verify resource ID exists in database

---

## 🎯 Role-Based Access

| Endpoint | Admin | Instructor | Student | Parent |
|----------|-------|------------|---------|--------|
| Manage Courses | ✅ | ✅ (own) | ❌ | ❌ |
| Create Assignments | ✅ | ✅ | ❌ | ❌ |
| Submit Assignments | ❌ | ❌ | ✅ | ❌ |
| Input Grades | ✅ | ✅ | ❌ | ❌ |
| View Grades | ✅ | ✅ | ✅ (own) | ✅ (children) |
| Mark Attendance | ✅ | ✅ | ❌ | ❌ |
| Check-in Attendance | ❌ | ❌ | ✅ | ❌ |
| Create Announcements | ✅ | ✅ | ❌ | ❌ |
| View Materials | ✅ | ✅ | ✅ (enrolled) | ✅ (children enrolled) |

---

## 🛠️ Common Issues

### Issue 1: Endpoint returns 401 without token
**Problem**: Trying to access protected endpoint without authentication

**Solution**: Add Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Issue 2: Data not saving to database
**Possible causes:**
1. Validation error (check response body)
2. Foreign key constraint (referenced record doesn't exist)
3. Missing required fields

**Debug:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test in tinker
php artisan tinker
>>> \App\Models\AttendanceSession::create([...]);
```

### Issue 3: Wrong URL (404 error)
**Problem**: Using `/api/` instead of `/api/v1/`

**Solution**: 
- ❌ Wrong: `http://localhost:8000/api/courses`
- ✅ Correct: `http://localhost:8000/api/v1/courses`

---

## 🔄 Development Commands

```bash
# Generate API documentation
php artisan l5-swagger:generate

# Clear cache
php artisan optimize:clear

# View routes
php artisan route:list | grep api

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Run migrations
php artisan migrate:fresh --seed

# View logs
tail -f storage/logs/laravel.log
```

---

## 📚 Documentation Files

- **Main Guide**: [SWAGGER_DOCUMENTATION.md](SWAGGER_DOCUMENTATION.md)
- **Quick Reference**: [API_QUICK_REFERENCE.md](API_QUICK_REFERENCE.md)
- **Troubleshooting**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Completion Summary**: [SWAGGER_COMPLETED_SUMMARY.md](SWAGGER_COMPLETED_SUMMARY.md)

---

## 🌐 Resources

- **Swagger UI**: http://localhost:8000/api/documentation
- **OpenAPI JSON**: http://localhost:8000/docs/api-docs.json
- **Laravel Documentation**: https://laravel.com/docs
- **Sanctum Documentation**: https://laravel.com/docs/sanctum

---

## ✅ Quick Checklist

Before making API requests:
- [ ] Using correct base URL: `/api/v1/`
- [ ] Token included in header: `Authorization: Bearer TOKEN`
- [ ] Content-Type header: `application/json`
- [ ] Accept header: `application/json`
- [ ] Request body is valid JSON
- [ ] Foreign keys exist in database
- [ ] User has required permissions

---

## 🆘 Support

If you encounter issues:
1. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. View logs: `tail -f storage/logs/laravel.log`
3. Test in Swagger UI: http://localhost:8000/api/documentation
4. Verify in Tinker: `php artisan tinker`

---

**Version**: 1.0.0  
**Last Updated**: December 18, 2024  
**Status**: Production Ready (Core Features)

---

**Happy Coding! 🚀**