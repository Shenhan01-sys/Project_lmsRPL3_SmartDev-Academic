/**
 * Instructor Dashboard API Helper - REFACTORED
 * Matches Laravel Project Structure
 */

const NGROK_BASE = 'https://loraine-seminiferous-snappily.ngrok-free.dev'; // Sesuaikan URL Ngrok kamu
const API_BASE = NGROK_BASE + '/api/v1';

function getToken() {
    return localStorage.getItem('auth_token');
}

function getHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${getToken()}`,
        'ngrok-skip-browser-warning': 'true'
    };
}

async function handleResponse(response) {
    if (!response.ok) {
        if (response.status === 401) {
            console.warn('Unauthorized, redirecting to login...');
            // window.location.href = '/login.html'; // Uncomment jika sudah ada page login
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP ${response.status}`);
    }
    // Handle 204 No Content (biasanya delete)
    if (response.status === 204) return null;
    return await response.json();
}

const InstructorAPI = {
    // --- AUTH & USER ---
    getCurrentUser: () => fetch(NGROK_BASE + '/api/user', {
        headers: getHeaders()
    }).then(handleResponse),

    // --- COURSES ---
    // Get All Courses (with Instructor info)
    getCourses: () => fetch(`${API_BASE}/courses`, {
        headers: getHeaders()
    }).then(handleResponse),

    // Get Single Course Detail (Termasuk Enrollments, Modules, Assignments)
    // Endpoint ini PENTING untuk fitur "Assignments per Course"
    getCourseDetail: (id) => fetch(`${API_BASE}/courses/${id}`, {
        headers: getHeaders()
    }).then(handleResponse),

    createCourse: (data) => fetch(`${API_BASE}/courses`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    updateCourse: (id, data) => fetch(`${API_BASE}/courses/${id}`, {
        method: 'PUT',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    deleteCourse: (id) => fetch(`${API_BASE}/courses/${id}`, {
        method: 'DELETE',
        headers: getHeaders()
    }).then(handleResponse),

    // --- ASSIGNMENTS ---
    // Get ALL Assignments (Global List)
    getAllAssignments: () => fetch(`${API_BASE}/assignments`, {
        headers: getHeaders()
    }).then(handleResponse),

    // Create Assignment
    // Payload harus ada: course_id, title, description, dll.
    createAssignment: (data) => fetch(`${API_BASE}/assignments`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    // Get Detail Assignment (Termasuk Submissions)
    // Backend: $assignment->load('submissions.student')
    getAssignmentDetail: (id) => fetch(`${API_BASE}/assignments/${id}`, {
        headers: getHeaders()
    }).then(handleResponse),

    updateAssignment: (id, data) => fetch(`${API_BASE}/assignments/${id}`, {
        method: 'PUT',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    deleteAssignment: (id) => fetch(`${API_BASE}/assignments/${id}`, {
        method: 'DELETE',
        headers: getHeaders()
    }).then(handleResponse),

    // --- GRADING (SUBMISSIONS) ---
    // Grade Submission
    // Endpoint ini asumsinya kamu pakai GradingController atau update Submission langsung?
    // Berdasarkan routes/api.php kamu: Route::apiResource('grades', GradeController::class)
    // Tapi grading biasanya menempel ke submission atau enrollment.
    // Mari gunakan endpoint '/api/v1/grades/bulk' atau create grade manual.
    // Untuk kesederhanaan, kita asumsikan update submission score dulu jika ada fiturnya,
    // Jika tidak, kita pakai GradeController.

    // Cek SubmissionController kamu: tidak ada method khusus 'grade'.
    // Jadi kita harus buat Grade entry baru via GradeController.
    createGrade: (data) => fetch(`${API_BASE}/grades`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    // --- ATTENDANCE ---
    // Get Sessions by Course
    getSessionsByCourse: (courseId) => fetch(`${API_BASE}/attendance-sessions/course/${courseId}/all`, {
        headers: getHeaders()
    }).then(handleResponse),

    createSession: (data) => fetch(`${API_BASE}/attendance-sessions`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    // Mark Attendance (Single Student)
    markAttendance: (sessionId, enrollmentId, status) => fetch(`${API_BASE}/attendance-records/mark/${sessionId}/${enrollmentId}`, {
        method: 'POST', // Cek route: Route::post("attendance-records/mark/{sessionId}/{enrollmentId}"...)
        headers: getHeaders(),
        body: JSON.stringify({ status: status }) // Body mungkin perlu status jika controller butuh
    }).then(handleResponse),

    // --- ANNOUNCEMENTS ---
    createAnnouncement: (data) => fetch(`${API_BASE}/announcements`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify(data)
    }).then(handleResponse),

    publishAnnouncement: (id) => fetch(`${API_BASE}/announcements/${id}/publish`, {
        method: 'POST',
        headers: getHeaders()
    }).then(handleResponse),
};
