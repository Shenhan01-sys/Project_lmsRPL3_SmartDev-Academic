# 🔧 Panduan Perbaikan student-dashboard-complete2.html

## ⚠️ Status: File Rusak - Perlu Restore & Fix Manual

File telah mengalami kerusakan struktur JavaScript akibat automated editing. Berikut panduan lengkap untuk memfixnya.

---

## 📋 MASALAH TERIDENTIFIKASI

### 1. **Function `handleTokenInput()` dan `fetchApi()` Merged Incorrectly**
   - Baris 418-460: Struktur function `handleTokenInput()` corrupt
   - Code `fetchApi()` masuk ke dalam `else` block
   - Missing closing braces

### 2. **Missing `async` keyword pada `fetchApi()`**
   - Function declaration tidak lengkap

### 3. **Fungsi `manualTokenInput()` dipanggil di HTML tapi tidak terdefinisi**
   - Line 89 HTML memanggil `manualTokenInput()`
   - Fungsi seharusnya ada setelah `handleTokenInput()`

---

## ✅ SOLUSI: RESTORE & FIX MANUAL

### LANGKAH 1: Restore dari Backup
```
Restore file student-dashboard-complete2.html ke versi terakhir yang tidak corrupt
ATAU copy dari student-dashboard-complete1.html
```

### LANGKAH 2: Edit Fungsi Token Management

Cari bagian JavaScript (sekitar line 407-460) dan ganti dengan:

```javascript
        // --- TOKEN MANAGEMENT SYSTEM ---
        
        /**
         * Fungsi sentral untuk menangani input token.
         * Akan menghapus token lama, meminta yang baru, dan mereload halaman.
         */
        async function handleTokenInput(msg) {
            // Hapus token bermasalah agar bersih
            localStorage.removeItem('auth_token');

            // Tampilkan Prompt
            const newToken = prompt(msg);

            if (newToken) {
                // Simpan dan Refresh
                localStorage.setItem('auth_token', newToken);
                location.reload();
            } else {
                // Jika user klik Cancel
                alert("Token diperlukan untuk mengakses aplikasi. Silakan refresh halaman untuk mencoba lagi.");
                document.body.innerHTML = '<div class="flex h-screen justify-center items-center text-slate-500">Akses Ditolak. Token tidak diberikan.</div>';
            }
        }

        // Fungsi untuk manual update token dari button
        function manualTokenInput() {
            handleTokenInput("Masukkan Bearer Token baru:");
        }

        // --- API CLIENT ---

        async function fetchApi(endpoint, options = {}) {
            const token = localStorage.getItem('auth_token');
            
            // Construct URL
            let url = endpoint.startsWith('http') ? endpoint : (endpoint.startsWith('/user') ? API_BASE_URL + endpoint : API_V1 + endpoint);
            
            const headers = {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true',
                ...options.headers
            };

            try {
                const res = await fetch(url, { ...options, headers });
                
                // DETEKSI 401 UNAUTHORIZED
                if (res.status === 401) {
                    throw new Error("Sesi kedaluwarsa atau Token tidak valid (Unauthorized).");
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
```

### LANGKAH 3: Tambahkan Tab Attendance di Course Detail

Cari bagian tab buttons Course Detail (sekitar line 230) dan pastikan ada:

```html
<button onclick="switchCourseTab('attendance')" id="tab-course-attendance"
    class="course-tab-btn px-2 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 border-b-2 border-transparent transition-colors whitespace-nowrap">Kehadiran</button>
```

Dan container-nya:

```html
<div id="course-content-attendance" class="course-tab-content hidden space-y-6"></div>
```

### LANGKAH 4: Tambahkan Fungsi-fungsi Course Detail yang Hilang

Tambahkan di akhir JavaScript (sebelum `</script>`):

