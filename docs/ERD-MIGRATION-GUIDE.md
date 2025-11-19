# ERD Migration Guide - SmartDev Academic LMS
## Version 1.0 → Version 2.0

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Pre-Migration Checklist](#pre-migration-checklist)
3. [Migration Strategy](#migration-strategy)
4. [Step-by-Step Migration](#step-by-step-migration)
5. [Laravel Migration Files](#laravel-migration-files)
6. [Model Updates](#model-updates)
7. [Controller Updates](#controller-updates)
8. [Validation & Testing](#validation--testing)
9. [Rollback Procedures](#rollback-procedures)
10. [Post-Migration Tasks](#post-migration-tasks)

---

## 📋 Overview

### What's Changing?

This guide covers the migration from ERD v1.0 to v2.0, which includes:

**New Features** (5 new tables):
- ✅ Attendance System (`attendance_sessions`, `attendance_records`)
- ✅ Announcement System (`announcements`)
- ✅ Notification System (`notifications`)
- ✅ Certificate System (`certificates`)

**Breaking Changes** (2 tables refactored):
- ⚠️ `submissions`: `student_id` → `enrollment_id`
- ⚠️ `grades`: `student_id` → `enrollment_id`

**Enhancements** (10+ tables):
- Additional columns for better functionality
- Status fields for lifecycle management
- File path columns for document storage

### Timeline Estimate

| Phase | Duration | Complexity |
|-------|----------|------------|
| Preparation | 1-2 days | Low |
| New Tables | 1 day | Low |
| Refactoring | 2-3 days | High |
| Code Updates | 5-7 days | Medium |
| Testing | 2-3 days | Medium |
| Deployment | 1 day | Medium |
| **Total** | **12-17 days** | **Medium-High** |

### Risk Assessment

| Risk | Level | Mitigation |
|------|-------|------------|
| Data Loss | HIGH | Full backup + staging test |
| Downtime | MEDIUM | Schedule maintenance window |
| API Breaking | HIGH | Version API endpoints |
| Performance | LOW | Add indexes proactively |

---

## 🔍 Pre-Migration Checklist

### 1. Environment Preparation

```bash
# ✅ Backup Database
mysqldump -u root -p lms_database > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql

# ✅ Backup Application Files
tar -czf backup_app_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/lmsRPL3

# ✅ Setup Staging Environment
cp .env .env.backup
cp .env.example .env.staging
# Configure staging database

# ✅ Check Database Connection
php artisan db:show
php artisan migrate:status
```

### 2. Dependencies Check

```bash
# ✅ Verify Laravel Version
php artisan --version
# Required: Laravel 10.x or higher

# ✅ Check PHP Version
php -v
# Required: PHP 8.1 or higher

# ✅ Check Database Version
mysql --version
# Required: MySQL 8.0+ or MariaDB 10.3+

# ✅ Install Required Packages
composer require barryvdh/laravel-dompdf
composer require simplesoftwareio/simple-qrcode
```

### 3. Code Review Checklist

- [ ] All controllers using `Student::find()` for submissions
- [ ] All controllers using `Student::find()` for grades
- [ ] All API endpoints returning student_id in responses
- [ ] All frontend components expecting student_id
- [ ] All validation rules using student_id
- [ ] All test cases using student_id

### 4. Communication Plan

- [ ] Notify all stakeholders about migration
- [ ] Schedule maintenance window (recommended: weekend)
- [ ] Prepare rollback communication
- [ ] Create status page for migration progress

---

## 🎯 Migration Strategy

### Approach: Zero-Downtime Migration (Recommended)

We'll use a **phased approach** to minimize downtime:

1. **Phase 1**: Add new tables (no downtime)
2. **Phase 2**: Add new columns to existing tables (no downtime)
3. **Phase 3**: Migrate data (short downtime)
4. **Phase 4**: Update application code (deploy with feature flags)
5. **Phase 5**: Remove old columns (scheduled maintenance)

### Alternative: Maintenance Window Approach

If zero-downtime is not critical:
- Schedule 4-6 hour maintenance window
- Run all migrations at once
- Deploy updated code
- Verify and resume

---

## 📝 Step-by-Step Migration

### PHASE 1: Create New Tables

#### Step 1.1: Attendance Sessions Table

```bash
php artisan make:migration create_attendance_sessions_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('session_name');
            $table->date('session_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->dateTime('deadline');
            $table->enum('status', ['scheduled', 'open', 'closed'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('session_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
```

#### Step 1.2: Attendance Records Table

```bash
php artisan make:migration create_attendance_records_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_session_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'present', 'sick', 'permission', 'absent'])->default('pending');
            $table->timestamp('check_in_time')->nullable();
            $table->enum('check_in_method', ['manual', 'qr', 'location'])->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->string('supporting_doc_path')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            // Unique constraint: one record per enrollment per session
            $table->unique(['enrollment_id', 'attendance_session_id'], 'unique_attendance');

            // Indexes
            $table->index('enrollment_id');
            $table->index('attendance_session_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
```

#### Step 1.3: Announcements Table

```bash
php artisan make:migration create_announcements_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('announcement_type', ['global', 'course'])->default('global');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('pinned')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('status');
            $table->index('announcement_type');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
```

#### Step 1.4: Notifications Table

```bash
php artisan make:migration create_notifications_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('related_entity_type')->nullable();
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_user_unread');
            $table->index('notification_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

#### Step 1.5: Certificates Table

```bash
php artisan make:migration create_certificates_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('certificate_code')->unique();
            $table->string('certificate_file_path');
            $table->decimal('final_grade', 5, 2);
            $table->decimal('attendance_percentage', 5, 2);
            $table->decimal('assignment_completion_rate', 5, 2);
            $table->char('grade_letter', 2)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['issued', 'revoked', 'expired'])->default('issued');
            $table->text('revocation_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->integer('verification_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('certificate_code');
            $table->index('enrollment_id');
            $table->index('course_id');
            $table->index('status');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
```

#### Step 1.6: Run New Tables Migration

```bash
# Test in staging first
php artisan migrate --pretend

# Run actual migration
php artisan migrate

# Verify tables created
php artisan db:show
```

---

### PHASE 2: Enhance Existing Tables

#### Step 2.1: Add Columns to Users Table

```bash
php artisan make:migration add_photo_path_to_users_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
```

#### Step 2.2: Enhance Enrollments Table

```bash
php artisan make:migration enhance_enrollments_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->nullable()->after('course_id');
            $table->enum('status', ['active', 'completed', 'dropped', 'failed'])->default('active')->after('enrollment_date');
            $table->decimal('final_grade', 5, 2)->nullable()->after('status');

            // Indexes
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['enrollment_date', 'status', 'final_grade']);
        });
    }
};
```

#### Step 2.3: Enhance Courses Table

```bash
php artisan make:migration enhance_courses_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('credits')->nullable()->after('description');
            $table->integer('max_students')->nullable()->after('credits');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('max_students');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['credits', 'max_students', 'status']);
        });
    }
};
```

#### Step 2.4: Enhance Materials Table

```bash
php artisan make:migration enhance_materials_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->bigInteger('file_size')->nullable()->after('file_type');
            $table->integer('download_count')->default(0)->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['file_size', 'download_count']);
        });
    }
};
```

#### Step 2.5: Enhance Assignments Table

```bash
php artisan make:migration enhance_assignments_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->decimal('max_score', 8, 2)->default(100)->after('due_date');
            $table->enum('status', ['draft', 'published', 'closed'])->default('published')->after('max_score');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['max_score', 'status']);
        });
    }
};
```

---

### PHASE 3: Refactor Submissions Table ⚠️ BREAKING CHANGE

#### Step 3.1: Add enrollment_id Column

```bash
php artisan make:migration add_enrollment_id_to_submissions_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Add new column (nullable first for data migration)
            $table->foreignId('enrollment_id')->nullable()->after('id');
            
            // Add new columns
            $table->dateTime('submission_date')->nullable()->after('assignment_id');
            $table->enum('status', ['submitted', 'graded', 'late', 'resubmit'])->default('submitted')->after('file_path');
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null')->after('feedback');

            // Indexes
            $table->index('enrollment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['enrollment_id', 'submission_date', 'status', 'graded_by']);
        });
    }
};
```

#### Step 3.2: Migrate Data from student_id to enrollment_id

```bash
php artisan make:migration migrate_submissions_student_to_enrollment
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Populate enrollment_id based on student_id + course_id
        DB::statement("
            UPDATE submissions s
            JOIN assignments a ON s.assignment_id = a.id
            JOIN enrollments e ON e.student_id = s.student_id AND e.course_id = a.course_id
            SET s.enrollment_id = e.id
            WHERE s.enrollment_id IS NULL
        ");

        // Check for orphaned records (submissions without valid enrollment)
        $orphaned = DB::table('submissions')
            ->whereNull('enrollment_id')
            ->count();

        if ($orphaned > 0) {
            throw new \Exception("Found {$orphaned} submissions without valid enrollment. Please fix manually before proceeding.");
        }
    }

    public function down(): void
    {
        // Set enrollment_id back to null (data restoration would require manual intervention)
        DB::table('submissions')->update(['enrollment_id' => null]);
    }
};
```

#### Step 3.3: Drop student_id Column

```bash
php artisan make:migration drop_student_id_from_submissions_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Make enrollment_id NOT NULL
            $table->foreignId('enrollment_id')->nullable(false)->change();
            
            // Add foreign key constraint
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            
            // Add unique constraint
            $table->unique(['enrollment_id', 'assignment_id'], 'unique_submission');
            
            // Drop old student_id column
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Restore student_id column (requires manual data restoration)
            $table->foreignId('student_id')->nullable()->after('id');
            
            // Drop unique constraint
            $table->dropUnique('unique_submission');
            
            // Make enrollment_id nullable
            $table->foreignId('enrollment_id')->nullable()->change();
        });
    }
};
```

---

### PHASE 4: Refactor Grades Table ⚠️ BREAKING CHANGE

#### Step 4.1: Add enrollment_id Column

```bash
php artisan make:migration add_enrollment_id_to_grades_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Add new column (nullable first for data migration)
            $table->foreignId('enrollment_id')->nullable()->after('id');
            
            // Add index
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('enrollment_id');
        });
    }
};
```

#### Step 4.2: Migrate Data from student_id to enrollment_id

```bash
php artisan make:migration migrate_grades_student_to_enrollment
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Populate enrollment_id based on student_id + course_id
        DB::statement("
            UPDATE grades g
            JOIN grade_components gc ON g.grade_component_id = gc.id
            JOIN enrollments e ON e.student_id = g.student_id AND e.course_id = gc.course_id
            SET g.enrollment_id = e.id
            WHERE g.enrollment_id IS NULL
        ");

        // Check for orphaned records
        $orphaned = DB::table('grades')
            ->whereNull('enrollment_id')
            ->count();

        if ($orphaned > 0) {
            throw new \Exception("Found {$orphaned} grades without valid enrollment. Please fix manually before proceeding.");
        }
    }

    public function down(): void
    {
        DB::table('grades')->update(['enrollment_id' => null]);
    }
};
```

#### Step 4.3: Drop student_id Column

```bash
php artisan make:migration drop_student_id_from_grades_table
```

**Migration File**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Make enrollment_id NOT NULL
            $table->foreignId('enrollment_id')->nullable(false)->change();
            
            // Add foreign key constraint
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            
            // Add unique constraint
            $table->unique(['enrollment_id', 'grade_component_id'], 'unique_grade');
            
            // Drop old student_id column
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Restore student_id column
            $table->foreignId('student_id')->nullable()->after('id');
            
            // Drop unique constraint
            $table->dropUnique('unique_grade');
            
            // Make enrollment_id nullable
            $table->foreignId('enrollment_id')->nullable()->change();
        });
    }
};
```

