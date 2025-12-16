let BASE_API = "https://portohansgunawan.my.id" + "/api/v1";
let currentUser = null;
let currentCourseId = null;
let currentSubmissionAssignmentId = null;
let globalCourses = [];
let currentEnrollments = [];

// --- HTTP CLIENT & AUTH ---
function getHeaders(isFormData = false) {
    const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        "ngrok-skip-browser-warning": "true",
    };

    // JANGAN set Content-Type jika mengirim FormData (biarkan browser yang set boundary otomatis)
    if (!isFormData) {
        headers["Content-Type"] = "application/json";
    }

    return headers;
}

async function fetchApi(endpoint, options = {}) {
    showLoader(true);
    try {
        // Cek apakah body adalah FormData
        const isFormData = options.body instanceof FormData;

        // Pastikan endpoint benar (tanpa duplikasi /api/v1 jika sudah ada)
        const url = endpoint.startsWith("http")
            ? endpoint
            : BASE_API + endpoint;

        const res = await fetch(url, {
            ...options,
            headers: {
                ...getHeaders(isFormData), // Panggil getHeaders dengan parameter
                ...options.headers, // Merge custom headers jika ada
            },
        });

        if (res.status === 401) throw new Error("Unauthorized");
        if (res.status === 204) return true;

        const json = await res.json().catch(() => null);
        if (!res.ok) {
            // DEBUG: Lihat struktur error response
            console.log("🔍 Full error response:", json);
            console.log("🔍 json.error:", json?.error);
            console.log("🔍 json.message:", json?.message);

            // Prioritas: json.error (detail error) > json.message (generic message)
            const errorMessage =
                json?.error || json?.message || `HTTP ${res.status}`;
            throw new Error(errorMessage);
        }
        return json;
    } catch (e) {
        console.error("API Error:", e);
        if (e.message === "Unauthorized") {
            Swal.fire("Sesi Habis", "Silakan login kembali", "warning").then(
                () => logout(),
            );
            return null;
        }
        // Throw error agar bisa ditangkap di caller
        throw e;
    } finally {
        showLoader(false);
    }
}

// Inject assignment modal HTML if not exists
function ensureAssignmentModalExists() {
    if (!document.getElementById("modal-create-assignment")) {
        const modalHTML = `
        <!-- Modal: Create/Edit Assignment -->
        <div id="modal-create-assignment" class="modal-overlay hidden">
            <div class="modal-container max-w-2xl">
                <div class="modal-header">
                    <h3 class="modal-title">Buat Tugas Baru</h3>
                    <button onclick="closeModal('modal-create-assignment')" class="modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form onsubmit="event.preventDefault(); submitAssignment();" class="modal-body">
                    <input type="hidden" id="assignId">

                    <div class="mb-4">
                        <label for="assignTitle" class="block text-sm font-bold mb-2">
                            Judul Tugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="assignTitle" required
                            class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="assignDesc" class="block text-sm font-bold mb-2">Deskripsi</label>
                        <textarea id="assignDesc" rows="3"
                            class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="assignDate" class="block text-sm font-bold mb-2">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="assignDate" required
                                class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label for="assignScore" class="block text-sm font-bold mb-2">Nilai Maksimal</label>
                            <input type="number" id="assignScore" value="100" min="0"
                                class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="assignFile" class="block text-sm font-bold mb-2">
                            File Soal (Opsional)
                        </label>
                        <input type="file" id="assignFile"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.doc,.docx,.zip,.rar">
                        <p class="text-xs text-slate-400 mt-1">Format: PDF, DOC, DOCX, ZIP, RAR (Max: 10MB)</p>
                    </div>

                    <div class="flex gap-2 pt-4 border-t">
                        <button type="button" onclick="closeModal('modal-create-assignment')"
                            class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded text-sm font-bold hover:bg-slate-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>`;
        document.body.insertAdjacentHTML("beforeend", modalHTML);
    }
}

async function initApp() {
    const token = localStorage.getItem("auth_token");

    // 1. Cek token - redirect ke login jika tidak ada
    if (!token) {
        window.location.replace("/login");
        return;
    }

    try {
        showLoader(true);

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

        // 3. VALIDASI ROLE - Pastikan user adalah instructor
        if (currentUser.role !== "instructor") {
            await Swal.fire({
                icon: "error",
                title: "Akses Ditolak",
                text: `Akun Anda adalah '${currentUser.role}'. Halaman ini khusus untuk Instructor.`,
                confirmButtonText: "OK",
            });
            localStorage.removeItem("auth_token");
            localStorage.removeItem("current_user");
            window.location.replace("/login");
            return;
        }

        // Update sidebar
        document.getElementById("sidebarUserName").innerText =
            currentUser.profile?.full_name || currentUser.name;

        loadDashboard();
    } catch (e) {
        console.error("Init Error:", e);

        if (
            e.message.includes("tidak valid") ||
            e.message.includes("Token") ||
            e.message.includes("Unauthorized")
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
            await Swal.fire({
                icon: "error",
                title: "Terjadi Kesalahan",
                text: e.message,
                confirmButtonText: "OK",
            });
        }
    } finally {
        showLoader(false);
    }

    // Ensure modals exist
    ensureAssignmentModalExists();
}

