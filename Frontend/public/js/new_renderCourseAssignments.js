async function renderCourseAssignments(list) {
    const container = document.getElementById("course-content-assignments");
    
    if (!list || list.length === 0) {
        container.innerHTML = '<p class="text-center py-4 text-slate-400">Tidak ada tugas.</p>';
        return;
    }

    await loadMySubmissions();
    
    const html = list.map(assignment => {
        const submission = mySubmissions.find(s => s.assignment_id === assignment.id);
        
        let statusBadge = '';
        let actionButton = '';
        
        if (submission) {
            const statusColors = { 'draft': 'bg-gray-200 text-gray-800', 'submitted': 'bg-blue-200 text-blue-800', 'graded': 'bg-green-200 text-green-800', 'returned': 'bg-yellow-200 text-yellow-800' };
            const statusLabels = { 'draft': 'Draft', 'submitted': 'Dikumpulkan', 'graded': 'Dinilai', 'returned': 'Dikembalikan' };
            
            statusBadge = `<span class="text-xs px-2 py-1 rounded font-bold ${statusColors[submission.status]}">${statusLabels[submission.status]}</span>`;
            
            if (submission.is_late) {
                statusBadge += ` <span class="text-xs px-2 py-1 rounded font-bold bg-red-100 text-red-700">Terlambat ${submission.late_days} hari</span>`;
            }
            
            if (submission.status === 'graded' && submission.grade !== null) {
                statusBadge += ` <span class="text-xs px-2 py-1 rounded font-bold bg-emerald-100 text-emerald-700">Nilai: ${submission.grade}</span>`;
            }
            
            if (submission.status === 'draft' || submission.status === 'returned') {
                actionButton = `<button onclick="openSubmitModal(${assignment.id}, ${submission.id})" class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700"><i class="bi bi-pencil"></i> Edit & Submit</button>`;
            } else if (submission.status === 'graded' && submission.feedback) {
                actionButton = `<button onclick="viewSubmissionDetail(${submission.id})" class="px-3 py-1 bg-green-600 text-white rounded text-xs font-bold hover:bg-green-700"><i class="bi bi-eye"></i> Lihat Detail</button>`;
            } else {
                actionButton = `<span class="text-xs text-slate-500">Menunggu penilaian...</span>`;
            }
        } else {
            statusBadge = '<span class="text-xs px-2 py-1 rounded font-bold bg-red-100 text-red-700">Belum Dikumpulkan</span>';
            actionButton = `<button onclick="openSubmitModal(${assignment.id})" class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700"><i class="bi bi-upload"></i> Submit</button>`;
        }
        
        const dueDate = new Date(assignment.due_date);
        const now = new Date();
        const isOverdue = dueDate < now && (!submission || submission.status === 'draft');
        const dueDateColor = isOverdue ? 'text-red-600 font-bold' : 'text-slate-600';
        
        return `<div class="dashboard-card p-4 mb-3"><div class="flex justify-between items-start mb-3"><div class="flex-1"><h4 class="font-bold text-slate-800 mb-1">${assignment.title}</h4><p class="text-xs ${dueDateColor} mb-2"><i class="bi bi-clock"></i> Due: ${formatDate(assignment.due_date)}${isOverdue ? '<span class="ml-2 text-red-600">⚠️ OVERDUE</span>' : ''}</p><p class="text-sm text-slate-600 mb-2">${assignment.description || 'Tidak ada deskripsi'}</p></div></div><div class="flex justify-between items-center pt-3 border-t border-slate-100"><div class="flex gap-2 items-center">${statusBadge}</div>${actionButton}</div></div>`;
    }).join('');
    
    container.innerHTML = html;
}
