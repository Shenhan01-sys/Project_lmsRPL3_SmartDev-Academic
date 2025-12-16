<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - SmartDev LMS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/instructorDashboard.css') }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f172a', // Slate 900
                        accent: '#4f46e5', // Indigo 600
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        sidebar: '#1e293b',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
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
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-accent mb-3"></div>
            <span class="text-slate-600 font-bold text-sm tracking-wider">MEMPROSES...</span>
        </div>
    </div>

    <aside class="w-64 bg-sidebar text-white hidden md:flex flex-col shadow-2xl z-20 shrink-0 transition-all duration-300">
        <div class="p-6 border-b border-white/10 flex items-center gap-3">
            <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="bi bi-mortarboard-fill text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg tracking-tight">SmartDev</h1>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Instructor Panel</p>
            </div>
        </div>

        <div class="p-6 text-center border-b border-white/5">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full mx-auto flex items-center justify-center text-2xl font-bold text-white shadow-lg mb-3">
                <i class="bi bi-person"></i>
            </div>
            <h4 id="sidebarUserName" class="font-bold text-sm text-white">Instructor</h4>
            <p class="text-xs text-slate-400">Pengajar</p>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
            <div onclick="navTo('dashboard')" id="nav-dashboard" class="nav-link active">
                <i class="bi bi-speedometer2 me-3 text-lg"></i> <span class="font-medium">Dashboard</span>
            </div>
            <div onclick="navTo('courses')" id="nav-courses" class="nav-link">
                <i class="bi bi-journal-bookmark-fill me-3 text-lg"></i> <span class="font-medium">Kursus Saya</span>
            </div>
            <div onclick="navTo('announcements')" id="nav-announcements" class="nav-link">
                <i class="bi bi-megaphone me-3 text-lg"></i> <span class="font-medium">Pengumuman</span>
            </div>
            <div onclick="navTo('profile')" id="nav-profile" class="nav-link">
                <i class="bi bi-person-circle me-3 text-lg"></i> <span class="font-medium">Profil Saya</span>
            </div>
        </nav>

        <div class="p-4 border-t border-white/10 bg-slate-900/50">
            <button onclick="logout()" class="w-full py-2.5 bg-white/5 hover:bg-red-500/20 text-slate-300 hover:text-red-400 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 group">
                <i class="bi bi-box-arrow-right group-hover:scale-110 transition-transform"></i> KELUAR
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-slate-50">
        <div class="bg-white border-b border-slate-200 px-6 py-2 flex justify-between items-center text-xs" style="display: none;">
            <span class="text-slate-400 font-mono">API Connection Check</span>
            <div class="flex items-center gap-2">
                <i class="bi bi-link-45deg text-slate-400"></i>
                <input type="text" id="ngrokUrl" value="https://portohansgunawan.my.id" class="bg-slate-50 border border-slate-200 rounded px-2 py-1 text-slate-600 w-64 focus:outline-none focus:border-accent">
                <button onclick="initApp()" class="text-accent hover:underline font-bold">Reconnect</button>
            </div>
        </div>

        <div id="main-scroll" class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">

            <section id="view-dashboard" class="page-view fade-in">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Ikhtisar Pengajar</h2>
                    <p class="text-slate-500 mt-1">Pantau kinerja kursus dan aktivitas siswa Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-200">
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kursus Aktif</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-2" id="statCourses">0</h3>
                        </div>
                        <i class="bi bi-journal-text absolute right-4 top-1/2 -translate-y-1/2 text-6xl text-slate-100 group-hover:text-indigo-50 transition-colors"></i>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-200">
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Siswa</p>
                            <h3 class="text-3xl font-extrabold text-emerald-600 mt-2" id="statStudents">0</h3>
                        </div>
                        <i class="bi bi-people-fill absolute right-4 top-1/2 -translate-y-1/2 text-6xl text-slate-100 group-hover:text-emerald-50 transition-colors"></i>
                    </div>
                </div>
                <h4 class="font-bold text-slate-700 mb-4">Kursus Terbaru</h4>
                <div id="dashboardCoursesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
            </section>

            <section id="view-courses" class="page-view hidden fade-in">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Manajemen Kursus</h2>
                        <p class="text-slate-500 text-sm">Kelola materi, tugas, dan penilaian.</p>
                    </div>
                </div>
                <div id="coursesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
            </section>

            <section id="view-course-detail" class="page-view hidden fade-in">
                <div class="mb-6">
                    <button onclick="navTo('courses')" class="text-slate-500 hover:text-accent text-sm font-bold mb-2 flex items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                    </button>
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span id="detailCode" class="px-2 py-0.5 bg-slate-800 text-white text-[10px] font-mono rounded">CODE</span>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Active</span>
                            </div>
                            <h2 id="detailTitle" class="text-3xl font-bold text-slate-800">Judul Kursus</h2>
                            <p id="detailDesc" class="text-slate-500 text-sm mt-1 max-w-2xl">Deskripsi kursus...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
                    <div class="flex border-b border-slate-200 overflow-x-auto">
                        <button onclick="switchTab('modules')" class="tab-btn active" id="tab-btn-modules">Modul & Materi</button>
                        <button onclick="switchTab('assignments')" class="tab-btn" id="tab-btn-assignments">Tugas</button>
                        <button onclick="switchTab('attendance')" class="tab-btn" id="tab-btn-attendance">Absensi</button>
                        <button onclick="switchTab('students')" class="tab-btn" id="tab-btn-students">Siswa</button>
                        <button onclick="switchTab('certificates')" class="tab-btn" id="tab-btn-certificates">Sertifikat</button>
                    </div>

                    <div class="p-6">
                        <div id="tab-modules" class="tab-content">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-slate-700">Daftar Modul</h4>
                                <button onclick="openCreateModuleModal()" class="text-accent hover:underline text-sm font-bold"><i class="bi bi-plus-lg"></i> Tambah Modul</button>
                            </div>
                            <div id="modulesAccordion" class="space-y-4"></div>
                        </div>

                        <div id="tab-assignments" class="tab-content hidden">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-slate-700">Daftar Tugas</h4>
                                <button onclick="openAssignmentModal()" class="bg-accent text-white px-3 py-1.5 rounded text-sm font-bold shadow-sm hover:bg-indigo-700"><i class="bi bi-plus-lg"></i> Buat Tugas</button>
                            </div>
                            <div id="assignmentListContainer" class="grid grid-cols-1 gap-4"></div>
                        </div>

                        <div id="tab-attendance" class="tab-content hidden">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-slate-700">Sesi Absensi</h4>
                                <button onclick="openCreateSessionModal()" class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-bold shadow-sm hover:bg-emerald-700"><i class="bi bi-calendar-plus"></i> Buat Sesi</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="col-span-1 border-r border-slate-100 pr-4">
                                    <div id="sessionListGroup" class="space-y-2 max-h-[400px] overflow-y-auto pr-2"></div>
                                </div>
                                <div class="col-span-2">
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg overflow-hidden">
                                        <div class="px-4 py-3 border-b border-slate-200 bg-white font-bold text-slate-700" id="sessionDetailHeader">Pilih sesi di kiri</div>
                                        <div class="max-h-[400px] overflow-y-auto">
                                            <table class="w-full text-sm text-left">
                                                <thead class="bg-slate-50 text-slate-500 font-bold border-b">
                                                    <tr>
                                                        <th class="px-4 py-2">Siswa</th>
                                                        <th class="px-4 py-2 text-center">Status</th>
                                                        <th class="px-4 py-2 text-right">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="attendanceTableBody" class="divide-y divide-slate-100">
                                                    <tr><td colspan="3" class="p-8 text-center text-slate-400">Belum ada sesi dipilih.</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-students" class="tab-content hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b">
                                    <tr>
                                        <th class="px-6 py-3">Nama Lengkap</th>
                                        <th class="px-6 py-3">Email</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentListBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>

                        <div id="tab-certificates" class="tab-content hidden">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h4 class="font-bold text-slate-700">Student Progress & Certificates</h4>
                                    <p class="text-xs text-slate-500">Monitor student eligibility. System automatically generates certificates for eligible students.</p>
                                </div>
                                <button onclick="bulkGenerateCertificates()" class="bg-accent text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition-colors">
                                    <i class="bi bi-arrow-repeat"></i> Check & Generate All Eligible
                                </button>
                            </div>
                            <div id="certificatesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="view-submissions" class="page-view hidden fade-in">
                <button onclick="backToCourseDetail()" class="text-slate-500 hover:text-accent text-sm font-bold mb-4 flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Kembali ke Materi
                </button>
                <div id="submissionContainer" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h3 id="submissionTitle" class="text-xl font-bold text-slate-800 mb-4">Submission</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 font-bold border-b">
                                <tr>
                                    <th class="px-4 py-3">Siswa</th>
                                    <th class="px-4 py-3">Waktu Kumpul</th>
                                    <th class="px-4 py-3">File</th>
                                    <th class="px-4 py-3">Nilai</th>
                                    <th class="px-4 py-3">Feedback</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="submissionTableBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="view-announcements" class="page-view hidden fade-in">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Pengumuman</h2>
                    <button onclick="openAnnouncementModal()" class="bg-accent text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow hover:bg-indigo-700">
                        <i class="bi bi-megaphone-fill"></i> Buat Baru
                    </button>
                </div>
                <div class="flex gap-2 mb-6">
                    <button onclick="loadAnnouncements()" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-sm font-bold hover:bg-slate-50">Semua</button>
                    <button onclick="loadAnnouncements({status: 'published'})" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-sm font-bold hover:bg-slate-50">Terbit</button>
                </div>
                <div id="announcementList" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
            </section>
            
            <!-- PROFILE SECTION -->
            <section id="view-profile" class="page-view hidden fade-in">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-slate-800">Profil Saya</h2>
                        <p class="text-slate-500 text-sm">Informasi lengkap dan pengaturan akun</p>
                    </div>
                    <button onclick="openEditProfileModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30">
                        <i class="bi bi-pencil-square"></i> Edit Profil
                    </button>
                </div>
            
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Profile Card -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 text-center relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-slate-800 to-slate-900"></div>
                            <div class="relative z-10">
                                <div id="profile-avatar" class="w-24 h-24 rounded-full bg-white border-4 border-white text-slate-800 flex items-center justify-center text-4xl font-bold mx-auto mb-4 shadow-md">I</div>
                                <h3 id="profile-name" class="text-xl font-bold text-slate-800 mb-1">Nama Instruktur</h3>
                                <p id="profile-code" class="text-sm font-mono text-blue-600 bg-blue-50 inline-block px-2 py-1 rounded mb-4">CODE</p>
            
                                <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 text-left">
                                    <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Spesialisasi</p><p id="profile-spec" class="text-sm font-medium text-slate-700">-</p></div>
                                    <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Pendidikan</p><p id="profile-edu" class="text-sm font-medium text-slate-700">-</p></div>
                                    <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Pengalaman</p><p id="profile-exp" class="text-sm font-medium text-slate-700">-</p></div>
                                    <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Status</p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span></div>
                                </div>
                            </div>
                        </div>
            
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-envelope-fill text-blue-600"></i> Kontak
                            </h4>
                            <div class="space-y-3 text-sm">
                                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Email</p><p id="profile-email" class="text-slate-700">-</p></div>
                                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Telepon</p><p id="profile-phone" class="text-slate-700">-</p></div>
                            </div>
                        </div>
            
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-info-circle-fill text-slate-600"></i> Bio
                            </h4>
                            <p id="profile-bio" class="text-sm text-slate-600 leading-relaxed">Belum ada biografi.</p>
                        </div>
                    </div>
            
                    <!-- Right Column - Stats & Courses -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-xl">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-journal-bookmark-fill text-2xl"></i>
                                    </div>
                                </div>
                                <p class="text-blue-100 text-sm mb-1">Total Kursus</p>
                                <h4 id="profile-total-courses" class="text-4xl font-bold">0</h4>
                            </div>
            
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-xl">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-people-fill text-2xl"></i>
                                    </div>
                                </div>
                                <p class="text-green-100 text-sm mb-1">Kapasitas Maksimal Siswa</p>
                                <h4 id="profile-total-students" class="text-4xl font-bold">0</h4>
                            </div>
                        </div>
            
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-slate-800">Daftar Kursus</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-slate-200 text-left">
                                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Kursus</th>
                                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Siswa</th>
                                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="profile-course-list">
                                        <tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada kursus.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div id="modal-create-module" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4" id="moduleModalTitle">Tambah Modul</h3>
            <form id="formCreateModule" onsubmit="event.preventDefault(); submitModule();">
                <input type="hidden" id="moduleId">
                <div class="space-y-3">
                    <div><label class="text-xs font-bold uppercase text-slate-500">Judul Modul</label><input type="text" id="moduleTitle" class="w-full border rounded p-2 text-sm" required></div>
                    <div><label class="text-xs font-bold uppercase text-slate-500">Urutan</label><input type="number" id="moduleOrder" class="w-full border rounded p-2 text-sm" value="1"></div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-create-module')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-create-material" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4" id="materialModalTitle">Tambah Materi</h3>
            <form id="formCreateMaterial" onsubmit="event.preventDefault(); submitMaterial();">
                <input type="hidden" id="materialId">
                <input type="hidden" id="materialModuleId">
                <div class="space-y-3">
                    <div><label class="text-xs font-bold uppercase text-slate-500">Judul</label><input type="text" id="materialTitle" class="w-full border rounded p-2 text-sm" required></div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500">Tipe</label>
                        <select id="materialType" class="w-full border rounded p-2 text-sm bg-white" onchange="toggleMaterialInput()">
                            <option value="file">Upload File</option>
                            <option value="link">Link Eksternal</option>
                            <option value="video">Video URL</option>
                        </select>
                    </div>
                    <div id="materialFileInput">
                        <label class="text-xs font-bold uppercase text-slate-500">File</label>
                        <input type="file" id="materialFile" class="w-full border rounded p-2 text-sm" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4,.mp3">
                        <p class="text-[10px] text-slate-400 mt-1">Max 50MB. PDF, Office, Gambar, Audio/Video.</p>
                    </div>
                    <div id="materialUrlInput" class="hidden">
                        <label class="text-xs font-bold uppercase text-slate-500">URL</label>
                        <input type="url" id="materialUrl" class="w-full border rounded p-2 text-sm" placeholder="https://...">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-create-material')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-create-assignment" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4">Buat Tugas Baru</h3>
            <form id="formCreateAssignment" onsubmit="event.preventDefault(); submitAssignment();">
                <input type="hidden" id="assignId">
                <div class="space-y-3">
                    <div><label class="text-xs font-bold uppercase text-slate-500">Judul</label><input type="text" id="assignTitle" class="w-full border rounded p-2 text-sm" required></div>
                    <div><label class="text-xs font-bold uppercase text-slate-500">Deskripsi</label><textarea id="assignDesc" class="w-full border rounded p-2 text-sm" rows="3"></textarea></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs font-bold uppercase text-slate-500">Tenggat Waktu</label><input type="datetime-local" id="assignDate" class="w-full border rounded p-2 text-sm" required></div>
                        <div><label class="text-xs font-bold uppercase text-slate-500">Poin Maks</label><input type="number" id="assignScore" class="w-full border rounded p-2 text-sm" value="100"></div>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500">Lampiran (Opsional)</label>
                        <input type="file" id="assignFile" class="w-full border rounded p-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-create-assignment')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-grading" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-sm p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4">Beri Nilai</h3>
            <input type="hidden" id="gradeSubmissionId">
            <input type="hidden" id="gradeEnrollmentId">
            <input type="hidden" id="gradeComponentId">

            <div class="space-y-3">
                <div><label class="text-xs font-bold uppercase text-slate-500">Nilai (0-100)</label><input type="number" id="gradeValue" class="w-full border rounded p-2 text-sm" min="0" max="100"></div>
                <div><label class="text-xs font-bold uppercase text-slate-500">Umpan Balik</label><textarea id="gradeFeedback" class="w-full border rounded p-2 text-sm" rows="3"></textarea></div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeModal('modal-grading')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                <button type="button" onclick="submitGrade()" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-bold hover:bg-emerald-700">Simpan</button>
            </div>
        </div>
    </div>

    <div id="modal-announcement" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4">Buat Pengumuman</h3>
            <form id="formAnnouncement" onsubmit="event.preventDefault(); submitAnnouncement();">
                <div class="space-y-3">
                    <input type="text" id="annTitle" placeholder="Judul" class="w-full border rounded p-2 text-sm" required>
                    <textarea id="annContent" placeholder="Isi Pengumuman" class="w-full border rounded p-2 text-sm" rows="3" required></textarea>
                    <div class="grid grid-cols-2 gap-4">
                        <select id="annCourseId" class="w-full border rounded p-2 text-sm bg-white"></select>
                        <select id="annPriority" class="w-full border rounded p-2 text-sm bg-white">
                            <option value="normal">Normal</option>
                            <option value="high">Penting</option>
                            <option value="urgent">Mendesak</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="datetime-local" id="annPublishedAt" class="w-full border rounded p-2 text-sm">
                        <select id="annStatus" class="w-full border rounded p-2 text-sm bg-white">
                            <option value="published">Terbit</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-announcement')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Edit Profile -->
    <div id="modal-edit-profile" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4" onclick="if(event.target === this) closeModal('modal-edit-profile')">
        <div class="bg-white rounded-xl w-full max-w-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4 text-slate-800">Edit Profil</h3>
            <form onsubmit="event.preventDefault(); submitProfileUpdate();">
                <div class="space-y-4">
                    <h6 class="font-bold text-slate-700 text-sm mt-4 border-b pb-2">Informasi Pribadi</h6>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="edit-profile-name" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" id="edit-profile-email" class="w-full border rounded-lg px-3 py-2 text-sm bg-slate-100" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Telepon</label>
                        <input type="tel" id="edit-profile-phone" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
    
                    <h6 class="font-bold text-slate-700 text-sm mt-4 border-b pb-2">Informasi Akademik</h6>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spesialisasi</label>
                            <input type="text" id="edit-profile-spec" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pendidikan Terakhir</label>
                            <select id="edit-profile-edu" class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
                                <option value="">Pilih...</option>
                                <option value="S1 (Bachelor)">S1 (Bachelor)</option>
                                <option value="S2 (Master)">S2 (Master)</option>
                                <option value="S3 (Doctoral)">S3 (Doctoral)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pengalaman Mengajar (tahun)</label>
                        <input type="number" id="edit-profile-exp" min="0" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bio</label>
                        <textarea id="edit-profile-bio" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('modal-edit-profile')" class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-create-session" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4">Sesi Absensi Baru</h3>
            <form id="formCreateSession" onsubmit="event.preventDefault(); submitSession();">
                <div class="space-y-3">
                    <div><label class="text-xs font-bold uppercase text-slate-500">Nama Sesi</label><input type="text" id="sessionName" placeholder="Contoh: Pertemuan 1" class="w-full border rounded p-2 text-sm" required></div>
                    <div><label class="text-xs font-bold uppercase text-slate-500">Mulai</label><input type="datetime-local" id="sessionStartTime" class="w-full border rounded p-2 text-sm" required></div>
                    <div><label class="text-xs font-bold uppercase text-slate-500">Selesai</label><input type="datetime-local" id="sessionEndTime" class="w-full border rounded p-2 text-sm" required></div>
                    <div><label class="text-xs font-bold uppercase text-slate-500">Batas Akhir Check-in</label><input type="datetime-local" id="sessionDeadline" class="w-full border rounded p-2 text-sm" required></div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-create-session')" class="px-4 py-2 text-slate-500 text-sm font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded text-sm font-bold hover:bg-indigo-700">Buat Sesi</button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/instructorDashboard.js') }}"></script>
</body>
</html>