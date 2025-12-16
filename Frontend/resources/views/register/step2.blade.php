@extends('register.registration')

@section('title', 'Upload Dokumen - Pendaftaran Siswa')
@section('current-step', '2')
@section('step1-status', 'completed')
@section('step2-status', 'active')
@section('step1-icon', 'fa-check')
@section('step2-icon', 'fa-file-upload')
@section('step1-dot', 'bg-white')
@section('step2-dot', 'bg-white')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-r from-[--accent-green] to-[--primary-blue] rounded-full flex items-center justify-center">
            <i class="fas fa-cloud-upload-alt text-3xl text-white"></i>
        </div>
        <h2 class="text-3xl font-bold text-[--text-primary] mb-3">Upload Dokumen Pendaftaran</h2>
        <p class="text-[--text-secondary] text-lg">Unggah dokumen-dokumen yang diperlukan untuk proses seleksi</p>
    </div>

    <!-- Success/Error Messages -->
    <div id="alertContainer"></div>

    <form id="step2Form" class="space-y-8">
        @csrf

        <!-- Upload Section -->
        <section class="bg-gradient-to-br from-[--light-gray] to-white rounded-2xl p-6 border border-[--gray-100]">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-[--primary-blue] rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-upload text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[--text-primary]">Dokumen Pendaftaran</h3>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-semibold mb-1">Catatan Penting:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Semua dokumen bersifat <strong>opsional</strong>, bisa diupload nanti</li>
                            <li>Format yang diterima: PDF, JPG, JPEG, PNG</li>
                            <li>Ukuran maksimal per file: 2MB</li>
                            <li>Pastikan dokumen terlihat jelas dan mudah dibaca</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- KTP Orang Tua -->
                <div>
                    <label class="form-label">
                        <i class="fas fa-id-card text-[--primary-blue] mr-2"></i>
                        KTP Orang Tua
                    </label>
                    <div class="file-upload-zone" id="ktp_orang_tua-zone">
                        <input type="file" name="ktp_orang_tua" id="ktp_orang_tua" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <div class="upload-content">
                            <i class="fas fa-id-card upload-icon text-4xl text-[--text-secondary] mb-3"></i>
                            <div class="upload-text">
                                <p class="upload-main-text font-semibold text-[--text-primary]">Drag & drop atau klik untuk upload</p>
                                <p class="upload-sub-text text-sm text-[--text-secondary]">PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                            </div>
                        </div>
                        <div class="file-preview hidden">
                            <i class="fas fa-file-alt text-3xl text-[--accent-green] mb-2"></i>
                            <p class="file-name font-semibold text-[--text-primary]"></p>
                            <p class="file-size text-sm text-[--text-secondary]"></p>
                            <button type="button" class="remove-file mt-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times-circle mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                    <p class="form-helper mt-2"><i class="fas fa-info-circle text-[--primary-blue] mr-1"></i>KTP Orang Tua yang masih berlaku</p>
                </div>

                <!-- Ijazah -->
                <div>
                    <label class="form-label">
                        <i class="fas fa-graduation-cap text-[--primary-blue] mr-2"></i>
                        Ijazah Terakhir
                    </label>
                    <div class="file-upload-zone" id="ijazah-zone">
                        <input type="file" name="ijazah" id="ijazah" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <div class="upload-content">
                            <i class="fas fa-file-pdf upload-icon text-4xl text-[--text-secondary] mb-3"></i>
                            <div class="upload-text">
                                <p class="upload-main-text font-semibold text-[--text-primary]">Drag & drop atau klik untuk upload</p>
                                <p class="upload-sub-text text-sm text-[--text-secondary]">PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                            </div>
                        </div>
                        <div class="file-preview hidden">
                            <i class="fas fa-file-alt text-3xl text-[--accent-green] mb-2"></i>
                            <p class="file-name font-semibold text-[--text-primary]"></p>
                            <p class="file-size text-sm text-[--text-secondary]"></p>
                            <button type="button" class="remove-file mt-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times-circle mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                    <p class="form-helper mt-2"><i class="fas fa-info-circle text-[--primary-blue] mr-1"></i>Ijazah SMP/MTs atau sederajat</p>
                </div>

                <!-- Foto Siswa -->
                <div>
                    <label class="form-label">
                        <i class="fas fa-camera text-[--primary-blue] mr-2"></i>
                        Foto Siswa (3x4)
                    </label>
                    <div class="file-upload-zone" id="foto_siswa-zone">
                        <input type="file" name="foto_siswa" id="foto_siswa" accept=".jpg,.jpeg,.png" class="hidden">
                        <div class="upload-content">
                            <i class="fas fa-portrait upload-icon text-4xl text-[--text-secondary] mb-3"></i>
                            <div class="upload-text">
                                <p class="upload-main-text font-semibold text-[--text-primary]">Drag & drop atau klik untuk upload</p>
                                <p class="upload-sub-text text-sm text-[--text-secondary]">JPG, JPEG, PNG (Maks 2MB)</p>
                            </div>
                        </div>
                        <div class="file-preview hidden">
                            <i class="fas fa-file-alt text-3xl text-[--accent-green] mb-2"></i>
                            <p class="file-name font-semibold text-[--text-primary]"></p>
                            <p class="file-size text-sm text-[--text-secondary]"></p>
                            <button type="button" class="remove-file mt-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times-circle mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                    <p class="form-helper mt-2"><i class="fas fa-info-circle text-[--primary-blue] mr-1"></i>Foto formal ukuran 3x4 dengan latar belakang merah/biru</p>
                </div>

                <!-- Bukti Pembayaran -->
                <div>
                    <label class="form-label">
                        <i class="fas fa-receipt text-[--primary-blue] mr-2"></i>
                        Bukti Pembayaran Pendaftaran
                    </label>
                    <div class="file-upload-zone" id="bukti_pembayaran-zone">
                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <div class="upload-content">
                            <i class="fas fa-money-bill-wave upload-icon text-4xl text-[--text-secondary] mb-3"></i>
                            <div class="upload-text">
                                <p class="upload-main-text font-semibold text-[--text-primary]">Drag & drop atau klik untuk upload</p>
                                <p class="upload-sub-text text-sm text-[--text-secondary]">PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                            </div>
                        </div>
                        <div class="file-preview hidden">
                            <i class="fas fa-file-alt text-3xl text-[--accent-green] mb-2"></i>
                            <p class="file-name font-semibold text-[--text-primary]"></p>
                            <p class="file-size text-sm text-[--text-secondary]"></p>
                            <button type="button" class="remove-file mt-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times-circle mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                    <p class="form-helper mt-2"><i class="fas fa-info-circle text-[--primary-blue] mr-1"></i>Bukti transfer biaya pendaftaran</p>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register.step1') }}" class="btn btn-secondary flex items-center justify-center px-8 py-4">
                <i class="fas fa-arrow-left mr-3"></i>
                Kembali ke Data Diri
            </a>
            <button type="submit" id="submitBtn" class="btn btn-accent flex items-center justify-center px-8 py-4 text-lg">
                <i class="fas fa-paper-plane mr-3"></i>
                Kirim Pendaftaran
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gunakan URL ini untuk server online (Hosting)
        const API_BASE_URL = 'https://portohansgunawan.my.id/api';

        // Gunakan URL ini untuk server lokal (php artisan serve)
        // const API_BASE_URL = 'http://127.0.0.1:8000/api';

        // Get token from localStorage (set after step1 registration)
        const authToken = localStorage.getItem('registration_token');

        if (!authToken) {
            showAlert('error', 'Token tidak ditemukan. Silakan daftar ulang dari Step 1.');
            setTimeout(() => {
                window.location.href = '{{ route("register.step1") }}';
            }, 2000);
            return;
        }

        // File upload handlers
        const fileInputs = ['ktp_orang_tua', 'ijazah', 'foto_siswa', 'bukti_pembayaran'];

        fileInputs.forEach(inputName => {
            const zone = document.getElementById(`${inputName}-zone`);
            const input = document.getElementById(inputName);

            if (!zone || !input) return;

            // Click to upload
            zone.addEventListener('click', function(e) {
                if (!e.target.classList.contains('remove-file') && !e.target.closest('.remove-file')) {
                    input.click();
                }
            });

            // Drag and drop
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-[--primary-blue]', 'bg-blue-50');
            });

            zone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('border-[--primary-blue]', 'bg-blue-50');
            });

            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-[--primary-blue]', 'bg-blue-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    handleFileSelect(input, zone);
                }
            });

            // File input change
            input.addEventListener('change', function() {
                handleFileSelect(this, zone);
            });

            // Remove file button
            const removeBtn = zone.querySelector('.remove-file');
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    input.value = '';
                    zone.querySelector('.upload-content').classList.remove('hidden');
                    zone.querySelector('.file-preview').classList.add('hidden');
                    zone.classList.remove('border-[--accent-green]', 'bg-green-50');
                });
            }
        });

        function handleFileSelect(input, zone) {
            const file = input.files[0];

            if (!file) return;

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAlert('error', `File ${file.name} terlalu besar. Maksimal 2MB.`);
                input.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('error', `File ${file.name} format tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.`);
                input.value = '';
                return;
            }

            // Update UI
            const uploadContent = zone.querySelector('.upload-content');
            const filePreview = zone.querySelector('.file-preview');
            const fileName = filePreview.querySelector('.file-name');
            const fileSize = filePreview.querySelector('.file-size');

            uploadContent.classList.add('hidden');
            filePreview.classList.remove('hidden');
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            zone.classList.add('border-[--accent-green]', 'bg-green-50');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form submission
        const step2Form = document.getElementById('step2Form');
        const submitBtn = document.getElementById('submitBtn');

        if (step2Form) {
            step2Form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Disable button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Mengirim...';

                try {
                    // Prepare FormData
                    const formData = new FormData();

                    fileInputs.forEach(inputName => {
                        const input = document.getElementById(inputName);
                        if (input && input.files.length > 0) {
                            formData.append(inputName, input.files[0]);
                        }
                    });

                    // Check if at least one file is uploaded
                    let hasFiles = false;
                    for (let pair of formData.entries()) {
                        hasFiles = true;
                        break;
                    }

                    if (!hasFiles) {
                        showAlert('warning', 'Anda belum mengupload dokumen apapun. Anda bisa melengkapinya nanti.');
                        setTimeout(() => {
                            // Redirect to success page or login
                            localStorage.removeItem('registration_token');
                            window.location.href = '/login?registered=true';
                        }, 2000);
                        return;
                    }

                    // Submit to API
                    const response = await fetch(`${API_BASE_URL}/upload-documents`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'ngrok-skip-browser-warning': 'true'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showAlert('success', result.message || 'Dokumen berhasil diupload! Pendaftaran Anda sedang diproses.');

                        // Clear token
                        localStorage.removeItem('registration_token');

                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = '/login?registered=true';
                        }, 2000);
                    } else {
                        // Handle validation errors
                        if (result.errors) {
                            let errorMessage = 'Error validasi:\n';
                            Object.keys(result.errors).forEach(key => {
                                errorMessage += `- ${result.errors[key].join(', ')}\n`;
                            });
                            showAlert('error', errorMessage);
                        } else {
                            showAlert('error', result.message || result.error || 'Gagal mengupload dokumen.');
                        }

                        // Re-enable button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-3"></i>Kirim Pendaftaran';
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi. Silakan coba lagi.');

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-3"></i>Kirim Pendaftaran';
                }
            });
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const iconMap = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            const colorMap = {
                success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-700', icon: 'text-green-500' },
                error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-700', icon: 'text-red-500' },
                warning: { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-700', icon: 'text-yellow-500' },
                info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-700', icon: 'text-blue-500' }
            };

            const colors = colorMap[type] || colorMap.info;
            const icon = iconMap[type] || iconMap.info;

            alertContainer.innerHTML = `
                <div class="mb-6 p-4 ${colors.bg} border ${colors.border} rounded-lg flex items-start">
                    <i class="fas ${icon} ${colors.icon} mr-3 mt-1"></i>
                    <span class="${colors.text} whitespace-pre-line">${message}</span>
                </div>
            `;

            // Scroll to alert
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
</script>
@endsection
