# 📁 File Paths Quick Reference - LMS Frontend

Panduan cepat lokasi semua file yang perlu dibuat untuk LMS Frontend.

**Project Root:** `C:\Users\hansg\OneDrive\Desktop\lmsRPL3`

---

## 📋 Table of Contents
- [Parent Dashboard Files](#parent-dashboard-files)
- [Instructor Dashboard Files](#instructor-dashboard-files)
- [Student Dashboard Files](#student-dashboard-files)
- [Authentication Files](#authentication-files)
- [Folder Creation Commands](#folder-creation-commands)

---

## 👨‍👩‍👧‍👦 Parent Dashboard Files

### Layouts
```
resources/views/layouts/parent.blade.php
```

### CSS
```
public/css/parent/dashboard.css
```

### JavaScript
```
public/js/parent/api.js
```

### Views
```
resources/views/parent/dashboard.blade.php
resources/views/parent/students/index.blade.php
resources/views/parent/students/show.blade.php
resources/views/parent/grades.blade.php
resources/views/parent/attendance.blade.php
resources/views/parent/announcements.blade.php
resources/views/parent/notifications.blade.php
resources/views/parent/certificates.blade.php
```

### Folder Creation
```bash
mkdir resources/views/parent
mkdir resources/views/parent/students
mkdir public/css/parent
mkdir public/js/parent
```

---

## 👨‍🏫 Instructor Dashboard Files

### Layouts
```
resources/views/layouts/instructor.blade.php
```

### CSS
```
public/css/instructor/dashboard.css
```

### JavaScript
```
public/js/instructor/api.js
```

### Views
```
resources/views/instructor/dashboard.blade.php
resources/views/instructor/courses/index.blade.php
resources/views/instructor/courses/create.blade.php
resources/views/instructor/courses/edit.blade.php
resources/views/instructor/courses/show.blade.php
resources/views/instructor/assignments/index.blade.php
resources/views/instructor/assignments/create.blade.php
resources/views/instructor/assignments/submissions.blade.php
resources/views/instructor/grading/index.blade.php
resources/views/instructor/attendance/index.blade.php
resources/views/instructor/attendance/create.blade.php
resources/views/instructor/students/index.blade.php
resources/views/instructor/announcements/index.blade.php
resources/views/instructor/announcements/create.blade.php
resources/views/instructor/certificates/index.blade.php
```

### Folder Creation
```bash
mkdir resources/views/instructor
mkdir resources/views/instructor/courses
mkdir resources/views/instructor/assignments
mkdir resources/views/instructor/grading
mkdir resources/views/instructor/attendance
mkdir resources/views/instructor/students
mkdir resources/views/instructor/announcements
mkdir resources/views/instructor/certificates
mkdir public/css/instructor
mkdir public/js/instructor
```

---

## 🎓 Student Dashboard Files

### Layouts
```
resources/views/layouts/student.blade.php
```

### CSS
```
public/css/student/dashboard.css
```

### JavaScript
```
public/js/student/api.js
```

### Views
```
resources/views/student/dashboard.blade.php
resources/views/student/courses/index.blade.php
resources/views/student/courses/show.blade.php
resources/views/student/assignments/index.blade.php
resources/views/student/assignments/show.blade.php
resources/views/student/grades/index.blade.php
resources/views/student/attendance/index.blade.php
resources/views/student/certificates/index.blade.php
resources/views/student/profile.blade.php
resources/views/student/notifications.blade.php
```

### Folder Creation
```bash
mkdir resources/views/student
mkdir resources/views/student/courses
mkdir resources/views/student/assignments
mkdir resources/views/student/grades
mkdir resources/views/student/attendance
mkdir resources/views/student/certificates
mkdir public/css/student
mkdir public/js/student
```

---

## 🔐 Authentication Files

### Views
```
resources/views/auth/login.blade.php
resources/views/auth/register.blade.php
```

### CSS & JS
```
public/css/auth.css
public/js/auth.js
```

### Folder Creation
```bash
mkdir resources/views/auth
```

---

## 🚀 Folder Creation Commands

### Windows PowerShell (Run from project root)

```powershell
# Layouts folder
New-Item -Path "resources/views/layouts" -ItemType Directory -Force

# Auth folder
New-Item -Path "resources/views/auth" -ItemType Directory -Force

# Parent folders
New-Item -Path "resources/views/parent" -ItemType Directory -Force
New-Item -Path "resources/views/parent/students" -ItemType Directory -Force
New-Item -Path "public/css/parent" -ItemType Directory -Force
New-Item -Path "public/js/parent" -ItemType Directory -Force

# Instructor folders
New-Item -Path "resources/views/instructor" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/courses" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/grading" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/students" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/announcements" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/certificates" -ItemType Directory -Force
New-Item -Path "public/css/instructor" -ItemType Directory -Force
New-Item -Path "public/js/instructor" -ItemType Directory -Force

# Student folders
New-Item -Path "resources/views/student" -ItemType Directory -Force
New-Item -Path "resources/views/student/courses" -ItemType Directory -Force
New-Item -Path "resources/views/student/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/student/grades" -ItemType Directory -Force
New-Item -Path "resources/views/student/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/student/certificates" -ItemType Directory -Force
New-Item -Path "public/css/student" -ItemType Directory -Force
New-Item -Path "public/js/student" -ItemType Directory -Force
```

### Git Bash / Linux (Run from project root)

```bash
# Layouts folder
mkdir -p resources/views/layouts

# Auth folder
mkdir -p resources/views/auth

# Parent folders
mkdir -p resources/views/parent/students
mkdir -p public/css/parent
mkdir -p public/js/parent

# Instructor folders
mkdir -p resources/views/instructor/courses
mkdir -p resources/views/instructor/assignments
mkdir -p resources/views/instructor/grading
mkdir -p resources/views/instructor/attendance
mkdir -p resources/views/instructor/students
mkdir -p resources/views/instructor/announcements
mkdir -p resources/views/instructor/certificates
mkdir -p public/css/instructor
mkdir -p public/js/instructor

# Student folders
mkdir -p resources/views/student/courses
mkdir -p resources/views/student/assignments
mkdir -p resources/views/student/grades
mkdir -p resources/views/student/attendance
mkdir -p resources/views/student/certificates
mkdir -p public/css/student
mkdir -p public/js/student
```

---

## 📊 File Count Summary

| Dashboard Type | View Files | CSS Files | JS Files | Total |
|---------------|------------|-----------|----------|-------|
| **Parent** | 8 files | 1 file | 1 file | **10 files** |
| **Instructor** | 14 files | 1 file | 1 file | **16 files** |
| **Student** | 10 files | 1 file | 1 file | **12 files** |
| **Auth** | 2 files | 1 file | 1 file | **4 files** |
| **Layouts** | 3 files | - | - | **3 files** |
| **TOTAL** | **37 files** | **4 files** | **4 files** | **45 files** |

---

## 🎯 Implementation Map

### Phase 1: Setup Structure (5 minutes)
1. ✅ Jalankan folder creation commands
2. ✅ Verify semua folder sudah dibuat

### Phase 2: Layouts & Assets (20 minutes)
1. ✅ Create `resources/views/layouts/parent.blade.php`
2. ✅ Create `resources/views/layouts/instructor.blade.php`
3. ✅ Create `resources/views/layouts/student.blade.php`
4. ✅ Create CSS files (parent, instructor, student)
5. ✅ Create API helper JS files (parent, instructor, student)

### Phase 3: Parent Dashboard (30 minutes)
1. ✅ Create dashboard page
2. ✅ Create students pages (index, show)
3. ✅ Create other pages (grades, attendance, etc.)

### Phase 4: Instructor Dashboard (45 minutes)
1. ✅ Create dashboard page
2. ✅ Create courses pages (index, create, edit, show)
3. ✅ Create assignments pages
4. ✅ Create other pages (grading, attendance, etc.)

### Phase 5: Student Dashboard (45 minutes)
1. ✅ Create dashboard page
2. ✅ Create courses pages (index, show)
3. ✅ Create assignments pages
4. ✅ Create other pages (grades, attendance, etc.)

### Phase 6: Authentication (15 minutes)
1. ✅ Create login page
2. ✅ Create register page
3. ✅ Create auth CSS & JS

### Phase 7: Routes & Controllers (30 minutes)
1. ✅ Update `routes/web.php`
2. ✅ Create controllers (Parent, Instructor, Student, Auth)
3. ✅ Test routes

---

## 📝 Where to Copy Code From

### For Parent Dashboard:
📖 **Source:** `PARENT-DASHBOARD-GUIDE.md`  
🌐 **Interactive:** `parent-dashboard-complete.html`  
🚀 **Quick Reference:** `PARENT-DASHBOARD-QUICK-REFERENCE.md`

### For Instructor Dashboard:
📖 **Source:** `INSTRUCTOR-DASHBOARD-GUIDE.md`  
🌐 **Interactive:** `instructor-dashboard-complete.html`  
🚀 **Quick Reference:** `INSTRUCTOR-DASHBOARD-QUICK-REFERENCE.md`

### For Student Dashboard:
📖 **Source:** `STUDENT-DASHBOARD-GUIDE.md`  
🌐 **Interactive:** `student-dashboard-complete.html`  
🚀 **Quick Reference:** `STUDENT-DASHBOARD-QUICK-REFERENCE.md`

### For Authentication:
🌐 **Interactive:** `login-guide.html` & `regist-guide.html`

---

## ✅ Quick Verification Checklist

Setelah membuat semua file, verify dengan checklist ini:

### Folder Structure
```bash
# Check if folders exist (Windows PowerShell)
Test-Path "resources/views/layouts"
Test-Path "resources/views/parent"
Test-Path "resources/views/instructor"
Test-Path "resources/views/student"
Test-Path "resources/views/auth"
Test-Path "public/css/parent"
Test-Path "public/css/instructor"
Test-Path "public/css/student"
Test-Path "public/js/parent"
Test-Path "public/js/instructor"
Test-Path "public/js/student"
```

### File Count
```bash
# Count files in each folder (Windows PowerShell)
(Get-ChildItem -Path "resources/views/parent" -Recurse -File).Count  # Should be 8
(Get-ChildItem -Path "resources/views/instructor" -Recurse -File).Count  # Should be 14
(Get-ChildItem -Path "resources/views/student" -Recurse -File).Count  # Should be 10
```

---

## 🔥 Common Path Mistakes

### ❌ WRONG
```
resources/view/parent/dashboard.blade.php          # Missing 's' in views
resources/views/Parent/dashboard.blade.php         # Capital P
resources/views/parent/Dashboard.blade.php         # Capital D
public/CSS/parent/dashboard.css                    # Capital CSS
public/js/Parent/api.js                           # Capital P
```

### ✅ CORRECT
```
resources/views/parent/dashboard.blade.php
resources/views/parent/dashboard.blade.php
resources/views/parent/dashboard.blade.php
public/css/parent/dashboard.css
public/js/parent/api.js
```

**⚠️ Important:** Laravel is case-sensitive! Huruf besar/kecil harus sama persis.

---

## 🆘 Troubleshooting

### "View [parent.dashboard] not found"
**Penyebab:** File tidak ada atau path salah  
**Solusi:**
```bash
# Verify file exists
Test-Path "resources/views/parent/dashboard.blade.php"

# Check if it's really a .blade.php file
Get-ChildItem "resources/views/parent" -File
```

### "Asset not loading" (CSS/JS tidak muncul)
**Penyebab:** File tidak ada di public folder  
**Solusi:**
```bash
# Verify asset exists
Test-Path "public/css/parent/dashboard.css"
Test-Path "public/js/parent/api.js"

# Check in browser DevTools > Network tab
# Look for 404 errors
```

### "Class not found" error
**Penyebab:** Controller belum dibuat  
**Solusi:**
```bash
php artisan make:controller ParentController
php artisan make:controller InstructorController
php artisan make:controller StudentController
php artisan make:controller AuthController
```

---

## 📞 Need More Help?

1. **Lihat detail path:** `INSTALLATION-PATH-GUIDE.md`
2. **Lihat implementation:** Guide files (PARENT-DASHBOARD-GUIDE.md, etc.)
3. **Interactive guide:** Buka `.html` files di browser
4. **API reference:** Quick reference files

---

## 🎉 Ready to Start!

1. ✅ Buat semua folder dengan commands di atas
2. ✅ Copy code dari guide files ke path yang sesuai
3. ✅ Update routes di `routes/web.php`
4. ✅ Buat controllers dengan artisan
5. ✅ Test di browser!

**Happy Coding! 🚀**