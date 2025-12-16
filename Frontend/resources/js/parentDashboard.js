
        // --- CONFIGURATION ---
        const API_BASE_URL = 'https://portohansgunawan.my.id/api';
        const API_V1 = API_BASE_URL + '/v1';

        // --- STATE ---
        let currentUser = null;
        let currentParent = null;
        let myStudents = [];
        let myAnnouncements = [];
        let allGrades = [];
        let allAttendance = [];
        let allCertificates = [];

        // --- PAGINATION STATE ---
        let currentStudentId = null;
        let gradesPage = 1;
        let attendancePage = 1;
        const ITEMS_PER_PAGE = 5;

        // --- AUTH & INIT ---
        document.addEventListener('DOMContentLoaded', initApp);

        async function initApp() {
            const token = localStorage.getItem('auth_token');

            if (!token) {
                const input = prompt("Token tidak ditemukan. Masukkan Bearer Token (dari Login):");
                if (input) {
                    localStorage.setItem('auth_token', input);
                    location.reload();
                } else {
                    alert("Token diperlukan untuk mengakses dashboard.");
                }
                return;
            }

            // Show Loading
            document.getElementById('loading-overlay').style.display = 'flex';

            try {
                // 1. Get User Profile
                const userRes = await fetchApi('/user');
                if (!userRes) throw new Error("Gagal memuat profil user");
                currentUser = userRes;
                document.getElementById('sidebar-username').innerText = currentUser.name;

                // 2. Find Parent Profile
                const parentRes = await fetchApi(`/v1/parents?search=${currentUser.email}`);
                if (parentRes && parentRes.data && parentRes.data.length > 0) {
                    currentParent = parentRes.data.find(p => p.email === currentUser.email);
                }

                if (!currentParent) {
                    const allParents = await fetchApi('/v1/parents?per_page=100');
                    if (allParents && allParents.data) {
                        currentParent = allParents.data.find(p => p.user_id === currentUser.id || p.email === currentUser.email);
                    }
                }

                if (!currentParent) {
                    throw new Error("Profil Orang Tua tidak ditemukan untuk akun ini.");
                }

                // 3. Load Data
                await loadDashboardData();

            } catch (error) {
                console.error("Init Error:", error);
                alert("Terjadi kesalahan: " + error.message);
                if (error.message.includes("Token")) {
                    localStorage.removeItem('auth_token');
                    location.reload();
                }
            } finally {
                document.getElementById('loading-overlay').style.display = 'none';
            }
        }

        async function fetchApi(endpoint, options = {}) {
            const token = localStorage.getItem('auth_token');
            const url = endpoint.startsWith('/v1') ? API_BASE_URL + endpoint : API_BASE_URL + endpoint;

            const headers = {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true',
                ...options.headers
            };

            try {
                const res = await fetch(url, { ...options, headers });
                if (res.status === 401) {
                    throw new Error("Token Expired");
                }
                // For 404, just return null without logging error (it's expected for some cases)
                if (res.status === 404) {
                    return null;
                }
                if (!res.ok) throw new Error(`API Error: ${res.status}`);
                return await res.json();
            } catch (err) {
                // Only log non-404 errors
                if (!err.message.includes('404')) {
                    console.error(`Fetch Error (${endpoint}):`, err);
                }
                return null;
            }
        }

        async function loadDashboardData() {
            // 1. Get Students
            const studentsRes = await fetchApi(`/v1/parents/${currentParent.id}/students`);
            myStudents = studentsRes || [];

            // 2. Get Announcements
            const annRes = await fetchApi('/v1/announcements');
            myAnnouncements = Array.isArray(annRes) ? annRes : (annRes.data || []);

            // 3. Get Grades & Attendance & Certificates for each student
            allGrades = [];
            allAttendance = [];
            allCertificates = [];

            for (const student of myStudents) {
                // Grades
                if (student.enrollments) {
                    for (const enroll of student.enrollments) {
                        try {
                            const gradesRes = await fetchApi(`/v1/grades/student?student_id=${student.id}&course_id=${enroll.course_id}`);
                            if (gradesRes && gradesRes.data && gradesRes.data.grades) {
                                const studentGrades = gradesRes.data.grades.map(g => ({
                                    ...g,
                                    student_name: student.full_name,
                                    student_id: student.id,
                                    course_name: enroll.course ? enroll.course.course_name : 'Course'
                                }));
                                allGrades.push(...studentGrades);
                            }
                        } catch (e) {
                            console.warn(`Failed to load grades for student ${student.id} course ${enroll.course_id}`, e);
                        }
                    }
                }

                // Attendance
                if (student.enrollments) {
                    for (const enroll of student.enrollments) {
                        try {
                            const attRes = await fetchApi(`/v1/attendance-records/student/${student.id}/course/${enroll.course_id}/history`);
                            if (attRes) {
                                const recs = (Array.isArray(attRes) ? attRes : (attRes.data || [])).map(a => ({
                                    ...a,
                                    student_name: student.full_name,
                                    student_id: student.id,
                                    course_name: enroll.course ? enroll.course.course_name : 'Course'
                                }));
                                allAttendance.push(...recs);
                            }
                        } catch (e) {
                            // Silently skip if student not enrolled or no attendance records
                            console.warn(`No attendance data for student ${student.id} in course ${enroll.course_id}`);
                        }
                    }
                }

                // Certificates
                const certRes = await fetchApi(`/v1/certificates/student/${student.id}`);
                if (certRes) {
                    const certs = (Array.isArray(certRes) ? certRes : (certRes.data || [])).map(c => ({ ...c, student_name: student.full_name }));
                    allCertificates.push(...certs);
                }
            }

            // 4. Render All
            renderDashboard();
            renderStudents();
            renderGrades();
            renderAttendance();
            renderAnnouncements();
            renderCertificates();
            updateFilters();
        }

        function updateFilters() {
            const gradeSelect = document.getElementById('grade-student-filter');
            const attSelect = document.getElementById('attendance-student-filter');

            // Clear existing options except first
            gradeSelect.innerHTML = '<option value="all">Semua Anak</option>';
            attSelect.innerHTML = '<option value="all">Semua Anak</option>';

            myStudents.forEach(s => {
                const opt1 = document.createElement('option');
                opt1.value = s.id;
                opt1.innerText = s.full_name;
                gradeSelect.appendChild(opt1);

                const opt2 = document.createElement('option');
                opt2.value = s.id;
                opt2.innerText = s.full_name;
                attSelect.appendChild(opt2);
            });
        }

        function switchView(viewName) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById('view-' + viewName);
            if (target) {
                target.classList.remove('hidden');
                document.getElementById('main-scroll').scrollTop = 0;
            }

            // Update Sidebar
            document.querySelectorAll('.nav-link-custom').forEach(el => el.classList.remove('active'));

            let navId = 'nav-' + viewName;
            if (viewName.includes('detail')) {
                if (viewName.includes('student')) navId = 'nav-students';
                else if (viewName.includes('announcement')) navId = 'nav-announcements';
            }

            const navEl = document.getElementById(navId);
            if (navEl) navEl.classList.add('active');
        }

        function renderDashboard() {
            // Stats
            document.getElementById('stat-students').innerText = myStudents.length;
            const totalEnrollments = myStudents.reduce((acc, s) => acc + (s.enrollments ? s.enrollments.length : 0), 0);
            document.getElementById('stat-enrollments').innerText = totalEnrollments;

            // Avg Grade
            let totalScore = 0;
            let countScore = 0;
            allGrades.forEach(g => {
                if (g.score) { totalScore += parseFloat(g.score); countScore++; }
            });
            const avg = countScore ? (totalScore / countScore).toFixed(1) : '-';
            document.getElementById('stat-avg-grade').innerText = avg;

            // Attendance Stat
            const presentCount = allAttendance.filter(a => a.attendance_status === 'present').length;
            const totalAtt = allAttendance.length;
            const attRate = totalAtt ? Math.round((presentCount / totalAtt) * 100) + '%' : '-';
            document.getElementById('stat-attendance').innerText = attRate;

            // Charts
            renderCharts();

            // Announcements Widget
            const recentAnn = myAnnouncements.slice(0, 2);
            document.getElementById('dashboard-announcements').innerHTML = recentAnn.length ? recentAnn.map(a => `
                <div class="flex flex-col p-4 rounded-xl bg-slate-50 border border-slate-100 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 py-1 text-[10px] uppercase font-bold tracking-wide bg-indigo-100 text-indigo-700 rounded-md">${a.type || 'Info'}</span>
                        <span class="text-xs text-slate-400">${new Date(a.created_at).toLocaleDateString()}</span>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-1">${a.title}</h4>
                    <p class="text-xs text-slate-500 line-clamp-2 mb-2">${a.content || ''}</p>
                    <button onclick="showAnnouncementDetail('${a.id}')" class="text-xs font-bold text-indigo-600 hover:underline self-start">Baca Selengkapnya</button>
                </div>
            `).join('') : '<p class="text-slate-500 text-sm p-4">Tidak ada pengumuman terbaru.</p>';
        }

        function renderCharts() {
            // 1. Grade Trend Chart (Per Assignment)
            const ctx1 = document.getElementById('gradeTrendChart');
            if (ctx1) {
                const existingChart1 = Chart.getChart("gradeTrendChart");
                if (existingChart1) existingChart1.destroy();

                // Sort grades by date
                const sortedGrades = [...allGrades].sort((a, b) => 
                    new Date(a.graded_at || a.created_at) - new Date(b.graded_at || b.created_at)
                );

                if (sortedGrades.length === 0) {
                    return; // No data to show
                }

                // Get unique grade dates/assignments as labels (limit to last 10 for readability)
                const uniqueDates = [...new Set(sortedGrades.map(g => {
                    const d = new Date(g.graded_at || g.created_at);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                }))].slice(-10); // Show last 10 data points

                const labels = uniqueDates.length ? uniqueDates : ["No Data"];
                const colors = ['#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4'];

                // Create dataset per student
                const datasets = myStudents.map((s, idx) => {
                    const studentGrades = sortedGrades.filter(g => g.student_id === s.id);
                    
                    // Map each label to corresponding grade
                    const data = labels.map(label => {
                        // Find grade that matches this label
                        const grade = studentGrades.find(g => {
                            const d = new Date(g.graded_at || g.created_at);
                            const dateLabel = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                            return dateLabel === label;
                        });
                        
                        return grade ? parseFloat(grade.score) : null;
                    });

                    return {
                        label: s.full_name,
                        data: data,
                        borderColor: colors[idx % colors.length],
                        backgroundColor: colors[idx % colors.length] + '40', // Add transparency
                        tension: 0.4, // More curve for smoother lines
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        spanGaps: true,
                        fill: false
                    };
                });

                new Chart(ctx1, {
                    type: 'line',
                    data: { labels: labels, datasets: datasets },
                    options: {
                        responsive: true, 
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { 
                                display: true, 
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': ' + (context.parsed.y || 'N/A');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                min: 0, 
                                max: 100, 
                                grid: { color: '#f1f5f9' }, 
                                title: { display: true, text: 'Nilai' },
                                ticks: {
                                    stepSize: 10
                                }
                            },
                            x: { 
                                grid: { display: false },
                                title: { display: true, text: 'Tanggal Penilaian' }
                            }
                        }
                    }
                });
            }

            // 2. Attendance Bar Chart
            const ctx2 = document.getElementById('attendanceBarChart');
            if (ctx2) {
                const existingChart2 = Chart.getChart("attendanceBarChart");
                if (existingChart2) existingChart2.destroy();

                const labels = myStudents.map(s => s.full_name);
                const presentData = myStudents.map(s => allAttendance.filter(a => a.student_id === s.id && a.attendance_status === 'present').length);
                const absentData = myStudents.map(s => allAttendance.filter(a => a.student_id === s.id && a.attendance_status !== 'present').length);

                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Hadir', data: presentData, backgroundColor: '#10b981', borderRadius: 4 },
                            { label: 'Absen/Izin', data: absentData, backgroundColor: '#f59e0b', borderRadius: 4 }
                        ]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { display: false } } },
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }

        function renderStudents() {
            const container = document.getElementById('students-container');
            if (!myStudents.length) {
                container.innerHTML = '<p class="col-span-3 text-center text-slate-500">Tidak ada data siswa.</p>';
                return;
            }

            const colors = ['text-indigo-600 bg-indigo-50', 'text-pink-600 bg-pink-50', 'text-emerald-600 bg-emerald-50'];

            container.innerHTML = myStudents.map((s, idx) => {
                const colorClass = colors[idx % colors.length];
                return `
                <div class="dashboard-card group hover:border-indigo-300">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-16 h-16 rounded-2xl ${colorClass} flex items-center justify-center text-3xl shadow-sm group-hover:scale-105 transition-transform">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-lg">Aktif</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">${s.full_name}</h3>
                    <p class="text-sm text-slate-500 mb-4">${s.email || '-'} | ${s.phone || '-'}</p>
                    <div class="flex gap-2">
                        <button onclick="showStudentDetail('${s.id}')" class="flex-1 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                            Lihat Profil
                        </button>
                    </div>
                </div>`;
            }).join('');
        }

        function showStudentDetail(id) {
            const student = myStudents.find(s => s.id == id);
            if (!student) return;

            currentStudentId = id;
            gradesPage = 1;
            attendancePage = 1;

            document.getElementById('detail-student-name').innerText = student.full_name;
            document.getElementById('detail-student-email').innerText = student.email || '-';
            document.getElementById('detail-student-phone').innerText = student.phone || '-';

            renderStudentGrades();
            renderStudentAttendance();

            switchView('student-detail');
        }

        function renderStudentGrades() {
            if (!currentStudentId) return;
            const sGrades = allGrades.filter(g => g.student_id == currentStudentId);
            const totalPages = Math.ceil(sGrades.length / ITEMS_PER_PAGE);

            const start = (gradesPage - 1) * ITEMS_PER_PAGE;
            const end = start + ITEMS_PER_PAGE;
            const pageItems = sGrades.slice(start, end);

            const container = document.getElementById('detail-student-grades');

            let html = pageItems.length ? pageItems.map(g => `
                <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg"><i class="bi bi-journal-text"></i></div>
                        <div><p class="font-bold text-sm text-slate-800">${g.grade_component ? g.grade_component.name : 'Nilai'}</p><p class="text-xs text-slate-500">Score</p></div>
                    </div>
                    <span class="text-lg font-bold text-slate-800">${g.score}</span>
                </div>
            `).join('') : '<p class="text-slate-500 italic">Belum ada data nilai.</p>';

            if (totalPages > 1) {
                html += `
                <div class="flex justify-between items-center mt-4 pt-2 border-t border-slate-100">
                    <button onclick="changeGradesPage(-1)" ${gradesPage === 1 ? 'disabled' : ''} class="px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed">Prev</button>
                    <span class="text-xs text-slate-500">Page ${gradesPage} of ${totalPages}</span>
                    <button onclick="changeGradesPage(1)" ${gradesPage === totalPages ? 'disabled' : ''} class="px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>`;
            }

            container.innerHTML = html;
        }

        function changeGradesPage(delta) {
            gradesPage += delta;
            renderStudentGrades();
        }

        function renderStudentAttendance() {
            if (!currentStudentId) return;
            const sAtt = allAttendance.filter(a => a.student_id == currentStudentId);
            const totalPages = Math.ceil(sAtt.length / ITEMS_PER_PAGE);

            const start = (attendancePage - 1) * ITEMS_PER_PAGE;
            const end = start + ITEMS_PER_PAGE;
            const pageItems = sAtt.slice(start, end);

            const container = document.getElementById('detail-student-attendance');

            let html = pageItems.length ? pageItems.map(a => `
                <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                         <div class="p-2 bg-slate-50 text-slate-500 rounded-lg"><i class="bi bi-calendar-date"></i></div>
                         <div><p class="font-bold text-sm text-slate-800">${new Date(a.attendance_time).toLocaleDateString()}</p><p class="text-xs text-slate-500">${a.course_name}</p></div>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded ${a.status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">${a.status}</span>
                </div>
            `).join('') : '<p class="text-slate-500 italic">Belum ada data kehadiran.</p>';

            if (totalPages > 1) {
                html += `
                <div class="flex justify-between items-center mt-4 pt-2 border-t border-slate-100">
                    <button onclick="changeAttendancePage(-1)" ${attendancePage === 1 ? 'disabled' : ''} class="px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed">Prev</button>
                    <span class="text-xs text-slate-500">Page ${attendancePage} of ${totalPages}</span>
                    <button onclick="changeAttendancePage(1)" ${attendancePage === totalPages ? 'disabled' : ''} class="px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>`;
            }

            container.innerHTML = html;
        }

        function changeAttendancePage(delta) {
            attendancePage += delta;
            renderStudentAttendance();
        }

        function renderGrades() {
            const filterId = document.getElementById('grade-student-filter').value;
            let filtered = allGrades;
            if (filterId !== 'all') {
                filtered = allGrades.filter(g => g.student_id == filterId);
            }

            const container = document.getElementById('grades-container');
            if (!filtered.length) {
                container.innerHTML = '<p class="col-span-full text-center text-slate-500">Tidak ada data nilai.</p>';
                return;
            }

            container.innerHTML = filtered.map(g => {
                const isGood = parseFloat(g.score) >= 75;
                const badgeColor = isGood ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                const componentName = g.grade_component ? g.grade_component.name : 'Tugas';

                return `
                <div class="dashboard-card grade-card relative overflow-hidden group rounded p-4">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><i class="bi bi-journal-bookmark-fill text-6xl text-slate-400"></i></div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-white rounded-lg shadow-sm text-indigo-600"><i class="bi bi-journal-text text-xl"></i></div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">${componentName}</div>
                    </div>
                    <div class="flex items-end justify-between mb-2">
                        <div><span class="text-4xl font-extrabold text-slate-800">${g.score}</span><span class="text-xs text-slate-400">/100</span></div>
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase ${badgeColor}">${isGood ? 'Lulus' : 'Remedial'}</span>
                    </div>
                    <div class="border-t border-slate-100 pt-3 mt-2">
                        <p class="text-xs text-slate-500 font-medium mb-1 truncate">Siswa: ${g.student_name}</p>
                    </div>
                </div>`;
            }).join('');
        }

        function renderAttendance() {
            const filterId = document.getElementById('attendance-student-filter').value;
            let filtered = allAttendance;
            if (filterId !== 'all') {
                filtered = allAttendance.filter(a => a.student_id == filterId);
            }

            const container = document.getElementById('attendance-list');
            if (!filtered.length) {
                container.innerHTML = '<p class="text-center text-slate-500">Tidak ada data kehadiran.</p>';
                return;
            }

            container.innerHTML = filtered.map(a => {
                const dateObj = new Date(a.attendance_time);
                const day = dateObj.getDate();
                const month = dateObj.toLocaleString('default', { month: 'short' });

                return `
                <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-200 hover:shadow-sm transition-all">
                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-50 p-2 rounded-lg text-center min-w-[50px]">
                            <span class="block text-xs font-bold text-indigo-400 uppercase">${month}</span>
                            <span class="block text-xl font-bold text-indigo-700">${day}</span>
                        </div>
                        <div><h4 class="font-bold text-sm text-slate-800">${a.student_name}</h4><p class="text-xs text-slate-500">${a.course_name}</p></div>
                    </div>
                    <span class="px-3 py-1 ${a.status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'} text-xs font-bold rounded-full">${a.status}</span>
                </div>`;
            }).join('');
        }

        function renderAnnouncements() {
            const container = document.getElementById('announcements-full-list');
            if (!myAnnouncements.length) {
                container.innerHTML = '<p class="text-center text-slate-500">Tidak ada pengumuman.</p>';
                return;
            }

            container.innerHTML = myAnnouncements.map(a => `
                <div class="dashboard-card rounded p-4 flex flex-col md:flex-row gap-4 items-start">
                    <div class="shrink-0 pt-1 hidden md:block">
                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded">${a.type || 'Umum'}</span>
                            <span class="text-xs text-slate-400">• ${new Date(a.created_at).toLocaleDateString()}</span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-2">${a.title}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4 line-clamp-2">${a.content || ''}</p>
                        <button onclick="showAnnouncementDetail('${a.id}')" class="px-4 py-2 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            Baca Selengkapnya
                        </button>
                    </div>
                </div>
                `).join('');
        }

        function showAnnouncementDetail(id) {
            const ann = myAnnouncements.find(a => a.id == id);
            if (!ann) return;

            document.getElementById('detail-announcement-title').innerText = ann.title;
            document.getElementById('detail-announcement-date').innerText = new Date(ann.created_at).toLocaleDateString();
            document.getElementById('detail-announcement-tag').innerText = ann.type || 'Info';
            document.getElementById('detail-announcement-content').innerText = ann.content;

            switchView('announcement-detail');
        }

        function renderCertificates() {
            const container = document.getElementById('certificates-list');
            if (!allCertificates.length) {
                container.innerHTML = '<p class="col-span-full text-center text-slate-500">Belum ada sertifikat.</p>';
                return;
            }

            container.innerHTML = allCertificates.map(c => `
                <div class="dashboard-card rounded p-4 relative overflow-hidden group border-b-4 border-b-amber-400">
                    <div class="absolute -right-6 -top-6 text-8xl text-amber-50 opacity-50 group-hover:rotate-12 transition-transform"><i class="bi bi-award-fill"></i></div>
                    <div class="relative z-10">
                        <div class="h-12 w-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center mb-4 shadow-sm"><i class="bi bi-trophy-fill text-2xl"></i></div>
                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] uppercase font-bold tracking-wider rounded mb-2">Sertifikat</span>
                        <h3 class="text-lg font-bold text-slate-800 leading-tight mb-1">${c.certificate_code}</h3>
                        <p class="text-sm text-slate-500 mb-4">Milik: ${c.student_name}</p>
                        <div class="flex items-center gap-2 text-xs text-slate-400 border-t border-slate-100 pt-3">
                            <i class="bi bi-calendar-check"></i> ${new Date(c.issued_at).toLocaleDateString()}
                        </div>
                        <button class="mt-4 w-full py-2 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors flex items-center justify-center gap-2"><i class="bi bi-download"></i> Unduh Sertifikat</button>
                    </div>
                </div>
            `).join('');
        }

        function logout() {
            if (confirm('Keluar sistem?')) {
                localStorage.removeItem('auth_token');
                location.reload();
            }
        }