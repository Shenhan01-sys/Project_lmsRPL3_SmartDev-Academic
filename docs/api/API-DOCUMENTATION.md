# API Documentation - LMS SmartDev

> **Comprehensive API Documentation for Learning Management System**
> 
> Version: 1.0
> Last Updated: December 2024

---

## Table of Contents

1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [Base URL](#base-url)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [API Endpoints](#api-endpoints)
   - [Authentication](#auth-endpoints)
   - [Users](#user-endpoints)
   - [Courses](#course-endpoints)
   - [Enrollments](#enrollment-endpoints)
   - [Assignments & Submissions](#assignment-endpoints)
   - [Grades](#grade-endpoints)
   - [Announcements](#announcement-endpoints)
   - [Notifications](#notification-endpoints)
   - [Attendance](#attendance-endpoints)
   - [Certificates](#certificate-endpoints)
7. [Rate Limiting](#rate-limiting)
8. [Changelog](#changelog)

---

## Introduction

The LMS SmartDev API is a RESTful API that provides access to all features of the Learning Management System. This API is designed for building web and mobile applications that interact with the LMS platform.

### Key Features
- 🔐 Token-based authentication (Laravel Sanctum)
- 📚 Complete course management
- 👥 User role management (Admin, Instructor, Student, Parent)
- 📝 Assignment and submission tracking
- 📊 Grading system
- 📢 Announcements and notifications
- 📅 Attendance tracking
- 🎓 Certificate generation and verification

---

## Authentication

### Authentication Method
This API uses **Laravel Sanctum** for token-based authentication.

### Obtaining Access Token

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "student@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "access_token": "1|abc123def456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "student@example.com",
    "role": "student"
  }
}
```

### Using the Token

Include the token in the `Authorization` header for all subsequent requests:

```
Authorization: Bearer 1|abc123def456...
```

### Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

---

## Base URL

```
Production: https://api.lms-smartdev.com/api
Development: http://localhost:8000/api
```

All endpoints should be prefixed with `/api/v1/` for versioned access.

---

## Response Format

### Success Response

```json
{
  "data": {
    // Response data here
  },
  "message": "Success message"
}
```

### Paginated Response

```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "http://localhost:8000/api/v1/courses?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://localhost:8000/api/v1/courses?page=5",
  "next_page_url": "http://localhost:8000/api/v1/courses?page=2",
  "path": "http://localhost:8000/api/v1/courses",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 67
}
```

---

## Error Handling

### Error Response Format

```json
{
  "message": "Error message",
  "error": "Detailed error description",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

---

## API Endpoints

## Auth Endpoints

### 1. Login

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "student"
  }
}
```

---

### 2. Register Student (Calon Siswa)

**Endpoint:** `POST /api/register-calon-siswa`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "date_of_birth": "2005-01-15"
}
```

**Response (201):**
```json
{
  "message": "Registration successful. Please wait for admin approval.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student"
  }
}
```

---

### 3. Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Successfully logged out"
}
```

---

### 4. Change Password

**Endpoint:** `POST /api/change-password`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "message": "Password changed successfully"
}
```

---

## User Endpoints

### 1. Get Current User

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "student",
  "student": {
    "id": 1,
    "student_number": "STD001",
    "enrollment_year": 2024
  }
}
```

---

### 2. List Users (Admin Only)

**Endpoint:** `GET /api/v1/users`

**Query Parameters:**
- `role` (optional): Filter by role (admin, instructor, student, parent)
- `page` (optional): Page number for pagination

**Response (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student",
      "created_at": "2024-01-15T10:00:00.000000Z"
    }
  ],
  "total": 50
}
```

---

## Course Endpoints

### 1. List Courses

**Endpoint:** `GET /api/v1/courses`

**Query Parameters:**
- `instructor_id` (optional): Filter by instructor
- `page` (optional): Page number

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "course_code": "CS101",
      "course_name": "Introduction to Computer Science",
      "description": "Basic concepts of computer science",
      "instructor": {
        "id": 1,
        "user": {
          "name": "Dr. Smith"
        }
      },
      "created_at": "2024-01-10T00:00:00.000000Z"
    }
  ]
}
```

---

### 2. Create Course

**Endpoint:** `POST /api/v1/courses`

**Authorization:** Admin or Instructor

**Request Body:**
```json
{
  "course_code": "CS101",
  "course_name": "Introduction to Computer Science",
  "description": "Basic concepts of computer science",
  "instructor_id": 1
}
```

**Response (201):**
```json
{
  "id": 1,
  "course_code": "CS101",
  "course_name": "Introduction to Computer Science",
  "description": "Basic concepts of computer science",
  "instructor_id": 1,
  "created_at": "2024-01-10T00:00:00.000000Z"
}
```

---

### 3. Get Course Details

**Endpoint:** `GET /api/v1/courses/{id}`

**Response (200):**
```json
{
  "id": 1,
  "course_code": "CS101",
  "course_name": "Introduction to Computer Science",
  "description": "Basic concepts",
  "instructor": {
    "id": 1,
    "user": {
      "name": "Dr. Smith"
    }
  },
  "enrollments": [
    {
      "id": 1,
      "student": {
        "id": 1,
        "user": {
          "name": "John Doe"
        }
      },
      "status": "active"
    }
  ],
  "course_modules": [],
  "assignments": []
}
```

---

### 4. Update Course

**Endpoint:** `PUT /api/v1/courses/{id}`

**Authorization:** Admin or Course Instructor

**Request Body:**
```json
{
  "course_code": "CS101",
  "course_name": "Introduction to Computer Science - Updated",
  "description": "Updated description",
  "instructor_id": 1
}
```

**Response (200):**
```json
{
  "id": 1,
  "course_code": "CS101",
  "course_name": "Introduction to Computer Science - Updated",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

---

### 5. Delete Course

**Endpoint:** `DELETE /api/v1/courses/{id}`

**Authorization:** Admin or Course Instructor

**Response (200):**
```json
{
  "message": "Course deleted successfully"
}
```

---

## Enrollment Endpoints

### 1. List Enrollments

**Endpoint:** `GET /api/v1/enrollments`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `student_id` (optional): Filter by student
- `status` (optional): Filter by status (active, completed, dropped)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "course_id": 1,
      "status": "active",
      "enrollment_date": "2024-01-15",
      "student": {
        "id": 1,
        "user": {
          "name": "John Doe"
        }
      },
      "course": {
        "id": 1,
        "course_name": "Computer Science 101"
      }
    }
  ]
}
```

---

### 2. Enroll Student

**Endpoint:** `POST /api/v1/enrollments`

**Authorization:** Admin or Student (self-enrollment if allowed)

**Request Body:**
```json
{
  "student_id": 1,
  "course_id": 1
}
```

**Response (201):**
```json
{
  "id": 1,
  "student_id": 1,
  "course_id": 1,
  "status": "active",
  "enrollment_date": "2024-01-15",
  "message": "Enrollment successful"
}
```

---

### 3. Update Enrollment Status

**Endpoint:** `PUT /api/v1/enrollments/{id}`

**Authorization:** Admin or Instructor

**Request Body:**
```json
{
  "status": "completed"
}
```

**Response (200):**
```json
{
  "id": 1,
  "status": "completed",
  "message": "Enrollment status updated"
}
```

---

## Assignment Endpoints

### 1. List Assignments

**Endpoint:** `GET /api/v1/assignments`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `status` (optional): Filter by status (draft, published, closed)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Assignment 1: Variables and Data Types",
      "description": "Complete the exercises",
      "due_date": "2024-02-01T23:59:59.000000Z",
      "max_score": 100,
      "status": "published",
      "course": {
        "course_name": "Computer Science 101"
      }
    }
  ]
}
```

---

### 2. Create Assignment

**Endpoint:** `POST /api/v1/assignments`

**Authorization:** Instructor (for their courses) or Admin

**Request Body:**
```json
{
  "course_id": 1,
  "title": "Assignment 1: Variables",
  "description": "Complete exercises on variables",
  "due_date": "2024-02-01 23:59:59",
  "max_score": 100,
  "status": "published"
}
```

**Response (201):**
```json
{
  "id": 1,
  "course_id": 1,
  "title": "Assignment 1: Variables",
  "due_date": "2024-02-01T23:59:59.000000Z",
  "max_score": 100,
  "status": "published",
  "created_at": "2024-01-15T10:00:00.000000Z"
}
```

---

### 3. Submit Assignment

**Endpoint:** `POST /api/v1/submissions`

**Authorization:** Student (enrolled in the course)

**Request Body:**
```json
{
  "assignment_id": 1,
  "enrollment_id": 1,
  "submission_text": "My solution for the assignment...",
  "file_path": "/uploads/submissions/file.pdf"
}
```

**Response (201):**
```json
{
  "id": 1,
  "assignment_id": 1,
  "enrollment_id": 1,
  "submission_text": "My solution...",
  "file_path": "/uploads/submissions/file.pdf",
  "submitted_at": "2024-01-20T15:30:00.000000Z",
  "status": "submitted"
}
```

---

### 4. Grade Submission

**Endpoint:** `PUT /api/v1/submissions/{id}`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "score": 85,
  "feedback": "Good work! Pay attention to edge cases.",
  "status": "graded"
}
```

**Response (200):**
```json
{
  "id": 1,
  "score": 85,
  "feedback": "Good work!",
  "status": "graded",
  "graded_at": "2024-01-22T10:00:00.000000Z"
}
```

---

## Grade Endpoints

### 1. List Grades

**Endpoint:** `GET /api/v1/grades`

**Query Parameters:**
- `enrollment_id` (optional): Filter by enrollment
- `grade_component_id` (optional): Filter by component

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "enrollment_id": 1,
      "grade_component_id": 1,
      "score": 85.5,
      "grade_letter": "B",
      "notes": "Good performance",
      "component": {
        "name": "Midterm Exam",
        "weight": 30
      }
    }
  ]
}
```

---

### 2. Create/Update Grade

**Endpoint:** `POST /api/v1/grades`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "enrollment_id": 1,
  "grade_component_id": 1,
  "score": 85.5,
  "notes": "Good performance on midterm"
}
```

**Response (201):**
```json
{
  "id": 1,
  "enrollment_id": 1,
  "grade_component_id": 1,
  "score": 85.5,
  "grade_letter": "B",
  "notes": "Good performance on midterm",
  "created_at": "2024-01-15T10:00:00.000000Z"
}
```

---

### 3. Get Student Grades

**Endpoint:** `GET /api/v1/grades/student?enrollment_id={id}`

**Authorization:** Student (own grades), Instructor, or Admin

**Response (200):**
```json
{
  "enrollment": {
    "id": 1,
    "student": {
      "user": {
        "name": "John Doe"
      }
    },
    "course": {
      "course_name": "Computer Science 101"
    }
  },
  "grades": [
    {
      "component": "Midterm Exam",
      "weight": 30,
      "score": 85.5,
      "grade_letter": "B"
    }
  ],
  "final_grade": 87.2
}
```

---

## Announcement Endpoints

### 1. List Announcements

**Endpoint:** `GET /api/v1/announcements`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `announcement_type` (optional): global or course
- `status` (optional): draft, published, archived
- `priority` (optional): normal, high, urgent

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Midterm Exam Schedule",
      "content": "The midterm exam will be held on...",
      "announcement_type": "course",
      "priority": "high",
      "status": "published",
      "published_at": "2024-01-15T08:00:00.000000Z",
      "course": {
        "id": 1,
        "course_name": "Computer Science 101"
      },
      "creator": {
        "name": "Dr. Smith"
      }
    }
  ]
}
```

