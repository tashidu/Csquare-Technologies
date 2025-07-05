/**
 * Modern Navbar JavaScript
 * Handles sidebar interactions with smooth animations and modern colors
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Navbar JavaScript loaded');

    // Initialize basic sidebar functionality
    initializeSidebar();
    setActiveNavigation();
    addResponsiveHandling();
    addNavLinkHandlers();

    // Open sidebar by default on desktop
    if (window.innerWidth > 768) {
        openSidebar();
    }

    console.log('✅ Navbar initialization complete');
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
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
}

/**
 * Toggle sidebar open/close
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    
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

    if (sidebar) sidebar.classList.add('active');
    if (sidebarOverlay) sidebarOverlay.classList.add('active');

    // Add class to main content for desktop
    if (window.innerWidth > 768 && mainContent) {
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

    if (sidebar) sidebar.classList.remove('active');
    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    if (mainContent) mainContent.classList.remove('sidebar-open');

    // Restore body scroll
    document.body.style.overflow = '';
}

/**
 * Set active navigation based on current page
 */
function setActiveNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;

    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        
        // Remove any existing active classes
        link.classList.remove('active');
        
        // Check if this is the current page
        if (linkHref && isCurrentPage(currentPath, linkHref)) {
            link.classList.add('active');
            link.style.background = 'rgba(79, 172, 254, 0.2)';
            link.style.borderLeft = '3px solid #4facfe';
            link.style.color = '#ffffff';
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
    
    // Handle root path
    if (normalizedCurrent === '' || normalizedCurrent === '/') {
        return normalizedLink === '' || normalizedLink === '/' || normalizedLink.endsWith('/index.php');
    }
    
    // Check for exact match or if current path starts with link path
    return normalizedCurrent === normalizedLink || 
           normalizedCurrent.startsWith(normalizedLink + '/') ||
           normalizedLink.includes(normalizedCurrent);
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
                // Desktop: ensure sidebar is open
                if (sidebar) sidebar.classList.add('active');
                if (mainContent) mainContent.classList.add('sidebar-open');
                document.body.style.overflow = '';
            } else {
                // Mobile: close sidebar
                closeSidebar();
            }
        }, 250);
    });
}

/**
 * Add navigation link handlers to prevent layout issues
 */
function addNavLinkHandlers() {
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('🔗 Nav link clicked:', this.href);

            // Ensure the link maintains its layout during navigation
            this.style.display = 'flex';
            this.style.alignItems = 'center';
            this.style.flexWrap = 'nowrap';

            // Add a small delay to prevent layout shift
            setTimeout(() => {
                // Navigation will proceed normally
                console.log('✅ Navigation proceeding to:', this.href);
            }, 50);
        });

        // Prevent any layout changes during hover/focus
        link.addEventListener('mouseenter', function() {
            this.style.display = 'flex';
            this.style.alignItems = 'center';
            this.style.flexWrap = 'nowrap';
        });
    });
}

/**
 * Get base URL for the application
 */
function getBaseUrl() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(segment => segment !== '');
    
    // Remove known subdirectories to get base URL
    const knownDirs = ['customer', 'item', 'reports'];
    const filteredSegments = segments.filter(segment => !knownDirs.includes(segment));
    
    return window.location.origin + '/' + (filteredSegments.length > 0 ? filteredSegments.join('/') + '/' : '');
}

// Initialize scroll to top functionality
function addScrollToTop() {
    // Create scroll to top button
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
        transition: all 0.3s ease;
        z-index: 1000;
    `;
    
    document.body.appendChild(scrollBtn);
    
    // Show/hide scroll button based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'flex';
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
    
    // Hover effect
    scrollBtn.addEventListener('mouseenter', () => {
        scrollBtn.style.transform = 'scale(1.1)';
    });
    
    scrollBtn.addEventListener('mouseleave', () => {
        scrollBtn.style.transform = 'scale(1)';
    });
}

// Initialize scroll to top
addScrollToTop();
