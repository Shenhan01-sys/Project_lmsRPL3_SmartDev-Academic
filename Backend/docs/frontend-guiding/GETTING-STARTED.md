# 🚀 Getting Started - LMS Frontend Implementation

Panduan singkat untuk memulai implementasi LMS Frontend. **Baca ini terlebih dahulu!**

---

## 📌 Quick Overview

Kamu akan membuat 3 dashboard:
1. **👨‍👩‍👧‍👦 Parent Dashboard** - Untuk orang tua melihat progress anak
2. **👨‍🏫 Instructor Dashboard** - Untuk instruktur manage course & siswa
3. **🎓 Student Dashboard** - Untuk siswa akses materi & submit tugas

**Total waktu implementasi:** ~3.5 jam untuk semua dashboard

---

## ✅ Prerequisites

Pastikan sudah punya:
- [ ] Laravel project installed di `C:\Users\hansg\OneDrive\Desktop\lmsRPL3`
- [ ] Backend API running via ngrok: `https://loraine-seminiferous-snappily.ngrok-free.dev`
- [ ] Code editor (VS Code recommended)
- [ ] Browser (Chrome/Edge recommended)
- [ ] Terminal/Command Prompt

---

## 🎯 5-Step Quick Start

### Step 1: Buka Project (1 menit)

```bash
# Buka terminal/cmd, masuk ke project
cd C:\Users\hansg\OneDrive\Desktop\lmsRPL3
```

### Step 2: Buat Semua Folder (2 menit)

**Windows PowerShell:**
```powershell
# Copy-paste semua baris ini sekaligus ke PowerShell
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
```

**Git Bash / Linux:**
```bash
mkdir -p resources/views/{layouts,auth,parent/students,instructor/{courses,assignments,grading,attendance,students,announcements,certificates},student/{courses,assignments,grades,attendance,certificates}}
mkdir -p public/{css,js}/{parent,instructor,student}
```

**✅ Verify:** Cek apakah folder sudah dibuat:
```powershell
Test-Path "resources/views/parent"  # Should return True
Test-Path "public/css/parent"       # Should return True
```

### Step 3: Buat Controllers (2 menit)

```bash
php artisan make:controller ParentController
php artisan make:controller InstructorController
php artisan make:controller StudentController
php artisan make:controller AuthController
```

**✅ Verify:** Controllers harus ada di `app/Http/Controllers/`

### Step 4: Copy Code dari Guide (Variable, tergantung dashboard)

Sekarang saatnya copy code! Kamu punya 2 pilihan:

#### Option A: Pakai Interactive HTML (RECOMMENDED ⭐)
1. Buka file HTML di browser:
   - `parent-dashboard-complete.html`
   - `instructor-dashboard-complete.html`
   - `student-dashboard-complete.html`

2. Klik tab yang kamu butuhkan
3. Klik tombol "Copy" di kanan atas code block
4. Paste ke file yang sesuai (lihat path di bawah)

#### Option B: Pakai Guide Markdown
1. Buka file `.md` di editor:
   - `PARENT-DASHBOARD-GUIDE.md`
   - `INSTRUCTOR-DASHBOARD-GUIDE.md`
   - `STUDENT-DASHBOARD-GUIDE.md`

2. Scroll ke section yang kamu butuhkan
3. Copy code block
4. Paste ke file yang sesuai

**🗂️ File Path Reference:** Lihat `FILE-PATHS-SUMMARY.md` untuk list lengkap path semua file.

### Step 5: Update Routes & Test (5 menit)

1. **Edit `routes/web.php`:**
   - Buka file: `C:\Users\hansg\OneDrive\Desktop\lmsRPL3\routes\web.php`
   - Copy route definitions dari `INSTALLATION-PATH-GUIDE.md` section "Setup Routes"
   - Paste & save

2. **Clear cache Laravel:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

3. **Run server:**
   ```bash
   php artisan serve
   ```

4. **Test di browser:**
   - Open: `http://localhost:8000`
   - Test login: `http://localhost:8000/login`
   - Test parent: `http://localhost:8000/parent/dashboard`
   - Test instructor: `http://localhost:8000/instructor/dashboard`
   - Test student: `http://localhost:8000/student/dashboard`

---

## 📁 Dimana Meletakkan File?

### Contoh: Parent Dashboard

| Code dari Guide | Path File di Project |
|----------------|---------------------|
| Layout Template | `resources/views/layouts/parent.blade.php` |
| CSS | `public/css/parent/dashboard.css` |
| API Helper JS | `public/js/parent/api.js` |
| Dashboard Page | `resources/views/parent/dashboard.blade.php` |
| Students List | `resources/views/parent/students/index.blade.php` |
| Student Detail | `resources/views/parent/students/show.blade.php` |

**💡 Tip:** Untuk list lengkap semua path, buka `FILE-PATHS-SUMMARY.md`

---

## 🎯 Implementation Order (Recommended)

Implement dashboards dalam urutan ini:

### 1️⃣ Parent Dashboard (30 menit) - TERMUDAH
Kenapa pertama?
- ✅ Paling sederhana (mostly read-only)
- ✅ Tidak ada form kompleks
- ✅ Bagus untuk belajar struktur

**Files to create (10 files):**
- 1 layout, 1 CSS, 1 JS, 8 views

### 2️⃣ Student Dashboard (45 menit) - SEDANG
**Files to create (12 files):**
- 1 layout, 1 CSS, 1 JS, 10 views
- Ada file upload di assignments

### 3️⃣ Instructor Dashboard (45 menit) - PALING KOMPLEKS
**Files to create (16 files):**
- 1 layout, 1 CSS, 1 JS, 14 views
- Banyak CRUD operations
- Form kompleks

