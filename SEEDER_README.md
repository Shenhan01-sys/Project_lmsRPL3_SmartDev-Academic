# 🌱 Database Seeder Documentation

## Overview

This document provides comprehensive instructions for seeding the SmartDev LMS database with dummy data. The seeders create realistic test data for all tables in the system.

---

## 📋 Prerequisites

Before running the seeders, ensure:

1. ✅ Database is configured in `.env` file
2. ✅ All migrations have been run: `php artisan migrate`
3. ✅ Composer dependencies are installed: `composer install`

---

## 🚀 Quick Start

### Run All Seeders

To seed the entire database with dummy data:

```bash
php artisan db:seed
```

Or if you need to refresh the database:

```bash
php artisan migrate:fresh --seed
```

---

## 📊 What Gets Seeded

The seeder will create the following data:

| Table | Count | Description |
|-------|-------|-------------|
| **Users** | ~70+ | Admin, instructors, students, parents, calon siswa |
| **Parents** | 20 | Parent profiles with occupations |
| **Instructors** | 10 | Instructor profiles with specializations |
| **Students** | 50 | Student profiles linked to parents |
| **Student Registrations** | 10 | Calon siswa with various registration statuses |
| **Courses** | 23 | Various subjects (Math, Science, Languages, etc.) |
| **Course Modules** | ~115+ | 5 modules per course |
| **Materials** | ~345+ | 2-4 materials per module |
| **Enrollments** | ~200+ | Students enrolled in 3-6 courses each |
| **Assignments** | ~92+ | 3-5 assignments per course |
| **Submissions** | ~500+ | Student submissions with various statuses |
| **Grade Components** | Varies | UTS, UAS, Tugas, Quiz, Praktik per course |
| **Grades** | ~1000+ | Grades for enrolled students |
| **Attendance Sessions** | ~230+ | 6-10 sessions per course |
| **Attendance Records** | ~2000+ | Attendance records for students |
| **Announcements** | ~100+ | Global and course-specific announcements |
| **Notifications** | ~700+ | User notifications of various types |
| **Certificates** | 0 | Not seeded (as per requirement) |

---

## 🔐 Default Login Credentials

### Admin Account
```
📧 Email: admin@example.com
🔒 Password: password
```

### Instructor Accounts
```
📧 Email: ahmad.hidayat@school.com (and 9 others)
🔒 Password: password123
```

### Student Accounts
```
📧 Email: ahmad.fauzi@student.com (and 49 others)
🔒 Password: password123
```

### Parent Accounts
```
📧 Email: bambang.suryanto@parent.com (and 19 others)
🔒 Password: password123
```

### Calon Siswa (Prospective Students)
```
📧 Email: ahmad.fauzi@example.com
📧 Email: siti.nur@example.com
📧 Email: budi.santoso@example.com
(and 7 others)
🔒 Password: password123
```

---

## 🎯 Seeder Execution Order

The seeders run in this specific order to maintain referential integrity:

1. **Admin User** - Creates admin account if not exists
2. **ParentSeeder** - Creates parent profiles
3. **InstructorSeeder** - Creates instructor profiles
4. **StudentSeeder** - Creates student profiles
5. **StudentRegistrationSeeder** - Creates registration records
6. **CourseSeeder** - Creates courses
7. **CourseModuleSeeder** - Creates course modules
8. **MaterialSeeder** - Creates learning materials
9. **EnrollmentSeeder** - Enrolls students in courses
10. **AssignmentSeeder** - Creates assignments
11. **SubmissionSeeder** - Creates student submissions
12. **GradeComponentSeeder** - Creates grade components
13. **GradeSeeder** - Creates grades
14. **AttendanceSeeder** - Creates attendance sessions & records
15. **AnnouncementSeeder** - Creates announcements
16. **NotificationSeeder** - Creates notifications

---

## 🎲 Data Characteristics

### Student Registrations
- **Pending Documents**: 2 users (just registered, no documents)
- **Pending Approval**: 4 users (documents uploaded, waiting admin)
- **Approved**: 3 users (converted to student role)
- **Rejected**: 1 user (rejected with notes)

### Enrollments
- **Active**: 70% of enrollments
- **Completed**: 20% of enrollments
- **Dropped**: 10% of enrollments

### Submissions
- **Graded**: 50% (with scores and feedback)
- **Submitted**: 30% (waiting for grading)
- **Late**: 15% (submitted after deadline)
- **Pending**: 5% (not yet submitted)

### Grades
- **Excellent (90-100)**: 20%
- **Good (80-89)**: 30%
- **Average (70-79)**: 30%
- **Below Average (60-69)**: 15%
- **Poor (50-59)**: 5%

### Attendance
- **Present**: 75%
- **Absent**: 15%
- **Sick**: 6%
- **Permission**: 4%

### Announcements
- **Global**: 8 announcements (from admin)
- **Course-specific**: ~3-6 per course (from instructors)
- **Published**: 80%
- **Draft**: 20%

### Notifications
- **Read**: 60%
- **Unread**: 40%
- Types: assignment, grade, announcement, attendance, course updates, etc.

---

## 🔄 Running Individual Seeders

If you need to run specific seeders:

