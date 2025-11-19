# 🚀 Quick Seeder Commands Reference

## One-Line Commands

### Fresh Start (Recommended)
```bash
# Drop all tables, run migrations, and seed database
php artisan migrate:fresh --seed
```

### Seed Only (Keep Existing Data)
```bash
# Add seed data to existing database
php artisan db:seed
```

### Force Production Seed
```bash
# Force seeding in production environment
php artisan db:seed --force
```

---

## Individual Seeder Commands

### Run Specific Seeder
```bash
php artisan db:seed --class=ParentSeeder
php artisan db:seed --class=InstructorSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentRegistrationSeeder
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=CourseModuleSeeder
php artisan db:seed --class=MaterialSeeder
php artisan db:seed --class=EnrollmentSeeder
php artisan db:seed --class=AssignmentSeeder
php artisan db:seed --class=SubmissionSeeder
php artisan db:seed --class=GradeComponentSeeder
php artisan db:seed --class=GradeSeeder
php artisan db:seed --class=AttendanceSeeder
php artisan db:seed --class=AnnouncementSeeder
php artisan db:seed --class=NotificationSeeder
```

---

## Database Reset Commands

### Complete Reset
```bash
# Drop all tables and re-migrate (WARNING: Deletes all data)
php artisan migrate:fresh

# Drop, migrate, and seed
php artisan migrate:fresh --seed
```

### Rollback and Re-run
```bash
# Rollback all migrations
php artisan migrate:reset

# Run migrations again
php artisan migrate

# Seed database
php artisan db:seed
```

### Wipe Database
```bash
# Wipe database (drops all tables, views, types)
php artisan db:wipe

# Then migrate and seed
php artisan migrate
php artisan db:seed
```

---

## Check Commands

### List Seeders
```bash
# View all available seeders
php artisan db:seed --help
```

### Check Database Status
```bash
# Show migration status
php artisan migrate:status
```

### Inspect Database
```bash
# Connect to database CLI
php artisan db

# Or use tinker to query
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Course::count()
>>> \App\Models\Student::count()
```

---

## Troubleshooting Commands

### Clear Caches Before Seeding
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan migrate:fresh --seed
```

### Optimize After Seeding
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
```

---

## Testing Specific Scenarios

### Seed Only User-Related Tables
```bash
php artisan db:seed --class=ParentSeeder
php artisan db:seed --class=InstructorSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentRegistrationSeeder
```

### Seed Only Course-Related Tables
```bash
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=CourseModuleSeeder
php artisan db:seed --class=MaterialSeeder
php artisan db:seed --class=AssignmentSeeder
```

### Seed Only Activity Data
```bash
php artisan db:seed --class=EnrollmentSeeder
php artisan db:seed --class=SubmissionSeeder
php artisan db:seed --class=AttendanceSeeder
php artisan db:seed --class=AnnouncementSeeder
php artisan db:seed --class=NotificationSeeder
```

---

## Production-Safe Commands

### Backup Before Seeding
```bash
# Create backup first (if you have mysqldump)
mysqldump -u username -p database_name > backup.sql

# Then seed
php artisan db:seed --force
```

### Seed with Specific Database
```bash
# Seed specific database connection
php artisan db:seed --database=mysql
php artisan db:seed --database=testing
```

---

## Useful Aliases (Add to .bashrc or .zshrc)

```bash
# Fresh migration and seed
alias fresh="php artisan migrate:fresh --seed"

# Seed database
alias seed="php artisan db:seed"

# Clear all caches
alias clear-all="php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"

# Fresh start
alias fresh-start="php artisan config:clear && php artisan migrate:fresh --seed"
```

---

## Quick Verification Queries

### After Seeding, Check Counts
```bash
php artisan tinker
```

Then in Tinker:
```php
// Check user counts by role
\App\Models\User::where('role', 'admin')->count()
\App\Models\User::where('role', 'instructor')->count()
\App\Models\User::where('role', 'student')->count()
\App\Models\User::where('role', 'parent')->count()
\App\Models\User::where('role', 'calon_siswa')->count()

// Check other tables
\App\Models\Course::count()
\App\Models\Enrollment::count()
\App\Models\Assignment::count()
\App\Models\Submission::count()
\App\Models\Attendance::count()
\App\Models\Announcement::count()

// Check relationships
\App\Models\Course::first()->enrollments()->count()
\App\Models\Student::first()->enrollments()->count()
```

---

## Error Recovery

### If Seeding Fails Halfway
```bash
# Clear database and try again
php artisan migrate:fresh
php artisan db:seed
```

### If Foreign Key Errors
```bash
# Make sure migrations are run first
php artisan migrate:status
php artisan migrate
php artisan db:seed
```

### If Memory Errors
```bash
# Increase memory limit temporarily
php -d memory_limit=512M artisan db:seed
```

---

## Development Workflow

### 1. Initial Setup
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

### 2. Daily Development
```bash
# If you modify seeders
php artisan migrate:fresh --seed
```

### 3. Before Committing
```bash
# Test clean migration
php artisan migrate:fresh --seed
# Verify all data is correct
```

---

## Quick Login Credentials

### Admin
```
Email: admin@example.com
Password: password
```

### Sample Instructor
```
Email: ahmad.hidayat@school.com
Password: password123
```

### Sample Student
```
Email: ahmad.fauzi@student.com
Password: password123
```

### Sample Parent
```
Email: bambang.suryanto@parent.com
Password: password123
```

### Sample Calon Siswa
```
Email: ahmad.fauzi@example.com
Password: password123
```

---

## Time Estimates

| Command | Time | Records |
|---------|------|---------|
| `migrate:fresh` | ~5s | 0 |
| `migrate:fresh --seed` | ~45s | ~5,500 |
| `db:seed` | ~40s | ~5,500 |
| Individual seeder | ~1-5s | Varies |

---

## Best Practices

✅ **DO**:
- Run `migrate:fresh --seed` when starting fresh
- Backup production before seeding
- Test seeders in development first
- Use `--force` flag in production

❌ **DON'T**:
- Seed production without backup
- Run seeders on live user data
- Forget to clear cache before seeding
- Mix old and new data without reset

---

## Emergency Reset

```bash
# Nuclear option - complete reset
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed
php artisan optimize
```

---

**Last Updated**: January 2025
**Quick Reference for**: SmartDev LMS Database Seeding