---

### 2. Create Announcement

**Endpoint:** `POST /api/v1/announcements`

**Authorization:** Instructor (for course) or Admin (for global)

**Request Body:**
```json
{
  "title": "Midterm Exam Schedule",
  "content": "The midterm exam will be held on February 15, 2024",
  "announcement_type": "course",
  "course_id": 1,
  "priority": "high",
  "status": "published"
}
```

**Response (201):**
```json
{
  "id": 1,
  "title": "Midterm Exam Schedule",
  "content": "The midterm exam...",
  "announcement_type": "course",
  "priority": "high",
  "status": "published",
  "created_at": "2024-01-15T10:00:00.000000Z"
}
```

---

### 3. Get Announcement Details

**Endpoint:** `GET /api/v1/announcements/{id}`

**Response (200):**
```json
{
  "id": 1,
  "title": "Midterm Exam Schedule",
  "content": "Full announcement content...",
  "announcement_type": "course",
  "priority": "high",
  "status": "published",
  "view_count": 45,
  "course": {
    "id": 1,
    "course_name": "Computer Science 101"
  },
  "creator": {
    "name": "Dr. Smith"
  }
}
```

---

### 4. Publish Announcement

**Endpoint:** `POST /api/v1/announcements/{id}/publish`

**Authorization:** Creator or Admin

