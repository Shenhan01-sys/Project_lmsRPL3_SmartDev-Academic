# ✅ Testing Checklist - SMARTDev Academic Landing Page

> Checklist lengkap untuk testing dan quality assurance landing page mockup

---

## 🖥️ Desktop Testing (1920px, 1440px, 1280px)

### Navigation Bar
- [ ] Logo dan branding ditampilkan dengan benar
- [ ] Semua menu links terlihat (Fitur, Cara Kerja, Kursus, FAQ)
- [ ] Login & Daftar buttons styled dengan benar
- [ ] Hover effects berfungsi pada semua links
- [ ] Smooth scroll ke section yang benar saat click menu
- [ ] Navbar menjadi solid/shadow saat scroll down
- [ ] Tidak ada horizontal overflow

### Hero Section
- [ ] Gradient background tampil dengan benar
- [ ] Heading dan subheading readable
- [ ] 2 CTA buttons (Daftar & Login) terlihat jelas
- [ ] Trust indicators (3 checkmarks) ditampilkan
- [ ] Dashboard preview image/placeholder tampil
- [ ] Spacing dan padding seimbang
- [ ] Text tidak terlalu panjang (max 60-80 karakter per baris)

### Role-Based Cards Section
- [ ] 3 cards (Siswa, Pengajar, Orang Tua) sejajar horizontal
- [ ] Gradient headers berbeda warna per card
- [ ] Icons ditampilkan dengan benar
- [ ] 4 bullet points per card readable
- [ ] CTA buttons berbeda per role
- [ ] Hover effect: card naik (-translate-y-2)
- [ ] Shadow meningkat saat hover
- [ ] Spacing antar cards konsisten

### Key Features Section
- [ ] 8 feature cards dalam grid 3 kolom
- [ ] Icons berbeda per feature dengan background color berbeda
- [ ] Heading dan description readable
- [ ] "Pelajari lebih lanjut" link dengan arrow
- [ ] Hover effect: shadow bertambah
- [ ] Background abu-abu lembut per card
- [ ] Grid tidak broken

### How It Works Section
- [ ] 3 steps ditampilkan dengan jelas
- [ ] Nomor dalam circle (gradient background)
- [ ] Timeline connector (garis vertikal) di antara steps
- [ ] Icons berbeda per step
- [ ] Status badges (pending_documents, pending_approval, approved)
- [ ] Document checklist di step 2 readable
- [ ] CTA button "Mulai Pendaftaran" di bawah
- [ ] Background gradient lembut

### Stats Section
- [ ] 4 counter boxes horizontal
- [ ] Counter animation berjalan saat section visible
- [ ] Angka bertambah smooth (tidak langsung loncat)
- [ ] Tanda "+" muncul setelah counter selesai
- [ ] Text label di bawah angka readable
- [ ] Gradient background (primary to secondary)
- [ ] Text putih kontras dengan background

### Course Preview Section
- [ ] 2 tabs (SMP & SMA) ditampilkan di atas
- [ ] Tab aktif memiliki gradient background
- [ ] Click tab berpindah konten dengan fade animation
- [ ] 3 course cards per tab
- [ ] Course code, nama, semester ditampilkan
- [ ] Instructor name dengan icon
- [ ] Modules & Materi count dengan layout grid
- [ ] "Lihat Detail" button per card
- [ ] Hover effect: card naik dengan shadow
- [ ] "Lihat Semua Kursus" button di bawah

### FAQ Section
- [ ] 8 FAQ items ditampilkan
- [ ] First FAQ terbuka by default
- [ ] Click question untuk expand/collapse
- [ ] Chevron icon rotate 180° saat expand
- [ ] Hanya 1 FAQ terbuka pada satu waktu
- [ ] Smooth height transition (tidak jumpy)
- [ ] Answer text readable dengan spacing yang baik
- [ ] Hover effect: sedikit geser ke kanan

### CTA Section
- [ ] Gradient background sama dengan hero
- [ ] Large heading dengan subtext
- [ ] 2 buttons (Daftar & Hubungi)
- [ ] Spacing antara buttons
- [ ] Hover effects berfungsi

### Footer
- [ ] 4 columns layout (Branding, Quick Links, Portal, Contact)
- [ ] Logo dan tagline di column 1
- [ ] Social media icons (4 icons)
- [ ] Quick links tidak broken
- [ ] Portal login links per role
- [ ] Contact info lengkap (email, WA, alamat)
- [ ] Bottom bar dengan copyright
- [ ] "Made with ❤️" text
- [ ] Background gelap (gray-900) dengan text putih