---

## 📚 Dokumentasi Reference

| File | Isi | Kapan Pakai |
|------|-----|------------|
| `GETTING-STARTED.md` | Panduan singkat (file ini) | Awal mulai |
| `INSTALLATION-PATH-GUIDE.md` | Detail semua path & instalasi | Setup folder |
| `FILE-PATHS-SUMMARY.md` | Quick reference path | Cari path file |
| `PARENT-DASHBOARD-GUIDE.md` | Parent implementation | Buat parent dashboard |
| `INSTRUCTOR-DASHBOARD-GUIDE.md` | Instructor implementation | Buat instructor dashboard |
| `STUDENT-DASHBOARD-GUIDE.md` | Student implementation | Buat student dashboard |
| `*-QUICK-REFERENCE.md` | Code snippets | Cari contoh code cepat |
| `*.html` files | Interactive guide | Browse & copy code |

---

## 🔧 Workflow untuk Setiap Dashboard

Untuk setiap dashboard (Parent/Instructor/Student), ikuti workflow ini:

### ✅ Phase 1: Structure (5 min)
1. Buat layout file di `resources/views/layouts/{role}.blade.php`
2. Buat CSS file di `public/css/{role}/dashboard.css`
3. Buat JS API helper di `public/js/{role}/api.js`

### ✅ Phase 2: Main Dashboard (10 min)
1. Buat main dashboard view
2. Copy code dari guide
3. Test di browser

### ✅ Phase 3: Sub Pages (20-40 min)
1. Buat view files satu per satu
2. Copy code dari guide
3. Test setiap page

### ✅ Phase 4: Routes & Controller (10 min)
1. Add routes ke `routes/web.php`
2. Add methods ke Controller
3. Test semua routes

---

## 🐛 Common Issues & Quick Fix

### Issue: "View not found"
```bash
# Fix: Pastikan file ada & path benar
Test-Path "resources/views/parent/dashboard.blade.php"  # Windows
ls resources/views/parent/dashboard.blade.php           # Linux

# Path harus PERSIS seperti ini (huruf kecil semua)
✅ resources/views/parent/dashboard.blade.php
❌ resources/views/Parent/dashboard.blade.php  # Salah!
```

### Issue: "CSS/JS not loading"
```bash
# Fix 1: Pastikan file ada di public/
Test-Path "public/css/parent/dashboard.css"

# Fix 2: Clear browser cache
# Tekan Ctrl + F5 di browser

# Fix 3: Check path di layout
# Harus: {{ asset('css/parent/dashboard.css') }}
```

### Issue: "Class not found"
```bash
# Fix: Pastikan controller sudah dibuat
php artisan make:controller ParentController

# Clear cache
php artisan cache:clear
composer dump-autoload
```

### Issue: "Route not found"
```bash
# Fix: Clear route cache
php artisan route:clear
php artisan route:cache

# List all routes to verify
php artisan route:list
```

---

## 💡 Tips & Best Practices

### 1. **Salin Persis Seperti Guide**
   - ⚠️ Jangan ubah path/nama file dulu
   - ⚠️ Copy code persis seperti di guide
   - ✅ Setelah jalan, baru customize

### 2. **Test Incremental**
   - ✅ Buat 1 page → test → lanjut
   - ❌ Jangan buat semua dulu baru test

### 3. **Pakai Interactive HTML**
   - ✅ Lebih mudah navigate
   - ✅ Copy button lebih cepat
   - ✅ Syntax highlighting

### 4. **Perhatikan Case Sensitivity**
   - ✅ `parent` (lowercase)
   - ❌ `Parent` (capital)
   - Laravel is case-sensitive!

### 5. **Save & Commit Regular**
   ```bash
   git add .
   git commit -m "Add parent dashboard"
   ```

---

## 📞 Need Help?

### Quick Troubleshooting Steps:
1. ✅ Baca error message lengkap
2. ✅ Check file exists di path yang benar
3. ✅ Verify folder permissions
4. ✅ Clear all cache: `php artisan cache:clear`
5. ✅ Check Laravel logs: `storage/logs/laravel.log`

### Documentation Files:
- **Path issues?** → Buka `FILE-PATHS-SUMMARY.md`
- **Installation issues?** → Buka `INSTALLATION-PATH-GUIDE.md`
- **Code examples?** → Buka `*-QUICK-REFERENCE.md`
- **Full guide?** → Buka `*-DASHBOARD-GUIDE.md`
- **Visual guide?** → Buka `*.html` files di browser

---

## ✅ Final Checklist

Sebelum mulai coding, pastikan:
- [ ] Project Laravel sudah running
- [ ] Backend API via ngrok accessible
- [ ] Semua folder sudah dibuat (Step 2)
- [ ] Controllers sudah dibuat (Step 3)
- [ ] Punya guide files siap dibuka
- [ ] Code editor sudah buka project folder
- [ ] Browser sudah siap untuk test

**Semua ✅? LET'S GO! 🚀**

---

## 🎉 You're Ready!

**Pilih starting point:**

### 👶 Pemula / Pertama kali
→ Mulai dengan **Parent Dashboard**  
→ Buka: `parent-dashboard-complete.html` di browser  
→ Follow step-by-step, copy-paste code  

### 💪 Sudah pengalaman
→ Mulai dari dashboard mana aja  
→ Pakai Quick Reference files untuk cepat  
→ Customize sesuai kebutuhan  

### 🚀 Expert
→ Baca structure di `INSTALLATION-PATH-GUIDE.md`  
→ Skim through guide files  
→ Build your way!  

---

**Happy Coding! 🎨✨**

**Estimated time to complete all dashboards: 2-4 hours**

Need help? Check the troubleshooting section or documentation reference above!