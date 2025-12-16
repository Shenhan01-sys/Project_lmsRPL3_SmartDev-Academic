/**
 * Registration Step 1 Handler
 * Handles form submission to API endpoint /api/register-calon-siswa
 */

document.addEventListener("DOMContentLoaded", function () {
    // Gunakan URL ini untuk server online (Hosting)
    const API_BASE_URL = "https://portohansgunawan.my.id/api";

    // Gunakan URL ini untuk server lokal (php artisan serve)
    // const API_BASE_URL = "http://127.0.0.1:8000/api";

    const step1Form = document.getElementById("step1Form");

    if (!step1Form) return;

    // Character counter for textarea
    const textarea = document.getElementById("alamat_orang_tua");
    const charCount = document.getElementById("charCount");

    if (textarea && charCount) {
        charCount.textContent = textarea.value.length;
        textarea.addEventListener("input", function () {
            charCount.textContent = this.value.length;
        });
    }

    // Password strength indicator
    const passwordInput = document.getElementById("password");
    const strengthContainer = document.querySelector(".password-strength");

    if (passwordInput && strengthContainer) {
        passwordInput.addEventListener("input", function () {
            const password = this.value;
            let strength = 0;
            let message = "";

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            strengthContainer.classList.remove("hidden");
            const bars = strengthContainer.querySelectorAll(".strength-bar");
            const strengthText =
                strengthContainer.querySelector(".strength-text");

            // Reset bars
            bars.forEach((bar) => {
                bar.className =
                    "strength-bar h-2 rounded-full transition-all duration-300";
            });

            // Update bars based on strength
            if (strength > 0) {
                for (let i = 0; i < strength; i++) {
                    if (strength === 1) {
                        bars[i].classList.add("bg-red-500");
                        message = "Password lemah";
                    } else if (strength === 2) {
                        bars[i].classList.add("bg-yellow-500");
                        message = "Password cukup";
                    } else if (strength === 3) {
                        bars[i].classList.add("bg-blue-500");
                        message = "Password kuat";
                    } else if (strength === 4) {
                        bars[i].classList.add("bg-[--accent-green]");
                        message = "Password sangat kuat";
                    }
                }
            }

            if (strengthText) {
                strengthText.textContent = message;
                strengthText.className = `strength-text text-xs mt-1 ${
                    strength === 1
                        ? "text-red-500"
                        : strength === 2
                          ? "text-yellow-500"
                          : strength === 3
                            ? "text-blue-500"
                            : "text-[--accent-green]"
                }`;
            }
        });
    }

    // Radio button styling
    const radioCards = document.querySelectorAll(".radio-card");
    radioCards.forEach((card) => {
        card.addEventListener("click", function () {
            const input = this.querySelector('input[type="radio"]');
            if (input) {
                // Remove selected class from all cards in this group
                const groupName = input.name;
                document
                    .querySelectorAll(`input[name="${groupName}"]`)
                    .forEach((radio) => {
                        radio
                            .closest(".radio-card")
                            .classList.remove("selected");
                    });

                // Add selected class to clicked card
                this.classList.add("selected");
                input.checked = true;
            }
        });
    });

    // Form submission handler
    step1Form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Client-side validation
        let isValid = true;
        const requiredFields = this.querySelectorAll("[required]");

        // Validate all required fields
        requiredFields.forEach((field) => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        // Password confirmation validation
        const password = document.getElementById("password");
        const passwordConfirmation = document.getElementById(
            "password_confirmation",
        );

        if (
            password &&
            passwordConfirmation &&
            password.value !== passwordConfirmation.value
        ) {
            isValid = false;
            password.classList.add("error");
            passwordConfirmation.classList.add("error");
            showFieldError(
                passwordConfirmation,
                "Konfirmasi password tidak cocok",
            );
        }

        if (!isValid) {
            showNotification(
                "Harap perbaiki error pada form sebelum melanjutkan",
                "error",
            );
            return;
        }

        // Get form data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Prepare data for API - kirim semua data sekaligus
        const registrationData = {
            name: data.name,
            email: data.email,
            password: data.password,
            password_confirmation: data.password_confirmation,
            tanggal_lahir: data.tanggal_lahir,
            tempat_lahir: data.tempat_lahir,
            jenis_kelamin: data.jenis_kelamin,
            nama_orang_tua: data.nama_orang_tua,
            email_orang_tua: data.email_orang_tua || "", // Optional
            phone_orang_tua: data.phone_orang_tua,
            alamat_orang_tua: data.alamat_orang_tua,
        };

        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-3"></i>Mendaftar...';

        try {
            // Submit to API
            const response = await fetch(
                `${API_BASE_URL}/register-calon-siswa`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "ngrok-skip-browser-warning": "true",
                    },
                    body: JSON.stringify(registrationData),
                },
            );

            const result = await response.json();
            console.log("API Response:", result); // Debug log

            if (response.ok) {
                // Save token to localStorage
                if (result.token) {
                    localStorage.setItem("registration_token", result.token);
                }

                showNotification(
                    result.message ||
                        "Registrasi berhasil! Silakan upload dokumen.",
                    "success",
                );

                // Redirect to step 2 after 1.5 seconds
                setTimeout(() => {
                    window.location.href = "/register/step2";
                }, 1500);
            } else {
                // Handle validation errors (422)
                if (result.errors) {
                    let errorMessage = "Kesalahan validasi:\n";
                    Object.keys(result.errors).forEach((key) => {
                        errorMessage += `• ${result.errors[key].join(", ")}\n`;

                        // Highlight error fields
                        const field = document.querySelector(`[name="${key}"]`);
                        if (field) {
                            field.classList.add("error");
                            showFieldError(field, result.errors[key][0]);
                        }
                    });
                    showNotification(errorMessage, "error");
                } else if (result.error) {
                    // Handle error from catch block (500)
                    console.error("Backend error:", result.error);
                    showNotification("Error: " + result.error, "error");
                } else if (result.message) {
                    // Handle generic message
                    showNotification(result.message, "error");
                } else {
                    // Fallback
                    showNotification(
                        "Registrasi gagal. Silakan coba lagi.",
                        "error",
                    );
                }

                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        } catch (error) {
            console.error("Network/Connection error:", error);
            showNotification(
                "Terjadi kesalahan koneksi ke server. Silakan cek koneksi internet Anda dan coba lagi.",
                "error",
            );

            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });

    // Helper function to validate individual field
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;

        // Remove previous error state
        field.classList.remove("error");
        const existingError = field.parentElement.querySelector(".field-error");
        if (existingError) {
            existingError.remove();
        }

        // Required field check
        if (field.hasAttribute("required") && !value) {
            isValid = false;
            field.classList.add("error");
            showFieldError(field, "Field ini wajib diisi");
            return isValid;
        }

        // Email validation
        if (field.type === "email" && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                field.classList.add("error");
                showFieldError(field, "Format email tidak valid");
            }
        }

        // Phone validation
        if (field.name === "phone_orang_tua" && value) {
            const phoneRegex = /^(08|62)\d{8,13}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                field.classList.add("error");
                showFieldError(
                    field,
                    "Format nomor telepon tidak valid (contoh: 08123456789)",
                );
            }
        }

        // Password validation
        if (field.name === "password" && value) {
            if (value.length < 8) {
                isValid = false;
                field.classList.add("error");
                showFieldError(field, "Password minimal 8 karakter");
            }
        }

        return isValid;
    }

    // Show field-specific error message
    function showFieldError(field, message) {
        const existingError = field.parentElement.querySelector(".field-error");
        if (existingError) {
            existingError.textContent = message;
        } else {
            const errorDiv = document.createElement("p");
            errorDiv.className =
                "field-error text-red-500 text-xs mt-1 flex items-center";
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message}`;

            // Insert after the input or input group
            const inputGroup = field.closest(".form-input-group");
            if (inputGroup) {
                inputGroup.parentElement.insertBefore(
                    errorDiv,
                    inputGroup.nextSibling,
                );
            } else {
                field.parentElement.insertBefore(errorDiv, field.nextSibling);
            }
        }
    }

    // Show notification (using SweetAlert if available, fallback to alert)
    function showNotification(message, type = "info") {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: type,
                title:
                    type === "success"
                        ? "Berhasil!"
                        : type === "error"
                          ? "Error!"
                          : "Informasi",
                text: message,
                confirmButtonColor:
                    type === "success"
                        ? "#10b981"
                        : type === "error"
                          ? "#ef4444"
                          : "#3b82f6",
                confirmButtonText: "OK",
            });
        } else {
            alert(message);
        }
    }

    // Real-time validation on blur
    const formInputs = step1Form.querySelectorAll("input, textarea");
    formInputs.forEach((input) => {
        input.addEventListener("blur", function () {
            if (this.value.trim() !== "") {
                validateField(this);
            }
        });

        // Remove error state on input
        input.addEventListener("input", function () {
            if (this.classList.contains("error")) {
                this.classList.remove("error");
                const existingError =
                    this.parentElement.querySelector(".field-error");
                if (existingError) {
                    existingError.remove();
                }
            }
        });
    });
});
