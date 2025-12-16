# 📋 Enrollment Validation - Quick Reference

## 🎯 Purpose

Validasi apakah student sudah enrolled di course sebelum melakukan aksi tertentu (submit assignment, dapat nilai, akses materials).

---

## 🏗️ Service Location

**File:** `app/Services/EnrollmentService.php`

---

## 📦 Main Methods

### Basic Validation

```php
// Direct course check
$enrollmentService->isStudentEnrolledInCourse($studentId, $courseId);

// Through assignment
$enrollmentService->isStudentEnrolledInAssignmentCourse($studentId, $assignmentId);

// Through grade component
$enrollmentService->isStudentEnrolledInGradeComponentCourse($studentId, $gradeComponentId);

// Through material
$enrollmentService->isStudentEnrolledInMaterialCourse($studentId, $materialId);

// Through course module
$enrollmentService->isStudentEnrolledInModuleCourse($studentId, $moduleId);
```

### Utility Methods

```php
// Get all enrolled course IDs
$courseIds = $enrollmentService->getEnrolledCourseIds($studentId);

// Bulk validation (returns NOT enrolled IDs)
$notEnrolled = $enrollmentService->validateBulkEnrollment($studentIds, $courseId);

// Quick bulk check
$hasUnenrolled = $enrollmentService->hasUnenrolledStudents($studentIds, $courseId);
```

---

## 🎯 Controllers Updated

### 1. SubmissionController ✅

**Injection:**
```php
protected $enrollmentService;

public function __construct(EnrollmentService $enrollmentService)
{
    $this->enrollmentService = $enrollmentService;
}
```

**Validation in `store()`:**
```php
if (!$this->enrollmentService->isStudentEnrolledInAssignmentCourse(
    $student->id,
    $validated['assignment_id']
)) {
    return response()->json([
        'message' => 'You are not enrolled in the course for this assignment.',
        'error' => 'ENROLLMENT_REQUIRED'
    ], 403);
}
```

**Validation in `update()`:**
- Cek enrollment jika `assignment_id` diubah

---

### 2. GradeController ✅

**Injection:**
```php
protected $enrollmentService;

public function __construct(
    GradingService $gradingService,
    EnrollmentService $enrollmentService
) {
    $this->gradingService = $gradingService;
    $this->enrollmentService = $enrollmentService;
}
```

**Validation in `store()`:**
```php
if (!$this->enrollmentService->isStudentEnrolledInGradeComponentCourse(
    $validated['student_id'],
    $validated['grade_component_id']
)) {
    return response()->json([
        'message' => 'Student is not enrolled in this course.',
        'error' => 'ENROLLMENT_REQUIRED'
    ], 400);
}
```

**Validation in `bulkStore()`:**
```php
$invalidEntries = [];
foreach ($validated['grades'] as $index => $gradeData) {
    if (!$this->enrollmentService->isStudentEnrolledInGradeComponentCourse(
        $gradeData['student_id'],
        $gradeData['grade_component_id']
    )) {
        $invalidEntries[] = [
            'index' => $index,
            'student_id' => $gradeData['student_id'],
            'grade_component_id' => $gradeData['grade_component_id'],
            'reason' => 'Student not enrolled in this course'
        ];
    }
}

if (!empty($invalidEntries)) {
    return response()->json([
        'message' => 'Some students are not enrolled in the required courses.',
        'error' => 'ENROLLMENT_REQUIRED',
        'invalid_entries' => $invalidEntries
    ], 400);
}
```

---

### 3. AssignmentController ✅

**Injection:**
```php
protected $enrollmentService;

public function __construct(EnrollmentService $enrollmentService)
{
    $this->enrollmentService = $enrollmentService;
}
```

**Filter in `index()`:**
```php
if ($user->role === 'student') {
    $enrolledCourseIds = $this->enrollmentService->getEnrolledCourseIds(
        $user->student->id
    );
    
    $assignments = Assignment::with('course')
        ->whereIn('course_id', $enrolledCourseIds)
        ->get();
}
```

**Validation in `show()`:**
```php
if ($user->role === 'student') {
    if (!$this->enrollmentService->isStudentEnrolledInCourse(
        $user->student->id,
        $assignment->course_id
    )) {
        return response()->json([
            'message' => 'You are not enrolled in the course for this assignment.',
            'error' => 'ENROLLMENT_REQUIRED'
        ], 403);
    }
}
```

---

## 🔒 Error Response Format

### Individual Validation Error (403)

```json
{
    "message": "You are not enrolled in the course for this assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

### Bulk Validation Error (400)

```json
{
    "message": "Some students are not enrolled in the required courses.",
    "error": "ENROLLMENT_REQUIRED",
    "invalid_entries": [
        {
            "index": 0,
            "student_id": 5,
            "grade_component_id": 12,
            "reason": "Student not enrolled in this course"
        }
    ]
}
```

---

## 📊 Implementation Checklist

- [x] Create `EnrollmentService.php`
- [x] Update `SubmissionController::store()`
- [x] Update `SubmissionController::update()`
- [x] Update `GradeController::store()`
- [x] Update `GradeController::bulkStore()`
- [x] Update `AssignmentController::index()`
- [x] Update `AssignmentController::show()`
- [x] Create documentation

---

## 🧪 Quick Test Commands

### Test 1: Submit Assignment (Enrolled)
```bash
POST /api/submissions
{
    "assignment_id": 5  // assignment dari enrolled course
}

Expected: 201 Created
```

### Test 2: Submit Assignment (NOT Enrolled)
```bash
POST /api/submissions
{
    "assignment_id": 10  // assignment dari course yang tidak diikuti
}

Expected: 403 Forbidden + ENROLLMENT_REQUIRED
```

### Test 3: Input Grade (NOT Enrolled)
```bash
POST /api/grades
{
    "student_id": 5,
    "grade_component_id": 12,  // component dari course yang tidak diikuti
    "score": 85
}

Expected: 400 Bad Request + ENROLLMENT_REQUIRED
```

---

## 💡 Usage Pattern

```php
// Step 1: Inject service
protected $enrollmentService;

public function __construct(EnrollmentService $enrollmentService)
{
    $this->enrollmentService = $enrollmentService;
}

// Step 2: Validate
public function someMethod(Request $request)
{
    $validated = $request->validate([...]);
    
    // Validation
    if (!$this->enrollmentService->isStudentEnrolledIn...) {
        return response()->json([
            'message' => '...',
            'error' => 'ENROLLMENT_REQUIRED'
        ], 403);
    }
    
    // Continue...
}
```

---

## 📚 Related Files

- **Service:** `app/Services/EnrollmentService.php`
- **Controllers:**
  - `app/Http/Controllers/API/SubmissionController.php`
  - `app/Http/Controllers/API/GradeController.php`
  - `app/Http/Controllers/API/AssignmentController.php`
- **Full Docs:** `docs/ENROLLMENT_VALIDATION.md`

---

## ✅ Benefits

1. **Data Integrity** - Student hanya bisa submit/akses enrolled courses
2. **Security** - Mencegah unauthorized access
3. **Centralized** - Satu service untuk semua validasi
4. **Maintainable** - Mudah update di satu tempat
5. **Testable** - Service independent, bisa di-unit test

---

**Last Updated:** 2025-01-28