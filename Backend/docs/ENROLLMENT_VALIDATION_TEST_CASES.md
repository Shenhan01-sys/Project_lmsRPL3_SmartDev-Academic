# 🧪 Enrollment Validation - Test Cases

## Overview

Dokumen ini berisi test cases untuk memverifikasi bahwa **EnrollmentService** bekerja dengan benar dalam memvalidasi enrollment student di berbagai controller.

---

## 🎯 Test Prerequisites

### Setup Test Data

```sql
-- Courses
INSERT INTO courses (id, course_code, course_name, instructor_id) VALUES
(1, 'MATH101', 'Mathematics 101', 1),
(2, 'PHY101', 'Physics 101', 1),
(3, 'ENG101', 'English 101', 2);

-- Students
INSERT INTO students (id, user_id, full_name, student_number) VALUES
(1, 10, 'Student A', 'STD001'),
(2, 11, 'Student B', 'STD002'),
(3, 12, 'Student C', 'STD003');

-- Enrollments
INSERT INTO enrollments (student_id, course_id) VALUES
(1, 1),  -- Student A enrolled in Math
(1, 3),  -- Student A enrolled in English
(2, 1),  -- Student B enrolled in Math
(3, 2);  -- Student C enrolled in Physics

-- Assignments
INSERT INTO assignments (id, course_id, title, description) VALUES
(1, 1, 'Math Quiz 1', 'First quiz'),
(2, 2, 'Physics Lab 1', 'First lab'),
(3, 3, 'English Essay', 'Write an essay');

-- Grade Components
INSERT INTO grade_components (id, course_id, name, weight) VALUES
(1, 1, 'Math Midterm', 0.3),
(2, 2, 'Physics Final', 0.4),
(3, 3, 'English Quiz', 0.2);
```

**Summary:**
- Student A (ID: 1) → Enrolled in: Math (1), English (3)
- Student B (ID: 2) → Enrolled in: Math (1)
- Student C (ID: 3) → Enrolled in: Physics (2)

---

## 📝 Test Cases

### 1. SubmissionController Tests

#### TC-SUB-001: Submit Assignment - Enrolled ✅

**Scenario:** Student A submits assignment untuk Math course (enrolled)

**Request:**
```http
POST /api/submissions
Authorization: Bearer {student_a_token}
Content-Type: application/json

{
    "assignment_id": 1,
    "file_path": "submissions/math_quiz.pdf"
}
```

**Expected Result:**
- Status: `201 Created`
- Response:
```json
{
    "id": 1,
    "student_id": 1,
    "assignment_id": 1,
    "file_path": "submissions/math_quiz.pdf",
    "grade": null,
    "feedback": null,
    "created_at": "2025-01-28T10:00:00.000000Z",
    "updated_at": "2025-01-28T10:00:00.000000Z"
}
```

---

#### TC-SUB-002: Submit Assignment - NOT Enrolled ❌

**Scenario:** Student A submits assignment untuk Physics course (NOT enrolled)

**Request:**
```http
POST /api/submissions
Authorization: Bearer {student_a_token}
Content-Type: application/json

{
    "assignment_id": 2,
    "file_path": "submissions/physics_lab.pdf"
}
```

**Expected Result:**
- Status: `403 Forbidden`
- Response:
```json
{
    "message": "You are not enrolled in the course for this assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

---

#### TC-SUB-003: Update Submission - Change to Non-Enrolled Course ❌

**Scenario:** Student A mencoba update submission untuk assignment dari course yang tidak diikuti

**Request:**
```http
PUT /api/submissions/1
Authorization: Bearer {student_a_token}
Content-Type: application/json

