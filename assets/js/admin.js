// Admin panel JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin components
    initializeSidebar();
    initializeTables();
    initializeForms();
    initializeAlerts();
    initializeConfirmations();
});

// Sidebar functionality
function initializeSidebar() {
    const toggleBtn = document.querySelector('.btn-toggle-sidebar');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            
            // Create overlay for mobile
            if (!overlay && window.innerWidth <= 768) {
                createSidebarOverlay();
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            sidebar && sidebar.classList.contains('show') &&
            !sidebar.contains(e.target) && 
            !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('show');
            removeSidebarOverlay();
        }
    });
}

function createSidebarOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    `;
    document.body.appendChild(overlay);
    
    overlay.addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.remove('show');
        removeSidebarOverlay();
    });
}

function removeSidebarOverlay() {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Table functionality
function initializeTables() {
    // Add Bootstrap table classes
    const tables = document.querySelectorAll('table:not(.table)');
    tables.forEach(table => {
        table.classList.add('table', 'table-striped', 'table-hover');
    });
    
    // Initialize DataTable if available
    if (typeof DataTable !== 'undefined') {
        const dataTables = document.querySelectorAll('.data-table');
        dataTables.forEach(table => {
            new DataTable(table, {
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });
        });
    }
    
    // Row selection functionality
    const selectAllCheckbox = document.querySelector('#select-all');
    const rowCheckboxes = document.querySelectorAll('.row-select');
    
    if (selectAllCheckbox && rowCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                toggleRowSelection(checkbox.closest('tr'), this.checked);
            });
        });
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleRowSelection(this.closest('tr'), this.checked);
                updateSelectAllCheckbox();
            });
        });
    }
}

function toggleRowSelection(row, selected) {
    if (selected) {
        row.classList.add('table-active');
    } else {
        row.classList.remove('table-active');
    }
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.querySelector('#select-all');
    const rowCheckboxes = document.querySelectorAll('.row-select');
    const checkedBoxes = document.querySelectorAll('.row-select:checked');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedBoxes.length === rowCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < rowCheckboxes.length;
    }
}

// Form functionality
function initializeForms() {
    // Auto-submit forms with data-auto-submit
    const autoSubmitSelects = document.querySelectorAll('select[data-auto-submit]');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validation="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showAlert('error', 'Por favor complete todos los campos requeridos correctamente.');
            }
            form.classList.add('was-validated');
        });
    });
    
    // Character counters
    const textAreas = document.querySelectorAll('textarea[data-max-length]');
    textAreas.forEach(textarea => {
        addCharacterCounter(textarea);
    });
}

function addCharacterCounter(textarea) {
    const maxLength = parseInt(textarea.dataset.maxLength);
    const counter = document.createElement('div');
    counter.className = 'character-counter text-muted small mt-1';
    textarea.parentNode.appendChild(counter);
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${textarea.value.length}/${maxLength} caracteres`;
        
        if (remaining < 20) {
            counter.classList.add('text-warning');
        } else {
            counter.classList.remove('text-warning');
        }
        
        if (remaining < 0) {
            counter.classList.add('text-danger');
            counter.classList.remove('text-warning');
        } else {
            counter.classList.remove('text-danger');
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

// Alert functionality
function initializeAlerts() {
    // Auto-hide success alerts
    const alerts = document.querySelectorAll('.alert-success, .alert-info');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Confirmation dialogs
function initializeConfirmations() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || '¿Está seguro de realizar esta acción?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Delete confirmations
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.')) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Utility functions
function showAlert(type, message, permanent = false) {
    const alertContainer = document.querySelector('.alert-container') || document.querySelector('.admin-main');
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show ${permanent ? 'alert-permanent' : ''}`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    if (!permanent) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
}

// File upload preview
function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Slug generation
function generateSlug(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
}

// Auto-generate slug from name
function initializeSlugGeneration() {
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.dataset.manual) {
                slugInput.value = generateSlug(this.value);
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
    }
}