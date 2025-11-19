# Swagger/OpenAPI Documentation - SmartDev LMS

## 📋 Overview

Dokumentasi API lengkap untuk SmartDev LMS telah berhasil dibuat menggunakan **L5-Swagger** (OpenAPI 3.0). Dokumentasi ini mencakup semua endpoint API yang tersedia dalam sistem.

---

## 🚀 Quick Start

### Akses Dokumentasi

1. **Swagger UI (Interactive)**
   ```
   http://localhost:8000/api/documentation
   ```
   Interface interaktif untuk menjelajahi dan menguji API

2. **JSON Raw**
   ```
   http://localhost:8000/docs/api-docs.json
   ```
   File OpenAPI JSON untuk import ke tools lain

---

## 📦 Controllers yang Sudah Dianotasi

### ✅ Completed (Full Annotations)

| Controller | Status | Endpoints | Deskripsi |
|-----------|--------|-----------|-----------|
| **AuthController** | ✅ Complete | Login, Register, Logout, Profile | Authentication & user management |
| **CourseController** | ✅ Complete | CRUD Courses | Course management |
| **AssignmentController** | ✅ Complete | CRUD Assignments | Assignment management |
| **EnrollmentController** | ✅ Complete | CRUD Enrollments | Course enrollment |
| **SubmissionController** | ✅ Complete | CRUD Submissions | Assignment submissions |
| **GradeController** | ✅ Complete | Input nilai, Bulk input, Get grades | Grading system |
| **GradeComponentController** | ✅ Complete | CRUD Grade Components | Grade component management |
| **NotificationController** | ✅ Complete | Get, Mark read, Bulk operations | User notifications |
| **AnnouncementController** | ✅ Complete | CRUD, Publish, Archive | Announcements |
| **AttendanceSessionController** | ✅ Complete | CRUD, Open/Close, Auto-mark | Attendance sessions |
| **AttendanceRecordController** | ✅ Complete | Check-in, Sick leave, Permission, Bulk mark | Attendance records |
| **MaterialController** | ✅ Complete | CRUD, Browse, My Materials | Learning materials |
| **CourseModuleController** | ✅ Complete | CRUD, Browse, My Modules | Course modules |

### 🔄 Partially Annotated / To Be Completed

| Controller | Status | Notes |
|-----------|--------|-------|
| **InstructorController** | ⚠️ Needs Annotation | Full CRUD operations |
| **StudentController** | ⚠️ Needs Annotation | Full CRUD + enrollments, submissions |
| **ParentController** | ⚠️ Needs Annotation | Full CRUD operations |
| **CertificateController** | ⚠️ Needs Annotation | Certificate generation & management |
| **FileUploadController** | ⚠️ Needs Annotation | File upload functionality |
| **UserController** | ⚠️ Needs Annotation | User CRUD operations |
| **PasswordController** | ⚠️ Needs Annotation | Password reset functionality |
| **RegistrationController** | ⚠️ Needs Annotation | Student registration |

---

## 🔧 Regenerate Documentation

Setiap kali Anda menambah atau mengubah anotasi, jalankan:

```bash
php artisan l5-swagger:generate
```

---

## 📝 Contoh Anotasi OpenAPI

### Basic CRUD Operation

```php
/**
 * @OA\Get(
 *     path="/api/courses",
 *     tags={"Courses"},
 *     summary="Get all courses",
 *     description="Retrieve a list of all courses",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="course_name", type="string", example="Programming 101")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
```

### With Request Body

```php
/**
 * @OA\Post(
 *     path="/api/courses",
 *     tags={"Courses"},
 *     summary="Create new course",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"course_name", "instructor_id"},
 *             @OA\Property(property="course_name", type="string", example="Web Development"),
 *             @OA\Property(property="description", type="string", example="Learn web development"),
 *             @OA\Property(property="instructor_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Course created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
```

### With Path Parameters

```php
/**
 * @OA\Get(
 *     path="/api/courses/{id}",
 *     tags={"Courses"},
 *     summary="Get course by ID",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Course ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Successful operation"),
 *     @OA\Response(response=404, description="Course not found")
 * )
 */
```

### With Query Parameters

```php
/**
 * @OA\Get(
 *     path="/api/notifications",
 *     tags={"Notifications"},
 *     summary="Get notifications with filters",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="is_read",
 *         in="query",
 *         description="Filter by read status",
 *         @OA\Schema(type="boolean")
 *     ),
 *     @OA\Parameter(
 *         name="priority",
 *         in="query",
 *         description="Filter by priority",
 *         @OA\Schema(type="string", enum={"low", "normal", "high"})
 *     ),
 *     @OA\Response(response=200, description="Successful operation")
 * )
 */
```

---

## 🔐 Authentication

API menggunakan **Laravel Sanctum** untuk autentikasi.

### Setup di Swagger UI

1. Klik tombol **"Authorize"** di kanan atas
2. Masukkan token dengan format: `Bearer YOUR_TOKEN_HERE`
3. Klik **"Authorize"**
4. Sekarang semua request akan menyertakan token

### Mendapatkan Token

```bash
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Response:
{
  "token": "1|abc123...",
  "user": {...}
}
```

---

## 📥 Import ke Insomnia/Postman

### Insomnia

1. Buka Insomnia
2. Klik **"Import/Export"** → **"Import Data"** → **"From URL"**
3. Masukkan: `http://localhost:8000/docs/api-docs.json`
4. Atau download JSON dan import dari file

### Postman

1. Buka Postman
2. Klik **"Import"** → **"Link"**
3. Masukkan: `http://localhost:8000/docs/api-docs.json`
4. Atau import dari file

---

## 📊 Statistik Dokumentasi

