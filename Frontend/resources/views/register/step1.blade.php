@extends('register.registration')

@section('title', 'Data Diri - Pendaftaran Siswa')
@section('current-step', '1')
@section('step1-status', 'active')
@section('step2-status', 'inactive')
@section('step1-icon', 'fa-user')
@section('step2-icon', 'fa-file-upload')
@section('step1-dot', 'bg-white')
@section('step2-dot', 'bg-white bg-opacity-30')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-r from-[--primary-blue] to-[--accent-green] rounded-full flex items-center justify-center">
            <i class="fas fa-user-graduate text-3xl text-white"></i>
        </div>
        <h2 class="text-3xl font-bold text-[--text-primary] mb-3">Informasi Data Diri</h2>
        <p class="text-[--text-secondary] text-lg">Lengkapi data diri dan informasi orang tua dengan benar</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <span class="text-green-700">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <span class="text-red-700">{{ session('error') }}</span>
    </div>
    @endif

    <form id="step1Form" method="POST" action="{{ route('register.store.step1') }}" class="space-y-8">
        @csrf

        <!-- Section 1: Data Siswa -->
        <section class="bg-gradient-to-br from-[--light-gray] to-white rounded-2xl p-6 border border-[--gray-100]">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-[--primary-blue] rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[--text-primary]">Data Siswa</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap dengan Icon -->
<div class="md:col-span-2">
    <label class="form-label" for="name">
        <i class="fas fa-signature text-[--primary-blue] mr-2"></i>
        Nama Lengkap
    </label>
    <div class="form-input-group">
        <input type="text" id="name" name="name" class="form-input with-icon"
               placeholder="Masukkan nama lengkap"
               value="{{ old('name') }}" required>
        <i class="fas fa-user form-icon"></i>
    </div>
    @error('name')
        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
        </p>
    @enderror
    <p class="form-helper"><i class="fas fa-info-circle text-[--primary-blue] mr-1"></i>Wajib diisi sesuai dokumen resmi</p>
</div>

<!-- Email dengan Icon -->
<div class="md:col-span-2">
    <label class="form-label" for="email">
        <i class="fas fa-envelope text-[--primary-blue] mr-2"></i>
        Email
    </label>
    <div class="form-input-group">
        <input type="email" id="email" name="email" class="form-input with-icon"
               placeholder="nama@email.com"
               value="{{ old('email') }}" required>
        <i class="fas fa-at form-icon"></i>
    </div>
    @error('email')
        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
        </p>
    @enderror
</div>

                <!-- Password -->
                <div>
                    <label class="form-label" for="password">
                        <i class="fas fa-lock text-[--primary-blue] mr-2"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Minimal 8 karakter" required>
                    @error('password')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                    <div class="password-strength mt-2 hidden">
                        <div class="flex space-x-1">
                            <div class="strength-bar w-1/4"></div>
                            <div class="strength-bar w-1/4"></div>
                            <div class="strength-bar w-1/4"></div>
                            <div class="strength-bar w-1/4"></div>
                        </div>
                        <p class="strength-text text-xs mt-1"></p>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="form-label" for="password_confirmation">
                        <i class="fas fa-lock text-[--primary-blue] mr-2"></i>
                        Konfirmasi Password
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-input" placeholder="Ulangi password" required>
                </div>



                <!-- Tanggal Lahir -->
                <div>
                    <label class="form-label" for="tanggal_lahir">
                        <i class="fas fa-calendar text-[--primary-blue] mr-2"></i>
                        Tanggal Lahir
                    </label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                           class="form-input" value="{{ old('tanggal_lahir') }}" required>
                    @error('tanggal_lahir')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="form-label" for="tempat_lahir">
                        <i class="fas fa-map-marker-alt text-[--primary-blue] mr-2"></i>
                        Tempat Lahir
                    </label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir"
                           class="form-input" placeholder="Kota tempat lahir"
                           value="{{ old('tempat_lahir') }}" required>
                    @error('tempat_lahir')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Jenis Kelamin Section - Diperbarui -->
