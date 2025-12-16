<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Student Portal - SmartDev LMS</title>

        <script src="https://cdn.tailwindcss.com"></script>
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="{{ asset('css/StudentDashboard.css') }}">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: "#4f46e5",
                            secondary: "#64748b",
                            sidebar: "#1e293b",
                            "sidebar-text": "#cbd5e1",
                        },
                        fontFamily: {
                            sans: [
                                "Segoe UI",
                                "Tahoma",
                                "Geneva",
                                "Verdana",
                                "sans-serif",
                            ],
                        },
                    },
                },
            };
        </script>
    </head>

    <body
        class="bg-[#f8fafc] font-sans text-slate-800 h-screen flex overflow-hidden leading-relaxed"
    >
        <script>
            (function() {
                if (!localStorage.getItem('auth_token')) {
                    window.location.replace('/login');
                }
            })();
        </script>
        <div id="loading-overlay">
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-16 w-16 border-b-2 border-primary mb-2 mx-auto"
                ></div>
                <div class="text-slate-600 font-bold">Memuat Data...</div>
            </div>
        </div>

        <aside
            class="w-[260px] bg-sidebar text-white hidden md:flex flex-col shadow-xl z-50 shrink-0 fixed h-full transition-all duration-300"
        >
            <div class="p-6 text-center border-b border-white/10">
                <div
                    class="text-xl font-bold tracking-wide flex items-center justify-center gap-2"
                >
                    <i class="bi bi-mortarboard-fill text-primary"></i> SmartDev
                </div>
            </div>

            <div class="p-4 mb-2 border-b border-white/10 text-center">
                <div
                    class="w-12 h-12 mx-auto bg-primary text-white rounded-full flex items-center justify-center text-xl mb-2 shadow-lg"
                >
                    <i class="bi bi-person-fill"></i>
                </div>
                <strong
                    class="block text-sm truncate px-2"
                    id="sidebar-username"
                    >Student</strong
                >
                <small class="text-sidebar-text text-xs">Student Panel</small>
            </div>
            <nav class="flex-1 overflow-y-auto py-2 no-scrollbar">
                <button
                    onclick="switchView('dashboard')"
                    id="nav-dashboard"
                    class="nav-link-custom active"
                >
                    <i class="bi bi-speedometer2 me-3 text-lg"></i>
                    <span>Dashboard</span>
                </button>
                <div
                    class="mt-6 mb-2 px-6 text-xs font-bold text-sidebar-text uppercase tracking-wider opacity-70"
                >
                    Akademik
                </div>
                <button
                    onclick="switchView('courses')"
                    id="nav-courses"
                    class="nav-link-custom"
                >
                    <i class="bi bi-book me-3 text-lg"></i>
                    <span>Kursus Saya</span>
                </button>
                <button
                    onclick="switchView('assignments')"
                    id="nav-assignments"
                    class="nav-link-custom"
                >
                    <i class="bi bi-journal-text me-3 text-lg"></i>
                    <span>Tugas</span>
                </button>
                <button
                    onclick="switchView('grades')"
                    id="nav-grades"
                    class="nav-link-custom"
                >
                    <i class="bi bi-bar-chart-steps me-3 text-lg"></i>
                    <span>Nilai</span>
                </button>
                <button
                    onclick="switchView('attendance')"
                    id="nav-attendance"
                    class="nav-link-custom"
                >
                    <i class="bi bi-calendar-check me-3 text-lg"></i>
                    <span>Kehadiran</span>
                </button>
                <button
                    onclick="switchView('certificates')"
                    id="nav-certificates"
                    class="nav-link-custom"
                >
                    <i class="bi bi-award me-3 text-lg"></i>
                    <span>Sertifikat</span>
                </button>
                <div
                    class="mt-6 mb-2 px-6 text-xs font-bold text-sidebar-text uppercase tracking-wider opacity-70"
                >
                    Akun
                </div>
                <button
                    onclick="switchView('profile')"
                    id="nav-profile"
                    class="nav-link-custom"
                >
                    <i class="bi bi-person-gear me-3 text-lg"></i>
                    <span>Profil</span>
                </button>
                <button
                    onclick="logout()"
                    class="nav-link-custom text-red-400 hover:text-red-300 hover:bg-red-900/20 hover:border-l-red-500"
                >
                    <i class="bi bi-box-arrow-right me-3 text-lg"></i>
                    <span>Keluar</span>
                </button>
            </nav>
        </aside>

        <main
            class="flex-1 ml-0 md:ml-[260px] h-full overflow-hidden flex flex-col w-full relative"
        >
            <div
                class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth"
                id="main-scroll"
            >
                <section
                    id="view-dashboard"
                    class="view-section fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200"
                    >
                        <div>
                            <h2 class="text-2xl font-bold text-secondary">
                                Dashboard Siswa
                            </h2>
                            <p class="text-sm text-slate-500">
                                Selamat datang kembali, mari belajar!
                            </p>
                        </div>
                        <div
                            class="flex items-center gap-3 bg-white px-3 py-1.5 rounded-md shadow-sm border border-slate-200"
                        >
                            <div
                                class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"
                            ></div>
                            <span
                                class="text-sm text-slate-500"
                                id="current-date"
                                >Loading...</span
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                        <div
                            class="dashboard-card rounded p-4 !bg-primary !text-white relative overflow-hidden border-none"
                        >
                            <div class="relative z-10">
                                <h3
                                    class="text-3xl font-bold mb-1"
                                    id="stat-courses"
                                >
                                    0
                                </h3>
                                <p class="text-indigo-100 text-sm">
                                    Kursus Diikuti
                                </p>
                            </div>
                            <i
                                class="bi bi-book-half absolute top-1/2 right-4 -translate-y-1/2 text-6xl opacity-20"
                            ></i>
                        </div>
                        <div
                            class="dashboard-card rounded p-4 border-l-4 !border-l-amber-500"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3
                                        class="text-3xl font-bold text-amber-500 mb-1"
                                        id="stat-pending"
                                    >
                                        0
                                    </h3>
                                    <p class="text-slate-500 text-sm">
                                        Tugas Pending
                                    </p>
                                </div>
                                <div
                                    class="bg-amber-50 p-3 rounded-full text-amber-500"
                                >
                                    <i class="bi bi-clock-history text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        <div
                            class="dashboard-card rounded p-4 border-l-4 !border-l-emerald-500"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3
                                        class="text-3xl font-bold text-emerald-500 mb-1"
                                        id="stat-attendance"
                                    >
                                        0%
                                    </h3>
                                    <p class="text-slate-500 text-sm">
                                        Rata-rata Kehadiran
                                    </p>
                                </div>
                                <div
                                    class="bg-emerald-50 p-3 rounded-full text-emerald-500"
                                >
                                    <i
                                        class="bi bi-calendar-check text-2xl"
                                    ></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="dashboard-card col-span-2">
                            <div
                                class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100"
                            >
                                <h3 class="font-bold text-lg text-slate-800">
                                    Tugas Terbaru
                                </h3>
                                <button
                                    onclick="switchView('assignments')"
                                    class="text-sm text-primary font-semibold hover:underline"
                                >
                                    Lihat Semua
                                </button>
                            </div>
                            <div
                                id="dashboard-assignments-list"
                                class="space-y-3"
                            ></div>
                        </div>
                        <div
                            class="dashboard-card bg-gradient-to-b from-indigo-50 to-white"
                        >
                            <h3 class="font-bold text-lg text-slate-800 mb-4">
                                Jadwal Hari Ini
                            </h3>
                            <div id="dashboard-schedule" class="space-y-4">
                                <p
                                    class="text-slate-500 text-center py-4 text-sm"
                                >
                                    Tidak ada jadwal kelas spesifik hari ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    id="view-courses"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex justify-between items-center mb-6 pb-3 border-b"
                    >
                        <h2 class="text-2xl font-bold text-secondary">
                            Kursus Saya
                        </h2>
                    </div>
                    <div
                        id="courses-container"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    ></div>
                </section>

                <section
                    id="view-course-detail"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <button
                        onclick="switchView('courses')"
                        class="mb-4 text-sm text-slate-500 hover:text-primary flex items-center gap-1 transition-colors"
                    >
                        <i class="bi bi-arrow-left"></i> Kembali ke Kursus
                    </button>

                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6"
                    >
                        <div
                            class="h-40 bg-gradient-to-r from-indigo-600 to-purple-600 p-6 md:p-8 flex items-end relative overflow-hidden"
                        >
                            <div class="absolute top-0 right-0 p-8 opacity-10">
                                <i
                                    class="bi bi-journal-bookmark-fill text-9xl text-white"
                                ></i>
                            </div>
                            <div class="relative z-10">
                                <span
                                    class="px-2 py-1 bg-white/20 text-white text-xs font-bold rounded backdrop-blur-sm mb-2 inline-block"
                                    id="detail-course-code"
                                    >CODE</span
                                >
                                <h1
                                    class="text-2xl md:text-3xl font-bold text-white mb-1"
                                    id="detail-course-name"
                                >
                                    Course Name
                                </h1>
                                <p
                                    class="text-indigo-100 text-sm flex items-center gap-2"
                                >
                                    <i class="bi bi-person-circle"></i>
                                    <span id="detail-course-instructor"
                                        >Instructor Name</span
                                    >
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-slate-800 mb-2">
                                Tentang Kursus
                            </h3>
                            <p
                                class="text-slate-600 leading-relaxed text-sm"
                                id="detail-course-desc"
                            >
                                Description...
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-6 border-b border-slate-200 mb-6 overflow-x-auto"
                    >
                        <button
                            onclick="switchCourseTab('modules')"
                            id="tab-course-modules"
                            class="course-tab-btn px-2 py-2 text-sm font-bold text-primary border-b-2 border-primary transition-colors whitespace-nowrap"
                        >
                            Materi Pembelajaran
                        </button>
                        <button
                            onclick="switchCourseTab('assignments')"
                            id="tab-course-assignments"
                            class="course-tab-btn px-2 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 border-b-2 border-transparent transition-colors whitespace-nowrap"
                        >
                            Tugas & Kuis
                        </button>
                        <button
                            onclick="switchCourseTab('attendance')"
                            id="tab-course-attendance"
                            class="course-tab-btn px-2 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 border-b-2 border-transparent transition-colors whitespace-nowrap"
                        >
                            Kehadiran
                        </button>
                        <button
                            onclick="switchCourseTab('people')"
                            id="tab-course-people"
                            class="course-tab-btn px-2 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 border-b-2 border-transparent transition-colors whitespace-nowrap"
                        >
                            Peserta
                        </button>
                    </div>

                    <div
                        id="course-content-modules"
                        class="course-tab-content space-y-4"
                    ></div>
                    <div
                        id="course-content-assignments"
                        class="course-tab-content hidden space-y-4"
                    ></div>
                    <div
                        id="course-content-attendance"
                        class="course-tab-content hidden space-y-6"
                    ></div>
                    <div
                        id="course-content-people"
                        class="course-tab-content hidden grid grid-cols-1 md:grid-cols-2 gap-4"
                    ></div>
                </section>

                <section
                    id="view-assignments"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-center mb-6 pb-3 border-b gap-4"
                    >
                        <h2 class="text-2xl font-bold text-secondary">
                            Daftar Tugas
                        </h2>
                        <div class="flex gap-2">
                            <button
                                onclick="filterAssignments('all')"
                                class="px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-600 hover:bg-slate-300 transition"
                            >
                                Semua
                            </button>
                            <button
                                onclick="filterAssignments('pending')"
                                class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-600 hover:bg-amber-200 transition"
                            >
                                Pending
                            </button>
                            <button
                                onclick="filterAssignments('submitted')"
                                class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
                            >
                                Terkirim
                            </button>
                        </div>
                    </div>
                    <div id="assignments-container" class="space-y-4"></div>
                </section>

                <section
                    id="view-grades"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex justify-between items-center mb-6 pb-3 border-b"
                    >
                        <h2 class="text-2xl font-bold text-secondary">
                            Transkrip Nilai
                        </h2>
                    </div>
                    <div class="dashboard-card overflow-hidden p-0">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead
                                class="text-xs text-slate-700 uppercase bg-slate-50 border-b"
                            >
                                <tr>
                                    <th class="px-6 py-3">Mata Pelajaran</th>
                                    <th class="px-6 py-3">Tugas / Komponen</th>
                                    <th class="px-6 py-3 text-center">Nilai</th>
                                    <th class="px-6 py-3 text-center">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="grades-table-body"></tbody>
                        </table>
                    </div>
                </section>

                <section
                    id="view-attendance"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex justify-between items-center mb-6 pb-3 border-b"
                    >
                        <h2 class="text-2xl font-bold text-secondary">
                            Riwayat Rekap Kehadiran
                        </h2>
                    </div>
                    <div
                        id="attendance-history-container"
                        class="space-y-4"
                    ></div>
                </section>

                <section
                    id="view-certificates"
                    class="view-section hidden fade-in max-w-7xl mx-auto"
                >
                    <div
                        class="flex justify-between items-center mb-6 pb-3 border-b"
                    >
                        <h2 class="text-2xl font-bold text-secondary">
                            Sertifikat Saya
                        </h2>
                    </div>
                    <div
                        id="certificates-container"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    ></div>
                </section>

                <!-- Profile Section -->
                <section
                    id="view-profile"
                    class="view-section hidden fade-in max-w-6xl mx-auto"
                >
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Profil Saya
                            </h2>
                            <p class="text-slate-500 text-sm">
                                Informasi lengkap akun siswa
                            </p>
                        </div>
                        <button
                            onclick="openEditProfileModal()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30"
                        >
                            <i class="bi bi-pencil-square"></i> Edit Profil
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column - Profile Card -->
                        <div class="lg:col-span-1 space-y-6">
                            <div
                                class="dashboard-card text-center relative overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-blue-600 to-blue-800"
                                ></div>
                                <div class="relative z-10 pt-6">
                                    <div
                                        id="profile-avatar"
                                        class="w-24 h-24 rounded-full bg-white border-4 border-white text-blue-600 flex items-center justify-center text-4xl font-bold mx-auto mb-4 shadow-md"
                                    >
                                        S
                                    </div>
                                    <h3
                                        id="profile-name-display"
                                        class="text-xl font-bold text-slate-800 mb-1"
                                    >
                                        Nama Siswa
                                    </h3>
                                    <p
                                        id="profile-student-number"
                                        class="text-sm font-mono text-blue-600 bg-blue-50 inline-block px-2 py-1 rounded mb-4"
                                    >
                                        STD000000
                                    </p>
                                    <div
                                        class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 text-left"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Kelas
                                            </p>
                                            <p
                                                id="profile-grade"
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
                                                id="profile-status"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                                >Active</span
                                            >
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Tahun Masuk
                                            </p>
                                            <p
                                                id="profile-enrollment"
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                -
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 uppercase font-bold mb-1"
                                            >
                                                Jenis Kelamin
                                            </p>
                                            <p
                                                id="profile-gender"
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                -
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-card">
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-envelope-fill text-blue-600"
                                    ></i>
                                    Kontak
                                </h4>
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Email
                                        </p>
                                        <p
                                            id="profile-email-display"
                                            class="text-slate-700"
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
                                            id="profile-phone"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Tanggal Lahir
                                        </p>
                                        <p
                                            id="profile-dob"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="dashboard-card">
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-geo-alt-fill text-red-600"
                                    ></i>
                                    Alamat
                                </h4>
                                <p
                                    id="profile-address"
                                    class="text-sm text-slate-600"
                                >
                                    -
                                </p>
                            </div>

                            <div class="dashboard-card">
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-people-fill text-green-600"
                                    ></i>
                                    Kontak Darurat
                                </h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Nama
                                        </p>
                                        <p
                                            id="profile-emergency-name"
                                            class="text-slate-700"
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
                                            id="profile-emergency-phone"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-card">
                                <h4
                                    class="font-bold text-slate-800 mb-4 flex items-center gap-2"
                                >
                                    <i
                                        class="bi bi-person-heart text-purple-600"
                                    ></i>
                                    Informasi Orang Tua
                                </h4>
                                <div
                                    id="profile-parent-info"
                                    class="grid grid-cols-2 gap-4 text-sm"
                                >
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Nama
                                        </p>
                                        <p
                                            id="profile-parent-name"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Hubungan
                                        </p>
                                        <p
                                            id="profile-parent-relation"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Email
                                        </p>
                                        <p
                                            id="profile-parent-email"
                                            class="text-slate-700"
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
                                            id="profile-parent-phone"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                    <div class="col-span-2">
                                        <p
                                            class="text-xs text-slate-400 uppercase font-bold mb-1"
                                        >
                                            Pekerjaan
                                        </p>
                                        <p
                                            id="profile-parent-occupation"
                                            class="text-slate-700"
                                        >
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
        <!-- Modal: Submit Assignment -->
        <div id="modal-submit-assignment" class="modal-overlay hidden">
            <div class="modal-container max-w-2xl">
                <div class="modal-header">
                    <h3 class="modal-title">Submit Tugas</h3>
                    <button onclick="closeModal('modal-submit-assignment')" class="modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="form-submit-assignment" onsubmit="submitAssignment(event)" class="modal-body">
                    <input type="hidden" id="submission-assignment-id">
                    <input type="hidden" id="submission-id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Judul Tugas</label>
                        <p id="submission-assignment-title" class="text-slate-700 p-3 bg-slate-50 rounded"></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Due Date</label>
                        <p id="submission-due-date" class="text-slate-700 p-3 bg-slate-50 rounded"></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Deskripsi</label>
                        <p id="submission-assignment-desc" class="text-sm text-slate-600 p-3 bg-slate-50 rounded min-h-[60px]"></p>
                    </div>

                    <div class="mb-4">
                        <label for="submission-file" class="block text-sm font-bold mb-2">
                            Upload File <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="submission-file"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.doc,.docx,.zip,.rar">
                        <p class="text-xs text-slate-400 mt-1">Format: PDF, DOC, DOCX, ZIP, RAR (Max: 10MB)</p>
                        <p id="current-file-name" class="text-xs text-green-600 mt-2 hidden"></p>
                    </div>

                    <div class="flex gap-2 pt-4 border-t">
                        <button type="button" onclick="submitAssignment(event, false)" 
                            class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded text-sm font-bold hover:bg-slate-300">
                            <i class="bi bi-save"></i> Simpan sebagai Draft
                        </button>
                        <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700">
                            <i class="bi bi-send-fill"></i> Submit Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script src="{{ asset('js/StudentDashboard.js') }}"></script>
    </body>
</html>