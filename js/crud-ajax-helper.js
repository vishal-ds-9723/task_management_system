/**
 * ═════════════════════════════════════════════════════════════════════════
 * TMS CRUD AJAX Helper - Universal CRUD Operations Without Page Refresh
 * ═════════════════════════════════════════════════════════════════════════
 * 
 * Handles all Create, Read, Update, Delete operations via AJAX
 * Works with modals, forms, and list updates
 */

class CRUDAJAXHelper {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /**
     * Open a CRUD modal (for create/edit)
     */
    async openCRUDModal(modalSelector, {
        title = 'Add New Item',
        action = 'create',
        itemId = null,
        editUrl = null,
        onOpen = null,
    } = {}) {
        const modal = document.querySelector(modalSelector);
        if (!modal) {
            console.error(`Modal not found: ${modalSelector}`);
            return;
        }

        // Update modal title
        const titleElem = modal.querySelector('.modal-title');
        if (titleElem) {
            titleElem.textContent = title;
        }

        // If editing, load the item data
        if (action === 'edit' && editUrl) {
            try {
                // Fetch item data
                const response = await fetch(editUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Failed to load item');

                const data = await response.json();

                // Pre-fill form with data
                const form = modal.querySelector('form');
                if (form) {
                    this.populateForm(form, data);
                }

                // Store the edit URL on modal for reference
                modal.dataset.editUrl = editUrl;
                modal.dataset.itemId = itemId;
            } catch (error) {
                console.error('Error loading item:', error);
                ajax.showError('Failed to load item for editing');
                return;
            }
        } else {
            // Clear form for new item
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
            delete modal.dataset.editUrl;
            delete modal.dataset.itemId;
        }

        // Show modal
        if (window.bootstrap?.Modal) {
            const bsModal = new window.bootstrap.Modal(modal);
            bsModal.show();
        } else {
            modal.style.display = 'block';
        }

        if (onOpen) {
            onOpen();
        }
    }

    /**
     * Close a CRUD modal
     */
    closeCRUDModal(modalSelector) {
        const modal = document.querySelector(modalSelector);
        if (!modal) return;

        if (window.bootstrap?.Modal) {
            const bsModal = window.bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        } else {
            modal.style.display = 'none';
        }
    }

    /**
     * Handle CRUD form submission (POST for create, PATCH for update)
     */
    async submitCRUDForm(formSelector, {
        successMessage = 'Operation successful',
        onSuccess = null,
        onError = null,
        reloadContainer = null,
        closeModal = null,
        redirectUrl = null,
    } = {}) {
        const form = document.querySelector(formSelector);
        if (!form) {
            console.error(`Form not found: ${formSelector}`);
            return;
        }

        const url = form.action;
        const method = form.method?.toUpperCase() || 'POST';
        const isMultipart = form.enctype === 'multipart/form-data';

        // Create body
        let body;
        const formData = new FormData(form);

        if (isMultipart) {
            body = formData;
        } else {
            body = Object.fromEntries(formData);
        }

        try {
            ajax.showLoadingState(form);

            const fetchOptions = {
                method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            };

            if (isMultipart) {
                // Let browser set Content-Type header for multipart
                fetchOptions.body = body;
            } else {
                fetchOptions.headers['Content-Type'] = 'application/json';
                fetchOptions.body = JSON.stringify(body);
            }

            const response = await fetch(url, fetchOptions);
            ajax.hideLoadingState(form);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Form submission failed');
            }

            const data = await response.json();

            // Show success
            ajax.showSuccess(successMessage);

            // Execute success callback
            if (onSuccess) {
                onSuccess(data);
            }

            // Close modal if specified
            if (closeModal) {
                this.closeCRUDModal(closeModal);
            }

            // Reload container if specified
            if (reloadContainer) {
                await ajax.reloadArea(reloadContainer);
            }

            // Redirect if specified
            if (redirectUrl) {
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 800);
            }

            // Reset form
            form.reset();

            return data;
        } catch (error) {
            ajax.hideLoadingState(form);
            console.error('CRUD form error:', error);

            if (onError) {
                onError(error);
            }

            ajax.showError(error.message || 'Operation failed. Please try again.');
            throw error;
        }
    }

    /**
     * Delete item with AJAX confirmation
     */
    async deleteItem(itemId, deleteUrl, {
        confirmMessage = 'Are you sure? This cannot be undone.',
        successMessage = 'Item deleted successfully',
        onSuccess = null,
        reloadContainer = null,
        removeElement = null,
    } = {}) {
        if (!confirm(confirmMessage)) {
            return null;
        }

        try {
            ajax.showLoadingState(null);

            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                }
            });

            ajax.hideLoadingState(null);

            if (!response.ok) {
                throw new Error('Deletion failed');
            }

            const data = await response.json();

            // Show success
            ajax.showSuccess(successMessage);

            // Remove element from DOM if specified
            if (removeElement) {
                const elem = document.querySelector(removeElement);
                if (elem) {
                    elem.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => elem.remove(), 300);
                }
            }

            // Reload container if specified
            if (reloadContainer) {
                await ajax.reloadArea(reloadContainer);
            }

            // Execute callback
            if (onSuccess) {
                onSuccess(data);
            }

            return data;
        } catch (error) {
            ajax.hideLoadingState(null);
            console.error('Delete error:', error);
            ajax.showError(error.message || 'Failed to delete item');
            throw error;
        }
    }

    /**
     * Populate form with data (for edit forms)
     */
    populateForm(form, data) {
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = !!data[key];
                } else if (field.type === 'radio') {
                    const radio = form.querySelector(`[name="${key}"][value="${data[key]}"]`);
                    if (radio) radio.checked = true;
                } else if (field.tagName === 'SELECT') {
                    // Handle select with possible array value
                    const value = Array.isArray(data[key]) ? data[key][0] : data[key];
                    field.value = value || '';
                } else {
                    field.value = data[key] || '';
                }
            }
        });
    }

    /**
     * Bulk action on multiple items
     */
    async bulkAction(itemIds, actionUrl, {
        action = 'delete',
        confirmMessage = 'Proceed with this action on all selected items?',
        successMessage = 'Action completed successfully',
        onSuccess = null,
        reloadContainer = null,
    } = {}) {
        if (!itemIds.length) {
            ajax.showError('No items selected');
            return;
        }

        if (!confirm(confirmMessage)) {
            return null;
        }

        try {
            ajax.showLoadingState(null);

            const response = await fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    ids: itemIds,
                    action,
                })
            });

            ajax.hideLoadingState(null);

            if (!response.ok) {
                throw new Error('Bulk action failed');
            }

            const data = await response.json();

            // Show success
            ajax.showSuccess(successMessage);

            // Reload container if specified
            if (reloadContainer) {
                await ajax.reloadArea(reloadContainer);
            }

            // Execute callback
            if (onSuccess) {
                onSuccess(data);
            }

            return data;
        } catch (error) {
            ajax.hideLoadingState(null);
            console.error('Bulk action error:', error);
            ajax.showError(error.message || 'Bulk action failed');
            throw error;
        }
    }

    /**
     * Live search for items
     */
    async liveSearch(searchUrl, query, resultSelector) {
        if (!query || query.length < 2) {
            const resultsElem = document.querySelector(resultSelector);
            if (resultsElem) resultsElem.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) throw new Error('Search failed');

            const results = await response.json();

            // Render results
            const resultsElem = document.querySelector(resultSelector);
            if (resultsElem) {
                resultsElem.innerHTML = results.map(item => `
                    <div class="search-result-item" onclick="selectSearchResult(${item.id})">
                        <span>${item.name || item.title}</span>
                        <small>${item.details || ''}</small>
                    </div>
                `).join('');
            }

            return results;
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    /**
     * Handle button clicks for common CRUD actions
     */
    initializeCRUDButtons(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Edit buttons
        container.querySelectorAll('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const itemId = btn.dataset.itemId;
                const editUrl = btn.dataset.editUrl;
                const modalSelector = btn.dataset.modal;

                if (modalSelector && editUrl) {
                    this.openCRUDModal(modalSelector, {
                        title: `Edit ${btn.dataset.itemType || 'Item'}`,
                        action: 'edit',
                        itemId,
                        editUrl,
                    });
                }
            });
        });

        // Delete buttons
        container.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const itemId = btn.dataset.itemId;
                const deleteUrl = btn.dataset.deleteUrl;
                const confirmMsg = btn.dataset.confirm || 'Are you sure you want to delete this item?';
                const removeSelector = btn.dataset.removeElement;
                const reloadSelector = btn.dataset.reloadContainer;

                this.deleteItem(itemId, deleteUrl, {
                    confirmMessage: confirmMsg,
                    removeElement: removeSelector,
                    reloadContainer: reloadSelector,
                });
            });
        });
    }
}

// Create global instance
const crud = new CRUDAJAXHelper();
