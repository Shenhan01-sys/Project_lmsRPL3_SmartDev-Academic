# ERD Changelog - SmartDev Academic LMS

## Version 2.0 (2024) - Major Update

### 📋 Overview
This document tracks all changes made to the Entity-Relationship Diagram (ERD) from version 1.0 to version 2.0.

**Migration Impact**: MEDIUM to HIGH  
**Breaking Changes**: YES  
**Data Migration Required**: YES  
**Estimated Migration Time**: 3-5 days

---

## 🆕 New Tables Added (5 tables)

### 1. `attendance_sessions`
**Purpose**: Manage attendance sessions per course with scheduling and lifecycle management.

**Key Features**:
- Session scheduling with date/time
- Status lifecycle: `scheduled` → `open` → `closed`
- Automatic deadline enforcement
- Support for multiple check-in methods (manual, QR, location-based)

**Columns**:
- `id` (PK)
- `course_id` (FK to courses)
- `created_by` (FK to users)
- `session_name`, `session_date`, `start_time`, `end_time`
- `deadline` (DATETIME)
- `status` (ENUM: scheduled, open, closed)
- `notes`, `created_at`, `updated_at`

**Relationships**:
- Belongs to: `courses`, `users` (creator)
- Has many: `attendance_records`

---

### 2. `attendance_records`
**Purpose**: Track individual student attendance per session with approval workflow.

**Key Features**:
- Automatic absence marking after deadline
- Support for sick/permission with document upload
- Instructor review/approval workflow
- Location tracking (optional)
- Unique constraint on (enrollment_id, attendance_session_id)

**Columns**:
- `id` (PK)
- `enrollment_id` (FK to enrollments) ⭐ **Uses enrollment_id**
- `attendance_session_id` (FK to attendance_sessions)
- `status` (ENUM: pending, present, sick, permission, absent)
- `check_in_time`, `check_in_method`
- `latitude`, `longitude` (for location-based check-in)
- `notes`, `supporting_doc_path`
- `reviewed_by` (FK to users), `reviewed_at`, `review_notes`
- `created_at`, `updated_at`

**Business Rules**:
```
Status Workflow:
1. pending (default) → present (on check-in)
2. pending → sick/permission (needs review + document)
3. pending → absent (auto after deadline)
```

**Relationships**:
- Belongs to: `enrollments`, `attendance_sessions`, `users` (reviewer)

---

### 3. `announcements`
**Purpose**: Multi-level announcement system (global and course-specific).

**Key Features**:
- Global announcements (course_id = NULL)
- Course-specific announcements
- Priority levels (normal, high, urgent)
- Scheduled publishing
- Pin to top functionality
- View count analytics

**Columns**:
- `id` (PK)
- `created_by` (FK to users)
- `course_id` (FK to courses, NULLABLE)
- `title`, `content`
- `announcement_type` (ENUM: global, course)
- `priority` (ENUM: normal, high, urgent)
- `status` (ENUM: draft, published, archived)
- `published_at`, `expires_at`
- `view_count`, `pinned`
- `created_at`, `updated_at`

**Business Rules**:
```
- Global announcement: course_id = NULL, announcement_type = 'global'
- Course announcement: course_id filled, announcement_type = 'course'
- Can schedule future publishing via published_at
- Auto-archive after expires_at
```

**Relationships**:
- Belongs to: `users` (creator), `courses` (optional)

---

### 4. `notifications`
**Purpose**: In-app notification system for real-time user updates.

**Key Features**:
- Multiple notification types
- Deep linking via action_url
- Read/unread tracking
- Priority levels
- Expiration support
- Polymorphic relationship support

**Columns**:
- `id` (PK)
- `user_id` (FK to users)
- `notification_type` (VARCHAR: assignment_due, grade_released, etc.)
- `title`, `message`
- `action_url` (deep link)
- `related_entity_type`, `related_entity_id` (polymorphic)
- `is_read`, `read_at`
- `priority` (ENUM: low, normal, high)
- `expires_at`
- `created_at`, `updated_at`

**Notification Types**:
```
- assignment_due: Assignment deadline reminder
- grade_released: New grade posted
- announcement_new: New announcement published
- attendance_reminder: Attendance session opening soon
- certificate_ready: Certificate generated
- course_enrolled: Successfully enrolled in course
- submission_graded: Assignment graded
```

**Indexes Required**:
```sql
INDEX idx_user_unread (user_id, is_read, created_at)
INDEX idx_notification_type (notification_type)
```

**Relationships**:
- Belongs to: `users`

---

### 5. `certificates`
**Purpose**: Certificate management with snapshot-based aggregated data.

