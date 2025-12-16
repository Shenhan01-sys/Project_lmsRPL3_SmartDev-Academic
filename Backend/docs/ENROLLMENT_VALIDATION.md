# 📚 Enrollment Validation Documentation

## 🎯 Overview

Dokumentasi ini menjelaskan implementasi **EnrollmentService** untuk memvalidasi apakah student sudah enrolled di course sebelum melakukan aksi tertentu (submit assignment, mendapat nilai, akses materials, dll).

## ❓ Problem Statement

### Masalah yang Diselesaikan

Sebelumnya, terdapat **gap dalam business logic validation** dimana:

1. **Submissions Table** - Student bisa submit assignment untuk course yang tidak diambil
2. **Grades Table** - Instructor bisa input nilai untuk student yang tidak enrolled
3. **Materials/Modules** - Student bisa akses konten course yang tidak diikuti

### Contoh Skenario Masalah

```
❌ TANPA VALIDASI:
1. Student A enrolled di "Mathematics 101" (course_id: 1)
2. Student A TIDAK enrolled di "Physics 101" (course_id: 2)
3. Ada assignment "Quiz 1" untuk Physics 101 (assignment_id: 5)
4. Student A bisa submit ke assignment_id: 5 ❌ (SEHARUSNYA TIDAK BISA!)

✅ DENGAN VALIDASI:
1. Student A enrolled di "Mathematics 101" (course_id: 1)
2. Student A TIDAK enrolled di "Physics 101" (course_id: 2)
3. Student A coba submit assignment untuk Physics 101
4. System menolak dengan error: "ENROLLMENT_REQUIRED" ✅
```

## 🏗️ Architecture

### Struktur Relasi Database

```
┌─────────────┐
│ enrollments │──┐
└─────────────┘  │
                 │
     ┌───────────┼───────────┐
     ▼           ▼           ▼
┌─────────┐ ┌─────────┐ ┌────────┐
│ student │ │ course  │ │        │
└─────────┘ └─────────┘ │        │
                 │       │        │
     ┌───────────┼───────┼────┐   │
     ▼           ▼       ▼    ▼   │
┌────────────┐ ┌─────────────────┐│
│assignments │ │ grade_components││
└────────────┘ └─────────────────┘│
     │                    │        │
     ▼                    ▼        │
┌────────────┐      ┌────────┐    │
│submissions │      │ grades │    │
└────────────┘      └────────┘    │
                                  │
┌──────────────┐   ┌──────────────┤
│course_modules│───│  materials   │
└──────────────┘   └──────────────┘
```

**Prinsip Validasi:**
- Sebelum akses `assignments`, `submissions`, `grades`, `materials`, `course_modules`
- Sistem harus cek dulu apakah student ada di tabel `enrollments` untuk course terkait

## 📦 EnrollmentService

### Location
`app/Services/EnrollmentService.php`

### Methods

#### 1. `isStudentEnrolledInCourse(int $studentId, int $courseId): bool`

Cek apakah student enrolled di course tertentu.

**Parameters:**
- `$studentId` - ID student dari tabel `students`
- `$courseId` - ID course dari tabel `courses`

**Returns:** `true` jika enrolled, `false` jika tidak

**Example:**
```php
$enrollmentService = app(EnrollmentService::class);

if ($enrollmentService->isStudentEnrolledInCourse(1, 5)) {
    // Student enrolled
} else {
    // Student not enrolled
}
```

---

#### 2. `isStudentEnrolledInAssignmentCourse(int $studentId, int $assignmentId): bool`

Cek apakah student enrolled di course dari assignment tertentu.

**Parameters:**
- `$studentId` - ID student
- `$assignmentId` - ID assignment

**Returns:** `true` jika enrolled, `false` jika tidak

**Logic Flow:**
```
assignmentId → get assignment → get course_id → check enrollment
```

---

#### 3. `isStudentEnrolledInGradeComponentCourse(int $studentId, int $gradeComponentId): bool`

Cek apakah student enrolled di course dari grade component tertentu.

**Parameters:**
- `$studentId` - ID student
- `$gradeComponentId` - ID grade component

**Returns:** `true` jika enrolled, `false` jika tidak

---

#### 4. `isStudentEnrolledInMaterialCourse(int $studentId, int $materialId): bool`

Cek apakah student enrolled di course dari material tertentu.

**Parameters:**
- `$studentId` - ID student
- `$materialId` - ID material

**Returns:** `true` jika enrolled, `false` jika tidak

**Logic Flow:**
```
materialId → get material → get course_module → get course_id → check enrollment
```

---

#### 5. `isStudentEnrolledInModuleCourse(int $studentId, int $moduleId): bool`

Cek apakah student enrolled di course dari course module tertentu.

**Parameters:**
- `$studentId` - ID student
- `$moduleId` - ID course module

**Returns:** `true` jika enrolled, `false` jika tidak

---

#### 6. `getEnrolledCourseIds(int $studentId): array`

