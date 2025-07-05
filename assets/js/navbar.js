/**
 * Beautiful Side Navbar JavaScript
 * Handles sidebar interactions and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar functionality
    initializeSidebar();
    setActiveNavigation();
    handleSubmenuToggle();
    addResponsiveHandling();
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

    // Toggle sidebar
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

/**
 * Handle submenu toggle
 */
function handleSubmenuToggle() {
    const submenuToggles = document.querySelectorAll('.submenu-toggle');

    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            const navItem = this.closest('.nav-item');
            const submenu = navItem.querySelector('.submenu');
            const arrow = this.querySelector('.nav-arrow');

            // Close other submenus
            submenuToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherNavItem = otherToggle.closest('.nav-item');
                    const otherSubmenu = otherNavItem.querySelector('.submenu');
                    const otherArrow = otherToggle.querySelector('.nav-arrow');

                    otherSubmenu.classList.remove('active');
                    otherToggle.classList.remove('active');
                    if (otherArrow) {
                        otherArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });

            // Toggle current submenu
            submenu.classList.toggle('active');
            this.classList.toggle('active');

            if (arrow) {
                arrow.style.transform = submenu.classList.contains('active')
                    ? 'rotate(90deg)'
                    : 'rotate(0deg)';
            }
        });
    });
}

/**
 * Set active navigation based on current page
 */
function setActiveNavigation() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link, .submenu-link');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && isCurrentPage(currentPath, href)) {
            link.classList.add('active');

            // If it's a submenu link, also activate parent and show submenu
            if (link.classList.contains('submenu-link')) {
                const navItem = link.closest('.nav-item');
                const parentToggle = navItem.querySelector('.submenu-toggle');
                const submenu = navItem.querySelector('.submenu');
                const arrow = parentToggle.querySelector('.nav-arrow');

                if (parentToggle) {
                    parentToggle.classList.add('active');
                    submenu.classList.add('active');
                    if (arrow) {
                        arrow.style.transform = 'rotate(90deg)';
                    }
                }
            }
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
 * Add responsive handling
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
        }, 250);
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

// Example: Add notification badges
// addNotificationBadge('#reportsDropdown', 3);
