# 🌙 Instruksi Menambahkan Dark Mode Toggle ke index_modern.html

Tool otomatis mengalami kesulitan dengan file HTML kompleks, jadi berikut adalah instruksi manual untuk menambahkan dark mode:

## 📝 **LANGKAH 1: Tambahkan CSS Variables (Baris ~34-40)**

Cari bagian yang dimulai dengan `<style>` dan comment `/* Custom Premium Styles */`. **GANTI** section body style yang lama dengan ini:

```css
    <style>
        /* CSS Variables for Theme */
        :root {
            --bg: #F8FAFC;
            --text: #1E293B;
            --primary: #4F46E5;
            --primaryT: rgba(79, 70, 229, 0.2);
            --transDur: 0.3s;
        }

        [data-theme="dark"] {
            --bg: #0F172A;
            --text: #F8FAFC;
        }

        /* Custom Premium Styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            transition: background-color var(--transDur) ease, color var(--transDur) ease;
        }

        /* Theme Toggle Button Styles */
        .theme__toggle {
            background-color: hsl(48, 90%, 85%);
            border-radius: 25% / 50%;
            box-shadow: 0 0 0 0.125em var(--primaryT);
            padding: 0.25em;
            width: 6em;
            height: 3em;
            -webkit-appearance: none;
            appearance: none;
            transition: background-color var(--transDur) ease-in-out,
                box-shadow 0.15s ease-in-out,
                transform var(--transDur) ease-in-out;
            cursor: pointer;
        }

        .theme__toggle:focus {
            outline: none;
        }

        .theme__toggle::before {
            content: "";
            display: block;
            width: 2.5em;
            height: 2.5em;
            border-radius: 50%;
            background-color: hsl(48, 90%, 55%);
            box-shadow: 0 0 0 0.125em hsla(48, 90%, 55%, 0.5);
            transition: background-color var(--transDur) ease-in-out,
                transform var(--transDur) ease-in-out;
        }

        .theme__toggle:checked {
            background-color: hsl(198, 60%, 30%);
        }

        .theme__toggle:checked::before {
            background-color: hsl(198, 60%, 70%);
            box-shadow: 0 0 0 0.125em hsla(198, 60%, 70%, 0.5);
            transform: translateX(3em);
        }

        /* Smooth Reveal Animation */
        .reveal {
```

---

## 📝 **LANGKAH 2: Tambahkan Toggle Button di Navbar (Setelah Desktop Menu, sebelum Auth Buttons)**

Cari section navbar dengan comment `<!-- Desktop Menu -->` dan `<!-- Auth Buttons -->`.  
**SISIPKAN** kode ini **DI ANTARA** keduanya:

```html
                </div>

                <!-- Dark Mode Toggle -->
                <div class="hidden md:flex items-center">
                    <input type="checkbox" id="themeToggle" class="theme__toggle" aria-label="Toggle dark mode">
                </div>

                <!-- Auth Buttons -->
```

---

## 📝 **LANGKAH 3: Tambahkan JavaScript (Di bagian bawah file, SEBELUM tag penutup `</script>`)**

Cari bagian `<script>` yang sudah ada (dekat baris 768), lalu **TAMBAHKAN** kode ini **DI DALAM** script tersebut, SETELAH kode Intersection Observer:

```javascript
            // Dark Mode Toggle
            const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;

            // Load saved theme from localStorage
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                htmlElement.setAttribute('data-theme', 'dark');
                themeToggle.checked = true;
            }

            // Toggle theme on checkbox change
            themeToggle.addEventListener('change', () => {
                if (themeToggle.checked) {
                    htmlElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    htmlElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>
```

---

## ✅ **VERIFIKASI**

Setelah menerapkan ketiga langkah di atas:

1. ✅ Buka `index_modern.html` di browser
2. ✅ Lihat ada toggle button di navbar (antara menu dan tombol auth)
3. ✅ Klik toggle - background harus berubah dari terang ke gelap
4. ✅ Refresh halaman - tema yang dipilih harus tetap tersimpan

---

## 🎨 **Cara customize warna dark mode**

Jika Anda ingin mengubah warna dark mode, edit section `[data-theme="dark"]` di CSS Variables:

```css
[data-theme="dark"] {
    --bg: #0F172A;      /* Background gelap */
    --text: #F8FAFC;    /* Text terang */
}
```

**Selamat mencoba! Jika ada error, beritahu saya bagian mana yang bermasalah.** 🚀
