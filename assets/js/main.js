/**
 * Sadhav Offshore - Main JavaScript File
 *
 * Handles:
 * 1. Mobile Navigation Toggle
 * 2. Sticky Header Effect
 * 3. Back to Top Button
 * 4. Dynamic Footer Year
 * 5. Testimonial Slider
 * 6. AJAX Form Submission (Contact, Jobs, RFQ)
 * 7. Lazy Image Loading
 * 8. Project Filtering
 * 9. CSRF Token Fetching
 * 10. Scroll-triggered Animations
 * 11. Animated Stat Counters
 */

document.addEventListener('DOMContentLoaded', () => {

    // Initialize all site-wide functionalities
    initMobileNav();
    initStickyHeader();
    initBackToTop();
    initFooterYear();
    initTestimonialSlider();
    initAjaxForms();
    initLazyLoad();
    initProjectFilters();
    fetchAndInjectCsrfToken();
    initScrollAnimations();
    initStatCounters();
    initServiceCarousel();
    
    // Initialize simple team carousel for mobile
    function initTeamCarousel() {
        if (window.innerWidth < 768) {
            const carousel = document.getElementById('team-carousel');
            if (!carousel) return;
            
            const track = carousel.querySelector('.team-carousel-track');
            const items = carousel.querySelectorAll('.item');
            const dots = carousel.querySelectorAll('.team-dot');
            const prevBtn = document.getElementById('team-prev');
            const nextBtn = document.getElementById('team-next');
            
            let currentIndex = 0;
            
            function showSlide(index) {
                const translateX = -index * 25;
                track.style.transform = `translateX(${translateX}%)`;
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === index);
                });
            }
            
            function nextSlide() {
                currentIndex = (currentIndex + 1) % items.length;
                showSlide(currentIndex);
            }
            
            function prevSlide() {
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                showSlide(currentIndex);
            }
            
            if (nextBtn) nextBtn.addEventListener('click', nextSlide);
            if (prevBtn) prevBtn.addEventListener('click', prevSlide);
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentIndex = index;
                    showSlide(currentIndex);
                });
            });
            
            // Touch support
            let startX = 0;
            carousel.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            });
            
            carousel.addEventListener('touchend', (e) => {
                const endX = e.changedTouches[0].clientX;
                const diff = startX - endX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) nextSlide();
                    else prevSlide();
                }
            });
        }
    }
    
    initTeamCarousel();
    
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });
});

/** 1. Mobile Navigation */
function initMobileNav() {
    const nav = document.querySelector('.site-header__nav');
    const menuToggle = document.querySelector('.site-header__menu-toggle');
    const closeButton = document.querySelector('.site-header__close-menu');
    const menuWrapper = document.querySelector('.site-header__menu-wrapper');
    const menu = document.querySelector('.site-header__menu');
    
    if (menuToggle && nav && menuWrapper && closeButton) {
        // Toggle mobile menu
        const toggleMenu = (isOpen) => {
            if (typeof isOpen === 'undefined') {
                isOpen = !nav.classList.contains('is-open');
            }
            
            nav.classList.toggle('is-open', isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
            
            // Toggle body class for potential other styling needs
            if (isOpen) {
                document.body.classList.add('menu-open');
            } else {
                document.body.classList.remove('menu-open');
            }
        };

        // Toggle on hamburger button click
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        // Close on close button click
        closeButton.addEventListener('click', () => {
            toggleMenu(false);
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (nav.classList.contains('is-open') && 
                !menuWrapper.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                toggleMenu(false);
            }
        });

        // Close menu when clicking on a link
        const menuLinks = menu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                toggleMenu(false);
            });
        });

        // Close menu on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                toggleMenu(false);
            }
        });
    }
}

/** 2. Sticky Header */
function initStickyHeader() {
    const header = document.getElementById('site-header');
    if (header) {
        const observer = new IntersectionObserver(
            ([e]) => e.target.classList.toggle('is-scrolled', e.intersectionRatio < 1),
            { threshold: [1] }
        );
        observer.observe(header);
    }
}

/** 3. Back to Top Button */
function initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    
    if (!backToTop) return;
    
    // Show/hide button on scroll
    const toggleBackToTop = () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    };
    
    // Smooth scroll to top
    const scrollToTop = (e) => {
        if (e) e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };
    
    // Initialize
    window.addEventListener('scroll', toggleBackToTop, { passive: true });
    backToTop.addEventListener('click', scrollToTop, { passive: true });
    
    // Check initial position
    toggleBackToTop();
    
    // Add keyboard accessibility
    backToTop.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            scrollToTop();
        }
    });
}

/** 4. Dynamic Footer Year */
function initFooterYear() {
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
}

