// Form validation JavaScript for ERP System

$(document).ready(function() {
    // Initialize form validation
    initializeValidation();
    
    // Real-time validation
    $('input, select, textarea').on('blur keyup change', function() {
        validateField($(this));
    });
    
    // Form submission validation
    $('form').on('submit', function(e) {
        if (!validateForm($(this))) {
            e.preventDefault();
            showError('Please fix the errors below before submitting.');
            return false;
        }
    });
});

// Initialize validation rules
function initializeValidation() {
    // Customer form validation
    $('#customerForm input[name="first_name"]').attr('data-required', 'true').attr('data-min-length', '2');
    $('#customerForm input[name="last_name"]').attr('data-required', 'true').attr('data-min-length', '2');
    $('#customerForm input[name="contact_no"]').attr('data-required', 'true').attr('data-pattern', '^[0-9]{10}$');
    $('#customerForm select[name="title"]').attr('data-required', 'true');
    $('#customerForm select[name="district"]').attr('data-required', 'true');
    
    // Item form validation
    $('#itemForm input[name="item_code"]').attr('data-required', 'true').attr('data-min-length', '3');
    $('#itemForm input[name="item_name"]').attr('data-required', 'true').attr('data-min-length', '3');
    $('#itemForm select[name="item_category"]').attr('data-required', 'true');
    $('#itemForm select[name="item_subcategory"]').attr('data-required', 'true');
    $('#itemForm input[name="quantity"]').attr('data-required', 'true').attr('data-pattern', '^[0-9]+$');
    $('#itemForm input[name="unit_price"]').attr('data-required', 'true').attr('data-pattern', '^[0-9]+(\\.[0-9]{1,2})?$');
}

// Validate individual field
function validateField(field) {
    const value = field.val().trim();
    const fieldName = field.attr('name');
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous validation
    field.removeClass('is-valid is-invalid');
    field.siblings('.invalid-feedback, .valid-feedback').remove();
    
    // Required field validation
    if (field.attr('data-required') === 'true' && value === '') {
        isValid = false;
        errorMessage = getFieldLabel(fieldName) + ' is required.';
    }
    
    // Minimum length validation
    else if (field.attr('data-min-length') && value.length < parseInt(field.attr('data-min-length'))) {
        isValid = false;
        errorMessage = getFieldLabel(fieldName) + ' must be at least ' + field.attr('data-min-length') + ' characters.';
    }
    
    // Pattern validation
    else if (field.attr('data-pattern') && value !== '') {
        const pattern = new RegExp(field.attr('data-pattern'));
        if (!pattern.test(value)) {
            isValid = false;
            errorMessage = getValidationMessage(fieldName);
        }
    }
    
    // Email validation
    else if (field.attr('type') === 'email' && value !== '') {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }
    }
    
    // Number validation
    else if (field.attr('type') === 'number' && value !== '') {
        if (isNaN(value) || parseFloat(value) < 0) {
            isValid = false;
            errorMessage = 'Please enter a valid positive number.';
        }
    }
    
    // Apply validation result
    if (isValid && value !== '') {
        field.addClass('is-valid');
        field.after('<div class="valid-feedback">Looks good!</div>');
    } else if (!isValid) {
        field.addClass('is-invalid');
        field.after('<div class="invalid-feedback">' + errorMessage + '</div>');
    }
    
    return isValid;
}

// Validate entire form
function validateForm(form) {
    let isFormValid = true;
    
    form.find('input, select, textarea').each(function() {
        if (!validateField($(this))) {
            isFormValid = false;
        }
    });
    
    return isFormValid;
}

// Get field label for error messages
function getFieldLabel(fieldName) {
    const labels = {
        'title': 'Title',
        'first_name': 'First Name',
        'middle_name': 'Middle Name',
        'last_name': 'Last Name',
        'contact_no': 'Contact Number',
        'district': 'District',
        'item_code': 'Item Code',
        'item_name': 'Item Name',
        'item_category': 'Item Category',
        'item_subcategory': 'Item Sub Category',
        'quantity': 'Quantity',
        'unit_price': 'Unit Price'
    };
    
    return labels[fieldName] || fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Get specific validation messages
function getValidationMessage(fieldName) {
    const messages = {
        'contact_no': 'Contact number must be exactly 10 digits.',
        'quantity': 'Quantity must be a positive number.',
        'unit_price': 'Unit price must be a valid decimal number (e.g., 100.50).',
        'item_code': 'Item code must be at least 3 characters long.'
    };
    
    return messages[fieldName] || 'Please enter a valid value.';
}

// Custom validation for contact number
function validateContactNumber(contactNo) {
    const pattern = /^[0-9]{10}$/;
    return pattern.test(contactNo);
}

// Custom validation for price
function validatePrice(price) {
    const pattern = /^[0-9]+(\.[0-9]{1,2})?$/;
    return pattern.test(price) && parseFloat(price) > 0;
}

// Custom validation for item code
function validateItemCode(itemCode) {
    return itemCode.length >= 3 && /^[A-Za-z0-9]+$/.test(itemCode);
}

// Validate date range
function validateDateRange(startDate, endDate) {
    if (!startDate || !endDate) {
        return false;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    return start <= end;
}

// Real-time contact number formatting
$(document).on('input', 'input[name="contact_no"]', function() {
    let value = $(this).val().replace(/\D/g, '');
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    $(this).val(value);
});

// Real-time price formatting
$(document).on('input', 'input[name="unit_price"]', function() {
    let value = $(this).val();
    // Allow only numbers and one decimal point
    value = value.replace(/[^0-9.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit decimal places to 2
    if (parts[1] && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    $(this).val(value);
});

// Real-time quantity formatting
$(document).on('input', 'input[name="quantity"]', function() {
    let value = $(this).val().replace(/\D/g, '');
    $(this).val(value);
});

// Dynamic subcategory loading based on category selection
$(document).on('change', 'select[name="item_category"]', function() {
    const categoryId = $(this).val();
    const subcategorySelect = $('select[name="item_subcategory"]');
    
    if (categoryId) {
        // In a real application, you would make an AJAX call here
        // For now, we'll just enable the subcategory dropdown
        subcategorySelect.prop('disabled', false);
    } else {
        subcategorySelect.prop('disabled', true).val('');
    }
});

// Prevent form submission on Enter key in input fields (except textarea)
$(document).on('keypress', 'input:not([type="submit"])', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        $(this).blur();
        return false;
    }
});

// Show validation summary
function showValidationSummary(errors) {
    let errorHtml = '<div class="alert alert-danger"><h6>Please fix the following errors:</h6><ul>';
    errors.forEach(function(error) {
        errorHtml += '<li>' + error + '</li>';
    });
    errorHtml += '</ul></div>';
    
    $('.container-fluid').prepend(errorHtml);
    
    // Scroll to top to show errors
    $('html, body').animate({ scrollTop: 0 }, 500);
}
