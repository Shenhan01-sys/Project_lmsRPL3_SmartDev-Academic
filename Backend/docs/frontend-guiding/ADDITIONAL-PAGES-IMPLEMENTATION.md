# 📄 Additional Pages Implementation Guide

**Ngrok Base URL:** `https://loraine-seminiferous-snappily.ngrok-free.dev`

Panduan implementasi untuk halaman tambahan: Attendance, Announcements, Notifications, dan Certificates.

---

## 📅 Attendance Page

**File:** `resources/views/parent/attendance.blade.php`

### Full Implementation

```blade
@extends('layouts.parent')

@section('title', 'Kehadiran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Kehadiran</h2>
        <p class="text-muted mb-0">Monitor kehadiran anak di setiap course</p>
    </div>
</div>

{{-- Student Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Pilih Anak</label>
                <select class="form-select" id="studentFilter" onchange="loadAttendance()">
                    <option value="">Loading...</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Pilih Course (Opsional)</label>
                <select class="form-select" id="courseFilter" onchange="filterByCourse()">
                    <option value="">Semua Course</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Overall Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Sessions</h6>
                <h2 class="fw-bold text-primary" id="totalSessions">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Present</h6>
                <h2 class="fw-bold text-success" id="totalPresent">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Absent</h6>
                <h2 class="fw-bold text-danger" id="totalAbsent">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Avg Attendance</h6>
                <h2 class="fw-bold text-info" id="avgPercentage">-</h2>
            </div>
        </div>
    </div>
</div>

{{-- Attendance by Course --}}
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Kehadiran Per Course</h5>
    </div>
    <div class="card-body">
        <div id="loadingIndicator" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data kehadiran...</p>
        </div>

        <div id="attendanceList" style="display:none"></div>

        <div id="emptyState" style="display:none" class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <p class="text-muted mt-3">Belum ada data kehadiran</p>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let students = [];
let attendanceData = [];
let filteredData = [];

async function initialize() {
    try {
        // Get user and students
        const user = await ParentAPI.getCurrentUser();
        const parentId = user.parent ? user.parent.id : null;

        if (!parentId) throw new Error('Parent data not found');

        students = await ParentAPI.getStudents(parentId);

        // Populate student filter
        const studentFilter = document.getElementById('studentFilter');
        studentFilter.innerHTML = students.map(s => 
            `<option value="${s.id}">${s.full_name}</option>`
        ).join('');

        // Load attendance for first student
        if (students.length > 0) {
            await loadAttendance();
        }

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data: ' + error.message);
    }
}

async function loadAttendance() {
    const studentId = document.getElementById('studentFilter').value;
    if (!studentId) return;

    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('attendanceList').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    try {
        const data = await ParentAPI.getAttendanceSummary(studentId);
        attendanceData = data.summary || [];
        filteredData = [...attendanceData];

        // Populate course filter
        const courseFilter = document.getElementById('courseFilter');
        courseFilter.innerHTML = '<option value="">Semua Course</option>' +
            attendanceData.map(a => 
                `<option value="${a.course_id}">${a.course_title}</option>`
            ).join('');

        renderAttendance();
        updateStatistics();

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data kehadiran: ' + error.message);
    }
}

function filterByCourse() {
    const courseId = document.getElementById('courseFilter').value;
    
    if (courseId === '') {
        filteredData = [...attendanceData];
    } else {
        filteredData = attendanceData.filter(a => a.course_id == courseId);
    }
    
    renderAttendance();
    updateStatistics();
}

function renderAttendance() {
    const container = document.getElementById('attendanceList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredData.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    container.style.display = 'block';

    container.innerHTML = filteredData.map(item => {
        const percentage = parseFloat(item.percentage);
        const percentageColor = percentage >= 80 ? 'success' : percentage >= 60 ? 'warning' : 'danger';

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">${item.course_title}</h5>
                            <small class="text-muted">Total: ${item.total_sessions} sessions</small>
                        </div>
                        <span class="badge bg-${percentageColor} fs-6">${percentage}%</span>
                    </div>

                    <div class="row text-center">
                        <div class="col">
                            <div class="border rounded p-3">
                                <i class="bi bi-check-circle fs-3 text-success"></i>
                                <h4 class="mb-0 mt-2">${item.present}</h4>
                                <small class="text-muted">Present</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-3">
                                <i class="bi bi-x-circle fs-3 text-danger"></i>
                                <h4 class="mb-0 mt-2">${item.absent}</h4>
                                <small class="text-muted">Absent</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-3">
                                <i class="bi bi-heart-pulse fs-3 text-warning"></i>
                                <h4 class="mb-0 mt-2">${item.sick || 0}</h4>
                                <small class="text-muted">Sick</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-3">
                                <i class="bi bi-file-text fs-3 text-info"></i>
                                <h4 class="mb-0 mt-2">${item.permission || 0}</h4>
                                <small class="text-muted">Permission</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function updateStatistics() {
    if (filteredData.length === 0) {
        document.getElementById('totalSessions').textContent = '0';
        document.getElementById('totalPresent').textContent = '0';
        document.getElementById('totalAbsent').textContent = '0';
        document.getElementById('avgPercentage').textContent = '0%';
        return;
    }

    let totalSessions = 0;
    let totalPresent = 0;
    let totalAbsent = 0;
    let sumPercentage = 0;

    filteredData.forEach(item => {
        totalSessions += item.total_sessions;
        totalPresent += item.present;
        totalAbsent += item.absent;
        sumPercentage += parseFloat(item.percentage);
    });

    const avgPercentage = (sumPercentage / filteredData.length).toFixed(1);

    document.getElementById('totalSessions').textContent = totalSessions;
    document.getElementById('totalPresent').textContent = totalPresent;
    document.getElementById('totalAbsent').textContent = totalAbsent;
    document.getElementById('avgPercentage').textContent = avgPercentage + '%';
}

function showError(message) {
    document.getElementById('loadingIndicator').innerHTML = 
        `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        </div>`;
}

document.addEventListener('DOMContentLoaded', initialize);
</script>
@endsection
```

