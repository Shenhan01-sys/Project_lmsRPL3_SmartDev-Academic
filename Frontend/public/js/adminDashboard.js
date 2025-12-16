const API_BASE = "https://portohansgunawan.my.id/api/v1";
let authToken = localStorage.getItem("auth_token");
let studentPage = 1;
let studentSearchDebounce;

let store = {
    users: [],
    courses: [],
    instructors: [],
    registrations: [],
    announcements: [],
    stats: {},
};

document.addEventListener("DOMContentLoaded", () => {
    initAuth();
});

async function initAuth() {
    if (!authToken) {
        // Langsung redirect ke halaman login tanpa alert
        window.location.href = "/login"; // Sesuaikan dengan route login Anda
        return;
    }
    loadDashboardData();
}

function getHeaders(isFormData = false) {
    const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${authToken}`,
        "ngrok-skip-browser-warning": "true",
    };
    if (!isFormData) headers["Content-Type"] = "application/json";
    return headers;
}

// Fetcher Cerdas
async function fetchApi(endpoint, options = {}) {
    // Tampilkan loader jika bukan method GET (agar UX lebih responsif)
    if (options.method && options.method !== "GET") showLoader(true);

    try {
        const isFormData = options.body instanceof FormData;
        // Handle URL absolute vs relative
        const url = endpoint.startsWith("http")
            ? endpoint
            : API_BASE + endpoint;

        const res = await fetch(url, {
            ...options,
            headers: {
                "Access-Control-Allow-Origin": "*",
                ...getHeaders(isFormData),
                ...options.headers,
            },
        });

        if (res.status === 401) {
            localStorage.removeItem("auth_token");
            location.reload(); // Force logout jika token expired
            return null;
        }

        // Handle No Content
        if (res.status === 204) return true;

        const json = await res.json().catch(() => null);
        if (!res.ok) {
            // Prioritas: json.error (detail error) > json.message (generic message)
            const errorMessage =
                json?.error || json?.message || `HTTP Error ${res.status}`;
            throw new Error(errorMessage);
        }

        return json;
    } catch (err) {
        console.error(`API Error ${endpoint}:`, err);
        Swal.fire("Error", err.message, "error");
        return null;
    } finally {
        showLoader(false);
    }
}

async function loadDashboardData() {
    showLoader(true);
    try {
        // Fetch Stats & Initial Data
        const [
            statsReg,
            statsUser,
            statsAcademic,
            statsFinance,
            statsSummary,
            users,
            courses,
            registrations,
            instructors,
            announcements,
        ] = await Promise.all([
            fetchApi("/stats/registrations"),
            fetchApi("/stats/users"),
            fetchApi("/stats/academic"),
            fetchApi("/stats/finance"),
            fetchApi("/stats/summary"),
            fetchApi("/users?per_page=1"),
            fetchApi("/courses"),
            fetchApi("/registrations?status=pending").catch((e) => ({
                total: 0,
            })),
            fetchApi("/instructors?per_page=1"),
            fetchApi("/announcements?per_page=5"),
        ]);

        // Store Data
        store.stats = {
            registrations: statsReg,
            users: statsUser,
            academic: statsAcademic,
            finance: statsFinance,
            summary: statsSummary,
        };

        // Update Counts
        if (statsSummary) {
            document.getElementById("stat-users").innerText =
                statsSummary.total_users || 0;
            document.getElementById("stat-courses").innerText =
                statsSummary.active_courses || 0;
            document.getElementById("stat-pending").innerText =
                statsSummary.pending_registrations || 0;
            document.getElementById("stat-instructors").innerText =
                statsSummary.total_instructors || 0;

            const pendingCount = statsSummary.pending_registrations || 0;
            const badge = document.getElementById("badge-pending");
            if (badge) {
                badge.innerText = pendingCount;
                badge.classList.toggle("hidden", pendingCount === 0);
            }
        } else {
            document.getElementById("stat-users").innerText = users?.total || 0;
            document.getElementById("stat-courses").innerText = Array.isArray(
                courses,
            )
                ? courses.length
                : courses?.total || 0;
            document.getElementById("stat-instructors").innerText =
                instructors?.total || 0;
            const pendingCount = registrations?.total || 0;
            document.getElementById("stat-pending").innerText = pendingCount;
        }

        // Update Standard Charts
        updateCharts();

        // === NEW FEATURES INIT ===
        loadRealEWSData(); // Fetch real data from /enrollments
        loadRealScatterData(); // Fetch real data for scatter plot

        // Load initial lists for other views
        store.announcements = announcements?.data || [];
        renderAnnouncements();

        store.courses = Array.isArray(courses) ? courses : courses?.data || [];
        renderCourses();
        updatePopularCoursesChart();
    } catch (e) {
        console.error("Init Error", e);
    } finally {
        showLoader(false);
    }
}

// ==========================================
// NEW FEATURE 1: REAL EARLY WARNING SYSTEM
// Menggunakan data dari endpoint /enrollments
// ==========================================
async function loadRealEWSData() {
    const listContainer = document.getElementById("ews-list");
    listContainer.innerHTML =
        '<div class="text-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-500 mx-auto"></div></div>';

    try {
        // Fetch enrollments karena ini satu-satunya endpoint publik yang punya nilai (final_grade) dan nama siswa
        // Idealnya: Gunakan pagination atau filter server-side jika data besar.
        // Disini kita fetch semua (default pagination) untuk demo.
        const enrollments = await fetchApi("/enrollments");

        if (!enrollments || enrollments.length === 0) {
            listContainer.innerHTML =
                '<p class="text-center text-xs text-slate-400 py-4">Data enrollment kosong.</p>';
            return;
        }

        // Filter siswa berisiko: Nilai < 60 (dan bukan null/0 yang mungkin belum dinilai)
        // Kita anggap nilai 0-10 sebagai belum dinilai, jadi ambil range 10-60 untuk "Berisiko"
        // Atau tampilkan semua yang < 60 jika ingin ketat.
        const atRisk = enrollments
            .filter((e) => {
                const grade = parseFloat(e.final_grade || 0);
                return grade > 0 && grade < 60; // Filter nilai rendah tapi sudah ada nilai
            })
            .slice(0, 10); // Ambil 10 teratas

        if (atRisk.length === 0) {
            listContainer.innerHTML =
                '<div class="flex flex-col items-center py-6 text-slate-400"><i class="bi bi-check-circle text-4xl text-emerald-100 mb-2"></i><span class="text-xs">Tidak ada siswa berisiko saat ini.</span></div>';
            return;
        }

        let html = "";
        atRisk.forEach((e) => {
            const studentName = e.student?.user?.name || "Siswa Tanpa Nama";
            const courseName = e.course?.course_name || "Kursus";
            const grade = parseFloat(e.final_grade).toFixed(1);

            html += `
                    <div class="flex items-start gap-3 p-3 bg-red-50/30 hover:bg-red-50 rounded-lg transition-all border border-red-100 group">
                        <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm mt-1">
                            ${studentName.charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h5 class="font-bold text-slate-800 text-xs truncate pr-2">${studentName}</h5>
                                <span class="text-[10px] font-bold text-red-600 bg-white px-1.5 py-0.5 rounded border border-red-200">Nilai: ${grade}</span>
                            </div>
                            <p class="text-[10px] text-slate-500 truncate mb-1">${courseName}</p>
                            <div class="w-full bg-red-200 rounded-full h-1">
                                <div class="bg-red-500 h-1 rounded-full" style="width: ${grade}%"></div>
                            </div>
                        </div>
                    </div>
                    `;
        });

        listContainer.innerHTML = html;
    } catch (error) {
        console.error("EWS Error", error);
        listContainer.innerHTML =
            '<p class="text-center text-xs text-red-400 py-4">Gagal memuat data risiko.</p>';
    }
}

// ==========================================
// NEW FEATURE 2: REAL DATA SCATTER PLOT
// ==========================================
async function loadRealScatterData() {
    const ctx = document.getElementById("academicScatterChart");
    if (!ctx) return;

    try {
        // Fetch all enrollments dengan detail student dan course
        const enrollmentsRes = await fetchApi("/enrollments?per_page=200");
        const enrollments = Array.isArray(enrollmentsRes) ? enrollmentsRes : [];

        console.log("📊 Total enrollments:", enrollments.length);

        if (enrollments.length === 0) {
            console.log("No enrollments found for scatter plot");
            return;
        }

        // Untuk setiap enrollment, fetch attendance stats
        const scatterDataPromises = enrollments.map(async (enrollment) => {
            try {
                const studentId = enrollment.student_id;
                const courseId = enrollment.course_id;
                const finalGrade = parseFloat(enrollment.final_grade || 0);

                // Skip jika belum ada grade
                if (finalGrade === 0) {
                    return null;
                }

                // Fetch attendance stats untuk COURSE YANG SAMA
                const statsRes = await fetchApi(
                    `/attendance-records/student/${studentId}/course/${courseId}/stats`,
                );

                // 🔍 Cek jika statsRes null atau total_sessions = 0
                if (!statsRes || statsRes.total_sessions === 0) {
                    // Tetap tampilkan dengan attendance=0
                    return {
                        x: 0, // Belum ada attendance record
                        y: finalGrade,
                        studentName: enrollment.student?.full_name || "Unknown",
                        courseName: enrollment.course?.course_name || "Unknown",
                        hasAttendance: false,
                    };
                }

                const attendancePercentage =
                    statsRes.attendance_percentage ?? 0;

                return {
                    x: attendancePercentage,
                    y: finalGrade,
                    studentName: enrollment.student?.full_name || "Unknown",
                    courseName: enrollment.course?.course_name || "Unknown",
                    hasAttendance: true,
                };
            } catch (error) {
                console.warn(
                    `❌ Error for enrollment ${enrollment.id}:`,
                    error,
                );
                return null;
            }
        });

        // Wait untuk semua requests selesai
        const scatterDataRaw = await Promise.all(scatterDataPromises);

        // Filter null values
        const scatterData = scatterDataRaw.filter((item) => item !== null);

        console.log("✅ Valid scatter data points:", scatterData.length);
        console.log("📈 Sample data (first 5):", scatterData.slice(0, 5));

        // 🔍 DEBUGGING: Hitung berapa yang punya attendance vs tidak
        const withAttendance = scatterData.filter(
            (d) => d.hasAttendance,
        ).length;
        const withoutAttendance = scatterData.filter(
            (d) => !d.hasAttendance,
        ).length;
        console.log(`📊 Data breakdown:`);
        console.log(`   - With attendance data: ${withAttendance}`);
        console.log(`   - Without attendance data (x=0): ${withoutAttendance}`);

        // 🔍 Show beberapa contoh yang x=0
        const zeroAttendance = scatterData.filter((d) => d.x === 0).slice(0, 3);
        console.log("📋 Sample students with x=0:", zeroAttendance);

        if (scatterData.length === 0) {
            console.log(
                "No valid data for scatter plot (no grades or attendance records yet)",
            );
            // Tampilkan pesan di UI
            const container = document.querySelector(
                "#academicScatterChart",
            ).parentElement;
            container.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #64748b;">
                            <div style="text-align: center;">
                                <i class="fas fa-chart-scatter" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                                <p>Belum ada data untuk ditampilkan</p>
                                <p style="font-size: 12px; opacity: 0.7;">Data akan muncul setelah ada nilai dan kehadiran siswa</p>
                            </div>
                        </div>
                    `;
            return;
        }

        // Destroy chart lama jika ada
        if (Chart.getChart(ctx)) Chart.getChart(ctx).destroy();

        // Create new scatter chart dengan data real
        new Chart(ctx, {
            type: "scatter",
            data: {
                datasets: [
                    {
                        label: "Distribusi Siswa (Kehadiran vs Nilai)",
                        data: scatterData,
                        backgroundColor: (ctx) => {
                            const v = ctx.raw;
                            if (!v) return "#10b981";

                            // 🔴 Merah jika nilai < 60
                            if (v.y < 60) return "#ef4444";
                            // 🟡 Kuning jika 60-75
                            if (v.y < 75) return "#f59e0b";
                            // 🟢 Hijau jika > 75
                            return "#10b981";
                        },
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        // Tambahkan border untuk yang tidak punya attendance
                        borderColor: (ctx) => {
                            const v = ctx.raw;
                            if (!v || !v.hasAttendance) return "#94a3b8"; // Abu-abu border untuk yang x=0
                            return "transparent";
                        },
                        borderWidth: (ctx) => {
                            const v = ctx.raw;
                            if (!v || !v.hasAttendance) return 2;
                            return 0;
                        },
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const data = ctx.raw;
                                const lines = [
                                    `Siswa: ${data.studentName}`,
                                    `Course: ${data.courseName}`,
                                    `Kehadiran: ${data.x.toFixed(1)}%`,
                                    `Nilai Akhir: ${data.y.toFixed(1)}`,
                                ];

                                if (!data.hasAttendance) {
                                    lines.push("⚠️ Belum ada data kehadiran");
                                }

                                return lines;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Persentase Kehadiran (%)",
                            color: "#94a3b8",
                            font: { size: 11, weight: "bold" },
                        },
                        min: 0,
                        max: 100,
                        grid: { color: "#f1f5f9" },
                    },
                    y: {
                        title: {
                            display: true,
                            text: "Nilai Akhir",
                            color: "#94a3b8",
                            font: { size: 11, weight: "bold" },
                        },
                        min: 0,
                        max: 100,
                        grid: { color: "#f1f5f9" },
                    },
                },
            },
        });

        console.log(
            `✨ Scatter plot loaded with ${scatterData.length} data points`,
        );
    } catch (error) {
        console.error("❌ Error loading scatter data:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Gagal memuat data scatter plot: " + error.message,
        });
    }
}