/** 5. Testimonial Slider */
function initTestimonialSlider() {
    const slider = document.querySelector('.testimonial-slider');
    if (!slider) return;

    const slidesContainer = slider.querySelector('.testimonial-slider__slides');
    const slides = slider.querySelectorAll('.testimonial-slider__slide');
    const prevButton = slider.querySelector('.testimonial-slider__prev');
    const nextButton = slider.querySelector('.testimonial-slider__next');
    
    // Clone first and last slides for infinite loop
    const firstClone = slides[0].cloneNode(true);
    const lastClone = slides[slides.length - 1].cloneNode(true);
    
    firstClone.classList.add('cloned');
    lastClone.classList.add('cloned');
    
    slidesContainer.insertBefore(lastClone, slides[0]);
    slidesContainer.appendChild(firstClone);
    
    let currentIndex = 1;
    const totalSlides = slides.length + 2; // +2 for cloned slides
    
    // Set initial position
    slidesContainer.style.transform = `translateX(-100%)`;
    slidesContainer.style.transition = 'transform 0.5s ease-in-out';

    function goToSlide(index) {
        // Disable transition for instant jump to cloned slide
        if (index === 0 && currentIndex === totalSlides - 2) {
            slidesContainer.style.transition = 'none';
            currentIndex = totalSlides - 2;
            slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
            // Force reflow
            void slidesContainer.offsetWidth;
            slidesContainer.style.transition = 'transform 0.5s ease-in-out';
        } else if (index === totalSlides - 1 && currentIndex === 1) {
            slidesContainer.style.transition = 'none';
            currentIndex = 1;
            slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
            // Force reflow
            void slidesContainer.offsetWidth;
            slidesContainer.style.transition = 'transform 0.5s ease-in-out';
        }
        
        // Update slide position with transition
        currentIndex = index;
        slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Reset position if at the end
        if (currentIndex === totalSlides - 1) {
            setTimeout(() => {
                slidesContainer.style.transition = 'none';
                currentIndex = 1;
                slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
                // Force reflow
                void slidesContainer.offsetWidth;
                slidesContainer.style.transition = 'transform 0.5s ease-in-out';
            }, 500);
        } else if (currentIndex === 0) {
            setTimeout(() => {
                slidesContainer.style.transition = 'none';
                currentIndex = totalSlides - 2;
                slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
                // Force reflow
                void slidesContainer.offsetWidth;
                slidesContainer.style.transition = 'transform 0.5s ease-in-out';
            }, 500);
        }
    }

    // Add event listeners for navigation
    if (prevButton && nextButton) {
        prevButton.addEventListener('click', () => {
            goToSlide(currentIndex - 1);
        });
        nextButton.addEventListener('click', () => {
            goToSlide(currentIndex + 1);
        });
    }

    // Auto-advance slides every 5 seconds
    let slideInterval = setInterval(() => {
        goToSlide(currentIndex + 1);
    }, 5000);

    // Pause auto-advance on hover
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });

    slider.addEventListener('mouseleave', () => {
        slideInterval = setInterval(() => {
            goToSlide(currentIndex + 1);
        }, 5000);
    });
}

/** 6. AJAX Form Submission */
function initAjaxForms() {
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => handleFormSubmit(e, 'forms/contact.php', '#form-notifications'));
    }

    const jobForm = document.getElementById('job-apply-form');
    if (jobForm) {
        jobForm.addEventListener('submit', (e) => handleFormSubmit(e, 'forms/job-apply.php', '#job-form-notifications'));
    }

    const rfqForm = document.getElementById('rfq-form');
    if (rfqForm) {
        rfqForm.addEventListener('submit', (e) => handleFormSubmit(e, 'forms/rfq.php', '#rfq-form-notifications'));
    }
}

async function handleFormSubmit(e, endpoint, notificationsSelector) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const notifications = document.querySelector(notificationsSelector);
    const submitButton = form.querySelector('button[type="submit"]');

    notifications.innerHTML = '';
    clearErrors(form);

    if (!validateForm(form)) {
        return;
    }

    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();

        if (result.status === 'ok') {
            displayMessage('success', result.message || 'Your submission was successful!', notifications);
            form.reset();
            fetchAndInjectCsrfToken(); // Refresh token after successful submission
        } else {
            if (result.errors) {
                Object.keys(result.errors).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) showError(field.closest('.form__group'), result.errors[key]);
                });
                 displayMessage('error', result.message || 'Please correct the errors below.', notifications);
            } else {
                 displayMessage('error', result.message || 'An unexpected error occurred. Please try again.', notifications);
            }
            if (result.refresh_token) {
                fetchAndInjectCsrfToken(); // Refresh token on validation failure
            }
        }
    } catch (error) {
        displayMessage('error', 'A network error occurred. Please check your connection and try again.', notifications);
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
    }
}


