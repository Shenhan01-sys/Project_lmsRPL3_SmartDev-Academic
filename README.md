# 📚 API Documentation - SmartDev LMS

> Comprehensive API documentation for SmartDev Academic Learning Management System

## 📖 Documentation Index

### 🔐 Authentication & Authorization
- **[Auth & Registration Guide](./AUTH-AND-REGISTRATION.md)** - Complete authentication and registration flow documentation
- **[Auth Flow Diagrams](./AUTH-FLOW-DIAGRAM.md)** - Visual diagrams for login, registration, and role-based flows
- **[Quick Reference](./QUICK-REFERENCE.md)** - Quick decision guide and API endpoint summary

### 📊 Database & Architecture
- **[ERD Database Structure](../ERD-SmartDev-LMS.md)** - Entity Relationship Diagram and database schema
- **[DFD Diagrams](../)** - Data Flow Diagrams for system processes

---

## 🎯 Key Design Decisions

### ✅ Authentication Strategy

**Single Unified Login Endpoint**
```
POST /api/login
```
- One endpoint for all roles (student, instructor, parent, admin)
- Frontend handles role-based redirection
- Backend returns role-specific profile data
- Token-based authentication using Laravel Sanctum

**Benefits:**
- ✅ Industry standard approach
- ✅ Better security (no role enumeration)
- ✅ Simpler frontend implementation
- ✅ Easier to maintain

### ✅ Registration Strategy

**Role-Based Registration**

| Role | Registration Method | Endpoint | Access |
|------|-------------------|----------|--------|
| **Calon Siswa** | Self-registration | `POST /api/register-calon-siswa` | Public |
| **Instructor** | Admin creates | `POST /api/v1/instructors` | Admin only |
| **Parent** | Admin creates | `POST /api/v1/parents` | Admin only |
| **Admin** | Super admin creates | `POST /api/v1/users` | Admin only |

**Benefits:**
- ✅ Secure by default (no unauthorized registrations)
- ✅ Clear access control
- ✅ Proper workflow for each role
- ✅ Admin oversight for sensitive roles

---

## 🚀 Quick Start