Mendapatkan semua course ID yang diikuti student.

**Parameters:**
- `$studentId` - ID student

**Returns:** Array of course IDs `[1, 3, 5, 7]`

**Use Case:** Filter data berdasarkan enrollment

---

#### 7. `validateBulkEnrollment(array $studentIds, int $courseId): array`

Validasi multiple students untuk bulk operations.

**Parameters:**
- `$studentIds` - Array of student IDs
- `$courseId` - ID course

**Returns:** Array of student IDs yang TIDAK enrolled

**Example:**
```php
$notEnrolled = $enrollmentService->validateBulkEnrollment([1, 2, 3, 4], 5);

if (!empty($notEnrolled)) {
    // Students with IDs in $notEnrolled are not enrolled
}
```

---

#### 8. `hasUnenrolledStudents(array $studentIds, int $courseId): bool`

Quick check apakah ada student yang tidak enrolled dari array.

**Parameters:**
- `$studentIds` - Array of student IDs
- `$courseId` - ID course

**Returns:** `true` jika ada yang tidak enrolled, `false` jika semua enrolled

---

## 🎯 Implementation in Controllers

### 1. SubmissionController

**File:** `app/Http/Controllers/API/SubmissionController.php`

#### store() Method

**Validasi:** Student harus enrolled di course dari assignment

```php
public function store(Request $request)
{
    $student = $request->user()->student;
    
    $validated = $request->validate([...]);
    
    // VALIDASI ENROLLMENT
    if (!$this->enrollmentService->isStudentEnrolledInAssignmentCourse(
        $student->id,
        $validated['assignment_id']
    )) {
        return response()->json([
            'message' => 'You are not enrolled in the course for this assignment.',
            'error' => 'ENROLLMENT_REQUIRED'
        ], 403);
    }
    
    // Lanjutkan proses submission...
}
```

#### update() Method

**Validasi:** Jika assignment_id diubah, cek enrollment di course baru

---

### 2. GradeController

**File:** `app/Http/Controllers/API/GradeController.php`

#### store() Method - Input Nilai Individual

**Validasi:** Student harus enrolled di course dari grade component

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    
    // VALIDASI ENROLLMENT
    if (!$this->enrollmentService->isStudentEnrolledInGradeComponentCourse(
        $validated['student_id'],
        $validated['grade_component_id']
    )) {
        return response()->json([
            'message' => 'Student is not enrolled in this course.',
            'error' => 'ENROLLMENT_REQUIRED'
        ], 400);
    }
    
    // Lanjutkan input nilai...
}
```

#### bulkStore() Method - Input Nilai Massal

**Validasi:** Semua student harus enrolled di course masing-masing

```php
public function bulkStore(Request $request)
{
    $validated = $request->validate([...]);
    
    // VALIDASI ENROLLMENT untuk setiap entry
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
    
    // Lanjutkan bulk input...
}
```

---

### 3. AssignmentController

**File:** `app/Http/Controllers/API/AssignmentController.php`

#### index() Method

**Filter:** Student hanya lihat assignments dari course yang diikuti

```php
public function index()
{
    $user = Auth::user();
    
    if ($user->role === 'student') {
        // Ambil enrolled course IDs
        $enrolledCourseIds = $this->enrollmentService->getEnrolledCourseIds(
            $user->student->id
        );
        
        // Filter assignments
        $assignments = Assignment::with('course')
            ->whereIn('course_id', $enrolledCourseIds)
            ->get();
    }
    
    return response()->json($assignments);
}
```

#### show() Method

**Validasi:** Student hanya bisa lihat detail assignment dari course yang diikuti

```php
public function show(Assignment $assignment)
{
    $user = Auth::user();
    
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
    
    return response()->json($assignment);
}
```

---

## 🔒 Security & Authorization

### Hierarchy of Checks

1. **Authentication** - Apakah user sudah login? (Middleware)
2. **Authorization** - Apakah user punya role yang tepat? (Policy)
3. **Enrollment Validation** - Apakah student enrolled di course? (EnrollmentService)

### Error Responses

#### ENROLLMENT_REQUIRED (403 Forbidden)

```json
{
    "message": "You are not enrolled in the course for this assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

#### Bulk Validation Error (400 Bad Request)

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
        },
        {
            "index": 3,
            "student_id": 8,
            "grade_component_id": 12,
            "reason": "Student not enrolled in this course"
        }
    ]
}
```

---

## 📊 Controllers Summary

| Controller | Method | Validation Type | Status |
|------------|--------|----------------|---------|
| **SubmissionController** | `store()` | Assignment → Course | ✅ Implemented |
| **SubmissionController** | `update()` | Assignment → Course (if changed) | ✅ Implemented |
| **GradeController** | `store()` | GradeComponent → Course | ✅ Implemented |
| **GradeController** | `bulkStore()` | GradeComponent → Course (bulk) | ✅ Implemented |
| **AssignmentController** | `index()` | Filter by enrolled courses | ✅ Implemented |
| **AssignmentController** | `show()` | Check enrollment | ✅ Implemented |
| **MaterialController** | `index()` | Query filter | ✅ Already exists |
| **CourseModuleController** | `index()` | Query filter | ✅ Already exists |

---

## 🧪 Testing

### Manual Testing Scenarios

#### Test 1: Submit Assignment (Enrolled)

```bash
# Student enrolled di course_id: 1
# Assignment untuk course_id: 1

