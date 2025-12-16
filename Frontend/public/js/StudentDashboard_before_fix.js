// --- CONFIGURATION ---
// Gunakan URL ini untuk server online (Hosting)
const API_BASE_URL = "https://portohansgunawan.my.id/api";

// Gunakan URL ini untuk server lokal (php artisan serve)
// const API_BASE_URL = 'http://127.0.0.1:8000/api';

const API_V1 = API_BASE_URL + "/v1";

// --- STATE ---
let currentUser = null;
let myCourses = [];
let myAssignments = [];
let myGrades = [];
let mySubmissions = [];
let myAttendanceHistory = [];
let myCertificates = [];

let currentCourseId = null;

// --- UTILS ---
document.getElementById("current-date").innerText =
    new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });

// --- INIT ---
document.addEventListener("DOMContentLoaded", initApp);

async function initApp() {
    const token = localStorage.getItem("auth_token");

    // 1. Cek Keberadaan Token - Langsung redirect jika tidak ada
    if (!token) {
        window.location.replace("/login");
        return;
    }

    document.getElementById("loading-overlay").style.display = "flex";

    try {
        // 2. Load data dari current_user (dari login)
        const currentUserData = localStorage.getItem("current_user");

        if (!currentUserData || currentUserData === "undefined") {
            throw new Error("Data tidak valid, silakan login ulang");
        }

        // Parse data login
        const loginData = JSON.parse(currentUserData);

        // Structure data sesuai dengan response login
        currentUser = loginData.user;
        currentUser.profile = loginData.profile;

        // 3. VALIDASI ROLE - Pastikan user adalah student
        if (currentUser.role !== "student") {
            await Swal.fire({
                icon: "error",
                title: "Akses Ditolak",
                text: `Akun Anda adalah '${currentUser.role}'. Halaman ini khusus untuk Student.`,
                confirmButtonText: "OK",
            });
            localStorage.removeItem("auth_token");
            localStorage.removeItem("current_user");
            window.location.replace("/login");
            return;
        }

        // Pastikan relasi student ada (fallback safety)
        if (!currentUser.student) {
            currentUser.student = { id: currentUser.id };
        }

        updateProfileUI();

        // 4. Load Data Aplikasi
        await loadCourses();

        await Promise.all([
            loadAssignments(),
            loadGrades(),
            loadGlobalAttendance(),
            loadCertificates(),
        ]);

        renderDashboard();
    } catch (error) {
        console.error("Init Error:", error);

        // Handler Otomatis untuk Error
        if (
            error.message.includes("tidak valid") ||
            error.message.includes("Token") ||
            error.message.includes("Unauthorized") ||
            error.message.includes("401")
        ) {
            await Swal.fire({
                icon: "error",
                title: "Sesi Berakhir",
                text: "Sesi Anda telah berakhir. Silakan login kembali.",
                confirmButtonText: "OK",
            });
            localStorage.removeItem("auth_token");
            localStorage.removeItem("current_user");
            window.location.replace("/login");
        } else {
            // Error lain (misal koneksi)
            await Swal.fire({
                icon: "error",
                title: "Terjadi Kesalahan",
                text: error.message,
                confirmButtonText: "OK",
            });
        }
    } finally {
        document.getElementById("loading-overlay").style.display = "none";
    }
}

// --- API CLIENT ---
async function fetchApi(endpoint, options = {}) {
    const token = localStorage.getItem("auth_token");

    // Construct URL
    let url = endpoint.startsWith("http")
        ? endpoint
        : endpoint.startsWith("/user")
          ? API_BASE_URL + endpoint
          : API_V1 + endpoint;

    const headers = {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        "ngrok-skip-browser-warning": "true",
        ...options.headers,
    };

    try {
        const res = await fetch(url, { ...options, headers });

        // DETEKSI 401 UNAUTHORIZED
        if (res.status === 401) {
            throw new Error(
                "Sesi kedaluwarsa atau Token tidak valid (Unauthorized).",
            );
        }

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || `API Error: ${res.status}`);
        }

        return await res.json();
    } catch (err) {
        console.error(`Fetch Error (${endpoint}):`, err);
        throw err;
    }
}

// --- DATA LOADERS ---