---

## 📢 Announcements Page

**File:** `resources/views/parent/announcements.blade.php`

### Full Implementation

```blade
@extends('layouts.parent')

@section('title', 'Pengumuman')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Pengumuman</h2>
        <p class="text-muted mb-0">Lihat pengumuman terbaru dari sekolah</p>
    </div>
</div>

{{-- Filter Section --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Filter by Scope</label>
                <select class="form-select" id="scopeFilter" onchange="loadAnnouncements()">
                    <option value="all">Semua Pengumuman</option>
                    <option value="global">Global</option>
                    <option value="course">Per Course</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" 
                       placeholder="🔍 Cari pengumuman..." onkeyup="filterAnnouncements()">
            </div>
        </div>
    </div>
</div>

{{-- Announcements List --}}
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Memuat pengumuman...</p>
</div>

<div id="announcementsList"></div>

<div id="emptyState" style="display:none" class="card text-center py-5">
    <div class="card-body">
        <i class="bi bi-megaphone fs-1 text-muted"></i>
        <p class="text-muted mt-3">Belum ada pengumuman</p>
    </div>
</div>

{{-- Pagination --}}
<nav id="pagination" style="display:none">
    <ul class="pagination justify-content-center"></ul>
</nav>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let allAnnouncements = [];
let filteredAnnouncements = [];
let currentPage = 1;

async function loadAnnouncements(page = 1) {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('announcementsList').innerHTML = '';
    document.getElementById('emptyState').style.display = 'none';

    try {
        const data = await ParentAPI.getAnnouncements(page);
        
        allAnnouncements = data.data || [];
        filteredAnnouncements = [...allAnnouncements];
        currentPage = page;

        filterAnnouncements();

        // Render pagination
        if (data.meta) {
            renderPagination(data.meta);
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('loadingIndicator').innerHTML = 
            `<div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Gagal memuat pengumuman: ${error.message}
            </div>`;
    }
}

function filterAnnouncements() {
    const scopeFilter = document.getElementById('scopeFilter').value;
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();

    filteredAnnouncements = allAnnouncements.filter(a => {
        const matchScope = scopeFilter === 'all' || a.scope === scopeFilter;
        const matchSearch = searchQuery === '' || 
            a.title.toLowerCase().includes(searchQuery) ||
            a.content.toLowerCase().includes(searchQuery);
        
        return matchScope && matchSearch;
    });

    renderAnnouncements();
}

function renderAnnouncements() {
    const container = document.getElementById('announcementsList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredAnnouncements.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';

    container.innerHTML = filteredAnnouncements.map(announcement => {
        const date = new Date(announcement.published_at);
        const formattedDate = date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const scopeBadge = announcement.scope === 'global' 
            ? '<span class="badge bg-primary">Global</span>'
            : '<span class="badge bg-info">Course</span>';

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2">${announcement.title}</h5>
                            ${scopeBadge}
                        </div>
                        <div class="text-end">
                            <small class="text-muted">
                                <i class="bi bi-calendar3"></i>
                                ${formattedDate}
                            </small>
                        </div>
                    </div>

                    <p class="mb-3">${announcement.content}</p>

                    ${announcement.course ? `
                        <div class="border-top pt-3">
                            <small class="text-muted">
                                <i class="bi bi-book"></i>
                                Course: ${announcement.course.title}
                            </small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function renderPagination(meta) {
    if (meta.last_page <= 1) {
        document.getElementById('pagination').style.display = 'none';
        return;
    }

    const pagination = document.getElementById('pagination');
    pagination.style.display = 'block';

    let html = '';
    for (let i = 1; i <= meta.last_page; i++) {
        html += `
            <li class="page-item ${i === meta.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadAnnouncements(${i}); return false;">
                    ${i}
                </a>
            </li>
        `;
    }

    pagination.querySelector('.pagination').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => loadAnnouncements(1));
