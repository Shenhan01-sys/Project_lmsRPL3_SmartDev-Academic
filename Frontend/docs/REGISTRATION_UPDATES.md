# 📋 Dokumentasi Update Form Registrasi Calon Siswa

## 🎯 Overview
Dokumen ini menjelaskan perubahan lengkap pada sistem registrasi calon siswa agar sesuai dengan requirement dari `RegistrationController.php` dan mengikuti pola konfigurasi upload yang sudah digunakan di Instructor Dashboard dan Student Dashboard.

---

## ✅ Perubahan yang Dilakukan

### 1. **Step 1 - Form Data Diri (step1.blade.php)**

#### ✨ Field Baru yang Ditambahkan:
1. **Email Orang Tua** (`email_orang_tua`)
   - Type: Email
   - Status: Optional
   - Note: Akan dibuat otomatis oleh sistem jika kosong saat approval

2. **NISN** (`nisn`)
   - Type: Text (10 digit)
   - Status: Required
   - Validasi: 10 digit angka
   - Unique: Ya

3. **Asal Sekolah** (`school_origin`)
   - Type: Text
   - Status: Required
   - Placeholder: "Nama sekolah asal (SMP/MTs)"

#### 🔧 Field yang Diperbaiki:
- **Nomor Telepon Siswa**: `phone` → `phone_number` (sesuai API)
- Semua field sudah disesuaikan dengan requirement API

#### 📝 Struktur Data Lengkap Step 1:
```javascript
{
  // Data Siswa
  "name": "required",
  "email": "required|unique",
  "password": "required|min:8|confirmed",
  "password_confirmation": "required",
  "phone_number": "required",
  "nisn": "required|unique|10 digits",
  "tanggal_lahir": "required|date",
  "tempat_lahir": "required",
  "jenis_kelamin": "required|L/P",
  "school_origin": "required",
  
  // Data Orang Tua
  "nama_orang_tua": "required",
  "email_orang_tua": "optional|email",
  "phone_orang_tua": "required",
  "alamat_orang_tua": "required|max:500"
}
```

---

### 2. **Step 2 - Upload Dokumen (step2.blade.php)**

#### ⚠️ Perubahan Major:
Dokumen yang di-upload telah disesuaikan dengan requirement API `/api/upload-documents`

#### ❌ Dokumen Lama (DIHAPUS):
- `ktp_orang_tua`
- `foto_siswa`
- `bukti_pembayaran`

#### ✅ Dokumen Baru (SESUAI API):
1. **Ijazah Terakhir** (`ijazah`)
   - Optional
   - Mimes: pdf, jpg, jpeg, png
   - Max: 2MB
   - Storage: `storage/app/public/registration/ijazah/`

2. **SKHUN** (`skhun`)
   - Optional
   - Mimes: pdf, jpg, jpeg, png
   - Max: 2MB
   - Storage: `storage/app/public/registration/skhun/`

3. **Kartu Keluarga** (`kk`)
   - Optional
   - Mimes: pdf, jpg, jpeg, png
   - Max: 2MB
   - Storage: `storage/app/public/registration/kk/`

4. **Akta Kelahiran** (`akta_kelahiran`)
   - Optional
   - Mimes: pdf, jpg, jpeg, png
   - Max: 2MB
   - Storage: `storage/app/public/registration/akta_kelahiran/`

#### 🎨 UI/UX Improvements:
- Drag & drop support untuk semua file
- Preview file name dan size sebelum upload
- Remove file button
- Visual feedback (border color changes)
- File validation client-side (type & size)
- Catatan informatif tentang requirement dokumen

---

### 3. **JavaScript Handler (register.js)** ⭐ NEW FILE

#### 📍 Location: `public/js/register.js`

#### 🎯 Features:
1. **Form Validation**
   - Real-time validation on blur
   - Password strength indicator
   - Field-specific error messages
   - NISN format validation (10 digits)
   - Email format validation
   - Phone number format validation

2. **API Integration**
   - Submit ke `POST /api/register-calon-siswa`
   - Kirim semua data sekaligus (tidak ada localStorage untuk additional data)
   - Handle response token
   - Error handling yang proper
   - Loading state management

