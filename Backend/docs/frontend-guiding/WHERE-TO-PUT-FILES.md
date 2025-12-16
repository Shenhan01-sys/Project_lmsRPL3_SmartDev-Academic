# 📁 WHERE TO PUT FILES - Visual Guide

**SIMPEL & PRAKTIS! Panduan dimana meletakkan setiap file yang kamu buat.**

---

## 🎯 Quick Rule

```
Project Root: C:\Users\hansg\OneDrive\Desktop\lmsRPL3

Semua path di bawah ini RELATIF dari project root.
```

---

## 👨‍👩‍👧‍👦 PARENT DASHBOARD

### ✅ 3 File Utama

| # | Apa | Taruh Dimana |
|---|-----|--------------|
| 1 | **Layout Template** | `resources/views/layouts/parent.blade.php` |
| 2 | **CSS Styling** | `public/css/parent/dashboard.css` |
| 3 | **API Helper JS** | `public/js/parent/api.js` |

### ✅ 8 File View (Blade)

| # | Halaman | Taruh Dimana |
|---|---------|--------------|
| 1 | Dashboard | `resources/views/parent/dashboard.blade.php` |
| 2 | Students List | `resources/views/parent/students/index.blade.php` |
| 3 | Student Detail | `resources/views/parent/students/show.blade.php` |
| 4 | Grades | `resources/views/parent/grades.blade.php` |
| 5 | Attendance | `resources/views/parent/attendance.blade.php` |
| 6 | Announcements | `resources/views/parent/announcements.blade.php` |
| 7 | Notifications | `resources/views/parent/notifications.blade.php` |
| 8 | Certificates | `resources/views/parent/certificates.blade.php` |

### 📂 Folder yang Perlu Dibuat

```bash
mkdir resources/views/parent
mkdir resources/views/parent/students
mkdir public/css/parent
mkdir public/js/parent
```

---

## 👨‍🏫 INSTRUCTOR DASHBOARD

### ✅ 3 File Utama

| # | Apa | Taruh Dimana |
|---|-----|--------------|
| 1 | **Layout Template** | `resources/views/layouts/instructor.blade.php` |
| 2 | **CSS Styling** | `public/css/instructor/dashboard.css` |
| 3 | **API Helper JS** | `public/js/instructor/api.js` |

### ✅ 14 File View (Blade)

| # | Halaman | Taruh Dimana |
|---|---------|--------------|
| 1 | Dashboard | `resources/views/instructor/dashboard.blade.php` |
| 2 | Courses List | `resources/views/instructor/courses/index.blade.php` |
| 3 | Create Course | `resources/views/instructor/courses/create.blade.php` |
| 4 | Edit Course | `resources/views/instructor/courses/edit.blade.php` |
| 5 | Course Detail | `resources/views/instructor/courses/show.blade.php` |
| 6 | Assignments List | `resources/views/instructor/assignments/index.blade.php` |
| 7 | Create Assignment | `resources/views/instructor/assignments/create.blade.php` |
| 8 | View Submissions | `resources/views/instructor/assignments/submissions.blade.php` |
| 9 | Grading | `resources/views/instructor/grading/index.blade.php` |
| 10 | Attendance List | `resources/views/instructor/attendance/index.blade.php` |
| 11 | Create Attendance | `resources/views/instructor/attendance/create.blade.php` |
| 12 | Students | `resources/views/instructor/students/index.blade.php` |
| 13 | Announcements | `resources/views/instructor/announcements/index.blade.php` |
| 14 | Create Announcement | `resources/views/instructor/announcements/create.blade.php` |

### 📂 Folder yang Perlu Dibuat

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

## 🎓 STUDENT DASHBOARD

### ✅ 3 File Utama

| # | Apa | Taruh Dimana |
|---|-----|--------------|
| 1 | **Layout Template** | `resources/views/layouts/student.blade.php` |
| 2 | **CSS Styling** | `public/css/student/dashboard.css` |
| 3 | **API Helper JS** | `public/js/student/api.js` |

### ✅ 10 File View (Blade)

| # | Halaman | Taruh Dimana |
|---|---------|--------------|
| 1 | Dashboard | `resources/views/student/dashboard.blade.php` |
| 2 | Courses List | `resources/views/student/courses/index.blade.php` |
| 3 | Course Detail | `resources/views/student/courses/show.blade.php` |
| 4 | Assignments List | `resources/views/student/assignments/index.blade.php` |
| 5 | Assignment Detail | `resources/views/student/assignments/show.blade.php` |
| 6 | My Grades | `resources/views/student/grades/index.blade.php` |
| 7 | My Attendance | `resources/views/student/attendance/index.blade.php` |
| 8 | My Certificates | `resources/views/student/certificates/index.blade.php` |
| 9 | Profile | `resources/views/student/profile.blade.php` |
| 10 | Notifications | `resources/views/student/notifications.blade.php` |