async function loadCourses() {
    try {
        const enrollments = await fetchApi("/enrollments");
        const list = Array.isArray(enrollments)
            ? enrollments
            : enrollments.data || [];

        myCourses = list.map((e) => e.course).filter((c) => c !== null);
        renderCourses();
    } catch (e) {
        console.error("Load Courses Failed:", e);
        await Swal.fire({
            icon: "error",
            title: "Gagal Memuat Kursus",
            text: "Terjadi kesalahan saat memuat data kursus.",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
        });
    }
}

async function loadAssignments() {
    try {
        const res = await fetchApi("/assignments");
        const all = Array.isArray(res) ? res : res.data || [];
        // Filter tugas yang sesuai dengan course yang diikuti student
        const myCourseIds = myCourses.map((c) => c.id);
        myAssignments = all.filter((a) => myCourseIds.includes(a.course_id));
        renderAssignments(myAssignments);
    } catch (e) {
        console.error(e);
    }
}

async function loadGrades() {
    try {
        const studentId = currentUser.profile?.id || currentUser.id;

        if (myCourses.length === 0) {
            myGrades = [];
            renderGrades();
            return;
        }

        const promises = myCourses.map(async (course) => {
            try {
                const res = await fetchApi(
                    `/grades/student?student_id=${studentId}&course_id=${course.id}`,
                );
                const grades = (res.data?.grades || []).map((g) => ({
                    ...g,
                    course_name: course.course_name,
                }));
                return grades;
            } catch (err) {
                console.error(
                    `Failed to load grades for course ${course.id}`,
                    err,
                );
                return [];
            }
        });

        const results = await Promise.all(promises);
        myGrades = results.flat();
        renderGrades();
    } catch (e) {
        console.error(e);
    }
}

async function loadGlobalAttendance() {
    try {
        const studentId = currentUser.profile?.id || currentUser.id;
        myAttendanceHistory = [];

        if (myCourses.length > 0) {
            const history = await fetchApi(
                `/attendance-records/student/${studentId}/course/${myCourses[0].id}/history`,
            );
            myAttendanceHistory = Array.isArray(history)
                ? history
                : history.data || [];
        }
        renderAttendanceHistory();
    } catch (e) {
        console.error(e);
    }
}

async function loadCertificates() {
    try {
        const res = await fetchApi("/certificates");
        // Filter certificate milik student ini (jika backend return semua)
        // Asumsi backend sudah filter by user
        myCertificates = Array.isArray(res) ? res : res.data || [];
        renderCertificates();
    } catch (e) {
        console.error(e);
    }
}

// --- RENDERERS ---

function renderDashboard() {
    // Stats
    document.getElementById("stat-courses").innerText = myCourses.length;

    const pending = myAssignments.filter(
        (a) => a.status !== "submitted" && a.status !== "graded",
    ).length;
    document.getElementById("stat-pending").innerText = pending;

    // Recent Assignments
    const recent = myAssignments.slice(0, 3);
    document.getElementById("dashboard-assignments-list").innerHTML =
        recent
            .map(
                (a) => `
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-2 rounded text-primary"><i class="bi bi-journal-text"></i></div>
                <div>
                    <h4 class="font-bold text-sm text-slate-800 line-clamp-1">${a.title}</h4>
                    <p class="text-xs text-slate-500">${a.course?.course_name || "Course " + a.course_id}</p>
                </div>
            </div>
            <span class="text-xs font-bold ${getStatusClass(a.status)}">${a.status}</span>
        </div>
    `,
            )
            .join("") ||
        '<p class="text-center text-slate-400 text-sm py-2">Belum ada tugas.</p>';
}

function renderCourses() {
    const container = document.getElementById("courses-container");
    container.innerHTML =
        myCourses
            .map(
                (c) => `
        <div class="dashboard-card group hover:shadow-lg transition-all duration-300 cursor-pointer" onclick="showCourseDetail(${c.id})">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-primary flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-book"></i>
                </div>
                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded">${c.course_code}</span>
            </div>
            <h3 class="font-bold text-lg text-slate-800 mb-2 group-hover:text-primary transition-colors">${c.course_name}</h3>
            <p class="text-sm text-slate-500 mb-4 line-clamp-2">${c.description || "Tidak ada deskripsi."}</p>
            <div class="flex items-center gap-2 text-xs text-slate-400 border-t pt-3">
                <i class="bi bi-person-circle"></i>
                <span>${c.instructor?.full_name || "Instructor"}</span>
            </div>
        </div>
    `,
            )
            .join("") ||
        '<p class="col-span-full text-center text-slate-500 py-8">Belum ada kursus.</p>';
}