**Key Features**:
- Snapshot approach (data frozen at issue time)
- Unique certificate codes for verification
- Public verification endpoint
- Revocation support
- Metadata storage (JSON)

**Columns**:
- `id` (PK)
- `enrollment_id` (FK to enrollments)
- `course_id` (FK to courses, denormalized)
- `certificate_code` (VARCHAR UNIQUE)
- `certificate_file_path` (VARCHAR)
- `final_grade` (DECIMAL 5,2) ⭐ **Snapshot**
- `attendance_percentage` (DECIMAL 5,2) ⭐ **Snapshot**
- `assignment_completion_rate` (DECIMAL 5,2) ⭐ **Snapshot**
- `grade_letter` (CHAR 2)
- `issue_date`, `expiry_date`
- `generated_by` (FK to users)
- `status` (ENUM: issued, revoked, expired)
- `revocation_reason`, `revoked_at`
- `verification_count`, `metadata`
- `created_at`, `updated_at`

**Snapshot Approach**:
```
The certificate stores CALCULATED VALUES at the time of issuance:
- final_grade: Calculated from all grade components
- attendance_percentage: Calculated from attendance records
- assignment_completion_rate: Calculated from submissions

These values are FROZEN and NOT affected by future changes!
If a grade is updated after certificate issuance, the certificate
remains unchanged (data integrity for issued credentials).
```

**Eligibility Rules**:
```
To generate certificate:
- final_grade >= 60.0
- attendance_percentage >= 75.0
- assignment_completion_rate >= 80.0
- enrollment.status == 'completed'
```

**Certificate Code Format**:
```
CERT-{YEAR}-{COURSE_CODE}-{RANDOM_8_CHARS}
Example: CERT-2024-CS101-A3F9K2M1
```

**Relationships**:
- Belongs to: `enrollments`, `courses`, `users` (generator)

---

## 🔄 Tables Modified (Refactoring)

### 1. `submissions` - ⭐ BREAKING CHANGE

**Changed From**:
```sql
student_id : BIGINT (FK to students)
```

**Changed To**:
```sql
enrollment_id : BIGINT (FK to enrollments)
```

**Reason for Change**:
- **Data Integrity**: enrollment_id guarantees student is enrolled in the course
- **Validation**: Prevents submissions from non-enrolled students at database level
- **Query Optimization**: Eliminates need for additional enrollment check queries
- **Single Source of Truth**: enrollment_id contains both student + course relationship

**Migration Strategy**:
```sql
-- Step 1: Add new column
ALTER TABLE submissions ADD COLUMN enrollment_id BIGINT AFTER id;

-- Step 2: Populate data
UPDATE submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN enrollments e ON e.student_id = s.student_id AND e.course_id = a.course_id
SET s.enrollment_id = e.id;

-- Step 3: Drop old FK and column
ALTER TABLE submissions 
  DROP FOREIGN KEY fk_submissions_student,
  DROP COLUMN student_id;

-- Step 4: Add new FK and make NOT NULL
ALTER TABLE submissions 
  ADD CONSTRAINT fk_submissions_enrollment 
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  MODIFY enrollment_id BIGINT NOT NULL;

-- Step 5: Add unique constraint
ALTER TABLE submissions 
  ADD UNIQUE KEY unique_submission (enrollment_id, assignment_id);
```

**Additional Columns Added**:
- `submission_date` (DATETIME) - explicit submission timestamp
- `status` (ENUM: submitted, graded, late, resubmit)
- `graded_by` (FK to users)

**Impact**:
- Controllers: Update `SubmissionController` to use `enrollment_id`
- Models: Update `Submission` model relationships
- Validation: Update validation rules
- API: Update API request/response formats
- Tests: Update test cases

---

### 2. `grades` - ⭐ BREAKING CHANGE

**Changed From**:
```sql
student_id : BIGINT (FK to students)
```

**Changed To**:
```sql
enrollment_id : BIGINT (FK to enrollments)
```

**Reason for Change**:
- Same reasons as `submissions` table
- Ensures grade can only be given to enrolled students
- Simplifies grade calculation queries

**Migration Strategy**:
```sql
-- Similar pattern to submissions migration
ALTER TABLE grades ADD COLUMN enrollment_id BIGINT AFTER id;

UPDATE grades g
JOIN grade_components gc ON g.grade_component_id = gc.id
JOIN enrollments e ON e.student_id = g.student_id AND e.course_id = gc.course_id
SET g.enrollment_id = e.id;

ALTER TABLE grades 
  DROP FOREIGN KEY fk_grades_student,
  DROP COLUMN student_id;

ALTER TABLE grades 
  ADD CONSTRAINT fk_grades_enrollment 
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  MODIFY enrollment_id BIGINT NOT NULL;

ALTER TABLE grades 
  ADD UNIQUE KEY unique_grade (enrollment_id, grade_component_id);
```