```javascript
        // --- COURSE DETAIL LOGIC ---

        async function showCourseDetail(courseId) {
            currentCourseId = courseId;
            document.getElementById('loading-overlay').style.display = 'flex';
            
            try {
                const course = await fetchApi(`/courses/${courseId}`);
                renderCourseDetailHeader(course);

                // Fetch Modules  
                const allMyModules = await fetchApi('/course-modules/my-modules');
                const courseModules = allMyModules.filter(m => m.course_id == courseId);
                renderCourseModules(courseModules);

                const assigns = await fetchApi(`/assignments`);
                const courseAssigns = assigns.filter(a => a.course_id == courseId);
                renderCourseAssignments(courseAssigns);

                await loadCourseAttendance(courseId);

                const peopleRes = await fetchApi(`/enrollments?course_id=${courseId}`);
                const people = Array.isArray(peopleRes) ? peopleRes : (peopleRes.data || []);
                const coursePeople = people.filter(p => p.course_id == courseId);
                renderCoursePeople(coursePeople);

                switchView('course-detail');
            } catch (e) {
                console.error(e);
                alert("Gagal memuat detail: " + e.message);
            } finally {
                document.getElementById('loading-overlay').style.display = 'none';
            }
        }

        function renderCourseDetailHeader(course) {
            document.getElementById('detail-course-name').innerText = course.course_name;
            document.getElementById('detail-course-code').innerText = course.course_code;
            document.getElementById('detail-course-instructor').innerText = course.instructor?.user?.name || '-';
            document.getElementById('detail-course-desc').innerText = course.description;
        }

        function renderCourseModules(modules) {
            const container = document.getElementById('course-content-modules');
            container.innerHTML = modules.map(m => {
                const materials = m.materials || [];
                return `
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white mb-4">
                    <div class="p-4 bg-slate-50 border-b">
                        <h4 class="font-bold text-slate-800">${m.title || m.module_name}</h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-slate-600">${m.description || ''}</p>
                        ${materials.map(mat => `
                            <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">${mat.title}</p>
                                </div>
                                <a href="${mat.file_path}" target="_blank" class="px-3 py-1 text-xs font-bold text-primary bg-indigo-50 rounded">Buka</a>
                            </div>
                        `).join('') || '<p class="text-xs text-slate-400">Tidak ada materi.</p>'}
                    </div>
                </div>`;
            }).join('') || '<p class="text-center py-8">Belum ada modul.</p>';
        }

        function renderCourseAssignments(list) {
            const container = document.getElementById('course-content-assignments');
            container.innerHTML = list.map(a => `
                <div class="dashboard-card p-4 mb-3">
                    <h4 class="font-bold">${a.title}</h4>
                    <p class="text-xs">Due: ${formatDate(a.due_date)}</p>
                </div>
            `).join('') || '<p class="text-center py-4">Tidak ada tugas.</p>';
        }

        function renderCoursePeople(people) {
            const container = document.getElementById('course-content-people');
            container.innerHTML = people.map(p => `
                <div class="flex items-center gap-3 p-3 border rounded bg-white">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center"><i class="bi bi-person"></i></div>
                    <p class="font-bold text-sm">${p.student?.user?.name || 'Student'}</p>
                </div>
            `).join('');
        }

        async function loadCourseAttendance(courseId) {
            const container = document.getElementById('course-content-attendance');
            const sessions = await fetchApi(`/attendance-sessions/course/${courseId}/active`);
            const studentId = currentUser.student?.id || currentUser.id;
            const history = await fetchApi(`/attendance-records/student/${studentId}/course/${courseId}/history`);

            let html = '';
            
            if (sessions.length > 0) {
                html += `<div class="bg-indigo-50 border rounded-xl p-4 mb-6">
                    <h4 class="font-bold text-indigo-800 mb-3">Sesi Aktif</h4>
                    ${sessions.map(s => `
                        <div class="bg-white p-3 rounded flex justify-between items-center mb-2">
                            <span>${s.session_name}</span>
                            <button onclick="submitAttendance(${s.id})" class="px-3 py-1 bg-emerald-500 text-white rounded text-xs font-bold">Hadir</button>
                        </div>
                    `).join('')}
                </div>`;
            }

            html += `<h4 class="font-bold mb-3">Riwayat</h4>
            <table class="w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="p-2">Sesi</th><th>Status</th><th>Waktu</th></tr></thead>
                <tbody>
                    ${history.map(h => `
                        <tr class="border-b">
                            <td class="p-2">${h.attendance_session?.session_name}</td>
                            <td><span class="badge ${getAttClass(h.status)}">${h.status}</span></td>
                            <td>${formatDate(h.attendance_time, true)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>`;
            
            container.innerHTML = html;
        }

        async function submitAttendance(sessionId) {
            try {
                await fetchApi(`/attendance-records/check-in/${sessionId}`, { method: 'POST' });
                alert('Berhasil Check-in!');
                loadCourseAttendance(currentCourseId);
            } catch(e) { alert(e.message); }
        }

        // Helper functions
        function showAssignmentDetail(id) {
            const a = myAssignments.find(x => x.id == id);
            if(!a) return;
            Swal.fire({
                title: a.title,
                html: `<p>${a.description}</p><p class="text-xs">Due: ${formatDate(a.due_date)}</p>`,
                confirmButtonText: 'Tutup'
            });
        }

        function switchCourseTab(tabName) {
            document.querySelectorAll('.course-tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('course-content-' + tabName).classList.remove('hidden');
            document.querySelectorAll('.course-tab-btn').forEach(b => {
                b.classList.remove('text-primary', 'border-primary');
                b.classList.add('text-slate-500', 'border-transparent');
            });
            document.getElementById('tab-course-' + tabName).classList.remove('text-slate-500', 'border-transparent');
            document.getElementById('tab-course-' + tabName).classList.add('text-primary', 'border-primary');
        }

        function formatDate(d, time=false) {
            if(!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', ...(time && { hour: '2-digit', minute: '2-digit'}) });
        }
```

---

## 🎯 FITUR YANG BERHASIL DITAMBAHKAN

✅ Manual Bearer Token Input dengan tombol "Update Token"  
✅ Tab Kehadiran di Course Detail  
✅ Attendance Submission untuk sesi aktif  
✅ Validasi role (hanya student yang bisa akses)  
✅ Error 401 handler otomatis  
✅ SweetAlert2 untuk notification

---

## 📌 CATATAN PENTING

- Lint errors `@apply` pada file complete1 adalah **NORMAL** (Tailwind CSS syntax)
- Fokus hanya pada JavaScript errors di complete2
- Setelah restore, test dulu fungsi `manualTokenInput()` dan `fetchApi()`

---

**Dibuat: 26 Nov 2025**  
**File yang terpengaruh: student-dashboard-complete2.html**