// --- DASHBOARD ---
async function loadDashboard() {
    const res = await fetchApi("/courses");
    if (!res) return;

    let allCourses = Array.isArray(res) ? res : res.data || [];
    // Filter: Only courses taught by this instructor
    const myCourses = allCourses.filter((c) => {
        if (currentUser?.role === "admin") return true;

        // Filter berdasarkan instructor_id yang cocok dengan profile.id
        // Backend mengirim course dengan instructor_id yang sesuai dengan instructor profile id
        if (currentUser.profile && currentUser.profile.id) {
            return c.instructor_id === currentUser.profile.id;
        }

        // Fallback: cek jika ada nested instructor object
        if (c.instructor && c.instructor.id) {
            return (
                c.instructor.id ===
                (currentUser.profile ? currentUser.profile.id : null)
            );
        }

        return false;
    });

    globalCourses = myCourses;

    // Calc stats
    const totalStudents = myCourses.reduce(
        (acc, c) => acc + (c.max_students || c.enrollments?.length || 0),
        0,
    );
    // const totalAssignments = myCourses.reduce((acc, c) => acc + (c.assignments_count || 0), 0);

    document.getElementById("statCourses").innerText = myCourses.length;
    document.getElementById("statStudents").innerText = totalStudents;
    // document.getElementById('statAssignments').innerText = totalAssignments;

    renderCourseList(myCourses, "dashboardCoursesList");
    renderCourseList(myCourses, "coursesList"); // Also populate Courses Tab
}

function renderCourseList(courses, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML =
            '<div class="col-span-full text-center py-10 text-slate-400">Belum ada kursus.</div>';
        return;
    }

    container.innerHTML = courses
        .map(
            (c) => `
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200 group cursor-pointer">
                <div class="h-24 bg-gradient-to-r from-indigo-500 to-purple-600 p-4 flex items-end">
                    <h4 class="text-white font-bold text-lg truncate w-full">${c.course_name}</h4>
                </div>
                <div class="p-5">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600">${c.course_code}</span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">${c.status || "Active"}</span>
                    </div>
                    <p class="text-sm text-slate-500 line-clamp-2 mb-4">${c.description || "Tidak ada deskripsi."}</p>
                    <button onclick="openCourseDetail(${c.id})" class="w-full py-2 bg-white border border-indigo-200 text-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-50 transition-colors">
                        Kelola Kursus
                    </button>
                </div>
            </div>
        `,
        )
        .join("");
}

// --- COURSE DETAIL & TABS ---
async function openCourseDetail(id) {
    currentCourseId = id;
    const course = await fetchApi(`/courses/${id}`);
    if (course) {
        document.getElementById("detailTitle").innerText = course.course_name;
        document.getElementById("detailCode").innerText = course.course_code;
        document.getElementById("detailDesc").innerText =
            course.description || "";

        currentEnrollments = course.enrollments || [];
        renderStudentsTable(currentEnrollments);
        renderAssignmentsList(course.assignments || []);

        // Switch view
        navTo("course-detail");
        switchTab("modules"); // Default tab
        // loadModulesTab called inside switchTab
    }
}

function backToCourseDetail() {
    // 1. Pindah tampilan ke halaman detail kursus
    navTo("course-detail");

    // 2. Otomatis buka tab "assignments" (Tugas)
    switchTab("assignments");
}

function switchTab(tabName) {
    document
        .querySelectorAll(".tab-content")
        .forEach((el) => el.classList.add("hidden"));
    document.getElementById(`tab-${tabName}`).classList.remove("hidden");

    document
        .querySelectorAll(".tab-btn")
        .forEach((el) => el.classList.remove("active"));
    document.getElementById(`tab-btn-${tabName}`).classList.add("active");

    // Lazy load data based on tab
    if (tabName === "modules") loadModulesTab();
    if (tabName === "attendance") loadAttendanceTab();
    if (tabName === "certificates") loadCertificatesTab();
}

// --- MODULES ---
async function loadModulesTab() {
    const container = document.getElementById("modulesAccordion");
    container.innerHTML =
        '<div class="text-center py-4 text-slate-400">Memuat modul...</div>';

    // Fetch modules & materials
    // Note: Idealnya ada endpoint /courses/{id}/modules yang return nested
    const allModules = await fetchApi("/course-modules");
    const allMaterials = await fetchApi("/materials");

    if (allModules) {
        // Filter manual (karena endpoint return all)
        let modules = (
            Array.isArray(allModules) ? allModules : allModules.data || []
        ).filter((m) => m.course_id == currentCourseId);
        let materials = Array.isArray(allMaterials)
            ? allMaterials
            : allMaterials.data || [];

        modules.sort((a, b) => a.module_order - b.module_order);

        if (modules.length === 0) {
            container.innerHTML =
                '<div class="text-center py-8 text-slate-400">Belum ada modul.</div>';
        } else {
            container.innerHTML = modules
                .map((m) => {
                    const myMaterials = materials.filter(
                        (mat) =>
                            mat.module_id == m.id ||
                            mat.course_module_id == m.id,
                    );
                    return renderModuleItem(m, myMaterials);
                })
                .join("");
        }
    } else {
        container.innerHTML =
            '<div class="text-center text-red-400">Gagal memuat modul.</div>';
    }
}

function renderModuleItem(m, materials) {
    // URL Base untuk storage file (Ganti sesuai domain backendmu)
    const STORAGE_URL = "https://portohansgunawan.my.id/storage/app/public/";

    return `
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 p-4 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition-colors" onclick="document.getElementById('mod-body-${m.id}').classList.toggle('hidden')">
                    <div class="font-bold text-slate-700">${m.title || m.module_name}</div>
                    <div class="flex items-center gap-2">
                        <button onclick="event.stopPropagation(); openEditModuleModal(${m.id}, '${m.title || m.module_name}', ${m.module_order})" class="p-1 text-slate-400 hover:text-blue-600"><i class="bi bi-pencil"></i></button>
                        <button onclick="event.stopPropagation(); deleteModule(${m.id})" class="p-1 text-slate-400 hover:text-red-500"><i class="bi bi-trash"></i></button>
                        <i class="bi bi-chevron-down text-slate-400 text-xs ml-2"></i>
                    </div>
                </div>
                <div id="mod-body-${m.id}" class="hidden bg-white p-4 border-t border-slate-100">
                    <div class="space-y-2">
                        ${materials
                            .map((mat) => {
                                // Logic link
                                let fullLink = "#";
                                if (mat.material_type === "file") {
                                    // Jika file, gabungkan domain storage + path
                                    // Path di DB: "materials/filename.pdf" -> Link: "https://domain.com/storage/materials/filename.pdf"
                                    fullLink = mat.content_path
                                        ? STORAGE_URL + mat.content_path
                                        : "#";
                                } else {
                                    // Jika link/video, pakai langsung
                                    fullLink = mat.content_path || "#";
                                }

                                return `
                            <div class="flex justify-between items-center p-2 hover:bg-slate-50 rounded group">
                                <div class="flex items-center gap-3">
                                    <i class="${getMaterialIcon(mat.material_type)} text-lg text-indigo-500"></i>
                                    <div>
                                        <div class="text-sm font-medium text-slate-700">${mat.title}</div>
                                        ${mat.content_path ? `<a href="${fullLink}" target="_blank" class="text-[10px] text-blue-500 hover:underline">Buka Materi</a>` : ""}
                                    </div>
                                </div>
                                <button onclick="deleteMaterial(${mat.id})" class="text-slate-300 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                            </div>
                            `;
                            })
                            .join("")}
                    </div>
                    <button onclick="openCreateMaterialModal(${m.id})" class="mt-3 w-full py-1.5 border border-dashed border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-50 hover:text-accent">+ Tambah Materi</button>
                </div>
            </div>
        `;
}