function renderAssignments(list) {
    const container = document.getElementById("assignments-container");
    container.innerHTML =
        list
            .map(
                (a) => `
        <div class="dashboard-card flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="bg-indigo-100 p-3 rounded-xl text-primary text-xl hidden md:block"><i class="bi bi-journal-text"></i></div>
                <div>
                    <h4 class="font-bold text-lg text-slate-800">${a.title}</h4>
                    <p class="text-sm text-slate-500 mb-1">${a.course?.course_name || "Course " + a.course_id}</p>
                    <div class="flex gap-3 text-xs text-slate-400">
                        <span><i class="bi bi-calendar"></i> Due: ${formatDate(a.due_date)}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 self-end md:self-center">
                <span class="badge ${getStatusClass(a.status)}">${a.status}</span>
                <button onclick="showAssignmentDetail(${a.id})" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    `,
            )
            .join("") ||
        '<p class="text-center text-slate-500 py-8">Tidak ada tugas.</p>';
}

function renderGrades() {
    const tbody = document.getElementById("grades-table-body");
    tbody.innerHTML =
        myGrades
            .map(
                (g) => `
        <tr class="bg-white border-b hover:bg-slate-50">
            <td class="px-6 py-4 font-medium text-slate-900">${g.course_name}</td>
            <td class="px-6 py-4">${g.grade_component?.name || "Komponen"}</td>
            <td class="px-6 py-4 text-center font-bold text-primary">${g.score}</td>
            <td class="px-6 py-4 text-center"><span class="badge badge-graded">Final</span></td>
        </tr>
    `,
            )
            .join("") ||
        '<tr><td colspan="4" class="text-center py-4 text-slate-500">Belum ada nilai.</td></tr>';
}

function renderAttendanceHistory() {
    const container = document.getElementById("attendance-history-container");
    const sorted = myAttendanceHistory.sort(
        (a, b) =>
            new Date(b.attendance_time || b.created_at) -
            new Date(a.attendance_time || a.created_at),
    );

    container.innerHTML =
        sorted
            .map(
                (a) => `
        <div class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-lg hover:shadow-sm transition-all">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-50 p-2 rounded text-center min-w-[50px]">
                    <span class="block text-xl font-bold text-indigo-700">${new Date(a.attendance_time || a.created_at).getDate()}</span>
                    <span class="block text-xs font-bold text-indigo-400 uppercase">${new Date(a.attendance_time || a.created_at).toLocaleString("default", { month: "short" })}</span>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-800">${a.course_name}</h4>
                    <p class="text-xs text-slate-500">${a.attendance_session?.session_name || "Sesi"}</p>
                </div>
            </div>
            <span class="badge ${getAttClass(a.status)}">${a.status}</span>
        </div>
    `,
            )
            .join("") ||
        '<p class="text-center text-slate-500 py-8">Belum ada riwayat kehadiran.</p>';
}

function renderCertificates() {
    const container = document.getElementById("certificates-container");
    container.innerHTML =
        myCertificates
            .map(
                (c) => `
        <div class="dashboard-card relative overflow-hidden group">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-primary text-2xl mb-4">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800 mb-1">${c.course?.course_name || "Certificate"}</h3>
                <p class="text-xs text-slate-500 mb-4">${c.certificate_code}</p>
                <button onclick="window.open('${API_BASE_URL}/certificates/verify/${c.certificate_code}', '_blank')" class="w-full py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-primary hover:text-white transition-colors">
                    Lihat Validitas
                </button>
            </div>
        </div>
    `,
            )
            .join("") ||
        '<p class="col-span-full text-center text-slate-500 py-8">Belum ada sertifikat.</p>';
}

// --- COURSE DETAIL LOGIC ---