3. **Data yang Dikirim**:
```javascript
{
  name, email, password, password_confirmation,
  nisn, phone_number, school_origin,
  tanggal_lahir, tempat_lahir, jenis_kelamin,
  nama_orang_tua, email_orang_tua, 
  phone_orang_tua, alamat_orang_tua
}
```

4. **User Experience**
   - SweetAlert2 notifications
   - Disable button saat submit
   - Loading spinner
   - Auto redirect ke step 2 setelah sukses
   - Save token ke localStorage

---

### 4. **Backend Controller Update** 

#### 📍 File: `app/Http/Controllers/API/RegistrationController.php`

#### 🔧 Method: `registerCalonSiswa()`

**Validation Rules Updated:**
```php
[
    "name" => "required|string|max:255",
    "email" => "required|string|email|max:255|unique:users",
    "password" => "required|string|min:8|confirmed",
    "nisn" => "required|string|unique:calon_siswas",
    "phone_number" => "required|string",
    "school_origin" => "required|string",
    
    // ✨ NEW: Optional fields
    "tanggal_lahir" => "nullable|date",
    "tempat_lahir" => "nullable|string|max:255",
    "jenis_kelamin" => "nullable|in:L,P",
    "nama_orang_tua" => "nullable|string|max:255",
    "email_orang_tua" => "nullable|email|max:255",
    "phone_orang_tua" => "nullable|string",
    "alamat_orang_tua" => "nullable|string",
]
```

**StudentRegistration::create() Updated:**
Sekarang menyimpan semua data additional fields yang dikirim dari form.

---

### 5. **Step 2 JavaScript Handler (Inline di step2.blade.php)**

#### 🎯 Features (Sesuai dengan Instructor/Student Dashboard Pattern):

1. **Token Management**
   - Retrieve token dari localStorage
   - Redirect ke step1 jika token tidak ada

2. **File Upload Handler**
   - Drag & drop support
   - Click to upload
   - File preview (name, size, icon)
   - Remove file button
   - Visual feedback (border colors)

3. **Client-side Validation**
   - File type validation (pdf, jpg, jpeg, png)
   - File size validation (max 2MB)
   - Real-time error messages

4. **API Submission**
   - FormData untuk file upload
   - Authorization: Bearer {token}
   - Submit ke `POST /api/upload-documents`
   - Handle success/error response
   - Alert container untuk feedback

5. **Post-Submit Actions**
   - Clear token dari localStorage
   - Redirect ke `/login?registered=true`
   - Atau langsung proceed jika tidak ada file di-upload

---

## 🔄 Flow Registrasi Lengkap