### Sticky CTA Bar
- [ ] Tidak terlihat saat page load
- [ ] Muncul setelah scroll past hero section
- [ ] Slide up animation smooth
- [ ] Text dan 2 buttons ditampilkan
- [ ] Hilang saat mendekati footer
- [ ] Fixed di bottom screen
- [ ] Tidak overlap dengan content

### Back to Top Button
- [ ] Tidak terlihat saat page load
- [ ] Muncul setelah scroll 300px
- [ ] Fade in/out smooth
- [ ] Click scroll to top dengan smooth behavior
- [ ] Gradient background (primary to secondary)
- [ ] Icon arrow up ditampilkan
- [ ] Fixed di bottom right

---

## 📱 Tablet Testing (768px - 1024px)

### Layout Changes
- [ ] Role cards: 2 kolom atau stack
- [ ] Feature cards: 2 kolom
- [ ] Course cards: 2 kolom
- [ ] Footer: 2 kolom atau stack
- [ ] Spacing dan padding adjusted

### Navigation
- [ ] Desktop menu masih terlihat atau berubah mobile menu
- [ ] Semua functionality tetap berjalan

### Readability
- [ ] Text size masih readable
- [ ] Images tidak terdistorsi
- [ ] Buttons masih clickable dengan ukuran cukup

---

## 📱 Mobile Testing (375px - 414px)

### Navigation
- [ ] Hamburger menu icon ditampilkan
- [ ] Desktop menu links hidden
- [ ] Click hamburger membuka mobile menu
- [ ] Mobile menu slide down smooth
- [ ] Icon berubah dari bars → times
- [ ] Click link menutup mobile menu
- [ ] Menu items stacked vertical
- [ ] Daftar & Login buttons full width

### Hero Section
- [ ] Single column layout
- [ ] Heading size reduced tapi masih readable
- [ ] CTA buttons stacked vertical atau horizontal (sesuai space)
- [ ] Trust indicators bisa wrap jika perlu
- [ ] Dashboard image hidden atau scaled down

### Role Cards
- [ ] Stacked vertical (1 per row)
- [ ] Spacing antar cards cukup
- [ ] Semua content readable
- [ ] CTA buttons full width

### Features
- [ ] 1 kolom, stacked vertical
- [ ] Icons dan text masih aligned
- [ ] Spacing consistent

### How It Works
- [ ] Timeline masih terlihat jelas
- [ ] Steps stacked vertical
- [ ] Document checklist readable
- [ ] Status badges tidak terpotong

### Stats
- [ ] 2x2 grid atau stacked 1 kolom
- [ ] Counter masih berfungsi
- [ ] Text tidak overflow

### Course Cards
- [ ] 1 kolom, stacked vertical
- [ ] Tabs full width
- [ ] Card details masih readable

### FAQ
- [ ] Full width, stacked
- [ ] Question text tidak terpotong
- [ ] Accordion masih smooth

### Footer
- [ ] 1 kolom, stacked vertical
- [ ] Semua info masih accessible
- [ ] Social icons horizontal
- [ ] Contact info tidak terpotong

### Sticky CTA
- [ ] Full width mobile
- [ ] Text dan buttons stack atau shrink
- [ ] Masih functional

---

## 🎨 Visual & Design Testing