async function showCourseDetail(courseId) {
    currentCourseId = courseId;
    document.getElementById("loading-overlay").style.display = "flex";

    try {
        const course = await fetchApi(`/courses/${courseId}`);
        renderCourseDetailHeader(course);

        // Fetch Modules (Using /my-modules for full content)
        const allMyModules = await fetchApi("/course-modules/my-modules");
        const courseModules = allMyModules.filter(
            (m) => m.course_id == courseId,
        );
        renderCourseModules(courseModules);

        const assigns = await fetchApi(`/assignments`);
        const courseAssigns = assigns.filter((a) => a.course_id == courseId);
        await renderCourseAssignments(courseAssigns);

        await loadCourseAttendance(courseId);

        const peopleRes = await fetchApi(`/enrollments?course_id=${courseId}`);
        const people = Array.isArray(peopleRes)
            ? peopleRes
            : peopleRes.data || [];
        const coursePeople = people.filter((p) => p.course_id == courseId);
        renderCoursePeople(coursePeople);

        switchView("course-detail");
    } catch (e) {
        console.error(e);
        await Swal.fire({
            icon: "error",
            title: "Gagal Memuat Detail",
            text: e.message,
            confirmButtonText: "OK",
        });
    } finally {
        document.getElementById("loading-overlay").style.display = "none";
    }
}

function renderCourseDetailHeader(course) {
    document.getElementById("detail-course-name").innerText =
        course.course_name;
    document.getElementById("detail-course-code").innerText =
        course.course_code;
    document.getElementById("detail-course-instructor").innerText =
        course.instructor?.user?.name || "-";
    document.getElementById("detail-course-desc").innerText =
        course.description;
}

function renderCourseModules(modules) {
    const container = document.getElementById("course-content-modules");
    container.innerHTML =
        modules
            .map((m) => {
                const materials = m.materials || [];
                return `
        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white mb-4">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center cursor-pointer" onclick="this.parentElement.classList.toggle('h-auto');">
                <h4 class="font-bold text-slate-800">${m.title || m.module_name}</h4>
            </div>
            <div class="p-4 space-y-3">
                <p class="text-sm text-slate-600">${m.description || ""}</p>
                <div class="space-y-2 border-t pt-2">
                    ${
                        materials
                            .map(
                                (mat) => `
                        <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg">
                            <i class="bi bi-file-earmark-text text-primary text-lg"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium">${mat.title}</p>
                                <p class="text-xs text-slate-400 uppercase">${mat.type}</p>
                            </div>
                            <a href="${mat.file_path}" target="_blank" class="px-3 py-1 text-xs font-bold text-primary bg-indigo-50 rounded">Buka</a>
                        </div>
                    `,
                            )
                            .join("") ||
                        '<p class="text-xs text-slate-400 italic">Tidak ada materi.</p>'
                    }
                </div>
            </div>
        </div>`;
            })
            .join("") ||
        '<p class="text-center py-8 text-slate-500">Belum ada modul.</p>';
}

async function renderCourseAssignments(list) {
    const container = document.getElementById("course-content-assignments");
    
    if (!list || list.length === 0) {
        container.innerHTML = '<p class="text-center py-4 text-slate-400">Tidak ada tugas.</p>';
        return;
    }

    await loadMySubmissions();
    
    const html = list.map(assignment => {
        const submission = mySubmissions.find(s => s.assignment_id === assignment.id);
        
        let statusBadge = '';
        let actionButton = '';
        
        if (submission) {
            const statusColors = { 'draft': 'bg-gray-200 text-gray-800', 'submitted': 'bg-blue-200 text-blue-800', 'graded': 'bg-green-200 text-green-800', 'returned': 'bg-yellow-200 text-yellow-800' };
            const statusLabels = { 'draft': 'Draft', 'submitted': 'Dikumpulkan', 'graded': 'Dinilai', 'returned': 'Dikembalikan' };
            
            statusBadge = `<span class="text-xs px-2 py-1 rounded font-bold ${statusColors[submission.status]}">${statusLabels[submission.status]}</span>`;
            
            if (submission.is_late) {
                statusBadge += ` <span class="text-xs px-2 py-1 rounded font-bold bg-red-100 text-red-700">Terlambat ${submission.late_days} hari</span>`;
            }
            
            if (submission.status === 'graded' && submission.grade !== null) {
                statusBadge += ` <span class="text-xs px-2 py-1 rounded font-bold bg-emerald-100 text-emerald-700">Nilai: ${submission.grade}</span>`;
            }
            
            if (submission.status === 'draft' || submission.status === 'returned') {
                actionButton = `<button onclick="openSubmitModal(${assignment.id}, ${submission.id})" class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700"><i class="bi bi-pencil"></i> Edit & Submit</button>`;
            } else if (submission.status === 'graded' && submission.feedback) {
                actionButton = `<button onclick="viewSubmissionDetail(${submission.id})" class="px-3 py-1 bg-green-600 text-white rounded text-xs font-bold hover:bg-green-700"><i class="bi bi-eye"></i> Lihat Detail</button>`;
            } else {
                actionButton = `<span class="text-xs text-slate-500">Menunggu penilaian...</span>`;
            }
        } else {
            statusBadge = '<span class="text-xs px-2 py-1 rounded font-bold bg-red-100 text-red-700">Belum Dikumpulkan</span>';
            actionButton = `<button onclick="openSubmitModal(${assignment.id})" class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700"><i class="bi bi-upload"></i> Submit</button>`;
        }
        
        const dueDate = new Date(assignment.due_date);
        const now = new Date();
        const isOverdue = dueDate < now && (!submission || submission.status === 'draft');
        const dueDateColor = isOverdue ? 'text-red-600 font-bold' : 'text-slate-600';
        
        return `<div class="dashboard-card p-4 mb-3"><div class="flex justify-between items-start mb-3"><div class="flex-1"><h4 class="font-bold text-slate-800 mb-1">${assignment.title}</h4><p class="text-xs ${dueDateColor} mb-2"><i class="bi bi-clock"></i> Due: ${formatDate(assignment.due_date)}${isOverdue ? '<span class="ml-2 text-red-600">⚠️ OVERDUE</span>' : ''}</p><p class="text-sm text-slate-600 mb-2">${assignment.description || 'Tidak ada deskripsi'}</p></div></div><div class="flex justify-between items-center pt-3 border-t border-slate-100"><div class="flex gap-2 items-center">${statusBadge}</div>${actionButton}</div></div>`;
    }).join('');
    
    container.innerHTML = html;
}