**Response (200):**
```json
{
  "message": "Announcement published successfully",
  "data": {
    "id": 1,
    "status": "published",
    "published_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

---

### 5. Archive Announcement

**Endpoint:** `POST /api/v1/announcements/{id}/archive`

**Authorization:** Creator or Admin

**Response (200):**
```json
{
  "message": "Announcement archived successfully",
  "data": {
    "id": 1,
    "status": "archived"
  }
}
```

---

### 6. Get Course Announcements

**Endpoint:** `GET /api/v1/announcements/course/{courseId}`

**Authorization:** Enrolled student, Instructor, or Admin

**Response (200):**
```json
[
  {
    "id": 1,
    "title": "Course Announcement",
    "announcement_type": "course",
    "priority": "high",
    "published_at": "2024-01-15T08:00:00.000000Z"
  },
  {
    "id": 2,
    "title": "Global Announcement",
    "announcement_type": "global",
    "priority": "urgent",
    "published_at": "2024-01-14T08:00:00.000000Z"
  }
]
```

---

### 7. Get Active Announcements

**Endpoint:** `GET /api/v1/announcements/active/list`

**Response (200):**
```json
[
  {
    "id": 1,
    "title": "Active Announcement",
    "priority": "high",
    "published_at": "2024-01-15T08:00:00.000000Z",
    "expires_at": null
  }
]
```

---

## Notification Endpoints

### 1. List Notifications

**Endpoint:** `GET /api/v1/notifications`

**Query Parameters:**
- `notification_type` (optional): assignment, grade, announcement, enrollment
- `is_read` (optional): true or false
- `priority` (optional): normal, high, urgent

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "New Assignment Posted",
      "message": "A new assignment has been posted in Computer Science 101",
      "notification_type": "assignment",
      "priority": "normal",
      "is_read": false,
      "action_url": "/assignments/1",
      "created_at": "2024-01-15T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Get Notification Details

**Endpoint:** `GET /api/v1/notifications/{id}`

**Note:** Automatically marks notification as read

**Response (200):**
```json
{
  "id": 1,
  "title": "New Assignment Posted",
  "message": "A new assignment has been posted...",
  "notification_type": "assignment",
  "is_read": true,
  "read_at": "2024-01-15T11:00:00.000000Z",
  "action_url": "/assignments/1"
}
```

---

### 3. Mark as Read

**Endpoint:** `POST /api/v1/notifications/{id}/mark-read`

**Response (200):**
```json
{
  "message": "Notification marked as read",
  "data": {
    "id": 1,
    "is_read": true,
    "read_at": "2024-01-15T11:00:00.000000Z"
  }
}
```

---

### 4. Mark All as Read

**Endpoint:** `POST /api/v1/notifications/mark-all-read`

**Response (200):**
```json
{
  "message": "All notifications marked as read",
  "count": 15
}
```

---

### 5. Get Unread Count

**Endpoint:** `GET /api/v1/notifications/unread/count`

**Response (200):**
```json
{
  "unread_count": 5
}
```

---

### 6. Get Unread Notifications

**Endpoint:** `GET /api/v1/notifications/unread/list`

**Response (200):**
```json
[
  {
    "id": 1,
    "title": "New Assignment",
    "message": "Assignment posted",
    "is_read": false,
    "created_at": "2024-01-15T10:00:00.000000Z"
  }
]
```

---

### 7. Bulk Mark as Read

**Endpoint:** `POST /api/v1/notifications/bulk-mark-read`

**Request Body:**
```json
{
  "notification_ids": [1, 2, 3, 4, 5]
}
```

**Response (200):**
```json
{
  "message": "Notifications marked as read",
  "count": 5
}
```

---

### 8. Bulk Delete

**Endpoint:** `POST /api/v1/notifications/bulk-delete`

**Request Body:**
```json
{
  "notification_ids": [1, 2, 3]
}
```

**Response (200):**
```json
{
  "message": "Notifications deleted successfully",
  "count": 3
}
```

---

### 9. Delete All Read Notifications

**Endpoint:** `DELETE /api/v1/notifications/delete-all-read`

**Response (200):**
```json
{
  "message": "All read notifications deleted successfully",
  "count": 20
}
```

---

## Attendance Endpoints

### Attendance Session Endpoints

### 1. List Attendance Sessions

**Endpoint:** `GET /api/v1/attendance-sessions`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `status` (optional): open or closed

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "session_name": "Week 1 - Introduction",
      "status": "open",
      "deadline": "2024-01-15T14:00:00.000000Z",
      "course": {
        "course_name": "Computer Science 101"
      },
      "summary": {
        "total": 30,
        "present": 25,
        "absent": 3,
        "sick": 1,
        "permission": 1
      },
      "attendance_percentage": 83.33
    }
  ]
}
```

---

### 2. Create Attendance Session

**Endpoint:** `POST /api/v1/attendance-sessions`

**Authorization:** Instructor (for their courses) or Admin

**Request Body:**
```json
{
  "course_id": 1,
  "session_name": "Week 2 - Variables and Data Types",
  "status": "open",
  "deadline": "2024-01-22 14:00:00",
  "start_time": "2024-01-22 13:00:00",
  "end_time": "2024-01-22 15:00:00"
}
```

**Response (201):**
```json
{
  "id": 2,
  "course_id": 1,
  "session_name": "Week 2 - Variables and Data Types",
  "status": "open",
  "deadline": "2024-01-22T14:00:00.000000Z",
  "created_at": "2024-01-15T10:00:00.000000Z"
}
```

---

### 3. Open Session

**Endpoint:** `POST /api/v1/attendance-sessions/{id}/open`

**Authorization:** Instructor or Admin

**Response (200):**
```json
{
  "message": "Attendance session opened successfully",
  "data": {
    "id": 1,
    "status": "open"
  }
}
```

---

### 4. Close Session

**Endpoint:** `POST /api/v1/attendance-sessions/{id}/close`

**Authorization:** Instructor or Admin