// --- MODULE ACTIONS (EDIT & DELETE) ---

function openEditModuleModal(id, title, order) {
    // 1. Ganti Judul Modal
    document.getElementById("moduleModalTitle").innerText = "Edit Modul";

    // 2. Isi Form dengan Data Lama
    document.getElementById("moduleId").value = id;
    document.getElementById("moduleTitle").value = title;
    document.getElementById("moduleOrder").value = order;

    // 3. Buka Modal
    openModal("modal-create-module");
}

// Reset modal title saat mau create baru (karena modalnya dipakai bareng)
// Update fungsi openModal yang lama atau buat fungsi khusus openCreateModuleModal
function openCreateModuleModal() {
    document.getElementById("formCreateModule").reset();
    document.getElementById("moduleId").value = ""; // Pastikan ID kosong
    document.getElementById("moduleModalTitle").innerText = "Tambah Modul"; // Reset judul
    openModal("modal-create-module");
}

async function deleteModule(id) {
    const result = await Swal.fire({
        title: "Hapus Modul?",
        text: "Semua materi di dalamnya juga akan terhapus!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        // Panggil API Delete
        const res = await fetchApi(`/course-modules/${id}`, {
            method: "DELETE",
        });

        if (res || res === true) {
            Swal.fire("Terhapus!", "Modul berhasil dihapus.", "success");
            switchTab("modules"); // Refresh tampilan
        }
    }
}

function getMaterialIcon(type) {
    if (type === "video") return "bi bi-play-circle-fill";
    if (type === "link") return "bi bi-link-45deg";
    return "bi bi-file-earmark-text-fill";
}

// --- MATERIALS & MODULES ACTIONS ---

function openCreateMaterialModal(moduleId) {
    document.getElementById("formCreateMaterial").reset();
    document.getElementById("materialId").value = "";
    document.getElementById("materialModuleId").value = moduleId;
    document.getElementById("materialModalTitle").innerText =
        "Tambah Materi Baru";
    toggleMaterialInput();
    openModal("modal-create-material");
}

async function submitMaterial() {
    const id = document.getElementById("materialId").value;
    const moduleId = document.getElementById("materialModuleId").value;
    const type = document.getElementById("materialType").value;

    // Gunakan FormData agar bisa upload file fisik
    const formData = new FormData();
    formData.append("module_id", moduleId);
    formData.append("title", document.getElementById("materialTitle").value);
    formData.append("material_type", type);

    if (id) {
        formData.append("_method", "PUT"); // Laravel method spoofing
    }

    if (type === "file") {
        const fileInput = document.getElementById("materialFile");
        if (fileInput.files.length > 0) {
            // PENTING: Backend harus menerima key 'content_file' sesuai request di MaterialController
            formData.append("content_file", fileInput.files[0]);
        }
    } else {
        formData.append(
            "content_url",
            document.getElementById("materialUrl").value,
        );
    }

    const url = id ? `/materials/${id}` : "/materials";
    // Method POST digunakan untuk FormData, _method akan handle PUT di server
    const res = await fetchApi(url, { method: "POST", body: formData });

    if (res) {
        closeModal("modal-create-material");
        Swal.fire("Berhasil", "Materi berhasil disimpan", "success");
        loadModulesTab();
    }
}

async function deleteMaterial(id) {
    const result = await Swal.fire({
        title: "Hapus Materi?",
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Ya, Hapus!",
    });

    if (result.isConfirmed) {
        const res = await fetchApi(`/materials/${id}`, { method: "DELETE" });
        if (res || res === true) {
            Swal.fire("Terhapus!", "Materi telah dihapus.", "success");
            loadModulesTab();
        }
    }
}

async function submitModule() {
    const id = document.getElementById("moduleId").value;
    const payload = {
        course_id: parseInt(currentCourseId),
        title: document.getElementById("moduleTitle").value,
        module_order: parseInt(document.getElementById("moduleOrder").value),
    };
    const method = id ? "PUT" : "POST";
    const url = id ? `/course-modules/${id}` : "/course-modules";

    closeModal("modal-create-module");
    const res = await fetchApi(url, { method, body: JSON.stringify(payload) });
    if (res) loadModulesTab();
}

function toggleMaterialInput() {
    const type = document.getElementById("materialType").value;
    if (type === "file") {
        document.getElementById("materialFileInput").classList.remove("hidden");
        document.getElementById("materialUrlInput").classList.add("hidden");
    } else {
        document.getElementById("materialFileInput").classList.add("hidden");
        document.getElementById("materialUrlInput").classList.remove("hidden");
    }
}

// --- ASSIGNMENTS ---
function renderAssignmentsList(list) {
    const container = document.getElementById("assignmentListContainer");
    if (!list.length) {
        container.innerHTML =
            '<div class="text-center py-10 text-slate-400">Belum ada tugas.</div>';
        return;
    }
    container.innerHTML = list
        .map(
            (a) => `
            <div class="p-4 bg-white border border-slate-200 rounded-lg shadow-sm flex justify-between items-center">
                <div>
                    <h5 class="font-bold text-slate-800">${a.title}</h5>
                    <p class="text-xs text-slate-500 mt-1">Due: ${new Date(a.due_date).toLocaleString()}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="viewSubmissions(${a.id})" class="px-3 py-1.5 text-xs font-bold text-accent bg-indigo-50 rounded hover:bg-indigo-100">Lihat Jawaban</button>
                    <button onclick="deleteAssignment(${a.id})" class="px-2 py-1.5 text-xs text-red-400 hover:text-red-600"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `,
        )
        .join("");
}

async function submitAssignment() {
    const id = document.getElementById("assignId").value;
    const titleValue = document.getElementById("assignTitle").value;
    const descValue = document.getElementById("assignDesc").value;
    const dateValue = document.getElementById("assignDate").value;
    const scoreValue = document.getElementById("assignScore").value;
    const fileInput = document.getElementById("assignFile");

    // Validate inputs
    if (!titleValue) {
        await Swal.fire({
            icon: "error",
            title: "Error",
            text: "Judul tugas harus diisi!",
        });
        return;
    }

    if (!dateValue) {
        await Swal.fire({
            icon: "error",
            title: "Error",
            text: "Due date harus diisi!",
        });
        return;
    }

    // Check if file upload uses FormData or JSON
    const hasFile = fileInput && fileInput.files && fileInput.files[0];

    let requestBody;
    let isFormData = false;

    if (hasFile) {
        // Use FormData for file upload
        const formData = new FormData();
        formData.append("course_id", parseInt(currentCourseId));
        formData.append("title", titleValue);
        formData.append("description", descValue || "");
        // Convert to MySQL datetime format: YYYY-MM-DD HH:MM:SS
        const dueDate = new Date(dateValue);
        const mysqlDate =
            dueDate.getFullYear() +
            "-" +
            String(dueDate.getMonth() + 1).padStart(2, "0") +
            "-" +
            String(dueDate.getDate()).padStart(2, "0") +
            " " +
            String(dueDate.getHours()).padStart(2, "0") +
            ":" +
            String(dueDate.getMinutes()).padStart(2, "0") +
            ":" +
            String(dueDate.getSeconds()).padStart(2, "0");
        formData.append("due_date", mysqlDate);
        formData.append("max_score", scoreValue ? parseInt(scoreValue) : 100);
        formData.append("file", fileInput.files[0]);

        requestBody = formData;
        isFormData = true;

        console.log("🔍 Assignment with File Upload");
    } else {
        // Use JSON for no file upload
        const payload = {
            course_id: parseInt(currentCourseId),
            title: titleValue,
            description: descValue || "",
            // Convert to MySQL datetime format: YYYY-MM-DD HH:MM:SS
            due_date: (() => {
                const dueDate = new Date(dateValue);
                return (
                    dueDate.getFullYear() +
                    "-" +
                    String(dueDate.getMonth() + 1).padStart(2, "0") +
                    "-" +
                    String(dueDate.getDate()).padStart(2, "0") +
                    " " +
                    String(dueDate.getHours()).padStart(2, "0") +
                    ":" +
                    String(dueDate.getMinutes()).padStart(2, "0") +
                    ":" +
                    String(dueDate.getSeconds()).padStart(2, "0")
                );
            })(),
            max_score: scoreValue ? parseInt(scoreValue) : 100,
            content_path: null, // Add default content_path
        };

        requestBody = JSON.stringify(payload);
        console.log("🔍 Assignment Payload:", payload);
    }

    closeModal("modal-create-assignment");

    try {
        const res = await fetchApi(id ? `/assignments/${id}` : "/assignments", {
            method: id ? "PUT" : "POST",
            body: requestBody,
        });

        if (res) {
            await Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: id ? "Tugas berhasil diupdate" : "Tugas berhasil dibuat",
                timer: 2000,
                showConfirmButton: false,
            });

            switchTab("assignments");
            const course = await fetchApi(`/courses/${currentCourseId}`);
            renderAssignmentsList(course.assignments || []);
        }
    } catch (error) {
        console.error("Error creating assignment:", error);
        await Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.error || "Terjadi kesalahan saat membuat tugas",
        });
    }
}

