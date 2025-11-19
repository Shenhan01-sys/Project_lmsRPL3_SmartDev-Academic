# SmartDev LMS - API Quick Reference Guide

## 🚀 Quick Start

### Base URL
```
http://localhost:8000/api/v1
```

⚠️ **PENTING**: API menggunakan prefix `/api/v1/` bukan `/api/`

### Documentation URLs
- **Swagger UI**: http://localhost:8000/api/documentation
- **JSON Export**: http://localhost:8000/docs/api-docs.json

---

## 🔐 Authentication

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response: { "token": "1|xxx...", "user": {...} }
```

### Register
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "student"
}
```

### Use Token
Add to all authenticated requests:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📚 COURSES

```http
GET    /api/v1/courses              # List all courses
POST   /api/v1/courses              # Create course
GET    /api/v1/courses/{id}         # Get course details
PUT    /api/v1/courses/{id}         # Update course
DELETE /api/v1/courses/{id}         # Delete course
GET    /api/v1/courses/{id}/students     # Get enrolled students
GET    /api/v1/courses/{id}/modules      # Get course modules
```

**Create Course Example:**
```json
{
  "course_name": "Web Development 101",
  "description": "Learn web development basics",
  "instructor_id": 1,
  "credits": 3,
  "max_students": 30
}
```

---

## 📝 ASSIGNMENTS

```http
GET    /api/v1/assignments              # List assignments
POST   /api/v1/assignments              # Create assignment
GET    /api/v1/assignments/{id}         # Get assignment
PUT    /api/v1/assignments/{id}         # Update assignment
DELETE /api/v1/assignments/{id}         # Delete assignment
GET    /api/v1/assignments/{id}/submissions  # Get submissions
```

**Create Assignment Example:**
```json
{
  "course_id": 1,
  "title": "Week 1 Project",
  "description": "Build a simple website",
  "due_date": "2024-12-31T23:59:59Z",
  "max_score": 100
}
```

---

## 📤 SUBMISSIONS

```http
GET    /api/v1/submissions          # List submissions
POST   /api/v1/submissions          # Submit assignment
GET    /api/v1/submissions/{id}     # Get submission
PUT    /api/v1/submissions/{id}     # Update submission
DELETE /api/v1/submissions/{id}     # Delete submission
```

**Submit Assignment Example:**
```json
{
  "assignment_id": 1,
  "file_path": "/uploads/submission.pdf",
  "notes": "My submission"
}
```

---

## 👥 ENROLLMENTS

```http
GET    /api/v1/enrollments          # List enrollments
POST   /api/v1/enrollments          # Enroll student
GET    /api/v1/enrollments/{id}     # Get enrollment
DELETE /api/v1/enrollments/{id}     # Unenroll student
```

**Enroll Student Example:**
```json
{
  "student_id": 1,
  "course_id": 1
}
```

---

## 📊 GRADES

```http
POST   /api/v1/grades                    # Input single grade
POST   /api/v1/grades/bulk               # Bulk grade input
GET    /api/v1/grades/student?student_id={id}&course_id={id}  # Student grades
GET    /api/v1/grades/course?course_id={id}                   # Course summary
PUT    /api/v1/grades/{id}               # Update grade
DELETE /api/v1/grades/{id}               # Delete grade
```

**Input Grade Example:**
```json
{
  "student_id": 1,
  "grade_component_id": 1,
  "score": 85.5,
  "max_score": 100,
  "notes": "Good work!"
}
```

**Bulk Input Example:**
```json
{
  "grades": [
    {
      "student_id": 1,
      "grade_component_id": 1,
      "score": 85,
      "max_score": 100
    },
    {
      "student_id": 2,
      "grade_component_id": 1,
      "score": 90,
      "max_score": 100
    }
  ]
}
```

---

## 🎯 GRADE COMPONENTS

```http
GET    /api/v1/grade-components?course_id={id}  # List components
POST   /api/v1/grade-components                 # Create component
GET    /api/v1/grade-components/{id}            # Get component
PUT    /api/v1/grade-components/{id}            # Update component
DELETE /api/v1/grade-components/{id}            # Delete component
```