### Colors
- [ ] Primary color (#667eea) konsisten
- [ ] Secondary color (#764ba2) konsisten
- [ ] Gradients smooth (tidak patah-patah)
- [ ] Accent colors (success, danger, warning) sesuai
- [ ] Kontras text readable (WCAG AA minimum)

### Typography
- [ ] Font Inter loaded dari Google Fonts
- [ ] Font weights bervariasi (300-800)
- [ ] Heading hierarchy jelas (h1 > h2 > h3)
- [ ] Body text readable (16px minimum)
- [ ] Line height comfortable (1.5-1.7)

### Spacing
- [ ] Section padding consistent (py-20)
- [ ] Card spacing uniform
- [ ] Text spacing tidak terlalu rapat atau renggang
- [ ] Margin antar elements balanced

### Icons
- [ ] Font Awesome icons loaded
- [ ] Icon size proporsional dengan text
- [ ] Icon colors sesuai theme
- [ ] No broken icons (box dengan X)

### Images
- [ ] Placeholder images loaded
- [ ] Aspect ratio maintained
- [ ] No distortion saat resize
- [ ] Alt text present (accessibility)

---

## ⚙️ Functionality Testing

### Smooth Scroll
- [ ] Click menu link scroll ke section
- [ ] Offset 80px untuk fixed navbar
- [ ] Smooth animation (tidak instant jump)

### Counter Animation
- [ ] Triggered saat stats section masuk viewport
- [ ] Numbers increment smooth
- [ ] Stops di target number
- [ ] Tidak retrigger saat scroll up/down

### Course Tabs
- [ ] Click SMP tab menampilkan SMP courses
- [ ] Click SMA tab menampilkan SMA courses
- [ ] Fade animation smooth
- [ ] Active state visual jelas
- [ ] Previous tab content hidden

### FAQ Accordion
- [ ] Click question untuk toggle
- [ ] Smooth expand/collapse
- [ ] Auto-close other FAQs
- [ ] Chevron rotate animation
- [ ] No layout shift

### Form Interactions (jika ada)
- [ ] Input fields functional
- [ ] Validation messages
- [ ] Submit button behavior
- [ ] Loading state

---

## 🚀 Performance Testing

### Load Time
- [ ] Page loads dalam < 3 detik (good connection)
- [ ] First Contentful Paint < 1.5s
- [ ] Time to Interactive < 3.5s

### Assets
- [ ] Tailwind CDN loaded
- [ ] Font Awesome CDN loaded
- [ ] Google Fonts loaded
- [ ] Custom CSS loaded
- [ ] JavaScript loaded

### Animations
- [ ] Smooth 60fps animations
- [ ] No janky scrolling
- [ ] Transitions tidak lag
- [ ] Hover effects instant

### Memory
- [ ] No memory leaks (check DevTools)
- [ ] Console clear dari errors
- [ ] No excessive repaints

---

## ♿ Accessibility Testing

### Keyboard Navigation
- [ ] Tab key navigates through interactive elements
- [ ] Focus visible (outline atau highlight)
- [ ] Enter/Space activate buttons
- [ ] Escape closes mobile menu
- [ ] No keyboard traps

### Screen Reader
- [ ] Semantic HTML tags (nav, section, footer)
- [ ] Alt text untuk images
- [ ] ARIA labels jika diperlukan
- [ ] Heading hierarchy logical

### Color Contrast
- [ ] Text vs background contrast ratio ≥ 4.5:1
- [ ] Large text ≥ 3:1
- [ ] Button text readable
- [ ] Link text distinguishable

---

## 🌐 Browser Compatibility

### Chrome
- [ ] Layout correct
- [ ] All animations working
- [ ] No console errors

### Firefox
- [ ] Layout correct
- [ ] All animations working
- [ ] No console errors

### Safari
- [ ] Layout correct
- [ ] Backdrop-filter working (or fallback)
- [ ] All animations working

### Edge
- [ ] Layout correct
- [ ] All animations working
- [ ] No console errors

---

## 🐛 Bug Checklist

### Common Issues
- [ ] No horizontal scrollbar
- [ ] No vertical content clipping
- [ ] Images not broken
- [ ] Links tidak dead (kecuali placeholder #)
- [ ] No JavaScript errors di console
- [ ] No CSS conflicts
- [ ] Animations complete (tidak stuck)

### Mobile Specific
- [ ] Touch targets ≥ 44x44px
- [ ] No zoom required untuk read text
- [ ] No horizontal scroll
- [ ] Buttons tapable (tidak terlalu kecil)

---

## 📊 SEO & Meta (untuk production)

- [ ] Title tag descriptive
- [ ] Meta description present
- [ ] Open Graph tags
- [ ] Canonical URL
- [ ] Structured data (Schema.org)
- [ ] Sitemap XML
- [ ] Robots.txt

---

## ✅ Final Checklist

### Pre-Launch
- [ ] All sections reviewed
- [ ] All links tested
- [ ] All animations smooth
- [ ] Responsive di semua breakpoints
- [ ] No console errors
- [ ] Performance acceptable
- [ ] Accessibility compliant
- [ ] Browser compatibility checked

### Documentation
- [ ] README complete
- [ ] Comments di code (jika complex)
- [ ] Asset sources documented
- [ ] Dependencies listed

### Handoff
- [ ] Mockup approved oleh stakeholders
- [ ] Design sistem documented
- [ ] Migration guide ready
- [ ] Todo list clear

---

## 📝 Testing Notes

**Tester Name**: _________________  
**Date**: _________________  
**Browser/Device**: _________________  

**Issues Found**:
```
1. 
2. 
3. 
```

**Recommendations**:
```
1. 
2. 
3. 
```

**Overall Status**: ⭕ Pass / ❌ Fail / ⚠️ Needs Review

---

**Version**: 1.0.0  
**Last Updated**: January 2025  
**Status**: Ready for QA