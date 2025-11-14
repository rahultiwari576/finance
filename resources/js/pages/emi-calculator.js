import axios from 'axios';
import Swal from 'sweetalert2';

$(function () {
    const form = $('#guestEmiForm');
    const resultsContainer = $('#guestEmiResults');
    const resultsPlaceholder = $('#resultsPlaceholder');
    const emiAmount = $('#guestEmiAmount');
    const totalRepayment = $('#guestTotalRepayment');
    const penalty = $('#guestPenalty');
    const totalInterest = $('#totalInterest');
    const tenureDisplay = $('#tenureDisplay');
    const scheduleBody = $('#guestEmiSchedule');

    // Slider synchronization
    const syncSlider = (sliderId, inputId) => {
        $(sliderId).on('input', function() {
            $(inputId).val($(this).val());
            formatCurrency(inputId);
            if (form[0].checkValidity()) {
                calculateEMI();
            }
        });
    };

    const syncInput = (inputId, sliderId) => {
        $(inputId).on('input', function() {
            const value = $(this).val();
            if (value) {
                $(sliderId).val(value);
            }
            if (form[0].checkValidity()) {
                calculateEMI();
            }
        });
    };

    // Format currency input
    const formatCurrency = (inputId) => {
        const input = $(inputId);
        if (inputId === '#principal_amount' || inputId === '#penalty_amount') {
            const value = input.val();
            if (value) {
                // Add thousand separators for display (optional)
                // input.val(parseInt(value).toLocaleString('en-IN'));
            }
        }
    };

    // Initialize sliders
    syncSlider('#principal_range', '#principal_amount');
    syncSlider('#interest_range', '#interest_rate');
    syncSlider('#tenure_range', '#tenure_months');

    syncInput('#principal_amount', '#principal_range');
    syncInput('#interest_rate', '#interest_range');
    syncInput('#tenure_months', '#tenure_range');

    // Calculate EMI function
    const calculateEMI = () => {
        const principal = parseFloat($('#principal_amount').val()) || 0;
        const rate = parseFloat($('#interest_rate').val()) || 0;
        const tenure = parseInt($('#tenure_months').val()) || 0;

        if (principal < 1000 || rate < 1 || tenure < 1) {
            return;
        }

        // Simple EMI calculation for preview
        const monthlyRate = rate / 12 / 100;
        const emi = principal * monthlyRate * Math.pow(1 + monthlyRate, tenure) / (Math.pow(1 + monthlyRate, tenure) - 1);
        const total = emi * tenure;
        const interest = total - principal;

        // Update preview (if you want real-time preview)
        // This is optional - you can remove if you only want calculation on submit
    };

    // Format number with Indian currency
    const formatINR = (amount) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount);
    };

    // Animate number
    const animateValue = (element, start, end, duration) => {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.text(formatINR(current));
        }, 16);
    };

    form.on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Calculating...');

        axios.post(form.attr('action') ?? form.data('url'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
            .then(({ data }) => {
                const { results } = data;
                const principal = parseFloat(formData.get('principal_amount'));
                const penaltyAmount = parseFloat(formData.get('custom_penalty_amount') || window.DEFAULT_PENALTY || 100);
                const tenure = parseInt(formData.get('tenure_months'));

                // Calculate total interest
                const totalInterestAmount = results.total_repayment - principal;

                // Animate values
                animateValue(emiAmount, 0, results.emi_amount, 1000);
                animateValue(totalRepayment, 0, results.total_repayment, 1000);
                animateValue(totalInterest, 0, totalInterestAmount, 1000);
                
                // Set penalty and tenure
                penalty.text(formatINR(penaltyAmount));
                tenureDisplay.text(tenure + 'M');

                // Render schedule
                scheduleBody.html(results.schedule.map((item, index) => {
                    const date = new Date(item.due_date);
                    const formattedDate = date.toLocaleDateString('en-IN', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                    
                    return `
                        <tr style="animation: fadeIn 0.3s ease ${index * 0.05}s both;">
                            <td>${item.installment_number}</td>
                            <td>${formattedDate}</td>
                            <td class="text-end fw-semibold">${formatINR(item.amount)}</td>
                        </tr>
                    `;
                }).join(''));

                // Show results
                resultsPlaceholder.addClass('d-none');
                resultsContainer.removeClass('d-none');
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: resultsContainer.offset().top - 100
                }, 500);

                submitBtn.prop('disabled', false).html(originalText);
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Calculation Error',
                    text: error.response?.data?.message || 'Unable to calculate EMI. Please check your inputs.',
                    confirmButtonColor: '#667eea'
                });
                submitBtn.prop('disabled', false).html(originalText);
            });
    });

    // Real-time input validation and formatting
    $('#principal_amount, #penalty_amount').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            $(this).val(parseInt(value));
        }
    });

    // Add input animations
    $('.form-control-lg').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
});
