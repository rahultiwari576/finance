@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Profile</span>
                <span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'primary' }}">{{ ucfirst($user->role) }}</span>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> {{ $user->name }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                <p class="mb-1"><strong>Aadhar:</strong> {{ $user->aadhar_number }}</p>
                <p class="mb-1"><strong>PAN:</strong> {{ $user->pan_number }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $user->phone_number }}</p>
                @if($user->address)
                    <p class="mb-1"><strong>Address:</strong> {{ $user->address }}</p>
                @endif
                @if($user->city || $user->state)
                    <p class="mb-1"><strong>Location:</strong> {{ trim(($user->city ?? '') . ', ' . ($user->state ?? ''), ', ') }}</p>
                @endif
                @if($user->profession)
                    <p class="mb-1"><strong>Profession:</strong> {{ $user->profession }}</p>
                @endif
                <div class="mt-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary w-100">Edit Profile</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Quick Actions</span>
                <a class="btn btn-link btn-sm" href="{{ route('emi.guest') }}">Smart EMI Calculator</a>
            </div>
            <div class="card-body">
                @if($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-danger w-100 mb-2">Admin Dashboard</a>
                @endif
                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#loanModal">Apply New Loan</button>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Loan Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="card bg-primary text-white summary-card loan-summary-card h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="summary-icon mb-2">
                                    <i class="bi bi-cash-stack display-6"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($loanSummary['total_principal'], 2) }}</h3>
                                <p class="mb-0 opacity-75 mt-2">Total Principal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card bg-success text-white summary-card loan-summary-card h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="summary-icon mb-2">
                                    <i class="bi bi-currency-exchange display-6"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($loanSummary['total_repayment'], 2) }}</h3>
                                <p class="mb-0 opacity-75 mt-2">Total Repayment</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card bg-warning text-dark summary-card loan-summary-card h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="summary-icon mb-2">
                                    <i class="bi bi-hourglass-split display-6"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($loanSummary['pending_amount'], 2) }}</h3>
                                <p class="mb-0 opacity-75 mt-2">Pending Amount</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card bg-info text-white summary-card loan-summary-card h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="summary-icon mb-2">
                                    <i class="bi bi-calendar-check display-6"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $loanSummary['pending_installments'] }}</h3>
                                <p class="mb-0 opacity-75 mt-2">Pending EMIs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Your Loans</span>
                <button class="btn btn-outline-primary btn-sm" id="refreshLoans">Refresh</button>
            </div>
            <div class="card-body" id="loansContainer" data-is-admin="{{ $user->isAdmin() ? 'true' : 'false' }}">
                <p class="text-muted">Loading loans...</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>EMI Reminders</span>
                <button class="btn btn-outline-secondary btn-sm" id="refreshReminders">Refresh</button>
            </div>
            <div class="card-body" id="remindersContainer" data-url="{{ route('loans.reminders') }}">
                <p class="text-muted">Fetching reminders...</p>
            </div>
        </div>
    </div>
</div>

@include('partials.loan-modal')

<!-- Loan Details Modal -->
<div class="modal fade" id="loanDetailsModal" tabindex="-1" aria-labelledby="loanDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanDetailsModalLabel">Loan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="loanDetailsContent">
                <p class="text-muted">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Payment Flow Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentContent">
                <!-- Payment content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- EMI Reminders Modal -->
<div class="modal fade" id="remindersModal" tabindex="-1" aria-labelledby="remindersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remindersModalLabel">EMI Reminders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="remindersModalContent">
                <p class="text-muted">Loading...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-success {
        background-color: #d1e7dd !important;
    }
    .table-warning {
        background-color: #fff3cd !important;
        font-weight: 500;
    }
    .table-light {
        background-color: #f8f9fa !important;
    }
    .table-warning td {
        border-color: #ffc107;
    }
    .table-success td {
        border-color: #198754;
    }
    .mark-paid {
        position: relative;
    }
    
    /* Loan Summary Cards */
    .summary-card {
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }

    .summary-icon {
        opacity: 0.8;
    }

    .summary-card h3 {
        font-size: 2rem;
    }

    .loan-summary-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .loan-summary-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
    }

    .loan-summary-card {
        height: 100%;
    }

    .loan-summary-card .card-body {
        padding: 1.5rem;
        min-height: 180px;
    }

    .loan-summary-card h3 {
        font-size: 1.75rem;
        word-break: break-word;
        line-height: 1.2;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    /* Ensure equal height cards */
    .row.g-3 > [class*="col-"] {
        display: flex;
    }

    .row.g-3 > [class*="col-"] > .card {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    /* Loan list item hover effect */
    .loan-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.2s ease;
    }
    
    /* Reminder list item hover effect */
    .reminder-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.2s ease;
    }
    
    .table-danger {
        background-color: #f8d7da !important;
    }
    
    .table-danger td {
        border-color: #dc3545;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .summary-card h3 {
            font-size: 1.5rem;
        }
        
        .summary-icon i {
            font-size: 2rem !important;
        }

        .loan-summary-card .card-body {
            padding: 1rem;
        }

        .modal-xl {
            max-width: 95%;
        }
        
        .loan-item h6 {
            font-size: 1rem;
        }
        
        .loan-item p {
            font-size: 0.85rem;
        }
        
        .loan-item small {
            font-size: 0.75rem;
        }
        
        #loanDetailsContent .col-md-6 {
            margin-bottom: 1rem;
        }
        
        #loanDetailsContent .col-md-3 {
            margin-bottom: 0.5rem;
        }
        
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .payment-method {
            padding: 0.75rem;
        }
    }
    
    /* Sticky table header */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }

    /* Loan Modal Styling */
    .loan-modal-content {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .loan-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }

    .loan-modal-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .loan-input {
        transition: all 0.3s ease;
        border-left: none;
    }

    .loan-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        transform: translateY(-2px);
    }

    .input-group-text {
        border-right: none;
        font-weight: 600;
    }

    .input-group .form-control:focus + .input-group-text,
    .input-group .form-control:focus ~ .input-group-text {
        border-color: #667eea;
    }

    #loanCalculationPreview {
        animation: slideDown 0.5s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .emi-preview-card {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 1rem;
        backdrop-filter: blur(10px);
        transition: transform 0.3s ease;
    }

    .emi-preview-card:hover {
        transform: scale(1.05);
        background: rgba(255,255,255,0.25);
    }

    .emi-preview-card .value {
        font-size: 1.5rem;
        font-weight: bold;
    }

    #previewLoan, #submitLoanBtn {
        transition: all 0.3s ease;
    }

    #previewLoan:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    #submitLoanBtn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    #submitLoanBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-label i {
        color: #667eea;
    }

    /* Loading animation */
    .loan-input.loading {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24'%3E%3Cpath fill='%23667eea' d='M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z' opacity='.25'/%3E%3Cpath fill='%23667eea' d='M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z'%3E%3CanimateTransform attributeName='transform' dur='0.75s' repeatCount='indefinite' type='rotate' values='0 12 12;360 12 12'/%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 40px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ mix('js/home.js') }}"></script>
@endpush