### 📂 Folder yang Perlu Dibuat

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

## 🔐 AUTHENTICATION

| # | Halaman | Taruh Dimana |
|---|---------|--------------|
| 1 | Login | `resources/views/auth/login.blade.php` |
| 2 | Register | `resources/views/auth/register.blade.php` |
| 3 | Auth CSS | `public/css/auth.css` |
| 4 | Auth JS | `public/js/auth.js` |

### 📂 Folder yang Perlu Dibuat

```bash
mkdir resources/views/auth
```

---

## 🚀 COPY-PASTE COMMANDS

### Windows PowerShell (Buat Semua Folder Sekaligus)

```powershell
# Masuk ke project root dulu
cd C:\Users\hansg\OneDrive\Desktop\lmsRPL3

# Buat semua folder
New-Item -Path "resources/views/layouts" -ItemType Directory -Force
New-Item -Path "resources/views/auth" -ItemType Directory -Force
New-Item -Path "resources/views/parent/students" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/courses" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/grading" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/students" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/announcements" -ItemType Directory -Force
New-Item -Path "resources/views/instructor/certificates" -ItemType Directory -Force
New-Item -Path "resources/views/student/courses" -ItemType Directory -Force
New-Item -Path "resources/views/student/assignments" -ItemType Directory -Force
New-Item -Path "resources/views/student/grades" -ItemType Directory -Force
New-Item -Path "resources/views/student/attendance" -ItemType Directory -Force
New-Item -Path "resources/views/student/certificates" -ItemType Directory -Force
New-Item -Path "public/css/parent" -ItemType Directory -Force
New-Item -Path "public/css/instructor" -ItemType Directory -Force
New-Item -Path "public/css/student" -ItemType Directory -Force
New-Item -Path "public/js/parent" -ItemType Directory -Force
New-Item -Path "public/js/instructor" -ItemType Directory -Force
New-Item -Path "public/js/student" -ItemType Directory -Force

Write-Host "✅ Semua folder sudah dibuat!" -ForegroundColor Green
```

### Git Bash / Linux (Alternative)

```bash
cd /c/Users/hansg/OneDrive/Desktop/lmsRPL3

mkdir -p resources/views/{layouts,auth,parent/students,instructor/{courses,assignments,grading,attendance,students,announcements,certificates},student/{courses,assignments,grades,attendance,certificates}}
mkdir -p public/{css,js}/{parent,instructor,student}

echo "✅ Semua folder sudah dibuat!"
```

---

## 📊 SUMMARY TABLE

| Dashboard | Layout | CSS | JS | Views | Total Files |
|-----------|--------|-----|----|----|-------------|
| **Parent** | 1 | 1 | 1 | 8 | **11** |
| **Instructor** | 1 | 1 | 1 | 14 | **17** |
| **Student** | 1 | 1 | 1 | 10 | **13** |
| **Auth** | - | 1 | 1 | 2 | **4** |
| **TOTAL** | **3** | **4** | **4** | **34** | **45** |

---

## ✅ VERIFICATION

### Cek Apakah Folder Sudah Dibuat (Windows PowerShell)

```powershell
# Jalankan ini untuk verify
Test-Path "resources/views/parent"       # Should return: True
Test-Path "resources/views/instructor"   # Should return: True
Test-Path "resources/views/student"      # Should return: True
Test-Path "public/css/parent"            # Should return: True
Test-Path "public/js/parent"             # Should return: True
```

### Cek Jumlah File yang Sudah Dibuat

```powershell
# Parent files
(Get-ChildItem "resources/views/parent" -Recurse -File).Count  # Should be: 8

# Instructor files  
(Get-ChildItem "resources/views/instructor" -Recurse -File).Count  # Should be: 14

# Student files
(Get-ChildItem "resources/views/student" -Recurse -File).Count  # Should be: 10
```

---

## ⚠️ COMMON MISTAKES

### ❌ SALAH (Jangan Seperti Ini!)

```
resources/view/parent/dashboard.blade.php          ❌ Missing 's' in views
resources/views/Parent/dashboard.blade.php         ❌ Capital P
resources/views/parent/Dashboard.blade.php         ❌ Capital D
public/CSS/parent/dashboard.css                    ❌ Capital CSS
public/Js/parent/api.js                           ❌ Capital J
resources/views/parent/dashboard.php               ❌ Missing .blade
```