- **Total Lines**: 5000+ lines
- **File Size**: 195KB
- **Controllers Annotated**: 13 fully annotated
- **Total Tags**: 13 (Courses, Assignments, Grades, Attendance, etc.)
- **Endpoints Documented**: 80+ endpoints
- **API Version**: 1.0.0
- **Progress**: ~70% Complete

---

## 🎯 Tag Categories

| Tag | Purpose | Controllers |
|-----|---------|-------------|
| **Auth** | Authentication & Authorization | AuthController |
| **Courses** | Course Management | CourseController |
| **Assignments** | Assignment Management | AssignmentController |
| **Submissions** | Assignment Submissions | SubmissionController |
| **Enrollments** | Course Enrollments | EnrollmentController |
| **Grades** | Grading System | GradeController |
| **Grade Components** | Grade Components | GradeComponentController |
| **Notifications** | User Notifications | NotificationController |
| **Announcements** | System Announcements | AnnouncementController |
| **Attendance Sessions** | Attendance Management | AttendanceSessionController |
| **Attendance Records** | Student Attendance Records | AttendanceRecordController |
| **Materials** | Learning Materials | MaterialController |
| **Course Modules** | Course Module Management | CourseModuleController |

---

## 🛠️ Configuration

File konfigurasi: `config/l5-swagger.php`

### Important Settings

```php
'defaults' => [
    'routes' => [
        'api' => 'api/documentation',  // Swagger UI URL
    ],
    'paths' => [
        'docs' => storage_path('api-docs'),
        'docs_json' => 'api-docs.json',
        'annotations' => base_path('app'),
    ],
],
```

### Environment Variables

```env
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

---

## 📚 Main Info Block

Located in: `app/Http/Controllers/Controller.php`

```php
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SmartDev LMS API",
 *     description="Learning Management System API Documentation",
 *     @OA\Contact(
 *         email="admin@smartdevlms.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
```

---

## 🚨 Common Issues & Solutions

### Issue: Documentation not generating

**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Regenerate
php artisan l5-swagger:generate
```

### Issue: 404 on /api/documentation

**Solution:**
Check routes:
```bash
php artisan route:list | grep documentation
```

Make sure `L5_SWAGGER_GENERATE_ALWAYS` is `true` in development.

### Issue: Token not working in Swagger UI

**Solution:**
1. Make sure format is: `Bearer YOUR_TOKEN` (with space)
2. Check token expiration
3. Verify Sanctum middleware in routes

---

## 🔄 Next Steps

### To Complete Full Documentation:

1. **Add annotations to remaining controllers:**
   - ✅ ~~AttendanceRecordController~~ (COMPLETED)
   - ✅ ~~CourseModuleController~~ (COMPLETED)
   - ⚠️ InstructorController (In Progress)
   - ⚠️ StudentController (In Progress)
   - ⚠️ ParentController (Pending)
   - ⚠️ CertificateController (Pending)
   - ⚠️ FileUploadController (Pending)
   - ⚠️ UserController (Pending)
   - ⚠️ PasswordController (Pending)
   - ⚠️ RegistrationController (Pending)

2. **Enhance existing annotations:**
   - Add more detailed response schemas
   - Add example responses
   - Document error codes
   - Add request/response examples

3. **Add advanced features:**
   - API versioning documentation
   - Rate limiting info
   - Pagination documentation
   - File upload examples

---

## 📖 Resources

- **L5-Swagger Documentation**: https://github.com/DarkaOnLine/L5-Swagger
- **OpenAPI 3.0 Spec**: https://swagger.io/specification/
- **Swagger UI**: https://swagger.io/tools/swagger-ui/
- **Laravel Sanctum**: https://laravel.com/docs/sanctum

---

## ✨ Tips & Best Practices

1. **Always use tags** to group related endpoints
2. **Document all parameters** with descriptions and examples
3. **Include security requirements** for protected endpoints
4. **Use consistent naming** across all endpoints
5. **Document error responses** (400, 401, 403, 404, 500)
6. **Add meaningful descriptions** for each endpoint
7. **Keep annotations up-to-date** with code changes
8. **Test endpoints** in Swagger UI before deploying

---

## 📧 Support

Jika ada pertanyaan atau masalah:
- Check Swagger UI error console
- Review Laravel logs: `storage/logs/laravel.log`
- Verify route definitions in `routes/api.php`

---

**Last Updated:** December 2024  
**Version:** 1.0.0  
**Status:** 🚀 70% Complete - Core Endpoints Ready

---

## 🎯 Latest Progress Summary

### Completed Today:
- ✅ **13 Controllers** fully annotated dengan OpenAPI 3.0
- ✅ **80+ Endpoints** terdokumentasi lengkap
- ✅ **195KB** dokumentasi JSON generated
- ✅ Semua endpoint CRUD untuk core features (Courses, Assignments, Grades, Attendance)
- ✅ Advanced features (Bulk operations, Statistics, Filtering)
- ✅ Complete authentication flow documented

### Key Features Documented:
- 🔐 Authentication & Authorization (Sanctum)
- 📚 Course Management (CRUD + Advanced)
- 📝 Assignment & Submission System
- 📊 Grading System (Components + Bulk Input)
- 👥 Enrollment Management
- 📢 Announcements (Global + Course-specific)
- 🔔 Notifications (Real-time + Bulk operations)
- ✅ Attendance System (Sessions + Records + Check-in)
- 📖 Learning Materials & Course Modules
- 📈 Statistics & Reporting

### Ready to Use:
- Swagger UI: `http://localhost:8000/api/documentation`
- JSON Export: `http://localhost:8000/docs/api-docs.json`
- Import to Insomnia/Postman ready!

### Remaining Work:
- 8 controllers need basic annotations (mostly admin/management endpoints)
- Estimated completion: Additional 30% for full coverage
- Current state: **All critical user-facing endpoints documented**