async function viewSubmissions(id) {
    currentSubmissionAssignmentId = id;
    const STORAGE_URL = "https://portohansgunawan.my.id/storage/app/public/";
    const a = await fetchApi(`/assignments/${id}`);
    if (a) {
        document.getElementById("submissionTitle").innerText =
            `Jawaban: ${a.title}`;
        const tbody = document.getElementById("submissionTableBody");
        const list = a.submissions || [];
        tbody.innerHTML = list.length
            ? list
                  .map(
                      (s) => `
                <tr class="border-b hover:bg-slate-50">
                    <td class="px-4 py-3">${s.enrollment?.student?.full_name || "Siswa"}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">${new Date(s.created_at).toLocaleString()}</td>
                    <td class="px-4 py-3">${s.file_path ? `<a href="${STORAGE_URL}${s.file_path}" target="_blank" class="text-blue-500 underline text-xs">File</a>` : "-"}</td>
                    <td class="px-4 py-3 font-bold">${s.grade ? s.grade : "undefined"}</td>
                    <td class="px-4 py-3 font-bold">${s.feedback ? s.feedback : "undefined"}</td>
                    <td class="px-4 py-3">
                        <button
                            class="text-xs bg-slate-800 text-white px-2 py-1 rounded hover:bg-slate-700"
                            onclick="openSubmissionGrading(${s.id}, '${s.grade || ""}', '${s.feedback || ""}')">
                            Nilai
                        </button>
                    </td>
                </tr>
            `,
                  )
                  .join("")
            : '<tr><td colspan="5" class="text-center p-4 text-slate-400">Belum ada pengumpulan.</td></tr>';
        navTo("submissions");
    }
}

