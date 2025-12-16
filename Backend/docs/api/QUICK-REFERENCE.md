# 🎯 Quick Reference - Auth & Registration Decisions

## ✅ Final Design Decisions

### 1. Login System

**Decision: SINGLE UNIFIED ENDPOINT**

```
✅ CORRECT APPROACH:
   POST /api/login (untuk SEMUA role)
   
❌ WRONG APPROACH:
   POST /api/login/student
   POST /api/login/instructor
   POST /api/login/parent
```

**Alasan:**
- ✅ Standard industry practice
- ✅ Lebih simple & maintainable
- ✅ Better security (tidak expose role structure)
- ✅ Frontend yang handle redirect berdasarkan role
- ✅ Sudah implemented dengan benar di AuthController

**Response Structure:**
```json
{
  "user": { "id": 1, "role": "student", ... },
  "profile": { ... role-specific data ... },
  "access_token": "1|abc123..."
}
```

**Frontend Redirect Logic:**
```javascript
if (response.user.role === 'student') {
  navigate('/student/dashboard');
} else if (response.user.role === 'instructor') {
  navigate('/instructor/dashboard');
} else if (response.user.role === 'parent') {
  navigate('/parent/dashboard');
} else if (response.user.role === 'admin') {
  navigate('/admin/dashboard');
}
```

---

### 2. Registration System

**Decision: ROLE-BASED REGISTRATION**

#### 🟢 Calon Siswa (Public - Self Registration)
```
POST /api/register-calon-siswa  ← Public, siapa saja bisa akses
POST /api/upload-documents      ← Protected, calon_siswa only
```

**Flow:**
1. Register basic info → role: `calon_siswa`
2. Upload documents → status: `pending_approval`
3. Admin approve → role changes to `student`

#### 🔴 Instructor (Admin Only)
```
POST /api/v1/instructors  ← Protected, admin only
```

**Reason:** Instructor = pegawai, tidak bisa self-register

#### 🔴 Parent (Admin Only)
```
POST /api/v1/parents  ← Protected, admin only
```

**Reason:** Parent di-create oleh admin setelah verifikasi

#### 🔴 Admin (Super Admin Only)
```
POST /api/v1/users (with role=admin)  ← Protected, admin only
```

**Reason:** Admin hanya bisa dibuat oleh admin lain

---

## 🛠️ Changes Made

### 1. Removed Dangerous Public Register Endpoint

**Before (❌ INSECURE):**
```php
Route::post('/register', [AuthController::class, 'register']);
// Siapa saja bisa register sebagai admin/instructor!
```

**After (✅ SECURE):**
```php
// Endpoint dihapus dari public routes
// Method register() di-deprecate dengan response 410 Gone
```

### 2. Updated AuthController

**Changes:**
```php
public function register(Request $request)
{
    // Now returns 410 Gone with helpful message
    return response()->json([
        'message' => 'This endpoint is deprecated.',
        'available_endpoints' => [
            'student_registration' => 'POST /api/register-calon-siswa (public)',
            'instructor_creation' => 'POST /api/v1/instructors (admin only)',
            'parent_creation' => 'POST /api/v1/parents (admin only)',
            'admin_creation' => 'POST /api/v1/users (admin only)',
        ]
    ], 410);
}
```

---

## 📊 Complete Flow Comparison

### ❌ OLD APPROACH (Insecure)
```
Anyone can register with any role:
POST /api/register { role: "admin" }  ← BAHAYA!

Anyone can login:
POST /api/login/student
POST /api/login/instructor
POST /api/login/parent
```

### ✅ NEW APPROACH (Secure)
```
Public Registration:
POST /api/register-calon-siswa  ← Only for students
POST /api/upload-documents      ← Upload docs

Admin Creates:
POST /api/v1/instructors  ← Admin creates instructor
POST /api/v1/parents      ← Admin creates parent
POST /api/v1/users        ← Admin creates admin

Universal Login:
POST /api/login  ← Everyone login here
```

---

## 🔐 Security Benefits

### 1. No Role Enumeration
```
❌ Bad: /api/login/student exists → attacker knows student login URL
✅ Good: /api/login → attacker doesn't know role structure
```

### 2. No Unauthorized Registration
```
❌ Bad: POST /api/register { role: "admin" }
✅ Good: Only admin can create other admins
```

### 3. Clear Access Control
```
Public:     /api/register-calon-siswa
Protected:  /api/upload-documents (calon_siswa)
Admin:      /api/v1/instructors (admin only)
```

---

## 📱 Frontend Implementation

