<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - SmartDev LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #4285F4;
            --accent-green: #20C997;
            --light-gray: #F8F9FA;
            --gray-100: #E9ECEF;
            --text-primary: #212529;
            --text-secondary: #6C757D;
            --error: #DC3545;
            --primary-hover: #3367D6;
            --accent-hover: #1AA87D;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .tech-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        /* === FORM STYLES === */
        .form-label {
            @apply block text-sm font-semibold text-[--text-primary] mb-2;
        }

        .form-input, .form-textarea, .form-select {
            @apply w-full h-14 px-5 py-4 border-2 border-[--gray-100] rounded-xl text-base bg-white;
            @apply transition-all duration-300 ease-in-out;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .form-textarea {
            @apply h-32 resize-none;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            @apply border-[--primary-blue] outline-none transform scale-[1.02];
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
        }

        .form-input.error, .form-textarea.error, .form-select.error {
            @apply border-[--error];
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.15);
        }

        .form-helper {
            @apply text-xs text-[--text-secondary] mt-1;
        }

        /* === BUTTONS === */
        .btn {
            @apply py-3.5 px-8 rounded-lg font-semibold text-base border-none cursor-pointer;
            @apply transition-all duration-300 ease-in-out;
        }

        .btn-primary {
            @apply bg-[--primary-blue] text-white;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
        }
        .btn-primary:hover {
            @apply bg-[--primary-hover] -translate-y-0.5;
            box-shadow: 0 6px 16px rgba(66, 133, 244, 0.4);
        }

        .btn-accent {
            @apply bg-[--accent-green] text-white;
            box-shadow: 0 4px 12px rgba(32, 201, 151, 0.3);
        }
        .btn-accent:hover {
            @apply bg-[--accent-hover] -translate-y-0.5;
            box-shadow: 0 6px 16px rgba(32, 201, 151, 0.4);
        }

        .btn-secondary {
            @apply bg-[--light-gray] text-[--text-primary] border-2 border-[--gray-100];
        }
        .btn-secondary:hover {
            @apply bg-[--gray-100];
        }

        /* === ENHANCED RADIO BUTTON STYLES === */
        .radio-group {
            @apply flex gap-4;
        }
        
        .radio-card {
            @apply flex-1 p-6 border-2 border-[--gray-100] rounded-xl cursor-pointer transition-all duration-300;
            @apply flex flex-col items-center justify-center space-y-3 bg-white;
        }
        
        .radio-card:hover {
            @apply border-[--primary-blue] transform -translate-y-1;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
        }
        
        .radio-card.selected {
            @apply border-[--primary-blue] bg-gradient-to-br from-blue-50 to-indigo-50 transform -translate-y-1;
            box-shadow: 0 12px 30px rgba(66, 133, 244, 0.2);
        }
        
        .radio-card input[type="radio"] {
            @apply hidden;
        }
        
        .radio-icon {
            @apply text-3xl transition-all duration-300 mb-2;
        }
        
        .radio-card.selected .radio-icon {
            @apply text-[--primary-blue] transform scale-110;
        }
        
        .radio-card:not(.selected) .radio-icon {
            @apply text-[--text-secondary];
        }
        
        .radio-label {
            @apply font-semibold text-lg transition-all duration-300;
        }
        
        .radio-card.selected .radio-label {
            @apply text-[--primary-blue];
        }
        
        .radio-card:not(.selected) .radio-label {
            @apply text-[--text-primary];
        }

        /* === ENHANCED FILE UPLOAD STYLES === */
        .file-upload-zone {
            @apply border-3 border-dashed border-[--gray-100] rounded-2xl p-12 text-center transition-all duration-300 cursor-pointer;
            @apply bg-gradient-to-br from-[--light-gray] to-gray-50 hover:from-blue-50 hover:to-indigo-50;
            @apply hover:border-[--primary-blue] hover:shadow-lg;
        }
        
        .file-upload-zone.dragover {
            @apply border-[--primary-blue] bg-gradient-to-br from-blue-50 to-indigo-50 transform scale-[1.02];
            box-shadow: 0 15px 40px rgba(66, 133, 244, 0.2);
            animation: pulse-upload 1s ease-in-out;
        }
        
        .file-upload-zone.has-file {
            @apply border-[--accent-green] bg-gradient-to-br from-green-50 to-emerald-50;
        }
        
        .file-upload-zone.error {
            @apply border-[--error] bg-gradient-to-br from-red-50 to-pink-50;
        }
        
        @keyframes pulse-upload {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1.02); }
        }
        
        .upload-icon {
            @apply text-4xl mb-4 transition-all duration-300;
        }
        
        .file-upload-zone:not(.has-file):not(.error) .upload-icon {
            @apply text-[--primary-blue];
        }
        
        .file-upload-zone.has-file .upload-icon {
            @apply text-[--accent-green];
        }
        
        .file-upload-zone.error .upload-icon {
            @apply text-[--error];
        }
        
        .upload-text {
            @apply transition-all duration-300;
        }
        
        .upload-main-text {
            @apply font-bold text-xl mb-2;
        }
        
        .upload-sub-text {
            @apply text-[--text-secondary] text-sm;
        }
        
        .file-upload-zone:not(.has-file):not(.error) .upload-main-text {
            @apply text-[--text-primary];
        }
        
        .file-upload-zone.has-file .upload-main-text {
            @apply text-[--accent-green];
        }
        
        .file-upload-zone.error .upload-main-text {
            @apply text-[--error];
        }
        
        .file-preview {
            @apply mt-6 p-4 bg-white rounded-xl border border-[--gray-100] shadow-sm;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .file-info {
            @apply flex items-center justify-between;
        }
        
        .file-details {
            @apply flex items-center flex-1 min-w-0;
        }
        
        .file-icon {
            @apply text-2xl mr-3 flex-shrink-0;
        }
        
        .file-text {
            @apply flex-1 min-w-0;
        }
        
        .file-name {
            @apply font-semibold text-[--text-primary] truncate;
        }
        
        .file-size {
            @apply text-[--text-secondary] text-sm;
        }
        
        .file-actions {
            @apply flex items-center space-x-2;
        }
        
        .remove-file {
            @apply w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-[--error] 
                   hover:bg-red-100 transition-colors duration-200;
        }
        
        .preview-file {
            @apply w-8 h-8 flex items-center justify-center rounded-full bg-blue-50 text-[--primary-blue] 
                   hover:bg-blue-100 transition-colors duration-200;
        }

        /* Form Input Groups with Icons */
        .form-input-group {
            @apply relative;
        }
        
        .form-icon {
            @apply absolute left-4 top-1/2 transform -translate-y-1/2 text-[--text-secondary] transition-colors duration-300;
        }
        
        .form-input:focus + .form-icon,
        .form-textarea:focus + .form-icon {
            @apply text-[--primary-blue];
        }
        
        .form-input.with-icon {
            @apply pl-12;
        }
        
        .form-textarea.with-icon {
            @apply pl-12 pt-4;
        }

        /* Progress & Other Styles */
        .progress-step {
            @apply flex items-center justify-center w-10 h-10 rounded-full border-2 font-semibold transition-all duration-300;
        }

        .progress-step.active {
            @apply bg-[--primary-blue] border-[--primary-blue] text-white;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.4);
        }

        .progress-step.completed {
            @apply bg-[--accent-green] border-[--accent-green] text-white;
        }

        .progress-step.inactive {
            @apply bg-white border-[--gray-100] text-[--text-secondary];
        }

        .tech-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Password strength indicator */
        .password-strength {
            @apply mt-3 space-y-2;
        }
        
        .strength-bars {
            @apply flex space-x-2;
        }
        
        .strength-bar {
            @apply h-2 rounded-full flex-1 transition-all duration-500;
        }
        
        .strength-text {
            @apply text-xs font-medium transition-colors duration-300;
        }

        /* Field Error Styles */
        .field-error {
            @apply text-[--error] text-xs mt-1 flex items-center;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape w-64 h-64 -top-32 -left-32" style="animation-delay: 0s;"></div>
        <div class="shape w-48 h-48 -bottom-24 -right-24" style="animation-delay: 2s;"></div>
        <div class="shape w-32 h-32 top-1/2 left-1/4" style="animation-delay: 4s;"></div>
    </div>

    <div class="tech-card rounded-3xl w-full max-w-4xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[--primary-blue] to-[--primary-hover] p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center tech-pulse">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">SmartDev Academy</h1>
                        <p class="text-blue-100 opacity-90">Platform Belajar Teknologi Masa Depan</p>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Progress Indicator -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="progress-step @yield('step1-status', 'inactive')">
                                <i class="fas @yield('step1-icon', 'fa-user')"></i>
                            </div>
                            <span class="text-sm font-medium">Data Diri</span>
                        </div>
                        
                        <div class="w-8 h-0.5 bg-white bg-opacity-30"></div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="progress-step @yield('step2-status', 'inactive')">
                                <i class="fas @yield('step2-icon', 'fa-file-upload')"></i>
                            </div>
                            <span class="text-sm font-medium">Dokumen</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Progress -->
            <div class="md:hidden mt-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Step @yield('current-step') of 2</span>
                    <div class="flex space-x-2">
                        <div class="w-3 h-3 rounded-full @yield('step1-dot', 'bg-white bg-opacity-30')"></div>
                        <div class="w-3 h-3 rounded-full @yield('step2-dot', 'bg-white bg-opacity-30')"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="p-8">
            @yield('content')
        </div>
    </div>

    <script>
        // Enhanced Radio Card Selection
        function setupRadioCards() {
            const radioCards = document.querySelectorAll('.radio-card');
            radioCards.forEach(card => {
                // Set initial selected state
                const radioInput = card.querySelector('input[type="radio"]');
                if (radioInput && radioInput.checked) {
                    card.classList.add('selected');
                }
                
                card.addEventListener('click', function() {
                    const radioInput = this.querySelector('input[type="radio"]');
                    if (radioInput) {
                        radioInput.checked = true;
                        
                        // Remove selected class from all cards in same group
                        const groupName = radioInput.name;
                        document.querySelectorAll(`.radio-card input[name="${groupName}"]`).forEach(input => {
                            input.closest('.radio-card').classList.remove('selected');
                        });
                        
                        // Add selected class to clicked card
                        this.classList.add('selected');
                        
                        // Add subtle animation
                        this.style.transform = 'translateY(-2px) scale(1.02)';
                        setTimeout(() => {
                            this.style.transform = 'translateY(-1px) scale(1)';
                        }, 150);
                    }
                });
            });
        }

        // Enhanced File Upload with Better Drag & Drop
        function setupFileUpload() {
            const fileZones = document.querySelectorAll('.file-upload-zone');
            
            fileZones.forEach(zone => {
                const fileInput = zone.querySelector('input[type="file"]');
                const uploadContent = zone.querySelector('.upload-content');
                const uploadIcon = zone.querySelector('.upload-icon');
                const uploadMainText = zone.querySelector('.upload-main-text');
                const uploadSubText = zone.querySelector('.upload-sub-text');
                
                // Click to select file
                zone.addEventListener('click', (e) => {
                    if (!e.target.closest('.file-actions')) {
                        fileInput.click();
                    }
                });
                
                // Enhanced Drag & Drop events
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    zone.classList.add('dragover');
                    uploadIcon.className = 'fas fa-file-upload upload-icon';
                    uploadMainText.textContent = 'Lepaskan untuk upload';
                    uploadSubText.textContent = 'Seret file ke sini...';
                });
                
                zone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    if (!zone.contains(e.relatedTarget)) {
                        zone.classList.remove('dragover');
                        resetUploadText(zone, fileInput);
                    }
                });
                
                zone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    zone.classList.remove('dragover');
                    
                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        handleFileSelection(fileInput);
                    }
                });
                
                // File input change
                fileInput.addEventListener('change', () => {
                    handleFileSelection(fileInput);
                });
            });
        }

        function resetUploadText(zone, fileInput) {
            const uploadIcon = zone.querySelector('.upload-icon');
            const uploadMainText = zone.querySelector('.upload-main-text');
            const uploadSubText = zone.querySelector('.upload-sub-text');
            
            if (!zone.classList.contains('has-file') && !zone.classList.contains('error')) {
                const accept = fileInput.getAttribute('accept');
                uploadIcon.className = getUploadIcon(fileInput);
                uploadMainText.textContent = 'Drag & drop atau klik untuk upload';
                uploadSubText.textContent = getAcceptText(fileInput);
            }
        }

        function getUploadIcon(fileInput) {
            const accept = fileInput.getAttribute('accept');
            if (accept.includes('.pdf')) {
                return 'fas fa-file-pdf upload-icon';
            } else if (fileInput.name === 'foto_siswa') {
                return 'fas fa-portrait upload-icon';
            } else if (fileInput.name === 'bukti_pembayaran') {
                return 'fas fa-money-bill-wave upload-icon';
            } else {
                return 'fas fa-cloud-upload-alt upload-icon';
            }
        }

        function getAcceptText(fileInput) {
            const accept = fileInput.getAttribute('accept');
            if (accept.includes('image/*') && accept.includes('.pdf')) {
                return 'PNG, JPG, atau PDF (Maks 2MB)';
            } else if (accept.includes('image/*')) {
                return fileInput.name === 'foto_siswa' ? 'PNG atau JPG (Maks 1MB)' : 'PNG atau JPG (Maks 2MB)';
            }
            return 'PNG, JPG, atau PDF (Maks 2MB)';
        }

        function handleFileSelection(fileInput) {
            const zone = fileInput.closest('.file-upload-zone');
            const uploadIcon = zone.querySelector('.upload-icon');
            const uploadMainText = zone.querySelector('.upload-main-text');
            const uploadSubText = zone.querySelector('.upload-sub-text');
            
            // Reset error state
            zone.classList.remove('error');
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Validate file first
                if (!validateFile(fileInput)) {
                    return;
                }
                
                zone.classList.add('has-file');
                
                // Update upload text
                uploadIcon.className = 'fas fa-check-circle upload-icon';
                uploadMainText.textContent = 'File berhasil diupload';
                uploadSubText.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                
                // Create or update file preview
                createFilePreview(zone, file, fileInput);
                
            } else {
                // No file selected
                zone.classList.remove('has-file');
                resetUploadText(zone, fileInput);
                
                // Remove preview
                const existingPreview = zone.querySelector('.file-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
            }
        }

        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return 'fas fa-image text-[--primary-blue]';
            if (fileType === 'application/pdf') return 'fas fa-file-pdf text-[--error]';
            return 'fas fa-file text-[--text-secondary]';
        }

        function createFilePreview(zone, file, fileInput) {
            // Remove existing preview
            const existingPreview = zone.querySelector('.file-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileIcon = getFileIcon(file.type);
            
            const preview = document.createElement('div');
            preview.className = 'file-preview';
            preview.innerHTML = `
                <div class="file-info">
                    <div class="file-details">
                        <i class="${fileIcon} file-icon"></i>
                        <div class="file-text">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${fileSize} MB</div>
                        </div>
                    </div>
                    <div class="file-actions">
                        ${file.type.startsWith('image/') ? `
                            <button type="button" class="preview-file" onclick="previewImage(this, '${URL.createObjectURL(file)}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        ` : ''}
                        <button type="button" class="remove-file" onclick="removeFile(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            zone.appendChild(preview);
        }

        function previewImage(button, imageUrl) {
            // Create modal for image preview
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl p-6 max-w-2xl mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Preview Gambar</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <img src="${imageUrl}" alt="Preview" class="max-w-full max-h-96 rounded-lg">
                </div>
            `;
            document.body.appendChild(modal);
            
            // Close modal on background click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        function removeFile(button) {
            const filePreview = button.closest('.file-preview');
            const zone = filePreview.closest('.file-upload-zone');
            const fileInput = zone.querySelector('input[type="file"]');
            
            // Reset file input
            fileInput.value = '';
            
            // Remove preview
            filePreview.remove();
            
            // Reset zone state
            zone.classList.remove('has-file', 'error');
            
            // Reset upload text
            resetUploadText(zone, fileInput);
        }

        function validateFile(fileInput) {
            if (!fileInput.files.length) return true;
            
            const file = fileInput.files[0];
            const maxSize = getMaxFileSize(fileInput.name);
            const acceptTypes = fileInput.getAttribute('accept');
            
            // Check file size
            if (file.size > maxSize) {
                showFileError(fileInput, `File terlalu besar! Maksimal ${maxSize / 1024 / 1024}MB`);
                return false;
            }
            
            // Check file type
            if (acceptTypes && !validateFileType(file, acceptTypes)) {
                showFileError(fileInput, 'Format file tidak didukung');
                return false;
            }
            
            return true;
        }
        
        function getMaxFileSize(fieldName) {
            const sizes = {
                'foto_siswa': 1 * 1024 * 1024, // 1MB
                'ktp_orang_tua': 2 * 1024 * 1024, // 2MB
                'ijazah': 2 * 1024 * 1024, // 2MB
                'bukti_pembayaran': 2 * 1024 * 1024 // 2MB
            };
            return sizes[fieldName] || 2 * 1024 * 1024;
        }
        
        function validateFileType(file, acceptTypes) {
            if (acceptTypes.includes('image/*') && file.type.startsWith('image/')) {
                return true;
            }
            if (acceptTypes.includes('.pdf') && file.type === 'application/pdf') {
                return true;
            }
            return false;
        }
        
        function showFileError(fileInput, message) {
            const zone = fileInput.closest('.file-upload-zone');
            const uploadIcon = zone.querySelector('.upload-icon');
            const uploadMainText = zone.querySelector('.upload-main-text');
            const uploadSubText = zone.querySelector('.upload-sub-text');
            
            zone.classList.add('error');
            zone.classList.remove('has-file');
            
            uploadIcon.className = 'fas fa-exclamation-circle upload-icon';
            uploadMainText.textContent = 'Error upload file';
            uploadSubText.textContent = message;
            
            // Remove any existing preview
            const existingPreview = zone.querySelector('.file-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Reset file input
            fileInput.value = '';
        }

        // Form Validation
        function setupFormValidation(formId) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
        }
        
        function validateField(field) {
            // Remove existing error
            field.classList.remove('error');
            hideFieldError(field);
            
            // Check required fields
            if (field.hasAttribute('required') && !field.value.trim()) {
                showFieldError(field, 'Field ini wajib diisi');
                return false;
            }
            
            // Email validation
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    showFieldError(field, 'Format email tidak valid');
                    return false;
                }
            }
            
            return true;
        }
        
        function showFieldError(field, message) {
            field.classList.add('error');
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-[--error] text-xs mt-1 flex items-center';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message}`;
            field.parentNode.appendChild(errorDiv);
        }
        
        function hideFieldError(field) {
            const errorDiv = field.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        // Enhanced Text Input with Icons
        function setupFormInputs() {
            // Add focus effects to all form inputs
            const inputs = document.querySelectorAll('.form-input, .form-textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        }

        // Notification System
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white font-semibold shadow-lg transform transition-all duration-300 z-50 ${
                type === 'success' ? 'bg-[--accent-green]' :
                type === 'error' ? 'bg-[--error]' :
                type === 'warning' ? 'bg-yellow-500' : 'bg-[--primary-blue]'
            }`;
            
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Remove after delay
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setupRadioCards();
            setupFileUpload();
            setupFormInputs();
            
            // Initialize form validation
            const form = document.querySelector('form');
            if (form && form.id) {
                setupFormValidation(form.id);
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>