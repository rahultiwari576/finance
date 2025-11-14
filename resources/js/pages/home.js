import axios from 'axios';
import Swal from 'sweetalert2';

$(function () {
    const loansContainer = $('#loansContainer');
    const remindersContainer = $('#remindersContainer');
    const loanForm = $('#loanApplicationForm');

    const loansUrl = loanForm.data('listUrl');
    const calculateUrl = loanForm.data('calculateUrl');


    const fetchReminders = () => {
        remindersContainer.html('<p class="text-muted">Fetching reminders...</p>');
        return axios.get(remindersContainer.data('url'))
            .then(({ data }) => {
                renderReminders(data.reminders);
                return data.reminders;
            })
            .catch(() => {
                remindersContainer.html('<p class="text-danger">Unable to fetch reminders.</p>');
                throw new Error('Failed to fetch reminders');
            });
    };

    const renderLoans = (loans = []) => {
        if (!loans.length) {
            loansContainer.html('<p class="text-muted">No loans yet. Apply for your first loan.</p>');
            return;
        }

        // Simple list format
        const template = loans.map((loan) => {
            const paidCount = loan.installments.filter(inst => inst.status === 'paid').length;
            const unpaidCount = loan.installments.filter(inst => inst.status !== 'paid').length;
            const statusBadge = loan.status === 'active' 
                ? '<span class="badge bg-success">Active</span>' 
                : loan.status === 'completed' 
                    ? '<span class="badge bg-secondary">Completed</span>' 
                    : '<span class="badge bg-warning">Pending</span>';

            return `
                <div class="list-group-item list-group-item-action loan-item" 
                     data-loan-id="${loan.id}" 
                     style="cursor: pointer;">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Loan #${loan.id}</h6>
                            <p class="mb-1 text-muted">
                                Principal: ₹${Number(loan.principal_amount).toFixed(2)} | 
                                EMI: ₹${Number(loan.emi_amount).toFixed(2)} | 
                                ${statusBadge}
                            </p>
                            <small class="text-muted">
                                Paid: ${paidCount} | Pending: ${unpaidCount} | 
                                Next Due: ${loan.next_due_date ?? 'N/A'}
                            </small>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            `;
        }).join('');

        loansContainer.html(`<div class="list-group">${template}</div>`);
    };

    // Store loans data globally for modal access
    let loansData = [];

    const fetchLoans = () => {
        loansContainer.html('<p class="text-muted">Loading loans...</p>');
        return axios.get(loansUrl)
            .then(({ data }) => {
                loansData = data.loans;
                renderLoans(data.loans);
                return data.loans;
            })
            .catch(() => {
                loansContainer.html('<p class="text-danger">Failed to load loans.</p>');
                throw new Error('Failed to load loans');
            });
    };

    // Store reminders data globally for modal access
    let remindersData = [];

    const renderReminders = (reminders = []) => {
        if (!reminders.length) {
            remindersContainer.html('<p class="text-muted">No pending EMI reminders.</p>');
            remindersData = [];
            return;
        }

        remindersData = reminders;

        // Group reminders by loan_id
        const remindersByLoan = reminders.reduce((acc, reminder) => {
            if (!acc[reminder.loan_id]) {
                acc[reminder.loan_id] = [];
            }
            acc[reminder.loan_id].push(reminder);
            return acc;
        }, {});

        // Simple list format - show each loan with reminder count
        const template = Object.keys(remindersByLoan).map((loanId) => {
            const loanReminders = remindersByLoan[loanId];
            const totalAmount = loanReminders.reduce((sum, r) => sum + r.amount + r.penalty_amount, 0);
            const earliestDue = loanReminders.sort((a, b) => new Date(a.due_date) - new Date(b.due_date))[0];
            const statusBadge = earliestDue.status === 'overdue' 
                ? '<span class="badge bg-danger">Overdue</span>' 
                : '<span class="badge bg-warning text-dark">Upcoming</span>';

            return `
                <div class="list-group-item list-group-item-action reminder-item" 
                     data-loan-id="${loanId}" 
                     style="cursor: pointer;">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Loan #${loanId}</h6>
                            <p class="mb-1 text-muted">
                                ${loanReminders.length} EMI${loanReminders.length > 1 ? 's' : ''} Pending | 
                                Total: ₹${Number(totalAmount).toFixed(2)} | 
                                ${statusBadge}
                            </p>
                            <small class="text-muted">
                                Next Due: ${earliestDue.due_date} | 
                                Amount: ₹${Number(earliestDue.amount).toFixed(2)} + Penalty: ₹${Number(earliestDue.penalty_amount).toFixed(2)}
                            </small>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            `;
        }).join('');

        remindersContainer.html(`<div class="list-group">${template}</div>`);
    };

    // Real-time EMI calculation on input change
    let calculationTimeout;
    const calculateEMI = () => {
        clearTimeout(calculationTimeout);
        const principal = parseFloat($('#loanPrincipal').val()) || 0;
        const interest = parseFloat($('#loanInterest').val()) || 0;
        const tenure = parseInt($('#loanTenure').val()) || 0;

        if (principal >= 1000 && interest >= 1 && tenure >= 1) {
            calculationTimeout = setTimeout(() => {
                const formData = new FormData(loanForm[0]);
                const previewCard = $('#loanCalculationPreview');
                const previewContent = $('#emiPreviewContent');
                
                // Show loading state
                previewCard.removeClass('d-none');
                previewContent.html('<div class="col-12 text-center"><div class="spinner-border spinner-border-sm" role="status"></div> <span class="ms-2">Calculating...</span></div>');

                axios.post(calculateUrl, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                    .then(({ data }) => {
                        const { results } = data;
                        const totalInterest = results.total_repayment - results.principal_amount;
                        
                        previewContent.html(`
                            <div class="col-md-4">
                                <div class="emi-preview-card text-center">
                                    <i class="bi bi-calendar-check display-6 mb-2"></i>
                                    <div class="value">₹${Number(results.emi_amount).toFixed(2)}</div>
                                    <small class="opacity-75">Monthly EMI</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emi-preview-card text-center">
                                    <i class="bi bi-currency-exchange display-6 mb-2"></i>
                                    <div class="value">₹${Number(results.total_repayment).toFixed(2)}</div>
                                    <small class="opacity-75">Total Repayment</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emi-preview-card text-center">
                                    <i class="bi bi-graph-up display-6 mb-2"></i>
                                    <div class="value">₹${Number(totalInterest).toFixed(2)}</div>
                                    <small class="opacity-75">Total Interest</small>
                                </div>
                            </div>
                        `);
                    })
                    .catch(() => {
                        previewCard.addClass('d-none');
                    });
            }, 500); // Debounce for 500ms
        } else {
            $('#loanCalculationPreview').addClass('d-none');
        }
    };

    // Attach real-time calculation to inputs
    $('#loanPrincipal, #loanInterest, #loanTenure').on('input', calculateEMI);

    // Preview button click
    $('#previewLoan').on('click', function () {
        if (!loanForm[0].checkValidity()) {
            loanForm[0].reportValidity();
            return;
        }
        calculateEMI();
    });

    loanForm.on('submit', function (e) {
        e.preventDefault();
        const submitBtn = $('#submitLoanBtn');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');
        
        const formData = new FormData(this);

        axios.post(loanForm.attr('action'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Application Submitted!',
                    text: data.message,
                    showConfirmButton: true,
                    timer: 3000
                });
                $('#loanModal').modal('hide');
                loanForm[0].reset();
                $('#loanCalculationPreview').addClass('d-none');
                fetchLoans();
                fetchReminders();
            })
            .catch((error) => {
                let message = 'Loan application failed.';
                if (error.response?.data?.message) {
                    message = error.response.data.message;
                } else if (error.response?.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    message = errors.join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Application Failed',
                    html: message
                });
            })
            .finally(() => {
                submitBtn.prop('disabled', false).html(originalText);
            });
    });

    // Open loan details modal on loan item click
    loansContainer.on('click', '.loan-item', function() {
        const loanId = $(this).data('loan-id');
        const loan = loansData.find(l => l.id === loanId);
        if (loan) {
            showLoanDetailsModal(loan);
        }
    });

    // Open reminders modal on reminder item click
    remindersContainer.on('click', '.reminder-item', function() {
        const loanId = $(this).data('loan-id');
        const loanReminders = remindersData.filter(r => r.loan_id == loanId);
        if (loanReminders.length > 0) {
            showRemindersModal(loanId, loanReminders);
        }
    });

    // Show loan details modal
    const showLoanDetailsModal = (loan) => {
        const isAdmin = loansContainer.data('is-admin') === 'true' || loansContainer.data('is-admin') === true;
        
        // Calculate remaining balance
        const paidAmount = loan.installments
            .filter(inst => inst.status === 'paid')
            .reduce((sum, inst) => sum + inst.amount, 0);
        const remainingBalance = loan.total_repayment - paidAmount;

        // Separate paid and pending EMIs
        const paidEMIs = loan.installments.filter(inst => inst.status === 'paid');
        const pendingEMIs = loan.installments.filter(inst => inst.status !== 'paid');
        const upcomingEmi = pendingEMIs.length > 0 
            ? pendingEMIs.sort((a, b) => new Date(a.due_date) - new Date(b.due_date))[0]
            : null;

        // Render paid EMIs
        const paidEMIsHtml = paidEMIs.map(emi => `
            <tr class="table-success">
                <td>${emi.due_date}</td>
                <td>₹${Number(emi.amount).toFixed(2)}</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td>${emi.paid_at ? new Date(emi.paid_at).toLocaleDateString() : 'N/A'}</td>
                <td>
                    ${isAdmin ? `<button class="btn btn-sm btn-danger delete-emi" data-emi-id="${emi.id}">Delete</button>` : '-'}
                </td>
            </tr>
        `).join('');

        // Render pending EMIs
        const pendingEMIsHtml = pendingEMIs.map(emi => {
            const isUpcoming = upcomingEmi && emi.id === upcomingEmi.id;
            const rowClass = isUpcoming ? 'table-warning' : 'table-light';
            return `
                <tr class="${rowClass}">
                    <td><strong>${emi.due_date}</strong></td>
                    <td>₹${Number(emi.amount).toFixed(2)}</td>
                    <td>
                        ${isUpcoming 
                            ? '<span class="badge bg-warning text-dark">Upcoming</span>' 
                            : '<span class="badge bg-secondary">Pending</span>'}
                    </td>
                    <td>-</td>
                    <td>
                        <button class="btn btn-sm btn-primary pay-now" data-emi-id="${emi.id}" data-emi-amount="${emi.amount}" data-emi-date="${emi.due_date}">
                            Pay Now
                        </button>
                        ${isAdmin ? `<button class="btn btn-sm btn-danger delete-emi ms-1" data-emi-id="${emi.id}">Delete</button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        const modalContent = `
            <div class="row">
                <!-- Total Loan Details -->
                <div class="col-12 mb-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Total Loan Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <strong>Principal Amount:</strong><br>
                                    <span class="text-primary">₹${Number(loan.principal_amount).toFixed(2)}</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Interest Rate:</strong><br>
                                    <span class="text-primary">${Number(loan.interest_rate).toFixed(2)}%</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Total EMIs:</strong><br>
                                    <span class="text-primary">${loan.installments.length}</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Remaining Balance:</strong><br>
                                    <span class="text-danger">₹${Number(remainingBalance).toFixed(2)}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong>Next EMI Date:</strong><br>
                                    <span class="text-warning">${loan.next_due_date || 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-${loan.status === 'active' ? 'success' : loan.status === 'completed' ? 'secondary' : 'warning'}">
                                        ${loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMI Status Section -->
                <div class="col-12">
                    <div class="row">
                        <!-- Paid EMIs -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Paid EMIs (${paidEMIs.length})</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Due Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Paid Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${paidEMIs.length > 0 ? paidEMIsHtml : '<tr><td colspan="5" class="text-center text-muted">No paid EMIs</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending EMIs -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">Upcoming/Pending EMIs (${pendingEMIs.length})</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Due Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Paid Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${pendingEMIs.length > 0 ? pendingEMIsHtml : '<tr><td colspan="5" class="text-center text-muted">No pending EMIs</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#loanDetailsContent').html(modalContent);
        $('#loanDetailsModalLabel').text(`Loan #${loan.id} Details`);
        $('#loanDetailsModal').data('current-loan-id', loan.id);
        const modal = new bootstrap.Modal(document.getElementById('loanDetailsModal'));
        modal.show();
        
        // Store modal instance for refresh
        window.currentLoanModal = modal;
        window.currentLoanId = loan.id;
    };

    // Handle Pay Now button
    $(document).on('click', '.pay-now', function() {
        const emiId = $(this).data('emi-id');
        const emiAmount = $(this).data('emi-amount');
        const emiDate = $(this).data('emi-date');
        showPaymentModal(emiId, emiAmount, emiDate);
    });

    // Show payment modal
    const showPaymentModal = (emiId, amount, dueDate) => {
        const paymentContent = `
            <div class="payment-flow">
                <div class="mb-3">
                    <h6>Payment Details</h6>
                    <p class="mb-1"><strong>EMI Amount:</strong> ₹${Number(amount).toFixed(2)}</p>
                    <p class="mb-1"><strong>Due Date:</strong> ${dueDate}</p>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Select Payment Method</h6>
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action payment-method" data-method="upi">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>UPI</strong>
                                    <p class="mb-0 small text-muted">Pay via UPI (Google Pay, PhonePe, etc.)</p>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action payment-method" data-method="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Credit/Debit Card</strong>
                                    <p class="mb-0 small text-muted">Visa, Mastercard, RuPay</p>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action payment-method" data-method="wallet">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Digital Wallet</strong>
                                    <p class="mb-0 small text-muted">Paytm, Amazon Pay, etc.</p>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action payment-method" data-method="netbanking">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Net Banking</strong>
                                    <p class="mb-0 small text-muted">Direct bank transfer</p>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </button>
                    </div>
                </div>
                <input type="hidden" id="payment_emi_id" value="${emiId}">
            </div>
        `;

        $('#paymentContent').html(paymentContent);
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    };

    // Handle payment method selection
    $(document).on('click', '.payment-method', function() {
        const method = $(this).data('method');
        const emiId = $('#payment_emi_id').val();
        processPayment(emiId, method);
    });

    // Process payment
    const processPayment = (emiId, method) => {
        // Find the pay URL from loansData
        const allInstallments = loansData.flatMap(loan => 
            loan.installments.map(emi => ({ ...emi, loanId: loan.id }))
        );
        const emi = allInstallments.find(e => e.id == emiId);
        
        if (!emi) {
            Swal.fire('Error', 'EMI not found.', 'error');
            return;
        }

        Swal.fire({
            title: 'Processing Payment...',
            html: `Payment method: <strong>${method.toUpperCase()}</strong><br>Please wait...`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Simulate payment processing (replace with actual payment gateway integration)
        setTimeout(() => {
            axios.post(emi.pay_url, { 
                custom_penalty_amount: null,
                payment_method: method 
            })
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful!',
                    text: data.message || 'EMI marked as paid.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    // Refresh loans data
                    fetchLoans().then(() => {
                        // Refresh loan details modal if open
                        if (window.currentLoanId) {
                            const loan = loansData.find(l => l.id == window.currentLoanId);
                            if (loan) {
                                showLoanDetailsModal(loan);
                            } else {
                                bootstrap.Modal.getInstance(document.getElementById('loanDetailsModal')).hide();
                            }
                        }
                        // Refresh reminders
                        fetchReminders().then(() => {
                            // Refresh reminders modal if open
                            const remindersModal = bootstrap.Modal.getInstance(document.getElementById('remindersModal'));
                            if (remindersModal && document.getElementById('remindersModal').classList.contains('show')) {
                                const currentLoanId = window.currentReminderLoanId;
                                if (currentLoanId) {
                                    const loanReminders = remindersData.filter(r => r.loan_id == currentLoanId);
                                    if (loanReminders.length > 0) {
                                        showRemindersModal(currentLoanId, loanReminders);
                                    } else {
                                        remindersModal.hide();
                                    }
                                }
                            }
                        });
                    });
                });
            })
            .catch((error) => {
                const message = error.response?.data?.message || 'Payment failed.';
                Swal.fire('Error', message, 'error');
            });
        }, 2000);
    };

    // Show reminders modal
    const showRemindersModal = (loanId, reminders) => {
        // Sort reminders by due date
        const sortedReminders = reminders.sort((a, b) => new Date(a.due_date) - new Date(b.due_date));
        
        // Calculate totals
        const totalAmount = sortedReminders.reduce((sum, r) => sum + r.amount, 0);
        const totalPenalty = sortedReminders.reduce((sum, r) => sum + r.penalty_amount, 0);
        const grandTotal = totalAmount + totalPenalty;

        // Render reminders table
        const remindersHtml = sortedReminders.map((reminder) => {
            const isOverdue = reminder.status === 'overdue';
            const rowClass = isOverdue ? 'table-danger' : 'table-warning';
            const statusBadge = isOverdue 
                ? '<span class="badge bg-danger">Overdue</span>' 
                : '<span class="badge bg-warning text-dark">Upcoming</span>';

            // Find the corresponding loan to get pay URL
            const loan = loansData.find(l => l.id == loanId);
            const emi = loan ? loan.installments.find(i => i.due_date === reminder.due_date) : null;
            const payUrl = emi ? emi.pay_url : null;

            return `
                <tr class="${rowClass}">
                    <td><strong>${reminder.due_date}</strong></td>
                    <td>₹${Number(reminder.amount).toFixed(2)}</td>
                    <td>₹${Number(reminder.penalty_amount).toFixed(2)}</td>
                    <td>₹${Number(reminder.amount + reminder.penalty_amount).toFixed(2)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        ${payUrl ? `
                            <button class="btn btn-sm btn-primary pay-now" 
                                    data-emi-id="${emi.id}" 
                                    data-emi-amount="${reminder.amount}" 
                                    data-emi-date="${reminder.due_date}">
                                Pay Now
                            </button>
                        ` : '-'}
                    </td>
                </tr>
            `;
        }).join('');

        const modalContent = `
            <div class="row">
                <!-- Summary Section -->
                <div class="col-12 mb-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Loan #${loanId} - Upcoming EMI Reminders</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <strong>Total Reminders:</strong><br>
                                    <span class="text-primary">${sortedReminders.length}</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Total EMI Amount:</strong><br>
                                    <span class="text-primary">₹${Number(totalAmount).toFixed(2)}</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Total Penalty:</strong><br>
                                    <span class="text-warning">₹${Number(totalPenalty).toFixed(2)}</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong>Grand Total:</strong><br>
                                    <span class="text-danger">₹${Number(grandTotal).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reminders Table -->
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">Upcoming EMI Reminders (${sortedReminders.length})</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Due Date</th>
                                            <th>EMI Amount</th>
                                            <th>Penalty</th>
                                            <th>Total Due</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${remindersHtml}
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th>₹${Number(totalAmount).toFixed(2)}</th>
                                            <th>₹${Number(totalPenalty).toFixed(2)}</th>
                                            <th><strong>₹${Number(grandTotal).toFixed(2)}</strong></th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#remindersModalContent').html(modalContent);
        $('#remindersModalLabel').text(`Loan #${loanId} - EMI Reminders`);
        window.currentReminderLoanId = loanId;
        const modal = new bootstrap.Modal(document.getElementById('remindersModal'));
        modal.show();
    };

    // Handle Delete EMI (Admin only)
    $(document).on('click', '.delete-emi', function() {
        const emiId = $(this).data('emi-id');
        
        Swal.fire({
            title: 'Delete EMI?',
            text: 'This will update the remaining balance. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/loans/installments/${emiId}`)
                    .then(({ data }) => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            // Refresh loans data
                            fetchLoans().then(() => {
                                // Refresh modal if open
                                if (window.currentLoanId) {
                                    const loan = loansData.find(l => l.id == window.currentLoanId);
                                    if (loan) {
                                        showLoanDetailsModal(loan);
                                    } else {
                                        // Loan data not found, close modal
                                        if (window.currentLoanModal) {
                                            window.currentLoanModal.hide();
                                        }
                                    }
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        const message = error.response?.data?.message || 'Failed to delete EMI.';
                        Swal.fire('Error', message, 'error');
                    });
            }
        });
    });

    loansContainer.on('click', '.delete-loan', function () {
        const loanId = $(this).data('loan-id');

        Swal.fire({
            title: 'Delete Loan?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/loans/${loanId}`)
                    .then(() => {
                        Swal.fire('Deleted!', 'Loan has been deleted.', 'success');
                        fetchLoans();
                        fetchReminders();
                    })
                    .catch(() => Swal.fire('Error', 'Could not delete loan.', 'error'));
            }
        });
    });

    $('#refreshLoans').on('click', fetchLoans);
    $('#refreshReminders').on('click', fetchReminders);

    // Loan Summary Cards Interactive
    $('.loan-summary-card').on('click', function() {
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 1000);
    });

    fetchLoans();
    fetchReminders();
});