function renderCoursePeople(people) {
    const container = document.getElementById("course-content-people");
    const unique = [
        ...new Map(people.map((item) => [item.student_id, item])).values(),
    ];
    container.innerHTML = unique
        .map(
            (p) => `
        <div class="flex items-center gap-3 p-3 border rounded bg-white">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center"><i class="bi bi-person"></i></div>
            <div><p class="font-bold text-sm">${p.student?.user?.name || "Student"}</p></div>
        </div>
    `,
        )
        .join("");
}

async function loadCourseAttendance(courseId) {
    const container = document.getElementById("course-content-attendance");
    const sessions = await fetchApi(
        `/attendance-sessions?course_id=${courseId}`,
    );
    const studentId = currentUser.profile?.id || currentUser.id;
    const history = await fetchApi(
        `/attendance-records/student/${studentId}/course/${courseId}/history`,
    );

    let html = "";

    if (sessions.length > 0) {
        html += `<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-6">
            <h4 class="font-bold text-indigo-800 mb-3">Sesi Aktif</h4>
            ${sessions
                .map(
                    (s) => `
                <div class="bg-white p-3 rounded shadow-sm flex justify-between items-center mb-2">
                    <span>${s.session_name}</span>
                    <button onclick="submitAttendance(${s.id}, 'check-in')" class="px-3 py-1 bg-emerald-500 text-white rounded text-xs font-bold">Hadir</button>
                </div>
            `,
                )
                .join("")}
        </div>`;
    }

    html += `<h4 class="font-bold text-slate-800 mb-3">Riwayat</h4>
    <table class="w-full text-sm text-left text-slate-600">
        <thead class="bg-slate-50"><tr><th class="p-2">Sesi</th><th class="p-2">Status</th><th class="p-2">Waktu</th></tr></thead>
        <tbody>
            ${history
                .map(
                    (h) => `
                <tr class="border-b">
                    <td class="p-2">${h.attendance_session?.session_name}</td>
                    <td class="p-2"><span class="badge ${getAttClass(h.status)}">${h.status}</span></td>
                    <td class="p-2">${formatDate(h.attendance_time, true)}</td>
                </tr>
            `,
                )
                .join("")}
        </tbody>
    </table>`;

    container.innerHTML = html;
}

async function submitAttendance(sessionId, type) {
    try {
        await fetchApi(`/attendance-records/check-in/${sessionId}`, {
            method: "POST",
        });
        await Swal.fire({
            icon: "success",
            title: "Berhasil Check-in!",
            text: "Kehadiran Anda telah dicatat",
            timer: 2000,
            showConfirmButton: false,
        });
        loadCourseAttendance(currentCourseId);
    } catch (e) {
        await Swal.fire({
            icon: "error",
            title: "Gagal Check-in",
            text: e.message,
            confirmButtonText: "OK",
        });
    }
}

