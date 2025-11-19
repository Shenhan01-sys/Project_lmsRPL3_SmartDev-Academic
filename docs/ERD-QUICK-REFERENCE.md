# ERD v2.0 - Quick Reference Guide
**SmartDev Academic LMS**

---

## 🎯 TL;DR

### What Changed?
- ✅ **5 New Tables**: Attendance, Announcements, Notifications, Certificates
- ⚠️ **2 Breaking Changes**: `submissions` & `grades` now use `enrollment_id`
- ✨ **10+ Enhanced Tables**: Added status fields, file paths, metadata

### Migration Time
- **Development**: 12-17 days
- **Testing**: 2-3 days
- **Total**: ~3 weeks

### Risk Level
- **Overall**: MEDIUM-HIGH
- **Breaking Changes**: HIGH (data migration required)
- **New Features**: MEDIUM (new code needed)

---

## 📊 Tables at a Glance

### Core Tables (14 total)
```
Authentication & Users (4)
├── users
├── students
├── instructors
└── parents

Academic (5)
├── courses
├── enrollments ⭐ CENTRAL HUB
├── course_modules
├── materials
└── student_registrations

Assignments & Grading (4)
├── assignments
├── submissions ⚠️ REFACTORED
├── grade_components
└── grades ⚠️ REFACTORED

Auth Tokens (3)
├── personal_access_tokens
├── sessions
└── password_reset_tokens
```

### New Feature Tables (5 total)
```
Attendance System (2)
├── attendance_sessions
└── attendance_records

Communication (2)
├── announcements
└── notifications

Certification (1)
└── certificates
```

---

## ⚠️ Breaking Changes

### 1. submissions Table
```diff
- student_id (FK to students)
+ enrollment_id (FK to enrollments)
```

**Migration SQL**:
```sql
UPDATE submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN enrollments e ON e.student_id = s.student_id AND e.course_id = a.course_id
SET s.enrollment_id = e.id;
```

**Controller Change**:
```php
// OLD
Submission::create([
    'student_id' => $studentId,
    'assignment_id' => $assignmentId,
]);

// NEW
$enrollment = Enrollment::where('student_id', $studentId)
    ->where('course_id', $courseId)
    ->firstOrFail();
    
Submission::create([
    'enrollment_id' => $enrollment->id,
    'assignment_id' => $assignmentId,
]);
```

### 2. grades Table
```diff
- student_id (FK to students)
+ enrollment_id (FK to enrollments)
```

**Same migration pattern as submissions**

---

## 🆕 New Tables Cheat Sheet

### attendance_sessions
```sql
Key Fields:
├── course_id (FK)
├── session_name
├── session_date
├── deadline (DATETIME)
└── status (scheduled/open/closed)

Purpose: Manage attendance sessions per course
```

### attendance_records
```sql
Key Fields:
├── enrollment_id (FK) ⭐
├── attendance_session_id (FK)
├── status (pending/present/sick/permission/absent)
├── check_in_time
├── supporting_doc_path
└── reviewed_by (FK)

Purpose: Track individual attendance
Unique: (enrollment_id, attendance_session_id)
```

### announcements
```sql
Key Fields:
├── created_by (FK)
├── course_id (FK, nullable)
├── title
├── content
├── announcement_type (global/course)
├── priority (normal/high/urgent)
└── status (draft/published/archived)

Purpose: Global and course announcements
```

### notifications
```sql
Key Fields:
├── user_id (FK)
├── notification_type
├── title
├── message
├── action_url
├── is_read
└── priority (low/normal/high)

Purpose: In-app notifications
Types: assignment_due, grade_released, announcement_new, etc.
```

### certificates
```sql
Key Fields:
├── enrollment_id (FK)
├── course_id (FK, denormalized)
├── certificate_code (UNIQUE)
├── certificate_file_path
├── final_grade ⭐ SNAPSHOT
├── attendance_percentage ⭐ SNAPSHOT
├── assignment_completion_rate ⭐ SNAPSHOT
└── status (issued/revoked/expired)

Purpose: Certificate generation with frozen snapshot data
Format: CERT-{YEAR}-{COURSE}-{RANDOM}
```

---

## 🔗 Relationship Map

### Enrollment-Centric Design
```
enrollments (CENTRAL HUB)
├── submissions (1:N)
├── grades (1:N)
├── attendance_records (1:N)
└── certificates (1:N)

All student-course operations go through enrollment!
```

### Full Relationship Tree
```
users
├── students (1:1)
├── instructors (1:1)
├── parents (1:1)
├── attendance_sessions (1:N as creator)
├── announcements (1:N as creator)
├── notifications (1:N)
└── certificates (1:N as generator)

courses
├── enrollments (1:N)
├── course_modules (1:N)
├── assignments (1:N)
├── grade_components (1:N)
├── attendance_sessions (1:N)
└── announcements (1:N)

enrollments ⭐ CENTRAL
├── submissions (1:N)
├── grades (1:N)
├── attendance_records (1:N)
└── certificates (1:N)
```