```
┌─────────────────────────────────────────────────────────────┐
│                    1. User Buka /register                    │
│                  (redirect ke /register/step1)               │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│              2. Step 1 - Isi Data Diri & Ortu                │
│  • Data siswa (name, email, password, nisn, dll)             │
│  • Data orang tua (nama, email*, phone, alamat)              │
│  • Client-side validation dengan register.js                 │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│          3. Submit ke API /api/register-calon-siswa          │
│  • Kirim semua data sekaligus (tidak bertahap)               │
│  • Backend create User (role: calon_siswa)                   │
│  • Backend create StudentRegistration (status: pending)      │
│  • Response: { token, message, data }                        │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│              4. Token Disimpan ke localStorage               │
│              Redirect otomatis ke /register/step2            │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│          5. Step 2 - Upload Dokumen (OPSIONAL)               │
│  • Ijazah, SKHUN, KK, Akta Kelahiran                         │
│  • Semua dokumen optional                                    │
│  • Drag & drop atau click to upload                          │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│           6. Submit ke API /api/upload-documents             │
│  • Authorization: Bearer {token}                             │
│  • FormData with files                                       │
│  • Backend store files ke storage/app/public/registration/   │
│  • Update StudentRegistration->documents (JSON)              │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│                 7. Clear Token dari localStorage             │
│              Redirect ke /login?registered=true              │
│        User dapat login dengan kredensial yang dibuat        │
└───────────────────────────────┬─────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────┐
│              8. Admin Review & Approve/Reject                │
│  • Admin lihat registrations di /api/v1/registrations        │
│  • Approve: POST /api/v1/registrations/{id}/approve          │
│    - User role: calon_siswa → student                        │
│    - Create Student account                                  │
│    - Create Parent account (auto-gen email jika kosong)      │
│    - Link Student-Parent relationship                        │
│  • Reject: POST /api/v1/registrations/{id}/reject            │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Database Schema Requirement

### Table: `users`
```sql
- id
- name
- email (unique)
- password
- role (enum: admin, instructor, student, parent, calon_siswa)
- created_at, updated_at
```

### Table: `calon_siswas` (StudentRegistration)
```sql
- id
- user_id (foreign key -> users.id)
- nisn (unique, 10 digits)
- phone_number
- school_origin
- tanggal_lahir (date)
- tempat_lahir
- jenis_kelamin (L/P)
- nama_orang_tua
- email_orang_tua (nullable)
- phone_orang_tua
- alamat_orang_tua
- registration_number (auto-generated: REG-YYYYMMDD-XXXX)
- registration_status (enum: pending, approved, rejected)
- documents (JSON: {ijazah, skhun, kk, akta_kelahiran})
- approved_at (nullable)
- approved_by (nullable, foreign key -> users.id)
- rejection_reason (nullable)
- created_at, updated_at
```

---

## 🚀 Deployment Checklist

### 1. Upload Files ke Production:
```
✅ resources/views/register/step1.blade.php
✅ resources/views/register/step2.blade.php
✅ public/js/register.js
✅ app/Http/Controllers/API/RegistrationController.php
```

### 2. Database Migration (jika diperlukan):
```sql
-- Pastikan kolom-kolom ini ada di tabel calon_siswas
ALTER TABLE calon_siswas ADD COLUMN email_orang_tua VARCHAR(255) NULL AFTER nama_orang_tua;
-- Atau jalankan migration yang sesuai
```

### 3. Clear Cache di Production:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php -r "opcache_reset();"
```

### 4. Storage Link:
```bash
php artisan storage:link
```

### 5. Set Permissions:
```bash
chmod -R 775 storage/app/public/registration
chown -R www-data:www-data storage/app/public/registration
```

---

## 🧪 Testing Checklist

### Step 1 Testing:
- [ ] Semua field required tervalidasi
- [ ] Email validation (format & unique)
- [ ] Password min 8 karakter
- [ ] Password confirmation match
- [ ] NISN validation (10 digit & unique)
- [ ] Phone number format validation
- [ ] Password strength indicator berfungsi
- [ ] Character counter untuk alamat berfungsi
- [ ] Radio button jenis kelamin
- [ ] Submit berhasil → redirect ke step2
- [ ] Token tersimpan di localStorage
- [ ] Error handling tampil dengan benar

### Step 2 Testing:
- [ ] Token check (redirect jika tidak ada)
- [ ] Drag & drop file berfungsi
- [ ] Click to upload berfungsi
- [ ] File preview (name, size, icon)
- [ ] Remove file button
- [ ] File type validation (pdf, jpg, jpeg, png)
- [ ] File size validation (max 2MB)
- [ ] Submit tanpa file → warning & redirect
- [ ] Submit dengan file → success & redirect
- [ ] Authorization header dikirim
- [ ] Token cleared setelah submit
- [ ] Redirect ke /login?registered=true

### API Testing:
- [ ] POST /api/register-calon-siswa
  - [ ] Required fields validation
  - [ ] Email unique validation
  - [ ] NISN unique validation
  - [ ] Password confirmation validation
  - [ ] Return token in response
  - [ ] User created with role calon_siswa
  - [ ] StudentRegistration created
- [ ] POST /api/upload-documents
  - [ ] Authorization required
  - [ ] File validation (type, size)
  - [ ] Files stored correctly
  - [ ] Documents JSON updated
- [ ] GET /api/v1/registrations (Admin)
  - [ ] List semua registrations
  - [ ] Filter by status
  - [ ] Pagination
