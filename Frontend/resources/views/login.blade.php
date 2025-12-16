<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SmartDev Academic</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-300 opacity-10 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-indigo-300 opacity-10 rounded-full blur-3xl animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4 transform hover:scale-110 transition-transform duration-300">
                <i class="bi bi-mortarboard-fill text-5xl text-indigo-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">SmartDev Academic</h1>
            <p class="text-indigo-100">Welcome back! Please login to your account.</p>
        </div>

        <!-- Login Card -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8 transform hover:scale-[1.02] transition-all duration-300">
            <form id="loginForm">
                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all duration-300"
                        placeholder="your.email@example.com"
                        required
                    >
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="bi bi-lock mr-1"></i>Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input-focus w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:outline-none transition-all duration-300"
                            placeholder="Enter your password"
                            required
                        >
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                        Forgot password?
                    </a>
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    id="loginBtn"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-300 transform hover:scale-[1.02] active:scale-95 shadow-lg"
                >
                    <i class="bi bi-box-arrow-in-right mr-2"></i>
                    <span id="loginBtnText">Login</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Or</span>
                </div>
            </div>

            <!-- API Token Login -->
            <button
                type="button"
                onclick="loginWithToken()"
                class="w-full border-2 border-indigo-200 text-indigo-600 font-semibold py-3 px-6 rounded-xl hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-indigo-200 transition-all duration-300 transform hover:scale-[1.02] active:scale-95"
            >
                <i class="bi bi-key mr-2"></i>
                Login with API Token
            </button>

            <!-- Register Link -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <a href="{{ route('registration') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                        Register here
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-indigo-100">
                © 2024 SmartDev Academic. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'https://portohansgunawan.my.id/api';

        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Login with Email & Password
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');

            // Disable button and show loading
            loginBtn.disabled = true;
            loginBtnText.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i>Logging in...';

            try {
                const response = await fetch(`${API_BASE_URL}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok && data.access_token) {
                    // Store token
                    localStorage.setItem('auth_token', data.access_token);

                    // Store SELURUH response dari login (user + profile)
                    localStorage.setItem('current_user', JSON.stringify(data));

                    // Show success message
                    await Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: 'Redirecting to dashboard...',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Redirect based on role
                    redirectToDashboard(data.user);
                } else {
                    throw new Error(data.message || 'Login failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: error.message || 'Invalid email or password'
                });
            } finally {
                // Re-enable button
                loginBtn.disabled = false;
                loginBtnText.innerHTML = 'Login';
            }
        });

        // Login with API Token
        async function loginWithToken() {
            const { value: token } = await Swal.fire({
                title: 'Login with API Token',
                input: 'text',
                inputLabel: 'Enter your Bearer Token',
                inputPlaceholder: 'Paste your token here...',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Login',
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6b7280',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Token is required!';
                    }
                }
            });

            if (token) {
                try {
                    // Verify token by fetching user info
                    const response = await fetch(`${API_BASE_URL}/user`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'ngrok-skip-browser-warning': 'true'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.data) {
                        // Store token
                        localStorage.setItem('auth_token', token);

                        await Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            text: 'Redirecting to dashboard...',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Redirect based on role
                        redirectToDashboard(data.data);
                    } else {
                        throw new Error('Invalid token');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Authentication Failed',
                        text: 'Invalid or expired token'
                    });
                }
            }
        }

        // Redirect to appropriate dashboard based on user role
        function redirectToDashboard(user) {
            console.log('Redirecting user:', user);
            const role = user.role || user.user_type;

            switch(role) {
                case 'admin':
                    window.location.href = '{{ route("admin.dashboard") }}';
                    break;
                case 'student':
                    window.location.href = '{{ route("student.dashboard") }}';
                    break;
                case 'instructor':
                    window.location.href = '{{ route("instructor.dashboard") }}';
                    break;
                case 'parent':
                    window.location.href = '{{ route("parent.dashboard") }}';
                    break;
                default:
                    window.location.href = '/';
            }
        }

        // Check if already logged in
        window.addEventListener('DOMContentLoaded', async () => {
            const token = localStorage.getItem('auth_token');

            if (token) {
                try {
                    const response = await fetch(`${API_BASE_URL}/user`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'ngrok-skip-browser-warning': 'true'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // API returns user data directly in data.data or data
                        const userData = data.data || data;
                        redirectToDashboard(userData);
                    }
                } catch (error) {
                    // Token invalid, continue to login page
                    localStorage.removeItem('auth_token');
                }
            }
        });
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</body>
</html>
