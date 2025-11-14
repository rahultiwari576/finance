import axios from 'axios';
import Swal from 'sweetalert2';

$(function () {
    // Password Toggle
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Email validation
    $('#email').on('blur', function() {
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

    // Aadhar number formatting
    $('#aadhar_number').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 12);
    });

    // OTP Input Handling
    const otpInputs = $('.otp-input');
    otpInputs.on('input', function() {
        const value = $(this).val().replace(/\D/g, '').slice(0, 1);
        $(this).val(value);
        
        if (value && $(this).index() < otpInputs.length - 1) {
            otpInputs.eq($(this).index() + 1).focus();
        }
        
        // Combine all OTP digits
        const fullCode = otpInputs.map(function() {
            return $(this).val();
        }).get().join('');
        $('#otp_full_code').val(fullCode);
    });

    otpInputs.on('keydown', function(e) {
        if (e.key === 'Backspace' && !$(this).val() && $(this).index() > 0) {
            otpInputs.eq($(this).index() - 1).focus();
        }
    });

    // OTP Timer
    let otpTimerInterval;
    function startOTPTimer() {
        let timeLeft = 300; // 5 minutes
        const timerElement = $('#otpTimer');
        
        clearInterval(otpTimerInterval);
        
        otpTimerInterval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
            
            if (timeLeft <= 0) {
                clearInterval(otpTimerInterval);
                timerElement.text('Expired');
                Swal.fire('OTP Expired', 'Please request a new OTP', 'warning');
            }
            
            timeLeft--;
        }, 1000);
    }

    // Email Login Form
    $('#emailLoginForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#emailLoginBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnLoader = submitBtn.find('.btn-loader');

        btnText.addClass('d-none');
        btnLoader.removeClass('d-none');
        submitBtn.prop('disabled', true);

        axios.post(form.attr('action'), form.serialize())
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome Back!',
                    text: 'Logged in successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect;
                });
            })
            .catch((error) => {
                const message = error.response?.data?.message || 'Unable to login';
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: message
                });
            })
            .finally(() => {
                btnText.removeClass('d-none');
                btnLoader.addClass('d-none');
                submitBtn.prop('disabled', false);
            });
    });

    // Aadhar Login Form
    $('#aadharLoginForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#aadharLoginBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnLoader = submitBtn.find('.btn-loader');

        btnText.addClass('d-none');
        btnLoader.removeClass('d-none');
        submitBtn.prop('disabled', true);

        axios.post(form.attr('action'), form.serialize())
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent!',
                    text: data.message,
                    timer: 2000
                });
                $('#otp_id').val(data.otp_token);
                $('#otpVerificationForm').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                startOTPTimer();
            })
            .catch((error) => {
                const message = error.response?.data?.message || 'Unable to send OTP';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            })
            .finally(() => {
                btnText.removeClass('d-none');
                btnLoader.addClass('d-none');
                submitBtn.prop('disabled', false);
            });
    });

    // OTP Verification Form
    $('#otpVerificationForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#otpVerifyBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnLoader = submitBtn.find('.btn-loader');
        
        // Get full OTP code
        const fullCode = otpInputs.map(function() {
            return $(this).val();
        }).get().join('');
        
        if (fullCode.length !== 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete OTP',
                text: 'Please enter all 6 digits'
            });
            return;
        }

        const formData = form.serialize() + '&code=' + fullCode;

        btnText.addClass('d-none');
        btnLoader.removeClass('d-none');
        submitBtn.prop('disabled', true);
        clearInterval(otpTimerInterval);

        axios.post(form.attr('action'), formData)
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Verified!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect;
                });
            })
            .catch((error) => {
                const message = error.response?.data?.message || 'Failed to verify OTP';
                Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: message
                });
                // Clear OTP inputs
                otpInputs.val('');
                otpInputs.first().focus();
                startOTPTimer();
            })
            .finally(() => {
                btnText.removeClass('d-none');
                btnLoader.addClass('d-none');
                submitBtn.prop('disabled', false);
            });
    });
});