- [ ] POST /api/v1/registrations/{id}/approve (Admin)
  - [ ] User role updated: calon_siswa → student
  - [ ] Student account created
  - [ ] Parent account created/linked
  - [ ] Email orang tua auto-generated jika kosong

---

## 🔍 Troubleshooting

### Issue 1: Token tidak tersimpan
**Solution:** Check browser localStorage, pastikan response API mengembalikan token.

### Issue 2: File tidak ter-upload
**Solution:**
- Check storage permission: `chmod -R 775 storage/app/public`
- Check storage link: `php artisan storage:link`
- Check max upload size di php.ini: `upload_max_filesize` & `post_max_size`

### Issue 3: Validation error tidak muncul
**Solution:** Check response format dari API, pastikan menggunakan key `errors` untuk validation errors.

### Issue 4: Email orang tua kosong saat approval
**Solution:** Normal, sistem akan auto-generate email jika kosong saat approval.

### Issue 5: CORS Error
**Solution:** Check config/cors.php, pastikan API path included.

---

## 📚 API Endpoints Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register-calon-siswa` | Public | Register calon siswa, return token |
| POST | `/api/upload-documents` | Bearer | Upload dokumen pendaftaran |
| GET | `/api/registration-registration_status` | Bearer | Check status registrasi |
| GET | `/api/v1/registrations` | Bearer (Admin) | List semua registrations |
| GET | `/api/v1/registrations/{id}` | Bearer | Detail satu registration |
| POST | `/api/v1/registrations/{id}/approve` | Bearer (Admin) | Approve & create accounts |
| POST | `/api/v1/registrations/{id}/reject` | Bearer (Admin) | Reject registration |

---

## 🎨 UI/UX Highlights

### Step 1:
- Clean form layout dengan sections
- Icon untuk setiap field
- Password strength indicator real-time
- Character counter untuk textarea
- Radio button dengan custom styling
- Form helper text untuk guidance
- Field-level error messages
- Responsive design

### Step 2:
- Drag & drop upload zones
- Visual file preview
- Upload progress feedback
- Color-coded states (default, hover, has-file, error)
- Info box dengan catatan penting
- Optional uploads (tidak wajib)
- Back button ke step 1
- Loading state saat submit

---

## 📝 Notes

1. **Email Orang Tua** sekarang optional karena:
   - Tidak semua orang tua punya email
   - Sistem akan auto-generate saat approval jika kosong
   - Format: `{nama.orang.tua}{random}@parent.com`

2. **Dokumen Upload** semua optional karena:
   - Bisa dilengkapi nanti via dashboard
   - Tidak menghambat proses registrasi
   - Lebih user-friendly

3. **Password Default** saat approval:
   - Student: nama lengkap siswa (tanpa spasi, lowercase)
   - Parent: nama orang tua (tanpa spasi, lowercase)
   - Harus diinformasikan ke user setelah approval

4. **File Storage**:
   - Location: `storage/app/public/registration/{docType}/`
   - Public URL: `/storage/registration/{docType}/{filename}`
   - Docs stored as JSON in StudentRegistration->documents

---

## ✨ Improvements dari Versi Sebelumnya

1. ✅ Submit langsung ke API (bukan POST form tradisional)
2. ✅ Token-based authentication untuk upload
3. ✅ Semua data dikirim sekaligus di step 1
4. ✅ Upload dokumen sesuai API requirement
5. ✅ Drag & drop file upload
6. ✅ Better error handling & user feedback
7. ✅ Konsisten dengan pattern dashboard lain
8. ✅ Real-time validation
9. ✅ Loading states & disabled buttons
10. ✅ SweetAlert2 notifications

---

## 🎯 Next Steps (Future Enhancements)

- [ ] Email verification untuk calon siswa
- [ ] SMS verification untuk phone number
- [ ] Upload progress bar (0-100%)
- [ ] Image preview untuk file gambar
- [ ] Auto-save form data (draft)
- [ ] Multi-language support
- [ ] Notification email saat approval/rejection
- [ ] Dashboard untuk calon_siswa (track status)

---

**Last Updated:** 2025-01-XX  
**Version:** 2.0  
**Author:** SmartDev Academic Development Team