---

## 🎯 Key Business Rules

### Enrollment Validation
```php
// Required before: submit, grade, attendance, certificate
$enrollment = Enrollment::where('student_id', $studentId)
    ->where('course_id', $courseId)
    ->where('status', 'active')
    ->firstOrFail();
```

### Certificate Eligibility
```php
if ($enrollment->final_grade >= 60.0 && 
    $attendancePercentage >= 75.0 &&
    $assignmentCompletionRate >= 80.0 &&
    $enrollment->status === 'completed') {
    // Generate certificate
}
```

### Attendance Workflow
```
pending → present (check-in)
pending → sick/permission (needs review + document)
pending → absent (auto after deadline)
```

### Announcement Types
```
Global: course_id = NULL
Course: course_id = specific course
```

---

## 📝 Migration Command Sequence

```bash
# 1. Backup
mysqldump -u root -p lms_database > backup_$(date +%Y%m%d).sql

# 2. Create new tables
php artisan make:migration create_attendance_sessions_table
php artisan make:migration create_attendance_records_table
php artisan make:migration create_announcements_table
php artisan make:migration create_notifications_table
php artisan make:migration create_certificates_table

# 3. Enhance existing tables
php artisan make:migration enhance_enrollments_table
php artisan make:migration enhance_courses_table
php artisan make:migration enhance_materials_table
php artisan make:migration enhance_assignments_table

# 4. Refactor (BREAKING)
php artisan make:migration add_enrollment_id_to_submissions_table
php artisan make:migration migrate_submissions_student_to_enrollment
php artisan make:migration drop_student_id_from_submissions_table

php artisan make:migration add_enrollment_id_to_grades_table
php artisan make:migration migrate_grades_student_to_enrollment
php artisan make:migration drop_student_id_from_grades_table

# 5. Run migrations
php artisan migrate

# 6. Verify
php artisan db:show
php artisan migrate:status
```

---

## 🔧 Model Updates Cheat Sheet

### New Models to Create
```bash
php artisan make:model AttendanceSession
php artisan make:model AttendanceRecord
php artisan make:model Announcement
php artisan make:model Notification
php artisan make:model Certificate
```

### Update Submission Model
```php
class Submission extends Model
{
    protected $fillable = [
        'enrollment_id',  // CHANGED from student_id
        'assignment_id',
        'submission_date',
        'status',
        'score',
        'feedback',
        'graded_by',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Helper method
    public function student()
    {
        return $this->enrollment->student;
    }
}
```

### Update Grade Model
```php
class Grade extends Model
{
    protected $fillable = [
        'enrollment_id',  // CHANGED from student_id
        'grade_component_id',
        'score',
        'max_score',
        'graded_by',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Helper method
    public function student()
    {
        return $this->enrollment->student;
    }
}
```

### Update Enrollment Model
```php
class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_date',
        'status',
        'final_grade',
    ];

    // NEW relationships
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
```

---

## 🗂️ Index Strategy

### Critical Indexes
```sql
-- Enrollments (CENTRAL HUB)
CREATE INDEX idx_enrollments_student ON enrollments(student_id);
CREATE INDEX idx_enrollments_course ON enrollments(course_id);
CREATE INDEX idx_enrollments_status ON enrollments(status);

-- Submissions (REFACTORED)
CREATE INDEX idx_submissions_enrollment ON submissions(enrollment_id);
CREATE UNIQUE INDEX unique_submission ON submissions(enrollment_id, assignment_id);

-- Grades (REFACTORED)
CREATE INDEX idx_grades_enrollment ON grades(enrollment_id);
CREATE UNIQUE INDEX unique_grade ON grades(enrollment_id, grade_component_id);

-- Attendance
CREATE INDEX idx_attendance_enrollment ON attendance_records(enrollment_id);
CREATE UNIQUE INDEX unique_attendance ON attendance_records(enrollment_id, attendance_session_id);

-- Notifications (HIGH TRAFFIC)
CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read, created_at);

-- Certificates
CREATE INDEX idx_certificates_code ON certificates(certificate_code);
```

---

## 🧪 Quick Test Cases

### Test Enrollment Validation
```php
public function test_cannot_submit_without_enrollment()
{
    $student = Student::factory()->create();
    $assignment = Assignment::factory()->create();

    $this->actingAs($student->user)
        ->postJson("/api/assignments/{$assignment->id}/submit")
        ->assertStatus(403);
}
```

