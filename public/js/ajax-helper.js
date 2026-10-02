/**
 * ============================================================
 * TMS AJAX Helper - Universal AJAX Utility for All Operations
 * ============================================================
 * 
 * This utility provides reusable functions for:
 * - Form submissions (POST, PATCH, PUT, DELETE)
 * - Data fetching (GET requests)
 * - List/Table updates without page refresh
 * - Modal operations
 * - Filter operations
 * - Error handling & loading states
 */

class AJAXHelper {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        this.apiTimeout = 30000; // 30 seconds
    }

    /**
     * Get common headers for API requests
     */
    getHeaders(includeContentType = true) {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken,
        };
        if (includeContentType) {
            headers['Content-Type'] = 'application/json';
        }
        return headers;
    }

    /**
     * Generic fetch wrapper with error handling
     */
    async request(url, options = {}) {
        const {
            method = 'GET',
            body = null,
            headers = {},
            timeout = this.apiTimeout,
            showLoading = true,
            loadingElement = null,
        } = options;

        try {
            // Show loading state
            if (showLoading) {
                this.showLoadingState(loadingElement);
            }

            const fetchOptions = {
                method,
                headers: { ...this.getHeaders(method !== 'GET'), ...headers },
                signal: AbortSignal.timeout(timeout)
            };

            if (body) {
                fetchOptions.body = typeof body === 'string' ? body : JSON.stringify(body);
            }

            const response = await fetch(url, fetchOptions);

            // Hide loading state
            if (showLoading) {
                this.hideLoadingState(loadingElement);
            }

            // Handle HTTP errors
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            // Parse response
            const contentType = response.headers.get('content-type');
            if (contentType?.includes('application/json')) {
                return await response.json();
            }
            return await response.text();
        } catch (error) {
            if (showLoading) {
                this.hideLoadingState(loadingElement);
            }
            console.error('AJAX Request Error:', error);
            this.showError(error.message || 'An error occurred. Please try again.');
            throw error;
        }
    }

    /**
     * Submit a form via AJAX without page refresh
     */
    async submitForm(formElement, options = {}) {
        const {
            successMessage = 'Operation completed successfully',
            onSuccess = null,
            redirectUrl = null,
            reloadArea = null,
        } = options;

        const formData = new FormData(formElement);
        const method = formElement.method?.toUpperCase() || 'POST';
        const action = formElement.action;

        // Create body based on content type
        let body;
        let headers = {};
        
        if (formElement.enctype === 'multipart/form-data') {
            // Keep FormData as is for multipart
            body = formData;
            headers = this.getHeaders(false); // Don't set Content-Type for multipart
        } else {
            // Convert to JSON for standard forms
            body = Object.fromEntries(formData);
            headers = this.getHeaders(true);
        }

        try {
            this.showLoadingState(formElement);
            
            const response = await fetch(action, {
                method,
                headers,
                body: formElement.enctype === 'multipart/form-data' ? body : JSON.stringify(body),
                signal: AbortSignal.timeout(this.apiTimeout)
            });

            this.hideLoadingState(formElement);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Form submission failed');
            }

            const data = await response.json();

            // Show success message
            this.showSuccess(successMessage);

            // Execute success callback
            if (onSuccess) {
                onSuccess(data);
            }

            // Reload specific area if specified
            if (reloadArea) {
                await this.reloadArea(reloadArea);
            }

            // Redirect if specified
            if (redirectUrl) {
                setTimeout(() => window.location.href = redirectUrl, 800);
            }

            // Reset form
            formElement.reset();

            return data;
        } catch (error) {
            this.hideLoadingState(formElement);
            console.error('Form submission error:', error);
            this.showError(error.message || 'Form submission failed');
            throw error;
        }
    }

    /**
     * Fetch data and update DOM element
     */
    async loadContent(url, targetElement, options = {}) {
        const {
            method = 'GET',
            body = null,
            loadingMessage = 'Loading...',
        } = options;

        const $target = typeof targetElement === 'string' 
            ? document.querySelector(targetElement) 
            : targetElement;

        if (!$target) {
            console.error('Target element not found');
            return;
        }

        try {
            $target.innerHTML = `<div class="loading-spinner">${loadingMessage}</div>`;

            const data = await this.request(url, {
                method,
                body,
                showLoading: false,
            });

            // If response is HTML, directly insert
            if (typeof data === 'string' && data.includes('<')) {
                $target.innerHTML = data;
            } else if (typeof data === 'object') {
                // If JSON, assume it has an 'html' property or just display it
                $target.innerHTML = data.html || JSON.stringify(data);
            } else {
                $target.innerHTML = data;
            }

            return data;
        } catch (error) {
            $target.innerHTML = `<div class="error-message">Failed to load content: ${error.message}</div>`;
            throw error;
        }
    }

    /**
     * Handle filter form submission without page refresh
     */
    async applyFilters(formElement, resultsContainer, options = {}) {
        const {
            method = 'GET',
            onSuccess = null,
        } = options;

        const formData = new FormData(formElement);
        const queryString = new URLSearchParams(formData).toString();
        const url = `${formElement.action}${formElement.action.includes('?') ? '&' : '?'}${queryString}`;

        try {
            this.showLoadingState(resultsContainer);

            const response = await fetch(url, {
                method,
                headers: this.getHeaders(false),
                signal: AbortSignal.timeout(this.apiTimeout)
            });

            this.hideLoadingState(resultsContainer);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            resultsContainer.innerHTML = html;

            // Update URL without page reload
            window.history.pushState({ path: url }, '', url);

            if (onSuccess) {
                onSuccess(html);
            }

            return html;
        } catch (error) {
            this.hideLoadingState(resultsContainer);
            console.error('Filter error:', error);
            this.showError('Failed to apply filters');
            throw error;
        }
    }

    /**
     * Reload a specific area/container
     */
    async reloadArea(containerSelector, options = {}) {
        const {
            url = window.location.href,
            method = 'GET',
        } = options;

        try {
            const response = await fetch(url, {
                method,
                headers: this.getHeaders(false),
                signal: AbortSignal.timeout(this.apiTimeout)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            // Parse the new page HTML
            const newHTML = await response.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(newHTML, 'text/html');
            const newContainer = newDoc.querySelector(containerSelector);

            if (newContainer) {
                const currentContainer = document.querySelector(containerSelector);
                currentContainer.innerHTML = newContainer.innerHTML;
                return newContainer.innerHTML;
            }
        } catch (error) {
            console.error('Area reload error:', error);
            throw error;
        }
    }

    /**
     * Delete item with confirmation
     */
    async deleteItem(url, options = {}) {
        const {
            confirmMessage = 'Are you sure you want to delete this item?',
            successMessage = 'Item deleted successfully',
            onSuccess = null,
            reloadArea = null,
        } = options;

        if (!confirm(confirmMessage)) {
            return null;
        }

        try {
            this.showLoadingState(null);

            const response = await fetch(url, {
                method: 'DELETE',
                headers: this.getHeaders(),
                signal: AbortSignal.timeout(this.apiTimeout)
            });

            this.hideLoadingState(null);

            if (!response.ok) {
                throw new Error('Deletion failed');
            }

            const data = await response.json();
            this.showSuccess(successMessage);

            if (onSuccess) {
                onSuccess(data);
            }

            if (reloadArea) {
                await this.reloadArea(reloadArea);
            }

            return data;
        } catch (error) {
            this.hideLoadingState(null);
            console.error('Delete error:', error);
            this.showError(error.message || 'Failed to delete item');
            throw error;
        }
    }

    /**
     * Show loading state on element
     */
    showLoadingState(element) {
        if (!element) return;

        const $el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!$el) return;

        // Disable form elements
        const inputs = $el.querySelectorAll('input, button, select, textarea');
        inputs.forEach(input => {
            input.disabled = true;
            input.style.opacity = '0.6';
        });

        // Add loading indicator
        const spinner = document.createElement('div');
        spinner.className = 'ajax-loading-spinner';
        spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        spinner.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            font-size: 14px;
            font-weight: 600;
        `;
        document.body.appendChild(spinner);
        $el.dataset.loadingSpinner = spinner.id = `spinner-${Date.now()}`;
    }

    /**
     * Hide loading state on element
     */
    hideLoadingState(element) {
        if (!element) return;

        const $el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!$el) return;

        // Re-enable form elements
        const inputs = $el.querySelectorAll('input, button, select, textarea');
        inputs.forEach(input => {
            input.disabled = false;
            input.style.opacity = '1';
        });

        // Remove loading spinner
        const spinnerId = $el.dataset.loadingSpinner;
        if (spinnerId) {
            const spinner = document.getElementById(spinnerId);
            if (spinner) spinner.remove();
        }
    }

    /**
     * Show success notification
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    /**
     * Show error notification
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Show generic notification
     */
    showNotification(message, type = 'info') {
        // Check if toast library exists (like toastr, Swal, or custom)
        if (window.toastr) {
            window.toastr[type](message);
            return;
        }

        // Fallback: Create simple notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Handle modal form submission
     */
    async submitModalForm(modalElement, options = {}) {
        const form = modalElement.querySelector('form');
        if (!form) {
            console.error('No form found in modal');
            return;
        }

        const {
            successMessage = 'Submitted successfully',
            onSuccess = null,
            closeModal = true,
            reloadArea = null,
        } = options;

        try {
            const data = await this.submitForm(form, {
                successMessage,
                onSuccess,
                showLoading: true,
                reloadArea,
            });

            if (closeModal) {
                // Close modal using Bootstrap or close button
                this.closeModal(modalElement);
            }

            return data;
        } catch (error) {
            console.error('Modal form error:', error);
            throw error;
        }
    }

    /**
     * Close a modal
     */
    closeModal(modalElement) {
        const $modal = typeof modalElement === 'string' 
            ? document.querySelector(modalElement) 
            : modalElement;

        if (!$modal) return;

        // Try Bootstrap modal
        if (window.bootstrap?.Modal) {
            const modal = new window.bootstrap.Modal($modal);
            modal.hide();
            return;
        }

        // Fallback: just hide the element
        $modal.style.display = 'none';
    }

    /**
     * Open modal with content
     */
    openModal(modalSelector, options = {}) {
        const {
            title = '',
            content = '',
            backdrop = true,
        } = options;

        const $modal = document.querySelector(modalSelector);
        if (!$modal) {
            console.error(`Modal not found: ${modalSelector}`);
            return;
        }

        // Update title if available
        const titleElem = $modal.querySelector('.modal-title');
        if (titleElem && title) {
            titleElem.textContent = title;
        }

        // Update content if available
        const bodyElem = $modal.querySelector('.modal-body');
        if (bodyElem && content) {
            bodyElem.innerHTML = content;
        }

        // Show modal using Bootstrap
        if (window.bootstrap?.Modal) {
            const modal = new window.bootstrap.Modal($modal, { backdrop });
            modal.show();
            return;
        }

        // Fallback
        $modal.style.display = 'block';
    }

    /**
     * Batch update multiple items
     */
    async batchUpdate(url, itemIds, updateData, options = {}) {
        const {
            successMessage = 'Items updated successfully',
            onSuccess = null,
            reloadArea = null,
        } = options;

        const body = {
            ids: itemIds,
            ...updateData
        };

        try {
            this.showLoadingState(null);

            const response = await fetch(url, {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify(body),
                signal: AbortSignal.timeout(this.apiTimeout)
            });

            this.hideLoadingState(null);

            if (!response.ok) {
                throw new Error('Batch update failed');
            }

            const data = await response.json();
            this.showSuccess(successMessage);

            if (onSuccess) {
                onSuccess(data);
            }

            if (reloadArea) {
                await this.reloadArea(reloadArea);
            }

            return data;
        } catch (error) {
            this.hideLoadingState(null);
            console.error('Batch update error:', error);
            this.showError(error.message || 'Failed to update items');
            throw error;
        }
    }

    /**
     * Auto-refresh content at intervals
     */
    autoRefresh(url, targetSelector, intervalMs = 30000) {
        const $target = document.querySelector(targetSelector);
        if (!$target) {
            console.error(`Target not found: ${targetSelector}`);
            return;
        }

        // Initial load
        this.loadContent(url, $target);

        // Set interval for auto-refresh
        const intervalId = setInterval(() => {
            this.loadContent(url, $target).catch(err => {
                console.error('Auto-refresh error:', err);
            });
        }, intervalMs);

        // Store interval ID for cleanup
        $target.dataset.refreshIntervalId = intervalId;

        return intervalId;
    }

    /**
     * Stop auto-refresh
     */
    stopAutoRefresh(targetSelector) {
        const $target = document.querySelector(targetSelector);
        if (!$target) return;

        const intervalId = $target.dataset.refreshIntervalId;
        if (intervalId) {
            clearInterval(parseInt(intervalId));
            delete $target.dataset.refreshIntervalId;
        }
    }
}

// Create global instance
const ajax = new AJAXHelper();

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .loading-spinner {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    .error-message {
        padding: 20px;
        background: #fee;
        color: #c00;
        border-radius: 4px;
        text-align: center;
    }
`;
document.head.appendChild(style);