**Response (200):**
```json
{
  "message": "Attendance session closed successfully",
  "data": {
    "id": 1,
    "status": "closed"
  }
}
```

---

### 5. Auto Mark Absent

**Endpoint:** `POST /api/v1/attendance-sessions/{id}/auto-mark-absent`

**Authorization:** Instructor or Admin

**Response (200):**
```json
{
  "message": "Auto-marked absent successfully",
  "marked_count": 5
}
```

---

### 6. Get Session Summary

**Endpoint:** `GET /api/v1/attendance-sessions/{id}/summary`

**Response (200):**
```json
{
  "session_id": 1,
  "session_name": "Week 1 - Introduction",
  "summary": {
    "total": 30,
    "present": 25,
    "absent": 3,
    "sick": 1,
    "permission": 1,
    "pending": 0
  },
  "attendance_percentage": 83.33
}
```

---

### 7. Get Course Sessions

**Endpoint:** `GET /api/v1/attendance-sessions/course/{courseId}/all`

**Response (200):**
```json
[
  {
    "id": 1,
    "session_name": "Week 1",
    "status": "closed",
    "deadline": "2024-01-15T14:00:00.000000Z",
    "summary": {
      "present": 25,
      "absent": 5
    },
    "attendance_percentage": 83.33
  }
]
```

---

### Attendance Record Endpoints

### 1. Student Check-In

**Endpoint:** `POST /api/v1/attendance-records/check-in/{sessionId}`

**Authorization:** Student (enrolled in course)

**Response (200):**
```json
{
  "message": "Check-in successful",
  "data": {
    "id": 1,
    "attendance_session_id": 1,
    "enrollment_id": 1,
    "status": "present",
    "check_in_time": "2024-01-15T13:15:00.000000Z"
  }
}
```

**Error Response (400):**
```json
{
  "message": "Attendance session is not open"
}
```

---

### 2. Request Sick Leave

**Endpoint:** `POST /api/v1/attendance-records/sick-leave/{sessionId}`

**Authorization:** Student

**Request Body:**
```json
{
  "notes": "I have a fever and cannot attend class today"
}
```

**Response (200):**
```json
{
  "message": "Sick leave request submitted successfully",
  "data": {
    "id": 2,
    "status": "sick",
    "notes": "I have a fever...",
    "attendance_session_id": 1
  }
}
```

---

### 3. Request Permission

**Endpoint:** `POST /api/v1/attendance-records/permission/{sessionId}`

**Authorization:** Student

**Request Body:**
```json
{
  "notes": "Family emergency, need to attend to urgent family matters"
}
```

**Response (200):**
```json
{
  "message": "Permission request submitted successfully",
  "data": {
    "id": 3,
    "status": "permission",
    "notes": "Family emergency..."
  }
}
```

---

### 4. Instructor Mark Attendance

**Endpoint:** `POST /api/v1/attendance-records/mark/{sessionId}/{enrollmentId}`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "status": "present",
  "notes": "Arrived on time"
}
```

**Response (200):**
```json
{
  "message": "Attendance marked successfully",
  "data": {
    "id": 1,
    "status": "present",
    "check_in_time": "2024-01-15T13:00:00.000000Z"
  }
}
```

---

### 5. Approve Attendance Request

**Endpoint:** `POST /api/v1/attendance-records/{recordId}/approve`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "notes": "Approved. Valid medical certificate provided."
}
```

**Response (200):**
```json
{
  "message": "Attendance record approved successfully",
  "data": {
    "id": 2,
    "status": "sick",
    "reviewed_by": 5,
    "reviewed_at": "2024-01-16T09:00:00.000000Z"
  }
}
```

---

### 6. Reject Attendance Request

**Endpoint:** `POST /api/v1/attendance-records/{recordId}/reject`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "notes": "Rejected. No valid documentation provided."
}
```

**Response (200):**
```json
{
  "message": "Attendance record rejected successfully",
  "data": {
    "id": 2,
    "status": "absent",
    "reviewed_by": 5,
    "reviewed_at": "2024-01-16T09:00:00.000000Z",
    "notes": "Rejected. No valid documentation provided."
  }
}
```

---

### 7. Bulk Mark Attendance

**Endpoint:** `POST /api/v1/attendance-records/bulk-mark/{sessionId}`

**Authorization:** Instructor or Admin

**Request Body:**
```json
{
  "records": [
    {
      "enrollment_id": 1,
      "status": "present",
      "notes": "On time"
    },
    {
      "enrollment_id": 2,
      "status": "absent"
    },
    {
      "enrollment_id": 3,
      "status": "present"
    }
  ]
}
```

**Response (200):**
```json
{
  "message": "Bulk attendance marked successfully",
  "count": 3,
  "data": [...]
}
```

---

### 8. Get Student Attendance History

**Endpoint:** `GET /api/v1/attendance-records/student/{studentId}/course/{courseId}/history`

**Authorization:** Student (self), Instructor, Admin

**Response (200):**
```json
[
  {
    "id": 1,
    "attendance_session": {
      "session_name": "Week 1",
      "deadline": "2024-01-15T14:00:00.000000Z"
    },
    "status": "present",
    "check_in_time": "2024-01-15T13:15:00.000000Z"
  },
  {
    "id": 2,
    "attendance_session": {
      "session_name": "Week 2"
    },
    "status": "sick",
    "notes": "Fever",
    "reviewed_by": 5
  }
]
```

---

### 9. Get Session Records

**Endpoint:** `GET /api/v1/attendance-records/session/{sessionId}/records`

**Authorization:** Instructor or Admin

**Response (200):**
```json
[
  {
    "id": 1,
    "enrollment": {
      "student": {
        "user": {
          "name": "John Doe"
        }
      }
    },
    "status": "present",
    "check_in_time": "2024-01-15T13:15:00.000000Z"
  }
]
```

---

### 10. Get Records Needing Review

**Endpoint:** `GET /api/v1/attendance-records/course/{courseId}/needs-review`

**Authorization:** Instructor or Admin

**Response (200):**
```json
[
  {
    "id": 2,
    "enrollment": {
      "student": {
        "user": {
          "name": "Jane Smith"
        }
      }
    },
    "attendance_session": {
      "session_name": "Week 2"
    },
    "status": "sick",
    "notes": "Medical certificate attached",
    "created_at": "2024-01-15T10:00:00.000000Z"
  }
]
```

---

### 11. Get Student Attendance Statistics

**Endpoint:** `GET /api/v1/attendance-records/student/{studentId}/course/{courseId}/stats`

**Authorization:** Student (self), Instructor, Admin

**Response (200):**
```json
{
  "total_sessions": 10,
  "present": 8,
  "absent": 1,
  "sick": 1,
  "permission": 0,
  "pending": 0,
  "attendance_percentage": 80.0,
  "excused": 1,
  "excused_percentage": 90.0
}
```

---

## Certificate Endpoints

### 1. List Certificates

**Endpoint:** `GET /api/v1/certificates`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `student_id` (optional): Filter by student
- `status` (optional): issued, revoked, expired

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "certificate_code": "CERT-2024-CS101-ABC12345",
      "enrollment": {
        "student": {
          "user": {
            "name": "John Doe"
          }
        }
      },
      "course": {
        "course_name": "Computer Science 101"
      },
      "final_grade": 85.5,
      "grade_letter": "B",
      "attendance_percentage": 90.0,
      "assignment_completion_rate": 95.0,
      "issue_date": "2024-06-15",
      "status": "issued"
    }
  ]
}
```