</script>
@endsection
```

---

## 🔔 Notifications Page

**File:** `resources/views/parent/notifications.blade.php`

### Full Implementation

```blade
@extends('layouts.parent')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Notifikasi</h2>
        <p class="text-muted mb-0">Lihat semua notifikasi Anda</p>
    </div>
    <button class="btn btn-primary" onclick="markAllAsRead()">
        <i class="bi bi-check-all me-1"></i> Tandai Semua Dibaca
    </button>
</div>

{{-- Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Notifikasi</h6>
                <h2 class="fw-bold text-primary" id="totalNotifs">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Belum Dibaca</h6>
                <h2 class="fw-bold text-danger" id="unreadNotifs">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Sudah Dibaca</h6>
                <h2 class="fw-bold text-success" id="readNotifs">-</h2>
            </div>
        </div>
    </div>
</div>

{{-- Filter Tabs --}}
<ul class="nav nav-tabs mb-3" id="notifTabs">
    <li class="nav-item">
        <button class="nav-link active" onclick="filterNotifications('all')">
            Semua
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="filterNotifications('unread')">
            Belum Dibaca
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="filterNotifications('read')">
            Sudah Dibaca
        </button>
    </li>
</ul>

{{-- Notifications List --}}
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Memuat notifikasi...</p>
</div>

<div id="notificationsList"></div>

<div id="emptyState" style="display:none" class="card text-center py-5">
    <div class="card-body">
        <i class="bi bi-bell-slash fs-1 text-muted"></i>
        <p class="text-muted mt-3">Tidak ada notifikasi</p>
    </div>
</div>

{{-- Pagination --}}
<nav id="pagination" style="display:none">
    <ul class="pagination justify-content-center"></ul>
</nav>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let allNotifications = [];
let filteredNotifications = [];
let currentFilter = 'all';

async function loadNotifications(page = 1) {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('notificationsList').innerHTML = '';
    document.getElementById('emptyState').style.display = 'none';

    try {
        const data = await ParentAPI.getNotifications(page);
        
        allNotifications = data.data || [];
        filterNotifications(currentFilter);

        // Update statistics
        updateStatistics();

        // Render pagination
        if (data.meta) {
            renderPagination(data.meta);
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('loadingIndicator').innerHTML = 
            `<div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Gagal memuat notifikasi: ${error.message}
            </div>`;
    }
}

function filterNotifications(filter) {
    currentFilter = filter;

    // Update active tab
    document.querySelectorAll('#notifTabs .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    event?.target?.classList.add('active');

    // Filter notifications
    if (filter === 'all') {
        filteredNotifications = [...allNotifications];
    } else if (filter === 'unread') {
        filteredNotifications = allNotifications.filter(n => !n.is_read);
    } else if (filter === 'read') {
        filteredNotifications = allNotifications.filter(n => n.is_read);
    }

    renderNotifications();
}

function renderNotifications() {
    const container = document.getElementById('notificationsList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (filteredNotifications.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';

    container.innerHTML = filteredNotifications.map(notif => {
        const date = new Date(notif.created_at);
        const timeAgo = getTimeAgo(date);

        return `
            <div class="card mb-2 ${!notif.is_read ? 'border-primary bg-light' : ''}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0 me-2">${notif.title}</h6>
                                ${!notif.is_read ? '<span class="badge bg-danger">New</span>' : ''}
                            </div>
                            <p class="mb-2">${notif.message}</p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> ${timeAgo}
                            </small>
                        </div>
                        <div class="ms-3">
                            ${!notif.is_read ? `
                                <button class="btn btn-sm btn-primary" 
                                        onclick="markAsRead(${notif.id})">
                                    <i class="bi bi-check2"></i> Tandai Dibaca
                                </button>
                            ` : `
                                <span class="text-success">
                                    <i class="bi bi-check-circle"></i> Dibaca
                                </span>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function updateStatistics() {
    const total = allNotifications.length;
    const unread = allNotifications.filter(n => !n.is_read).length;
    const read = total - unread;

    document.getElementById('totalNotifs').textContent = total;
    document.getElementById('unreadNotifs').textContent = unread;
    document.getElementById('readNotifs').textContent = read;
}

async function markAsRead(notificationId) {
    try {
        await ParentAPI.markNotificationRead(notificationId);
        
        // Update local data
        const notification = allNotifications.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
        }

        filterNotifications(currentFilter);
        updateStatistics();

        // Update badge in sidebar
        if (typeof checkNotifications === 'function') {
            checkNotifications();
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Gagal menandai sebagai dibaca');
    }
}

async function markAllAsRead() {
    if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) {
        return;
    }

    try {
        await ParentAPI.markAllNotificationsRead();
        
        // Update local data
        allNotifications.forEach(n => n.is_read = true);

        filterNotifications(currentFilter);
        updateStatistics();

        // Update badge in sidebar
        if (typeof checkNotifications === 'function') {
            checkNotifications();
        }

        alert('Semua notifikasi telah ditandai sebagai dibaca');

    } catch (error) {
        console.error('Error:', error);
        alert('Gagal menandai semua notifikasi');
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);
    const diffDay = Math.floor(diffHour / 24);

    if (diffSec < 60) return 'Baru saja';
    if (diffMin < 60) return `${diffMin} menit yang lalu`;
    if (diffHour < 24) return `${diffHour} jam yang lalu`;
    if (diffDay < 7) return `${diffDay} hari yang lalu`;
    
    return date.toLocaleDateString('id-ID');
}

function renderPagination(meta) {
    if (meta.last_page <= 1) {
        document.getElementById('pagination').style.display = 'none';
        return;
    }

    const pagination = document.getElementById('pagination');
    pagination.style.display = 'block';

    let html = '';
    for (let i = 1; i <= meta.last_page; i++) {
        html += `
            <li class="page-item ${i === meta.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadNotifications(${i}); return false;">
                    ${i}
                </a>
            </li>
        `;
    }

    pagination.querySelector('.pagination').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => loadNotifications(1));
</script>
@endsection
```

---

## 🎓 Certificates Page

**File:** `resources/views/parent/certificates.blade.php`

### Full Implementation

```blade
@extends('layouts.parent')

@section('title', 'Sertifikat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Sertifikat</h2>
        <p class="text-muted mb-0">Lihat dan download sertifikat anak</p>
    </div>
</div>

{{-- Student Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Pilih Anak</label>
                <select class="form-select" id="studentFilter" onchange="loadCertificates()">
                    <option value="">Loading...</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Verify Certificate</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="verifyCode" 
                           placeholder="Masukkan kode sertifikat">
                    <button class="btn btn-outline-primary" onclick="verifyCertificate()">
                        <i class="bi bi-shield-check"></i> Verify
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Sertifikat</h6>
                <h2 class="fw-bold text-primary" id="totalCerts">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Issued</h6>
                <h2 class="fw-bold text-success" id="issuedCerts">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Pending</h6>
                <h2 class="fw-bold text-warning" id="pendingCerts">-</h2>
            </div>
        </div>
    </div>
</div>

{{-- Certificates Grid --}}
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Memuat sertifikat...</p>
</div>

<div id="certificatesList" class="row g-4"></div>

<div id="emptyState" style="display:none" class="card text-center py-5">
    <div class="card-body">
        <i class="bi bi-award fs-1 text-muted"></i>
        <p class="text-muted mt-3">Belum ada sertifikat</p>
    </div>
</div>

{{-- Verify Modal --}}
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Certificate Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="verifyResult"></div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="{{ asset('js/parent/api.js') }}"></script>
<script>
let students = [];
let certificates = [];

async function initialize() {
    try {
        // Get user and students
        const user = await ParentAPI.getCurrentUser();
        const parentId = user.parent ? user.parent.id : null;

        if (!parentId) throw new Error('Parent data not found');

        students = await ParentAPI.getStudents(parentId);

        // Populate student filter
        const studentFilter = document.getElementById('studentFilter');
        studentFilter.innerHTML = students.map(s => 
            `<option value="${s.id}">${s.full_name}</option>`
        ).join('');

        // Load certificates for first student
        if (students.length > 0) {
            await loadCertificates();
        }

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data: ' + error.message);
    }
}

async function loadCertificates() {
    const studentId = document.getElementById('studentFilter').value;
    if (!studentId) return;

    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('certificatesList').innerHTML = '';
    document.getElementById('emptyState').style.display = 'none';

    try {
        const data = await ParentAPI.getCertificates(studentId);
        certificates = data.data || data;

        renderCertificates();
        updateStatistics();

    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat sertifikat: ' + error.message);
    }
}

function renderCertificates() {
    const container = document.getElementById('certificatesList');
    const loading = document.getElementById('loadingIndicator');
    const emptyState = document.getElementById('emptyState');

    loading.style.display = 'none';

    if (certificates.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';

    container.innerHTML = certificates.map(cert => {
        const date = new Date(cert.issued_at);
        const formattedDate = date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const statusBadge = cert.status === 'issued'
            ? '<span class="badge bg-success">Issued</span>'
            : cert.status === 'pending'
            ? '<span class="badge bg-warning">Pending</span>'
            : '<span class="badge bg-danger">Revoked</span>';

        return `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-flex">
                                <i class="bi bi-award fs-1 text-primary"></i>
                            </div>
                        </div>

                        <h5 class="text-center mb-3">${cert.course?.title || 'Certificate'}</h5>

                        <div class="mb-3">
                            ${statusBadge}
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Certificate Code:</small><br>
                            <code>${cert.certificate_code}</code>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Issued Date:</small><br>
                            <strong>${formattedDate}</strong>
                        </div>

                        ${cert.status === 'issued' ? `
                            <div class="d-grid gap-2">
                                <a href="${cert.file_path}" target="_blank" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="shareCertificate('${cert.certificate_code}')">
                                    <i class="bi bi-share"></i> Share
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function updateStatistics() {
    const total = certificates.length;
    const issued = certificates.filter(c => c.status === 'issued').length;
    const pending = certificates.filter(c => c.status === 'pending').length;

    document.getElementById('totalCerts').textContent = total;
    document.getElementById('issuedCerts').textContent = issued;
    document.getElementById('pendingCerts').textContent = pending;
}

async function verifyCertificate() {
    const code = document.getElementById('verifyCode').value.trim();
    
    if (!code) {
        alert('Masukkan kode sertifikat');
        return;
    }

    try {
        const data = await ParentAPI.verifyCertificate(code);
        
        const resultHtml = `
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle"></i> Certificate Valid</h5>
                <hr>
                <p><strong>Student:</strong> ${data.student?.full_name || 'N/A'}</p>
                <p><strong>Course:</strong> ${data.course?.title || 'N/A'}</p>
                <p><strong>Issued:</strong> ${new Date(data.issued_at).toLocaleDateString('id-ID')}</p>
                <p><strong>Status:</strong> <span class="badge bg-success">${data.status}</span></p>
            </div>
        `;

        document.getElementById('verifyResult').innerHTML = resultHtml;
        new bootstrap.Modal(document.getElementById('verifyModal')).show();

    } catch (error) {
        const resultHtml = `
            <div class="alert alert-danger">
                <h5><i class="bi bi-x-circle"></i> Certificate Invalid</h5>
                <p>${error.message}</p>
            </div>
        `;

        document.getElementById('verifyResult').innerHTML = resultHtml;
        new bootstrap.Modal(document.getElementById('verifyModal')).show();
    }
}

function shareCertificate(code) {
    const url = `${window.location.origin}/certificates/verify/${code}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Certificate Verification',
            text: 'Verify my certificate',
            url: url
        }).catch(err => console.log('Share failed:', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copied to clipboard!');
        });
    }
}

function showError(message) {
    document.getElementById('loadingIndicator').innerHTML = 
        `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        </div>`;
}

document.addEventListener('DOMContentLoaded', initialize);
</script>
@endsection
```

---

## 🔗 API Helper Methods (Missing from Previous)

Add these methods to `public/js/parent/api.js` if not already present:

```javascript
// Add to ParentAPI object:

/**
 * Mark all notifications as read
 */
async markAllNotificationsRead() {
    const res = await fetch(`${API_BASE}/notifications/read-all`, {
        method: 'POST',
        headers: getHeaders()
    });
    return handleResponse(res);
}
```

---

## ✅ Checklist Implementation

- [x] **Attendance Page**: Summary per course, statistics, filtering
- [x] **Announcements Page**: List with pagination, filtering, search
- [x] **Notifications Page**: Real-time notifications, mark as read, filtering
- [x] **Certificates Page**: View, download, verify certificates

---

## 📝 Notes

1. All pages menggunakan **ngrok base URL** dengan header `ngrok-skip-browser-warning: true`
2. Semua pages sudah include **loading states**, **error handling**, dan **empty states**
3. UI konsisten menggunakan **Bootstrap 5.3** components
4. Pagination implemented where needed
5. Real-time features untuk notifications (auto-refresh di layout)
6. Certificate verification bisa digunakan publicly (tanpa auth)

---

**Last Updated:** 2024  
**Ngrok URL:** https://loraine-seminiferous-snappily.ngrok-free.dev