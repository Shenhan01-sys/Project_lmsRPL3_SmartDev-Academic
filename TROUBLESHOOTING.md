# 🔧 Troubleshooting Guide - SmartDev LMS API

## ⚠️ MASALAH UMUM & SOLUSI

---

## 1. 🚨 ENDPOINT BISA DIAKSES TANPA TOKEN

### Masalah:
Endpoint API bisa diakses tanpa token JWT/Bearer authentication.

### Penyebab:
1. Middleware `auth:sanctum` tidak diterapkan
2. Route tidak berada di dalam group yang protected
3. Salah URL endpoint

### ✅ Solusi:

#### Cek URL yang Benar:
API menggunakan prefix **`/api/v1/`** bukan `/api/`

**SALAH** ❌:
```
http://localhost:8000/api/attendance-sessions
```

**BENAR** ✅:
```
http://localhost:8000/api/v1/attendance-sessions
```

#### Verifikasi Middleware:
```bash
# Cek route list untuk memastikan middleware
php artisan route:list | grep attendance-sessions
```

Harus ada `auth:sanctum` di middleware column.

#### Test dengan Token:
```bash
# Request TANPA token (harus 401)
curl http://localhost:8000/api/v1/attendance-sessions

# Response yang diharapkan:
# {"message":"Unauthenticated."}

# Request DENGAN token (harus berhasil)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/v1/attendance-sessions
```

---

## 2. 🗄️ DATA TIDAK TERSIMPAN KE DATABASE

### Masalah:
Create endpoint mengembalikan success tapi data tidak ada di database.

### Penyebab:
1. Database transaction rollback
2. Validation error tidak terlihat
3. Model fillable tidak lengkap
4. Foreign key constraint error

### ✅ Solusi:

#### A. Cek Error Log:
```bash
# Lihat error terbaru
tail -50 storage/logs/laravel.log

# Monitor real-time
tail -f storage/logs/laravel.log
```

#### B. Cek Response dari API:
Perhatikan response body, bukan hanya status code:
```json
// Jika error, akan ada:
{
  "message": "Error creating attendance session",
  "error": "SQLSTATE[23000]: Integrity constraint violation..."
}
```

#### C. Cek Model Fillable:
```php
// app/Models/AttendanceSession.php
protected $fillable = [
    'course_id',
    'session_name',
    'status',
    'deadline',
    'start_time',
    'end_time'
];
```

#### D. Cek Foreign Key:
```bash
# Pastikan course_id exists
php artisan tinker

>>> \App\Models\Course::pluck('id');
# Output: [1, 2, 3, ...]

# Test create manual
>>> \App\Models\AttendanceSession::create([
    'course_id' => 1,
    'session_name' => 'Test',
    'status' => 'open',
    'deadline' => now()->addDay()
]);
```

#### E. Cek Database Connection:
```bash
# Test koneksi
php artisan tinker

>>> DB::connection()->getPdo();
# Jika error, fix .env database config
```

---

## 3. 🔐 TOKEN AUTHENTICATION ISSUES

### Masalah A: Token Tidak Valid
```json
{"message": "Unauthenticated."}
```

#### Penyebab & Solusi:

**1. Token Expired:**
```bash
# Check token di database
php artisan tinker
>>> \Laravel\Sanctum\PersonalAccessToken::latest()->first();
```

**2. Format Token Salah:**
```
# SALAH ❌
Authorization: YOUR_TOKEN_HERE
Authorization: token YOUR_TOKEN_HERE

# BENAR ✅
Authorization: Bearer YOUR_TOKEN_HERE
```

**3. Token Tidak di Database:**
```bash
# Cek table personal_access_tokens
php artisan tinker
>>> \Laravel\Sanctum\PersonalAccessToken::count();
# Jika 0, login ulang untuk generate token baru
```

### Masalah B: Login Tidak Generate Token

#### Cek AuthController:
```php
// AuthController@login harus return token
return response()->json([
    'token' => $token,  // ← Pastikan ada
    'user' => $user
]);
```

---

## 4. 📝 VALIDATION ERRORS

### Masalah:
Request selalu return 422 Validation Error.

### Contoh Error:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "deadline": ["The deadline must be a date after now."]
  }
}
```

### ✅ Solusi:

#### Cek Format Data:

**Dates:**
```json
// SALAH ❌
"deadline": "2024-12-31"

// BENAR ✅
"deadline": "2024-12-31T23:59:59Z"
// atau
"deadline": "2024-12-31 23:59:59"
```

**Booleans:**
```json
// SALAH ❌
"is_active": "true"

// BENAR ✅
"is_active": true
```

**Numbers:**
```json
// SALAH ❌
"weight": "30.0"

// BENAR ✅
"weight": 30.0
```

---

## 5. 🔄 CORS ISSUES (Frontend)

### Masalah:
Frontend tidak bisa akses API karena CORS error.

### ✅ Solusi:

#### A. Install Laravel CORS:
```bash
composer require fruitcake/laravel-cors
```

#### B. Publish Config:
```bash
php artisan vendor:publish --tag="cors"
```

#### C. Update `config/cors.php`:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'], // Frontend URL
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

#### D. Add Middleware in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ...
    \Fruitcake\Cors\HandleCors::class,
];
```

---

## 6. 🗃️ DATABASE QUERY ISSUES

### Masalah A: "No query results for model"

```json
{"message": "No query results for model [App\\Models\\AttendanceSession]."}
```

#### Solusi:
```bash
# Cek apakah data exists
php artisan tinker

>>> \App\Models\AttendanceSession::count();
>>> \App\Models\AttendanceSession::all();

# Jika kosong, run seeder
php artisan db:seed --class=AttendanceSeeder
```

### Masalah B: Foreign Key Constraint