/** 7. Lazy Image Loading */
function initLazyLoad() {
    const lazyImages = document.querySelectorAll('.lazy-image');
    if ('IntersectionObserver' in window) {
        let lazyImageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy-image');
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });
        lazyImages.forEach(lazyImage => lazyImageObserver.observe(lazyImage));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(lazyImage => {
            lazyImage.src = lazyImage.dataset.src;
        });
    }
}

/** 8. Project Filtering */
function initProjectFilters() {
    const filterForm = document.getElementById('project-filters');
    if (!filterForm) return;

    const projectItems = document.querySelectorAll('.project-item');
    const filterService = document.getElementById('filter-service');
    const filterYear = document.getElementById('filter-year');

    function filterProjects() {
        const serviceValue = filterService.value;
        const yearValue = filterYear.value;
        
        // Update URL
        const params = new URLSearchParams(window.location.search);
        if (serviceValue) params.set('service', serviceValue); else params.delete('service');
        if (yearValue) params.set('year', yearValue); else params.delete('year');
        window.history.replaceState({}, '', `${window.location.pathname}?${params}`);

        projectItems.forEach(item => {
            const itemService = item.dataset.service;
            const itemYear = item.dataset.year;
            const serviceMatch = (serviceValue === '' || itemService === serviceValue);
            const yearMatch = (yearValue === '' || itemYear === yearValue);

            if (serviceMatch && yearMatch) {
                item.classList.remove('is-hidden');
            } else {
                item.classList.add('is-hidden');
            }
        });
    }
    
    // Set initial state from URL
    const initialParams = new URLSearchParams(window.location.search);
    if (initialParams.has('service')) filterService.value = initialParams.get('service');
    if (initialParams.has('year')) filterYear.value = initialParams.get('year');
    filterProjects();

    filterForm.addEventListener('change', filterProjects);
    filterForm.addEventListener('submit', e => e.preventDefault());
}

/** 9. CSRF Token Fetching */
async function fetchAndInjectCsrfToken() {
    const forms = document.querySelectorAll('form.form');
    if (forms.length === 0) return;

    try {
        const response = await fetch('forms/token.php');
        const data = await response.json();
        if (data.token) {
            forms.forEach(form => {
                const tokenInput = form.querySelector('input[name="csrf_token"]');
                if (tokenInput) {
                    tokenInput.value = data.token;
                }
            });
        }
    } catch (error) {
        console.error('Could not fetch CSRF token:', error);
    }
}

/** 10. Scroll-triggered Animations */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach(el => observer.observe(el));
    } else {
        // Fallback for older browsers
        animatedElements.forEach(el => el.classList.add('is-visible'));
    }
}

/** 11. Animated Stat Counters */
function initStatCounters() {
    // Target elements with data-target attribute that are direct children of stat items
    const counters = document.querySelectorAll('[data-target]');
    if (counters.length === 0) return;

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const targetValue = counter.getAttribute('data-target');
                const isNumeric = /^\d+\+?$/.test(targetValue);

                // Create h2 element if it doesn't exist
                let h2 = counter.querySelector('h2');
                if (!h2) {
                    h2 = document.createElement('h2');
                    counter.innerHTML = ''; // Clear existing content
                    counter.appendChild(h2);
                }

                if (isNumeric) {
                    // Handle both numbers and numbers with '+' sign
                    const hasPlus = targetValue.includes('+');
                    const target = parseInt(targetValue, 10);
                    let current = 0;
                    const duration = 2000; // 2 seconds
                    const startTime = performance.now();

                    const updateCounter = (timestamp) => {
                        const elapsed = timestamp - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        current = Math.min(target * progress, target);
                        h2.textContent = Math.floor(current) + (hasPlus ? '+' : '');
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            h2.textContent = target + (hasPlus ? '+' : '');
                        }
                    };
                    
                    requestAnimationFrame(updateCounter);
                } else {
                    h2.textContent = targetValue;
                }
                
                observer.unobserve(counter);
            }
        });
    }, { 
        threshold: 0.8,
        rootMargin: '0px 0px -50px 0px' // Start animation a bit before element comes into view
    });

    counters.forEach(counter => {
        // Create h2 for the initial zero value
        const h2 = document.createElement('h2');
        h2.textContent = '0' + (counter.getAttribute('data-target').includes('+') ? '+' : '');
        counter.innerHTML = ''; // Clear existing content
        counter.appendChild(h2);
        
        // Store the original text to restore if needed
        counter.setAttribute('data-original', h2.textContent);
        observer.observe(counter);
    });
}