### Test Certificate Eligibility
```php
public function test_certificate_requires_eligibility()
{
    $enrollment = Enrollment::factory()->create([
        'final_grade' => 55,  // Below threshold
    ]);

    $this->postJson("/api/enrollments/{$enrollment->id}/certificate")
        ->assertStatus(422);
}
```

### Test Attendance Auto-Absent
```php
public function test_auto_mark_absent_after_deadline()
{
    $session = AttendanceSession::factory()->create([
        'deadline' => now()->subHours(2),
        'status' => 'closed',
    ]);

    Artisan::call('attendance:auto-mark-absent');

    $this->assertDatabaseHas('attendance_records', [
        'attendance_session_id' => $session->id,
        'status' => 'absent',
    ]);
}
```

---

## 🚨 Common Pitfalls

### 1. Forgetting Enrollment Check
```php
❌ BAD
$submission = Submission::create([
    'enrollment_id' => $request->enrollment_id,  // User input!
]);

✅ GOOD
$enrollment = Enrollment::where('student_id', $studentId)
    ->where('course_id', $courseId)
    ->where('status', 'active')
    ->firstOrFail();
    
$submission = Submission::create([
    'enrollment_id' => $enrollment->id,
]);
```

### 2. Using student_id Directly
```php
❌ BAD
$grades = Grade::where('student_id', $studentId)->get();

✅ GOOD
$grades = Grade::whereHas('enrollment', function($q) use ($studentId) {
    $q->where('student_id', $studentId);
})->get();
```

### 3. Certificate Data After Issuance
```php
❌ BAD - Updating grade after certificate issued
$grade->update(['score' => 95]);
// Certificate still shows old grade!

✅ GOOD - Understand snapshot approach
// Certificate data is FROZEN at issue time
// Grade changes don't affect issued certificates
```

---

## 📊 Query Performance Tips

### Use Eager Loading
```php
// Load enrollment with student in one query
$submissions = Submission::with('enrollment.student')->get();

// Load multiple relationships
$enrollments = Enrollment::with([
    'student',
    'course',
    'submissions',
    'grades',
    'attendanceRecords'
])->get();
```

### Optimize Attendance Queries
```php
// Get attendance summary
$summary = DB::table('attendance_records')
    ->select('enrollment_id')
    ->selectRaw('COUNT(CASE WHEN status = "present" THEN 1 END) as present')
    ->selectRaw('COUNT(CASE WHEN status = "absent" THEN 1 END) as absent')
    ->groupBy('enrollment_id')
    ->get();
```

---

## 🔄 Rollback Commands

### Emergency Rollback
```bash
# 1. Maintenance mode
php artisan down

# 2. Restore database
mysql -u root -p lms_database < backup_before_migration.sql

# 3. Restore code
git checkout previous-stable-tag
composer install --no-dev

# 4. Clear cache
php artisan config:cache
php artisan route:cache

# 5. Resume
php artisan up
```

### Selective Rollback
```bash
# Rollback last migration
php artisan migrate:rollback

# Rollback specific batch
php artisan migrate:rollback --batch=5

# Rollback specific steps
php artisan migrate:rollback --step=3
```

---

## ✅ Final Checklist

### Pre-Migration
- [ ] Full database backup completed
- [ ] Staging environment setup and tested
- [ ] All documentation reviewed
- [ ] Team briefed on changes
- [ ] Maintenance window scheduled

### During Migration
- [ ] Run migrations in correct order
- [ ] Verify data after each migration
- [ ] Check for orphaned records
- [ ] Validate foreign key constraints
- [ ] Test critical workflows

### Post-Migration
- [ ] All tests passing
- [ ] API documentation updated
- [ ] Frontend code updated
- [ ] Performance metrics acceptable
- [ ] Users notified of new features
- [ ] Monitoring in place

---

## 📞 Quick Links

| Resource | Location |
|----------|----------|
| Full Changelog | `ERD-CHANGELOG.md` |
| Comparison | `ERD-COMPARISON.md` |
| Migration Guide | `ERD-MIGRATION-GUIDE.md` |
| PlantUML v2 | `ERD-SmartDev-LMS-v2.puml` |
| This Guide | `ERD-QUICK-REFERENCE.md` |

---

## 💡 Pro Tips

1. **Always validate enrollment** before student-course operations
2. **Use transactions** for critical data migrations
3. **Test in staging** before production deployment
4. **Monitor query performance** after adding indexes
5. **Keep backups** for at least 30 days post-migration
6. **Document custom changes** if you deviate from this guide
7. **Use feature flags** for gradual rollout
8. **Train users** on new features before go-live

---

**Last Updated**: 2024  
**Version**: 2.0  
**Status**: Production Ready

**Quick Access**: Keep this file open during migration! 🚀