---

## 🔧 Model Updates

### New Models to Create

#### AttendanceSession Model

```bash
php artisan make:model AttendanceSession
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'session_name',
        'session_date',
        'start_time',
        'end_time',
        'deadline',
        'status',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'deadline' => 'datetime',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
```

#### AttendanceRecord Model

```bash
php artisan make:model AttendanceRecord
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'attendance_session_id',
        'status',
        'check_in_time',
        'check_in_method',
        'latitude',
        'longitude',
        'notes',
        'supporting_doc_path',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
```

#### Certificate Model

```bash
php artisan make:model Certificate
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'certificate_code',
        'certificate_file_path',
        'final_grade',
        'attendance_percentage',
        'assignment_completion_rate',
        'grade_letter',
        'issue_date',
        'expiry_date',
        'generated_by',
        'status',
        'revocation_reason',
        'revoked_at',
        'verification_count',
        'metadata',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Scopes
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    // Methods
    public function incrementVerificationCount(): void
    {
        $this->increment('verification_count');
    }
}
```

### Update Existing Models

#### Submission Model (Updated)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',  // ⚠️ CHANGED from student_id
        'assignment_id',
        'submission_date',
        'file_path',
        'submission_text',
        'status',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function enrollment(): BelongsTo  // ⚠️ NEW
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Helper to get student (through enrollment)
    public function student()
    {
        return $this->enrollment->student;
    }
}
```

#### Grade Model (Updated)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',  // ⚠️ CHANGED from student_id
        'grade_component_id',
        'score',
        'max_score',
        'notes',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function enrollment(): BelongsTo  // ⚠️ NEW
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradeComponent(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Helper to get student (through enrollment)
    public function student()
    {
        return $this->enrollment->student;
    }
}
```