```
SQLSTATE[23000]: Integrity constraint violation: 
1452 Cannot add or update a child row: a foreign key constraint fails
```

#### Solusi:
```bash
# Cek parent record exists
php artisan tinker

>>> \App\Models\Course::find(1); // Harus ada
>>> \App\Models\User::find(1);   // Harus ada

# Atau use seeder
php artisan db:seed
```

---

## 7. 📊 SWAGGER DOCUMENTATION ISSUES

### Masalah A: Dokumentasi Tidak Muncul

```
404 Not Found: /api/documentation
```

#### Solusi:
```bash
# Regenerate docs
php artisan l5-swagger:generate

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check .env
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

### Masalah B: Annotations Error

```
Required @OA\PathItem() not found
```

#### Solusi:
Pastikan semua annotations lengkap:
```php
/**
 * @OA\Get(
 *     path="/api/v1/resource",  // ← Harus ada path
 *     tags={"Tag"},              // ← Harus ada tags
 *     summary="Description",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Success")  // ← Harus ada response
 * )
 */
```

---

## 8. 🔍 DEBUGGING TIPS

### A. Enable Debug Mode:
```env
# .env
APP_DEBUG=true
APP_ENV=local
```

### B. Check Logs:
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Last 100 lines
tail -100 storage/logs/laravel.log
```

### C. Use Tinker:
```bash
php artisan tinker

# Test model
>>> $session = new \App\Models\AttendanceSession();
>>> $session->fillable;

# Test relationship
>>> \App\Models\AttendanceSession::with('course')->first();

# Test query
>>> DB::enableQueryLog();
>>> \App\Models\AttendanceSession::all();
>>> DB::getQueryLog();
```

### D. Clear All Cache:
```bash
php artisan optimize:clear

# Or individually:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 9. 🧪 TESTING ENDPOINTS

### A. Using CURL:

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

**Create with Token:**
```bash
curl -X POST http://localhost:8000/api/v1/attendance-sessions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": 1,
    "session_name": "Week 1 Attendance",
    "status": "open",
    "deadline": "2024-12-31T23:59:59Z"
  }'
```

**Get All:**
```bash
curl -X GET http://localhost:8000/api/v1/attendance-sessions \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### B. Using Swagger UI:

1. Buka: `http://localhost:8000/api/documentation`
2. Klik **"Authorize"** (kanan atas)
3. Masukkan: `Bearer YOUR_TOKEN`
4. Klik **"Authorize"**
5. Test endpoint langsung di UI

### C. Using Insomnia/Postman:

1. Import collection dari: `http://localhost:8000/docs/api-docs.json`
2. Setup environment variable `base_url` = `http://localhost:8000`
3. Setup environment variable `token` = `YOUR_TOKEN`
4. Use `{{base_url}}/api/v1/...` in requests
5. Use `Bearer {{token}}` in Authorization header

---

## 10. ⚡ PERFORMANCE ISSUES

### Masalah: API Lambat

#### Solusi:

**A. Enable Query Logging:**
```php
// Tambahkan di AppServiceProvider
DB::listen(function($query) {
    Log::info($query->sql);
    Log::info($query->bindings);
    Log::info($query->time);
});
```

**B. Optimize Queries:**
```php
// LAMBAT ❌ (N+1 problem)
$sessions = AttendanceSession::all();
foreach ($sessions as $session) {
    echo $session->course->name;
}

// CEPAT ✅ (Eager loading)
$sessions = AttendanceSession::with('course')->get();
```

**C. Add Database Indexes:**
```php
// migration
$table->index('course_id');
$table->index('status');
$table->index('deadline');
```

**D. Use Cache:**
```php
$courses = Cache::remember('courses', 3600, function () {
    return Course::all();
});
```

---

## 📞 QUICK HELP COMMANDS

```bash
# Check Laravel version
php artisan --version

# Check routes
php artisan route:list | grep attendance

# Check database connection
php artisan db:show

# Run migrations
php artisan migrate:fresh --seed

# Generate API docs
php artisan l5-swagger:generate

# Clear everything
php artisan optimize:clear

# Create test data
php artisan db:seed

# Interactive console
php artisan tinker

# Check storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache

# View logs
tail -f storage/logs/laravel.log
```

---

## 🎯 CHECKLIST SEBELUM REPORT BUG

Sebelum report issue, pastikan sudah:

- [ ] ✅ Menggunakan URL yang benar (`/api/v1/...`)
- [ ] ✅ Token JWT sudah disertakan di header
- [ ] ✅ Format token: `Bearer YOUR_TOKEN`
- [ ] ✅ Data request sesuai format (JSON)
- [ ] ✅ Foreign key references exists
- [ ] ✅ Cek error di `storage/logs/laravel.log`
- [ ] ✅ Clear cache (`php artisan optimize:clear`)
- [ ] ✅ Database connection aktif
- [ ] ✅ Test dengan Swagger UI dulu
- [ ] ✅ Verifikasi dengan CURL/Tinker

---

## 📚 RESOURCES

- **API Documentation**: http://localhost:8000/api/documentation
- **OpenAPI JSON**: http://localhost:8000/docs/api-docs.json
- **Laravel Logs**: `storage/logs/laravel.log`
- **Laravel Docs**: https://laravel.com/docs
- **Sanctum Docs**: https://laravel.com/docs/sanctum

---

## 🆘 STILL STUCK?

1. **Check logs first**: `tail -50 storage/logs/laravel.log`
2. **Test in Tinker**: `php artisan tinker`
3. **Verify with Swagger**: http://localhost:8000/api/documentation
4. **Search error**: Copy error message → Google/Stack Overflow

---

**Last Updated**: December 18, 2024  
**Version**: 1.0.0