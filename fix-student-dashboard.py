import re

# Read file
with open('docs/frontend-guiding/student-dashboard-refactored.html', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove Update Token button (safe removal with multiline regex)
content = re.sub(
    r'<div class="px-6 mb-2">\s*<button[^>]*onclick="manualTokenInput\(\)"[^>]*>.*?</button>\s*</div>',
    '',
    content,
    flags=re.DOTALL
)

# 2. Fix currentUser.profile?.id || currentUser.id -> currentUser.profile.id
content = content.replace('currentUser.profile?.id || currentUser.id', 'currentUser.profile.id')
content = content.replace('currentUser.student?.id || currentUser.id', 'currentUser.profile.id')

# 3. Fix the initApp function - normalize data structure
old_init = '''                    // 2. Fetch User Profile
                    currentUser = await fetchApi("/user");

                    // 3. VALIDASI ROLE (Fitur Baru)
                    // Jika role user bukan 'student', tolak akses dan minta token ulang
                    if (currentUser.role !== "student") {
                        throw new Error(
                            `Akun terdeteksi sebagai '${currentUser.role}'. Halaman ini khusus untuk Student.`,
                        );
                    }

                    // Pastikan relasi student ada (fallback safety)
                    if (!currentUser.student) {
                        currentUser.student = { id: currentUser.id };
                    }'''

new_init = '''                    // 2. Fetch User Profile
                    const userData = await fetchApi("/user");
                    
                    // Normalize structure: support both /login and /user response
                    if (userData.user && userData.profile) {
                        // From /login response
                        currentUser = userData.user;
                        currentUser.profile = userData.profile;
                    } else if (userData.student) {
                        // From /user response  
                        currentUser = userData;
                        currentUser.profile = userData.student;
                    } else {
                        currentUser = userData;
                    }

                    // 3. VALIDASI ROLE
                    if (currentUser.role !== "student") {
                        throw new Error(
                            `Akun terdeteksi sebagai '${currentUser.role}'. Halaman ini khusus untuk Student.`,
                        );
                    }

                    // Ensure profile exists
                    if (!currentUser.profile) {
                        throw new Error("Student profile not found");
                    }'''

content = content.replace(old_init, new_init)

# Write back
with open('docs/frontend-guiding/student-dashboard-refactored.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("✅ File fixed successfully!")
