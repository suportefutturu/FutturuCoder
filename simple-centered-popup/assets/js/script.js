/**
 * Simple Centered Popup - Frontend JavaScript
 * Version 2.0
 * 
 * Handles popup display, animations, cookie-based frequency control,
 * and accessibility features.
 *
 * @package Simple_Centered_Popup
 */

(function() {
    'use strict';

    // Configuration from WordPress
    const config = window.scpConfig || {};
    const cssVars = config.cssVars || {};

    // DOM Elements
    let overlay = null;
    let popup = null;
    let closeButtons = [];

    /**
     * Set CSS custom properties dynamically
     */
    function setCSSVariables() {
        if (!overlay) return;
        
        Object.entries(cssVars).forEach(([property, value]) => {
            overlay.style.setProperty(property, value);
        });
    }

    /**
     * Initialize the popup
     */
    function init() {
        // Get elements
        overlay = document.getElementById('scp-popup-overlay');
        if (!overlay) return;

        popup = overlay.querySelector('.scp-popup');
        if (!popup) return;

        // Get all close buttons
        closeButtons = Array.from(overlay.querySelectorAll('.scp-close-btn, .scp-close-btn-action'));

        // Set CSS variables
        setCSSVariables();

        // Bind events
        bindEvents();

        // Check if should show
        if (config.autoOpen && shouldShowPopup()) {
            setTimeout(openPopup, config.delay || 1000);
        }
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Close button clicks
        closeButtons.forEach(btn => {
            btn.addEventListener('click', closePopup);
        });

        // Overlay click (close on outside click)
        overlay.addEventListener('click', handleOverlayClick);

        // ESC key to close
        document.addEventListener('keydown', handleKeydown);

        // Window resize for responsive adjustments
        window.addEventListener('resize', debounce(handleResize, 250));
    }

    /**
     * Handle overlay click
     * @param {Event} e - Click event
     */
    function handleOverlayClick(e) {
        if (e.target === overlay || e.target.classList.contains('scp-overlay')) {
            closePopup();
        }
    }

    /**
     * Handle keyboard events
     * @param {KeyboardEvent} e - Keyboard event
     */
    function handleKeydown(e) {
        if (e.key === 'Escape' && isActive()) {
            closePopup();
        }
    }

    /**
     * Handle window resize
     */
    function handleResize() {
        // Recalculate positions if needed
        if (isActive()) {
            // Could add logic here for responsive adjustments
        }
    }

    /**
     * Debounce utility function
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in ms
     * @returns {Function}
     */
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

    /**
     * Check if popup should be shown based on cookies/localStorage
     * @returns {boolean}
     */
    function shouldShowPopup() {
        const frequencyDays = config.frequencyDays || 7;
        const cookieName = config.cookieName || 'scp_popup_shown';
        
        // If frequency is 0, always show
        if (frequencyDays === 0) {
            return true;
        }

        const lastShown = localStorage.getItem(cookieName);
        
        if (!lastShown) {
            return true;
        }

        const lastShownDate = new Date(parseInt(lastShown, 10));
        const now = new Date();
        const diffTime = Math.abs(now - lastShownDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        return diffDays >= frequencyDays;
    }

    /**
     * Mark popup as shown (set cookie/localStorage)
     */
    function markAsShown() {
        const cookieName = config.cookieName || 'scp_popup_shown';
        localStorage.setItem(cookieName, Date.now().toString());
    }

    /**
     * Check if popup is currently active/visible
     * @returns {boolean}
     */
    function isActive() {
        return overlay && overlay.classList.contains('active');
    }

    /**
     * Open the popup
     */
    function openPopup() {
        if (!overlay || !popup) return;

        // Add active class to trigger animation
        overlay.classList.add('active');
        
        // Trap focus inside popup for accessibility
        trapFocus();
        
        // Mark as shown
        markAsShown();

        // Dispatch custom event
        dispatchCustomEvent('opened');
    }

    /**
     * Close the popup
     */
    function closePopup() {
        if (!overlay || !popup) return;

        // Remove active class to trigger closing animation
        overlay.classList.remove('active');
        
        // Release focus trap
        releaseFocusTrap();
        
        // Dispatch custom event
        dispatchCustomEvent('closed');

        // Optional: Send AJAX request to track dismissal
        trackDismissal();
    }

    /**
     * Track popup dismissal via AJAX
     */
    function trackDismissal() {
        if (!config.ajaxUrl || !config.nonce) return;

        const formData = new FormData();
        formData.append('action', 'scp_dismiss_popup');
        formData.append('nonce', config.nonce);

        fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).catch(error => console.warn('SCP AJAX Error:', error));
    }

    /**
     * Focus trap for accessibility
     */
    let firstFocusable = null;
    let lastFocusable = null;
    let previousActiveElement = null;

    function trapFocus() {
        previousActiveElement = document.activeElement;
        
        const focusableElements = popup.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length > 0) {
            firstFocusable = focusableElements[0];
            lastFocusable = focusableElements[focusableElements.length - 1];
            
            // Focus first element
            firstFocusable.focus();
            
            // Add focus trap listener
            popup.addEventListener('keydown', handleFocusTrap);
        }
    }

    function releaseFocusTrap() {
        if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
            previousActiveElement.focus();
        }
        
        if (popup) {
            popup.removeEventListener('keydown', handleFocusTrap);
        }
        
        firstFocusable = null;
        lastFocusable = null;
        previousActiveElement = null;
    }

    function handleFocusTrap(e) {
        if (e.key !== 'Tab') return;
        
        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstFocusable) {
                e.preventDefault();
                lastFocusable.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastFocusable) {
                e.preventDefault();
                firstFocusable.focus();
            }
        }
    }

    /**
     * Dispatch custom event
     * @param {string} eventName - Name of the event
     */
    function dispatchCustomEvent(eventName) {
        const event = new CustomEvent(`scp_popup_${eventName}`, {
            detail: {
                popup: popup,
                overlay: overlay
            }
        });
        document.dispatchEvent(event);
    }

    /**
     * Public API
     */
    window.SCPPopup = {
        open: openPopup,
        close: closePopup,
        isActive: isActive,
        shouldShow: shouldShowPopup,
        destroy: function() {
            if (overlay) {
                overlay.remove();
            }
            overlay = null;
            popup = null;
            closeButtons = [];
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