---

### 2. Get Certificate Details

**Endpoint:** `GET /api/v1/certificates/{id}`

**Note:** Automatically increments verification_count

**Response (200):**
```json
{
  "id": 1,
  "certificate_code": "CERT-2024-CS101-ABC12345",
  "enrollment": {
    "student": {
      "user": {
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  },
  "course": {
    "course_name": "Computer Science 101",
    "course_code": "CS101"
  },
  "final_grade": 85.5,
  "grade_letter": "B",
  "attendance_percentage": 90.0,
  "assignment_completion_rate": 95.0,
  "issue_date": "2024-06-15",
  "expiry_date": null,
  "status": "issued",
  "verification_count": 5,
  "metadata": {
    "student_name": "John Doe",
    "course_name": "Computer Science 101",
    "instructor_name": "Dr. Smith"
  }
}
```

---

### 3. Check Eligibility

**Endpoint:** `GET /api/v1/certificates/eligibility/{enrollmentId}`

**Authorization:** Student (self), Instructor, Admin

**Response (200):**
```json
{
  "enrollment_id": 1,
  "student_name": "John Doe",
  "course_name": "Computer Science 101",
  "eligible": true,
  "errors": [],
  "details": {
    "final_grade": 85.5,
    "attendance_percentage": 90.0,
    "assignment_completion_rate": 95.0,
    "enrollment_status": "completed"
  }
}
```

**Not Eligible Response:**
```json
{
  "eligible": false,
  "errors": [
    "Final grade must be at least 60",
    "Attendance percentage must be at least 75%"
  ],
  "details": {
    "final_grade": 55.0,
    "attendance_percentage": 70.0,
    "assignment_completion_rate": 80.0
  }
}
```

---

### 4. Generate Certificate

**Endpoint:** `POST /api/v1/certificates/generate/{enrollmentId}`

**Authorization:** Instructor (for their courses) or Admin

**Response (201):**
```json
{
  "message": "Certificate generated successfully",
  "data": {
    "id": 1,
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "enrollment_id": 1,
    "course_id": 1,
    "final_grade": 85.5,
    "grade_letter": "B",
    "issue_date": "2024-06-15",
    "status": "issued"
  }
}
```

**Error Response (400):**
```json
{
  "message": "Student is not eligible for certificate",
  "errors": [
    "Attendance percentage must be at least 75%"
  ],
  "eligibility": {
    "eligible": false,
    "attendance_percentage": 70.0
  }
}
```

---

### 5. Bulk Generate Certificates

**Endpoint:** `POST /api/v1/certificates/bulk-generate/{courseId}`

**Authorization:** Instructor or Admin

**Response (200):**
```json
{
  "message": "Bulk certificate generation completed",
  "summary": {
    "total_enrollments": 30,
    "success_count": 25,
    "failed_count": 3,
    "already_exists_count": 2
  },
  "results": {
    "success": [
      {
        "enrollment_id": 1,
        "student_name": "John Doe",
        "certificate_id": 1,
        "certificate_code": "CERT-2024-CS101-ABC12345"
      }
    ],
    "failed": [
      {
        "enrollment_id": 5,
        "student_name": "Jane Smith",
        "errors": ["Final grade must be at least 60"]
      }
    ],
    "already_exists": [
      {
        "enrollment_id": 10,
        "student_name": "Bob Johnson",
        "certificate_id": 15
      }
    ]
  }
}
```

---

### 6. Verify Certificate by Code (Public)

**Endpoint:** `GET /api/v1/certificates/verify/code/{certificateCode}`

**Note:** This is a PUBLIC endpoint - no authentication required

**Response (200):**
```json
{
  "valid": true,
  "certificate": {
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "student_name": "John Doe",
    "course_name": "Computer Science 101",
    "final_grade": 85.5,
    "grade_letter": "B",
    "issue_date": "2024-06-15",
    "expiry_date": null,
    "status": "issued",
    "verification_count": 6
  },
  "message": "Certificate is valid"
}
```

**Invalid Certificate Response:**
```json
{
  "valid": false,
  "certificate": {
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "status": "revoked"
  },
  "message": "Certificate is not valid or has been revoked/expired"
}
```

**Not Found Response (404):**
```json
{
  "valid": false,
  "message": "Certificate not found"
}
```

---

### 7. Verify Certificate by ID (Public)

**Endpoint:** `GET /api/v1/certificates/verify/{id}`

**Note:** This is a PUBLIC endpoint - no authentication required

**Response:** Same format as verify by code

---

### 8. Revoke Certificate

**Endpoint:** `POST /api/v1/certificates/{id}/revoke`

**Authorization:** Admin only

**Request Body:**
```json
{
  "reason": "Academic misconduct discovered"
}
```

**Response (200):**
```json
{
  "message": "Certificate revoked successfully",
  "data": {
    "id": 1,
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "status": "revoked",
    "revocation_reason": "Academic misconduct discovered",
    "revoked_at": "2024-07-01T10:00:00.000000Z"
  }
}
```

---

### 9. Get Student Certificates

**Endpoint:** `GET /api/v1/certificates/student/{studentId}`

**Authorization:** Student (self), Parent, Instructor, Admin

