# 📚 RINGKASAN FITUR MANAJEMEN TUGAS

> Quick Reference untuk Developer Frontend & Backend

## 🎯 Fitur Utama

### ✨ Status Submission
- **DRAFT** - Submission baru, belum dikumpulkan
- **SUBMITTED** - Sudah dikumpulkan, menunggu dinilai
- **GRADED** - Sudah dinilai oleh instructor
- **RETURNED** - Dikembalikan untuk revisi

### ⏰ Late Submission
- Auto-detect ketika submit melewati deadline
- Tracking berapa hari terlambat
- Field: `is_late` (boolean), `late_days` (integer)

---

## 📋 Endpoint API

### **ASSIGNMENTS (Tugas)**

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/v1/assignments` | All | List tugas |
| GET | `/api/v1/assignments/{id}` | All | Detail tugas |
| POST | `/api/v1/assignments` | Instructor/Admin | Buat tugas |
| PUT | `/api/v1/assignments/{id}` | Instructor/Admin | Update tugas |
| DELETE | `/api/v1/assignments/{id}` | Instructor/Admin | Hapus tugas |

### **SUBMISSIONS (Pengumpulan)**

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/v1/submissions` | All | List submissions |
| GET | `/api/v1/submissions/{id}` | All | Detail submission |
| POST | `/api/v1/submissions` | Student | Buat submission (draft/submit) |
| PUT | `/api/v1/submissions/{id}` | All | Update submission |
| DELETE | `/api/v1/submissions/{id}` | All | Hapus submission |
| POST | `/api/v1/submissions/{id}/submit` | Student | Submit draft |
| GET | `/api/v1/assignments/{id}/submissions` | Instructor/Admin | List submissions + stats |

---

## 🚀 Quick Start

### Student: Submit Tugas

```javascript
// 1. Save as Draft
POST /api/v1/submissions
{
  "assignment_id": 1,
  "file_path": "/uploads/draft.pdf",
  "submit_now": false
}

// 2. Submit
POST /api/v1/submissions/{id}/submit
// Response akan include: is_late, late_days
```

### Instructor: Beri Nilai

```javascript
PUT /api/v1/submissions/{id}
{
  "grade": 88.5,
  "feedback": "Good work!",
  "status": "graded"
}
```

### Instructor: Return untuk Revisi

```javascript
PUT /api/v1/submissions/{id}
{
  "status": "returned",
  "feedback": "Please revise..."
}
```

---

## 📊 Response Format

### Submit Success (On Time)
```json
{
  "message": "Assignment submitted successfully",
  "submission": {
    "id": 20,
    "status": "submitted",
    "submitted_at": "2025-12-16T10:15:00Z",
    "is_late": false,
    "late_days": 0
  }
}
```

### Submit Success (Late)
```json
{
  "message": "Assignment submitted successfully",
  "submission": {
    "id": 21,
    "status": "submitted",
    "submitted_at": "2025-12-27T16:30:00Z",
    "is_late": true,
    "late_days": 2
  },
  "is_late": true,
  "late_days": 2
}
```

### Assignment Submissions + Statistics
```json
{
  "assignment": { ... },
  "submissions": [ ... ],
  "statistics": {
    "total": 30,
    "submitted": 25,
    "graded": 20,
    "late": 5,
    "draft": 5
  }
}
```

---

## 🔐 Authorization Rules

### Student
- ✅ Buat submission (jika enrolled)
- ✅ Update draft/returned submission
- ✅ Submit draft
- ✅ Lihat submission sendiri
- ❌ Lihat submission student lain
- ❌ Beri nilai

### Instructor
- ✅ Buat assignment (untuk course sendiri)
- ✅ Lihat semua submissions di course sendiri
- ✅ Beri nilai dan feedback
- ✅ Return submission untuk revisi
- ❌ Submit tugas (bukan role student)

### Admin
- ✅ Full access ke semua fitur
- ✅ Buat assignment di semua course
- ✅ Lihat semua submissions

---

## ⚠️ Validasi Penting

### Create Submission
```javascript
// WAJIB
- assignment_id (exists di DB)
- Student harus enrolled di course
- Tidak boleh duplicate submission

// OPTIONAL
- file_path
- submit_now (false = draft, true = submit)
```

### Update Submission (Student)
```javascript
// HANYA JIKA status = "draft" atau "returned"
- file_path
- submit_now
```

### Update Submission (Instructor)
```javascript
// Bebas update kapan saja
- grade (0-100)
- feedback
- status (draft/submitted/graded/returned)
```

---

## 🎨 UI Components

### Status Badge
```javascript
const statusStyles = {
  'draft': 'bg-gray-200 text-gray-800',
  'submitted': 'bg-blue-200 text-blue-800',
  'graded': 'bg-green-200 text-green-800',
  'returned': 'bg-yellow-200 text-yellow-800'
};
```

### Late Warning
```javascript
if (submission.is_late) {
  showWarning(`⚠️ Submitted ${submission.late_days} day(s) late`);
}
```

---

## 🔧 Migration

```bash
# Run migration
php artisan migrate

# File: 2025_12_13_134700_add_status_and_submitted_at_to_submissions.php
# Menambahkan:
# - status (enum)
# - submitted_at (timestamp)
# - is_late (boolean)
# - late_days (integer)
```

---

## 📖 Dokumentasi Lengkap

Lihat file: `docs/frontend-guiding/guidingFiturManajemenTugas.txt`

Berisi:
- ✅ Semua endpoint dengan contoh JSON lengkap
- ✅ Flow diagram untuk student & instructor
- ✅ Contoh implementasi frontend (JavaScript)
- ✅ Troubleshooting guide
- ✅ Best practices
- ✅ API testing examples (Postman/cURL)

---

## 🐛 Common Issues

### "You are not enrolled in the course"
→ Check enrollment table, pastikan status = "active"

### "You have already submitted this assignment"
→ Gunakan PUT untuk update, bukan POST lagi

### "Cannot update submitted submission"
→ Status harus "draft" atau "returned" untuk student edit

### Late days tidak kalkulasi
→ Pastikan due_date ada di assignment
→ Call method `markAsSubmitted()` saat submit

---

## 📞 Support

- 📄 Full Docs: `guidingFiturManajemenTugas.txt`
- 🔗 API Routes: `routes/api.php`
- 🗃️ Models: `app/Models/Submission.php`, `app/Models/Assignment.php`
- 🎮 Controllers: `app/Http/Controllers/API/SubmissionController.php`

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Implementation