### Login Component
```javascript
// Single login form for all roles
const handleLogin = async (email, password) => {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  // Store token
  localStorage.setItem('token', data.access_token);
  localStorage.setItem('user', JSON.stringify(data.user));
  
  // Role-based redirect
  redirectByRole(data.user.role);
};
```

### Registration Component
```javascript
// Only for calon siswa
const handleRegister = async (formData) => {
  const response = await fetch('/api/register-calon-siswa', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  });
  
  const data = await response.json();
  
  if (data.success) {
    // Redirect to document upload
    navigate('/upload-documents');
  }
};
```

### Admin Create Instructor
```javascript
// Admin dashboard only
const createInstructor = async (formData) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('/api/v1/instructors', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(formData)
  });
  
  return response.json();
};
```

---

## 🎭 Role Matrix

| Feature | Calon Siswa | Student | Instructor | Parent | Admin |
|---------|-------------|---------|------------|--------|-------|
| Self Register | ✅ | ❌ | ❌ | ❌ | ❌ |
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| Upload Docs | ✅ | ❌ | ❌ | ❌ | ❌ |
| Enroll Course | ❌ | ✅ | ❌ | ❌ | ✅ |
| Create Course | ❌ | ❌ | ✅ | ❌ | ✅ |
| View Child Grades | ❌ | ❌ | ❌ | ✅ | ✅ |
| Approve Registration | ❌ | ❌ | ❌ | ❌ | ✅ |
| Create Users | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 📝 API Endpoints Summary

### Public (No Auth)
```
POST /api/login                    - Universal login
POST /api/register-calon-siswa     - Student registration
POST /api/forgot-password          - Password reset request
POST /api/reset-password           - Password reset confirm
```

### Protected (Calon Siswa)
```
POST /api/upload-documents         - Upload registration docs
GET  /api/registration-status      - Check status
```

### Protected (All Authenticated)
```
GET  /api/user                     - Current user
POST /api/logout                   - Logout
POST /api/change-password          - Change password
```

### Protected (Admin Only)
```
POST /api/v1/instructors           - Create instructor
POST /api/v1/parents               - Create parent
POST /api/v1/users                 - Create admin
GET  /api/v1/registrations/pending - Get pending registrations
POST /api/v1/registrations/{id}/approve - Approve registration
POST /api/v1/registrations/{id}/reject  - Reject registration
```

---

## ✅ Testing Checklist

### Login Tests
- [ ] Login with valid student credentials → redirect to /student/dashboard
- [ ] Login with valid instructor credentials → redirect to /instructor/dashboard
- [ ] Login with valid parent credentials → redirect to /parent/dashboard
- [ ] Login with valid admin credentials → redirect to /admin/dashboard
- [ ] Login with invalid credentials → return 422 error
- [ ] Token is stored in localStorage
- [ ] Profile data is loaded based on role

### Registration Tests
- [ ] Register as calon_siswa with valid data → success
- [ ] Upload documents with valid files → success
- [ ] Check registration status → returns correct status
- [ ] Admin can see pending registrations
- [ ] Admin can approve registration → role changes to student
- [ ] Admin can reject registration → status becomes rejected
- [ ] Cannot register as instructor/parent via public endpoint
- [ ] Only admin can create instructor/parent

### Security Tests
- [ ] Cannot access /api/register anymore (removed)
- [ ] Cannot POST to /api/v1/instructors without admin token
- [ ] Cannot POST to /api/v1/parents without admin token
- [ ] Cannot approve registration without admin token
- [ ] Token is required for protected endpoints
- [ ] Invalid token returns 401

---

## 🚀 Next Steps

1. **Update Frontend:**
   - Remove multiple login pages (if any)
   - Create single login page
   - Add role-based routing
   - Implement ProtectedRoute component

2. **Add Middleware:**
   - Create role-checking middleware
   - Apply to admin-only routes
   - Test authorization

3. **Create Documentation:**
   - API documentation for frontend team
   - Postman collection
   - Integration examples

4. **Testing:**
   - Unit tests for AuthController
   - Integration tests for registration flow
   - E2E tests for complete user journeys

---

## 📚 Related Documentation

- [Complete Auth Flow](./AUTH-AND-REGISTRATION.md)
- [Flow Diagrams](./AUTH-FLOW-DIAGRAM.md)
- [ERD Database Structure](../ERD-SmartDev-LMS.md)

---

**Summary:**
✅ Single login endpoint for all roles  
✅ Public registration only for students  
✅ Admin creates instructors, parents, admins  
✅ Secure by default  
✅ Simple frontend implementation  

**Last Updated:** January 2025