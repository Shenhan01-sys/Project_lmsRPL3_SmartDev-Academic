// Submit assignment function - FIXED VERSION
async function submitAssignment(event, submitNow = true) {
    event.preventDefault();
    
    const assignmentId = document.getElementById('submission-assignment-id').value;
    const submissionId = document.getElementById('submission-id').value;
    const fileInput = document.getElementById('submission-file');
    
    if (!submissionId && !fileInput.files[0]) {
        await Swal.fire({ icon: 'warning', title: 'File Diperlukan', text: 'Silakan pilih file untuk diupload' });
        return;
    }
    
    if (fileInput.files[0] && fileInput.files[0].size > 10 * 1024 * 1024) {
        await Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal 10MB' });
        return;
    }
    
    try {
        document.getElementById('loading-overlay').style.display = 'flex';
        
        // Prepare FormData untuk upload file
        const formData = new FormData();
        formData.append('assignment_id', assignmentId);
        formData.append('submit_now', submitNow ? '1' : '0');
        
        // Tambahkan file jika ada
        if (fileInput.files[0]) {
            formData.append('file', fileInput.files[0]);
        }
        
        let response;
        if (submissionId) {
            // Update existing submission
            formData.append('_method', 'PUT');
            response = await fetchApi(`/submissions/${submissionId}`, { 
                method: 'POST', 
                body: formData 
            });
        } else {
            // Create new submission
            response = await fetchApi('/submissions', { 
                method: 'POST', 
                body: formData 
            });
        }
        
        closeModal('modal-submit-assignment');
        
        // Show success message
        const statusText = submitNow ? 'dikumpulkan' : 'disimpan sebagai draft';
        let message = `Tugas berhasil ${statusText}!`;
        
        // Check if late
        if (submitNow && response.submission?.is_late) {
            message += ` ⚠️ Terlambat ${response.submission.late_days} hari.`;
        }
        
        await Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: message, 
            timer: 3000, 
            showConfirmButton: false 
        });
        
        // Reload assignments
        await showCourseDetail(currentCourseId);
        
    } catch (error) {
        console.error('Error submitting assignment:', error);
        await Swal.fire({ 
            icon: 'error', 
            title: 'Gagal Submit', 
            text: error.message || 'Terjadi kesalahan saat mengumpulkan tugas' 
        });
    } finally {
        document.getElementById('loading-overlay').style.display = 'none';
    }
}