#### Enrollment Model (Updated)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_date',
        'status',
        'final_grade',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany  // ⚠️ NEW
    {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasMany  // ⚠️ NEW
    {
        return $this->hasMany(Grade::class);
    }

    public function attendanceRecords(): HasMany  // ⚠️ NEW
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function certificates(): HasMany  // ⚠️ NEW
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

---

## 🎮 Controller Updates

### Example: SubmissionController (Updated)

**Before**:
```php
public function store(Request $request, Assignment $assignment)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'file' => 'required|file|max:10240',
    ]);

    $submission = Submission::create([
        'student_id' => $validated['student_id'],
        'assignment_id' => $assignment->id,
        'file_path' => $request->file('file')->store('submissions'),
    ]);

    return response()->json($submission);
}
```

**After**:
```php
public function store(Request $request, Assignment $assignment)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'file' => 'required|file|max:10240',
    ]);

    // ⚠️ CHANGED: Get enrollment first
    $enrollment = Enrollment::where('student_id', $validated['student_id'])
        ->where('course_id', $assignment->course_id)
        ->where('status', 'active')
        ->firstOrFail();

    $submission = Submission::create([
        'enrollment_id' => $enrollment->id,  // ⚠️ CHANGED
        'assignment_id' => $assignment->id,
        'submission_date' => now(),
        'file_path' => $request->file('file')->store('submissions'),
        'status' => 'submitted',
    ]);

    return response()->json($submission->load('enrollment.student'));
}
```

---

## ✅ Validation & Testing

### Data Integrity Checks

```php
// Check for orphaned submissions
$orphanedSubmissions = DB::table('submissions')
    ->leftJoin('enrollments', 'submissions.enrollment_id', '=', 'enrollments.id')
    ->whereNull('enrollments.id')
    ->count();

// Check for orphaned grades
$orphanedGrades = DB::table('grades')
    ->leftJoin('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
    ->whereNull('enrollments.id')
    ->count();

// Check unique constraints
$duplicateSubmissions = DB::table('submissions')
    ->select('enrollment_id', 'assignment_id', DB::raw('COUNT(*) as count'))
    ->groupBy('enrollment_id', 'assignment_id')
    ->having('count', '>', 1)
    ->get();
```

### Test Cases

```php
// Test enrollment validation
public function test_cannot_submit_without_enrollment()
{
    $student = Student::factory()->create();
    $assignment = Assignment::factory()->create();

    $response = $this->actingAs($student->user)
        ->postJson("/api/assignments/{$assignment->id}/submit", [
            'file' => UploadedFile::fake()->create('document.pdf')
        ]);

    $response->assertStatus(403);
}

// Test certificate generation
public function test_certificate_generation_requires_eligibility()
{
    $enrollment = Enrollment::factory()->create([
        'final_grade' => 55,  // Below threshold
    ]);

    $response = $this->actingAs($enrollment->course->instructor->user)
        ->postJson("/api/enrollments/{$enrollment->id}/certificate");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['eligibility']);
}
```

---

## 🔙 Rollback Procedures

### Emergency Rollback

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Restore database from backup
mysql -u root -p lms_database < backup_before_migration.sql

# 3. Restore application code
git checkout previous-stable-tag
composer install --no-dev
php artisan config:cache
php artisan route:cache

# 4. Bring application back up
php artisan up
```

### Selective Migration Rollback

```bash
# Rollback last migration
php artisan migrate:rollback

# Rollback specific batch
php artisan migrate:rollback --batch=5

# Rollback all migrations (DANGEROUS!)
php artisan migrate:reset
```

---

## 📊 Post-Migration Tasks

### 1. Verify Data Integrity

```bash
php artisan db:show
php artisan migrate:status

# Run custom verification script
php artisan app:verify-migration
```

### 2. Update API Documentation

- Update Postman collection
- Update OpenAPI/Swagger specs
- Update frontend API calls

### 3. Monitor Performance

```bash
# Enable query logging
DB::enableQueryLog();

# Check slow queries
php artisan telescope:prune
```

### 4. User Communication

- Send email to all users about new features
- Update help documentation
- Create video tutorials

---

## 📝 Final Checklist

- [ ] Database backup completed
- [ ] Staging environment tested
- [ ] All migrations run successfully
- [ ] No orphaned records found
- [ ] All models updated
- [ ] All controllers updated
- [ ] All tests passing
- [ ] API documentation updated
- [ ] Frontend code updated
- [ ] Performance metrics acceptable
- [ ] Rollback plan tested
- [ ] Users notified
- [ ] Documentation updated

---

## 🆘 Support & Troubleshooting

### Common Issues

**Issue**: Foreign key constraint fails
```bash
# Solution: Check for orphaned records first
SELECT * FROM submissions WHERE enrollment_id NOT IN (SELECT id FROM enrollments);
```

**Issue**: Unique constraint violation
```bash
# Solution: Find duplicates
SELECT enrollment_id, assignment_id, COUNT(*)
FROM submissions
GROUP BY enrollment_id, assignment_id
HAVING COUNT(*) > 1;
```

---

**Document Version**: 1.0  
**Last Updated**: 2024  
**Maintained By**: Development Team