// --- HELPERS & UTILS ---

function showAssignmentDetail(id) {
    const a = myAssignments.find((x) => x.id == id);
    if (!a) return;
    Swal.fire({
        title: a.title,
        html: `<p class="text-sm text-slate-600 mb-4">${a.description}</p>
                <p class="text-xs text-slate-500">Due: ${formatDate(a.due_date)}</p>`,
        confirmButtonText: "Tutup",
    });
}

function switchView(viewId) {
    document
        .querySelectorAll(".view-section")
        .forEach((el) => el.classList.add("hidden"));
    document.getElementById("view-" + viewId).classList.remove("hidden");
    document
        .querySelectorAll(".nav-link-custom")
        .forEach((el) => el.classList.remove("active"));
    const navEl = document.getElementById("nav-" + viewId);
    if (navEl) navEl.classList.add("active");
}

function switchCourseTab(tabName) {
    document
        .querySelectorAll(".course-tab-content")
        .forEach((c) => c.classList.add("hidden"));
    document
        .getElementById("course-content-" + tabName)
        .classList.remove("hidden");
    document.querySelectorAll(".course-tab-btn").forEach((b) => {
        b.classList.remove("text-primary", "border-primary");
        b.classList.add("text-slate-500", "border-transparent");
    });
    document
        .getElementById("tab-course-" + tabName)
        .classList.remove("text-slate-500", "border-transparent");
    document
        .getElementById("tab-course-" + tabName)
        .classList.add("text-primary", "border-primary");
}

function updateProfileUI() {
    if (!currentUser) return;

    const profile = currentUser.profile || {};
    const name = profile.full_name || currentUser.name;

    // Sidebar
    document.getElementById("sidebar-username").innerText = name;

    // Profile page
    document.getElementById("profile-name-display").innerText = name;
    document.getElementById("profile-avatar").innerText = name
        .charAt(0)
        .toUpperCase();
    document.getElementById("profile-student-number").innerText =
        profile.student_number || "N/A";
    document.getElementById("profile-email-display").innerText =
        profile.email || currentUser.email;
    document.getElementById("profile-phone").innerText = profile.phone || "-";
    document.getElementById("profile-grade").innerText = profile.current_grade
        ? `Kelas ${profile.current_grade}`
        : "-";
    document.getElementById("profile-enrollment").innerText =
        profile.enrollment_year || "-";
    document.getElementById("profile-dob").innerText = profile.date_of_birth
        ? new Date(profile.date_of_birth).toLocaleDateString("id-ID")
        : "-";
    document.getElementById("profile-gender").innerText =
        profile.gender === "male"
            ? "Laki-laki"
            : profile.gender === "female"
              ? "Perempuan"
              : "-";
    document.getElementById("profile-address").innerText =
        profile.address || "-";
    document.getElementById("profile-emergency-name").innerText =
        profile.emergency_contact_name || "-";
    document.getElementById("profile-emergency-phone").innerText =
        profile.emergency_contact_phone || "-";

    // Status badge
    const statusBadge = document.getElementById("profile-status");
    if (profile.status === "active") {
        statusBadge.className =
            "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800";
        statusBadge.innerText = "Aktif";
    } else {
        statusBadge.className =
            "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800";
        statusBadge.innerText = profile.status || "N/A";
    }

    // Parent info
    const parent = profile.parent || {};
    document.getElementById("profile-parent-name").innerText =
        parent.full_name || "-";
    document.getElementById("profile-parent-relation").innerText =
        parent.relationship || "-";
    document.getElementById("profile-parent-email").innerText =
        parent.email || "-";
    document.getElementById("profile-parent-phone").innerText =
        parent.phone || "-";
    document.getElementById("profile-parent-occupation").innerText =
        parent.occupation || "-";
}

function openEditProfileModal() {
    if (!currentUser || !currentUser.profile) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Data profil belum dimuat",
        });
        return;
    }

    const profile = currentUser.profile;

    // Fill form
    document.getElementById("edit-profile-name").value =
        profile.full_name || "";
    document.getElementById("edit-profile-email").value = profile.email || "";
    document.getElementById("edit-profile-phone").value = profile.phone || "";
    document.getElementById("edit-profile-dob").value = profile.date_of_birth
        ? profile.date_of_birth.split("T")[0]
        : "";
    document.getElementById("edit-profile-address").value =
        profile.address || "";
    document.getElementById("edit-profile-emergency-name").value =
        profile.emergency_contact_name || "";
    document.getElementById("edit-profile-emergency-phone").value =
        profile.emergency_contact_phone || "";

    openModal("modal-edit-profile");
}

