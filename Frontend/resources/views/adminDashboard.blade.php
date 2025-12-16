<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin Real-Time Dashboard - SmartDev LMS</title>

        <script src="https://cdn.tailwindcss.com"></script>
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        />
        <link rel="stylesheet" href="{{ asset('css/adminDashboard.css') }}" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />

        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: "#0f172a", // Slate 900
                            accent: "#4f46e5", // Indigo 600
                            success: "#10b981",
                            warning: "#f59e0b",
                            danger: "#ef4444",
                            sidebar: "#1e293b",
                        },
                        fontFamily: { sans: ["Inter", "sans-serif"] },
                    },
                },
            };
        </script>
    </head>

    <body class="flex h-screen overflow-hidden text-slate-800">
        <script>
            (function() {
                if (!localStorage.getItem('auth_token')) {
                    window.location.replace('/login');
                }
            })();
        </script>
        <div id="loader">
            <div class="flex flex-col items-center">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-accent mb-3"
                ></div>
                <span class="text-slate-600 font-bold text-sm tracking-wider"
                    >MENGAMBIL DATA...</span
                >
            </div>
        </div>

        <aside
            class="w-64 bg-sidebar text-white hidden md:flex flex-col shadow-2xl z-20 shrink-0 transition-all duration-300"
        >
            <div class="p-6 border-b border-white/10 flex items-center gap-3">
                <div
                    class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30"
                >
                    <i class="bi bi-shield-lock-fill text-white"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg tracking-tight">SmartDev</h1>
                    <p
                        class="text-[10px] text-slate-400 uppercase tracking-widest"
                    >
                        Admin Panel
                    </p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
                <div
                    class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider"
                >
                    Menu Utama
                </div>
                <div
                    onclick="nav('dashboard')"
                    id="btn-dashboard"
                    class="nav-link active"
                >
                    <i class="bi bi-grid-1x2 me-3 text-lg"></i>
                    <span class="font-medium">Dashboard</span>
                </div>
                <div
                    onclick="nav('registrations')"
                    id="btn-registrations"
                    class="nav-link relative"
                >
                    <i class="bi bi-person-plus me-3 text-lg"></i>
                    <span class="font-medium">Registrasi Siswa</span>
                    <span
                        id="badge-pending"
                        class="absolute right-3 bg-danger text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full hidden shadow-sm"
                        >0</span
                    >
                </div>

                <div
                    class="px-3 mb-2 mt-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider"
                >
                    Manajemen
                </div>
                <div
                    onclick="nav('instructors')"
                    id="btn-instructors"
                    class="nav-link"
                >
                    <i class="bi bi-person-video3 me-3 text-lg"></i>
                    <span class="font-medium">Instruktur</span>
                </div>
                <div
                    onclick="nav('students')"
                    id="btn-students"
                    class="nav-link"
                >
                    <i class="bi bi-people me-3 text-lg"></i>
                    <span class="font-medium">Siswa & User</span>
                </div>
                <div onclick="nav('courses')" id="btn-courses" class="nav-link">
                    <i class="bi bi-journal-bookmark-fill me-3 text-lg"></i>
                    <span class="font-medium">Kursus</span>
                </div>

                <div
                    class="px-3 mb-2 mt-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider"
                >
                    Lainnya
                </div>
                <div
                    onclick="nav('announcements')"
                    id="btn-announcements"
                    class="nav-link"
                >
                    <i class="bi bi-megaphone me-3 text-lg"></i>
                    <span class="font-medium">Pengumuman</span>
                </div>
            </nav>

            <div class="p-4 border-t border-white/10 bg-slate-900/50">
                <button
                    onclick="logout()"
                    class="w-full py-2.5 bg-white/5 hover:bg-red-500/20 text-slate-300 hover:text-red-400 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 group"
                >
                    <i
                        class="bi bi-box-arrow-right group-hover:scale-110 transition-transform"
                    ></i>
                    KELUAR SISTEM
                </button>
            </div>
        </aside>

        <main
            class="flex-1 flex flex-col h-screen overflow-hidden relative bg-slate-50"
        >
            <div
                class="md:hidden bg-sidebar text-white p-4 flex justify-between items-center shadow-md z-30 shrink-0"
            >
                <span class="font-bold flex items-center gap-2"
                    ><i class="bi bi-shield-lock-fill text-accent"></i> Admin
                    Panel</span
                >
                <button
                    onclick="alert('Menu mobile belum aktif')"
                    class="text-xl"
                >
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div
                id="main-scroll"
                class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth"
            >
                <section id="view-dashboard" class="page-view fade-in">
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4"
                    >
                        <div>
                            <h2
                                class="text-3xl font-bold text-slate-800 tracking-tight"
                            >
                                Dashboard Eksekutif
                            </h2>
                            <p
                                class="text-slate-500 mt-1 flex items-center gap-2"
                            >
                                <span
                                    class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"
                                ></span>
                                Sistem Terhubung & Online
                            </p>
                        </div>
                        <button
                            onclick="loadDashboardData()"
                            class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:text-accent hover:border-accent shadow-sm transition-all text-sm font-medium flex items-center gap-2"
                        >
                            <i class="bi bi-arrow-clockwise"></i> Segarkan Data
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                    >
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden group"
                        >
                            <div class="relative z-10">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider"
                                >
                                    Total User
                                </p>
                                <h3
                                    class="text-3xl font-extrabold text-slate-800 mt-2"
                                    id="stat-users"
                                >
                                    0
                                </h3>
                            </div>
                            <div
                                class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-blue-50 to-transparent group-hover:from-blue-100 transition-all"
                            ></div>
                            <i
                                class="bi bi-people-fill absolute right-4 top-1/2 -translate-y-1/2 text-4xl text-blue-200 group-hover:scale-110 transition-transform"
                            ></i>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden group cursor-pointer"
                            onclick="nav('registrations')"
                        >
                            <div class="relative z-10">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider"
                                >
                                    Registrasi Pending
                                </p>
                                <h3
                                    class="text-3xl font-extrabold text-orange-500 mt-2"
                                    id="stat-pending"
                                >
                                    0
                                </h3>
                            </div>
                            <div
                                class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-orange-50 to-transparent group-hover:from-orange-100 transition-all"
                            ></div>
                            <i
                                class="bi bi-person-plus-fill absolute right-4 top-1/2 -translate-y-1/2 text-4xl text-orange-200 group-hover:scale-110 transition-transform"
                            ></i>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden group"
                        >
                            <div class="relative z-10">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider"
                                >
                                    Kursus Aktif
                                </p>
                                <h3
                                    class="text-3xl font-extrabold text-slate-800 mt-2"
                                    id="stat-courses"
                                >
                                    0
                                </h3>
                            </div>
                            <div
                                class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-emerald-50 to-transparent group-hover:from-emerald-100 transition-all"
                            ></div>
                            <i
                                class="bi bi-book-half absolute right-4 top-1/2 -translate-y-1/2 text-4xl text-emerald-200 group-hover:scale-110 transition-transform"
                            ></i>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden group"
                        >
                            <div class="relative z-10">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider"
                                >
                                    Instruktur
                                </p>
                                <h3
                                    class="text-3xl font-extrabold text-slate-800 mt-2"
                                    id="stat-instructors"
                                >
                                    0
                                </h3>
                            </div>
                            <div
                                class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-indigo-50 to-transparent group-hover:from-indigo-100 transition-all"
                            ></div>
                            <i
                                class="bi bi-person-video3 absolute right-4 top-1/2 -translate-y-1/2 text-4xl text-indigo-200 group-hover:scale-110 transition-transform"
                            ></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <div
                            class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm"
                        >
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h4
                                        class="font-bold text-slate-800 text-lg"
                                    >
                                        Tren Pendaftaran User
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        Berdasarkan data real-time tahun ini
                                    </p>
                                </div>
                                <div
                                    class="p-2 bg-blue-50 rounded-lg text-accent"
                                >
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                            <div class="h-72 w-full">
                                <canvas id="registrationChart"></canvas>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col"
                        >
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="font-bold text-slate-800 text-lg">
                                    Distribusi Role
                                </h4>
                                <div
                                    class="p-2 bg-indigo-50 rounded-lg text-indigo-600"
                                >
                                    <i class="bi bi-pie-chart-fill"></i>
                                </div>
                            </div>
                            <div
                                class="h-64 flex justify-center items-center relative flex-1"
                            >
                                <canvas id="userDistChart"></canvas>
                            </div>
                            <div
                                class="mt-4 text-center text-xs text-slate-400"
                            >
                                Total data dari database
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div
                            class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4
                                        class="font-bold text-slate-800 text-lg flex items-center gap-2"
                                    >
                                        <i
                                            class="bi bi-exclamation-triangle-fill text-red-500"
                                        ></i>
                                        Early Warning
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        Siswa berisiko (Nilai Rendah)
                                    </p>
                                </div>
                                <span
                                    class="px-2 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full animate-pulse"
                                    >Live Data</span
                                >
                            </div>

                            <div
                                class="flex-1 overflow-y-auto pr-2 space-y-3 max-h-80 custom-scrollbar"
                                id="ews-list"
                            >
                                <div
                                    class="text-center py-8 text-slate-400 text-sm"
                                >
                                    Menghitung risiko akademik...
                                </div>
                            </div>

                            <div
                                class="mt-4 pt-4 border-t border-slate-100 text-[10px] text-slate-400 italic text-center"
                            >
                                *Mengambil data real-time dari Enrollments
                                dengan Nilai < 60
                            </div>
                        </div>

                        <div
                            class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <h4
                                        class="font-bold text-slate-800 text-lg"
                                    >
                                        Analisis Akademik
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        Distribusi Siswa berdasarkan Nilai Akhir
                                        (Y)
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <span
                                        class="flex items-center gap-1 text-[10px] text-slate-500"
                                        ><span
                                            class="w-2 h-2 rounded-full bg-emerald-400"
                                        ></span>
                                        Aman (>60)</span
                                    >
                                    <span
                                        class="flex items-center gap-1 text-[10px] text-slate-500"
                                        ><span
                                            class="w-2 h-2 rounded-full bg-red-400"
                                        ></span>
                                        Berisiko (<60)</span
                                    >
                                </div>
                            </div>
                            <div class="h-80 w-full relative">
                                <canvas id="academicScatterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    id="view-registrations"
                    class="page-view hidden fade-in"
                >
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Registrasi Siswa
                            </h2>
                            <p class="text-slate-500 text-sm mt-1">
                                Validasi calon siswa yang mendaftar.
                            </p>
                        </div>
                        <button
                            onclick="loadRegistrations()"
                            class="p-2 bg-white border rounded-lg text-slate-600 hover:text-accent"
                        >
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>

                    <div
                        class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="w-full text-sm text-left text-slate-600"
                            >
                                <thead
                                    class="text-xs text-slate-500 uppercase bg-slate-50 border-b"
                                >
                                    <tr>
                                        <th class="px-6 py-4 font-semibold">
                                            Calon Siswa
                                        </th>
                                        <th class="px-6 py-4 font-semibold">
                                            Kontak
                                        </th>
                                        <th class="px-6 py-4 font-semibold">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 font-semibold">
                                            Tgl Daftar
                                        </th>
                                        <th
                                            class="px-6 py-4 text-center font-semibold"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    id="registration-table-body"
                                    class="divide-y divide-slate-100"
                                ></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="view-instructors" class="page-view hidden fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-slate-800">
                            Daftar Instruktur
                        </h2>
                        <button
                            onclick="openModal('modal-instructor')"
                            class="bg-accent hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all"
                        >
                            <i class="bi bi-plus-lg"></i> Tambah Instruktur
                        </button>
                    </div>
                    <div
                        id="instructors-grid"
                        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                    ></div>
                </section>

                <section id="view-students" class="page-view hidden fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Database Siswa
                            </h2>
                        </div>
                        <div class="relative flex gap-2">
                            <select
                                id="filter-student-status"
                                onchange="loadStudents()"
                                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <input
                                type="text"
                                id="search-student"
                                onkeyup="debounceSearchStudent()"
                                placeholder="Cari siswa..."
                                class="pl-4 pr-10 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent w-64"
                            />
                            <i
                                class="bi bi-search absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                            ></i>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                    >
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead
                                class="bg-slate-50 border-b text-xs uppercase text-slate-500 font-bold"
                            >
                                <tr>
                                    <th class="px-6 py-4">Nama Lengkap</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">NIS/NIM</th>
                                    <th class="px-6 py-4">Orang Tua</th>
                                    <th class="px-6 py-4 text-center">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                id="students-table-body"
                                class="divide-y divide-slate-100"
                            >
                                <tr>
                                    <td
                                        colspan="5"
                                        class="p-8 text-center text-slate-400"
                                    >
                                        Memuat data siswa...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div
                            class="px-6 py-4 border-t border-slate-100 bg-slate-50 text-xs text-slate-500 flex justify-between items-center"
                        >
                            <span id="student-count-info">0 Data</span>
                            <div class="flex gap-1">
                                <button
                                    onclick="changeStudentPage(-1)"
                                    class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50"
                                    id="btn-prev-student"
                                >
                                    Prev
                                </button>
                                <button
                                    onclick="changeStudentPage(1)"
                                    class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50"
                                    id="btn-next-student"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="view-courses" class="page-view hidden fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-slate-800">
                            Manajemen Kursus
                        </h2>
                        <button
                            onclick="openCreateCourseModal()"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-emerald-500/30 flex items-center gap-2"
                        >
                            <i class="bi bi-plus-lg"></i> Buat Kursus
                        </button>
                    </div>
                    <div
                        class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-8"
                    >
                        <h4 class="font-bold text-slate-700 mb-4">
                            Top 5 Kursus Terpopuler
                        </h4>
                        <div class="h-64 w-full">
                            <canvas id="popularCoursesChart"></canvas>
                        </div>
                    </div>
                    <div
                        id="courses-grid"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    ></div>
                </section>

                <section
                    id="view-announcements"
                    class="page-view hidden fade-in"
                >
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-slate-800">
                            Pengumuman Global
                        </h2>
                        <button
                            onclick="openModal('modal-announcement')"
                            class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2"
                        >
                            <i class="bi bi-megaphone-fill"></i> Buat Baru
                        </button>
                    </div>
                    <div id="announcements-list" class="space-y-4"></div>
                </section>

                <section
                    id="view-instructor-detail"
                    class="page-view hidden fade-in"
                >
                    <div class="flex items-center gap-4 mb-6">
                        <button
                            onclick="nav('instructors')"
                            class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                        >
                            <i class="bi bi-arrow-left text-lg"></i>
                        </button>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-slate-800">
                                Detail Instruktur
                            </h2>
                            <p class="text-slate-500 text-sm">
                                Informasi lengkap dan daftar kursus
                            </p>
                        </div>
                        <button
                            id="btn-edit-instructor-detail"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30"
                        >
                            <i class="bi bi-pencil-square"></i> Edit Data
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1 space-y-6">
                            <div
                                class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 text-center relative overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-slate-800 to-slate-900"
                                ></div>
                                <div class="relative z-10">
                                    <div
                                        id="det-ins-avatar"
                                        class="w-24 h-24 rounded-full bg-white border-4 border-white text-slate-800 flex items-center justify-center text-4xl font-bold mx-auto mb-4 shadow-md"
                                    >
                                        I
                                    </div>
                                    <h3
                                        id="det-ins-name"
                                        class="text-xl font-bold text-slate-800 mb-1"
                                    >
                                        Nama Instruktur
                                    </h3>
                                    <p
                                        id="det-ins-code"
                                        class="text-sm font-mono text-blue-600 bg-blue-50 inline-block px-2 py-1 rounded mb-4"
                                    >
                                        CODE
                                    </p>

                                    <div
                                        class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 text-left"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Spesialisasi
                                            </p>
                                            <p
                                                id="det-ins-spec"
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                -
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Pendidikan
                                            </p>
                                            <p
                                                id="det-ins-edu"
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                -
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Pengalaman
                                            </p>
                                            <p
                                                id="det-ins-exp"
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                -
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Status
                                            </p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                                >Active</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white rounded-xl border border-slate-200 shadow-sm p-6"
                            >
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-person-lines-fill text-slate-400"
                                    ></i>
                                    Kontak
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Email
                                        </p>
                                        <p
                                            id="det-ins-email"
                                            class="text-sm font-medium text-slate-700 break-all"
                                        >
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Telepon
                                        </p>
                                        <p
                                            id="det-ins-phone"
                                            class="text-sm font-medium text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white rounded-xl border border-slate-200 shadow-sm p-6"
                            >
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-card-text text-slate-400"
                                    ></i>
                                    Bio
                                </h4>
                                <p
                                    id="det-ins-bio"
                                    class="text-sm text-slate-600 leading-relaxed"
                                >
                                    -
                                </p>
                            </div>
                        </div>

                        <div class="lg:col-span-2 space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4"
                                >
                                    <div
                                        class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl"
                                    >
                                        <i
                                            class="bi bi-journal-bookmark-fill"
                                        ></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm text-slate-500 font-medium"
                                        >
                                            Total Kursus
                                        </p>
                                        <h4
                                            id="det-ins-courses"
                                            class="text-2xl font-bold text-slate-800"
                                        >
                                            0
                                        </h4>
                                    </div>
                                </div>
                                <div
                                    class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4"
                                >
                                    <div
                                        class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl"
                                    >
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm text-slate-500 font-medium"
                                        >
                                            Kapasitas Maksimal Siswa
                                        </p>
                                        <h4
                                            id="det-ins-students"
                                            class="text-2xl font-bold text-slate-800"
                                        >
                                            0
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 border-b border-slate-100 bg-slate-50/50"
                                >
                                    <h4 class="font-bold text-slate-800">
                                        Daftar Kursus
                                    </h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead
                                            class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100"
                                        >
                                            <tr>
                                                <th class="px-6 py-3">
                                                    Nama Kursus
                                                </th>
                                                <th class="px-6 py-3">Kode</th>
                                                <th
                                                    class="px-6 py-3 text-center"
                                                >
                                                    Siswa
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-center"
                                                >
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            id="det-ins-course-list"
                                            class="divide-y divide-slate-100"
                                        ></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div
            id="modal-create-course"
            class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 fade-in"
        >
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden"
            >
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center"
                >
                    <h3 class="font-bold text-lg text-slate-800">
                        Buat Kursus Baru
                    </h3>
                    <button
                        onclick="closeModal('modal-create-course')"
                        class="text-slate-400 hover:text-red-500"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form
                        onsubmit="
                            event.preventDefault();
                            submitCreateCourse();
                        "
                    >
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Kode</label
                                ><input
                                    type="text"
                                    id="create-course-code"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Nama</label
                                ><input
                                    type="text"
                                    id="create-course-name"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Instruktur</label
                            >
                            <select
                                id="create-course-instructor"
                                class="w-full border rounded p-2 text-sm bg-white"
                                required
                            >
                                <option value="">Memuat data...</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Deskripsi</label
                            ><textarea
                                id="create-course-desc"
                                class="w-full border rounded p-2 text-sm"
                                rows="3"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                onclick="closeModal('modal-create-course')"
                                class="px-4 py-2 text-slate-600 text-sm font-bold"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-emerald-600 text-white rounded text-sm font-bold hover:bg-emerald-700"
                            >
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            id="modal-edit-course"
            class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 fade-in"
        >
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden"
            >
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center"
                >
                    <h3 class="font-bold text-lg text-slate-800">
                        Edit Kursus
                    </h3>
                    <button
                        onclick="closeModal('modal-edit-course')"
                        class="text-slate-400 hover:text-red-500"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form
                        onsubmit="
                            event.preventDefault();
                            updateCourse();
                        "
                    >
                        <input type="hidden" id="edit-course-id" />
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Kode</label
                                ><input
                                    type="text"
                                    id="edit-course-code"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Nama</label
                                ><input
                                    type="text"
                                    id="edit-course-name"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Instruktur</label
                                >
                                <select
                                    id="edit-course-instructor"
                                    class="w-full border rounded p-2 text-sm bg-white"
                                    required
                                >
                                    <option value="">Memuat...</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Status</label
                                >
                                <select
                                    id="edit-course-status"
                                    class="w-full border rounded p-2 text-sm bg-white"
                                >
                                    <option value="Published">Published</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Deskripsi</label
                            ><textarea
                                id="edit-course-desc"
                                class="w-full border rounded p-2 text-sm"
                                rows="3"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                onclick="closeModal('modal-edit-course')"
                                class="px-4 py-2 text-slate-600 text-sm font-bold"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            id="modal-instructor"
            class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 overflow-y-auto"
        >
            <div
                class="bg-white rounded-xl w-full max-w-2xl shadow-2xl overflow-hidden my-8"
            >
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center"
                >
                    <h3 class="font-bold text-lg text-slate-800">
                        Tambah Instruktur Baru
                    </h3>
                    <button
                        onclick="closeModal('modal-instructor')"
                        class="text-slate-400 hover:text-red-500"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form
                        onsubmit="
                            event.preventDefault();
                            submitInstructor();
                        "
                    >
                        <h6
                            class="text-xs font-bold text-slate-400 uppercase mb-3 border-b pb-1"
                        >
                            Akun & Login
                        </h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Nama Lengkap</label
                                ><input
                                    type="text"
                                    id="ins-name"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Email</label
                                ><input
                                    type="email"
                                    id="ins-email"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                        </div>
                        <div class="mb-6">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Password Default</label
                            ><input
                                type="password"
                                id="ins-pass"
                                class="w-full border rounded p-2 text-sm"
                                placeholder="Min. 8 Karakter"
                                required
                            />
                        </div>
                        <h6
                            class="text-xs font-bold text-slate-400 uppercase mb-3 border-b pb-1"
                        >
                            Profil Instruktur
                        </h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >NIP / Kode</label
                                ><input
                                    type="text"
                                    id="ins-code"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >No. Telepon</label
                                ><input
                                    type="text"
                                    id="ins-phone"
                                    class="w-full border rounded p-2 text-sm"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Spesialisasi</label
                                ><input
                                    type="text"
                                    id="ins-spec"
                                    class="w-full border rounded p-2 text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Pendidikan Terakhir</label
                                ><select
                                    id="ins-edu"
                                    class="w-full border rounded p-2 text-sm"
                                >
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Pengalaman (Tahun)</label
                            ><input
                                type="number"
                                id="ins-exp"
                                class="w-full border rounded p-2 text-sm"
                                value="0"
                            />
                        </div>
                        <div class="mb-6">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Bio Singkat</label
                            ><textarea
                                id="ins-bio"
                                class="w-full border rounded p-2 text-sm"
                                rows="3"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                onclick="closeModal('modal-instructor')"
                                class="px-4 py-2 text-slate-500 font-medium hover:bg-slate-50 rounded text-sm"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-accent text-white rounded font-bold text-sm shadow-lg shadow-indigo-500/30"
                            >
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            id="modal-edit-instructor"
            class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 overflow-y-auto"
        >
            <div
                class="bg-white rounded-xl w-full max-w-2xl shadow-2xl overflow-hidden my-8"
            >
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center"
                >
                    <h3 class="font-bold text-lg text-slate-800">
                        Edit Instruktur
                    </h3>
                    <button
                        onclick="closeModal('modal-edit-instructor')"
                        class="text-slate-400 hover:text-red-500"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form
                        onsubmit="
                            event.preventDefault();
                            updateInstructor();
                        "
                    >
                        <input type="hidden" id="edit-ins-id" />
                        <h6
                            class="text-xs font-bold text-slate-400 uppercase mb-3 border-b pb-1"
                        >
                            Profil Utama
                        </h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Nama Lengkap</label
                                ><input
                                    type="text"
                                    id="edit-ins-name"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Email</label
                                ><input
                                    type="email"
                                    id="edit-ins-email"
                                    class="w-full border rounded p-2 text-sm"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >NIP / Kode</label
                                ><input
                                    type="text"
                                    id="edit-ins-code"
                                    class="w-full border rounded p-2 text-sm"
                                    disabled
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >No. Telepon</label
                                ><input
                                    type="text"
                                    id="edit-ins-phone"
                                    class="w-full border rounded p-2 text-sm"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Spesialisasi</label
                                ><input
                                    type="text"
                                    id="edit-ins-spec"
                                    class="w-full border rounded p-2 text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Status</label
                                >
                                <select
                                    id="edit-ins-status"
                                    class="w-full border rounded p-2 text-sm"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="resigned">Resigned</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Pendidikan Terakhir</label
                                ><select
                                    id="edit-ins-edu"
                                    class="w-full border rounded p-2 text-sm"
                                >
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                    >Pengalaman (Tahun)</label
                                ><input
                                    type="number"
                                    id="edit-ins-exp"
                                    class="w-full border rounded p-2 text-sm"
                                    value="0"
                                />
                            </div>
                        </div>
                        <div class="mb-6">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase mb-1"
                                >Bio Singkat</label
                            ><textarea
                                id="edit-ins-bio"
                                class="w-full border rounded p-2 text-sm"
                                rows="3"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                onclick="closeModal('modal-edit-instructor')"
                                class="px-4 py-2 text-slate-500 font-medium hover:bg-slate-50 rounded text-sm"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded font-bold text-sm shadow-lg shadow-blue-500/30"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            id="modal-announcement"
            class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm"
        >
            <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-2xl">
                <h3 class="font-bold text-lg mb-4">Buat Pengumuman</h3>
                <form
                    id="formAnnouncement"
                    onsubmit="
                        event.preventDefault();
                        submitAnnouncement();
                    "
                >
                    <div class="space-y-3">
                        <input
                            type="text"
                            id="annTitle"
                            placeholder="Judul"
                            class="w-full border rounded p-2 text-sm"
                            required
                        />
                        <textarea
                            id="annContent"
                            placeholder="Isi Pengumuman"
                            class="w-full border rounded p-2 text-sm"
                            rows="3"
                            required
                        ></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <select
                                id="annCourseId"
                                class="w-full border rounded p-2 text-sm bg-white"
                            ></select>
                            <select
                                id="annPriority"
                                class="w-full border rounded p-2 text-sm bg-white"
                            >
                                <option value="normal">Normal</option>
                                <option value="high">Penting</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input
                                type="datetime-local"
                                id="annPublishedAt"
                                class="w-full border rounded p-2 text-sm"
                            />
                            <select
                                id="annStatus"
                                class="w-full border rounded p-2 text-sm bg-white"
                            >
                                <option value="published">Terbit</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            type="button"
                            onclick="closeModal('modal-announcement')"
                            class="px-4 py-2 text-slate-500 text-sm font-bold"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700"
                        >
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Registration Detail -->
        <div
            id="modal-registration-detail"
            class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4"
            onclick="
                if (event.target.id === 'modal-registration-detail')
                    closeModal('modal-registration-detail');
            "
        >
            <div
                class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto"
                onclick="event.stopPropagation()"
            >
                <!-- Header -->
                <div
                    class="sticky top-0 bg-gradient-to-r from-primary to-blue-600 text-white px-6 py-4 flex justify-between items-center rounded-t-2xl"
                >
                    <div>
                        <h3 class="text-xl font-bold">
                            Detail Registrasi Calon Siswa
                        </h3>
                        <p class="text-xs opacity-90 mt-1">
                            Informasi lengkap pendaftaran siswa baru
                        </p>
                    </div>
                    <button
                        onclick="closeModal('modal-registration-detail')"
                        class="text-white hover:bg-white/20 rounded-full p-2 transition"
                    >
                        <i class="bi bi-x-lg text-2xl"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Registration Meta Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-blue-500 text-white rounded-full p-3"
                                >
                                    <i class="bi bi-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-blue-600 font-medium"
                                    >
                                        Tanggal Daftar
                                    </p>
                                    <p
                                        class="font-bold text-slate-800"
                                        id="detail-created-at"
                                    >
                                        -
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-white rounded-xl p-4 border-2 border-slate-200 shadow-sm"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-slate-100 text-slate-700 rounded-lg p-3"
                                >
                                    <i class="bi bi-hash text-xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 font-medium uppercase tracking-wide"
                                    >
                                        ID Registrasi
                                    </p>
                                    <p
                                        class="font-bold text-slate-800"
                                        id="detail-reg-id"
                                    >
                                        -
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-white rounded-xl p-4 border-2 border-slate-200 shadow-sm"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-slate-100 text-slate-700 rounded-lg p-3"
                                >
                                    <i class="bi bi-person-badge text-xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 font-medium uppercase tracking-wide"
                                    >
                                        User ID
                                    </p>
                                    <p
                                        class="font-bold text-slate-800"
                                        id="detail-user-id"
                                    >
                                        -
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div
                        class="bg-white rounded-xl p-5 border-2 border-slate-200 shadow-sm"
                    >
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                            <div class="bg-primary/10 text-primary rounded-lg p-2.5">
                                <i class="bi bi-person-fill text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">
                                Informasi Calon Siswa
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-person"></i> Nama Lengkap
                                </label>
                                <p
                                    class="font-bold text-slate-800 mt-1"
                                    id="detail-student-name"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1 text-sm"
                                    id="detail-student-email"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-shield-check"></i> Role
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1"
                                    id="detail-student-role"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-geo-alt"></i> Tempat Lahir
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1"
                                    id="detail-tempat-lahir"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-calendar-event"></i> Tanggal
                                    Lahir
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1"
                                    id="detail-tanggal-lahir"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-slate-200"
                            >
                                <label
                                    class="text-xs text-slate-500 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-gender-ambiguous"></i> Jenis
                                    Kelamin
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1"
                                    id="detail-jenis-kelamin"
                                >
                                    -
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Information -->
                    <div
                        class="bg-white rounded-xl p-5 border-2 border-slate-200 shadow-sm"
                    >
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                            <div class="bg-primary/10 text-primary rounded-lg p-2.5">
                                <i class="bi bi-people-fill text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">
                                Informasi Orang Tua / Wali
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="bg-white rounded-lg p-3 border border-blue-200"
                            >
                                <label
                                    class="text-xs text-blue-600 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-person-vcard"></i> Nama
                                    Orang Tua
                                </label>
                                <p
                                    class="font-bold text-slate-800 mt-1"
                                    id="detail-parent-name"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-blue-200"
                            >
                                <label
                                    class="text-xs text-blue-600 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-envelope-at"></i> Email
                                    Orang Tua
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1 text-sm"
                                    id="detail-parent-email"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-blue-200"
                            >
                                <label
                                    class="text-xs text-blue-600 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-telephone"></i> No. Telepon
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1"
                                    id="detail-parent-phone"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-blue-200"
                            >
                                <label
                                    class="text-xs text-blue-600 font-medium flex items-center gap-1"
                                >
                                    <i class="bi bi-house"></i> Alamat Lengkap
                                </label>
                                <p
                                    class="font-semibold text-slate-800 mt-1 text-sm"
                                    id="detail-parent-address"
                                >
                                    -
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div
                        class="bg-white rounded-xl p-5 border-2 border-slate-200 shadow-sm"
                    >
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-slate-100 text-slate-700 rounded-lg p-2.5"
                                >
                                    <i
                                        class="bi bi-file-earmark-text-fill text-lg"
                                    ></i>
                                </div>
                                <h4 class="font-bold text-slate-800 text-lg">
                                    Dokumen Pendukung
                                </h4>
                            </div>
                            <span
                                class="text-xs bg-slate-100 px-3 py-1.5 rounded-full border border-slate-300 text-slate-700 font-medium"
                                id="detail-doc-count"
                            >
                                0 / 4 Dokumen
                            </span>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-3"
                            id="detail-documents"
                        >
                            <!-- Akan diisi dinamis -->
                        </div>
                    </div>

                    <!-- Status & Timeline -->
                    <div
                        class="bg-white rounded-xl p-5 border-2 border-slate-200 shadow-sm"
                    >
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                            <div class="bg-slate-100 text-slate-700 rounded-lg p-2.5">
                                <i class="bi bi-clock-history text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">
                                Status & Timeline
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Status Registrasi</label

                                >
                                <div class="mt-2" id="detail-status">-</div>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Kelengkapan</label
                                >
                                <p
                                    class="font-semibold text-slate-800 mt-2"
                                    id="detail-is-complete"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Pending Approval</label
                                >
                                <p
                                    class="font-semibold text-slate-800 mt-2"
                                    id="detail-is-pending"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Submitted At</label
                                >
                                <p
                                    class="font-semibold text-slate-800 mt-2 text-sm"
                                    id="detail-submitted-at"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Approved At</label
                                >
                                <p
                                    class="font-semibold text-slate-800 mt-2 text-sm"
                                    id="detail-approved-at"
                                >
                                    -
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-lg p-3 border border-amber-200"
                            >
                                <label
                                    class="text-xs text-amber-600 font-medium"
                                    >Approved By</label
                                >
                                <p
                                    class="font-semibold text-slate-800 mt-2"
                                    id="detail-approved-by"
                                >
                                    -
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-3 bg-white rounded-lg p-3 border border-amber-200"
                            id="detail-approval-notes-container"
                            style="display: none"
                        >
                            <label class="text-xs text-amber-600 font-medium"
                                >Catatan Approval</label
                            >
                            <p
                                class="font-semibold text-slate-800 mt-2 text-sm"
                                id="detail-approval-notes"
                            >
                                -
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-between pt-4 border-t-2 border-slate-200"
                    >
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="bi bi-info-circle"></i>
                            <span class="text-sm"
                                >Verifikasi data dengan teliti sebelum
                                menyetujui</span
                            >
                        </div>
                        <div class="flex gap-3" id="detail-actions">
                            <!-- Tombol approve/reject akan muncul di sini -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/adminDashboard.js') }}"></script>
    </body>
</html>