**Response (200):**
```json
[
  {
    "id": 1,
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "course": {
      "course_name": "Computer Science 101",
      "course_code": "CS101"
    },
    "final_grade": 85.5,
    "grade_letter": "B",
    "issue_date": "2024-06-15",
    "status": "issued"
  },
  {
    "id": 2,
    "certificate_code": "CERT-2024-MATH101-XYZ78901",
    "course": {
      "course_name": "Mathematics 101"
    },
    "final_grade": 90.0,
    "grade_letter": "A",
    "issue_date": "2024-06-20",
    "status": "issued"
  }
]
```

---

### 10. Get Course Certificates

**Endpoint:** `GET /api/v1/certificates/course/{courseId}`

**Authorization:** Instructor (for their courses) or Admin

**Response (200):**
```json
[
  {
    "id": 1,
    "certificate_code": "CERT-2024-CS101-ABC12345",
    "enrollment": {
      "student": {
        "user": {
          "name": "John Doe"
        }
      }
    },
    "final_grade": 85.5,
    "grade_letter": "B",
    "issue_date": "2024-06-15",
    "status": "issued"
  }
]
```

---

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Default Rate Limit:** 60 requests per minute per user
- **Authentication Endpoints:** 5 requests per minute
- **Public Endpoints (Certificate Verification):** 100 requests per minute

When rate limit is exceeded, the API returns:

```json
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

**Response Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60
```

---

## Common Use Cases & Examples

### Use Case 1: Student Enrollment Flow

**Scenario:** A student wants to enroll in a course and start learning.

**Step-by-step API calls:**

```bash
# 1. Login
POST /api/login
{
  "email": "student@example.com",
  "password": "password123"
}

# Response: Get access_token

# 2. Browse available courses
GET /api/v1/courses
Authorization: Bearer {token}

# 3. Enroll in a course
POST /api/v1/enrollments
Authorization: Bearer {token}
{
  "student_id": 1,
  "course_id": 1
}

# 4. Get course details
GET /api/v1/courses/1
Authorization: Bearer {token}

# 5. Check for announcements
GET /api/v1/announcements/course/1
Authorization: Bearer {token}

# 6. View assignments
GET /api/v1/assignments?course_id=1
Authorization: Bearer {token}
```

---

### Use Case 2: Instructor Creating and Grading Assignment

**Scenario:** Instructor creates an assignment, students submit, instructor grades.

```bash
# 1. Instructor creates assignment
POST /api/v1/assignments
Authorization: Bearer {instructor_token}
{
  "course_id": 1,
  "title": "Week 1 Assignment",
  "description": "Complete exercises 1-10",
  "due_date": "2024-12-31 23:59:59",
  "max_score": 100,
  "status": "published"
}

# 2. Student submits assignment
POST /api/v1/submissions
Authorization: Bearer {student_token}
{
  "assignment_id": 1,
  "enrollment_id": 1,
  "submission_text": "My answers here...",
  "file_path": "/uploads/submission.pdf"
}

# 3. Instructor views submissions
GET /api/v1/submissions?assignment_id=1
Authorization: Bearer {instructor_token}

# 4. Instructor grades submission
PUT /api/v1/submissions/1
Authorization: Bearer {instructor_token}
{
  "score": 85,
  "feedback": "Good work! Pay attention to question 5.",
  "status": "graded"
}

# 5. Student checks grade
GET /api/v1/grades/student?enrollment_id=1
Authorization: Bearer {student_token}
```

---

### Use Case 3: Attendance Tracking

**Scenario:** Instructor opens attendance, students check in, instructor reviews.

```bash
# 1. Instructor creates attendance session
POST /api/v1/attendance-sessions
Authorization: Bearer {instructor_token}
{
  "course_id": 1,
  "session_name": "Week 3 - Data Structures",
  "status": "open",
  "deadline": "2024-12-15 14:00:00"
}

# 2. Student checks in
POST /api/v1/attendance-records/check-in/1
Authorization: Bearer {student_token}

# Response: status = "present", check_in_time recorded

# 3. Student requests sick leave (alternative)
POST /api/v1/attendance-records/sick-leave/1
Authorization: Bearer {student_token}
{
  "notes": "I have a fever and cannot attend"
}

# 4. Instructor reviews sick leave requests
GET /api/v1/attendance-records/course/1/needs-review
Authorization: Bearer {instructor_token}

# 5. Instructor approves sick leave
POST /api/v1/attendance-records/2/approve
Authorization: Bearer {instructor_token}
{
  "notes": "Approved. Get well soon!"
}

# 6. After deadline, auto-mark absent
POST /api/v1/attendance-sessions/1/auto-mark-absent
Authorization: Bearer {instructor_token}

# 7. View attendance statistics
GET /api/v1/attendance-records/student/1/course/1/stats
Authorization: Bearer {student_token}
```

---

### Use Case 4: Certificate Generation and Verification

**Scenario:** Student completes course, instructor generates certificate, public verification.

```bash
# 1. Check if student is eligible
GET /api/v1/certificates/eligibility/1
Authorization: Bearer {instructor_token}

# Response: eligible = true/false with details

# 2. Generate certificate (if eligible)
POST /api/v1/certificates/generate/1
Authorization: Bearer {instructor_token}

# Response: certificate with unique code

# 3. Student views their certificates
GET /api/v1/certificates/student/1
Authorization: Bearer {student_token}

# 4. Public verification (NO AUTH REQUIRED)
GET /api/v1/certificates/verify/code/CERT-2024-CS101-ABC12345

# Response: certificate validity and details

# 5. Bulk generate for entire course
POST /api/v1/certificates/bulk-generate/1
Authorization: Bearer {instructor_token}

# Response: summary of success/failed generations
```

---

### Use Case 5: Real-time Notifications

**Scenario:** Student receives and manages notifications.

```bash
# 1. Get unread notification count (for badge)
GET /api/v1/notifications/unread/count
Authorization: Bearer {token}

# Response: { "unread_count": 5 }

# 2. Get unread notifications
GET /api/v1/notifications/unread/list
Authorization: Bearer {token}

# 3. Open notification (auto marks as read)
GET /api/v1/notifications/1
Authorization: Bearer {token}

# 4. Mark all as read
POST /api/v1/notifications/mark-all-read
Authorization: Bearer {token}

# 5. Delete read notifications
DELETE /api/v1/notifications/delete-all-read
Authorization: Bearer {token}

# 6. Bulk delete specific notifications
POST /api/v1/notifications/bulk-delete
Authorization: Bearer {token}
{
  "notification_ids": [1, 2, 3, 4, 5]
}
```