// Fungsi khusus untuk membuka modal penilaian TUGAS (Submission)
function openSubmissionGrading(submissionId, currentGrade, currentFeedback) {
    // Set ID submission
    document.getElementById("gradeSubmissionId").value = submissionId;

    // Kosongkan enrollment & component ID karena tidak dipakai di update submission
    document.getElementById("gradeEnrollmentId").value = "";
    document.getElementById("gradeComponentId").value = "";

    // Isi nilai yang sudah ada (jika edit)
    document.getElementById("gradeValue").value =
        currentGrade !== "null" && currentGrade !== "undefined"
            ? currentGrade
            : "";
    document.getElementById("gradeFeedback").value =
        currentFeedback !== "null" && currentFeedback !== "undefined"
            ? currentFeedback
            : "";

    // Ubah tombol simpan agar memanggil fungsi yang benar
    const saveBtn = document.querySelector(
        '#modal-grading button[onclick*="submitGrade"]',
    );
    saveBtn.setAttribute("onclick", "submitSubmissionGrade()"); // Arahkan ke fungsi baru

    openModal("modal-grading");
}

async function submitSubmissionGrade() {
    const submissionId = document.getElementById("gradeSubmissionId").value;
    const score = document.getElementById("gradeValue").value;
    const feedback = document.getElementById("gradeFeedback").value;

    if (score === "" || score < 0 || score > 100) {
        Swal.fire("Invalid", "Nilai harus antara 0 - 100", "warning");
        return;
    }

    // Payload sesuai API SubmissionController@update
    const payload = {
        grade: parseFloat(score),
        feedback: feedback,
    };

    // Kirim ke Endpoint SUBMISSION (PUT), bukan Grades
    const res = await fetchApi(`/submissions/${submissionId}`, {
        method: "PUT",
        body: JSON.stringify(payload),
    });

    if (res) {
        closeModal("modal-grading");
        Swal.fire("Berhasil", "Nilai tugas berhasil disimpan", "success");

        // Refresh halaman list submission (ambil ID assignment dari judul atau simpan di var global)
        // Cara gampang: kembali ke list assignment lalu buka lagi, atau refresh viewSubmissions jika ada variable global assignmentId
        // Di sini kita asumsikan user klik kembali atau kita reload view ini jika ada currentAssignmentId (perlu diset di viewSubmissions)

        // Opsional: Refresh otomatis jika Anda menyimpan variable assignmentId saat viewSubmissions
        // if (typeof currentAssignmentId !== 'undefined') viewSubmissions(currentAssignmentId);

        // Atau kembali ke daftar tugas agar data ter-refresh
        viewSubmissions(currentSubmissionAssignmentId);
    }
}

// --- ATTENDANCE ---
async function loadAttendanceTab() {
    const res = await fetchApi(
        `/attendance-sessions/course/${currentCourseId}/all`,
    );
    const list = document.getElementById("sessionListGroup");
    const data = (Array.isArray(res) ? res : res?.data) || [];

    list.innerHTML = data.length
        ? data
              .map(
                  (s) => `
            <div onclick="loadSessionDetail(${s.id}, '${s.session_name}')" class="p-3 bg-white border border-slate-200 rounded cursor-pointer hover:border-accent hover:shadow-sm transition-all">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-sm text-slate-700">${s.session_name}</span>
                    <span class="text-[10px] uppercase font-bold ${s.status === "open" ? "text-green-600 bg-green-50" : "text-slate-500 bg-slate-100"} px-1.5 py-0.5 rounded">${s.status}</span>
                </div>
                <div class="text-xs text-slate-400">${new Date(s.start_time).toLocaleDateString()}</div>
            </div>
        `,
              )
              .join("")
        : '<div class="text-center text-xs text-slate-400 py-4">Belum ada sesi.</div>';
}

async function loadSessionDetail(sid, name) {
    document.getElementById("sessionDetailHeader").innerText = name;
    const tbody = document.getElementById("attendanceTableBody");
    tbody.innerHTML =
        '<tr><td colspan="3" class="text-center p-4 text-slate-400">Memuat data...</td></tr>';

    const res = await fetchApi(`/attendance-records/session/${sid}/records`);
    const recs = (Array.isArray(res) ? res : res?.data) || [];

    tbody.innerHTML = recs.length
        ? recs
              .map(
                  (r) => `
            <tr class="border-b hover:bg-slate-50">
                <td class="px-4 py-2 text-sm font-medium text-slate-700">${r.enrollment?.student?.full_name || "Siswa"}</td>
                <td class="px-4 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${
                        r.status === "present"
                            ? "bg-green-100 text-green-700"
                            : r.status === "sick"
                              ? "bg-yellow-100 text-yellow-700"
                              : r.status === "absent"
                                ? "bg-red-100 text-red-700"
                                : "bg-slate-100 text-slate-500"
                    }">
                        ${r.status === "present" ? "✓ Hadir" : r.status === "sick" ? "⚕ Sakit" : r.status === "absent" ? "✗ Alfa" : r.status}
                    </span>
                </td>
                <td class="px-4 py-2 text-right">
                    <div class="flex justify-end gap-1">
                        <button onclick="markAttendance(${sid}, ${r.enrollment_id}, 'present')"
                                class="w-6 h-6 rounded bg-green-100 text-green-700 hover:bg-green-200 text-xs font-bold transition-all ${r.status === "present" ? "ring-2 ring-green-500" : ""}"
                                title="Hadir">H</button>
                        <button onclick="markAttendance(${sid}, ${r.enrollment_id}, 'sick')"
                                class="w-6 h-6 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200 text-xs font-bold transition-all ${r.status === "sick" ? "ring-2 ring-yellow-500" : ""}"
                                title="Sakit">S</button>
                        <button onclick="markAttendance(${sid}, ${r.enrollment_id}, 'absent')"
                                class="w-6 h-6 rounded bg-red-100 text-red-700 hover:bg-red-200 text-xs font-bold transition-all ${r.status === "absent" ? "ring-2 ring-red-500" : ""}"
                                title="Alfa">A</button>
                    </div>
                </td>
            </tr>
        `,
              )
              .join("")
        : '<tr><td colspan="3" class="text-center p-4 text-slate-400">Tidak ada data siswa di sesi ini.</td></tr>';
}

