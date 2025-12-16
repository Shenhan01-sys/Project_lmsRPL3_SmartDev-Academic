<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal - SmartDev LMS</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/parentDashboard.css') }}" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#64748b',
                        sidebar: '#1e293b',
                        'sidebar-text': '#cbd5e1',
                    },
                    fontFamily: { sans: ['Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'] }
                }
            }
        }
    </script>
</head>

<body class="bg-[#f8fafc] font-sans text-slate-800 h-screen flex overflow-hidden leading-relaxed">
    <script>
        (function() {
            if (!localStorage.getItem('auth_token')) {
                window.location.replace('/login');
            }
        })();
    </script>
    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-primary mb-2 mx-auto"></div>
            <div class="text-slate-600 font-bold">Memuat Data...</div>
        </div>
    </div>

    <!-- Sidebar -->
    <aside
        class="w-[260px] bg-sidebar text-white hidden md:flex flex-col shadow-xl z-50 shrink-0 fixed h-full transition-all duration-300">
        <div class="p-6 text-center border-b border-white/10">
            <div class="text-xl font-bold tracking-wide flex items-center justify-center gap-2">
                <i class="bi bi-mortarboard-fill text-primary"></i> SmartDev
            </div>
        </div>

        <div class="p-4 mb-2 border-b border-white/10 text-center">
            <div
                class="w-12 h-12 mx-auto bg-primary text-white rounded-full flex items-center justify-center text-xl mb-2 shadow-lg">
                <i class="bi bi-person"></i>
            </div>
            <strong class="block text-sm" id="sidebar-username">Orang Tua</strong>
            <small class="text-sidebar-text text-xs">Parent Panel</small>
        </div>

        <nav class="flex-1 overflow-y-auto py-2 no-scrollbar">
            <button onclick="switchView('dashboard')" id="nav-dashboard" class="nav-link-custom active">
                <i class="bi bi-speedometer2 me-3 text-lg"></i> <span>Dashboard</span>
            </button>
            <button onclick="switchView('students')" id="nav-students" class="nav-link-custom">
                <i class="bi bi-people me-3 text-lg"></i> <span>Anak Saya</span>
            </button>
            <button onclick="switchView('announcements')" id="nav-announcements" class="nav-link-custom">
                <i class="bi bi-megaphone me-3 text-lg"></i> <span>Pengumuman</span>
            </button>

            <div class="mt-6 mb-2 px-6 text-xs font-bold text-sidebar-text uppercase tracking-wider opacity-70">Akademik
            </div>

            <button onclick="switchView('grades')" id="nav-grades" class="nav-link-custom">
                <i class="bi bi-bar-chart-steps me-3 text-lg"></i> <span>Nilai & Evaluasi</span>
            </button>
            <button onclick="switchView('attendance')" id="nav-attendance" class="nav-link-custom">
                <i class="bi bi-calendar-check me-3 text-lg"></i> <span>Kehadiran</span>
            </button>
            <button onclick="switchView('certificates')" id="nav-certificates" class="nav-link-custom">
                <i class="bi bi-award me-3 text-lg"></i> <span>Sertifikat</span>
            </button>
        </nav>

        <div class="p-4 border-t border-white/10 mt-auto">
            <button onclick="logout()"
                class="nav-link-custom text-red-400 hover:text-red-300 hover:bg-red-500/10 !border-l-transparent">
                <i class="bi bi-box-arrow-right me-3 text-lg"></i> <span>Keluar</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#f8fafc] w-full md:ml-[260px]">

        <div class="md:hidden bg-sidebar text-white p-4 flex justify-between items-center shadow-md z-30 shrink-0">
            <span class="font-bold flex items-center gap-2"><i class="bi bi-mortarboard-fill text-primary"></i> SmartDev
                LMS</span>
            <button onclick="alert('Menu mobile belum aktif di demo ini')" class="text-2xl"><i
                    class="bi bi-list"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth" id="main-scroll">

            <!-- DASHBOARD VIEW -->
            <section id="view-dashboard" class="view-section fade-in max-w-7xl mx-auto">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-secondary">Dashboard Overview</h2>
                    <div
                        class="flex items-center gap-3 bg-white px-3 py-1.5 rounded-md shadow-sm border border-slate-200">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm text-slate-500">API Connected</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div
                        class="dashboard-card rounded p-4 !bg-primary !text-white relative overflow-hidden border-none">
                        <div class="relative z-10">
                            <h3 class="text-3xl font-bold mb-1" id="stat-students">0</h3>
                            <p class="text-indigo-100 text-sm">Total Anak</p>
                        </div>
                        <i class="bi bi-people-fill absolute top-1/2 right-4 -translate-y-1/2 text-6xl opacity-20"></i>
                    </div>
                    <div class="dashboard-card rounded p-4 border-l-4 !border-l-emerald-500">
                        <h3 class="text-3xl font-bold text-emerald-600 mb-1" id="stat-enrollments">0</h3>
                        <p class="text-slate-500 text-sm">Total Kursus Aktif</p>
                    </div>
                    <div class="dashboard-card rounded p-4 border-l-4 !border-l-amber-500">
                        <h3 class="text-3xl font-bold text-amber-500 mb-1" id="stat-avg-grade">-</h3>
                        <p class="text-slate-500 text-sm">Rata-rata Nilai</p>
                    </div>
                    <div class="dashboard-card rounded p-4 border-l-4 !border-l-sky-500">
                        <h3 class="text-3xl font-bold text-sky-500 mb-1" id="stat-attendance">-</h3>
                        <p class="text-slate-500 text-sm">Kehadiran</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="dashboard-card rounded p-4 col-span-2">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">Komparasi Nilai</h3>
                                <p class="text-xs text-slate-500">Rata-rata per Anak</p>
                            </div>
                        </div>
                        <div class="h-80 w-full"><canvas id="gradeTrendChart"></canvas></div>
                    </div>

                    <div class="dashboard-card rounded p-4">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">Status Kehadiran</h3>
                                <p class="text-xs text-slate-500">Total Sesi</p>
                            </div>
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600"><i class="bi bi-list-check"></i>
                            </div>
                        </div>
                        <div class="h-80 relative w-full"><canvas id="attendanceBarChart"></canvas></div>
                    </div>
                </div>

                <div class="dashboard-card rounded p-4">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                        <h3 class="font-bold text-lg text-slate-800">Pengumuman Terbaru</h3>
                        <button onclick="switchView('announcements')"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-bold">Lihat Semua</button>
                    </div>
                    <div id="dashboard-announcements" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Announcements injected here -->
                    </div>
                </div>
            </section>

            <!-- STUDENTS VIEW -->
            <section id="view-students" class="view-section hidden fade-in max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6 pb-3 border-b">
                    <h2 class="text-2xl font-bold text-secondary">Daftar Anak</h2>
                </div>
                <div id="students-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Students injected here -->
                </div>
            </section>

            <!-- STUDENT DETAIL VIEW -->
            <section id="view-student-detail" class="view-section hidden fade-in max-w-7xl mx-auto">
                <button onclick="switchView('students')"
                    class="mb-6 flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Anak
                </button>

                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8 mb-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5 text-9xl text-indigo-600 pointer-events-none">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 items-start md:items-center">
                        <div id="detail-student-avatar"
                            class="w-24 h-24 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-4xl shadow-md">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 id="detail-student-name" class="text-3xl font-bold text-slate-800">-</h1>
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Aktif</span>
                            </div>
                            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500">
                                <span><i class="bi bi-envelope me-2"></i><span id="detail-student-email">-</span></span>
                                <span><i class="bi bi-telephone me-2"></i><span
                                        id="detail-student-phone">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-indigo-500"></i> Nilai Terbaru
                        </h3>
                        <div id="detail-student-grades" class="space-y-4">
                            <!-- Grades injected here -->
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-calendar-check-fill text-emerald-500"></i> Riwayat Kehadiran
                        </h3>
                        <div id="detail-student-attendance" class="space-y-4">
                            <!-- Attendance injected here -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- GRADES VIEW -->
            <section id="view-grades" class="view-section hidden fade-in max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6 pb-3 border-b">
                    <h2 class="text-2xl font-bold text-secondary">Laporan Nilai</h2>
                    <select id="grade-student-filter" onchange="renderGrades()"
                        class="px-4 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Anak</option>
                    </select>
                </div>
                <div id="grades-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Grades injected here -->
                </div>
            </section>

            <!-- ATTENDANCE VIEW -->
            <section id="view-attendance" class="view-section hidden fade-in max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6 pb-3 border-b">
                    <h2 class="text-2xl font-bold text-secondary">Riwayat Kehadiran</h2>
                    <select id="attendance-student-filter" onchange="renderAttendance()"
                        class="px-4 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Anak</option>
                    </select>
                </div>
                <div class="dashboard-card space-y-4" id="attendance-list">
                    <!-- Attendance injected here -->
                </div>
            </section>

            <!-- ANNOUNCEMENTS VIEW -->
            <section id="view-announcements" class="view-section hidden fade-in max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6 pb-3 border-b">
                    <h2 class="text-2xl font-bold text-secondary">Pengumuman Sekolah</h2>
                </div>
                <div class="grid gap-4" id="announcements-full-list">
                    <!-- Announcements injected here -->
                </div>
            </section>

            <!-- ANNOUNCEMENT DETAIL VIEW -->
            <section id="view-announcement-detail" class="view-section hidden fade-in max-w-4xl mx-auto">
                <button onclick="switchView('announcements')"
                    class="mb-6 flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                    <i class="bi bi-arrow-left"></i> Kembali ke Pengumuman
                </button>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                    <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600 relative">
                        <div
                            class="absolute -bottom-10 left-8 h-20 w-20 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-md">
                            <i class="bi bi-megaphone-fill text-4xl"></i>
                        </div>
                    </div>
                    <div class="pt-12 px-8 pb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <span id="detail-announcement-tag"
                                class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold uppercase rounded-md">TAG</span>
                            <span id="detail-announcement-date" class="text-sm text-slate-400">Date</span>
                        </div>
                        <h1 id="detail-announcement-title" class="text-3xl font-bold text-slate-800 mb-6">Judul
                            Pengumuman</h1>

                        <div class="prose max-w-none text-slate-600 leading-relaxed">
                            <p id="detail-announcement-content">Isi konten pengumuman akan muncul di sini...</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CERTIFICATES VIEW -->
            <section id="view-certificates" class="view-section hidden fade-in max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6 pb-3 border-b">
                    <h2 class="text-2xl font-bold text-secondary">Sertifikat & Penghargaan</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="certificates-list">
                    <!-- Certificates injected here -->
                </div>
            </section>

        </div>
    </main>
    <script src="{{ asset('js/parentDashboard.js') }}"></script>
</body>

</html>