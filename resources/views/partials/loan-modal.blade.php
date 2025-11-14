<div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content loan-modal-content">
            <div class="modal-header loan-modal-header">
                <div class="d-flex align-items-center">
                    <div class="loan-modal-icon me-3">
                        <i class="bi bi-bank"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="loanModalLabel">Apply for a New Loan</h5>
                        <small class="text-muted">Fill in the details below to get started</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loanApplicationForm"
                    action="{{ route('loans.apply') }}"
                    data-list-url="{{ route('loans.list') }}"
                    data-calculate-url="{{ route('emi.calculate') }}">
                    @csrf
                    
                    <!-- Loan Amount -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-currency-rupee me-2"></i>Loan Amount (Principal)
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white">
                                <i class="bi bi-cash-stack"></i>
                            </span>
                            <input type="number" 
                                   name="principal_amount" 
                                   id="loanPrincipal" 
                                   class="form-control form-control-lg loan-input" 
                                   required 
                                   min="1000"
                                   placeholder="Enter loan amount">
                            <span class="input-group-text">₹</span>
                        </div>
                        <small class="text-muted">Minimum: ₹1,000</small>
                    </div>

                    <div class="row g-3">
                        <!-- Interest Rate -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-percent me-2"></i>Interest Rate (%)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-info text-white">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </span>
                                <input type="number" 
                                       name="interest_rate" 
                                       id="loanInterest" 
                                       class="form-control loan-input" 
                                       required 
                                       step="0.1" 
                                       min="1"
                                       placeholder="e.g., 12.5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- Tenure -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-month me-2"></i>Tenure (Months)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                                <input type="number" 
                                       name="tenure_months" 
                                       id="loanTenure" 
                                       class="form-control loan-input" 
                                       required 
                                       min="1"
                                       placeholder="e.g., 12">
                                <span class="input-group-text">Months</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Penalty -->
                    <div class="mb-4 mt-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-exclamation-triangle me-2"></i>Custom Penalty (₹)
                            <span class="badge bg-secondary ms-2">Optional</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-warning text-dark">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <input type="number" 
                                   name="custom_penalty_amount" 
                                   id="loanPenalty" 
                                   class="form-control loan-input" 
                                   min="0" 
                                   placeholder="Defaults to ₹{{ config('loan.default_penalty', 100) }}">
                            <span class="input-group-text">₹</span>
                        </div>
                        <small class="text-muted">Leave empty to use default penalty of ₹{{ config('loan.default_penalty', 100) }}</small>
                    </div>

                    <!-- EMI Preview Card -->
                    <div class="card border-0 shadow-sm mb-4 d-none" id="loanCalculationPreview">
                        <div class="card-body bg-gradient-primary text-white rounded">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-calculator display-6 me-3"></i>
                                <div>
                                    <h6 class="mb-0">EMI Preview</h6>
                                    <small class="opacity-75">Your estimated monthly payment</small>
                                </div>
                            </div>
                            <div class="row g-3" id="emiPreviewContent">
                                <!-- Content will be dynamically loaded -->
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-lg" id="previewLoan">
                            <i class="bi bi-eye me-2"></i>Preview EMI
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submitLoanBtn">
                            <i class="bi bi-check-circle me-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

