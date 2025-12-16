// ============================================
// SMARTDev Academic Landing Page - JavaScript
// GSAP-Inspired Smooth & Fluid Interactions
// ============================================

document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // SMOOTH SCROLL SETUP
    // ==========================================
    let lastScrollTop = 0;
    let ticking = false;

    // ==========================================
    // Navigation - Smooth Scroll Effect
    // ==========================================
    const nav = document.querySelector("nav");

    function updateNavigation(scrollTop) {
        if (scrollTop > 100) {
            nav.classList.add("scrolled");
        } else {
            nav.classList.remove("scrolled");
        }
    }

    // ==========================================
    // Parallax Effect - Hero Section
    // ==========================================
    const heroImage = document.querySelector(
        "section:first-of-type .container > div > div:last-child",
    );

    function applyParallax(scrollTop) {
        if (heroImage && window.innerWidth > 768) {
            const speed = 0.5;
            const yPos = -(scrollTop * speed);
            heroImage.style.transform = `translateY(${yPos}px)`;
        }
    }

    // ==========================================
    // Optimized Scroll Handler with RAF
    // ==========================================
    window.addEventListener("scroll", function () {
        lastScrollTop = window.pageYOffset;

        if (!ticking) {
            window.requestAnimationFrame(function () {
                updateNavigation(lastScrollTop);
                applyParallax(lastScrollTop);
                updateScrollProgress(lastScrollTop);
                updateStickyCTA(lastScrollTop);
                updateBackToTop(lastScrollTop);
                ticking = false;
            });

            ticking = true;
        }
    });

    // ==========================================
    // Mobile Menu Toggle
    // ==========================================
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const mobileMenu = document.getElementById("mobileMenu");

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener("click", () => {
            const isHidden = mobileMenu.classList.contains("hidden");
            mobileMenu.classList.toggle("hidden");

            const icon = mobileMenuBtn.querySelector("i");
            if (isHidden) {
                icon.classList.remove("fa-bars");
                icon.classList.add("fa-times");
            } else {
                icon.classList.remove("fa-times");
                icon.classList.add("fa-bars");
            }
        });

        // Close mobile menu when clicking a link
        const mobileLinks = mobileMenu.querySelectorAll("a");
        mobileLinks.forEach((link) => {
            link.addEventListener("click", () => {
                mobileMenu.classList.add("hidden");
                const icon = mobileMenuBtn.querySelector("i");
                icon.classList.remove("fa-times");
                icon.classList.add("fa-bars");
            });
        });
    }

    // ==========================================
    // Smooth Scroll for Anchor Links
    // ==========================================
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            const href = this.getAttribute("href");

            if (
                href === "#" ||
                href === "#register" ||
                href === "#login" ||
                href === "#contact"
            ) {
                e.preventDefault();
                return;
            }

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offsetTop = target.offsetTop - 80;

                // Smooth scroll with custom easing
                smoothScrollTo(offsetTop, 800);
            }
        });
    });

    // Custom smooth scroll function with easing
    function smoothScrollTo(targetPosition, duration) {
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function easeInOutCubic(t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        }

        function animation(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / duration, 1);
            const ease = easeInOutCubic(progress);

            window.scrollTo(0, startPosition + distance * ease);

            if (timeElapsed < duration) {
                requestAnimationFrame(animation);
            }
        }

        requestAnimationFrame(animation);
    }

    // ==========================================
    // Counter Animation with Smooth Easing
    // ==========================================
    const counters = document.querySelectorAll(".counter");
    let countersAnimated = false;

    // Custom easing functions
    function easeOutExpo(x) {
        return x === 1 ? 1 : 1 - Math.pow(2, -10 * x);
    }

    function easeOutQuart(x) {
        return 1 - Math.pow(1 - x, 4);
    }

    function animateCounters() {
        if (countersAnimated) return;

        counters.forEach((counter, index) => {
            const target = +counter.getAttribute("data-target");
            const duration = 3500;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = easeOutExpo(progress);
                const current = Math.floor(eased * target);

                counter.textContent = current.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString() + "+";
                }
            }

            // Stagger the animation start
            setTimeout(() => {
                requestAnimationFrame(updateCounter);
            }, index * 100);
        });

        countersAnimated = true;
    }

    // ==========================================
    // Intersection Observer for Scroll Reveals
    // ==========================================
    const observerOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px",
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("revealed");
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    }, observerOptions);

    // Stats section observer (for counter animation)
    const statsSection = document.getElementById("stats");
    if (statsSection) {
        const statsObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        statsObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.3 },
        );

        statsObserver.observe(statsSection);
    }

    // Observe role cards
    const roleCards = document.querySelectorAll("#roles .bg-white");
    roleCards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(40px)";
        revealObserver.observe(card);
    });

    // Observe feature cards
    const featureCards = document.querySelectorAll("#features .bg-gray-50");
    featureCards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(40px)";
        revealObserver.observe(card);
    });

    // Observe course cards
    const courseCards = document.querySelectorAll("#courses .bg-white");
    courseCards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(40px)";
        revealObserver.observe(card);
    });

    // ==========================================
    // Course Tabs with Smooth Transition
    // ==========================================
    const courseTabs = document.querySelectorAll(".course-tab");
    const courseContents = document.querySelectorAll(".course-content");

    courseTabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            const targetTab = tab.getAttribute("data-tab");

            // Remove active from all
            courseTabs.forEach((t) => t.classList.remove("active"));
            courseContents.forEach((c) => c.classList.remove("active"));

            // Add active to clicked
            tab.classList.add("active");
            const targetContent = document.getElementById(
                `${targetTab}-courses`,
            );

            // Smooth transition
            setTimeout(() => {
                targetContent.classList.add("active");
            }, 50);
        });
    });

    // ==========================================
    // FAQ Accordion with Smooth Animation
    // ==========================================
    const faqItems = document.querySelectorAll(".faq-item");

    faqItems.forEach((item) => {
        const question = item.querySelector(".faq-question");
        const answer = item.querySelector(".faq-answer");

        question.addEventListener("click", () => {
            const isActive = item.classList.contains("active");

            // Close all items with smooth animation
            faqItems.forEach((faq) => {
                faq.classList.remove("active");
                const faqAnswer = faq.querySelector(".faq-answer");
                faqAnswer.classList.add("hidden");
            });

            // Open clicked item if it wasn't active
            if (!isActive) {
                item.classList.add("active");
                answer.classList.remove("hidden");
            }
        });
    });

    // Open first FAQ by default
    if (faqItems.length > 0) {
        faqItems[0].classList.add("active");
        faqItems[0].querySelector(".faq-answer").classList.remove("hidden");
    }

    // ==========================================
    // Sticky CTA Bar
    // ==========================================
    const stickyCTA = document.getElementById("stickyCTA");
    let ctaShown = false;

    function updateStickyCTA(scrollPosition) {
        if (!stickyCTA) return;

        const windowHeight = window.innerHeight;
        const footer = document.querySelector("footer");

        if (scrollPosition > windowHeight && !ctaShown) {
            stickyCTA.classList.add("show");
            ctaShown = true;
        } else if (scrollPosition <= windowHeight && ctaShown) {
            stickyCTA.classList.remove("show");
            ctaShown = false;
        }

        // Hide when near footer
        if (footer) {
            const footerOffset = footer.offsetTop;
            if (scrollPosition + windowHeight >= footerOffset - 100) {
                stickyCTA.classList.remove("show");
            } else if (scrollPosition > windowHeight) {
                stickyCTA.classList.add("show");
            }
        }
    }

    // ==========================================
    // Scroll Progress Bar
    // ==========================================
    const progressBar = document.createElement("div");
    progressBar.id = "scrollProgress";
    document.body.appendChild(progressBar);

    function updateScrollProgress(scrollTop) {
        const docHeight =
            document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + "%";
    }

    // ==========================================
    // Back to Top Button
    // ==========================================
    const backToTopBtn = document.createElement("button");
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className =
        "fixed bottom-6 right-6 bg-gradient-to-r from-primary to-secondary text-white w-12 h-12 rounded-full shadow-lg opacity-0 pointer-events-none transition-all duration-300 hover:shadow-2xl z-40";
    backToTopBtn.id = "backToTop";
    document.body.appendChild(backToTopBtn);

    function updateBackToTop(scrollTop) {
        if (scrollTop > 300) {
            backToTopBtn.style.opacity = "1";
            backToTopBtn.style.pointerEvents = "auto";
        } else {
            backToTopBtn.style.opacity = "0";
            backToTopBtn.style.pointerEvents = "none";
        }
    }

    backToTopBtn.addEventListener("click", () => {
        smoothScrollTo(0, 1000);
    });

    // ==========================================
    // Magnetic Button Effect (Subtle)
    // ==========================================
    const buttons = document.querySelectorAll(
        'a[class*="bg-gradient"], button[class*="bg-"]',
    );

    buttons.forEach((button) => {
        button.addEventListener("mouseenter", function () {
            this.style.transition = "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)";
        });

        button.addEventListener("mousemove", function (e) {
            if (window.innerWidth < 768) return; // Disable on mobile

            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            // Subtle magnetic effect
            const moveX = x * 0.1;
            const moveY = y * 0.1;

            this.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.01)`;
        });

        button.addEventListener("mouseleave", function () {
            this.style.transition = "all 0.6s cubic-bezier(0.4, 0, 0.2, 1)";
            this.style.transform = "";
        });
    });

    // ==========================================
    // Card Tilt Effect (Subtle)
    // ==========================================
    const tiltCards = document.querySelectorAll(
        "#roles .bg-white, #courses .bg-white",
    );

    tiltCards.forEach((card) => {
        card.addEventListener("mouseenter", function () {
            this.style.transition = "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)";
        });

        card.addEventListener("mousemove", function (e) {
            if (window.innerWidth < 768) return;

            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (y - centerY) / 30;
            const rotateY = (centerX - x) / 30;

            this.style.transform = `
                perspective(1000px)
                rotateX(${-rotateX}deg)
                rotateY(${rotateY}deg)
                translateY(-8px)
                scale(1.01)
            `;
        });

        card.addEventListener("mouseleave", function () {
            this.style.transition = "all 0.6s cubic-bezier(0.4, 0, 0.2, 1)";
            this.style.transform = "";
        });
    });

    // ==========================================
    // Lazy Load Images
    // ==========================================
    const images = document.querySelectorAll("img[data-src]");
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add("fade-in");
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach((img) => imageObserver.observe(img));

    // ==========================================
    // Toast Notification System
    // ==========================================
    function showToast(message, type = "info") {
        const toast = document.createElement("div");
        const colors = {
            success: "bg-green-500",
            error: "bg-red-500",
            warning: "bg-yellow-500",
            info: "bg-blue-500",
        };

        const icons = {
            success: "check-circle",
            error: "exclamation-circle",
            warning: "exclamation-triangle",
            info: "info-circle",
        };

        toast.className = `fixed top-20 right-6 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-all duration-500`;
        toast.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-${icons[type]}"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Slide in with smooth animation
        requestAnimationFrame(() => {
            toast.style.transform = "translateX(0)";
        });

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = "translateX(400px)";
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 500);
        }, 3000);
    }

    window.showToast = showToast;

    // ==========================================
    // Form Validation
    // ==========================================
    const forms = document.querySelectorAll("form");
    forms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
                submitBtn.disabled = true;

                // Simulate API call
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showToast("Form berhasil dikirim!", "success");
                }, 2000);
            }
        });
    });

    // ==========================================
    // Keyboard Navigation
    // ==========================================
    document.addEventListener("keydown", (e) => {
        // Close mobile menu on Escape
        if (
            e.key === "Escape" &&
            mobileMenu &&
            !mobileMenu.classList.contains("hidden")
        ) {
            mobileMenu.classList.add("hidden");
            const icon = mobileMenuBtn.querySelector("i");
            icon.classList.remove("fa-times");
            icon.classList.add("fa-bars");
        }

        // Scroll to top on Home key
        if (e.key === "Home" && e.ctrlKey) {
            e.preventDefault();
            smoothScrollTo(0, 1000);
        }

        // Scroll to bottom on End key
        if (e.key === "End" && e.ctrlKey) {
            e.preventDefault();
            const docHeight = document.documentElement.scrollHeight;
            smoothScrollTo(docHeight, 1000);
        }
    });

    // ==========================================
    // Analytics Event Tracking
    // ==========================================
    function trackEvent(category, action, label) {
        console.log(`Event: ${category} - ${action} - ${label}`);
        // Integrate with GA4, GTM, etc.
        // if (typeof gtag !== 'undefined') {
        //     gtag('event', action, {
        //         'event_category': category,
        //         'event_label': label
        //     });
        // }
    }

    // Track CTA clicks
    document.querySelectorAll('a[href="#register"]').forEach((btn) => {
        btn.addEventListener("click", () => {
            trackEvent("CTA", "click", "Register Button");
        });
    });

    document.querySelectorAll('a[href="#login"]').forEach((btn) => {
        btn.addEventListener("click", () => {
            trackEvent("CTA", "click", "Login Button");
        });
    });

    // ==========================================
    // Performance Monitoring
    // ==========================================
    if ("performance" in window) {
        window.addEventListener("load", () => {
            const perfData = window.performance.timing;
            const pageLoadTime =
                perfData.loadEventEnd - perfData.navigationStart;
            console.log(
                `%c⚡ Page Load Time: ${pageLoadTime}ms`,
                "color: #28a745; font-weight: bold;",
            );

            // Report FCP (First Contentful Paint)
            if ("PerformanceObserver" in window) {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.name === "first-contentful-paint") {
                            console.log(
                                `%c🎨 First Contentful Paint: ${Math.round(entry.startTime)}ms`,
                                "color: #667eea; font-weight: bold;",
                            );
                        }
                    }
                });
                observer.observe({ entryTypes: ["paint"] });
            }
        });
    }

    // ==========================================
    // Cursor Position Effect (Hero Only)
    // ==========================================
    const heroSection = document.querySelector("section:first-of-type");
    if (heroSection && window.innerWidth > 1024) {
        heroSection.addEventListener("mousemove", (e) => {
            const rect = heroSection.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Create subtle light effect following cursor
            const percentX = (x / rect.width) * 100;
            const percentY = (y / rect.height) * 100;

            heroSection.style.backgroundImage = `
                radial-gradient(circle at ${percentX}% ${percentY}%, rgba(255,255,255,0.1) 0%, transparent 50%),
                linear-gradient(-45deg, #667eea, #764ba2, #667eea, #5568d3)
            `;
        });
    }

    // ==========================================
    // Console Welcome Message
    // ==========================================
    console.log(
        "%c🎓 SMARTDev Academic",
        "font-size: 24px; font-weight: bold; color: #667eea; text-shadow: 2px 2px 4px rgba(102,126,234,0.3);",
    );
    console.log(
        "%cWelcome to SMARTDev Academic LMS!",
        "font-size: 14px; color: #764ba2; font-weight: 600;",
    );
    console.log(
        "%c✨ GSAP-Inspired Smooth Animations",
        "font-size: 12px; color: #28a745; font-weight: bold;",
    );
    console.log(
        "%cBuilt with ❤️ using Laravel 12, Tailwind CSS 4, and Vite",
        "font-size: 11px; color: #6b7280;",
    );

    console.log(
        "%c✅ All systems initialized successfully!",
        "color: #28a745; font-weight: bold; font-size: 13px; margin-top: 8px;",
    );

    // ==========================================
    // Initialize Complete
    // ==========================================
    document.body.classList.add("loaded");
});

// ==========================================
// Utility Functions
// ==========================================

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
}