async function submitProfileUpdate() {
    try {
        const payload = {
            full_name: document.getElementById("edit-profile-name").value,
            phone: document.getElementById("edit-profile-phone").value,
            date_of_birth: document.getElementById("edit-profile-dob").value,
            address: document.getElementById("edit-profile-address").value,
            emergency_contact_name: document.getElementById(
                "edit-profile-emergency-name",
            ).value,
            emergency_contact_phone: document.getElementById(
                "edit-profile-emergency-phone",
            ).value,
        };

        const studentId = currentUser.profile.id;

        const res = await fetchApi(`/students/${studentId}`, {
            method: "PUT",
            body: JSON.stringify(payload),
        });

        if (res) {
            closeModal("modal-edit-profile");

            await Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Profil berhasil diperbarui",
                confirmButtonText: "OK",
            });

            // Update currentUser dengan data baru
            currentUser.profile = {
                ...currentUser.profile,
                ...payload,
            };

            // Update localStorage
            const userData = JSON.parse(
                localStorage.getItem("userData") || "{}",
            );
            userData.profile = currentUser.profile;
            localStorage.setItem("userData", JSON.stringify(userData));

            // Reload profile display
            updateProfileUI();
        }
    } catch (error) {
        console.error("Error updating profile:", error);
        await Swal.fire({
            icon: "error",
            title: "Gagal",
            text: "Gagal memperbarui profil",
            confirmButtonText: "OK",
        });
    }
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove("hidden");
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add("hidden");
}

function formatDate(d, time = false) {
    if (!d) return "-";
    return new Date(d).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        ...(time && { hour: "2-digit", minute: "2-digit" }),
    });
}

function getStatusClass(s) {
    return s === "submitted"
        ? "badge-submitted"
        : s === "graded"
          ? "badge-graded"
          : "badge-pending";
}

function getAttClass(s) {
    return s === "present"
        ? "bg-emerald-100 text-emerald-700"
        : "bg-red-100 text-red-700";
}

function filterAssignments(status) {
    if (status === "all") renderAssignments(myAssignments);
    else renderAssignments(myAssignments.filter((a) => a.status === status));
}

async function logout() {
    const result = await Swal.fire({
        title: "Konfirmasi Logout",
        text: "Apakah Anda yakin ingin keluar?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Logout",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        // Hapus data
        localStorage.removeItem("auth_token");
        localStorage.removeItem("current_user");

        await Swal.fire({
            title: "Berhasil Logout",
            text: "Anda akan diarahkan ke halaman login",
            icon: "success",
            timer: 1500,
            showConfirmButton: false,
        });

        window.location.replace("/login");
    }
}

// ========================================
// SUBMISSION FUNCTIONS (STUDENT UPLOAD TUGAS)
// ========================================

// Load submissions untuk student
async function loadMySubmissions() {
    try {
        const studentId = currentUser.profile?.id || currentUser.id;
        const res = await fetchApi(`/submissions?student_id=${studentId}`);
        mySubmissions = Array.isArray(res) ? res : res.data || [];
        return mySubmissions;
    } catch (error) {
        console.error("Error loading submissions:", error);
        return [];
    }
}

// Open modal untuk submit assignment
function openSubmitModal(assignmentId, submissionId = null) {
    const assignment = myAssignments.find(a => a.id === assignmentId);
    if (!assignment) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Assignment tidak ditemukan' });
        return;
    }
    
    document.getElementById('submission-assignment-id').value = assignmentId;
    document.getElementById('submission-id').value = submissionId || '';
    document.getElementById('submission-assignment-title').textContent = assignment.title;
    document.getElementById('submission-due-date').textContent = formatDate(assignment.due_date);
    document.getElementById('submission-assignment-desc').textContent = assignment.description || 'Tidak ada deskripsi';
    
    document.getElementById('submission-file').value = '';
    document.getElementById('current-file-name').classList.add('hidden');
    
    if (submissionId) {
        const submission = mySubmissions.find(s => s.id === submissionId);
        if (submission && submission.file_path) {
            const fileName = submission.file_path.split('/').pop();
            document.getElementById('current-file-name').textContent = `File saat ini: ${fileName}`;
            document.getElementById('current-file-name').classList.remove('hidden');
            document.getElementById('submission-file').removeAttribute('required');
        }
    } else {
        document.getElementById('submission-file').setAttribute('required', 'required');
    }
    
    openModal('modal-submit-assignment');
}

