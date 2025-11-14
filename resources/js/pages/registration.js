import axios from 'axios';
import Swal from 'sweetalert2';

$(function () {
    const form = $('#registrationForm');
    const extractButton = $('#extractAadharButton');
    let currentStep = 1;

    // Step Navigation
    $('#nextStep').on('click', function() {
        if (validateStep1()) {
            currentStep = 2;
            showStep(2);
            updateProgress();
        }
    });

    $('#prevStep').on('click', function() {
        currentStep = 1;
        showStep(1);
        updateProgress();
    });

    function showStep(step) {
        $('.form-step').addClass('d-none').removeClass('active');
        $(`#step${step}`).removeClass('d-none').addClass('active');
    }

    function updateProgress() {
        $('.step').removeClass('active');
        $(`.step[data-step="${currentStep}"]`).addClass('active');
    }

    function validateStep1() {
        const name = $('#regName').val();
        const email = $('#regEmail').val();
        const password = $('#regPassword').val();
        const passwordConfirm = $('#regPasswordConfirm').val();
        const age = $('#regAge').val();
        const phone = $('#regPhone').val();

        if (!name || !email || !password || !passwordConfirm || !age || !phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Form',
                text: 'Please fill in all required fields'
            });
            return false;
        }

        if (password !== passwordConfirm) {
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Passwords do not match'
            });
            return false;
        }

        return true;
    }

    // Password Strength Checker
    $('#regPassword').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        const strengthFill = $('#strengthFill');
        const strengthText = $('#strengthText');

        strengthFill.removeClass('weak medium strong');
        
        if (password.length === 0) {
            strengthFill.css('width', '0%');
            strengthText.text('Password strength');
            return;
        }

        if (strength < 2) {
            strengthFill.addClass('weak').css('width', '33%');
            strengthText.text('Weak password');
        } else if (strength < 4) {
            strengthFill.addClass('medium').css('width', '66%');
            strengthText.text('Medium password');
        } else {
            strengthFill.addClass('strong').css('width', '100%');
            strengthText.text('Strong password');
        }
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }

    // Password Match Checker
    $('#regPasswordConfirm').on('input', function() {
        const password = $('#regPassword').val();
        const confirm = $(this).val();
        
        if (confirm.length === 0) {
            $(this).siblings('.match-icon, .mismatch-icon').addClass('d-none');
            return;
        }

        if (password === confirm) {
            $(this).siblings('.match-icon').removeClass('d-none');
            $(this).siblings('.mismatch-icon').addClass('d-none');
        } else {
            $(this).siblings('.match-icon').addClass('d-none');
            $(this).siblings('.mismatch-icon').removeClass('d-none');
        }
    });

    // Password Toggle
    $('#toggleRegPassword').on('click', function() {
        const passwordInput = $('#regPassword');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Email Validation
    $('#regEmail').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && emailRegex.test(email)) {
            $(this).siblings('.success-icon').removeClass('d-none');
            $(this).siblings('.error-icon').addClass('d-none');
        } else if (email) {
            $(this).siblings('.success-icon').addClass('d-none');
            $(this).siblings('.error-icon').removeClass('d-none');
        } else {
            $(this).siblings('.success-icon, .error-icon').addClass('d-none');
        }
    });

    // Phone Number Formatting
    $('#regPhone').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });

    // Aadhar Number Formatting
    $('#aadhar_number_field').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 12);
    });

    // PAN Number Formatting
    $('#regPAN').on('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
    });

    // File Upload Handlers
    $('#aadhar_document').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#aadharFileName').removeClass('d-none').html(`<i class="bi bi-file-check me-2"></i>${file.name}`);
        }
    });

    $('#pan_document').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#panFileName').removeClass('d-none').html(`<i class="bi bi-file-check me-2"></i>${file.name}`);
        }
    });

    // Extract Aadhar Button
    extractButton.on('click', function () {
        const fileInput = $('#aadhar_document')[0];

        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing File',
                text: 'Please upload an Aadhar PDF or image first.'
            });
            return;
        }

        const formData = new FormData();
        formData.append('aadhar_document', fileInput.files[0]);

        const originalText = extractButton.html();
        extractButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Extracting...');

        axios.post(form.data('extractUrl'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(({ data }) => {
            $('#aadhar_number_field').val(data.aadhar_number);
            Swal.fire({
                icon: 'success',
                title: 'Extracted!',
                text: 'Aadhar number extracted successfully.',
                timer: 2000
            });
        }).catch((error) => {
            const message = error.response?.data?.message || 'Failed to extract Aadhar number.';
            Swal.fire({
                icon: 'error',
                title: 'Extraction Failed',
                text: message
            });
        }).finally(() => {
            extractButton.prop('disabled', false).html(originalText);
        });
    });

    // Form Submission
    form.on('submit', function (e) {
        e.preventDefault();
        
        if (!validateStep1()) {
            showStep(1);
            updateProgress();
            return;
        }

        const submitBtn = $('#submitRegBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnLoader = submitBtn.find('.btn-loader');

        btnText.addClass('d-none');
        btnLoader.removeClass('d-none');
        submitBtn.prop('disabled', true);

        const formData = new FormData(this);

        axios.post(form.attr('action'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(({ data }) => {
            Swal.fire({
                icon: 'success',
                title: 'Welcome!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        }).catch((error) => {
            if (error.response?.status === 422) {
                const validationErrors = Object.values(error.response.data.errors || {}).flat().join('<br>');
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    html: validationErrors || 'Please check your input.'
                });
            } else {
                const message = error.response?.data?.message || 'Registration failed.';
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: message
                });
            }
        }).finally(() => {
            btnText.removeClass('d-none');
            btnLoader.addClass('d-none');
            submitBtn.prop('disabled', false);
        });
    });
});

