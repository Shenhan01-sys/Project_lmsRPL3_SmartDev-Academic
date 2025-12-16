# Business Logic Documentation - LMS SmartDev

> **Comprehensive Guide to System Architecture, Business Rules, and Logic Flows**
> 
> Version: 1.0
> Last Updated: December 2024

---

## Table of Contents

1. [System Overview](#system-overview)
2. [User Roles & Permissions](#user-roles--permissions)
3. [Authentication & Authorization](#authentication--authorization)
4. [Core Business Flows](#core-business-flows)
5. [Module Deep Dive](#module-deep-dive)
   - [Course Management](#course-management)
   - [Enrollment System](#enrollment-system)
   - [Assignment & Submission](#assignment--submission)
   - [Grading System](#grading-system)
   - [Announcement System](#announcement-system)
   - [Notification System](#notification-system)
   - [Attendance System](#attendance-system)
   - [Certificate System](#certificate-system)
6. [Data Relationships](#data-relationships)
7. [Validation Rules](#validation-rules)
8. [Business Rules & Constraints](#business-rules--constraints)
9. [State Management](#state-management)
10. [Best Practices](#best-practices)

---

## System Overview

### Architecture Pattern

LMS SmartDev menggunakan **MVC (Model-View-Controller)** pattern dengan Laravel framework:

```
┌─────────────────────────────────────────────────┐
│                   Client Layer                   │
│          (Web/Mobile Application)               │
└─────────────────┬───────────────────────────────┘
                  │ HTTP/HTTPS
                  │ JSON REST API
┌─────────────────▼───────────────────────────────┐
│              API Layer (Routes)                  │
│           - Authentication Middleware            │
│           - Rate Limiting                        │
│           - Request Validation                   │
└─────────────────┬───────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────┐
│           Controller Layer                       │
│    - Business Logic Orchestration               │
│    - Request/Response Handling                  │
│    - Authorization Checks (Policy)              │
└─────────────────┬───────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────┐
│            Model Layer                           │
│    - Data Validation                            │
│    - Business Logic (Methods)                   │
│    - Relationships                              │
│    - Scopes & Accessors                         │
└─────────────────┬───────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────┐
│           Database Layer                         │
│    - MySQL/PostgreSQL                           │
│    - Migrations & Seeders                       │
└─────────────────────────────────────────────────┘
```

### Key Design Principles

1. **Separation of Concerns**: Each layer has a specific responsibility
2. **Policy-Based Authorization**: Uses Laravel Policies for access control
3. **RESTful API Design**: Follows REST conventions
4. **Token-Based Authentication**: Uses Laravel Sanctum
5. **Eloquent ORM**: For database interactions
6. **Validation at Multiple Levels**: Request validation + Model validation

---

## User Roles & Permissions

### Role Hierarchy

```
┌─────────────┐
│    Admin    │  ← Highest privileges
└──────┬──────┘
       │
┌──────▼──────┐
│ Instructor  │  ← Course management
└──────┬──────┘
       │
┌──────▼──────┐
│   Student   │  ← Learning activities
└──────┬──────┘
       │
┌──────▼──────┐
│   Parent    │  ← Monitoring children
└─────────────┘
```

### Detailed Permissions Matrix

| Feature | Admin | Instructor | Student | Parent |
|---------|-------|------------|---------|--------|
| **Users** |
| Create User | ✅ | ❌ | ❌ | ❌ |
| Edit Any User | ✅ | ❌ | ❌ | ❌ |
| View Users | ✅ | ✅ (Limited) | ❌ | ❌ |
| Delete User | ✅ | ❌ | ❌ | ❌ |
| **Courses** |
| Create Course | ✅ | ✅ | ❌ | ❌ |
| Edit Any Course | ✅ | ❌ | ❌ | ❌ |
| Edit Own Course | ✅ | ✅ | ❌ | ❌ |
| View Courses | ✅ | ✅ | ✅ | ✅ |
| Delete Course | ✅ | ✅ (Own) | ❌ | ❌ |
| **Enrollments** |
| Enroll Student | ✅ | ✅ | ✅ (Self) | ❌ |
| Approve Enrollment | ✅ | ✅ | ❌ | ❌ |
| Drop Student | ✅ | ✅ | ✅ (Self) | ❌ |
| **Assignments** |
| Create Assignment | ✅ | ✅ | ❌ | ❌ |
| Edit Assignment | ✅ | ✅ (Own) | ❌ | ❌ |
| Submit Assignment | ❌ | ❌ | ✅ | ❌ |
| Grade Submission | ✅ | ✅ | ❌ | ❌ |
| **Grades** |
| Input Grades | ✅ | ✅ | ❌ | ❌ |
| View All Grades | ✅ | ✅ (Course) | ❌ | ❌ |
| View Own Grades | ❌ | ❌ | ✅ | ✅ (Children) |
| **Announcements** |
| Create Global | ✅ | ❌ | ❌ | ❌ |
| Create Course | ✅ | ✅ | ❌ | ❌ |
| Edit Announcement | ✅ | ✅ (Own) | ❌ | ❌ |
| View Announcements | ✅ | ✅ | ✅ | ✅ |
| **Notifications** |
| View Own | ✅ | ✅ | ✅ | ✅ |
| Manage Own | ✅ | ✅ | ✅ | ✅ |
| **Attendance** |
| Create Session | ✅ | ✅ | ❌ | ❌ |
| Check In | ❌ | ❌ | ✅ | ❌ |
| Mark Attendance | ✅ | ✅ | ❌ | ❌ |
| Review Leave | ✅ | ✅ | ❌ | ❌ |
| View Records | ✅ | ✅ | ✅ (Own) | ✅ (Children) |
| **Certificates** |
| Generate | ✅ | ✅ | ❌ | ❌ |
| Verify | ✅ | ✅ | ✅ | ✅ (Public) |
| Revoke | ✅ | ❌ | ❌ | ❌ |
| Download | ✅ | ✅ (Course) | ✅ (Own) | ✅ (Children) |

---

## Authentication & Authorization

### Authentication Flow

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ 1. POST /api/login
       │    { email, password }
       ▼
┌─────────────────────┐
│  AuthController     │
│  - Validate creds   │
│  - Check user role  │
└──────┬──────────────┘
       │ 2. Valid credentials?
       ▼
┌─────────────────────┐
│  Generate Token     │
│  (Laravel Sanctum)  │
└──────┬──────────────┘
       │ 3. Return token
       ▼
┌─────────────────────┐
│   Client stores     │
│   token locally     │
└─────────────────────┘
       │
       │ 4. Subsequent requests
       │    Authorization: Bearer {token}
       ▼
┌─────────────────────┐
│  Middleware checks  │
│  token validity     │
└─────────────────────┘
```

### Authorization Process

Laravel Policies are used for fine-grained authorization:

```php
// Example: Before executing controller method
public function update(Request $request, Announcement $announcement)
{
    // 1. Check authorization using Policy
    $this->authorize('update', $announcement);
    
    // Policy will check:
    // - Is user the creator?
    // - Or is user an admin?
    // - Or is user instructor of the course?
    
    // 2. If authorized, proceed with logic
    $announcement->update($validated);
    
    return response()->json($announcement);
}
```

**Policy Logic Example:**

```php
// AnnouncementPolicy.php
public function update(User $user, Announcement $announcement): bool
{
    // Admin can update all
    if ($user->role === 'admin') {
        return true;
    }
    
    // Creator can update their own
    if ($announcement->created_by === $user->id) {
        return true;
    }
    
    // Instructor can update for their course
    if ($user->role === 'instructor' && $announcement->course_id) {
        $course = $announcement->course;
        return $course->instructor_id === $user->instructor->id;
    }
    
    return false;
}
```

---

## Core Business Flows

### 1. Student Registration Flow

```
┌─────────────────────────────────────────────────────┐
│          Student Registration Journey               │
└─────────────────────────────────────────────────────┘

Step 1: Registration
┌──────────────┐
│ Student fills│  → POST /api/register-calon-siswa
│ registration │     { name, email, password, ... }
│     form     │
└──────┬───────┘
       │
       ▼
┌────────────────────┐
│  System creates    │
│  - User (pending)  │
│  - Student record  │
└──────┬─────────────┘
       │
       ▼
Step 2: Document Upload (Optional)
┌────────────────────┐
│ Student uploads    │  → POST /api/upload-documents
│ required documents │     { document_type, file }
└──────┬─────────────┘
       │
       ▼
Step 3: Admin Review
┌────────────────────┐
│ Admin reviews      │  → POST /api/registrations/{id}/approve
│ registration       │     OR /reject
└──────┬─────────────┘
       │
       ▼
┌────────────────────┐
│ System updates     │
│ status to 'active' │
│ or 'rejected'      │
└──────┬─────────────┘
       │
       ▼
┌────────────────────┐
│ Notification sent  │
│ to student         │
└────────────────────┘
```

### 2. Course Enrollment Flow

```
┌─────────────────────────────────────────────────────┐
│            Course Enrollment Process                │
└─────────────────────────────────────────────────────┘

Step 1: Student browses courses
┌──────────────┐
│ GET /courses │  → View available courses
└──────┬───────┘
       │
       ▼
Step 2: Enrollment request
┌──────────────────┐
│ POST /enrollments│  → { student_id, course_id }
└──────┬───────────┘
       │
       ▼ System validates:
┌────────────────────────────────┐
│ 1. Student not already enrolled│
│ 2. Course is available         │
│ 3. Prerequisites met (if any)  │
│ 4. Course capacity not exceeded│
└──────┬─────────────────────────┘
       │
       ▼ Validation passed?
┌────────────────────┐
│ Create enrollment  │
│ status: 'active'   │
└──────┬─────────────┘
       │
       ▼
┌────────────────────┐
│ Trigger events:    │
│ - Send notification│
│ - Update stats     │
└────────────────────┘
```

### 3. Assignment Submission Flow

```
┌─────────────────────────────────────────────────────┐
│         Assignment Submission Process               │
└─────────────────────────────────────────────────────┘

Instructor creates assignment
┌──────────────────────┐
│ POST /assignments    │
│ - title, description │
│ - due_date           │
│ - max_score          │
└──────┬───────────────┘
       │ status: 'published'
       ▼
┌──────────────────────┐
│ System creates       │
│ notifications for    │
│ enrolled students    │
└──────┬───────────────┘
       │
       ▼
Student submits
┌──────────────────────┐
│ POST /submissions    │
│ - assignment_id      │
│ - submission_text    │
│ - file_path          │
└──────┬───────────────┘
       │
       ▼ System checks:
┌────────────────────────────────┐
│ 1. Student enrolled in course  │
│ 2. Assignment is published     │
│ 3. Before due date (or allow   │
│    late submission)            │
└──────┬─────────────────────────┘
       │ Valid?
       ▼
┌──────────────────────┐
│ Create submission    │
│ status: 'submitted'  │
│ submitted_at: now()  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Notify instructor    │
│ "New submission"     │
└──────┬───────────────┘
       │
       ▼
Instructor grades
┌──────────────────────┐
│ PUT /submissions/{id}│
│ - score              │
│ - feedback           │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Update submission    │
│ status: 'graded'     │
│ graded_at: now()     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Notify student       │
│ "Assignment graded"  │
└────────────────────── ┘
```

### 4. Attendance Check-In Flow

```
┌─────────────────────────────────────────────────────┐
│            Attendance Check-In Process              │
└─────────────────────────────────────────────────────┘

Instructor creates session
┌───────────────────────────┐
│ POST /attendance-sessions │
│ - session_name            │
│ - deadline                │
│ status: 'open'            │
└──────┬────────────────────┘
       │
       ▼
Student checks in
┌───────────────────────────┐
│ POST /attendance-records/ │
│      check-in/{sessionId} │
└──────┬────────────────────┘
       │
       ▼ System validates:
┌────────────────────────────────┐
│ 1. Session is 'open'           │
│ 2. Before deadline             │
│ 3. Student enrolled in course  │
│ 4. Not already checked in      │
└──────┬─────────────────────────┘
       │ Valid?
       ▼
┌───────────────────────────┐
│ Create/Update record      │
│ status: 'present'         │
│ check_in_time: now()      │
└──────┬────────────────────┘
       │
       ▼
Alternative: Student requests sick leave
┌───────────────────────────┐
│ POST /attendance-records/ │
│      sick-leave/{id}      │
│ { notes: "..." }          │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Create record             │
│ status: 'sick'            │
│ needs review              │
└──────┬────────────────────┘
       │
       ▼
Instructor reviews
┌───────────────────────────┐
│ POST /attendance-records/ │
│      {id}/approve         │
│ OR {id}/reject            │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Update record             │
│ reviewed_by: instructor   │
│ reviewed_at: now()        │
└──────┬────────────────────┘
       │
       ▼
After deadline
┌───────────────────────────┐
│ POST /attendance-sessions/│
│      {id}/auto-mark-absent│
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Mark all students without │
│ record as 'absent'        │
└───────────────────────────┘
```

### 5. Certificate Generation Flow

```
┌─────────────────────────────────────────────────────┐
│          Certificate Generation Process             │
└─────────────────────────────────────────────────────┘

Step 1: Check eligibility
┌───────────────────────────┐
│ GET /certificates/        │
│     eligibility/{id}      │
└──────┬────────────────────┘
       │
       ▼ System calculates:
┌────────────────────────────────┐
│ 1. Final grade ≥ 60            │
│ 2. Attendance ≥ 75%            │
│ 3. Assignment completion ≥ 80% │
│ 4. Enrollment status: completed│
└──────┬─────────────────────────┘
       │ Eligible?
       ▼ YES
Step 2: Generate certificate
┌───────────────────────────┐
│ POST /certificates/       │
│      generate/{id}        │
└──────┬────────────────────┘
       │
       ▼
┌────────────────────────────────┐
│ System generates:              │
│ - Unique certificate code      │
│ - Calculate grade letter       │
│ - Set issue_date               │
│ - Create metadata              │
└──────┬─────────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Save certificate          │
│ status: 'issued'          │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Notify student            │
│ "Certificate issued"      │
└───────────────────────────┘

Public verification
┌───────────────────────────┐
│ GET /certificates/verify/ │
│     code/{code}           │
│ (NO AUTH REQUIRED)        │
└──────┬────────────────────┘
       │
       ▼
┌────────────────────────────────┐
│ Return certificate info:       │
│ - Student name                 │
│ - Course name                  │
│ - Final grade                  │
│ - Issue date                   │
│ - Valid status                 │
│ Increment verification_count   │
└────────────────────────────────┘
```

---

## Module Deep Dive

## Course Management

### Data Model

```
Course
├── id (PK)
├── course_code (unique)
├── course_name
├── description
├── instructor_id (FK → instructors)
├── created_at
└── updated_at

Relationships:
- belongsTo: Instructor
- hasMany: Enrollments
- hasMany: CourseModules
- hasMany: Assignments
- hasMany: Announcements
- hasMany: AttendanceSessions
```

### Business Rules

1. **Course Code Uniqueness**
   - Each course must have a unique course code
   - Format validation can be customized (e.g., CS101, MATH201)

2. **Instructor Assignment**
   - A course must have exactly one instructor
   - Instructor can have multiple courses
   - Only instructor or admin can modify the course

3. **Course Visibility**
   - All users can view available courses
   - Full details only visible to enrolled students or instructor

4. **Course Deletion**
   - Only admin or course instructor can delete
   - Consider soft delete if enrollments exist
   - Cascade considerations for related data

### Key Methods

```php
// CourseController

public function store(Request $request)
{
    // 1. Validate input
    $validated = $request->validate([
        'course_code' => 'required|unique:courses',
        'course_name' => 'required|max:255',
        'instructor_id' => 'required|exists:instructors,id',
    ]);
    
    // 2. Check authorization
    $this->authorize('create', Course::class);
    
    // 3. Create course
    $course = Course::create($validated);
    
    // 4. Return response
    return response()->json($course, 201);
}

public function update(Request $request, Course $course)
{
    // 1. Check authorization (Policy checks ownership)
    $this->authorize('update', $course);
    
    // 2. Validate and update
    $validated = $request->validate([...]);
    $course->update($validated);
    
    return response()->json($course);
}
```

---

## Enrollment System

### Data Model

```
Enrollment
├── id (PK)
├── student_id (FK → students)
├── course_id (FK → courses)
├── status (active|completed|dropped)
├── enrollment_date
├── completion_date
├── final_grade
└── timestamps

Relationships:
- belongsTo: Student
- belongsTo: Course
- hasMany: Submissions
- hasMany: Grades
- hasMany: AttendanceRecords
```

### Enrollment States

```
┌─────────┐
│ pending │ → Initial state (if approval required)
└────┬────┘
     │ approve
     ▼
┌─────────┐
│ active  │ → Student actively learning
└────┬────┘
     │ complete course OR drop
     ▼
┌───────────┐     ┌─────────┐
│ completed │     │ dropped │
└───────────┘     └─────────┘
```

### Business Rules

1. **Single Enrollment Rule**
   - A student cannot enroll in the same course twice simultaneously
   - Check for existing active enrollment before creating new one

2. **Prerequisite Checking** (Optional)
   - System can validate if student met prerequisites
   - Implemented via course relationships

3. **Course Capacity** (Optional)
   - Check if course has reached maximum capacity
   - Implement waitlist if needed

4. **Status Transitions**
   - `active` → `completed`: When course finished + grade entered
   - `active` → `dropped`: Student/admin drops the course
   - Cannot re-enroll if status is `active`

### Validation Logic

```php
// EnrollmentController

public function store(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
    ]);
    
    // Business rule: Check existing enrollment
    $existingEnrollment = Enrollment::where('student_id', $validated['student_id'])
        ->where('course_id', $validated['course_id'])
        ->whereIn('status', ['active', 'pending'])
        ->first();
    
    if ($existingEnrollment) {
        return response()->json([
            'message' => 'Student is already enrolled in this course'
        ], 400);
    }
    
    // Create enrollment
    $enrollment = Enrollment::create([
        'student_id' => $validated['student_id'],
        'course_id' => $validated['course_id'],
        'status' => 'active',
        'enrollment_date' => now(),
    ]);
    
    // Trigger notification
    // NotificationService::enrollmentCreated($enrollment);
    
    return response()->json($enrollment, 201);
}
```

---

## Assignment & Submission

### Data Models

```
Assignment
├── id (PK)
├── course_id (FK)
├── title
├── description
├── due_date
├── max_score
├── status (draft|published|closed)
└── timestamps

Submission
├── id (PK)
├── assignment_id (FK)
├── enrollment_id (FK)
├── submission_text
├── file_path
├── score
├── feedback
├── status (pending|submitted|graded|late)
├── submitted_at
├── graded_at
└── timestamps
```

### Assignment Lifecycle

```
┌───────┐
│ draft │ → Instructor preparing
└───┬───┘
    │ publish
    ▼
┌───────────┐
│ published │ → Students can submit
└───┬───────┘
    │ close OR past due_date
    ▼
┌────────┐
│ closed │ → No more submissions (unless late allowed)
└────────┘
```

### Submission Lifecycle

```
┌─────────┐
│ pending │ → Student hasn't submitted yet
└────┬────┘
     │ submit before due_date
     ▼
┌───────────┐
│ submitted │ → Waiting for grading
└────┬──────┘
     │ instructor grades
     ▼
┌────────┐
│ graded │ → Final state
└────────┘

Alternative path:
┌─────────┐
│ pending │ → submit after due_date
└────┬────┘
     │
     ▼
┌──────┐
│ late │ → May have penalty
└───┬──┘
    │ instructor grades
    ▼
┌────────┐
│ graded │
└────────┘
```

### Business Rules

1. **Submission Deadline**
   - Students can submit before `due_date`
   - Late submission handling:
     - Option A: Block late submissions
     - Option B: Allow with late flag
     - Option C: Apply score penalty

2. **Single Submission Rule**
   - One submission per student per assignment
   - Update existing submission if resubmit allowed

3. **Grading Authorization**
   - Only course instructor or admin can grade
   - Score must be ≤ `max_score`

4. **File Upload**
   - Validate file type and size
   - Store securely with unique filename
   - Associate with submission

### Key Logic

```php
// SubmissionController

public function store(Request $request)
{
    $validated = $request->validate([
        'assignment_id' => 'required|exists:assignments,id',
        'enrollment_id' => 'required|exists:enrollments,id',
        'submission_text' => 'nullable|string',
        'file_path' => 'nullable|string',
    ]);
    
    $assignment = Assignment::findOrFail($validated['assignment_id']);
    
    // Check if assignment is published
    if ($assignment->status !== 'published') {
        return response()->json([
            'message' => 'Assignment is not available for submission'
        ], 400);
    }
    
    // Check for existing submission
    $existingSubmission = Submission::where('assignment_id', $validated['assignment_id'])
        ->where('enrollment_id', $validated['enrollment_id'])
        ->first();
    
    if ($existingSubmission) {
        return response()->json([
            'message' => 'You have already submitted this assignment'
        ], 400);
    }
    
    // Determine status based on due date
    $status = now()->gt($assignment->due_date) ? 'late' : 'submitted';
    
    $submission = Submission::create([
        ...$validated,
        'status' => $status,
        'submitted_at' => now(),
    ]);
    
    // Notify instructor
    // NotificationService::newSubmission($submission);
    
    return response()->json($submission, 201);
}
```

---

## Grading System

### Data Models

```
GradeComponent
├── id (PK)
├── course_id (FK)
├── name (e.g., "Midterm Exam", "Final Project")
├── weight (percentage, total should = 100)
├── description
└── timestamps

Grade
├── id (PK)
├── enrollment_id (FK)
├── grade_component_id (FK)
├── score (0-100)
├── grade_letter (A, B, C, D, F)
├── notes
└── timestamps
```

### Grade Calculation

**Component-Based Grading:**

```
Final Grade = Σ (Component Score × Component Weight)

Example:
- Midterm (30%): 85 points
- Final (40%): 90 points
- Assignments (20%): 88 points
- Participation (10%): 95 points

Final Grade = (85 × 0.30) + (90 × 0.40) + (88 × 0.20) + (95 × 0.10)
            = 25.5 + 36 + 17.6 + 9.5
            = 88.6

Grade Letter: B (if 80-89 = B)
```

### Grade Letter Conversion

```php
public static function calculateGradeLetter(float $score): string
{
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    return 'F';
}
```

### Business Rules

1. **Component Weight Validation**
   - Total weight of all components for a course should = 100%
   - Validate on component creation/update

2. **Grade Entry Authorization**
   - Only course instructor or admin can enter grades
   - Cannot modify grades for completed enrollments (optional)

3. **Final Grade Calculation**
   - Automatically calculated from component grades
   - Stored in `enrollments.final_grade`
   - Recalculated when any component grade changes

4. **Grade Visibility**
   - Students can only view their own grades
   - Instructors can view all grades for their courses
   - Parents can view their children's grades

### Implementation

```php
// GradeController

public function store(Request $request)
{
    $validated = $request->validate([
        'enrollment_id' => 'required|exists:enrollments,id',
        'grade_component_id' => 'required|exists:grade_components,id',
        'score' => 'required|numeric|min:0|max:100',
        'notes' => 'nullable|string',
    ]);
    
    $enrollment = Enrollment::findOrFail($validated['enrollment_id']);
    
    // Authorization check (Policy)
    $this->authorize('create', [Grade::class, $enrollment]);
    
    // Create or update grade
    $grade = Grade::updateOrCreate(
        [
            'enrollment_id' => $validated['enrollment_id'],
            'grade_component_id' => $validated['grade_component_id'],
        ],
        [
            'score' => $validated['score'],
            'grade_letter' => Grade::calculateGradeLetter($validated['score']),
            'notes' => $validated['notes'],
        ]
    );
    
    // Recalculate final grade
    $this->recalculateFinalGrade($enrollment);
    
    return response()->json($grade, 201);
}

protected function recalculateFinalGrade(Enrollment $enrollment)
{
    $course = $enrollment->course;
    $components = $course->gradeComponents;
    
    $finalGrade = 0;
    foreach ($components as $component) {
        $grade = Grade::where('enrollment_id', $enrollment->id)
            ->where('grade_component_id', $component->id)
            ->first();
        
        if ($grade) {
            $finalGrade += ($grade->score * $component->weight / 100);
        }
    }
    
    $enrollment->update([
        'final_grade' => round($finalGrade, 2),
    ]);
}
```

---

## Announcement System

### Data Model

```
Announcement
├── id (PK)
├── created_by (FK → users)
├── course_id (FK → courses, nullable for global)
├── title
├── content
├── announcement_type (global|course)
├── priority (normal|high|urgent)
├── status (draft|published|archived)
├── published_at
├── expires_at
├── view_count
├── pinned (boolean)
└── timestamps
```

### Announcement Types

**1. Global Announcements:**
- Visible to all users in the system
- Only admin can create
- Examples: System maintenance, holiday notice

**2. Course Announcements:**
- Visible only to students enrolled in the course + instructor
- Instructor or admin can create
- Examples: Assignment deadline, class cancellation

### Priority Levels

```
┌─────────┐
│ urgent  │  → Red badge, immediate attention
├─────────┤
│  high   │  → Orange badge, important
├─────────┤
│ normal  │  → Blue badge, regular info
└─────────┘
```

### Announcement Lifecycle

```
┌───────┐
│ draft │ → Private, only visible to creator
└───┬───┘
    │ publish
    ▼
┌───────────┐
│ published │ → Visible to target audience
└───┬───────┘
    │ archive OR expire
    ▼
┌──────────┐
│ archived │ → Hidden from main view, kept for records
└──────────┘
```

### Business Rules

1. **Creation Authorization**
   - Global: Admin only
   - Course: Instructor (for their course) or Admin

2. **Target Audience**
   - Global: All authenticated users
   - Course: Enrolled students + course instructor

3. **Publishing**
   - Can schedule `published_at` for future
   - Auto-hide after `expires_at` if set

4. **View Tracking**
   - Increment `view_count` when opened
   - Track for statistics, not per-user

### Announcement vs Notification

**Key Difference:**

| Aspect | Announcement | Notification |
|--------|--------------|--------------|
| **Target** | Broadcast (many users) | Personal (1 user) |
| **Storage** | 1 record | N records (1 per user) |
| **Created by** | Manual (admin/instructor) | Auto (system events) |
| **Mark as read** | No (view_count only) | Yes (is_read flag) |
| **Example** | "UTS next week" | "You got a new grade" |

### Integration Flow

When announcement is published:

```php
public function publish(Announcement $announcement)
{
    $this->authorize('publish', $announcement);
    
    // 1. Publish announcement
    $announcement->publish();
    
    // 2. Create notifications for target users
    if ($announcement->announcement_type === 'global') {
        $users = User::all();
    } else {
        $users = $announcement->course->enrollments()
            ->with('student.user')
            ->get()
            ->pluck('student.user');
    }
    
    foreach ($users as $user) {
        Notification::create([
            'user_id' => $user->id,
            'notification_type' => 'announcement',
            'title' => 'New Announcement: ' . $announcement->title,
            'message' => 'A new announcement has been posted',
            'action_url' => '/announcements/' . $announcement->id,
            'related_entity_type' => 'App\Models\Announcement',
            'related_entity_id' => $announcement->id,
        ]);
    }
    
    return response()->json($announcement);
}
```

---

## Notification System

### Data Model

```
Notification
├── id (PK)
├── user_id (FK → users)
├── notification_type (assignment|grade|announcement|enrollment|etc)
├── title
├── message
├── action_url
├── related_entity_type (polymorphic)
├── related_entity_id (polymorphic)
├── is_read (boolean)
├── read_at
├── priority (normal|high|urgent)
├── expires_at
└── timestamps
```

### Notification Types

```
┌─────────────────────┐
│   assignment        │ → New assignment posted
├─────────────────────┤
│   grade             │ → Grade published
├─────────────────────┤
│   announcement      │ → New announcement
├─────────────────────┤
│   enrollment        │ → Enrollment status changed
├─────────────────────┤
│   attendance        │ → Attendance session opened
├─────────────────────┤
│   submission        │ → Submission graded (for student)
│                     │   OR New submission (for instructor)
├─────────────────────┤
│   certificate       │ → Certificate issued
└─────────────────────┘
```

### Notification Lifecycle

```
┌─────────┐
│ unread  │ → is_read = false, read_at = null
└────┬────┘
     │ user opens notification
     ▼
┌─────────┐
│  read   │ → is_read = true, read_at = now()
└────┬────┘
     │ user deletes OR system expires
     ▼
┌─────────┐
│ deleted │ → Soft delete or hard delete
└─────────┘
```

### Business Rules

1. **Personal Notifications**
   - Each notification belongs to exactly one user
   - Users can only see their own notifications

2. **Auto Mark as Read**
   - When notification is viewed (show endpoint)
   - Manually via mark-as-read endpoint

3. **Bulk Operations**
   - Mark all as read
   - Delete multiple
   - Delete all read

4. **Expiration**
   - Notifications can have `expires_at`
   - Expired notifications filtered from active list

### Trigger Events

**System automatically creates notifications when:**

```php
// Example: When assignment is published
Event::listen(AssignmentPublished::class, function ($event) {
    $assignment = $event->assignment;
    $enrollments = $assignment->course->enrollments()->where('status', 'active')->get();
    
    foreach ($enrollments as $enrollment) {
        Notification::create([
            'user_id' => $enrollment->student->user_id,
            'notification_type' => 'assignment',
            'title' => 'New Assignment Posted',
            'message' => "A new assignment '{$assignment->title}' has been posted",
            'action_url' => '/assignments/' . $assignment->id,
            'related_entity_type' => 'App\Models\Assignment',
            'related_entity_id' => $assignment->id,
            'priority' => 'normal',
        ]);
    }
});
```

### Badge Counter Logic

```php
// Get unread count for badge display
public function getUnreadCount()
{
    $user = Auth::user();
    
    $count = Notification::where('user_id', $user->id)
        ->unread()  // scope: where('is_read', false)
        ->active()  // scope: not expired
        ->count();
    
    return response()->json(['unread_count' => $count]);
}
```

---

## Attendance System

### Data Models

```
AttendanceSession
├── id (PK)
├── course_id (FK)
├── session_name (e.g., "Week 1 - Introduction")
├── status (open|closed)
├── deadline
├── start_time
├── end_time
└── timestamps

AttendanceRecord
├── id (PK)
├── enrollment_id (FK)
├── attendance_session_id (FK)
├── status (present|absent|sick|permission|pending)
├── check_in_time
├── notes
├── supporting_doc_path (future: for sick/permission)
├── reviewed_by (FK → users)
├── reviewed_at
└── timestamps
```

### Attendance Session Lifecycle

```
┌──────┐
│ open │ → Students can check in
└──┬───┘
   │ instructor closes OR deadline passed
   ▼
┌────────┐
│ closed │ → No more check-ins
└────────┘
```

### Attendance Record States

```
┌─────────┐
│ pending │ → No record yet (student hasn't checked in)
└────┬────┘
     │
     ├─→ check-in → ┌─────────┐
     │              │ present │
     │              └─────────┘
     │
     ├─→ sick leave → ┌──────┐
     │                │ sick │ → needs review
     │                └───┬──┘
     │                    │ instructor approves/rejects
     │                    ▼
     │                ┌─────────┐   ┌────────┐
     │                │ sick    │   │ absent │
     │                │(approved)│   │(rejected)
     │                └─────────┘   └────────┘
     │
     ├─→ permission → ┌────────────┐
     │                │ permission │ → needs review
     │                └────────────┘
     │
     └─→ after deadline → ┌────────┐
                          │ absent │ → auto-marked
                          └────────┘
```

### Check-In Flow

**Scenario 1: Normal Check-In**

```
Student → POST /check-in/{sessionId}
          ↓
     Validate:
     ✓ Session is open
     ✓ Before deadline
     ✓ Student enrolled
     ✓ Not already checked in
          ↓
     Create/Update record:
     - status: 'present'
     - check_in_time: now()
          ↓
     Response: Success
```

**Scenario 2: Sick Leave Request**

```
Student → POST /sick-leave/{sessionId}
          Body: { notes: "I have fever" }
          ↓
     Create record:
     - status: 'sick'
     - notes: "I have fever"
     - reviewed_by: null (pending)
          ↓
     Response: Request submitted
          ↓
Instructor → GET /needs-review/{courseId}
             See pending sick leaves
          ↓
Instructor → POST /approve/{recordId}
             OR POST /reject/{recordId}
          ↓
     Update record:
     - reviewed_by: instructor_id
     - reviewed_at: now()
     - status: kept as 'sick' (if approved)
               OR changed to 'absent' (if rejected)
```

### Business Rules

1. **Session Deadline**
   - Students can only check in before `deadline`
   - After deadline: instructor can still manually mark
   - Auto-mark absent: for students without any record

2. **Single Record Rule**
   - One record per student per session
   - Update existing if status changes

3. **Review Process**
   - Sick/permission requests need instructor review
   - Instructor can approve (excused absence) or reject

4. **Attendance Calculation**
   - Present count / Total sessions = Attendance %
   - Excused absences (approved sick/permission) can be:
     - Counted as present (policy decision)
     - Or separate category

### Statistics Calculation

```php
public function getStudentAttendanceStats($studentId, $courseId)
{
    $enrollment = Enrollment::where('student_id', $studentId)
        ->where('course_id', $courseId)
        ->firstOrFail();
    
    $records = AttendanceRecord::where('enrollment_id', $enrollment->id)->get();
    
    $stats = [
        'total_sessions' => $records->count(),
        'present' => $records->where('status', 'present')->count(),
        'absent' => $records->where('status', 'absent')->count(),
        'sick' => $records->where('status', 'sick')->count(),
        'permission' => $records->where('status', 'permission')->count(),
    ];
    
    // Calculate percentage
    $stats['attendance_percentage'] = $stats['total_sessions'] > 0
        ? round(($stats['present'] / $stats['total_sessions']) * 100, 2)
        : 0;
    
    // Include excused absences
    $stats['excused'] = $stats['sick'] + $stats['permission'];
    $stats['excused_percentage'] = $stats['total_sessions'] > 0
        ? round((($stats['present'] + $stats['excused']) / $stats['total_sessions']) * 100, 2)
        : 0;
    
    return response()->json($stats);
}
```

### Auto-Mark Absent Logic

```php
public function autoMarkAbsent(AttendanceSession $session)
{
    // Only if session has expired
    if (!$session->hasExpired()) {
        return response()->json(['message' => 'Session not expired'], 400);
    }
    
    // Get all enrollments
    $enrollments = $session->course->enrollments()
        ->where('status', 'active')
        ->get();
    
    $markedCount = 0;
    
    foreach ($enrollments as $enrollment) {
        // Check if record exists
        $record = AttendanceRecord::where('enrollment_id', $enrollment->id)
            ->where('attendance_session_id', $session->id)
            ->first();
        
        // Mark absent if no record OR status is pending
        if (!$record) {
            AttendanceRecord::create([
                'enrollment_id' => $enrollment->id,
                'attendance_session_id' => $session->id,
                'status' => 'absent',
            ]);
            $markedCount++;
        } elseif ($record->status === 'pending') {
            $record->update(['status' => 'absent']);
            $markedCount++;
        }
    }
    
    return response()->json(['marked_count' => $markedCount]);
}
```

---

## Certificate System

### Data Model

```
Certificate
├── id (PK)
├── enrollment_id (FK)
├── course_id (FK)
├── certificate_code (unique)
├── certificate_file_path
├── final_grade
├── attendance_percentage
├── assignment_completion_rate
├── grade_letter
├── issue_date
├── expiry_date
├── generated_by (FK → users)
├── status (issued|revoked|expired)
├── revocation_reason
├── revoked_at
├── verification_count
├── metadata (JSON)
└── timestamps
```

### Certificate Code Format

```
CERT-{YEAR}-{COURSE_CODE}-{RANDOM}

Examples:
- CERT-2024-CS101-ABC12345
- CERT-2024-MATH201-XYZ67890
```

### Eligibility Criteria

A student is eligible for certificate if:

```
✓ Enrollment status = 'completed'
✓ Final grade ≥ 60
✓ Attendance percentage ≥ 75%
✓ Assignment completion rate ≥ 80%
```

### Eligibility Check Logic

```php
public static function checkEligibility(Enrollment $enrollment): array
{
    $errors = [];
    
    // 1. Check enrollment status
    if ($enrollment->status !== 'completed') {
        $errors[] = 'Enrollment must be completed';
    }
    
    // 2. Check final grade
    if (!$enrollment->final_grade || $enrollment->final_grade < 60) {
        $errors[] = 'Final grade must be at least 60';
    }
    
    // 3. Calculate attendance percentage
    $totalSessions = $enrollment->course->attendanceSessions()->count();
    $presentCount = $enrollment->attendanceRecords()
        ->where('status', 'present')
        ->count();
    
    $attendancePercentage = $totalSessions > 0
        ? round(($presentCount / $totalSessions) * 100, 2)
        : 100; // No sessions = 100%
    
    if ($attendancePercentage < 75) {
        $errors[] = 'Attendance percentage must be at least 75%';
    }
    
    // 4. Calculate assignment completion rate
    $totalAssignments = $enrollment->course->assignments()
        ->where('status', 'published')
        ->count();
    $submittedCount = $enrollment->submissions()->count();
    
    $completionRate = $totalAssignments > 0
        ? round(($submittedCount / $totalAssignments) * 100, 2)
        : 100; // No assignments = 100%
    
    if ($completionRate < 80) {
        $errors[] = 'Assignment completion rate must be at least 80%';
    }
    
    return [
        'eligible' => empty($errors),
        'errors' => $errors,
        'attendance_percentage' => $attendancePercentage,
        'assignment_completion_rate' => $completionRate,
    ];
}
```

### Certificate Generation Flow

```
1. Check eligibility
   ↓
2. Generate unique code
   ↓
3. Calculate grade letter
   ↓
4. Create certificate record
   - Store metrics (grade, attendance, etc.)
   - Set status: 'issued'
   - Set issue_date: now()
   ↓
5. Generate PDF (optional - future)
   ↓
6. Notify student
```

### Bulk Generation

```php
public function bulkGenerate($courseId)
{
    $course = Course::findOrFail($courseId);
    $enrollments = Enrollment::where('course_id', $courseId)
        ->where('status', 'completed')
        ->get();
    
    $results = [
        'success' => [],
        'failed' => [],
        'already_exists' => [],
    ];
    
    foreach ($enrollments as $enrollment) {
        // Check if already exists
        if (Certificate::where('enrollment_id', $enrollment->id)->exists()) {
            $results['already_exists'][] = [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student->user->name,
            ];
            continue;
        }
        
        // Check eligibility
        $eligibility = Certificate::checkEligibility($enrollment);
        
        if (!$eligibility['eligible']) {
            $results['failed'][] = [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student->user->name,
                'errors' => $eligibility['errors'],
            ];
            continue;
        }
        
        // Generate certificate
        $certificate = Certificate::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $courseId,
            'certificate_code' => Certificate::generateCertificateCode(
                $course->course_code
            ),
            'final_grade' => $enrollment->final_grade,
            'attendance_percentage' => $eligibility['attendance_percentage'],
            'assignment_completion_rate' => $eligibility['assignment_completion_rate'],
            'grade_letter' => Certificate::calculateGradeLetter($enrollment->final_grade),
            'issue_date' => now(),
            'generated_by' => Auth::id(),
            'status' => 'issued',
        ]);
        
        $results['success'][] = [
            'enrollment_id' => $enrollment->id,
            'student_name' => $enrollment->student->user->name,
            'certificate_id' => $certificate->id,
            'certificate_code' => $certificate->certificate_code,
        ];
    }
    
    return response()->json([
        'summary' => [
            'total' => $enrollments->count(),
            'success_count' => count($results['success']),
            'failed_count' => count($results['failed']),
            'already_exists_count' => count($results['already_exists']),
        ],
        'results' => $results,
    ]);
}
```

### Verification (Public Endpoint)

Certificate verification is **PUBLIC** - no authentication required.

```php
public function verify($certificateCode)
{
    $certificate = Certificate::where('certificate_code', $certificateCode)
        ->with(['enrollment.student.user', 'course'])
        ->first();
    
    if (!$certificate) {
        return response()->json([
            'valid' => false,
            'message' => 'Certificate not found'
        ], 404);
    }
    
    // Increment verification count
    $certificate->incrementVerificationCount();
    
    // Check validity
    $isValid = $certificate->isValid(); // status=issued && not expired
    
    return response()->json([
        'valid' => $isValid,
        'certificate' => [
            'certificate_code' => $certificate->certificate_code,
            'student_name' => $certificate->enrollment->student->user->name,
            'course_name' => $certificate->course->course_name,
            'final_grade' => $certificate->final_grade,
            'grade_letter' => $certificate->grade_letter,
            'issue_date' => $certificate->issue_date,
            'status' => $certificate->status,
            'verification_count' => $certificate->verification_count,
        ],
        'message' => $isValid 
            ? 'Certificate is valid' 
            : 'Certificate is not valid or has been revoked/expired'
    ]);
}
```

### Revocation

```php
public function revoke(Request $request, Certificate $certificate)
{
    $this->authorize('revoke', $certificate); // Admin only
    
    $validated = $request->validate([
        'reason' => 'required|string|max:1000',
    ]);
    
    $certificate->revoke($validated['reason'], Auth::id());
    
    // revoke() method updates:
    // - status = 'revoked'
    // - revocation_reason = $reason
    // - revoked_at = now()
    // - metadata['revoked_by'] = $userId
    
    return response()->json([
        'message' => 'Certificate revoked successfully',
        'data' => $certificate
    ]);
}
```

---

## Data Relationships

### ERD Overview

```
┌──────────┐       ┌───────────┐       ┌──────────┐
│  Users   │──────▶│Instructors│──────▶│ Courses  │
└────┬─────┘       └───────────┘       └────┬─────┘
     │                                       │
     │                                       │
     ▼                                       ▼
┌──────────┐                         ┌─────────────┐
│ Students │◀────────────────────────│ Enrollments │
└────┬─────┘                         └──────┬──────┘
     │                                      │
     │                                      ├──▶ Submissions
     │                                      ├──▶ Grades
     │                                      ├──▶ AttendanceRecords
     │                                      └──▶ Certificates
     │
     └──▶ Parents
```

### Key Relationships

**1. User → Student/Instructor/Parent**

```php
// User.php
public function student()
{
    return $this->hasOne(Student::class);
}

public function instructor()
{
    return $this->hasOne(Instructor::class);
}

public function parentProfile()
{
    return $this->hasOne(ParentModel::class);
}
```

**2. Course → Instructor**

```php
// Course.php
public function instructor()
{
    return $this->belongsTo(Instructor::class);
}

// Instructor.php
public function courses()
{
    return $this->hasMany(Course::class);
}
```

**3. Enrollment (Bridge)**

```php
// Enrollment.php
public function student()
{
    return $this->belongsTo(Student::class);
}

public function course()
{
    return $this->belongsTo(Course::class);
}

public function submissions()
{
    return $this->hasMany(Submission::class);
}

public function grades()
{
    return $this->hasMany(Grade::class);
}

public function attendanceRecords()
{
    return $this->hasMany(AttendanceRecord::class);
}

public function certificate()
{
    return $this->hasOne(Certificate::class);
}
```

**4. Parent → Students**

```php
// ParentModel.php (Parents.php)
public function students()
{
    return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id');
}

// Student.php
public function parents()
{
    return $this->belongsToMany(ParentModel::class, 'parent_student', 'student_id', 'parent_id');
}
```

### Eager Loading Best Practices

**Instead of N+1 queries:**

```php
// BAD: N+1 problem
$enrollments = Enrollment::all();
foreach ($enrollments as $enrollment) {
    echo $enrollment->student->user->name; // Extra query per enrollment
}
```

**Use eager loading:**

```php
// GOOD: Single query with joins
$enrollments = Enrollment::with([
    'student.user',
    'course.instructor.user'
])->get();

foreach ($enrollments as $enrollment) {
    echo $enrollment->student->user->name; // No extra queries
}
```

---

## Validation Rules

### Request Validation

Laravel uses Form Request Validation in controllers:

```php
$validated = $request->validate([
    'field_name' => 'rule1|rule2|rule3',
]);
```

### Common Validation Rules

**1. Course Creation**

```php
[
    'course_code' => 'required|string|unique:courses,course_code|max:50',
    'course_name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'instructor_id' => 'required|exists:instructors,id',
]
```

**2. Assignment Creation**

```php
[
    'course_id' => 'required|exists:courses,id',
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'due_date' => 'required|date|after:now',
    'max_score' => 'required|numeric|min:0|max:100',
    'status' => 'required|in:draft,published,closed',
]
```

**3. Attendance Check-In**

```php
// No body validation needed for check-in
// Session ID from URL parameter
// Validation done in business logic:
// - Session status = 'open'
// - Before deadline
// - Student enrolled
// - Not already checked in
```

**4. Certificate Generation**

```php
// No body validation for single generation
// Enrollment ID from URL parameter
// Validation in business logic:
// - Check eligibility (grade, attendance, completion)
// - No existing certificate
```

### Custom Validation Rules

```php
// Example: Custom rule to check if user is instructor of course
Validator::extend('instructor_owns_course', function ($attribute, $value, $parameters, $validator) {
    $userId = Auth::id();
    $courseId = $value;
    
    $instructor = Instructor::where('user_id', $userId)->first();
    if (!$instructor) {
        return false;
    }
    
    return Course::where('id', $courseId)
        ->where('instructor_id', $instructor->id)
        ->exists();
});

// Usage in validation
$validated = $request->validate([
    'course_id' => 'required|exists:courses,id|instructor_owns_course',
]);
```

---

## Business Rules & Constraints

### Database Constraints

**1. Unique Constraints**

```sql
-- Course code must be unique
ALTER TABLE courses ADD UNIQUE(course_code);

-- Certificate code must be unique
ALTER TABLE certificates ADD UNIQUE(certificate_code);

-- One enrollment per student per course
ALTER TABLE enrollments ADD UNIQUE(student_id, course_id);
```

**2. Foreign Key Constraints**

```sql
-- Enrollment must reference valid student and course
ALTER TABLE enrollments 
  ADD FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  ADD FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;
```

### Application-Level Rules

**1. Enrollment Rules**

- Cannot enroll in same course twice (if status = active/completed)
- Cannot drop after course completion
- Final grade can only be set when status = completed

**2. Assignment Rules**

- Cannot submit after due date (unless late submission allowed)
- Cannot modify submission after grading
- Score must not exceed max_score

**3. Attendance Rules**

- Cannot check in after session deadline
- Cannot change present to absent (only instructor can)
- Auto-mark absent only after deadline

**4. Certificate Rules**

- Cannot generate for incomplete enrollment
- Cannot modify issued certificate (immutable)
- Only admin can revoke

**5. Grade Rules**

- Grade component weights must sum to 100%
- Score must be between 0-100
- Cannot modify grades after enrollment completed (optional policy)
- Only instructor or admin can enter/modify grades

**6. Notification Rules**

- Personal notifications (1 user = 1 notification)
- Auto-generated by system events
- Cannot create notifications for other users manually
- Expired notifications hidden from active list

**7. Announcement Rules**

- Global announcements: Admin only
- Course announcements: Instructor (own course) or Admin
- Cannot delete published announcement with high view count (policy decision)
- Expired announcements auto-archived

---

## State Management

### Enrollment States

**State Diagram:**

```
                    ┌─────────────┐
          ┌────────▶│   pending   │
          │         └──────┬──────┘
          │                │
          │                │ approve
          │                ▼
    reject│         ┌─────────────┐
          │         │   active    │◀────────┐
          │         └──────┬──────┘         │
          │                │                 │
          │                │                 │ reactivate
          │                ▼                 │
          │         ┌─────────────┐         │
          └────────▶│   dropped   │─────────┘
                    └─────────────┘
                           
                    ┌─────────────┐
                    │  completed  │◀─── active (course finished)
                    └─────────────┘
```

**Valid Transitions:**

| From | To | Trigger | Authorization |
|------|----|---------|--------------:|
| pending | active | Admin approves | Admin |
| pending | dropped | Admin rejects | Admin |
| active | completed | Course finished + grade entered | Instructor/Admin |
| active | dropped | Student/admin drops | Student/Admin |
| dropped | active | Re-enrollment (policy) | Admin |

**State Validation:**

```php
public function updateStatus(Enrollment $enrollment, string $newStatus)
{
    $validTransitions = [
        'pending' => ['active', 'dropped'],
        'active' => ['completed', 'dropped'],
        'dropped' => ['active'], // Re-enrollment
        'completed' => [], // Final state - no transitions
    ];
    
    $currentStatus = $enrollment->status;
    
    if (!in_array($newStatus, $validTransitions[$currentStatus])) {
        throw new InvalidStateTransitionException(
            "Cannot transition from {$currentStatus} to {$newStatus}"
        );
    }
    
    $enrollment->update(['status' => $newStatus]);
}
```

---

### Assignment States

**State Diagram:**

```
┌───────┐
│ draft │ → Instructor preparing
└───┬───┘
    │
    │ publish
    ▼
┌───────────┐
│ published │ → Students can view and submit
└───┬───────┘
    │
    │ close OR auto-close (past due_date)
    ▼
┌────────┐
│ closed │ → No more submissions (final)
└────────┘
```

**Valid Transitions:**

```php
// AssignmentPolicy or Controller validation
$validTransitions = [
    'draft' => ['published'],
    'published' => ['closed'],
    'closed' => [], // Final state
];
```

---

### Submission States

**State Diagram:**

```
                    ┌─────────┐
          ┌────────▶│ pending │ (Not submitted yet)
          │         └────┬────┘
          │              │
          │              │ submit (before due_date)
          │              ▼
          │         ┌───────────┐
          │         │ submitted │
          │         └─────┬─────┘
          │               │
          │               │ instructor grades
    reset │               ▼
  (policy)│         ┌────────┐
          │         │ graded │ (Final)
          │         └────────┘
          │         
          │         ┌──────┐
          └─────────│ late │ (submit after due_date)
                    └───┬──┘
                        │
                        │ instructor grades
                        ▼
                    ┌────────┐
                    │ graded │
                    └────────┘
```

---

### Attendance Record States

**State Diagram:**

```
                    ┌─────────┐
          ┌────────▶│ pending │ (No record yet)
          │         └────┬────┘
          │              │
          │              ├──→ student check-in
          │              │    ┌─────────┐
          │              │───▶│ present │
          │              │    └─────────┘
          │              │
          │              ├──→ student request sick
          │              │    ┌──────┐
          │              │───▶│ sick │ (needs review)
          │              │    └───┬──┘
          │              │        │
          │              │        ├──→ instructor approve
          │              │        │    ┌──────────────┐
          │              │        │───▶│ sick (final) │
          │              │        │    └──────────────┘
          │              │        │
          │              │        └──→ instructor reject
          │              │             ┌────────┐
          │              │            ▶│ absent │
          │              │             └────────┘
          │              │
          │              ├──→ student request permission
          │              │    ┌────────────┐
          │              │───▶│ permission │ (needs review)
          │              │    └─────┬──────┘
          │              │          │
          │              │          ├──→ approve
          │              │          │    ┌─────────────────────┐
          │              │          │───▶│ permission (final)  │
          │              │          │    └─────────────────────┘
          │              │          │
          │              │          └──→ reject
          │              │               ┌────────┐
          │              │              ▶│ absent │
          │              │               └────────┘
          │              │
          │              └──→ after deadline (auto)
          │                   ┌────────┐
          │                  ▶│ absent │
          │                   └────────┘
          │
    reset │ (instructor manual update)
          │
          └─────────────────────────────────┘
```

**State Rules:**

```php
// Valid state transitions for attendance
$validTransitions = [
    'pending' => ['present', 'absent', 'sick', 'permission'],
    'present' => ['absent'], // Instructor can change
    'absent' => ['present', 'sick', 'permission'], // Instructor correction
    'sick' => ['absent'], // If review rejects
    'permission' => ['absent'], // If review rejects
];

// Who can change states
$statePermissions = [
    'present' => ['student', 'instructor', 'admin'], // via check-in or manual
    'sick' => ['student'], // request only
    'permission' => ['student'], // request only
    'absent' => ['instructor', 'admin', 'system'], // manual or auto
];
```

---

### Certificate States

**State Diagram:**

```
┌────────┐
│ issued │ → Valid certificate
└───┬────┘
    │
    │ admin revokes
    ▼
┌─────────┐
│ revoked │ → Invalid (permanently)
└─────────┘

┌────────┐
│ issued │ → Valid until expiry_date
└───┬────┘
    │
    │ expiry_date passed
    ▼
┌─────────┐
│ expired │ → Invalid (time-based)
└─────────┘
```

**State Validation:**

```php
public function isValid(): bool
{
    // Check status
    if ($this->status !== 'issued') {
        return false;
    }
    
    // Check expiry
    if ($this->expiry_date && $this->expiry_date->isPast()) {
        return false;
    }
    
    return true;
}
```

**State is Immutable:**
- Once issued, certificate data should not change
- Only state can change: issued → revoked/expired
- No reverse transitions (revoked cannot become issued again)

---

### Announcement States

**State Diagram:**

```
┌───────┐
│ draft │ → Only visible to creator
└───┬───┘
    │
    │ publish
    ▼
┌───────────┐
│ published │ → Visible to target audience
└───┬───────┘
    │
    │ archive OR expires_at passed
    ▼
┌──────────┐
│ archived │ → Hidden from main view
└──────────┘
    │
    │ can be republished (policy)
    └──────────────────────────────┐
                                   ▼
                            ┌───────────┐
                            │ published │
                            └───────────┘
```

---

### Notification States

**Simple Two-State:**

```
┌────────┐
│ unread │ → is_read = false
└───┬────┘
    │
    │ user opens OR marks as read
    ▼
┌────────┐
│  read  │ → is_read = true, read_at set
└───┬────┘
    │
    │ user deletes OR system expires
    ▼
┌─────────┐
│ deleted │ → Removed from database
└─────────┘
```

---

## Best Practices

### 1. Controller Best Practices

**✅ DO:**

```php
// 1. Use Policy for authorization
public function update(Request $request, Course $course)
{
    $this->authorize('update', $course);
    
    $validated = $request->validate([...]);
    $course->update($validated);
    
    return response()->json($course);
}

// 2. Use transactions for multiple operations
public function enroll(Request $request)
{
    DB::beginTransaction();
    
    try {
        $enrollment = Enrollment::create($data);
        $this->createNotification($enrollment);
        $this->updateStatistics($enrollment);
        
        DB::commit();
        return response()->json($enrollment, 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

// 3. Eager load relationships
public function index()
{
    $courses = Course::with(['instructor.user', 'enrollments'])
        ->paginate(15);
    
    return response()->json($courses);
}

// 4. Use Resource Controllers (REST conventions)
Route::apiResource('courses', CourseController::class);

// 5. Return appropriate HTTP status codes
return response()->json($data, 201); // Created
return response()->json($data, 200); // OK
return response()->json(['error' => 'Not found'], 404);
return response()->json(['error' => 'Forbidden'], 403);
```

**❌ DON'T:**

```php
// 1. Don't put business logic in routes
Route::get('/courses', function () {
    // BAD: Logic in route
    $courses = Course::where('active', true)->get();
    return response()->json($courses);
});

// 2. Don't use raw SQL when Eloquent suffices
// BAD
$courses = DB::select('SELECT * FROM courses WHERE instructor_id = ?', [$id]);

// GOOD
$courses = Course::where('instructor_id', $id)->get();

// 3. Don't forget authorization checks
public function delete(Course $course)
{
    // BAD: No authorization check
    $course->delete();
    return response()->json(['message' => 'Deleted']);
}

// 4. Don't return sensitive data
// BAD
return response()->json($user); // Includes password hash

// GOOD
return response()->json($user->only(['id', 'name', 'email', 'role']));
```

---

### 2. Model Best Practices

**✅ DO:**

```php
// 1. Define fillable or guarded
protected $fillable = [
    'name', 'email', 'role',
];

// 2. Use casts for type conversion
protected $casts = [
    'is_active' => 'boolean',
    'published_at' => 'datetime',
    'metadata' => 'array',
];

// 3. Define relationships
public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

// 4. Use scopes for common queries
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

// Usage: Course::active()->get();

// 5. Add model methods for business logic
public function isEligibleForCertificate(): bool
{
    return $this->final_grade >= 60 
        && $this->attendance_percentage >= 75;
}

// 6. Use accessors for computed attributes
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}

// Usage: $user->full_name
```

**❌ DON'T:**

```php
// 1. Don't leave mass assignment vulnerability
// BAD: No fillable/guarded
class User extends Model
{
    // Missing $fillable or $guarded
}

// 2. Don't put complex logic in models
// BAD: Too much logic in model
public function calculateEverything()
{
    // 200 lines of complex calculations
}

// GOOD: Use service classes for complex logic
```

---

### 3. Policy Best Practices

**✅ DO:**

```php
// 1. Keep authorization logic in policies
class CoursePolicy
{
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin' 
            || ($user->instructor && $user->instructor->id === $course->instructor_id);
    }
}

// 2. Use descriptive method names
public function createForCourse(User $user, Course $course): bool
{
    // Clear what this checks
}

// 3. Return bool for simple checks
public function view(User $user, Course $course): bool
{
    return true; // Simple yes/no
}

// 4. Use Response for detailed feedback (optional)
public function update(User $user, Course $course): Response
{
    if ($user->role === 'admin') {
        return Response::allow();
    }
    
    if ($user->instructor && $user->instructor->id === $course->instructor_id) {
        return Response::allow();
    }
    
    return Response::deny('You are not authorized to update this course.');
}
```

---

### 4. Database Best Practices

**✅ DO:**

```php
// 1. Use migrations for schema changes
php artisan make:migration create_courses_table

// 2. Add indexes for foreign keys and frequently queried columns
Schema::create('enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->onDelete('cascade');
    $table->foreignId('course_id')->constrained()->onDelete('cascade');
    
    // Add composite unique index
    $table->unique(['student_id', 'course_id']);
    
    // Add index for status queries
    $table->index('status');
});

// 3. Use database transactions
DB::transaction(function () {
    // Multiple operations that should be atomic
});

// 4. Use query builder for complex queries
$results = DB::table('enrollments')
    ->join('students', 'enrollments.student_id', '=', 'students.id')
    ->select('enrollments.*', 'students.name')
    ->where('enrollments.status', 'active')
    ->get();
```

---

### 5. Security Best Practices

**✅ DO:**

```php
// 1. Always validate input
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);

// 2. Use parameterized queries (Eloquent does this automatically)
Course::where('instructor_id', $id)->get(); // Safe

// 3. Sanitize output (Laravel does this in Blade)
{{ $user->name }} // Auto-escaped

// 4. Use CSRF protection (enabled by default in Laravel)

// 5. Hash passwords
$user->password = Hash::make($request->password);

// 6. Use middleware for authentication
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});

// 7. Implement rate limiting
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute
});
```

**❌ DON'T:**

```php
// 1. Don't trust user input
// BAD
$course = Course::find($request->id); // What if ID is malicious?

// GOOD
$validated = $request->validate(['id' => 'required|integer|exists:courses,id']);
$course = Course::findOrFail($validated['id']);

// 2. Don't expose sensitive data
// BAD
return response()->json($user); // Includes password_hash

// GOOD
return response()->json($user->only(['id', 'name', 'email']));

// 3. Don't use raw SQL with user input
// BAD
DB::select("SELECT * FROM users WHERE email = '{$request->email}'"); // SQL injection

// GOOD
User::where('email', $request->email)->first();
```

---

### 6. Performance Best Practices

**✅ DO:**

```php
// 1. Use eager loading to prevent N+1 queries
$courses = Course::with(['instructor.user', 'enrollments'])->get();

// 2. Use pagination for large datasets
$courses = Course::paginate(15);

// 3. Use caching for frequently accessed data
$courses = Cache::remember('courses.all', 3600, function () {
    return Course::with('instructor')->get();
});

// 4. Use select() to limit columns
$users = User::select(['id', 'name', 'email'])->get();

// 5. Use chunk() for large datasets
Course::chunk(100, function ($courses) {
    foreach ($courses as $course) {
        // Process course
    }
});

// 6. Add database indexes
Schema::table('enrollments', function (Blueprint $table) {
    $table->index(['student_id', 'status']);
});
```

---

### 7. API Response Best Practices

**✅ DO:**

```php
// 1. Consistent response format
// Success
return response()->json([
    'data' => $resource,
    'message' => 'Operation successful'
], 200);

// Error
return response()->json([
    'message' => 'Error occurred',
    'error' => $exception->getMessage()
], 500);

// 2. Use appropriate HTTP status codes
201 // Created
200 // OK
204 // No Content
400 // Bad Request
401 // Unauthorized
403 // Forbidden
404 // Not Found
422 // Validation Error
500 // Server Error

// 3. Include metadata for collections
return response()->json([
    'data' => $courses,
    'meta' => [
        'total' => $courses->total(),
        'per_page' => $courses->perPage(),
        'current_page' => $courses->currentPage(),
    ]
]);

// 4. Use API Resources for transformation
return new CourseResource($course);
return CourseResource::collection($courses);
```

---

### 8. Testing Best Practices

**✅ DO:**

```php
// 1. Write tests for critical paths
public function test_student_can_enroll_in_course()
{
    $student = Student::factory()->create();
    $course = Course::factory()->create();
    
    $response = $this->actingAs($student->user)
        ->postJson('/api/v1/enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'course_id' => $course->id,
    ]);
}

// 2. Test authorization
public function test_student_cannot_delete_course()
{
    $student = Student::factory()->create();
    $course = Course::factory()->create();
    
    $response = $this->actingAs($student->user)
        ->deleteJson("/api/v1/courses/{$course->id}");
    
    $response->assertStatus(403);
}

// 3. Use factories for test data
Course::factory()->count(10)->create();

// 4. Test edge cases
public function test_cannot_enroll_twice_in_same_course()
{
    // Test duplicate enrollment prevention
}
```

---

### 9. Code Organization Best Practices

**✅ DO:**

```
app/
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       ├── CourseController.php
│   │       └── EnrollmentController.php
│   ├── Middleware/
│   └── Requests/
│       └── StoreCourseRequest.php
├── Models/
│   ├── Course.php
│   └── Enrollment.php
├── Policies/
│   ├── CoursePolicy.php
│   └── EnrollmentPolicy.php
├── Services/
│   ├── NotificationService.php
│   └── CertificateService.php
└── Repositories/ (optional)
    └── CourseRepository.php
```

**Separation of Concerns:**

```php
// Controller: Orchestration
class CourseController extends Controller
{
    public function store(Request $request, CourseService $service)
    {
        $this->authorize('create', Course::class);
        $validated = $request->validate([...]);
        
        $course = $service->createCourse($validated);
        
        return response()->json($course, 201);
    }
}

// Service: Business Logic
class CourseService
{
    public function createCourse(array $data): Course
    {
        DB::beginTransaction();
        
        try {
            $course = Course::create($data);
            $this->notificationService->notifyNewCourse($course);
            
            DB::commit();
            return $course;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

// Model: Data & Simple Logic
class Course extends Model
{
    protected $fillable = ['course_code', 'course_name', 'instructor_id'];
    
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
```

---

### 10. Error Handling Best Practices

**✅ DO:**

```php
// 1. Use try-catch for external operations
public function store(Request $request)
{
    try {
        $course = Course::create($validated);
        return response()->json($course, 201);
    } catch (\Exception $e) {
        Log::error('Course creation failed: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Error creating course',
            'error' => $e->getMessage()
        ], 500);
    }
}

// 2. Use custom exceptions
class EnrollmentException extends \Exception {}

throw new EnrollmentException('Student already enrolled');

// 3. Log errors
Log::error('Failed to process enrollment', [
    'student_id' => $studentId,
    'course_id' => $courseId,
    'error' => $e->getMessage()
]);

// 4. Return meaningful error messages
return response()->json([
    'message' => 'Validation failed',
    'errors' => [
        'email' => ['The email has already been taken.']
    ]
], 422);
```

---

## Summary

### System Architecture
- **MVC Pattern** with Laravel
- **Policy-Based Authorization** for access control
- **RESTful API** design
- **Token-Based Authentication** with Sanctum

### Key Concepts
- **Enrollment** is the central entity connecting students and courses
- **State Management** ensures data integrity
- **Eager Loading** prevents performance issues
- **Validation** at multiple levels (request, business logic, database)

### Core Business Rules
1. One enrollment per student per course
2. Eligibility checks before certificate generation
3. Attendance tracking with review process
4. Grade calculation from weighted components
5. Notification auto-generation from system events

### Best Practices
- Use Policies for authorization
- Eager load relationships
- Validate all input
- Use transactions for atomic operations
- Return appropriate HTTP status codes
- Write tests for critical paths
- Log errors for debugging
- Keep controllers thin
- Put business logic in services
- Use database indexes for performance

---

## Additional Resources

### Laravel Documentation
- **Eloquent ORM:** https://laravel.com/docs/eloquent
- **Authentication:** https://laravel.com/docs/sanctum
- **Authorization:** https://laravel.com/docs/authorization
- **Validation:** https://laravel.com/docs/validation

### Project Structure
```
lmsRPL3/
├── app/
│   ├── Http/Controllers/API/
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── docs/
│   └── api/
│       ├── API-DOCUMENTATION.md
│       └── BUSINESS-LOGIC.md (this file)
└── tests/
```

### Database Schema Reference
See `ERD-SmartDev-LMS-v2.puml` for complete entity relationship diagram.

---

## Conclusion

This business logic documentation provides a comprehensive understanding of how the LMS SmartDev system works, from high-level architecture to detailed implementation patterns. 

**Key Takeaways:**

1. **Authorization is handled by Policies** - centralized and reusable
2. **State management is explicit** - clear transitions and validations
3. **Business rules are enforced** at multiple levels (validation, policies, database)
4. **Performance is considered** through eager loading and caching
5. **Security is built-in** through validation, authentication, and authorization

Use this documentation alongside the API documentation to build robust integrations with the LMS system.

---

**Document Version:** 1.0  
**Last Updated:** December 2024  
**Maintained By:** LMS SmartDev Development Team

For questions or clarifications, please contact the development team or refer to the code comments in the actual implementation.