**Impact**:
- Controllers: Update `GradeController` and `GradeComponentController`
- Services: Update `GradingService`
- Models: Update `Grade` model relationships
- Reports: Update grade report queries

---

### 3. `students` - Enhanced

**Columns Added**:
- `place_of_birth` (VARCHAR 255)
- More detailed personal information alignment

**Impact**: LOW - Additive only, no breaking changes

---

### 4. `instructors` - Enhanced

**Columns Added**:
- More detailed profile information

**Impact**: LOW - Additive only, no breaking changes

---

### 5. `enrollments` - Enhanced

**Columns Added**:
- `enrollment_date` (DATE)
- `status` (ENUM: active, completed, dropped, failed)
- `final_grade` (DECIMAL 5,2)

**Reason**:
- Track enrollment lifecycle
- Store final calculated grade for certificate generation
- Better reporting and analytics

**Impact**: MEDIUM - Requires logic to update status and calculate final_grade

---

### 6. `courses` - Enhanced

**Columns Added**:
- `credits` (INT)
- `max_students` (INT)
- `status` (ENUM: active, inactive, archived)

**Impact**: LOW - Additive only

---

### 7. `materials` - Enhanced

**Columns Added**:
- `file_size` (BIGINT)
- `download_count` (INT)

**Impact**: LOW - Analytics enhancement

---

### 8. `assignments` - Enhanced

**Columns Added**:
- `max_score` (DECIMAL 8,2)
- `status` (ENUM: draft, published, closed)

**Impact**: MEDIUM - Requires workflow implementation

---

### 9. `grade_components` - Enhanced

**Columns Added**:
- `component_type` (ENUM: assignment, quiz, exam, project, attendance)

**Impact**: LOW - Better categorization

---

### 10. `users` - Enhanced

**Columns Added**:
- `photo_path` (VARCHAR 255) - Profile photo storage

**Impact**: LOW - Optional field

---

## ❌ Tables Removed

### `files` table (REMOVED)

**Reason for Removal**:
- Generic file storage table was redundant
- File management is better handled with specific columns in relevant tables
- Reduces complexity and improves query performance

**Replaced By**:
- `materials.file_path` - for course materials
- `submissions.file_path` - for student submissions
- `attendance_records.supporting_doc_path` - for sick/permission documents
- `certificates.certificate_file_path` - for generated certificates
- `users.photo_path` - for profile photos
- `student_registrations.*_path` - for registration documents

---

## 🔗 Relationship Changes

### New Central Hub: `enrollments`

**Before (v1)**:
```
students → submissions (direct)
students → grades (direct)
```

**After (v2)**:
```
students → enrollments → submissions
students → enrollments → grades
students → enrollments → attendance_records
students → enrollments → certificates
```

**Benefits**:
1. ✅ Database-level enrollment validation
2. ✅ Single source of truth for student-course relationship
3. ✅ Cascading deletes properly handled
4. ✅ Simplified queries (no need for separate enrollment checks)
5. ✅ Better data integrity

---

## 📊 New Indexes Required

```sql
-- Enrollments
CREATE INDEX idx_enrollments_student ON enrollments(student_id);
CREATE INDEX idx_enrollments_course ON enrollments(course_id);
CREATE INDEX idx_enrollments_status ON enrollments(status);
CREATE UNIQUE INDEX unique_enrollment ON enrollments(student_id, course_id);

-- Submissions (updated)
CREATE INDEX idx_submissions_enrollment ON submissions(enrollment_id);
CREATE INDEX idx_submissions_assignment ON submissions(assignment_id);
CREATE INDEX idx_submissions_status ON submissions(status);
CREATE UNIQUE INDEX unique_submission ON submissions(enrollment_id, assignment_id);

-- Grades (updated)
CREATE INDEX idx_grades_enrollment ON grades(enrollment_id);
CREATE INDEX idx_grades_component ON grades(grade_component_id);
CREATE UNIQUE INDEX unique_grade ON grades(enrollment_id, grade_component_id);

-- Attendance Records
CREATE INDEX idx_attendance_enrollment ON attendance_records(enrollment_id);
CREATE INDEX idx_attendance_session ON attendance_records(attendance_session_id);
CREATE INDEX idx_attendance_status ON attendance_records(status);
CREATE UNIQUE INDEX unique_attendance ON attendance_records(enrollment_id, attendance_session_id);

-- Notifications
CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read, created_at);
CREATE INDEX idx_notifications_type ON notifications(notification_type);

-- Certificates
CREATE INDEX idx_certificates_code ON certificates(certificate_code);
CREATE INDEX idx_certificates_enrollment ON certificates(enrollment_id);
CREATE INDEX idx_certificates_course ON certificates(course_id);
CREATE INDEX idx_certificates_status ON certificates(status);

-- Announcements
CREATE INDEX idx_announcements_course ON announcements(course_id);
CREATE INDEX idx_announcements_status ON announcements(status);
CREATE INDEX idx_announcements_type ON announcements(announcement_type);
```

