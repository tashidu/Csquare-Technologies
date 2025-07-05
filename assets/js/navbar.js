/**
 * Beautiful Side Navbar JavaScript
 * Handles sidebar interactions and animations
 */

// IMMEDIATELY disable all animations - before DOM loads
(function() {
    const style = document.createElement('style');
    style.textContent = `
        .nav-loading, .nav-loading::before, .nav-loading::after {
            display: none !important;
            animation: none !important;
        }
        .nav-link { animation: none !important; transition: none !important; }
    `;
    document.head.appendChild(style);
})();

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar functionality
    initializeSidebar();
    setActiveNavigation();
    addResponsiveHandling();
    addFastNavigation();

    // Open sidebar by default on desktop
    if (window.innerWidth > 768) {
        openSidebar();
    }
});

/**
 * Initialize sidebar functionality
 */
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar (only for mobile toggle button)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            toggleSidebar();
        });
    }

    // Close sidebar
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
}

/**
 * Toggle sidebar open/close
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    if (sidebar.classList.contains('active')) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

/**
 * Open sidebar
 */
function openSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.add('active');
    sidebarOverlay.classList.add('active');

    // Add class to main content for desktop
    if (window.innerWidth > 768) {
        mainContent.classList.add('sidebar-open');
    }

    // Prevent body scroll on mobile
    if (window.innerWidth <= 768) {
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close sidebar
 */
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    mainContent.classList.remove('sidebar-open');

    // Restore body scroll
    document.body.style.overflow = '';
}

// Submenu functionality removed for simple flat navigation

/**
 * Set active navigation based on current page
 */
function setActiveNavigation() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && isCurrentPage(currentPath, href)) {
            link.classList.add('active');
        }
    });
}

/**
 * Check if current page matches navigation link
 */
function isCurrentPage(currentPath, linkHref) {
    // Remove base URL and normalize paths
    const normalizedCurrent = currentPath.toLowerCase().replace(/\/$/, '');
    const normalizedLink = linkHref.toLowerCase().replace(/\/$/, '');
    
    // Handle different matching scenarios
    if (normalizedCurrent === normalizedLink) return true;
    if (normalizedCurrent.includes(normalizedLink) && normalizedLink !== '') return true;
    if (normalizedCurrent === '' && normalizedLink.includes('index')) return true;
    
    return false;
}

/**
 * Add breadcrumb navigation
 */