async function markAttendance(sid, eid, status) {
    console.log("🎯 markAttendance called:", { sid, eid, status });

    try {
        // Tampilkan loading state
        const button = event?.target;
        if (button) {
            button.disabled = true;
            button.classList.add("opacity-50");
        }

        const res = await fetchApi(`/attendance-records/mark/${sid}/${eid}`, {
            method: "POST",
            body: JSON.stringify({ status }),
        });

        console.log("✅ Mark attendance response:", res);

        if (res) {
            // Show success feedback
            await Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: `Status absensi diubah menjadi ${status === "present" ? "Hadir" : status === "sick" ? "Sakit" : "Alfa"}`,
                timer: 1500,
                showConfirmButton: false,
            });
        }

        // Refresh table
        const name = document.getElementById("sessionDetailHeader").innerText;
        await loadSessionDetail(sid, name);
    } catch (error) {
        console.error("❌ Error marking attendance:", error);
        await Swal.fire({
            icon: "error",
            title: "Gagal!",
            text: "Gagal mengubah status absensi: " + error.message,
        });
    }
}

// --- STUDENTS & CERTIFICATES ---
function renderStudentsTable(list) {
    const tbody = document.getElementById("studentListBody");
    tbody.innerHTML = list.length
        ? list
              .map(
                  (e) => `
            <tr class="border-b hover:bg-slate-50">
                <td class="px-6 py-3 font-medium text-slate-700">${e.student?.full_name || "Unknown"}</td>
                <td class="px-6 py-3 text-slate-500">${e.student?.user?.email || "-"}</td>
                <td class="px-6 py-3 text-center"><span class="text-xs bg-emerald-50 text-emerald-600 px-2 py-1 rounded font-bold">Active</span></td>
            </tr>
        `,
              )
              .join("")
        : '<tr><td colspan="3" class="text-center p-4 text-slate-400">Belum ada siswa terdaftar.</td></tr>';
}

async function loadCertificatesTab() {
    const container = document.getElementById("certificatesContainer");
    container.innerHTML =
        '<div class="col-span-full text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500 mx-auto"></div></div>';

    try {
        const certRes = await fetchApi(
            `/certificates/course/${currentCourseId}`,
        );
        const issuedCerts =
            (Array.isArray(certRes) ? certRes : certRes?.data) || [];

        if (!currentEnrollments.length) {
            container.innerHTML =
                '<div class="col-span-full text-center text-slate-400">Tidak ada siswa.</div>';
            return;
        }

        container.innerHTML = "";

        for (const enrollment of currentEnrollments) {
            const cert = issuedCerts.find(
                (c) => c.student_id == enrollment.student_id,
            );
            const name = enrollment.student?.full_name || "Siswa";

            if (cert) {
                // Issued Card (Green)
                container.innerHTML += `
                        <div class="bg-white p-5 rounded-xl border border-emerald-200 shadow-sm relative overflow-hidden h-full flex flex-col transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                            <div class="absolute top-0 right-0 p-2"><i class="bi bi-patch-check-fill text-emerald-500 text-xl"></i></div>
                            <h5 class="font-bold text-slate-800">${name}</h5>
                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-bold w-max mt-1">Certified</span>
                            <p class="text-xs text-slate-500 mt-2 font-mono">${cert.certificate_code}</p>
                            <div class="mt-auto pt-4 flex gap-2">
                                <button onclick="window.open('${BASE_API}/certificates/${cert.id}/download?token=${localStorage.getItem("auth_token")}')" class="flex-1 py-1.5 border border-emerald-500 text-emerald-600 rounded text-xs font-bold hover:bg-emerald-50">Download</button>
                            </div>
                        </div>`;
            } else {
                // Progress Card
                const eligibility = await fetchApi(
                    `/certificates/eligibility/${enrollment.id}`,
                );
                const att = eligibility?.details?.attendance_percentage || 0;
                const ass =
                    eligibility?.details?.assignment_completion_rate || 0;
                const errors = eligibility?.errors || [];

                // Generate Requirements List
                let reqList = "";
                if (errors.length > 0) {
                    reqList = errors
                        .map(
                            (e) =>
                                `<li class="text-[11px] text-slate-500">${e}</li>`,
                        )
                        .join("");
                } else {
                    reqList = `<li class="text-[11px] text-emerald-600 font-bold">All requirements met! Ready to generate.</li>`;
                }

                container.innerHTML += `
                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm h-full flex flex-col transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="font-bold text-slate-800 text-base">${name}</h5>
                                <span class="bg-slate-600 text-white text-[10px] px-2 py-1 rounded font-bold tracking-wide">In Progress</span>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center text-sm border-b border-slate-100 pb-2">
                                    <span class="text-slate-500">Attendance</span>
                                    <span class="font-mono font-bold ${att >= 75 ? "text-emerald-600" : "text-slate-700"}">${att}% / 75%</span>
                                </div>
                                <div class="flex justify-between items-center text-sm pb-2">
                                    <span class="text-slate-500">Assignments</span>
                                    <span class="font-mono font-bold ${ass >= 80 ? "text-emerald-600" : "text-slate-700"}">${ass}% / 80%</span>
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mt-auto">
                                <div class="flex items-center gap-1.5 mb-2 text-xs text-slate-400">
                                    <i class="bi bi-info-circle"></i> <span>Requirements:</span>
                                </div>
                                <ul class="list-disc pl-4 space-y-1">
                                    ${reqList}
                                </ul>
                            </div>
                        </div>`;
            }
        }
    } catch (e) {
        console.error(e);
        container.innerHTML =
            '<div class="text-red-400 text-center col-span-full">Gagal memuat data.</div>';
    }
}

