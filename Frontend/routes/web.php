<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home/Landing Page
Route::get("/", function () {
    return view("welcome");
})->name("home");

// Authentication Routes
Route::get("/login", function () {
    return view("login");
})->name("login");

// Registration Routes
Route::get("/register", function () {
    return redirect()->route("register.step1");
})->name("registration");

Route::get("/register/step1", function () {
    return view("register.step1");
})->name("register.step1");

Route::post("/register/step1", function () {
    // This will be handled by API endpoint
    // Frontend should submit directly to API
    return redirect()->route("register.step2");
})->name("register.store.step1");

Route::get("/register/step2", function () {
    return view("register.step2");
})->name("register.step2");

Route::post("/register/step2", function () {
    // This will be handled by API endpoint
    // Frontend should submit directly to API
    return redirect()
        ->route("login")
        ->with("success", "Registrasi berhasil! Silakan login.");
})->name("register.store.step2");

// Dashboard Routes (Protected - Frontend will handle auth via localStorage token)

// Admin Dashboard
Route::get("/admin/dashboard", function () {
    return view("adminDashboard");
})->name("admin.dashboard");

// Student Dashboard
Route::get("/student/dashboard", function () {
    return view("studentDashboard");
})->name("student.dashboard");

// Instructor Dashboard
Route::get("/instructor/dashboard", function () {
    return view("instructorDashboard");
})->name("instructor.dashboard");

// Parent Dashboard
Route::get("/parent/dashboard", function () {
    return view("parentDashboard");
})->name("parent.dashboard");

// Logout (Clear local storage via JavaScript)
Route::get("/logout", function () {
    return view("logout");
})->name("logout");

// API Proxy Routes (Optional - if you want to proxy API calls through Laravel)
// This can help with CORS issues if needed
/*
Route::prefix('api/proxy')->group(function () {
    Route::any('{any}', function (Request $request) {
        $apiBaseUrl = 'https://portohansgunawan.my.id/api/v1';
        $path = $request->path();
        $path = str_replace('api/proxy/', '', $path);

        $token = $request->bearerToken();

        $client = new \GuzzleHttp\Client();

        $response = $client->request($request->method(), "$apiBaseUrl/$path", [
            'headers' => [
                'Authorization' => $token ? "Bearer $token" : '',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'query' => $request->query(),
            'json' => $request->all(),
        ]);

        return response($response->getBody(), $response->getStatusCode());
    })->where('any', '.*');
});
*/
