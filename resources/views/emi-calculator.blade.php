@extends('layouts.app')

@section('title', 'Smart EMI Calculator')

@section('content')
<div class="emi-calculator-wrapper">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark mb-2">
                        <i class="bi bi-calculator"></i> Smart EMI Calculator
                    </h2>
                    <p class="text-muted">Calculate your loan EMI instantly with our advanced calculator</p>
                </div>

                <div class="row g-4">
                    <!-- Input Section -->
                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0 calculator-card">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-sliders me-2"></i>Loan Details
                                </h5>
                                
                                <form id="guestEmiForm" action="{{ route('emi.calculate') }}">
                                    @csrf
                                    
                                    <!-- Loan Amount -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-currency-rupee me-1"></i>Loan Amount
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-primary text-white">₹</span>
                                            <input type="number" 
                                                   name="principal_amount" 
                                                   id="principal_amount"
                                                   class="form-control form-control-lg" 
                                                   required 
                                                   min="1000" 
                                                   placeholder="Enter loan amount"
                                                   value="100000">
                                        </div>
                                        <div class="range-container mt-2">
                                            <input type="range" 
                                                   class="form-range" 
                                                   id="principal_range"
                                                   min="10000" 
                                                   max="10000000" 
                                                   step="10000" 
                                                   value="100000">
                                            <div class="d-flex justify-content-between small text-muted">
                                                <span>₹10K</span>
                                                <span>₹1Cr</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Interest Rate -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-percent me-1"></i>Interest Rate (Annual)
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" 
                                                   name="interest_rate" 
                                                   id="interest_rate"
                                                   class="form-control form-control-lg" 
                                                   required 
                                                   step="0.1" 
                                                   min="1" 
                                                   max="30"
                                                   placeholder="Enter interest rate"
                                                   value="12">
                                            <span class="input-group-text bg-success text-white">%</span>
                                        </div>
                                        <div class="range-container mt-2">
                                            <input type="range" 
                                                   class="form-range" 
                                                   id="interest_range"
                                                   min="1" 
                                                   max="30" 
                                                   step="0.1" 
                                                   value="12">
                                            <div class="d-flex justify-content-between small text-muted">
                                                <span>1%</span>
                                                <span>30%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tenure -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-month me-1"></i>Loan Tenure
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" 
                                                   name="tenure_months" 
                                                   id="tenure_months"
                                                   class="form-control form-control-lg" 
                                                   required 
                                                   min="1" 
                                                   max="360"
                                                   placeholder="Enter tenure in months"
                                                   value="12">
                                            <span class="input-group-text bg-info text-white">Months</span>
                                        </div>
                                        <div class="range-container mt-2">
                                            <input type="range" 
                                                   class="form-range" 
                                                   id="tenure_range"
                                                   min="6" 
                                                   max="360" 
                                                   step="1" 
                                                   value="12">
                                            <div class="d-flex justify-content-between small text-muted">
                                                <span>6M</span>
                                                <span>30Y</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Penalty -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Late Payment Penalty
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-warning text-dark">₹</span>
                                            <input type="number" 
                                                   name="custom_penalty_amount" 
                                                   id="penalty_amount"
                                                   class="form-control form-control-lg" 
                                                   min="0" 
                                                   placeholder="Default ₹{{ config('loan.default_penalty', 100) }}"
                                                   value="{{ config('loan.default_penalty', 100) }}">
                                        </div>
                                        <small class="text-muted">Leave empty for default penalty</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                                        <i class="bi bi-calculator me-2"></i>Calculate EMI
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0 results-card" id="resultsCard">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-graph-up me-2"></i>EMI Breakdown
                                </h5>
                                
                                <div id="guestEmiResults" class="d-none">
                                    <!-- Summary Cards -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <div class="summary-card bg-primary text-white">
                                                <div class="summary-icon">
                                                    <i class="bi bi-calendar-check"></i>
                                                </div>
                                                <div class="summary-content">
                                                    <small class="d-block opacity-75">Monthly EMI</small>
                                                    <h4 class="mb-0 fw-bold" id="guestEmiAmount">₹0</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="summary-card bg-success text-white">
                                                <div class="summary-icon">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                                <div class="summary-content">
                                                    <small class="d-block opacity-75">Total Amount</small>
                                                    <h4 class="mb-0 fw-bold" id="guestTotalRepayment">₹0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="info-box mb-4">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="info-item">
                                                    <div class="info-value text-primary" id="totalInterest">₹0</div>
                                                    <div class="info-label">Total Interest</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="info-item">
                                                    <div class="info-value text-warning" id="guestPenalty">₹0</div>
                                                    <div class="info-label">Penalty</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="info-item">
                                                    <div class="info-value text-info" id="tenureDisplay">0M</div>
                                                    <div class="info-label">Tenure</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Schedule Table -->
                                    <div class="schedule-section">
                                        <h6 class="mb-3">
                                            <i class="bi bi-list-ul me-2"></i>Payment Schedule
                                        </h6>
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-hover table-sm">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Due Date</th>
                                                        <th class="text-end">Amount (₹)</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="guestEmiSchedule"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Placeholder -->
                                <div id="resultsPlaceholder" class="text-center py-5">
                                    <i class="bi bi-calculator display-1 text-muted"></i>
                                    <p class="text-muted mt-3">Fill in the details and click Calculate to see results</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .emi-calculator-wrapper {
        background: transparent;
        min-height: calc(100vh - 56px);
    }

    .calculator-card, .results-card {
        border-radius: 20px;
        background: #ffffff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .calculator-card:hover, .results-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    }

    .form-control-lg {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 2px solid #e9ecef;
        border-right: none;
    }

    .input-group-lg .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }

    .range-container {
        padding: 0 10px;
    }

    .form-range {
        height: 8px;
        cursor: pointer;
    }

    .form-range::-webkit-slider-thumb {
        background: #667eea;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .form-range::-moz-range-thumb {
        background: #667eea;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .summary-card {
        border-radius: 15px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .summary-card:hover {
        transform: scale(1.05);
    }

    .summary-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .summary-content h4 {
        font-size: 1.5rem;
    }

    .info-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
    }

    .info-item {
        padding: 10px;
    }

    .info-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .schedule-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #e9ecef;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    #resultsPlaceholder {
        opacity: 0.5;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #guestEmiResults.d-none {
        display: none !important;
    }

    #guestEmiResults:not(.d-none) {
        animation: fadeIn 0.5s ease;
    }

    @media (max-width: 768px) {
        h2 {
            font-size: 1.5rem;
        }
        
        .summary-card {
            flex-direction: column;
            text-align: center;
        }
        
        .summary-icon {
            font-size: 2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ mix('js/emi-calculator.js') }}"></script>
@endpush
