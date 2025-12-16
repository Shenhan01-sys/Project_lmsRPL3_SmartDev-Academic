# 🌐 API Configuration Documentation

## 📍 Production API Base URL

```
https://portohansgunawan.my.id/api
```

## 🔧 Configuration dalam Kode

### Semua File JavaScript yang Menggunakan API:

#### 1. **StudentDashboard.js**
```javascript
// Gunakan URL ini untuk server online (Hosting)
const API_BASE_URL = "https://portohansgunawan.my.id/api";

// Gunakan URL ini untuk server lokal (php artisan serve)
// const API_BASE_URL = 'http://127.0.0.1:8000/api';

const API_V1 = API_BASE_URL + "/v1";
```

#### 2. **adminDashboard.js**
```javascript
const API_BASE = "https://portohansgunawan.my.id/api";
```

#### 3. **InstructorDashboard.js**
```javascript
const API_BASE_URL = "https://portohansgunawan.my.id/api";
const API_V1 = API_BASE_URL + "/v1";
```

#### 4. **parentDashboard.js**
```javascript
const API_BASE_URL = "https://portohansgunawan.my.id/api";
const API_V1 = API_BASE_URL + "/v1";
```

#### 5. **register.js** ✅ UPDATED
```javascript
// Gunakan URL ini untuk server online (Hosting)
const API_BASE_URL = "https://portohansgunawan.my.id/api";

// Gunakan URL ini untuk server lokal (php artisan serve)
// const API_BASE_URL = "http://127.0.0.1:8000/api";
```

#### 6. **step2.blade.php** (inline script) ✅ UPDATED
```javascript
// Gunakan URL ini untuk server online (Hosting)
const API_BASE_URL = 'https://portohansgunawan.my.id/api';

// Gunakan URL ini untuk server lokal (php artisan serve)
// const API_BASE_URL = 'http://127.0.0.1:8000/api';
```

---

## 🔑 Headers yang Digunakan

### Public Endpoints (Tanpa Authentication):
```javascript
headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "ngrok-skip-browser-warning": "true"
}
```

**Digunakan di:**
- POST `/api/register-calon-siswa` (register.js)
- POST `/api/login` (semua login pages)

### Protected Endpoints (Dengan Authentication):
```javascript
headers: {
    "Authorization": `Bearer ${token}`,
    "Accept": "application/json",
    "ngrok-skip-browser-warning": "true"
}
```

**Untuk JSON Data:**
```javascript
headers: {
    "Content-Type": "application/json",
    "Authorization": `Bearer ${token}`,
    "Accept": "application/json",
    "ngrok-skip-browser-warning": "true"
}
```

**Untuk File Upload (FormData):**
```javascript
headers: {
    "Authorization": `Bearer ${token}`,
    "ngrok-skip-browser-warning": "true"
    // JANGAN tambahkan Content-Type, browser akan set otomatis dengan boundary
}
```

---

## 📋 API Endpoints Overview

### 🌍 Public Endpoints (No Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register-calon-siswa` | Register calon siswa |
| POST | `/api/login` | Login user |

### 🔒 Protected Endpoints (Auth Required)

#### Registration Flow:
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/upload-documents` | Bearer | Upload dokumen registrasi |
| GET | `/api/registration-status` | Bearer | Check status registrasi |

#### Admin:
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/registrations` | Bearer (Admin) | List registrations |
| GET | `/api/v1/registrations/{id}` | Bearer (Admin) | Detail registration |
| POST | `/api/v1/registrations/{id}/approve` | Bearer (Admin) | Approve registration |
| POST | `/api/v1/registrations/{id}/reject` | Bearer (Admin) | Reject registration |

#### Student:
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/students/{id}` | Bearer | Student profile |
| PUT | `/api/v1/students/{id}` | Bearer | Update profile |
| GET | `/api/v1/students/{id}/enrollments` | Bearer | Student enrollments |
| GET | `/api/v1/students/{id}/courses` | Bearer | Student courses |

#### Instructor:
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/instructors/{id}` | Bearer | Instructor profile |
| PUT | `/api/v1/instructors/{id}` | Bearer | Update profile |
| GET | `/api/v1/instructors/{id}/courses` | Bearer | Instructor courses |

