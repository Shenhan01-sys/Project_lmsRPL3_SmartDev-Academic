# 📊 Entity Relationship Diagram (ERD) - SmartDev Academic LMS

## 🗃️ **Database Structure Overview**

SmartDev Academic LMS menggunakan **12 tabel utama** dengan struktur yang **normalized** dan **well-designed relationships**.

### 📋 **Core Tables**

#### 1. **users** (Authentication & User Management)
```sql
- id (PK)
- name, email, password
- role: ENUM('student', 'instructor', 'admin')
- level: ENUM('SMP', 'SMA')
- parent_id (FK → parents.id)
- profile_photo_path
```

#### 2. **student_registrations** (Registration Workflow)
```sql
- id (PK)
- user_id (FK → users.id)
- Personal: tanggal_lahir, tempat_lahir, jenis_kelamin
- Parent: nama_orang_tua, phone_orang_tua, alamat_orang_tua
- Documents: ktp_orang_tua_path, ijazah_path, foto_siswa_path, bukti_pembayaran_path
- Workflow: registration_status, submitted_at, approved_at, approval_notes
- approved_by (FK → users.id)
```

#### 3. **parents** (Guardian Information)
```sql
- id (PK)
- name, email, phone, address
```

#### 4. **courses** (Academic Courses)
```sql
- id (PK)
- course_code (UNIQUE), course_name, description
- instructor_id (FK → users.id)
```

#### 5. **course_modules** (Course Content Structure)
```sql
- id (PK)
- course_id (FK → courses.id)
- module_name, description, order
```

#### 6. **materials** (Learning Materials)
```sql
- id (PK)
- course_module_id (FK → course_modules.id)
- title, content, material_type, file_path, order
```

#### 7. **enrollments** (Student-Course Registration)
```sql
- id (PK)
- student_id (FK → users.id)
- course_id (FK → courses.id)
- UNIQUE(student_id, course_id)
```

#### 8. **assignments** (Course Tasks)
```sql
- id (PK)
- course_id (FK → courses.id)
- title, description, due_date
```

#### 9. **submissions** (Assignment Submissions)
```sql
- id (PK)
- student_id (FK → users.id)
- assignment_id (FK → assignments.id)
- submission_text, file_path, submitted_at, score, feedback
```

#### 10. **grade_components** (Assessment Components)
```sql
- id (PK)
- course_id (FK → courses.id)
- component_name, component_type, max_score, weight, description
```

#### 11. **grades** (Student Grades)
```sql
- id (PK)
- student_id (FK → users.id)
- grade_component_id (FK → grade_components.id)
- score, max_score, notes, graded_at
- graded_by (FK → users.id)
- UNIQUE(student_id, grade_component_id)
```

### 🔐 **Authentication Tables**

#### 12. **personal_access_tokens** (API Authentication)
#### 13. **sessions** (Web Sessions)
#### 14. **password_reset_tokens** (Password Recovery)

## 🔗 **Key Relationships**

### **1:Many Relationships**
- **users** → **student_registrations** (1 user has 1 registration)
- **users** → **courses** (1 instructor teaches many courses)
- **courses** → **course_modules** (1 course has many modules)
- **course_modules** → **materials** (1 module has many materials)
- **courses** → **assignments** (1 course has many assignments)
- **assignments** → **submissions** (1 assignment has many submissions)
- **courses** → **grade_components** (1 course has many grade components)
- **grade_components** → **grades** (1 component has many grades)

### **Many:Many Relationships**
- **users** ↔ **courses** (through **enrollments**)
  - Students can enroll in multiple courses
  - Courses can have multiple students

### **Self-Referencing Relationships**
- **users** → **users** (student → parent via parent_id)
- **users** → **student_registrations** (admin approves via approved_by)
- **users** → **grades** (instructor grades via graded_by)

## 🎯 **Database Design Highlights**

### ✅ **Normalization Benefits**
1. **Separation of Concerns**: Authentication vs Registration data
2. **Data Integrity**: Foreign key constraints
3. **Scalability**: Modular course structure
4. **Flexibility**: Configurable grade components

### ✅ **Business Logic Implementation**
1. **Registration Workflow**: Status tracking with approval system
2. **Academic Structure**: Hierarchical course → modules → materials
3. **Assessment System**: Flexible grading with multiple components
4. **Role-Based Access**: Student, Instructor, Admin roles

### ✅ **Performance Optimizations**
1. **Indexes**: On foreign keys and frequently queried fields
2. **Unique Constraints**: Prevent duplicate enrollments/grades
3. **Efficient Queries**: Well-structured relationships

## 🔐 **Enrollment Validation**

### **Business Logic Implementation**

Untuk menjaga **data integrity**, sistem mengimplementasikan validasi enrollment di application layer:

#### **EnrollmentService**
Centralized service untuk validasi apakah student sudah enrolled di course sebelum:
- ✅ Submit assignment (`SubmissionController`)
- ✅ Menerima/input nilai (`GradeController`)
- ✅ Akses assignment details (`AssignmentController`)
- ✅ Akses materials & modules (`MaterialController`, `CourseModuleController`)

#### **Validation Points**
1. **submissions** → Cek enrollment via `assignments.course_id`
2. **grades** → Cek enrollment via `grade_components.course_id`
3. **materials** → Cek enrollment via `course_modules.course_id`
4. **assignments** → Filter by enrolled courses

#### **Implementation**
- **Location**: `app/Services/EnrollmentService.php`
- **Documentation**: `docs/ENROLLMENT_VALIDATION.md`
- **Controllers Updated**: 
  - `SubmissionController` - Submit & update validations
  - `GradeController` - Individual & bulk grade input validations
  - `AssignmentController` - Index filtering & show validation

**Benefits:**
- ✅ Prevents unauthorized access to course materials
- ✅ Ensures accurate grade reporting
- ✅ Maintains referential integrity at business logic level
- ✅ Centralized, maintainable, and testable

---

**📁 Files:**
- `ERD-SmartDev-LMS.puml` - PlantUML diagram source
- `ERD-SmartDev-LMS.md` - This documentation
- `ENROLLMENT_VALIDATION.md` - Enrollment validation documentation
- `ENROLLMENT_VALIDATION_SUMMARY.md` - Quick reference guide

**🛠️ Tools to View:**
- PlantUML online editor
- VS Code PlantUML extension
- Draw.io (import PlantUML)

**📊 Generated from:** SmartDev Academic LMS Database Structure (Laravel 12.x)