---

## 🚀 Migration Checklist

### Phase 1: Preparation
- [ ] Backup production database
- [ ] Setup staging environment
- [ ] Review ERD with team
- [ ] Prepare rollback plan
- [ ] Estimate downtime window

### Phase 2: Create New Tables
- [ ] Create `attendance_sessions` migration
- [ ] Create `attendance_records` migration
- [ ] Create `announcements` migration
- [ ] Create `notifications` migration
- [ ] Create `certificates` migration
- [ ] Run migrations in staging
- [ ] Verify table structure

### Phase 3: Modify Existing Tables
- [ ] Backup `submissions` and `grades` tables
- [ ] Add new columns to various tables
- [ ] Refactor `submissions` to use `enrollment_id`
- [ ] Refactor `grades` to use `enrollment_id`
- [ ] Add indexes
- [ ] Verify data integrity

### Phase 4: Data Migration
- [ ] Populate `enrollment_id` in submissions
- [ ] Populate `enrollment_id` in grades
- [ ] Validate all data migrated correctly
- [ ] Check for orphaned records
- [ ] Verify foreign key constraints

### Phase 5: Application Code Updates
- [ ] Update Eloquent models
- [ ] Update controllers
- [ ] Update services
- [ ] Update validation rules
- [ ] Update API documentation
- [ ] Update frontend API calls

### Phase 6: Testing
- [ ] Unit tests for new features
- [ ] Integration tests
- [ ] API endpoint tests
- [ ] Performance tests
- [ ] User acceptance testing

### Phase 7: Deployment
- [ ] Schedule maintenance window
- [ ] Deploy to production
- [ ] Run migrations
- [ ] Verify all features
- [ ] Monitor errors
- [ ] Update documentation

---

## 📈 Impact Analysis

### High Impact (Breaking Changes)
- ✅ `submissions` table refactoring
- ✅ `grades` table refactoring
- Estimated effort: 3-5 days

### Medium Impact (New Features)
- ✅ Attendance system implementation
- ✅ Certificate generation system
- ✅ Notification system
- Estimated effort: 10-15 days

### Low Impact (Enhancements)
- ✅ Announcement system
- ✅ Additional columns in existing tables
- Estimated effort: 3-5 days

**Total Estimated Effort**: 16-25 working days (3-5 weeks)

---

## 🔒 Data Integrity Rules

### Enrollment Validation
```php
// All operations must validate enrollment first
$enrollment = Enrollment::where('student_id', $studentId)
    ->where('course_id', $courseId)
    ->where('status', 'active')
    ->firstOrFail();

// Then use enrollment_id for operations
$submission = Submission::create([
    'enrollment_id' => $enrollment->id,
    'assignment_id' => $assignmentId,
    // ...
]);
```

### Certificate Eligibility
```php
// Must meet all criteria
if ($enrollment->final_grade >= 60.0 && 
    $attendancePercentage >= 75.0 &&
    $assignmentCompletionRate >= 80.0 &&
    $enrollment->status === 'completed') {
    // Generate certificate
}
```

### Attendance Workflow
```php
// Auto-mark absent after deadline
if (now() > $session->deadline && $record->status === 'pending') {
    $record->update(['status' => 'absent']);
}
```

---

## 📝 Notes

1. **Snapshot vs. Live Data**: Certificates use snapshot approach to preserve data integrity of issued credentials.

2. **Enrollment as Central Hub**: All student-course operations must go through `enrollment_id` to ensure data consistency.

3. **File Management**: Each table has its own file path column instead of using a generic files table.

4. **Status Enums**: Most tables now include status fields for lifecycle management.

5. **Soft Deletes**: Consider implementing soft deletes for `enrollments` to preserve historical data.

6. **Audit Trail**: Consider adding audit logging for certificate generation, revocation, and grade changes.

---

## 🔄 Version History

| Version | Date | Description | Author |
|---------|------|-------------|--------|
| 1.0 | 2024-Q1 | Initial ERD design | Dev Team |
| 2.0 | 2024-Q4 | Major update with new features | Dev Team |

---

## 📞 Support

For questions about this migration, contact the development team.

**Last Updated**: 2024
**Document Version**: 2.0