/** 12. Service Carousel */
function initServiceCarousel() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.owlCarousel !== 'undefined') {
        // Destroy any existing carousel instances
        if ($.fn.owlCarousel) {
            const $carousel = $('#service-carousel');
            if ($carousel.hasClass('owl-loaded')) {
                $carousel.trigger('destroy.owl.carousel').removeClass('owl-loaded');
            }
        }

        $('#service-carousel').owlCarousel({
            loop: true,
            margin: 15,
            nav: false,
            dots: true,
            dotsEach: true,
            lazyLoad: false,
            autoplay: true,
            center: false,
            autoWidth: false,
            responsiveClass: true,
            touchDrag: true,
            pullDrag: true,
            freeDrag: false,
            mouseDrag: true,
            dotClass: 'owl-dot',
            dotsClass: 'owl-dots',
            responsive: {
                0: {
                    items: 1,
                    margin: 10,
                    stagePadding: 15,
                    nav: false,
                    dots: true,
                    touchDrag: true,
                    pullDrag: true,
                    freeDrag: false
                },
                480: {
                    items: 1,
                    margin: 10,
                    stagePadding: 20,
                    nav: false,
                    dots: true
                },
                768: {
                    items: 2,
                    margin: 15,
                    stagePadding: 20,
                    nav: false,
                    dots: true
                },
                992: {
                    items: 3,
                    margin: 20,
                    stagePadding: 15,
                    nav: true,
                    dots: true,
                    center: false,
                    loop: false
                }
            },
            onInitialized: function() {
                // Force refresh to fix any rendering issues
                this.refresh();
                
                // Function to show dots
                const showDots = () => {
                    const $dots = $('.owl-dots');
                    if ($dots.length) {
                        $dots.css({
                            'display': 'block',
                            'opacity': '1',
                            'visibility': 'visible'
                        }).addClass('owl-dots-visible');
                        
                        // Ensure dots have proper classes
                        $dots.find('.owl-dot').first().addClass('active');
                    }
                };
                
                // Show dots immediately
                showDots();
                
                // Show dots again after a short delay to ensure they stay visible
                setTimeout(showDots, 100);
            }
        });
    }
}


// --- Helper Functions ---

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        const formGroup = field.closest('.form__group');
        let errorMessage = '';
        if (!field.value.trim()) {
            errorMessage = `${field.labels[0].textContent} is required.`;
        } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            errorMessage = 'Please enter a valid email address.';
        } else if (field.type === 'file') {
            const maxFileSize = 10 * 1024 * 1024; // 10MB
            if (field.files.length > 0 && field.files[0].size > maxFileSize) {
                errorMessage = `File size cannot exceed 10MB.`;
            }
        }
        if (errorMessage) {
            showError(formGroup, errorMessage);
            isValid = false;
        }
    });
    return isValid;
}

function showError(formGroup, message) {
    if (!formGroup) return;
    formGroup.classList.add('has-error');
    const errorContainer = formGroup.querySelector('.form__error-message');
    if (errorContainer) errorContainer.textContent = message;
}

function clearErrors(form) {
    form.querySelectorAll('.has-error').forEach(group => {
        group.classList.remove('has-error');
        const errorContainer = group.querySelector('.form__error-message');
        if (errorContainer) errorContainer.textContent = '';
    });
}

function displayMessage(type, message, container) {
    container.innerHTML = `<div class="message ${type}">${message}</div>`;
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


// navbar js start 
$(document).ready(function () {
    function handleDropdowns() {
        if ($(window).width() >= 992) {
            // Desktop: hover to open
            $(".dropdown").off("mouseenter mouseleave click"); // clear old bindings
            $(".dropdown").hover(
                function () {
                    $(".dropdown-menu").not($(this).find(".dropdown-menu")).slideUp(150);
                    $(this).find(".dropdown-menu").stop(true, true).slideDown(150);
                },
                function () {
                    $(this).find(".dropdown-menu").stop(true, true).slideUp(150);
                }
            );
        } else {
            // Mobile: click to open/close
            $(".dropdown").off("mouseenter mouseleave"); // remove hover
            $(".dropdown > a").off("click").on("click", function (e) {
                e.preventDefault(); // stop navigation on first tap
                let $menu = $(this).next(".dropdown-menu");

                // close other open menus
                $(".dropdown-menu").not($menu).slideUp(150);

                // toggle this one
                $menu.stop(true, true).slideToggle(150);
            });
        }
    }

    // Run on load + resize
    handleDropdowns();
    $(window).on("resize", handleDropdowns);

    // Custom toggler for mobile collapse
    $(".navbar-toggler").off("click").on("click", function () {
        $("#navbarNavDropdown").slideToggle();
    });
});



window.addEventListener("scroll", function() {
  const navbar = document.querySelector(".navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});


// navbar js end