async function bulkGenerateCertificates() {
    if (
        await Swal.fire({
            title: "Generate Otomatis?",
            text: "Sistem akan mengecek semua siswa yang memenuhi syarat.",
            icon: "question",
            showCancelButton: true,
        }).then((r) => r.isConfirmed)
    ) {
        const res = await fetchApi(
            `/certificates/bulk-generate/${currentCourseId}`,
            { method: "POST" },
        );
        if (res) {
            Swal.fire(
                `${res.message || "Berhasil"}`,
                `${res.summary.already_exists_count || 0} sertifikat baru dibuat.`,
                "success",
            );
            loadCertificatesTab();
        }
    }
}

// --- ANNOUNCEMENTS ---
async function loadAnnouncements(filter = {}) {
    const container = document.getElementById("announcementList");
    container.innerHTML =
        '<div class="col-span-full text-center py-10"><div class="spinner-border text-primary"></div></div>';

    const res = await fetchApi("/announcements");
    const list = (Array.isArray(res) ? res : res?.data) || [];

    const filtered = filter.status
        ? list.filter((a) => a.status === filter.status)
        : list;

    if (!filtered.length) {
        container.innerHTML =
            '<div class="col-span-full text-center text-slate-400 py-10">Belum ada pengumuman.</div>';
        return;
    }

    container.innerHTML = filtered
        .map(
            (a) => `
            <div class="bg-white p-5 rounded-xl border-l-4 ${a.priority === "urgent" ? "border-red-500" : "border-indigo-500"} shadow-sm">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 px-2 py-1 rounded text-slate-500">${a.course ? a.course.course_code : "GLOBAL"}</span>
                    <span class="text-[10px] ${a.status === "published" ? "text-green-600" : "text-amber-600"} font-bold bg-white border px-2 py-0.5 rounded-full">${a.status}</span>
                </div>
                <h4 class="font-bold text-slate-800 text-lg mb-1">${a.title}</h4>
                <p class="text-sm text-slate-600 mb-3">${a.content}</p>
                <div class="text-xs text-slate-400 flex items-center gap-1"><i class="bi bi-clock"></i> ${new Date(a.created_at).toLocaleDateString()}</div>
            </div>
        `,
        )
        .join("");
}

async function submitAnnouncement() {
    const payload = {
        title: document.getElementById("annTitle").value,
        content: document.getElementById("annContent").value,
        course_id: document.getElementById("annCourseId").value || null,
        announcement_type: document.getElementById("annCourseId").value
            ? "course"
            : "global",
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
        loadAnnouncements();
    }
}

function openAnnouncementModal() {
    const sel = document.getElementById("annCourseId");
    globalCourses.forEach(
        (c) =>
            (sel.innerHTML += `<option value="${c.id}">${c.course_name}</option>`),
    );
    openModal("modal-announcement");
}

// --- UTILS & MODALS ---
function navTo(page) {
    document
        .querySelectorAll(".page-view")
        .forEach((v) => v.classList.add("hidden"));
    document.getElementById("view-" + page).classList.remove("hidden");

    document
        .querySelectorAll(".nav-link")
        .forEach((n) => n.classList.remove("active"));
    const activeNav = document.getElementById("nav-" + page);
    if (activeNav) activeNav.classList.add("active");

    if (page === "courses") loadDashboard();
    if (page === "announcements") loadAnnouncements();
    if (page === "profile") loadProfile();
}

function showLoader(show) {
    document.getElementById("loader").style.display = show ? "flex" : "none";
}
function openModal(id) {
    document.getElementById(id).classList.remove("hidden");
}
function closeModal(id) {
    document.getElementById(id).classList.add("hidden");
}

function openCreateSessionModal() {
    document.getElementById("formCreateSession").reset();
    const now = new Date().toISOString().slice(0, 16);
    document.getElementById("sessionStartTime").value = now;
    document.getElementById("sessionEndTime").value = now;
    document.getElementById("sessionDeadline").value = now;
    openModal("modal-create-session");
}

async function submitSession() {
    const payload = {
        course_id: currentCourseId,
        session_name: document.getElementById("sessionName").value,
        start_time: new Date(
            document.getElementById("sessionStartTime").value,
        ).toISOString(),
        end_time: new Date(
            document.getElementById("sessionEndTime").value,
        ).toISOString(),
        deadline: new Date(
            document.getElementById("sessionDeadline").value,
        ).toISOString(),
        status: "open",
    };
    closeModal("modal-create-session");
    const res = await fetchApi("/attendance-sessions", {
        method: "POST",
        body: JSON.stringify(payload),
    });
    if (res) {
        loadAttendanceTab();
        Swal.fire("Berhasil", "Sesi absensi dibuat.", "success");
    }
}

function openGradingModal(submissionId, enrollmentId, gradeComponentId) {
    document.getElementById("gradeSubmissionId").value = submissionId;
    document.getElementById("gradeEnrollmentId").value = enrollmentId;
    // tambah hidden input baru di modal untuk grade_component_id
    document.getElementById("gradeComponentId").value = gradeComponentId;
    document.getElementById("gradeValue").value = "";
    document.getElementById("gradeFeedback").value = "";
    openModal("modal-grading");
}

async function submitGrade() {
    const enrollmentId = document.getElementById("gradeEnrollmentId").value;
    const gradeComponentId = document.getElementById("gradeComponentId").value;
    const score = Number(document.getElementById("gradeValue").value);
    const feedback = document.getElementById("gradeFeedback").value;

    if (!gradeComponentId) {
        Swal.fire("Error", "Grade component id tidak ditemukan.", "error");
        return;
    }
    if (Number.isNaN(score) || score < 0 || score > 100) {
        Swal.fire("Invalid", "Nilai harus antara 0 - 100", "warning");
        return;
    }

    const payload = {
        enrollment_id: Number(enrollmentId),
        grade_component_id: Number(gradeComponentId),
        score: score,
        notes: feedback,
        // kalau mau kirim max_score custom: max_score: 100
    };

    closeModal("modal-grading");

    const res = await fetchApi("/grades", {
        method: "POST",
        body: JSON.stringify(payload),
    });

    if (res) {
        Swal.fire("Berhasil", "Nilai tersimpan", "success");
        // panggil fungsi refresh tabel submission kamu di sini
        // misalnya: loadSubmissions(currentAssignmentId);
    }
}