**Create Component Example:**
```json
{
  "course_id": 1,
  "name": "Midterm Exam",
  "description": "Midterm examination",
  "weight": 30.0,
  "max_score": 100,
  "is_active": true
}
```

---

## 🔔 NOTIFICATIONS

```http
GET    /api/v1/notifications                    # List notifications
GET    /api/v1/notifications/{id}               # Get notification
DELETE /api/v1/notifications/{id}               # Delete notification
POST   /api/v1/notifications/{id}/mark-read     # Mark as read
POST   /api/v1/notifications/{id}/mark-unread   # Mark as unread
POST   /api/v1/notifications/mark-all-read      # Mark all as read
GET    /api/v1/notifications/unread             # Get unread
GET    /api/v1/notifications/unread-count       # Get unread count
POST   /api/v1/notifications/bulk-mark-read     # Bulk mark read
POST   /api/v1/notifications/bulk-delete        # Bulk delete
```

**Query Parameters:**
- `notification_type` - Filter by type
- `is_read` - Filter by read status (true/false)
- `priority` - Filter by priority (low, normal, high)

---

## 📢 ANNOUNCEMENTS

```http
GET    /api/v1/announcements                    # List announcements
POST   /api/v1/announcements                    # Create announcement
GET    /api/v1/announcements/{id}               # Get announcement
PUT    /api/v1/announcements/{id}               # Update announcement
DELETE /api/v1/announcements/{id}               # Delete announcement
POST   /api/v1/announcements/{id}/publish       # Publish
POST   /api/v1/announcements/{id}/archive       # Archive
GET    /api/v1/announcements/course/{courseId}  # Course announcements
GET    /api/v1/announcements/global             # Global announcements
GET    /api/v1/announcements/active             # Active announcements
```

**Create Announcement Example:**
```json
{
  "title": "Important Update",
  "content": "Class will be held online tomorrow",
  "announcement_type": "course",
  "priority": "high",
  "course_id": 1,
  "status": "published",
  "published_at": "2024-12-18T10:00:00Z"
}
```

---

## ✅ ATTENDANCE SESSIONS

```http
GET    /api/v1/attendance-sessions                      # List sessions
POST   /api/v1/attendance-sessions                      # Create session
GET    /api/v1/attendance-sessions/{id}                 # Get session
PUT    /api/v1/attendance-sessions/{id}                 # Update session
DELETE /api/v1/attendance-sessions/{id}                 # Delete session
POST   /api/v1/attendance-sessions/{id}/open            # Open session
POST   /api/v1/attendance-sessions/{id}/close           # Close session
POST   /api/v1/attendance-sessions/{id}/auto-mark-absent # Auto mark absent
GET    /api/v1/attendance-sessions/{id}/summary         # Get summary
GET    /api/v1/attendance-sessions/course/{courseId}    # Course sessions
GET    /api/v1/attendance-sessions/course/{courseId}/active # Active sessions
```

**Create Session Example:**
```json
{
  "course_id": 1,
  "session_name": "Week 1 Attendance",
  "status": "open",
  "deadline": "2024-12-18T14:00:00Z",
  "start_time": "2024-12-18T10:00:00Z",
  "end_time": "2024-12-18T12:00:00Z"
}
```

---

## 📝 ATTENDANCE RECORDS

```http
POST   /api/v1/attendance-sessions/{sessionId}/check-in      # Student check-in
POST   /api/v1/attendance-sessions/{sessionId}/sick-leave    # Request sick leave
POST   /api/v1/attendance-sessions/{sessionId}/permission    # Request permission
POST   /api/v1/attendance-records/bulk-mark                  # Bulk mark attendance
GET    /api/v1/attendance-sessions/{sessionId}/records       # Get session records
GET    /api/v1/students/{studentId}/attendance/{courseId}    # Student history
GET    /api/v1/students/{studentId}/attendance/{courseId}/stats # Attendance stats
```

**Check-in (Student):**
```http
POST /api/v1/attendance-sessions/1/check-in
Authorization: Bearer STUDENT_TOKEN
```

