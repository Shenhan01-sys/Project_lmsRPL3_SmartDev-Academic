<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="color-scheme" content="light dark" />
  <title>Masuk - {{ config('app.name', 'Aplikasi') }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-200 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
  <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 rounded-md shadow ring-1 ring-slate-300 dark:ring-slate-700">Lewati ke konten utama</a>

  <main id="main" role="main" class="w-full max-w-md">
    <div class="bg-white dark:bg-slate-900 shadow-xl rounded-2xl p-6 sm:p-8 md:p-10 ring-1 ring-slate-200 dark:ring-slate-700">
      <div class="flex flex-col items-center mb-6">
        <div class="h-12 w-12 md:h-14 md:w-14 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center ring-1 ring-slate-200 dark:ring-slate-700">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6 md:h-7 md:w-7" aria-hidden="true" focusable="false">
            <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" stroke="#6366f1" stroke-width="1.5" fill="#eef2ff" />
            <path d="M8.5 10.5L12 12l3.5-1.5" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-100">Masuk ke Akun</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 text-center">Silakan masukkan kredensial Anda untuk melanjutkan</p>
      </div>

      <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm" aria-describedby="form-help">
        @csrf

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
          <div class="mt-1">
            <input id="email" type="email" name="email" inputmode="email" value="{{ old('email') }}" required autofocus autocomplete="email" autocapitalize="none" autocorrect="off" spellcheck="false" translate="no"
                   class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 placeholder-slate-400 @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   placeholder="you@example.com" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror />
          </div>
          @error('email')
            <p id="email-error" class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Kata Sandi</label>
          <div class="mt-1 relative">
            <input id="password" type="password" name="password" required autocomplete="current-password" autocapitalize="none" spellcheck="false" translate="no"
                   class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 placeholder-slate-400 pr-10 @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   placeholder="••••••••" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror />
            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 px-3 inline-flex items-center text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200" aria-label="Tampilkan/sembunyikan kata sandi" aria-controls="password" aria-pressed="false">
              <svg id="eyeIcon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none" />
              </svg>
            </button>
          </div>
          @error('password')
            <p id="password-error" class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 select-none">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('remember') ? 'checked' : '' }} />
            <span class="text-sm text-slate-700 dark:text-slate-300">Ingat saya</span>
          </label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">Lupa kata sandi?</a>
          @endif
        </div>

        <p id="form-help" class="sr-only">Isi email dan kata sandi Anda untuk masuk. Bidang yang wajib diisi ditandai.</p>

        <button type="submit" id="submitBtn" class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900 text-white px-4 py-2.5 text-sm sm:text-base font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed">
          <svg id="spinner" class="hidden h-5 w-5 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
            <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span id="btnText">Masuk</span>
          <span class="sr-only" aria-live="polite">Mengirim formulir</span>
        </button>
      </form>

      <div class="mt-6">
        @if (session('status'))
          <div class="rounded-lg bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 text-sm p-3" role="status" aria-live="polite">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="mt-3 rounded-lg bg-rose-50 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 text-sm p-3" role="alert" aria-live="assertive">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
    </div>

    <p class="mt-6 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
      © {{ date('Y') }} {{ config('app.name', 'Aplikasi') }}. Semua hak dilindungi.
    </p>
  </main>

  <noscript>
    <div class="fixed inset-x-0 top-0 mx-auto max-w-md mt-4 px-4">
      <div class="rounded-lg bg-amber-50 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200 p-3 text-sm ring-1 ring-amber-200 dark:ring-amber-700">
        JavaScript dinonaktifkan. Beberapa fitur seperti menampilkan/menyembunyikan kata sandi dan indikator pemuatan tidak akan berfungsi.
      </div>
    </div>
  </noscript>

  <script>
    (function () {
      const form = document.getElementById('loginForm');
      const submitBtn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const btnText = document.getElementById('btnText');
      const toggleBtn = document.getElementById('togglePassword');
      const eyeIcon = document.getElementById('eyeIcon');
      const pwd = document.getElementById('password');

      const iconShow = '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18"></path>'+
                       '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.58 10.58A3 3 0 0012 15a3 3 0 002.42-4.42M6.53 6.53C3.9 8.2 2 12 2 12s3.5 7 10 7c2.04 0 3.86-.5 5.39-1.31"></path>';
      const iconHide = '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>'+
                       '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none"></circle>';

      if (toggleBtn && pwd && eyeIcon) {
        toggleBtn.addEventListener('click', function () {
          const isPw = pwd.getAttribute('type') === 'password';
          pwd.setAttribute('type', isPw ? 'text' : 'password');
          this.setAttribute('aria-pressed', isPw ? 'true' : 'false');
          eyeIcon.innerHTML = isPw ? iconShow : iconHide;
        });
      }

      if (form && submitBtn && spinner && btnText) {
        form.addEventListener('submit', function () {
          submitBtn.disabled = true;
          spinner.classList.remove('hidden');
          btnText.textContent = 'Memproses...';
          form.setAttribute('aria-busy', 'true');
        });
      }
    })();
  </script>
</body>
</html>