// --- EXISTING CHARTS & LOGIC (UNCHANGED) ---

function updateCharts() {
    // Registration Chart (Line)
    const ctxReg = document.getElementById("registrationChart");
    if (ctxReg && store.stats.registrations) {
        if (Chart.getChart(ctxReg)) Chart.getChart(ctxReg).destroy();
        new Chart(ctxReg, {
            type: "line",
            data: {
                labels: store.stats.registrations.labels,
                datasets: [
                    {
                        label: "Pendaftaran",
                        data: store.stats.registrations.data,
                        borderColor: "#4f46e5",
                        backgroundColor: "rgba(79, 70, 229, 0.1)",
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 2,
                        pointRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        grid: { borderDash: [4, 4] },
                        beginAtZero: true,
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // User Distribution Chart (Doughnut)
    const ctxDist = document.getElementById("userDistChart");
    if (ctxDist && store.stats.users) {
        if (Chart.getChart(ctxDist)) Chart.getChart(ctxDist).destroy();
        const u = store.stats.users;
        new Chart(ctxDist, {
            type: "doughnut",
            data: {
                labels: ["Siswa", "Instruktur", "Admin", "Ortu"],
                datasets: [
                    {
                        data: [u.student, u.instructor, u.admin, u.parent],
                        backgroundColor: [
                            "#4f46e5",
                            "#f59e0b",
                            "#0f172a",
                            "#10b981",
                        ],
                        borderWidth: 0,
                        hoverOffset: 10,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                        },
                    },
                },
            },
        });
    }
}

// --- NAVIGATION & VIEWS ---
function nav(viewId) {
    console.log("Navigating to:", viewId); // Debug log

    document
        .querySelectorAll(".page-view")
        .forEach((el) => el.classList.add("hidden"));

    const targetView = document.getElementById(`view-${viewId}`);
    if (!targetView) {
        console.error(`Element with id "view-${viewId}" not found!`);
        return;
    }

    targetView.classList.remove("hidden");
    document
        .querySelectorAll(".nav-link")
        .forEach((el) =>
            el.classList.remove("active", "text-white", "bg-white/5"),
        );

    const btn = document.getElementById("btn-" + viewId);
    if (btn) btn.classList.add("active");

    if (viewId === "registrations") loadRegistrations();
    if (viewId === "instructors") loadInstructors();
    if (viewId === "students") loadStudents();
    if (viewId === "courses") loadCourses();
    if (viewId === "announcements") loadAnnouncements();
}

// --- DATA LOADERS ---
async function loadInstructors() {
    const grid = document.getElementById("instructors-grid");
    grid.innerHTML =
        '<div class="col-span-full text-center p-8 text-slate-400">Memuat instruktur...</div>';
    const res = await fetchApi("/instructors");
    store.instructors =
        res && res.data ? res.data : Array.isArray(res) ? res : [];
    renderInstructors();
}
async function loadCourses() {
    const grid = document.getElementById("courses-grid");
    grid.innerHTML =
        '<div class="col-span-full text-center p-8 text-slate-400">Memuat kursus...</div>';
    const res = await fetchApi("/courses");
    store.courses = res && res.data ? res.data : Array.isArray(res) ? res : [];
    renderCourses();
    updatePopularCoursesChart();
}

async function loadAnnouncements() {
    const list = document.getElementById("announcements-list");
    list.innerHTML =
        '<div class="text-center p-8 text-slate-400">Memuat pengumuman...</div>';
    const res = await fetchApi("/announcements");
    store.announcements =
        res && res.data ? res.data : Array.isArray(res) ? res : [];
    renderAnnouncements();
}

async function updatePopularCoursesChart() {
    const ctx = document.getElementById("popularCoursesChart");
    if (!ctx) return;

    let labels = [];
    let data = [];
    let label = "Rata-rata Nilai";
    let color = "#10b981";

    try {
        const res = await fetchApi("/stats/academic");
        const academicData = res && Array.isArray(res) ? res : [];

        if (academicData.length > 0) {
            const sorted = academicData
                .sort((a, b) => (b.average_score || 0) - (a.average_score || 0))
                .slice(0, 5);
            labels = sorted.map((c) => c.course_name);
            data = sorted.map((c) => c.average_score || 0);
        }
    } catch (e) {
        console.error(e);
    }

    if (labels.length === 0 && store.courses && store.courses.length > 0) {
        label = "Jumlah Siswa";
        color = "#4f46e5";
        const sorted = [...store.courses]
            .sort((a, b) => (b.students_count || 0) - (a.students_count || 0))
            .slice(0, 5);
        labels = sorted.map((c) => c.course_name || c.name);
        data = sorted.map((c) => c.students_count || 0);
    }

    if (Chart.getChart(ctx)) Chart.getChart(ctx).destroy();

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                {
                    label: label,
                    data: data,
                    backgroundColor: color,
                    borderRadius: 6,
                    barThickness: 30,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: label === "Rata-rata Nilai" ? 100 : undefined,
                    grid: { borderDash: [2, 4], color: "#e2e8f0" },
                },
                x: { grid: { display: false } },
            },
            plugins: { legend: { display: false } },
        },
    });
}

// --- REGISTRATIONS LOGIC ---
async function loadRegistrations() {
    const tbody = document.getElementById("registration-table-body");
    tbody.innerHTML =
        '<tr><td colspan="5" class="text-center p-8 text-slate-400">Memuat data registrasi...</td></tr>';

    try {
        const res = await fetchApi("/registrations");
        const registrations =
            res && res.data ? res.data : Array.isArray(res) ? res : [];
        store.registrations = registrations;

        if (registrations.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="5" class="text-center p-8 text-slate-400">Tidak ada registrasi</td></tr>';
            return;
        }

        tbody.innerHTML = registrations
            .map((reg) => {
                const statusBadge = getRegistrationStatusBadge(
                    reg.registration_status,
                );
                const studentName = reg.user ? reg.user.name : "-";
                const studentEmail = reg.user ? reg.user.email : "-";

                return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">${studentName}</div>
                                <div class="text-xs text-slate-500">${studentEmail}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">${reg.phone_orang_tua || "-"}</div>
                                <div class="text-xs text-slate-500">${reg.email_orang_tua || "-"}</div>
                            </td>
                            <td class="px-6 py-4">
                                ${statusBadge}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">${reg.created_at ? new Date(reg.created_at).toLocaleDateString("id-ID") : "-"}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="showRegistrationDetail(${reg.id})" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs hover:bg-primary/90 transition">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                    `;
            })
            .join("");
    } catch (error) {
        console.error("Error loading registrations:", error);
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center p-8 text-danger">Gagal memuat data registrasi</td></tr>';
    }
}

function getRegistrationStatusBadge(status) {
    const badges = {
        pending_documents:
            '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Pending Dokumen</span>',
        pending_payment:
            '<span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">Pending Pembayaran</span>',
        pending_approval:
            '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Pending Approval</span>',
        approved:
            '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Disetujui</span>',
        rejected:
            '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Ditolak</span>',
    };
    return (
        badges[status] ||
        '<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">' +
            status +
            "</span>"
    );
}

async function showRegistrationDetail(id) {
    try {
        const reg = await fetchApi(`/registrations/${id}`);
        const base_path = ""

        // Fill registration meta info
        document.getElementById("detail-reg-id").textContent = `#${reg.id}`;
        document.getElementById("detail-user-id").textContent =
            reg.user_id || "-";
        document.getElementById("detail-created-at").textContent =
            reg.created_at
                ? new Date(reg.created_at).toLocaleDateString("id-ID", {
                      day: "2-digit",
                      month: "short",
                      year: "numeric",
                  })
                : "-";

        // Fill student info
        document.getElementById("detail-student-name").textContent =
            reg.user?.name || "-";
        document.getElementById("detail-student-email").textContent =
            reg.user?.email || "-";
        document.getElementById("detail-student-role").innerHTML = reg.user
            ?.role
            ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="bi bi-person-badge me-1"></i>${reg.user.role}
                    </span>`
            : "-";
        document.getElementById("detail-tempat-lahir").textContent =
            reg.tempat_lahir || "-";

        // Format tanggal lahir
        document.getElementById("detail-tanggal-lahir").textContent =
            reg.tanggal_lahir
                ? new Date(reg.tanggal_lahir).toLocaleDateString("id-ID", {
                      day: "2-digit",
                      month: "long",
                      year: "numeric",
                  })
                : "-";

        // Format jenis kelamin
        const gender =
            reg.jenis_kelamin === "L"
                ? '<i class="bi bi-gender-male text-blue-600"></i> Laki-laki'
                : reg.jenis_kelamin === "P"
                  ? '<i class="bi bi-gender-female text-pink-600"></i> Perempuan'
                  : "-";
        document.getElementById("detail-jenis-kelamin").innerHTML = gender;

        // Fill parent info
        document.getElementById("detail-parent-name").textContent =
            reg.nama_orang_tua || "-";
        document.getElementById("detail-parent-email").textContent =
            reg.email_orang_tua || "-";
        document.getElementById("detail-parent-phone").textContent =
            reg.phone_orang_tua || "-";
        document.getElementById("detail-parent-address").textContent =
            reg.alamat_orang_tua || "-";

        // Fill documents
        const docsContainer = document.getElementById("detail-documents");
        const docs = [
            {
                label: "KTP Orang Tua",
                url: reg.ktp_orang_tua_url,
                path: reg.ktp_orang_tua_path,
                icon: "bi-person-vcard-fill",
                color: "blue",
            },
            {
                label: "Ijazah",
                url: reg.ijazah_url,
                path: reg.ijazah_path,
                icon: "bi-file-earmark-text-fill",
                color: "green",
            },
            {
                label: "Foto Siswa",
                url: reg.foto_siswa_url,
                path: reg.foto_siswa_path,
                icon: "bi-image-fill",
                color: "purple",
            },
            {
                label: "Bukti Pembayaran",
                url: reg.bukti_pembayaran_url,
                path: reg.bukti_pembayaran_path,
                icon: "bi-receipt-cutoff",
                color: "orange",
            },
        ];

        const uploadedDocs = docs.filter((doc) => doc.path).length;
        document.getElementById("detail-doc-count").textContent =
            `${uploadedDocs} / ${docs.length} Dokumen`;

        docsContainer.innerHTML = docs
            .map((doc) => {
                if (doc.path) {
                    return `
                            <a href="${doc.url}" target="_blank"
                                class="flex items-center gap-3 p-3 bg-white rounded-lg border-2 border-${doc.color}-200 hover:border-${doc.color}-400 hover:shadow-md transition group">
                                <div class="bg-${doc.color}-100 text-${doc.color}-600 rounded-lg p-2">
                                    <i class="bi ${doc.icon} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-800">${doc.label}</p>
                                    <p class="text-xs text-green-600 flex items-center gap-1">
                                        <i class="bi bi-check-circle-fill"></i> Tersedia
                                    </p>
                                </div>
                                <i class="bi bi-box-arrow-up-right text-${doc.color}-600 opacity-0 group-hover:opacity-100 transition"></i>
                            </a>
                        `;
                } else {
                    return `
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border-2 border-gray-200">
                                <div class="bg-gray-200 text-gray-400 rounded-lg p-2">
                                    <i class="bi ${doc.icon} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-600">${doc.label}</p>
                                    <p class="text-xs text-red-500 flex items-center gap-1">
                                        <i class="bi bi-x-circle-fill"></i> Belum diupload
                                    </p>
                                </div>
                            </div>
                        `;
                }
            })
            .join("");

        // Fill status & timeline
        document.getElementById("detail-status").innerHTML =
            getRegistrationStatusBadge(reg.registration_status);

        document.getElementById("detail-is-complete").innerHTML =
            reg.is_complete
                ? '<span class="inline-flex items-center gap-1 text-green-600"><i class="bi bi-check-circle-fill"></i> Lengkap</span>'
                : '<span class="inline-flex items-center gap-1 text-red-600"><i class="bi bi-x-circle-fill"></i> Belum Lengkap</span>';

        document.getElementById("detail-is-pending").innerHTML =
            reg.is_pending_approval
                ? '<span class="inline-flex items-center gap-1 text-orange-600"><i class="bi bi-clock-fill"></i> Ya</span>'
                : '<span class="inline-flex items-center gap-1 text-slate-600"><i class="bi bi-dash-circle-fill"></i> Tidak</span>';

        document.getElementById("detail-submitted-at").textContent =
            reg.submitted_at
                ? new Date(reg.submitted_at).toLocaleString("id-ID", {
                      day: "2-digit",
                      month: "short",
                      year: "numeric",
                      hour: "2-digit",
                      minute: "2-digit",
                  })
                : "-";

        document.getElementById("detail-approved-at").textContent =
            reg.approved_at
                ? new Date(reg.approved_at).toLocaleString("id-ID", {
                      day: "2-digit",
                      month: "short",
                      year: "numeric",
                      hour: "2-digit",
                      minute: "2-digit",
                  })
                : "-";

        document.getElementById("detail-approved-by").textContent =
            reg.approved_by || "-";

        // Show approval notes if exists
        const notesContainer = document.getElementById(
            "detail-approval-notes-container",
        );
        if (reg.approval_notes) {
            document.getElementById("detail-approval-notes").textContent =
                reg.approval_notes;
            notesContainer.style.display = "block";
        } else {
            notesContainer.style.display = "none";
        }

        // Fill actions
        const actionsContainer = document.getElementById("detail-actions");
        if (
            reg.registration_status === "approved" ||
            reg.registration_status === "rejected"
        ) {
            actionsContainer.innerHTML = `
                    <span class="text-sm text-slate-500 flex items-center gap-2">
                        <i class="bi bi-info-circle"></i> Tidak ada aksi tersedia
                    </span>
                `;
        } else {
            actionsContainer.innerHTML = `
                    <button onclick="rejectRegistration(${reg.id})"
                            class="px-5 py-2.5 bg-white border-2 border-red-500 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition flex items-center gap-2">
                        <i class="bi bi-x-circle"></i> Tolak
                    </button>
                    <button onclick="approveRegistration(${reg.id})"
                            class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-semibold hover:from-green-600 hover:to-green-700 transition flex items-center gap-2 shadow-lg">
                        <i class="bi bi-check-circle"></i> Approve & Buat Akun
                    </button>
                `;
        }

        openModal("modal-registration-detail");
    } catch (error) {
        console.error("Error loading registration detail:", error);
        Swal.fire("Error", "Gagal memuat detail registrasi", "error");
    }
}

async function approveRegistration(id) {
    const result = await Swal.fire({
        title: "Approve Registrasi?",
        html: `
                    <p class="text-sm">Dengan menyetujui, sistem akan:</p>
                    <ul class="text-sm text-left mt-2 space-y-1">
                        <li>✓ Membuat akun siswa dengan password default = nama lengkap siswa</li>
                        <li>✓ Membuat akun orang tua dengan password default = nama lengkap orang tua</li>
                        <li>✓ Mengaktifkan akses ke sistem LMS</li>
                    </ul>
                `,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Approve",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        try {
            const res = await fetchApi(`/registrations/${id}/approve`, {
                method: "POST",
            });
            if (res) {
                await Swal.fire({
                    title: "Berhasil!",
                    html: `
                                <div class="text-left space-y-3">
                                    <p class="font-bold">2 Akun telah dibuat:</p>
                                    <div class="bg-blue-50 p-3 rounded">
                                        <p class="font-semibold text-sm">👨‍🎓 Akun Siswa:</p>
                                        <p class="text-xs">Email: ${res.data.student.email}</p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded">
                                        <p class="font-semibold text-sm">👨‍👩‍👧 Akun Orang Tua:</p>
                                        <p class="text-xs">Email: ${res.data.parent.email}</p>                                            </div>
                                    <p class="text-xs text-gray-600 italic">*Silakan informasikan kredensial login kepada siswa dan orang tua</p>
                                </div>
                            `,
                    icon: "success",
                    confirmButtonText: "OK",
                });
                closeModal("modal-registration-detail");
                loadRegistrations();
                loadDashboardData();
            }
        } catch (error) {
            Swal.fire(
                "Error",
                error.message || "Gagal approve registrasi",
                "error",
            );
        }
    }
}

async function rejectRegistration(id) {
    const result = await Swal.fire({
        title: "Tolak Registrasi?",
        input: "textarea",
        inputLabel: "Alasan Penolakan",
        inputPlaceholder: "Masukkan alasan penolakan...",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Tolak",
        cancelButtonText: "Batal",
        inputValidator: (value) => {
            if (!value) {
                return "Alasan penolakan harus diisi!";
            }
        },
    });

    if (result.isConfirmed) {
        try {
            const res = await fetchApi(`/registrations/${id}/reject`, {
                method: "POST",
                body: JSON.stringify({ reason: result.value }),
            });
            if (res) {
                Swal.fire("Berhasil", "Registrasi ditolak", "success");
                closeModal("modal-registration-detail");
                loadRegistrations();
                loadDashboardData();
            }
        } catch (error) {
            Swal.fire(
                "Error",
                error.message || "Gagal reject registrasi",
                "error",
            );
        }
    }
}

// --- STUDENTS LOGIC ---
async function loadStudents(page = 1) {
    const tbody = document.getElementById("students-table-body");
    tbody.innerHTML =
        '<tr><td colspan="5" class="p-8 text-center text-slate-400">Memuat data siswa...</td></tr>';

    const status = document.getElementById("filter-student-status").value;
    const search = document.getElementById("search-student").value;

    const params = new URLSearchParams({
        per_page: 10,
        page: page,
    });
    if (status) params.append("status", status);
    if (search) params.append("search", search);

    const res = await fetchApi(`/students?${params.toString()}`);

    if (!res || !res.data || res.data.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="p-8 text-center text-slate-400">Tidak ada data siswa ditemukan.</td></tr>';
        document.getElementById("student-count-info").innerText = "0 Data";
        updatePagination(0, 1);
        return;
    }

    const students = res.data;
    studentPage = res.current_page;
    const totalPages = res.last_page;
    const total = res.total;

    document.getElementById("student-count-info").innerText =
        `Menampilkan ${res.from || 0}-${res.to || 0} dari ${total} siswa`;
    updatePagination(totalPages, studentPage);

    tbody.innerHTML = students
        .map(
            (s) => `
                <tr class="border-b hover:bg-slate-50 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                ${(s.full_name || s.name || "S").charAt(0)}
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">${s.full_name || s.name}</div>
                                <div class="text-xs text-slate-400">${s.student_number || "No NIS"}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">${s.email || (s.user ? s.user.email : "-")}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 font-mono">${s.student_number || "-"}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">${s.parent ? s.parent.full_name || "Ada" : "-"}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${getStatusColor(s.status)}">
                            ${s.status || "Active"}
                        </span>
                    </td>
                </tr>
            `,
        )
        .join("");
}

function getStatusColor(status) {
    if (status === "inactive") return "bg-slate-100 text-slate-500";
    if (status === "dropped") return "bg-red-50 text-red-600";
    if (status === "graduated") return "bg-blue-50 text-blue-600";
    return "bg-emerald-50 text-emerald-600";
}

function debounceSearchStudent() {
    clearTimeout(studentSearchDebounce);
    studentSearchDebounce = setTimeout(() => {
        studentPage = 1;
        loadStudents(1);
    }, 500);
}

function changeStudentPage(delta) {
    const newPage = studentPage + delta;
    if (newPage > 0) loadStudents(newPage);
}

function updatePagination(totalPages, current) {
    document.getElementById("btn-prev-student").disabled = current <= 1;
    document.getElementById("btn-next-student").disabled =
        current >= totalPages;
}

// --- RENDER FUNCTIONS ---
function renderInstructors() {
    const grid = document.getElementById("instructors-grid");
    if (store.instructors.length === 0) {
        grid.innerHTML =
            '<div class="col-span-full text-center p-8 text-slate-400">Belum ada instruktur.</div>';
        return;
    }
    grid.innerHTML = store.instructors
        .map(
            (i) => `
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all group relative cursor-pointer" onclick="showInstructorDetail(${i.id})">
                    <button onclick="event.stopPropagation(); openEditInstructorModal(${i.id})" class="absolute top-4 right-4 text-slate-400 hover:text-blue-600 z-10 p-1 rounded hover:bg-slate-100"><i class="bi bi-pencil-square"></i></button>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-800 text-white flex items-center justify-center text-lg font-bold">${(i.full_name || i.name || "I").charAt(0)}</div>
                        <div><h4 class="font-bold text-slate-800">${i.full_name || i.name}</h4><p class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded inline-block">${i.instructor_code || "N/A"}</p></div>
                    </div>
                    <p class="text-sm text-slate-600"><i class="bi bi-mortarboard me-2"></i> ${i.specialization || "-"}</p>
                    <p class="text-sm text-slate-600"><i class="bi bi-envelope me-2"></i> ${i.email}</p>
                </div>
            `,
        )
        .join("");
}

function renderCourses() {
    const grid = document.getElementById("courses-grid");
    if (store.courses.length === 0) {
        grid.innerHTML =
            '<div class="col-span-full text-center p-8 text-slate-400">Belum ada kursus.</div>';
        return;
    }
    grid.innerHTML = store.courses
        .map(
            (c) => `
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="h-20 bg-gradient-to-r from-blue-600 to-indigo-600 p-4 flex items-end"><h4 class="text-white font-bold">${c.course_name || c.name}</h4></div>
                    <div class="p-5">
                        <div class="flex justify-between mb-2">
                            <span class="text-xs font-bold bg-slate-100 px-2 py-1 rounded">${c.course_code || "CODE"}</span>
                            <span class="text-xs text-green-600 font-bold">Active</span>
                        </div>
                        <p class="text-sm text-slate-500 mb-4 line-clamp-2">${c.description || "No Desc"}</p>
                        <div class="flex gap-2 pt-2 border-t border-slate-100">
                                <button onclick="openEditCourseModal(${c.id})" class="flex-1 py-1.5 text-slate-500 hover:text-accent text-xs font-bold">Edit</button>
                                <button onclick="deleteCourse(${c.id})" class="flex-1 py-1.5 text-red-400 hover:text-red-600 text-xs font-bold">Hapus</button>
                        </div>
                    </div>
                </div>`,
        )
        .join("");
}

function renderAnnouncements() {
    const list = document.getElementById("announcements-list");
    if (store.announcements.length === 0) {
        list.innerHTML =
            '<div class="text-center p-8 text-slate-400">Belum ada pengumuman.</div>';
        return;
    }
    list.innerHTML = store.announcements
        .map(
            (a) => `
                <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex justify-between items-center">
                    <div>
                        <h4 class="font-bold text-slate-800">${a.title}</h4>
                        <p class="text-sm text-slate-500 line-clamp-1">${a.content}</p>
                        <span class="text-[10px] text-slate-400 uppercase mt-1 block">${new Date(a.created_at).toLocaleDateString()}</span>
                    </div>
                    <button onclick="deleteAnnouncement(${a.id})" class="text-slate-300 hover:text-red-500"><i class="bi bi-trash-fill"></i></button>
                </div>
            `,
        )
        .join("");
}

// --- ACTIONS & MODALS ---
async function loadInstructorsForSelect(selectId) {
    const res = await fetchApi("/instructors");
    const data = res && res.data ? res.data : Array.isArray(res) ? res : [];
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">Pilih Instruktur...</option>';
    data.forEach((i) => {
        sel.innerHTML += `<option value="${i.id}">${i.full_name || i.name}</option>`;
    });
}

async function submitCreateCourse() {
    const payload = {
        course_name: document.getElementById("create-course-name").value,
        course_code: document.getElementById("create-course-code").value,
        description: document.getElementById("create-course-desc").value,
        instructor_id: document.getElementById("create-course-instructor")
            .value,
    };
    closeModal("modal-create-course");
    const res = await fetchApi("/courses", {
        method: "POST",
        body: JSON.stringify(payload),
    });
    if (res) {
        Swal.fire("Berhasil", "Kursus dibuat", "success");
        loadDashboardData();
    }
}

async function openEditCourseModal(id) {
    const course = store.courses.find((c) => c.id == id);
    if (!course) return;
    document.getElementById("edit-course-id").value = id;
    document.getElementById("edit-course-name").value =
        course.course_name || course.name;
    document.getElementById("edit-course-code").value =
        course.course_code || course.code;
    document.getElementById("edit-course-desc").value =
        course.description || "";
    document.getElementById("edit-course-status").value =
        course.status || "Published";
    await loadInstructorsForSelect("edit-course-instructor");
    const sel = document.getElementById("edit-course-instructor");
    const insId =
        course.instructor_id || (course.instructor && course.instructor.id);
    if (insId) sel.value = insId;
    openModal("modal-edit-course");
}

async function updateCourse() {
    const id = document.getElementById("edit-course-id").value;
    const payload = {
        course_name: document.getElementById("edit-course-name").value,
        course_code: document.getElementById("edit-course-code").value,
        description: document.getElementById("edit-course-desc").value,
        instructor_id: document.getElementById("edit-course-instructor").value,
        status: document.getElementById("edit-course-status").value,
    };
    closeModal("modal-edit-course");
    const res = await fetchApi(`/courses/${id}`, {
        method: "PUT",
        body: JSON.stringify(payload),
    });
    if (res) {
        Swal.fire("Update Berhasil", "", "success");
        loadDashboardData();
    }
}

async function showInstructorDetail(id) {
    const resIns = await fetchApi(`/instructors/${id}`);
    if (!resIns) {
        Swal.fire("Error", "Instruktur tidak ditemukan", "error");
        return;
    }

    const resCourses = await fetchApi(`/instructors/${id}/courses`);
    const myCourses =
        resCourses && resCourses.data
            ? resCourses.data
            : Array.isArray(resCourses)
              ? resCourses
              : [];

    const insName = resIns.full_name || resIns.name;
    document.getElementById("det-ins-name").innerText = insName;
    document.getElementById("det-ins-code").innerText =
        resIns.instructor_code || "N/A";
    document.getElementById("det-ins-email").innerText = resIns.email;
    document.getElementById("det-ins-spec").innerText = resIns.specialization;
    document.getElementById("det-ins-phone").innerText = resIns.phone || "-";
    document.getElementById("det-ins-avatar").innerText = insName.charAt(0);
    document.getElementById("det-ins-exp").innerText =
        (resIns.experience_years || 0) + " Thn";
    document.getElementById("det-ins-bio").innerText =
        resIns.bio || "Tidak ada biografi.";
    document.getElementById("det-ins-edu").innerText =
        resIns.education_level || "-";

    // Calc stats
    const totalStudents = myCourses.reduce(
        (acc, curr) => acc + (curr.max_students || 0),
        0,
    );
    document.getElementById("det-ins-students").innerText = totalStudents;
    document.getElementById("det-ins-courses").innerText = myCourses.length;

    document.getElementById("det-ins-course-list").innerHTML = myCourses.length
        ? myCourses
              .map(
                  (c) => `
                <tr class="border-b hover:bg-slate-50">
                    <td class="px-6 py-3 font-bold text-slate-700">${c.course_name || c.name}</td>
                    <td class="px-6 py-3 text-xs font-mono">${c.course_code || c.code}</td>
                    <td class="px-6 py-3 text-center">${c.students_count || 0}</td>
                    <td class="px-6 py-3 text-center"><span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold">${c.status || "Active"}</span></td>
                </tr>`,
              )
              .join("")
        : '<tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada kursus.</td></tr>';

    const btnEdit = document.getElementById("btn-edit-instructor-detail");
    if (btnEdit) btnEdit.onclick = () => openEditInstructorModal(id);

    nav("instructor-detail");
}

// --- UTILS ---
function showLoader(show) {
    document.getElementById("loader").style.display = show ? "flex" : "none";
}
function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}
function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add("hidden");
    modal.classList.remove("flex");
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
        localStorage.removeItem("auth_token");

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

// Open Modal Wrappers
async function openCreateCourseModal() {
    document.getElementById("create-course-name").value = "";
    document.getElementById("create-course-code").value = "";
    document.getElementById("create-course-desc").value = "";
    await loadInstructorsForSelect("create-course-instructor");
    openModal("modal-create-course");
}

async function submitInstructor() {
    const payload = {
        full_name: document.getElementById("ins-name").value,
        email: document.getElementById("ins-email").value,
        password: document.getElementById("ins-pass").value,
        instructor_code: document.getElementById("ins-code").value,
        phone: document.getElementById("ins-phone").value,
        specialization: document.getElementById("ins-spec").value,
        education_level: document.getElementById("ins-edu").value,
        experience_years: document.getElementById("ins-exp").value,
        bio: document.getElementById("ins-bio").value,
    };
    closeModal("modal-instructor");
    const res = await fetchApi("/instructors", {
        method: "POST",
        body: JSON.stringify(payload),
    });
    if (res) {
        Swal.fire("Berhasil", "Instruktur ditambahkan", "success");
        loadDashboardData();
    }
}

async function openEditInstructorModal(id) {
    const res = await fetchApi(`/instructors/${id}`);
    if (!res) return;
    document.getElementById("edit-ins-id").value = id;
    document.getElementById("edit-ins-name").value = res.full_name || res.name;
    document.getElementById("edit-ins-email").value = res.email;
    document.getElementById("edit-ins-code").value = res.instructor_code || "";
    document.getElementById("edit-ins-phone").value = res.phone || "";
    document.getElementById("edit-ins-spec").value = res.specialization || "";
    document.getElementById("edit-ins-edu").value = res.education_level || "S1";
    document.getElementById("edit-ins-exp").value = res.experience_years || 0;
    document.getElementById("edit-ins-bio").value = res.bio || "";
    document.getElementById("edit-ins-status").value = res.status || "active";
    openModal("modal-edit-instructor");
}

async function updateInstructor() {
    const id = document.getElementById("edit-ins-id").value;
    const payload = {
        full_name: document.getElementById("edit-ins-name").value,
        email: document.getElementById("edit-ins-email").value,
        phone: document.getElementById("edit-ins-phone").value,
        specialization: document.getElementById("edit-ins-spec").value,
        education_level: document.getElementById("edit-ins-edu").value,
        experience_years: document.getElementById("edit-ins-exp").value,
        bio: document.getElementById("edit-ins-bio").value,
        status: document.getElementById("edit-ins-status").value,
    };
    closeModal("modal-edit-instructor");
    const res = await fetchApi(`/instructors/${id}`, {
        method: "PUT",
        body: JSON.stringify(payload),
    });
    if (res) {
        Swal.fire("Update Berhasil", "", "success");
        loadDashboardData();
        if (
            !document
                .getElementById("view-instructor-detail")
                .classList.contains("hidden")
        )
            showInstructorDetail(id);
    }
}

async function deleteCourse(id) {
    if (
        await Swal.fire({
            title: "Hapus?",
            text: "Data tidak bisa kembali",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
        }).then((r) => r.isConfirmed)
    ) {
        const res = await fetchApi(`/courses/${id}`, {
            method: "DELETE",
        });
        if (res) {
            Swal.fire("Terhapus", "", "success");
            nav("announcements");
        }
    }
}

async function deleteAnnouncement(id) {
    if (
        await Swal.fire({
            title: "Hapus Pengumuman?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
        }).then((r) => r.isConfirmed)
    ) {
        const res = await fetchApi(`/announcements/${id}`, {
            method: "DELETE",
        });
        if (res) {
            Swal.fire("Terhapus", "", "success");
            nav("announcements");
        }
    }
}

async function submitAnnouncement() {
    const payload = {
        title: document.getElementById("annTitle").value,
        content: document.getElementById("annContent").value,
        course_id: document.getElementById("annCourseId").value || null,
        announcement_type: "global",
        priority: document.getElementById("annPriority").value,
        status: document.getElementById("annStatus").value,
        published_at: document.getElementById("annPublishedAt").value,
        expires_at: "",
    };
    closeModal("modal-announcement");
    const res = await fetchApi("/announcements", {
        method: "POST",
        body: JSON.stringify(payload),
    });
    if (res) {
        Swal.fire("Berhasil", "Pengumuman dibuat", "success");
        closeModal("modal-announcement");
        setTimeout(() => nav("announcements"), 100);
    }
}