**Request Sick Leave:**
```json
{
  "notes": "I have flu and fever",
  "attachment": "/uploads/sick-note.pdf"
}
```

**Bulk Mark Attendance:**
```json
{
  "records": [
    {
      "enrollment_id": 1,
      "attendance_session_id": 1,
      "status": "present",
      "notes": ""
    },
    {
      "enrollment_id": 2,
      "attendance_session_id": 1,
      "status": "absent",
      "notes": ""
    }
  ]
}
```

---

## 📖 MATERIALS

```http
GET    /api/v1/materials              # List materials
POST   /api/v1/materials              # Create material
GET    /api/v1/materials/{id}         # Get material
PUT    /api/v1/materials/{id}         # Update material
DELETE /api/v1/materials/{id}         # Delete material
GET    /api/v1/materials/browse       # Browse all materials
GET    /api/v1/materials/my-materials # Get my materials
```

**Create Material Example:**
```json
{
  "course_module_id": 1,
  "title": "Introduction to Programming",
  "content_type": "document",
  "content_path": "/materials/intro.pdf",
  "description": "Basic programming concepts"
}
```

---

## 📚 COURSE MODULES

```http
GET    /api/v1/course-modules              # List modules
POST   /api/v1/course-modules              # Create module
GET    /api/v1/course-modules/{id}         # Get module
PUT    /api/v1/course-modules/{id}         # Update module
DELETE /api/v1/course-modules/{id}         # Delete module
GET    /api/v1/course-modules/browse       # Browse modules
GET    /api/v1/course-modules/my-modules   # Get my modules
```

**Create Module Example:**
```json
{
  "course_id": 1,
  "module_name": "Week 1: Introduction",
  "module_order": 1,
  "description": "Introduction to the course"
}
```

---

## 📋 RESPONSE CODES

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Resource deleted successfully |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Access denied |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

---

## 🎯 COMMON QUERY PARAMETERS

### Pagination
```
?page=1&per_page=15
```

### Filtering
```
?status=active
?course_id=1
?student_id=1
```

### Search
```
?search=John
```

### Sorting
```
?sort_by=created_at&sort_order=desc
```

---

## 🔧 HEADERS

### Required for All Authenticated Requests
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

---

## 💡 TIPS

1. **Always include token** for protected endpoints
2. **Check response codes** for error handling
3. **Use filtering** to reduce response size
4. **Handle pagination** for large datasets
5. **Validate data** before sending requests
6. **Use bulk operations** for efficiency
7. **Check enrollment** before accessing course content
8. **Review permissions** for each role

---

## 🚨 COMMON ERRORS

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Add valid Bearer token

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
    "email": ["The email has already been taken."]
  }
}
```
**Solution**: Fix validation errors

### 404 Not Found
```json
{
  "message": "Resource not found."
}
```
**Solution**: Check resource ID exists

---

## 🎓 ROLE PERMISSIONS

| Feature | Admin | Instructor | Student | Parent |
|---------|-------|------------|---------|--------|
| Manage Courses | ✅ | ✅ (own) | ❌ | ❌ |
| Create Assignments | ✅ | ✅ (own courses) | ❌ | ❌ |
| Submit Assignments | ❌ | ❌ | ✅ | ❌ |
| Input Grades | ✅ | ✅ (own courses) | ❌ | ❌ |
| View Grades | ✅ | ✅ (own courses) | ✅ (own) | ✅ (children) |
| Mark Attendance | ✅ | ✅ (own courses) | ❌ | ❌ |
| Check-in Attendance | ❌ | ❌ | ✅ | ❌ |
| Create Announcements | ✅ | ✅ | ❌ | ❌ |
| View Announcements | ✅ | ✅ | ✅ | ✅ |

---

## 📞 SUPPORT

- **Documentation**: http://localhost:8000/api/documentation
- **GitHub**: [Your Repository]
- **Email**: admin@smartdevlms.com

---

**Last Updated**: December 18, 2024  
**Version**: 1.0.0  
**Status**: Production Ready (Core Features)