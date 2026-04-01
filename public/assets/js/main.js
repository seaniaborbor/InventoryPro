// Custom JavaScript for Inventory Management System

// Global Variables
let currentUserId = null;
let csrfToken = null;

// Initialize on page load
$(document).ready(function() {
    // Initialize CSRF token
    csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Add fade-in animation to main content
    $('#content .container-fluid').addClass('fade-in');
});

// Show loading spinner
function showLoading() {
    if ($('#loadingSpinner').length === 0) {
        $('body').append(`
            <div id="loadingSpinner" class="spinner-overlay">
                <div class="spinner-border spinner-border-custom text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    } else {
        $('#loadingSpinner').show();
    }
}

// Hide loading spinner
function hideLoading() {
    $('#loadingSpinner').hide();
}

// Format date
function formatDate(dateString, format = 'YYYY-MM-DD') {
    if (!dateString) return '';
    var date = new Date(dateString);
    
    if (format === 'YYYY-MM-DD') {
        return date.toISOString().split('T')[0];
    } else if (format === 'DD/MM/YYYY') {
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } else if (format === 'DD MMM YYYY') {
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }
    
    return dateString;
}

// Format currency
function formatCurrency(amount, currency = 'LRD') {
    if (!amount && amount !== 0) return '-';
    amount = parseFloat(amount);
    
    if (currency === 'USD') {
        return '$ ' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    } else {
        return 'L$ ' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

// Show success message
function showSuccess(message, title = 'Success') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Show error message
function showError(message, title = 'Error') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#dc3545'
    });
}

// Show warning message
function showWarning(message, title = 'Warning') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonColor: '#ffc107'
    });
}

// Show info message
function showInfo(message, title = 'Information') {
    Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonColor: '#0dcaf0'
    });
}

// Confirm action dialog
function confirmAction(message, callback, title = 'Are you sure?') {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// AJAX request wrapper
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    showLoading();
    
    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(response) {
            hideLoading();
            if (response.status === 'success') {
                if (successCallback) successCallback(response);
            } else {
                if (errorCallback) {
                    errorCallback(response);
                } else {
                    showError(response.message || 'An error occurred');
                }
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            if (xhr.status === 401) {
                window.location.href = baseUrl + '/auth/login';
            } else {
                showError('Connection error. Please try again.');
                if (errorCallback) errorCallback(xhr);
            }
        }
    });
}

// Initialize DataTable with common settings
function initDataTable(tableId, options = {}) {
    var defaultOptions = {
        responsive: true,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        pageLength: 10,
        order: [[0, 'desc']]
    };
    
    var settings = $.extend({}, defaultOptions, options);
    return $(tableId).DataTable(settings);
}

// Print function
function printElement(elementId) {
    var originalContent = document.body.innerHTML;
    var printContent = document.getElementById(elementId).innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Export to Excel
function exportToExcel(tableId, filename = 'export.xlsx') {
    // Requires SheetJS library
    if (typeof XLSX === 'undefined') {
        showError('Excel export library not loaded');
        return;
    }
    
    var table = document.getElementById(tableId);
    var workbook = XLSX.utils.table_to_book(table, {sheet: "Sheet1", raw: true});
    XLSX.writeFile(workbook, filename);
}

// Export to PDF
function exportToPDF(elementId, filename = 'export.pdf') {
    // Requires html2pdf library
    if (typeof html2pdf === 'undefined') {
        showError('PDF export library not loaded');
        return;
    }
    
    var element = document.getElementById(elementId);
    var opt = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: filename,
        image: {type: 'jpeg', quality: 0.98},
        html2canvas: {scale: 2},
        jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'}
    };
    
    html2pdf().set(opt).from(element).save();
}

// Barcode Scanner
function initBarcodeScanner(callback) {
    const html5QrCode = new Html5Qrcode("qr-reader");
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        html5QrCode.stop();
        if (callback) callback(decodedText);
    };
    
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    
    return html5QrCode;
}

// Format number
function formatNumber(number, decimals = 2) {
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// Calculate age from date of birth
function calculateAge(birthDate) {
    var today = new Date();
    var birth = new Date(birthDate);
    var age = today.getFullYear() - birth.getFullYear();
    var m = today.getMonth() - birth.getMonth();
    
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

// Generate random string
function generateRandomString(length = 10) {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    
    return result;
}

// Debounce function for search inputs
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