### 1. Login (All Roles)

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student"
  },
  "profile": {
    "student_number": "2024001",
    "current_grade": "10"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

**Frontend Implementation:**
```javascript
// Store token
localStorage.setItem('token', response.access_token);

// Redirect based on role
if (response.user.role === 'student') {
  navigate('/student/dashboard');
} else if (response.user.role === 'instructor') {
  navigate('/instructor/dashboard');
}
// etc...
```

### 2. Register Student (Public)

**Endpoint:** `POST /api/register-calon-siswa`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "tanggal_lahir": "2005-05-15",
  "tempat_lahir": "Jakarta",
  "jenis_kelamin": "L",
  "nama_orang_tua": "Jane Doe",
  "phone_orang_tua": "081234567899",
  "alamat_orang_tua": "Jl. Contoh No. 123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "user": {
    "id": 1,
    "role": "calon_siswa"
  },
  "next_step": "upload_documents"
}
```

### 3. Authenticated Requests

**All protected endpoints require the Bearer token:**

```javascript
fetch('/api/v1/courses', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
```

---

## 📋 API Endpoint Categories

### 🔓 Public Endpoints (No Authentication)
```
POST /api/login                    - Universal login
POST /api/register-calon-siswa     - Student registration
POST /api/forgot-password          - Password reset request
POST /api/reset-password           - Password reset confirmation
```

### 🔐 Protected Endpoints (Authentication Required)

#### General
```
GET  /api/user                     - Get current user
POST /api/logout                   - Logout
POST /api/change-password          - Change password
```

#### Registration Flow (Calon Siswa)
```
POST /api/upload-documents         - Upload registration documents
GET  /api/registration-status      - Check registration status
```

#### Academic Resources
```
GET    /api/v1/courses             - List courses
POST   /api/v1/courses             - Create course
GET    /api/v1/courses/{id}        - Get course details
PUT    /api/v1/courses/{id}        - Update course
DELETE /api/v1/courses/{id}        - Delete course

GET    /api/v1/course-modules      - List modules
POST   /api/v1/course-modules      - Create module
...

GET    /api/v1/materials           - List materials
POST   /api/v1/materials           - Create material
...

GET    /api/v1/assignments         - List assignments
POST   /api/v1/assignments         - Create assignment
...

GET    /api/v1/submissions         - List submissions
POST   /api/v1/submissions         - Submit assignment
...
```

#### User Management (Admin Only)
```
GET    /api/v1/students            - List students
POST   /api/v1/students            - Create student
GET    /api/v1/students/{id}       - Get student details
PUT    /api/v1/students/{id}       - Update student
DELETE /api/v1/students/{id}       - Delete student

GET    /api/v1/instructors         - List instructors
POST   /api/v1/instructors         - Create instructor (Admin)
...

GET    /api/v1/parents             - List parents
POST   /api/v1/parents             - Create parent (Admin)
...
```

#### Registration Management (Admin Only)
```
GET  /api/v1/registrations/pending        - Get pending registrations
GET  /api/v1/registrations                - Get all registrations
POST /api/v1/registrations/{id}/approve   - Approve registration
POST /api/v1/registrations/{id}/reject    - Reject registration
```

#### Grading System
```
GET  /api/v1/grade-components      - List grade components
POST /api/v1/grade-components      - Create grade component
...

GET  /api/v1/grades                - List grades
POST /api/v1/grades                - Create grade
POST /api/v1/grades/bulk           - Bulk create grades
GET  /api/v1/grades/student        - Get student grades
GET  /api/v1/grades/course         - Get course grades
...
```

---

## 🎭 Role-Based Access Control

### Access Matrix

| Feature | Calon Siswa | Student | Instructor | Parent | Admin |
|---------|-------------|---------|------------|--------|-------|
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| Self Register | ✅ | ❌ | ❌ | ❌ | ❌ |
| Upload Docs | ✅ | ❌ | ❌ | ❌ | ❌ |
| Enroll Courses | ❌ | ✅ | ❌ | ❌ | ✅ |
| Access Materials | ❌ | ✅ | ✅ | ❌ | ✅ |
| Submit Assignments | ❌ | ✅ | ❌ | ❌ | ❌ |
| Create Courses | ❌ | ❌ | ✅ | ❌ | ✅ |
| Grade Students | ❌ | ❌ | ✅ | ❌ | ✅ |
| View Child Grades | ❌ | ❌ | ❌ | ✅ | ✅ |
| Approve Registrations | ❌ | ❌ | ❌ | ❌ | ✅ |
| Create Users | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 🔒 Security Features

### 1. Token-Based Authentication
- **Laravel Sanctum** for API authentication
- **Token rotation** on login (old tokens revoked)
- **Stateless** authentication for scalability

### 2. Role-Based Authorization
- **Middleware** checks at route level
- **Policies** for resource-level authorization
- **Query scopes** for data filtering

### 3. Input Validation
- **Server-side validation** for all inputs
- **File type and size validation**
- **SQL injection prevention** (Eloquent ORM)
- **XSS prevention** (auto-escaped outputs)

### 4. Password Security
- **Bcrypt hashing** for passwords
- **Minimum 8 characters** requirement
- **Password confirmation** on registration
- **Password reset** via email token

### 5. File Upload Security
- **Allowed types:** jpg, png, pdf only
- **Max sizes:** 2MB (documents), 1MB (photos)
- **Secure storage:** Outside public directory
- **Randomized filenames** to prevent guessing

---

## 🔄 Registration Workflow

```
1. Calon Siswa Register
   POST /api/register-calon-siswa
   ↓
   Status: pending_documents
   Role: calon_siswa

2. Upload Documents
   POST /api/upload-documents
   ↓
   Status: pending_approval
   Role: calon_siswa (still)

3. Admin Review
   GET /api/v1/registrations/pending
   ↓
   Admin sees pending registration

4a. Admin Approve
    POST /api/v1/registrations/{id}/approve
    ↓
    Status: approved
    Role: student ← CHANGED!

4b. Admin Reject
    POST /api/v1/registrations/{id}/reject
    ↓
    Status: rejected
    Role: calon_siswa (unchanged)
```

---

## 📱 Frontend Integration Guide

### Setup Axios Interceptor
```javascript
import axios from 'axios';

// Add token to all requests
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 responses
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### Protected Route Component (React)
```javascript
function ProtectedRoute({ children, allowedRoles }) {
  const user = JSON.parse(localStorage.getItem('user'));
  
  if (!user) {
    return <Navigate to="/login" />;
  }
  
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/unauthorized" />;
  }
  
  return children;
}

// Usage
<Route 
  path="/student/dashboard" 
  element={
    <ProtectedRoute allowedRoles={['student']}>
      <StudentDashboard />
    </ProtectedRoute>
  } 
/>
```

---

## 🧪 Testing

### Manual Testing with cURL

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@example.com","password":"password123"}'
```

**Authenticated Request:**
```bash
curl -X GET http://localhost:8000/api/v1/courses \
  -H "Authorization: Bearer 1|abc123..."
```

### Postman Collection
Import the provided Postman collection for easy testing of all endpoints.

---

## 📊 Response Format

### Success Response
```json
{
  "data": { ... },
  "message": "Success message"
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

### Pagination Response
```json
{
  "data": [ ... ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

---

## 🎯 Best Practices

### 1. Always Use HTTPS in Production
```
https://api.smartdevlms.com/api/login
```

### 2. Store Tokens Securely
```javascript
// ✅ Good: httpOnly cookies (best)
// ✅ Good: localStorage (acceptable for SPAs)
// ❌ Bad: regular cookies without httpOnly
// ❌ Bad: storing in plain JavaScript variables
```

### 3. Handle Token Expiration
```javascript
// Check token validity before requests
if (isTokenExpired(token)) {
  refreshToken() || redirectToLogin();
}
```

### 4. Validate on Frontend AND Backend
```javascript
// Frontend: Immediate feedback
// Backend: Security enforcement
```

### 5. Use Proper HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 📚 Additional Resources

- **Laravel Sanctum:** https://laravel.com/docs/sanctum
- **RESTful API Design:** https://restfulapi.net/
- **HTTP Status Codes:** https://httpstatuses.com/

---

## 🆘 Common Issues & Solutions

### Issue: 401 Unauthorized
**Solution:** Check if token is included in Authorization header

### Issue: 403 Forbidden
**Solution:** User doesn't have permission for this resource (check role)

### Issue: 419 CSRF Token Mismatch
**Solution:** Get CSRF cookie first: `GET /sanctum/csrf-cookie`

### Issue: CORS Error
**Solution:** Configure CORS in `config/cors.php`

---

## 📞 Support

For questions or issues:
1. Check the documentation files in this folder
2. Review the code examples
3. Test endpoints using Postman collection
4. Contact the backend team

---

**Version:** 1.0  
**Last Updated:** January 2025  
**Maintained by:** SmartDev LMS Backend Team