// Submit assignment function
async function submitAssignment(event, submitNow = true) {
    event.preventDefault();
    
    const assignmentId = document.getElementById('submission-assignment-id').value;
    const submissionId = document.getElementById('submission-id').value;
    const fileInput = document.getElementById('submission-file');
    
    if (!submissionId && !fileInput.files[0]) {
        await Swal.fire({ icon: 'warning', title: 'File Diperlukan', text: 'Silakan pilih file untuk diupload' });
        return;
    }
    
    if (fileInput.files[0] && fileInput.files[0].size > 10 * 1024 * 1024) {
        await Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal 10MB' });
        return;
    }
    
    try {
        document.getElementById('loading-overlay').style.display = 'flex';
        
        let filePath = null;
        if (fileInput.files[0]) {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('type', 'assignment_submission');
            
            const uploadRes = await fetchApi('/upload', { method: 'POST', body: formData });
            filePath = uploadRes.file_path || uploadRes.path;
        }
        
        const studentId = currentUser.profile?.id || currentUser.id;
        const submissionData = { assignment_id: parseInt(assignmentId), student_id: studentId, submit_now: submitNow };
        
        if (filePath) { submissionData.file_path = filePath; }
        
        let response;
        if (submissionId) {
            if (submitNow && !submissionData.file_path) {
                response = await fetchApi(`/submissions/${submissionId}/submit`, { method: 'POST' });
            } else {
                response = await fetchApi(`/submissions/${submissionId}`, { method: 'PUT', body: JSON.stringify(submissionData) });
            }
        } else {
            response = await fetchApi('/submissions', { method: 'POST', body: JSON.stringify(submissionData) });
        }
        
        closeModal('modal-submit-assignment');
        
        const statusText = submitNow ? 'dikumpulkan' : 'disimpan sebagai draft';
        let message = `Tugas berhasil ${statusText}!`;
        
        if (submitNow && response.submission?.is_late) {
            message += ` ⚠️ Terlambat ${response.submission.late_days} hari.`;
        }
        
        await Swal.fire({ icon: 'success', title: 'Berhasil!', text: message, timer: 3000, showConfirmButton: false });
        
        await showCourseDetail(currentCourseId);
        
    } catch (error) {
        console.error('Error submitting assignment:', error);
        await Swal.fire({ icon: 'error', title: 'Gagal Submit', text: error.message || 'Terjadi kesalahan saat mengumpulkan tugas' });
    } finally {
        document.getElementById('loading-overlay').style.display = 'none';
    }
}

// View submission detail
async function viewSubmissionDetail(submissionId) {
    try {
        const submission = mySubmissions.find(s => s.id === submissionId);
        if (!submission) return;
        
        const assignment = myAssignments.find(a => a.id === submission.assignment_id);
        
        let content = `<div class="text-left space-y-4">
                <div><label class="text-xs font-bold text-slate-500">Tugas</label><p class="text-slate-800">${assignment?.title || 'N/A'}</p></div>
                <div><label class="text-xs font-bold text-slate-500">Status</label><p class="text-slate-800">${submission.status}</p></div>
                <div><label class="text-xs font-bold text-slate-500">Dikumpulkan</label><p class="text-slate-800">${formatDate(submission.submitted_at || submission.created_at)}</p></div>
                ${submission.grade !== null ? `<div><label class="text-xs font-bold text-slate-500">Nilai</label><p class="text-2xl font-bold text-green-600">${submission.grade}</p></div>` : ''}
                ${submission.feedback ? `<div><label class="text-xs font-bold text-slate-500">Feedback dari Instructor</label><p class="text-slate-700 bg-slate-50 p-3 rounded">${submission.feedback}</p></div>` : ''}
                ${submission.file_path ? `<div><label class="text-xs font-bold text-slate-500">File</label><a href="${API_BASE_URL}/storage/${submission.file_path}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-2"><i class="bi bi-download"></i> Download File</a></div>` : ''}
            </div>`;
        
        await Swal.fire({ title: 'Detail Submission', html: content, width: 600, confirmButtonText: 'Tutup' });
    } catch (error) {
        console.error('Error viewing submission:', error);
    }
}