---

## Integration Guide

### Quick Start Tutorial

**Prerequisites:**
- API Base URL
- User credentials (email & password)
- HTTP client (Postman, curl, axios, etc.)

**Step 1: Authentication**

```javascript
// JavaScript/Axios example
const axios = require('axios');

const API_BASE = 'http://localhost:8000/api';

async function login(email, password) {
  try {
    const response = await axios.post(`${API_BASE}/login`, {
      email: email,
      password: password
    });
    
    const token = response.data.access_token;
    
    // Store token for subsequent requests
    localStorage.setItem('auth_token', token);
    
    return token;
  } catch (error) {
    console.error('Login failed:', error.response.data);
    throw error;
  }
}

// Usage
const token = await login('student@example.com', 'password123');
```

**Step 2: Making Authenticated Requests**

```javascript
// Set up axios instance with default headers
const apiClient = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add token to all requests
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors (token expired)
apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response.status === 401) {
      // Token expired, redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

**Step 3: Fetching Data**

```javascript
// Get courses
async function getCourses() {
  try {
    const response = await apiClient.get('/v1/courses');
    return response.data.data;
  } catch (error) {
    console.error('Error fetching courses:', error);
    throw error;
  }
}

// Get courses with filtering
async function getCoursesFiltered(instructorId) {
  try {
    const response = await apiClient.get('/v1/courses', {
      params: { instructor_id: instructorId }
    });
    return response.data.data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}
```

**Step 4: Creating Resources**

```javascript
// Enroll in course
async function enrollInCourse(studentId, courseId) {
  try {
    const response = await apiClient.post('/v1/enrollments', {
      student_id: studentId,
      course_id: courseId
    });
    
    console.log('Enrollment successful:', response.data);
    return response.data;
  } catch (error) {
    if (error.response.status === 400) {
      console.error('Already enrolled or validation error');
    } else if (error.response.status === 403) {
      console.error('Not authorized');
    }
    throw error;
  }
}
```

**Step 5: Handling Pagination**

```javascript
// Fetch paginated data
async function getNotifications(page = 1) {
  try {
    const response = await apiClient.get('/v1/notifications', {
      params: { page: page }
    });
    
    return {
      data: response.data.data,
      currentPage: response.data.current_page,
      lastPage: response.data.last_page,
      total: response.data.total
    };
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// Load all pages
async function getAllNotifications() {
  let allNotifications = [];
  let currentPage = 1;
  let lastPage = 1;
  
  do {
    const result = await getNotifications(currentPage);
    allNotifications = [...allNotifications, ...result.data];
    lastPage = result.lastPage;
    currentPage++;
  } while (currentPage <= lastPage);
  
  return allNotifications;
}
```

---

### PHP/Laravel Integration

```php
// Using Guzzle HTTP Client

use GuzzleHttp\Client;

class LmsApiClient
{
    private $client;
    private $token;
    
    public function __construct($baseUrl)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }
    
    public function login($email, $password)
    {
        $response = $this->client->post('/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        $this->token = $data['access_token'];
        
        return $this->token;
    }
    
    public function getCourses()
    {
        $response = $this->client->get('/api/v1/courses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function enrollStudent($studentId, $courseId)
    {
        $response = $this->client->post('/api/v1/enrollments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'json' => [
                'student_id' => $studentId,
                'course_id' => $courseId,
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }
}

// Usage
$api = new LmsApiClient('http://localhost:8000');
$api->login('admin@example.com', 'password');
$courses = $api->getCourses();
```

---

### Python Integration

```python
import requests

class LmsApiClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        
    def login(self, email, password):
        response = requests.post(
            f"{self.base_url}/api/login",
            json={"email": email, "password": password}
        )
        response.raise_for_status()
        
        data = response.json()
        self.token = data['access_token']
        return self.token
    
    def _get_headers(self):
        return {
            'Authorization': f'Bearer {self.token}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    
    def get_courses(self):
        response = requests.get(
            f"{self.base_url}/api/v1/courses",
            headers=self._get_headers()
        )
        response.raise_for_status()
        return response.json()
    
    def enroll_student(self, student_id, course_id):
        response = requests.post(
            f"{self.base_url}/api/v1/enrollments",
            headers=self._get_headers(),
            json={
                'student_id': student_id,
                'course_id': course_id
            }
        )
        response.raise_for_status()
        return response.json()

# Usage
api = LmsApiClient('http://localhost:8000')
api.login('student@example.com', 'password123')
courses = api.get_courses()
print(courses)
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. 401 Unauthorized Error

**Problem:** API returns 401 even with valid credentials.

**Possible Causes:**
- Token expired
- Token not included in header
- Token format incorrect

**Solutions:**

```bash
# Check if token is being sent
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/v1/courses

# Verify token format (must include "Bearer ")
Authorization: Bearer 1|abc123...
# NOT: Authorization: 1|abc123...

# Token might be expired - login again
POST /api/login
```

---

#### 2. 403 Forbidden Error

**Problem:** Authenticated but not authorized for action.

**Possible Causes:**
- User role doesn't have permission
- Trying to access another user's resources
- Policy restrictions

**Solutions:**

```javascript
// Check user role
GET /api/user

// Example: Students cannot delete courses
// Solution: Use correct role (instructor/admin)

// Example: Student trying to view another student's grades
// Solution: Only access own resources
```

---

#### 3. 422 Validation Error

**Problem:** Request validation failed.

**Example Error:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

**Solution:**

```javascript
// Check validation errors in response
if (error.response.status === 422) {
  const errors = error.response.data.errors;
  Object.keys(errors).forEach(field => {
    console.log(`${field}: ${errors[field].join(', ')}`);
  });
}
```

---

#### 4. 404 Not Found Error

**Problem:** Resource doesn't exist.

**Possible Causes:**
- Wrong endpoint URL
- Resource ID doesn't exist
- Resource was deleted

**Solutions:**

```bash
# Verify endpoint URL
GET /api/v1/courses/999  # ID might not exist

# Check if resource exists first
GET /api/v1/courses  # List all courses

# Verify spelling
GET /api/v1/course  # Wrong! Should be /courses
```

---

#### 5. 429 Too Many Requests

**Problem:** Rate limit exceeded.

**Response:**

```json
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

**Solution:**

```javascript
// Implement exponential backoff
async function makeRequestWithRetry(fn, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (error.response.status === 429) {
        const retryAfter = error.response.data.retry_after || 60;
        console.log(`Rate limited. Waiting ${retryAfter}s...`);
        await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
      } else {
        throw error;
      }
    }
  }
  throw new Error('Max retries exceeded');
}
```

---

#### 6. CORS Issues (Browser)

**Problem:** CORS policy blocking requests from browser.

**Error:**

```
Access to XMLHttpRequest at 'http://api.lms.com' from origin 'http://localhost:3000' 
has been blocked by CORS policy
```

**Solution:**

Backend needs to configure CORS headers (Laravel handles this):

```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

Frontend workaround (development only):

```javascript
// Use proxy in package.json (React)
"proxy": "http://localhost:8000"

// Or use CORS proxy (not for production)
const PROXY = 'https://cors-anywhere.herokuapp.com/';
axios.get(PROXY + API_URL);
```

---

#### 7. File Upload Issues

**Problem:** File upload fails or times out.

**Solutions:**

```javascript
// Use FormData for file uploads
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('assignment_id', 1);

axios.post('/api/upload/submission/1', formData, {
  headers: {
    'Content-Type': 'multipart/form-data',
    'Authorization': `Bearer ${token}`
  },
  onUploadProgress: (progressEvent) => {
    const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
    console.log(`Upload progress: ${progress}%`);
  }
});

// Check file size limits (Laravel default: 2MB)
// Increase in php.ini if needed:
// upload_max_filesize = 10M
// post_max_size = 10M
```

---

#### 8. Attendance Check-in Fails

**Problem:** Student cannot check in to attendance.

**Common Issues:**

```bash
# Session not open
{
  "message": "Attendance session is not open"
}
# Solution: Wait for instructor to open session

# After deadline
{
  "message": "Attendance session has expired"
}
# Solution: Contact instructor, or use sick leave request

# Already checked in
{
  "message": "You have already checked in"
}
# Solution: Check attendance status
GET /api/v1/attendance-records/student/1/course/1/history

# Not enrolled in course
{
  "message": "You are not enrolled in this course"
}
# Solution: Enroll first
POST /api/v1/enrollments
```

---

## Postman Collection

### Import Collection

A Postman collection is available for easy API testing:

**Collection includes:**
- All endpoints organized by module
- Pre-configured environment variables
- Sample requests with test data
- Automated tests for responses

**To import:**

1. Download collection: `LMS-SmartDev-API.postman_collection.json`
2. Open Postman
3. Click "Import" → Select file
4. Configure environment variables:
   - `base_url`: http://localhost:8000/api
   - `token`: (will be set automatically after login)

**Environment Variables:**

```json
{
  "base_url": "http://localhost:8000/api",
  "token": "",
  "student_id": "1",
  "course_id": "1",
  "enrollment_id": "1"
}
```

**Using the Collection:**

1. Run "Auth → Login" request first
2. Token will be automatically saved to environment
3. All subsequent requests will use the token
4. Explore endpoints by module

---

## API Versioning

### Current Version: v1

All endpoints are prefixed with `/api/v1/`

### Version Policy

- **Breaking changes** will increment major version (v2, v3)
- **New features** added to current version without breaking existing
- **Deprecation** warnings provided 6 months before removal
- **Backward compatibility** maintained within major version

### Version Headers

```bash
# Specify API version (optional, defaults to latest)
Accept: application/vnd.lms.v1+json

# Check current version
GET /api/version
Response: { "version": "1.0", "release_date": "2024-12-01" }
```

### Deprecation Notice

Deprecated endpoints will return warning header:

```
Deprecation: true
Sunset: Sat, 01 Jun 2025 00:00:00 GMT
Link: <https://docs.lms-smartdev.com/migration>; rel="deprecation"
```

---

## Future Features (Coming Soon)

### Webhooks (Planned)

Subscribe to events and receive real-time notifications:

**Events:**
- `enrollment.created`
- `submission.graded`
- `certificate.issued`
- `attendance.checked_in`

**Configuration:**

```bash
POST /api/v1/webhooks
{
  "url": "https://your-app.com/webhook",
  "events": ["enrollment.created", "submission.graded"],
  "secret": "your_webhook_secret"
}
```

**Payload Example:**

```json
{
  "event": "enrollment.created",
  "timestamp": "2024-12-15T10:00:00Z",
  "data": {
    "enrollment_id": 123,
    "student_id": 45,
    "course_id": 10
  },
  "signature": "sha256=abc123..."
}
```

---

### GraphQL API (Planned)

Alternative to REST for flexible queries:

```graphql
query {
  course(id: 1) {
    id
    courseName
    instructor {
      user {
        name
      }
    }
    enrollments {
      student {
        user {
          name
        }
      }
      status
    }
  }
}
```

---

### Real-time Updates (Planned)

WebSocket support for live updates:

```javascript
// Connect to WebSocket
const ws = new WebSocket('wss://api.lms-smartdev.com/ws');

// Subscribe to notifications
ws.send(JSON.stringify({
  action: 'subscribe',
  channel: 'notifications',
  token: 'Bearer abc123...'
}));

// Receive real-time updates
ws.onmessage = (event) => {
  const notification = JSON.parse(event.data);
  console.log('New notification:', notification);
};
```

---

## Changelog

### Version 1.0 (December 2024)

**New Features:**
- ✅ Complete authentication system with Sanctum
- ✅ User management (CRUD)
- ✅ Course management
- ✅ Enrollment system
- ✅ Assignment and submission tracking
- ✅ Grading system with grade components
- ✅ Announcement system (global and course-specific)
- ✅ Notification system with real-time alerts
- ✅ Attendance tracking with check-in
- ✅ Certificate generation and verification

**Improvements:**
- Authorization policies for all endpoints
- Comprehensive validation
- Proper error handling
- Pagination support
- Filtering and sorting capabilities

---

## Support

For questions or issues, please contact:

- **Email:** support@lms-smartdev.com
- **Documentation:** https://docs.lms-smartdev.com
- **GitHub:** https://github.com/lms-smartdev/api

---

## License

This API is proprietary software. All rights reserved.

© 2024 LMS SmartDev. All rights reserved.