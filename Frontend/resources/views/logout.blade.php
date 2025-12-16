<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out - SmartDev Academic</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-auto">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-indigo-600 mx-auto mb-4"></div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Logging Out...</h1>
            <p class="text-gray-600">Please wait while we securely log you out.</p>
        </div>
    </div>

    <script>
        // Clear all authentication data
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('user_data');

        // Clear session storage
        sessionStorage.clear();

        // Wait a moment then redirect to login
        setTimeout(() => {
            window.location.href = '{{ route("login") }}';
        }, 1000);
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