#### Courses & Assignments:
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/courses` | Bearer | List courses |
| POST | `/api/v1/courses` | Bearer | Create course |
| GET | `/api/v1/courses/{id}` | Bearer | Course detail |
| POST | `/api/v1/assignments` | Bearer | Create assignment |
| POST | `/api/v1/submissions` | Bearer | Submit assignment |

---

## 🚨 Important Notes

### 1. **CSRF Token**
- ❌ JANGAN gunakan `X-CSRF-TOKEN` untuk API endpoints
- ✅ API menggunakan Bearer token authentication
- ⚠️ CSRF token hanya untuk web form submission (non-API)

### 2. **CORS Headers**
- Header `ngrok-skip-browser-warning: true` diperlukan
- Backend sudah dikonfigurasi untuk accept CORS dari frontend

### 3. **File Upload**
- Gunakan `FormData` object
- JANGAN set `Content-Type` manual
- Browser akan set otomatis dengan multipart/form-data boundary

### 4. **Token Storage**
```javascript
// Store token after login/register
localStorage.setItem('auth_token', token);

// Retrieve token
const token = localStorage.getItem('auth_token');

// Remove token on logout
localStorage.removeItem('auth_token');
```

### 5. **Error Handling**
```javascript
try {
    const response = await fetch(url, options);
    const result = await response.json();
    
    if (!response.ok) {
        // Handle error
        if (result.errors) {
            // Validation errors
            console.error('Validation errors:', result.errors);
        } else if (result.error) {
            // General error
            console.error('Error:', result.error);
        } else if (result.message) {
            // Message error
            console.error('Message:', result.message);
        }
    }
} catch (error) {
    console.error('Network error:', error);
}
```

---

## 🔄 Switching Between Local & Production

### Quick Switch (Comment/Uncomment):

**Development (Local):**
```javascript
// const API_BASE_URL = "https://portohansgunawan.my.id/api";
const API_BASE_URL = "http://127.0.0.1:8000/api";
```

**Production (Hosting):**
```javascript
const API_BASE_URL = "https://portohansgunawan.my.id/api";
// const API_BASE_URL = "http://127.0.0.1:8000/api";
```

### Files to Update:
1. ✅ `public/js/StudentDashboard.js`
2. ✅ `public/js/adminDashboard.js`
3. ✅ `public/js/InstructorDashboard.js`
4. ✅ `public/js/parentDashboard.js`
5. ✅ `public/js/register.js`
6. ✅ `resources/views/register/step2.blade.php`

---

## 🧪 Testing API Connection

### Test dengan Browser Console:

```javascript
// Test public endpoint
fetch('https://portohansgunawan.my.id/api/register-calon-siswa', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': 'true'
    },
    body: JSON.stringify({
        name: 'Test User',
        email: 'test@test.com',
        password: 'password123',
        password_confirmation: 'password123',
        nisn: '1234567890',
        phone_number: '08123456789',
        school_origin: 'Test School'
    })
})
.then(res => res.json())
.then(data => console.log(data))
.catch(err => console.error(err));
```

### Test dengan cURL:

```bash
curl -X POST https://portohansgunawan.my.id/api/register-calon-siswa \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "nisn": "1234567890",
    "phone_number": "08123456789",
    "school_origin": "Test School"
  }'
```

---

## 📊 Status Codes

| Code | Meaning | Action |
|------|---------|--------|
| 200 | OK | Success |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Check request data |
| 401 | Unauthorized | Token invalid/expired, redirect to login |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Backend error, contact admin |

---

## 🛠️ Troubleshooting

### Issue: "Failed to fetch" atau CORS Error
**Solution:**
1. Check network tab di browser DevTools
2. Pastikan API URL benar: `https://portohansgunawan.my.id/api`
3. Pastikan backend CORS sudah dikonfigurasi
4. Tambahkan header `ngrok-skip-browser-warning: true`

### Issue: 401 Unauthorized
**Solution:**
1. Check token ada di localStorage: `localStorage.getItem('auth_token')`
2. Token mungkin expired, logout & login ulang
3. Pastikan header Authorization format: `Bearer {token}`

### Issue: 422 Validation Error
**Solution:**
1. Check response.errors untuk detail field yang error
2. Pastikan semua required fields terisi
3. Pastikan format data sesuai (email, phone, dll)

### Issue: File upload gagal
**Solution:**
1. Pastikan menggunakan FormData
2. JANGAN set Content-Type header manual
3. Check file size < 2MB
4. Check file type sesuai (pdf, jpg, jpeg, png)

---

**Last Updated:** 2025-01-XX  
**Version:** 2.0  
**Maintained by:** SmartDev Academic Development Team