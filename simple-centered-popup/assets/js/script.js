/**
 * Simple Centered Popup JavaScript
 *
 * @package Simple_Centered_Popup
 */

(function() {
    'use strict';

    // Check if config is available
    if (typeof scpConfig === 'undefined') {
        return;
    }

    /**
     * Popup Manager Class
     */
    class PopupManager {
        constructor() {
            this.overlay = null;
            this.popup = null;
            this.closeButtons = null;
            this.isOpen = false;
            this.focusableElements = null;
            this.firstFocusable = null;
            this.lastFocusable = null;
            this.previousActiveElement = null;

            this.init();
        }

        /**
         * Initialize popup
         */
        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        /**
         * Setup popup elements and event listeners
         */
        setup() {
            this.overlay = document.getElementById('scp-popup-overlay');
            
            if (!this.overlay) {
                return;
            }

            this.popup = this.overlay.querySelector('.scp-popup');
            this.closeButtons = this.overlay.querySelectorAll('.scp-close-btn');
            
            if (!this.popup) {
                return;
            }

            // Check if we should show the popup
            if (!this.shouldShow()) {
                this.overlay.remove();
                return;
            }

            // Setup event listeners
            this.bindEvents();

            // Auto open if configured
            if (scpConfig.autoOpen) {
                const delay = parseInt(scpConfig.delay, 10) || 1000;
                setTimeout(() => this.open(), delay);
            }
        }

        /**
         * Check if popup should be shown based on cookie/localStorage
         * @return {boolean}
         */
        shouldShow() {
            const frequencyDays = parseInt(scpConfig.frequencyDays, 10) || 7;
            const cookieName = scpConfig.cookieName || 'scp_popup_shown';
            
            // If frequency is 0, always show
            if (frequencyDays === 0) {
                return true;
            }

            // Check localStorage first
            const storedData = localStorage.getItem(cookieName);
            
            if (storedData) {
                try {
                    const data = JSON.parse(storedData);
                    const shownDate = new Date(data.timestamp);
                    const now = new Date();
                    const diffTime = Math.abs(now - shownDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    // If not enough days have passed, don't show
                    if (diffDays <= frequencyDays) {
                        return false;
                    }
                } catch (e) {
                    // Invalid data, remove it
                    localStorage.removeItem(cookieName);
                }
            }

            // Fallback to cookie check
            const cookieValue = this.getCookie(cookieName);
            if (cookieValue) {
                return false;
            }

            return true;
        }

        /**
         * Get cookie value by name
         * @param {string} name - Cookie name
         * @return {string|null}
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }

        /**
         * Set cookie
         * @param {string} name - Cookie name
         * @param {string} value - Cookie value
         * @param {number} days - Days until expiration
         */
        setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
        }

        /**
         * Store popup dismiss data in localStorage
         */
        storeDismissData() {
            const cookieName = scpConfig.cookieName || 'scp_popup_shown';
            const data = {
                timestamp: Date.now(),
                dismissed: true
            };
            localStorage.setItem(cookieName, JSON.stringify(data));
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Close button clicks
            this.closeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.close();
                });
            });

            // Overlay click (close when clicking outside popup)
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) {
                    this.close();
                }
            });

            // Keyboard events
            document.addEventListener('keydown', (e) => {
                if (!this.isOpen) {
                    return;
                }

                // Close on Escape
                if (e.key === 'Escape' || e.keyCode === 27) {
                    this.close();
                    return;
                }

                // Trap focus within popup
                if (e.key === 'Tab' || e.keyCode === 9) {
                    this.trapFocus(e);
                }
            });

            // Handle button links
            const buttonLinks = this.overlay.querySelectorAll('.scp-button[href]');
            buttonLinks.forEach(link => {
                link.addEventListener('click', () => {
                    // Allow normal navigation, popup will be dismissed via cookie
                    this.dismiss();
                });
            });
        }

        /**
         * Open popup
         */
        open() {
            if (this.isOpen) {
                return;
            }

            this.previousActiveElement = document.activeElement;
            this.isOpen = true;
            
            // Add active class to trigger animation
            this.overlay.classList.add('active');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            // Set focus to popup
            setTimeout(() => {
                const firstFocusable = this.popup.querySelector(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (firstFocusable) {
                    firstFocusable.focus();
                }
            }, 100);

            // Announce to screen readers
            this.announceToScreenReader('Popup opened');
        }

        /**
         * Close popup
         */
        close() {
            if (!this.isOpen) {
                return;
            }

            this.dismiss();
            
            this.overlay.classList.remove('active');
            this.isOpen = false;
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Return focus to previous element
            if (this.previousActiveElement) {
                this.previousActiveElement.focus();
            }

            this.announceToScreenReader('Popup closed');
        }

        /**
         * Dismiss popup (store in localStorage/cookie)
         */
        dismiss() {
            this.storeDismissData();
            
            // Send AJAX request to track dismissal (optional)
            this.trackDismissal();
        }

        /**
         * Track popup dismissal via AJAX
         */
        trackDismissal() {
            if (typeof scpConfig.ajaxUrl === 'undefined') {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'scp_dismiss_popup');
            formData.append('nonce', scpConfig.nonce);

            fetch(scpConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).catch(error => {
                console.warn('SCP: Failed to track dismissal', error);
            });
        }

        /**
         * Trap focus within popup for accessibility
         * @param {KeyboardEvent} e - Tab key event
         */
        trapFocus(e) {
            this.focusableElements = this.popup.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (this.focusableElements.length === 0) {
                return;
            }

            this.firstFocusable = this.focusableElements[0];
            this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === this.firstFocusable) {
                    e.preventDefault();
                    this.lastFocusable.focus();
                }
            } else {
                if (document.activeElement === this.lastFocusable) {
                    e.preventDefault();
                    this.firstFocusable.focus();
                }
            }
        }

        /**
         * Announce message to screen readers
         * @param {string} message - Message to announce
         */
        announceToScreenReader(message) {
            let announcer = document.getElementById('scp-screen-reader-announcer');
            
            if (!announcer) {
                announcer = document.createElement('div');
                announcer.id = 'scp-screen-reader-announcer';
                announcer.setAttribute('aria-live', 'polite');
                announcer.setAttribute('aria-atomic', 'true');
                announcer.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;';
                document.body.appendChild(announcer);
            }

            announcer.textContent = '';
            
            // Small delay to ensure screen readers pick up the change
            setTimeout(() => {
                announcer.textContent = message;
            }, 100);
        }
    }

    // Initialize popup manager
    new PopupManager();

    // Expose to global scope for manual control
    window.SCPPopup = {
        open: function() {
            const event = new CustomEvent('scp-open-popup');
            document.dispatchEvent(event);
        },
        close: function() {
            const event = new CustomEvent('scp-close-popup');
            document.dispatchEvent(event);
        }
    };

})();