POST /api/submissions
{
    "assignment_id": 5
}

Expected: 201 Created ✅
```

#### Test 2: Submit Assignment (Not Enrolled)

```bash
# Student enrolled di course_id: 1
# Assignment untuk course_id: 2 (NOT ENROLLED)

POST /api/submissions
{
    "assignment_id": 10
}

Expected: 403 Forbidden
{
    "message": "You are not enrolled in the course for this assignment.",
    "error": "ENROLLMENT_REQUIRED"
}
```

#### Test 3: Bulk Input Grades (Mixed)

```bash
POST /api/grades/bulk
{
    "grades": [
        {"student_id": 1, "grade_component_id": 5, "score": 85},  // ✅ Enrolled
        {"student_id": 2, "grade_component_id": 5, "score": 90},  // ❌ NOT Enrolled
        {"student_id": 3, "grade_component_id": 5, "score": 88}   // ✅ Enrolled
    ]
}

Expected: 400 Bad Request with invalid_entries for student_id: 2
```

---

## 💡 Best Practices

### 1. Dependency Injection

Selalu inject EnrollmentService via constructor:

```php
protected $enrollmentService;

public function __construct(EnrollmentService $enrollmentService)
{
    $this->enrollmentService = $enrollmentService;
}
```

### 2. Early Return Pattern

Validasi enrollment di awal method:

```php
public function store(Request $request)
{
    // 1. Validate input
    $validated = $request->validate([...]);
    
    // 2. Check enrollment (EARLY RETURN)
    if (!$this->enrollmentService->isStudentEnrolled(...)) {
        return response()->json([...], 403);
    }
    
    // 3. Continue with business logic
    // ...
}
```

### 3. Consistent Error Messages

Gunakan error code yang konsisten: `ENROLLMENT_REQUIRED`

### 4. Role-Based Validation

Enrollment validation hanya untuk role `student`:

```php
if ($user->role === 'student') {
    // Validate enrollment
}
// Admin dan Instructor tidak perlu validasi enrollment
```

---

## 🔄 Migration Path

### Implementasi Bertahap

1. ✅ **Phase 1:** Buat EnrollmentService
2. ✅ **Phase 2:** Update SubmissionController
3. ✅ **Phase 3:** Update GradeController
4. ✅ **Phase 4:** Update AssignmentController
5. ⏭️ **Phase 5:** (Optional) Update MaterialController & CourseModuleController untuk konsistensi

---

## 📝 Future Improvements

### 1. Cache Optimization

Cache enrollment data untuk performa:

```php
use Illuminate\Support\Facades\Cache;

public function isStudentEnrolledInCourse(int $studentId, int $courseId): bool
{
    $cacheKey = "enrollment:{$studentId}:{$courseId}";
    
    return Cache::remember($cacheKey, 3600, function () use ($studentId, $courseId) {
        return Enrollment::where('student_id', $studentId)
                        ->where('course_id', $courseId)
                        ->exists();
    });
}
```

### 2. Event-Based Validation

Trigger events saat validation gagal untuk logging:

```php
event(new EnrollmentValidationFailed($studentId, $courseId, $action));
```

### 3. Custom Validation Rule

Buat Laravel validation rule:

```php
'assignment_id' => [
    'required',
    'exists:assignments,id',
    new EnrolledInAssignmentCourse($student->id)
]
```

---

## 📚 Related Documentation

- [ERD-SmartDev-LMS.md](./ERD-SmartDev-LMS.md) - Database structure
- [Policy Documentation](../app/Policies/) - Authorization logic
- [GradingService](../app/Services/GradingService.php) - Grading logic

---

## 👥 Contributors

- Implementation Date: 2025
- Service Location: `app/Services/EnrollmentService.php`
- Updated Controllers: 
  - `SubmissionController`
  - `GradeController`
  - `AssignmentController`

---

## 🎉 Benefits

1. ✅ **Data Integrity** - Student hanya bisa submit/akses course yang diikuti
2. ✅ **Security** - Mencegah unauthorized access ke course materials
3. ✅ **Accurate Reporting** - Grades dan submissions hanya untuk enrolled students
4. ✅ **Centralized Logic** - Validasi di satu tempat (DRY principle)
5. ✅ **Maintainable** - Mudah update/extend di masa depan
6. ✅ **Testable** - Service bisa di-unit test secara terpisah

---

**Last Updated:** 2025-01-28
**Version:** 1.0.0