### ✅ BENAR (Harus Seperti Ini!)

```
resources/views/parent/dashboard.blade.php         ✅ Correct!
resources/views/instructor/courses/index.blade.php ✅ Correct!
resources/views/student/grades/index.blade.php     ✅ Correct!
public/css/parent/dashboard.css                    ✅ Correct!
public/js/parent/api.js                           ✅ Correct!
```

---

## 🎯 WORKFLOW

### 1️⃣ Buat Folder (5 menit)
```powershell
# Copy-paste command di atas ☝️
```

### 2️⃣ Buka Guide File
- **Parent:** Buka `parent-dashboard-complete.html` di browser
- **Instructor:** Buka `instructor-dashboard-complete.html` di browser  
- **Student:** Buka `student-dashboard-complete.html` di browser

### 3️⃣ Copy Code ke File yang Benar
| Dari Guide | Copy Code Ini | Taruh Di |
|------------|---------------|----------|
| Section "Layout" | Layout code | `resources/views/layouts/{role}.blade.php` |
| Section "CSS" | CSS code | `public/css/{role}/dashboard.css` |
| Section "API Helper" | JavaScript code | `public/js/{role}/api.js` |
| Section "Dashboard" | Blade code | `resources/views/{role}/dashboard.blade.php` |
| Section "Other Pages" | Blade code | `resources/views/{role}/{page}.blade.php` |

**{role}** = parent, instructor, atau student

### 4️⃣ Test
```bash
php artisan serve
# Buka: http://localhost:8000/parent/dashboard
# Buka: http://localhost:8000/instructor/dashboard
# Buka: http://localhost:8000/student/dashboard
```

---

## 💡 PRO TIPS

### 1. Gunakan Code Editor yang Bagus
- ✅ **VS Code** (Recommended)
- ✅ PHPStorm
- ✅ Sublime Text

### 2. Install Extension untuk VS Code
- Laravel Blade Snippets
- Laravel Extra Intellisense
- PHP Intelephense

### 3. Jangan Copy Manual (Gunakan Copy Button!)
- Setiap code block di HTML guide punya tombol "Copy"
- Klik → Paste → Done! ✅

### 4. Test Incremental
- Buat 1 file → Test → Lanjut ✅
- Jangan buat semua dulu baru test ❌

### 5. Commit Regular
```bash
git add .
git commit -m "Add parent dashboard layout"
git push
```

---

## 🆘 TROUBLESHOOTING

### "View [parent.dashboard] not found"

**Problem:** File tidak ada atau path salah

**Solution:**
```powershell
# Cek apakah file ada
Test-Path "resources/views/parent/dashboard.blade.php"

# List semua file di folder
Get-ChildItem "resources/views/parent" -Recurse
```

### "Asset not loading" (CSS/JS tidak muncul)

**Problem:** File tidak ada di public folder

**Solution:**
```powershell
# Cek apakah file ada
Test-Path "public/css/parent/dashboard.css"
Test-Path "public/js/parent/api.js"

# Clear browser cache
# Tekan: Ctrl + Shift + R
```

### "Class not found"

**Problem:** Controller belum dibuat

**Solution:**
```bash
php artisan make:controller ParentController
php artisan make:controller InstructorController
php artisan make:controller StudentController
composer dump-autoload
```

---

## 📞 NEED MORE HELP?

| Issue | Lihat File Ini |
|-------|----------------|
| Path tidak jelas | `FILE-PATHS-SUMMARY.md` |
| Instalasi detail | `INSTALLATION-PATH-GUIDE.md` |
| Quick start | `GETTING-STARTED.md` |
| Code examples | `*-DASHBOARD-GUIDE.md` |
| Code snippets | `*-QUICK-REFERENCE.md` |

---

## ✅ FINAL CHECKLIST

Sebelum mulai coding:
- [ ] Sudah masuk ke project root
- [ ] Sudah jalankan folder creation commands
- [ ] Semua folder sudah terverify (Test-Path returns True)
- [ ] Guide HTML files sudah siap dibuka di browser
- [ ] Code editor sudah buka project folder
- [ ] Laravel server sudah running (`php artisan serve`)

**Semua ✅? MULAI CODING! 🚀**

---

**Happy Coding! 🎨✨**

**Total Time:** ~2-3 hours untuk semua dashboard (jika dikerjakan 3-4 orang parallel)