```bash
# Seed only parents
php artisan db:seed --class=ParentSeeder

# Seed only instructors
php artisan db:seed --class=InstructorSeeder

# Seed only students
php artisan db:seed --class=StudentSeeder

# Seed only courses
php artisan db:seed --class=CourseSeeder

# Seed student registrations
php artisan db:seed --class=StudentRegistrationSeeder

# Seed enrollments
php artisan db:seed --class=EnrollmentSeeder

# Seed assignments
php artisan db:seed --class=AssignmentSeeder

# Seed submissions
php artisan db:seed --class=SubmissionSeeder

# Seed attendance
php artisan db:seed --class=AttendanceSeeder

# Seed announcements
php artisan db:seed --class=AnnouncementSeeder

# Seed notifications
php artisan db:seed --class=NotificationSeeder

# Seed grade components
php artisan db:seed --class=GradeComponentSeeder

# Seed grades
php artisan db:seed --class=GradeSeeder
```

---

## ⚠️ Important Notes

1. **Admin Account**: If an admin account with email `admin@example.com` already exists, the seeder will skip creating it.

2. **Certificates**: Certificate table is NOT seeded as per requirement. Certificates are generated dynamically when students complete courses.

3. **File Paths**: Seeded data includes fake file paths (e.g., `submissions/xxx.pdf`). These are dummy paths for testing purposes. In production, actual files would be uploaded.

4. **Dependencies**: Some seeders depend on others (e.g., EnrollmentSeeder requires StudentSeeder and CourseSeeder to run first).

5. **Randomization**: Data is randomized on each run, so you'll get different data distributions each time.

---

## 🧹 Resetting the Database

To completely reset and re-seed the database:

```bash
# Option 1: Drop all tables, re-run migrations, and seed
php artisan migrate:fresh --seed

# Option 2: Drop and recreate database (if you have permission)
php artisan db:wipe
php artisan migrate
php artisan db:seed
```

---

## 🎨 Course Categories

The seeder creates courses in the following categories:

- **Matematika**: Matematika Dasar, Matematika Lanjutan
- **Sains**: Fisika Dasar, Fisika Modern, Kimia Dasar, Kimia Organik, Biologi Umum, Biologi Molekuler
- **Bahasa**: Bahasa Indonesia, Sastra Indonesia, English Foundation, Advanced English
- **Sosial**: Sejarah Indonesia, Sejarah Dunia, Geografi, Ekonomi Dasar, Ekonomi Makro
- **Teknologi**: Pemrograman Dasar, Web Development
- **Seni**: Seni Rupa, Seni Musik
- **Olahraga**: Pendidikan Jasmani

Each course has:
- Unique course code (e.g., MTK101, FIS201)
- 5 modules with realistic titles
- 2-4 materials per module (documents, videos, presentations, links)
- 3-5 assignments with various types (quiz, essay, project, presentation)
- Attendance sessions
- Grade components

---

## 📈 Testing Scenarios

The seeded data supports testing various scenarios:

### For Students:
✅ View enrolled courses
✅ Access course materials
✅ Submit assignments
✅ Check grades
✅ View attendance records
✅ Read announcements
✅ Receive notifications

### For Instructors:
✅ Create and manage courses
✅ Grade student submissions
✅ Mark attendance
✅ Post announcements
✅ View student progress

### For Parents:
✅ View children's grades
✅ Monitor attendance
✅ Check course enrollment

### For Admin:
✅ Approve/reject student registrations
✅ Manage users
✅ Post global announcements
✅ View system-wide statistics

### Registration Flow:
✅ Calon siswa registers
✅ Upload documents
✅ Admin reviews
✅ Approve → becomes student
✅ Reject → remains calon siswa

---

## 🛠️ Troubleshooting

### Issue: Foreign key constraint fails
**Solution**: Make sure migrations are run before seeding:
```bash
php artisan migrate
php artisan db:seed
```

### Issue: "No instructors found"
**Solution**: Seeders run in order. If you run individual seeders, run prerequisites first:
```bash
php artisan db:seed --class=InstructorSeeder
php artisan db:seed --class=CourseSeeder
```

### Issue: Database already has data
**Solution**: Use fresh migration:
```bash
php artisan migrate:fresh --seed
```

### Issue: Out of memory
**Solution**: Increase PHP memory limit in `php.ini`:
```
memory_limit = 512M
```

---

## 📝 Customization

To customize the seeded data, edit the seeder files in `database/seeders/`:

- `DatabaseSeeder.php` - Main seeder orchestration
- `StudentSeeder.php` - Modify student count/names
- `CourseSeeder.php` - Add/remove courses
- `AssignmentSeeder.php` - Change assignment types
- etc.

After editing, run:
```bash
php artisan migrate:fresh --seed
```

---

## 🎉 Success!

After running the seeders, your database will be populated with realistic test data ready for development and testing!

Check the seeding summary in the console output to verify all data was created successfully.

---

## 📞 Support

If you encounter issues:
1. Check this documentation
2. Review seeder files in `database/seeders/`
3. Check migration files in `database/migrations/`
4. Review the database schema

For questions, contact the development team.

---

**Last Updated**: January 2025
**Version**: 1.0
**Author**: SmartDev LMS Development Team