function addBreadcrumb(activePageName) {
    const container = document.querySelector('.container-fluid');
    if (!container || document.querySelector('.breadcrumb-container')) return;
    
    const breadcrumbHtml = `
        <div class="breadcrumb-container">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="${getBaseUrl()}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            ${activePageName}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', breadcrumbHtml);
}

/**
 * Add navigation handling - NO ANIMATIONS AT ALL
 */
function addFastNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        // Remove ALL animations and loading states on click
        link.addEventListener('click', function(e) {
            // Check if clicking the same page - prevent reload
            const currentPath = window.location.pathname;
            const linkHref = this.getAttribute('href');

            if (linkHref && isCurrentPage(currentPath, linkHref)) {
                e.preventDefault(); // Don't reload the same page
                return false;
            }

            // Remove any loading states immediately - NO ANIMATIONS
            document.querySelectorAll('.nav-loading').forEach(el => {
                el.classList.remove('nav-loading');
                el.style.animation = 'none';
                el.style.background = '';
                el.style.transform = '';
            });

            // NO visual effects - just navigate instantly
            this.style.animation = 'none';
            this.style.transition = 'none';
        });

        // Preload on hover for instant navigation
        link.addEventListener('mouseenter', function() {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                const preloadLink = document.createElement('link');
                preloadLink.rel = 'prefetch';
                preloadLink.href = href;
                document.head.appendChild(preloadLink);
            }
        });
    });
}

/**
 * Add responsive handling (optimized)
 */
function addResponsiveHandling() {
    let resizeTimer;

    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            if (window.innerWidth > 768) {
                // Desktop: restore body scroll and remove overlay
                document.body.style.overflow = '';

                // Keep sidebar open on desktop if it was open
                if (sidebar.classList.contains('active')) {
                    mainContent.classList.add('sidebar-open');
                }
            } else {
                // Mobile: remove desktop class
                mainContent.classList.remove('sidebar-open');

                // If sidebar is open on mobile, prevent body scroll
                if (sidebar.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                }
            }
        }, 100); // Reduced from 250ms to 100ms for faster response
    });
}

/**
 * Add scroll effects to navbar
 */
function addScrollEffects() {
    const navbar = document.querySelector('.modern-navbar');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add/remove shadow based on scroll
        if (scrollTop > 10) {
            navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        }
        
        // Hide/show navbar on scroll (optional)
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

/**
 * Play click sound effect (optional)
 */
function playClickSound() {
    // You can add a subtle click sound here if desired
    // const audio = new Audio('assets/sounds/click.mp3');
    // audio.volume = 0.1;
    // audio.play().catch(() => {}); // Ignore errors
}

/**
 * Get base URL helper function
 */
function getBaseUrl() {
    const protocol = window.location.protocol;
    const host = window.location.host;
    const pathname = window.location.pathname;
    const pathArray = pathname.split('/');
    
    // Remove current directory from path
    const basePath = pathArray.slice(0, -1).join('/') + '/';
    return protocol + '//' + host + basePath;
}

/**
 * Add notification badges (example usage)
 */
function addNotificationBadge(elementSelector, count) {
    const element = document.querySelector(elementSelector);
    if (!element || count <= 0) return;
    
    const badge = document.createElement('span');
    badge.className = 'notification-badge';
    badge.textContent = count > 99 ? '99+' : count;
    
    element.style.position = 'relative';
    element.appendChild(badge);
}

/**
 * Smooth scroll to top functionality
 */
function addScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollBtn.className = 'btn btn-primary scroll-to-top';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    `;
    
    document.body.appendChild(scrollBtn);
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    // Smooth scroll to top
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Initialize scroll to top
addScrollToTop();

// Optimize page loading performance
optimizePageLoading();

// Example: Add notification badges
// addNotificationBadge('#reportsDropdown', 3);

/**
 * Optimize page loading - REMOVE ALL ANIMATIONS IMMEDIATELY
 */
function optimizePageLoading() {
    // IMMEDIATELY remove ALL loading states and animations
    document.querySelectorAll('.nav-loading, [class*="loading"], [class*="shimmer"]').forEach(element => {
        element.classList.remove('nav-loading');
        element.style.display = 'none';
        element.style.animation = 'none';
        element.style.background = 'transparent';
        element.style.transform = 'none';
        element.style.transition = 'none';
        element.style.opacity = '1';
    });

    // Remove ALL pseudo-element animations
    const allNavLinks = document.querySelectorAll('.nav-link');
    allNavLinks.forEach(link => {
        link.style.animation = 'none';
        link.style.transition = 'none';
        link.style.transform = 'none';
    });

    // Preload critical navigation pages for instant loading
    const criticalPages = [
        'customer/',
        'item/',
        'reports/invoice_report.php',
        'customer/add.php',
        'item/add.php'
    ];

    criticalPages.forEach(page => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = getBaseUrl() + page;
        document.head.appendChild(link);
    });

    // COMPLETELY DISABLE ALL NAVIGATION ANIMATIONS
    const style = document.createElement('style');
    style.textContent = `
        /* DISABLE ALL NAVBAR ANIMATIONS */
        .nav-loading,
        .nav-loading::before,
        .nav-loading::after,
        .nav-link::before,
        .nav-link::after {
            display: none !important;
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }

        .nav-link,
        .nav-link:hover,
        .nav-link:active,
        .nav-link:focus {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }

        /* Remove shimmer completely */
        *[class*="shimmer"],
        *[class*="loading"] {
            animation: none !important;
        }
    `;
    document.head.appendChild(style);

    // Override global loading functions for navigation
    if (typeof window.showLoading === 'function') {
        window.originalShowLoading = window.showLoading;
        window.showLoading = function() {
            // Skip loading for navigation - always fast
            return;
        };
    }
}