<div class="md:col-span-2">
    <label class="form-label">
        <i class="fas fa-venus-mars text-[--primary-blue] mr-2"></i>
        Jenis Kelamin
    </label>
    <div class="radio-group">
        <label class="radio-card {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}" data-value="L">
            <input type="radio" name="jenis_kelamin" value="L"
                   {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }} required>
            <i class="fas fa-mars radio-icon"></i>
            <span class="radio-label">Laki-laki</span>
        </label>
        <label class="radio-card {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}" data-value="P">
            <input type="radio" name="jenis_kelamin" value="P"
                   {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }} required>
            <i class="fas fa-venus radio-icon"></i>
            <span class="radio-label">Perempuan</span>
        </label>
    </div>
    @error('jenis_kelamin')
        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
        </p>
    @enderror
</div>
            </div>
        </section>

        <!-- Section 2: Data Orang Tua -->
        <section class="bg-gradient-to-br from-[--light-gray] to-white rounded-2xl p-6 border border-[--gray-100]">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-[--accent-green] rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[--text-primary]">Data Orang Tua</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Orang Tua -->
                <div>
                    <label class="form-label" for="nama_orang_tua">
                        <i class="fas fa-user-tie text-[--accent-green] mr-2"></i>
                        Nama Orang Tua
                    </label>
                    <input type="text" id="nama_orang_tua" name="nama_orang_tua"
                           class="form-input" placeholder="Nama lengkap orang tua"
                           value="{{ old('nama_orang_tua') }}" required>
                    @error('nama_orang_tua')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Orang Tua -->
                <div>
                    <label class="form-label" for="email_orang_tua">
                        <i class="fas fa-envelope text-[--accent-green] mr-2"></i>
                        Email Orang Tua
                    </label>
                    <input type="email" id="email_orang_tua" name="email_orang_tua"
                           class="form-input" placeholder="email.orangtua@email.com"
                           value="{{ old('email_orang_tua') }}">
                    @error('email_orang_tua')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                    <p class="form-helper"><i class="fas fa-info-circle text-[--accent-green] mr-1"></i>Opsional, akan dibuat otomatis jika kosong</p>
                </div>

                <!-- Phone Orang Tua -->
                <div>
                    <label class="form-label" for="phone_orang_tua">
                        <i class="fas fa-mobile-alt text-[--accent-green] mr-2"></i>
                        Nomor Telepon Orang Tua
                    </label>
                    <input type="tel" id="phone_orang_tua" name="phone_orang_tua"
                           class="form-input" placeholder="08xxxxxxxxxx"
                           value="{{ old('phone_orang_tua') }}" required>
                    @error('phone_orang_tua')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Alamat Orang Tua -->
                <div class="md:col-span-2">
                    <label class="form-label" for="alamat_orang_tua">
                        <i class="fas fa-home text-[--accent-green] mr-2"></i>
                        Alamat Orang Tua
                    </label>
                    <textarea id="alamat_orang_tua" name="alamat_orang_tua" class="form-textarea"
                              placeholder="Alamat lengkap tempat tinggal (maksimal 500 karakter)"
                              maxlength="500" required>{{ old('alamat_orang_tua') }}</textarea>
                    <div class="flex justify-between items-center mt-1">
                        <p class="form-helper"><i class="fas fa-info-circle text-[--accent-green] mr-1"></i>Alamat akan digunakan untuk korespondensi</p>
                        <span class="text-xs text-[--text-secondary]"><span id="charCount">0</span></span>
                    </div>
                    @error('alamat_orang_tua')
                        <p class="field-error text-[--error] text-xs mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary px-12 py-4 text-lg">
                <i class="fas fa-arrow-right mr-3"></i>
                Lanjut ke Upload Dokumen
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Registration Step 1 Handler -->
<script src="{{ asset('js/register.js') }}"></script>
@endsection