function openAssignmentModal() {
    ensureAssignmentModalExists();

    // Reset form
    document.getElementById("assignId").value = "";
    document.getElementById("assignTitle").value = "";
    document.getElementById("assignDesc").value = "";
    document.getElementById("assignDate").value = "";
    document.getElementById("assignScore").value = "100";

    const fileInput = document.getElementById("assignFile");
    if (fileInput) fileInput.value = "";

    // Set modal title
    const modalTitle = document.querySelector(
        "#modal-create-assignment .modal-title",
    );
    if (modalTitle) modalTitle.textContent = "Buat Tugas Baru";

    openModal("modal-create-assignment");
}

// Initialize
document.addEventListener("DOMContentLoaded", initApp);

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

// Profile Functions
// Profile Functions
async function loadProfile() {
    try {
        showLoader(true);

        // Cek apakah sudah ada data currentUser dari login
        if (!currentUser || !currentUser.profile) {
            await Swal.fire({
                icon: "error",
                title: "Data Tidak Tersedia",
                text: "Data profil belum dimuat. Silakan login kembali.",
                confirmButtonText: "OK",
            });
            window.location.replace("/login");
            return;
        }

        const profile = currentUser.profile;

        // Update profile card
        const name = profile.full_name || currentUser.name;
        document.getElementById("profile-name").innerText = name;
        document.getElementById("profile-code").innerText =
            profile.instructor_code || "N/A";
        document.getElementById("profile-email").innerText =
            profile.email || currentUser.email;
        document.getElementById("profile-spec").innerText =
            profile.specialization || "-";
        document.getElementById("profile-phone").innerText =
            profile.phone || "-";
        document.getElementById("profile-avatar").innerText = name
            .charAt(0)
            .toUpperCase();
        document.getElementById("profile-exp").innerText =
            (profile.experience_years || 0) + " Thn";
        document.getElementById("profile-bio").innerText =
            profile.bio || "Belum ada biografi.";
        document.getElementById("profile-edu").innerText =
            profile.education_level || "-";

        // Load courses from API using instructor endpoint
        const resCourses = await fetchApi(`/instructors/${profile.id}/courses`);
        const myCourses =
            resCourses && resCourses.data
                ? resCourses.data
                : Array.isArray(resCourses)
                  ? resCourses
                  : [];

        // Calculate stats using students_count
        const totalStudents = myCourses.reduce(
            (acc, curr) => acc + (curr.students_count || 0),
            0,
        );
        document.getElementById("profile-total-courses").innerText =
            myCourses.length;
        document.getElementById("profile-total-students").innerText =
            totalStudents;

        // Render course list
        const courseList = document.getElementById("profile-course-list");
        if (myCourses.length === 0) {
            courseList.innerHTML =
                '<tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada kursus.</td></tr>';
        } else {
            courseList.innerHTML = myCourses
                .map(
                    (c) => `
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-6 py-3 font-bold text-slate-700">${c.course_name || c.name}</td>
                        <td class="px-6 py-3 text-xs font-mono">${c.course_code || c.code}</td>
                        <td class="px-6 py-3 text-center">${c.students_count || 0}</td>
                        <td class="px-6 py-3 text-center"><span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold">${c.status || "Active"}</span></td>
                    </tr>
                `,
                )
                .join("");
        }

        showLoader(false);
    } catch (error) {
        console.error("Error loading profile:", error);
        await Swal.fire({
            icon: "error",
            title: "Gagal Memuat Profil",
            text: error.message,
            confirmButtonText: "OK",
        });
        showLoader(false);
    }
}

function openEditProfileModal() {
    // Populate form with current data
    if (!currentUser || !currentUser.profile) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Data profil belum dimuat",
            confirmButtonText: "OK",
        });
        return;
    }
    const profile = currentUser.profile;

    // Fill form
    document.getElementById("edit-profile-name").value =
        profile.full_name || "";
    document.getElementById("edit-profile-email").value = profile.email || "";
    document.getElementById("edit-profile-phone").value = profile.phone || "";
    document.getElementById("edit-profile-spec").value =
        profile.specialization || "";
    document.getElementById("edit-profile-edu").value =
        profile.education_level || "";
    document.getElementById("edit-profile-exp").value =
        profile.experience_years || 0;
    document.getElementById("edit-profile-bio").value = profile.bio || "";

    openModal("modal-edit-profile");
}

async function submitProfileUpdate() {
    try {
        const payload = {
            full_name: document.getElementById("edit-profile-name").value,
            phone: document.getElementById("edit-profile-phone").value,
            specialization: document.getElementById("edit-profile-spec").value,
            education_level: document.getElementById("edit-profile-edu").value,
            experience_years:
                parseInt(document.getElementById("edit-profile-exp").value) ||
                0,
            bio: document.getElementById("edit-profile-bio").value,
        };

        const instructorId = currentUser.profile.id;

        const res = await fetchApi(`/instructors/${instructorId}`, {
            method: "PUT",
            body: JSON.stringify(payload),
        });

        if (res) {
            closeModal("modal-edit-profile");
            await Swal.fire(
                "Berhasil",
                "Profil berhasil diperbarui",
                "success",
            );

            // Update currentUser dengan data baru
            currentUser.profile = { ...currentUser.profile, ...payload };

            // Reload profile display
            loadProfile();
        }
    } catch (error) {
        console.error("Error updating profile:", error);
        Swal.fire("Error", "Gagal memperbarui profil", "error");
    }
}
