<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMARTDev Academic - Platform Pembelajaran Modern</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#7C3AED', // Violet 600
                        dark: '#0F172A', // Slate 900
                        light: '#F8FAFC', // Slate 50
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Custom Premium Styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            overflow-x: hidden;
        }

        /* Smooth Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Staggered Delays */
        .delay-100 { transition-delay: 0.1s; }
        .delay-200 { transition-delay: 0.2s; }
        .delay-300 { transition-delay: 0.3s; }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Premium Card Hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.1);
        }

        /* Gradient Text */
        .text-gradient {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #F1F5F9; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        /* Blob Background Animation */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
            animation: float 10s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, -20px); }
        }
    </style>
</head>

<body class="antialiased">

    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-4">
        <div class="container mx-auto px-6">
            <div class="glass rounded-2xl px-6 py-3 flex justify-between items-center shadow-sm">
                <a href="#" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform duration-300">
                        <i class="fas fa-graduation-cap text-lg"></i>
                    </div>
                    <div class="leading-tight">
                        <h1 class="font-bold text-lg text-dark tracking-tight">SMARTDev</h1>
                        <p class="text-[10px] text-gray-500 font-medium tracking-wider">ACADEMIC</p>
                    </div>
                </a>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Beranda</a>
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Fitur</a>
                    <a href="#courses" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Kursus</a>
                    <a href="#faq" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">FAQ</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}"
                        class="text-sm font-semibold text-gray-600 hover:text-primary transition-colors">Masuk</a>
                    <a href="{{ route('registration') }}"
                        class="bg-dark text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary transition-all duration-300 shadow-lg hover:shadow-primary/30 transform hover:-translate-y-0.5">
                        Daftar Sekarang
                    </a>
                </div>

                <button class="md:hidden text-gray-600 text-xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <section id="home" class="relative min-h-screen flex items-center pt-24 overflow-hidden">
        <div class="blob bg-purple-300 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bg-blue-300 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/2 translate-y-1/2 animation-delay-2000"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-semibold mb-6">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Platform Pembelajaran Masa Depan
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold leading-tight mb-6 text-dark">
                        Revolusi Cara <br>
                        <span class="text-gradient">Belajar & Mengajar</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed max-w-lg">
                        Platform LMS terintegrasi yang menghubungkan siswa, guru, dan orang tua dalam satu ekosistem digital yang modern, aman, dan menyenangkan.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('registration') }}" class="px-8 py-4 bg-primary text-white rounded-xl font-semibold shadow-xl shadow-primary/30 hover:bg-primary/90 transition-all duration-300 transform hover:-translate-y-1 text-center">
                            Mulai Gratis
                        </a>
                    </div>

                    <div class="mt-12 flex items-center gap-8 pt-8 border-t border-gray-200/60">
                        <div>
                            <h4 class="text-2xl font-bold text-dark">10k+</h4>
                            <p class="text-sm text-gray-500">Siswa Aktif</p>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-dark">500+</h4>
                            <p class="text-sm text-gray-500">Instruktur</p>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-dark">4.9</h4>
                            <p class="text-sm text-gray-500">Rating User</p>
                        </div>
                    </div>
                </div>

                <div class="relative reveal delay-200 hidden md:block">
                    <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border-4 border-white transform rotate-2 hover:rotate-0 transition-all duration-700">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80" alt="Students learning" class="w-full h-auto object-cover">

                        <div class="absolute bottom-8 left-8 bg-white p-4 rounded-xl shadow-lg flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tugas Selesai</p>
                                <p class="font-bold text-sm text-dark">Matematika Dasar</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-yellow-400 rounded-full opacity-20 blur-xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-primary rounded-full opacity-20 blur-xl"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white relative">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4">Solusi Untuk Semua</h2>
                <p class="text-gray-600">Satu platform yang memenuhi kebutuhan setiap pemangku kepentingan dalam ekosistem pendidikan.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="group p-8 rounded-3xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 reveal delay-100">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Untuk Siswa</h3>
                    <p class="text-gray-500 mb-6 leading-relaxed">Akses materi belajar interaktif, kerjakan tugas, dan pantau progres nilai secara real-time dari mana saja.</p>
                    <a href="#" class="text-blue-600 font-semibold flex items-center gap-2 group-hover:gap-3 transition-all">
                        Pelajari Lebih Lanjut <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <div class="group p-8 rounded-3xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 reveal delay-200">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Untuk Pengajar</h3>
                    <p class="text-gray-500 mb-6 leading-relaxed">Kelola kelas, buat soal ujian otomatis, dan analisis perkembangan siswa dengan dashboard analitik canggih.</p>
                    <a href="#" class="text-purple-600 font-semibold flex items-center gap-2 group-hover:gap-3 transition-all">
                        Pelajari Lebih Lanjut <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <div class="group p-8 rounded-3xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 reveal delay-300">
                    <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Untuk Orang Tua</h3>
                    <p class="text-gray-500 mb-6 leading-relaxed">Pantau kehadiran, nilai, dan aktivitas belajar anak Anda secara transparan dan langsung dari aplikasi.</p>
                    <a href="#" class="text-green-600 font-semibold flex items-center gap-2 group-hover:gap-3 transition-all">
                        Pelajari Lebih Lanjut <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-24 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="flex mx-auto flex-col md:flex-row justify-between items-end mb-16 reveal">
                <div class="text-center mx-auto max-w-2xl">
                    <span class="text-primary font-semibold tracking-wider text-sm uppercase mb-2 block">Fitur Unggulan</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-dark">Teknologi Pendidikan Terdepan</h2>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 reveal">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-book"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Materi Terstruktur</h4>
                    <p class="text-sm text-gray-500">Kursus, Module, dan Materi disusun dengan struktur hierarki yang jelas. Mendukung PDF, video, dan link.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 reveal delay-100">
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Manajemen Tugas</h4>
                    <p class="text-sm text-gray-500">Submit tugas file atau teks, pelacakan deadline otomatis, dan feedback langsung dari pengajar.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 reveal delay-200">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Monitoring Nilai</h4>
                    <p class="text-sm text-gray-500">Komponen nilai fleksibel dengan perhitungan bobot otomatis dan grafik perkembangan siswa.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 reveal delay-300">
                    <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-pencil-ruler"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Course Builder</h4>
                    <p class="text-sm text-gray-500">Buat modul & materi dengan mudah, upload berbagai format file, dan atur urutan pembelajaran.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="courses" class="py-24 bg-white transition-all duration-300">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4">Jelajahi Kelas Populer</h2>
                <p class="text-gray-600">Pilih materi pembelajaran yang sesuai dengan jenjang pendidikan Anda.</p>
            </div>

            <div class="flex justify-center gap-4 mb-12 reveal">
                <button onclick="switchCourseTab('sma')" id="btn-sma"
                    class="px-8 py-2 rounded-full bg-dark text-white font-medium shadow-lg transform transition-all duration-300 hover:scale-105">
                    SMA
                </button>
                <button onclick="switchCourseTab('smp')" id="btn-smp"
                    class="px-8 py-2 rounded-full bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition-all duration-300 hover:scale-105">
                    SMP
                </button>
            </div>

            <div class="relative min-h-[400px]">
                
                <div id="courses-sma" class="grid md:grid-cols-3 gap-8 transition-opacity duration-500">
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover reveal">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-dark">
                                Matematika SMA
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-book-open mr-1"></i> 12 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 24 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">Aljabar Linear & Matriks</h3>
                            <p class="text-sm text-gray-500 mb-4">Pelajari konsep dasar aljabar linear untuk tingkat SMA kelas 12.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Pak Budi</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover reveal delay-100">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-dark">
                                Sains SMA
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-flask mr-1"></i> 8 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 16 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">Fisika Kuantum Dasar</h3>
                            <p class="text-sm text-gray-500 mb-4">Pengenalan dunia fisika modern untuk siswa berbakat.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Bu Siti</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover reveal delay-200">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-dark">
                                Teknologi
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-laptop mr-1"></i> 15 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 30 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">Dasar Pemrograman Python</h3>
                            <p class="text-sm text-gray-500 mb-4">Belajar coding dari nol dengan bahasa Python yang populer.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Pak Andi</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="courses-smp" class="hidden grid md:grid-cols-3 gap-8 transition-opacity duration-500 opacity-0">
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-blue-600">
                                IPA Terpadu
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-leaf mr-1"></i> 10 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 18 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">Biologi & Ekosistem</h3>
                            <p class="text-sm text-gray-500 mb-4">Memahami makhluk hidup dan lingkungannya untuk kelas 7 SMP.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Bu Ratna</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-purple-600">
                                Bahasa
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-language mr-1"></i> 14 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 20 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">English Conversation</h3>
                            <p class="text-sm text-gray-500 mb-4">Latihan percakapan bahasa Inggris sehari-hari yang percaya diri.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Mr. John</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
                        <div class="h-48 bg-gray-200 relative">
                            <img src="https://images.unsplash.com/photo-1596495578065-6e0763fa1178?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full h-full object-cover" alt="Course">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-orange-600">
                                Matematika SMP
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-calculator mr-1"></i> 12 Modul</span>
                                <span><i class="fas fa-clock mr-1"></i> 22 Jam</span>
                            </div>
                            <h3 class="text-lg font-bold text-dark mb-2">Geometri Bangun Ruang</h3>
                            <p class="text-sm text-gray-500 mb-4">Cara mudah menghitung volume dan luas permukaan bangun ruang.</p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Pak Dedi</span>
                                <button class="text-primary font-semibold text-sm hover:underline">Detail</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-24 bg-gray-50 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-0 w-64 h-64 bg-blue-200/20 rounded-full blur-3xl -translate-x-1/2"></div>
            <div class="absolute bottom-1/4 right-0 w-64 h-64 bg-purple-200/20 rounded-full blur-3xl translate-x-1/2"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                <span class="text-primary font-semibold tracking-wider text-sm uppercase mb-2 block">Alur Pendaftaran</span>
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4">Mulai Perjalanan Anda</h2>
                <p class="text-gray-600">Proses pendaftaran yang mudah dan transparan dalam 4 langkah sederhana.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 relative">
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 -translate-y-1/2 z-0 transform -translate-y-8"></div>

                <div class="relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group reveal z-10">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full flex items-center justify-center border-4 border-gray-50 shadow-sm">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md group-hover:scale-110 transition-transform">1</div>
                    </div>
                    <div class="mt-8 text-center">
                        <div class="w-16 h-16 mx-auto bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:rotate-6 transition-transform duration-300">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Isi Form</h3>
                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">Lengkapi data diri calon siswa dan informasi orang tua.</p>
                        <div class="inline-block px-3 py-1 bg-yellow-50 text-yellow-600 border border-yellow-100 rounded-full text-xs font-mono">
                            pending documents
                        </div>
                    </div>
                </div>

                <div class="relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group reveal delay-100 z-10">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full flex items-center justify-center border-4 border-gray-50 shadow-sm">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md group-hover:scale-110 transition-transform">2</div>
                    </div>
                    <div class="mt-8 text-center">
                        <div class="w-16 h-16 mx-auto bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:rotate-6 transition-transform duration-300">
                            <i class="fas fa-upload"></i>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Upload Dokumen</h3>
                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">Upload KTP, Ijazah, dan bukti pembayaran pendaftaran.</p>
                        <div class="inline-block px-3 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-full text-xs font-mono">
                            pending approval
                        </div>
                    </div>
                </div>

                <div class="relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group reveal delay-200 z-10">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full flex items-center justify-center border-4 border-gray-50 shadow-sm">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md group-hover:scale-110 transition-transform">3</div>
                    </div>
                    <div class="mt-8 text-center">
                        <div class="w-16 h-16 mx-auto bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:rotate-6 transition-transform duration-300">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Verifikasi</h3>
                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">Tim admin kami akan memverifikasi data Anda (1-3 hari).</p>
                        <div class="inline-block px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full text-xs font-mono">
                            verifying
                        </div>
                    </div>
                </div>

                <div class="relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group reveal delay-300 z-10">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full flex items-center justify-center border-4 border-gray-50 shadow-sm">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md group-hover:scale-110 transition-transform">4</div>
                    </div>
                    <div class="mt-8 text-center">
                        <div class="w-16 h-16 mx-auto bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:rotate-6 transition-transform duration-300">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Mulai Belajar</h3>
                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">Akun aktif! Akses dashboard dan mulai pembelajaran.</p>
                        <div class="inline-block px-3 py-1 bg-green-50 text-green-600 border border-green-100 rounded-full text-xs font-mono">
                            active
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-dark z-0"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-secondary/20 z-0"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary/30 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

        <div class="container mx-auto px-6 relative z-10 text-center reveal">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Siap Memulai Perjalanan Belajar?</h2>
            <p class="text-gray-300 text-lg mb-10 max-w-2xl mx-auto">Bergabunglah dengan ribuan siswa lainnya dan rasakan pengalaman belajar yang berbeda.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('registration') }}"
                    class="px-8 py-4 bg-white text-dark rounded-xl font-bold hover:bg-gray-100 transition-all transform hover:-translate-y-1 shadow-lg">
                    Daftar Sekarang
                </a>
                <a href="#contact" class="px-8 py-4 bg-transparent border border-white/30 text-white rounded-xl font-bold hover:bg-white/10 transition-all backdrop-blur-sm">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white pt-20 pb-10 border-t border-gray-800">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-sm"></i>
                        </div>
                        <span class="font-bold text-xl">SMARTDev</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        Platform pembelajaran digital terdepan untuk memajukan pendidikan Indonesia melalui teknologi.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold mb-6">Menu</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Harga</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-6">Bantuan</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Pusat Bantuan</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-6">Kontak</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-primary"></i>
                            <span>Jl. Pendidikan No. 123, Jakarta Selatan, Indonesia</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-primary"></i>
                            <span>info@smartdev.id</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone text-primary"></i>
                            <span>+62 812 3456 7890</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
                <p>&copy; 2025 SMARTDev Academic. All rights reserved. Developed by <a href="https://hans-porto-web.vercel.app/" class="text-green-500">Hans Gunawan</a></p>
            </div>
        </div>
    </footer>

    <div id="devModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="devModalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md scale-95 opacity-0" id="devModalPanel">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-tools text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Development In Progress</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 leading-relaxed">
                                        Halo! Terima kasih telah mengunjungi <strong>SMARTDev Academic</strong>. 
                                        Saat ini sistem sedang dalam tahap pengembangan aktif (Beta). Fitur Login dan Registrasi akan segera tersedia sepenuhnya.
                                    </p>
                                    <p>Develop by : Hans Gunawan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="closeDevModal()"
                            class="inline-flex w-full justify-center rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 sm:ml-3 sm:w-auto transition-colors">
                            Mengerti
                        </button>
                        <button type="button" onclick="closeDevModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-6 left-6 z-40 animate-bounce" style="animation-duration: 4s;">
        <div class="glass px-4 py-2 rounded-full shadow-lg border border-white/40 flex items-center gap-3">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
            </span>
            <div>
                <p class="text-xs font-bold text-dark">Beta Preview</p>
                <p class="text-[10px] text-gray-500">Development Mode</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Navbar Scroll Effect
            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('bg-white/80', 'backdrop-blur-md', 'shadow-md');
                    navbar.classList.remove('py-4');
                    navbar.classList.add('py-2');
                } else {
                    navbar.classList.remove('bg-white/80', 'backdrop-blur-md', 'shadow-md');
                    navbar.classList.remove('py-2');
                    navbar.classList.add('py-4');
                }
            });

            // Intersection Observer for Reveal Animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });

            // --- Development Modal Logic ---
            const devLinks = document.querySelectorAll('a[href="#login"], a[href="#register"]');
            
            devLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    showDevModal();
                });
            });
        });

        // --- Course Tab Logic ---
        function switchCourseTab(tab) {
            const smaContainer = document.getElementById('courses-sma');
            const smpContainer = document.getElementById('courses-smp');
            const btnSma = document.getElementById('btn-sma');
            const btnSmp = document.getElementById('btn-smp');

            // Reset Animation Classes
            smaContainer.classList.remove('opacity-100');
            smaContainer.classList.add('opacity-0');
            smpContainer.classList.remove('opacity-100');
            smpContainer.classList.add('opacity-0');

            setTimeout(() => {
                if (tab === 'sma') {
                    smaContainer.classList.remove('hidden');
                    smpContainer.classList.add('hidden');
                    
                    // Button Styles
                    btnSma.classList.add('bg-dark', 'text-white', 'shadow-lg');
                    btnSma.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    
                    btnSmp.classList.remove('bg-dark', 'text-white', 'shadow-lg');
                    btnSmp.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');

                    // Fade In
                    setTimeout(() => {
                        smaContainer.classList.remove('opacity-0');
                        smaContainer.classList.add('opacity-100');
                    }, 50);

                } else {
                    smpContainer.classList.remove('hidden');
                    smaContainer.classList.add('hidden');

                    // Button Styles
                    btnSmp.classList.add('bg-dark', 'text-white', 'shadow-lg');
                    btnSmp.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');

                    btnSma.classList.remove('bg-dark', 'text-white', 'shadow-lg');
                    btnSma.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');

                    // Fade In
                    setTimeout(() => {
                        smpContainer.classList.remove('opacity-0');
                        smpContainer.classList.add('opacity-100');
                    }, 50);
                }
            }, 300); // Wait for fade out
        }

        // --- Modal Functions ---
        function showDevModal() {
            const modal = document.getElementById('devModal');
            const backdrop = document.getElementById('devModalBackdrop');
            const panel = document.getElementById('devModalPanel');

            modal.classList.remove('hidden');
            
            // Animation In
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDevModal() {
            const modal = document.getElementById('devModal');
            const backdrop = document.getElementById('devModalBackdrop');
            const panel = document.getElementById('devModalPanel');

            // Animation Out
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</body>

</html>