{
    "assignment_id": 2
}
```

**Expected Result:**
- Status: `403 Forbidden`
- Response:
```json
{
    "message": "Student is not enrolled in the course for the new assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

---

### 2. GradeController Tests

#### TC-GRADE-001: Input Grade - Enrolled ✅

**Scenario:** Instructor input nilai untuk Student A di Math course (enrolled)

**Request:**
```http
POST /api/grades
Authorization: Bearer {instructor_token}
Content-Type: application/json

{
    "student_id": 1,
    "grade_component_id": 1,
    "score": 85,
    "max_score": 100,
    "notes": "Good work"
}
```

**Expected Result:**
- Status: `201 Created`
- Response:
```json
{
    "message": "Nilai berhasil di-input.",
    "data": {
        "id": 1,
        "student_id": 1,
        "grade_component_id": 1,
        "score": 85,
        "max_score": 100,
        "notes": "Good work",
        "graded_at": "2025-01-28T10:00:00.000000Z"
    }
}
```

---

#### TC-GRADE-002: Input Grade - NOT Enrolled ❌

**Scenario:** Instructor input nilai untuk Student A di Physics course (NOT enrolled)

**Request:**
```http
POST /api/grades
Authorization: Bearer {instructor_token}
Content-Type: application/json

{
    "student_id": 1,
    "grade_component_id": 2,
    "score": 90,
    "max_score": 100
}
```

**Expected Result:**
- Status: `400 Bad Request`
- Response:
```json
{
    "message": "Student is not enrolled in this course.",
    "error": "ENROLLMENT_REQUIRED"
}
```

---

#### TC-GRADE-003: Bulk Input Grades - All Enrolled ✅

**Scenario:** Instructor bulk input nilai untuk Math course (all enrolled)

**Request:**
```http
POST /api/grades/bulk
Authorization: Bearer {instructor_token}
Content-Type: application/json

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

**Expected Result:**
- Status: `201 Created`
- Response:
```json
{
    "message": "Nilai massal berhasil di-input.",
    "data": [...],
    "count": 2
}
```

---

#### TC-GRADE-004: Bulk Input Grades - Mixed Enrollment ❌

**Scenario:** Instructor bulk input nilai, beberapa student tidak enrolled

**Request:**
```http
POST /api/grades/bulk
Authorization: Bearer {instructor_token}
Content-Type: application/json

{
    "grades": [
        {
            "student_id": 1,
            "grade_component_id": 1,
            "score": 85,
            "max_score": 100
        },
        {
            "student_id": 3,
            "grade_component_id": 1,
            "score": 88,
            "max_score": 100
        }
    ]
}
```

**Expected Result:**
- Status: `400 Bad Request`
- Response:
```json
{
    "message": "Some students are not enrolled in the required courses.",
    "error": "ENROLLMENT_REQUIRED",
    "invalid_entries": [
        {
            "index": 1,
            "student_id": 3,
            "grade_component_id": 1,
            "reason": "Student not enrolled in this course"
        }
    ]
}
```

---

### 3. AssignmentController Tests

#### TC-ASSIGN-001: View Assignment List - Student ✅

**Scenario:** Student A melihat list assignments (hanya dari enrolled courses)

**Request:**
```http
GET /api/assignments
Authorization: Bearer {student_a_token}
```

**Expected Result:**
- Status: `200 OK`
- Response hanya berisi assignments dari course_id: 1 dan 3
```json
[
    {
        "id": 1,
        "course_id": 1,
        "title": "Math Quiz 1",
        "description": "First quiz"
    },
    {
        "id": 3,
        "course_id": 3,
        "title": "English Essay",
        "description": "Write an essay"
    }
]
```

**Note:** Assignment ID 2 (Physics) TIDAK muncul karena Student A tidak enrolled

---

#### TC-ASSIGN-002: View Assignment Detail - Enrolled ✅

**Scenario:** Student A melihat detail assignment dari Math course (enrolled)

**Request:**
```http
GET /api/assignments/1
Authorization: Bearer {student_a_token}
```

**Expected Result:**
- Status: `200 OK`
- Response:
```json
{
    "id": 1,
    "course_id": 1,
    "title": "Math Quiz 1",
    "description": "First quiz",
    "course": {
        "id": 1,
        "course_code": "MATH101",
        "course_name": "Mathematics 101"
    }
}
```

---

#### TC-ASSIGN-003: View Assignment Detail - NOT Enrolled ❌

**Scenario:** Student A mencoba melihat detail assignment dari Physics course (NOT enrolled)

**Request:**
```http
GET /api/assignments/2
Authorization: Bearer {student_a_token}
```

**Expected Result:**
- Status: `403 Forbidden`
- Response:
```json
{
    "message": "You are not enrolled in the course for this assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

---

### 4. Edge Cases

#### TC-EDGE-001: Student Without Student Profile ❌

**Scenario:** User dengan role student tapi tidak punya student profile

**Request:**
```http
POST /api/submissions
Authorization: Bearer {user_without_profile_token}
Content-Type: application/json

{
    "assignment_id": 1,
    "file_path": "test.pdf"
}
```

**Expected Result:**
- Status: `403 Forbidden`
- Response:
```json
{
    "message": "You must be a student to submit assignments."
}
```

---

#### TC-EDGE-002: Admin Bypass Validation ✅

**Scenario:** Admin dapat view semua assignments tanpa enrollment check

**Request:**
```http
GET /api/assignments
Authorization: Bearer {admin_token}
```

**Expected Result:**
- Status: `200 OK`
- Response berisi SEMUA assignments (tidak difilter)

---

#### TC-EDGE-003: Instructor View Own Course Only ✅

**Scenario:** Instructor hanya bisa view assignments dari course yang dia ajar

**Request:**
```http
GET /api/assignments
Authorization: Bearer {instructor_1_token}
```

**Expected Result:**
- Status: `200 OK`
- Response hanya berisi assignments dari course yang instructor_id = 1

---

## 🔄 Test Execution Steps

### Manual Testing

1. **Setup Environment**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Create Test Users**
   - Admin user
   - Instructor users
   - Student users with profiles

3. **Create Test Data**
   - Run SQL setup dari Prerequisites section

4. **Get Auth Tokens**
   ```bash
   POST /api/login
   {
       "email": "student_a@example.com",
       "password": "password"
   }
   ```

5. **Run Test Cases**
   - Execute requests dari test cases di atas
   - Verify response status dan body

### Automated Testing (Future)

```php
// tests/Feature/EnrollmentValidationTest.php

public function test_student_can_submit_assignment_if_enrolled()
{
    // TC-SUB-001
}

public function test_student_cannot_submit_assignment_if_not_enrolled()
{
    // TC-SUB-002
}

// ... more tests
```

---

## ✅ Success Criteria

- [ ] All positive test cases (✅) return expected success responses
- [ ] All negative test cases (❌) return proper error responses with `ENROLLMENT_REQUIRED`
- [ ] No student can submit/access content from non-enrolled courses
- [ ] No instructor can input grades for students not in their courses
- [ ] Admin has full access without restrictions
- [ ] Performance: Enrollment checks complete in < 100ms

---

## 📊 Test Coverage Matrix

| Controller | Method | Enrolled | NOT Enrolled | Bulk | Edge Cases |
|------------|--------|----------|--------------|------|------------|
| **SubmissionController** | store() | ✅ TC-SUB-001 | ✅ TC-SUB-002 | N/A | ✅ TC-EDGE-001 |
| **SubmissionController** | update() | ✅ | ✅ TC-SUB-003 | N/A | - |
| **GradeController** | store() | ✅ TC-GRADE-001 | ✅ TC-GRADE-002 | N/A | - |
| **GradeController** | bulkStore() | ✅ TC-GRADE-003 | ✅ TC-GRADE-004 | ✅ | - |
| **AssignmentController** | index() | ✅ TC-ASSIGN-001 | N/A | N/A | ✅ TC-EDGE-002 |
| **AssignmentController** | show() | ✅ TC-ASSIGN-002 | ✅ TC-ASSIGN-003 | N/A | ✅ TC-EDGE-003 |

---

## 🐛 Common Issues & Troubleshooting

### Issue 1: "Student profile not found"
**Cause:** User tidak punya record di tabel `students`
**Solution:** Pastikan setiap user dengan role `student` punya entry di tabel `students`

### Issue 2: "Course not found in enrollment check"
**Cause:** Assignment/GradeComponent tidak punya relasi ke course
**Solution:** Verify foreign key relationships di database

### Issue 3: "Enrollment check always returns false"
**Cause:** Mismatch antara `user_id` dan `student_id`
**Solution:** Pastikan menggunakan `student_id` dari tabel `students`, bukan `user_id`

---

## 📝 Notes

- **Test Order:** Run positive tests first, then negative tests
- **Cleanup:** Reset database after each test suite
- **Tokens:** Generate fresh tokens untuk setiap test session
- **Timestamps:** Ignore timestamp fields saat compare responses

---

**Last Updated:** 2025-01-28
**Version:** 1.0.0