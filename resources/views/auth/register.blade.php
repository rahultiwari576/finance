@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper register-wrapper">
        <div class="auth-card register-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join LaraFinance and start your financial journey</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps mb-4">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Personal</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Documents</div>
                </div>
            </div>

            <form id="registrationForm" action="{{ route('register.submit') }}" data-extract-url="{{ route('register.extract-aadhar') }}" enctype="multipart/form-data" class="auth-form">
                @csrf
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" id="step1">
                    <h5 class="step-title mb-4">
                        <i class="bi bi-person me-2"></i>Personal Information
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-person me-2"></i>Full Name
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" name="name" id="regName" class="form-control auth-input" placeholder="Enter your full name" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email Address
                                </label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" id="regEmail" class="form-control auth-input" placeholder="Enter your email" required>
                                    <i class="bi bi-check-circle-fill input-icon success-icon d-none"></i>
                                    <i class="bi bi-x-circle-fill input-icon error-icon d-none"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <div class="input-wrapper">
                                    <input type="password" name="password" id="regPassword" class="form-control auth-input" placeholder="Create a password" required>
                                    <button type="button" class="password-toggle" id="toggleRegPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-2">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <small class="strength-text" id="strengthText">Password strength</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>Confirm Password
                                </label>
                                <div class="input-wrapper">
                                    <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control auth-input" placeholder="Confirm your password" required>
                                    <i class="bi bi-check-circle-fill input-icon match-icon d-none"></i>
                                    <i class="bi bi-x-circle-fill input-icon mismatch-icon d-none"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-calendar me-2"></i>Age
                                </label>
                                <div class="input-wrapper">
                                    <input type="number" name="age" id="regAge" class="form-control auth-input" min="18" max="120" placeholder="Age" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-phone me-2"></i>Phone Number
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" name="phone_number" id="regPhone" class="form-control auth-input" maxlength="10" placeholder="Enter 10-digit phone number" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="button" class="btn btn-outline-primary" id="nextStep">
                            Next <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Documents -->
                <div class="form-step d-none" id="step2">
                    <h5 class="step-title mb-4">
                        <i class="bi bi-file-earmark-text me-2"></i>Document Information
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-card-text me-2"></i>Aadhar Number
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" name="aadhar_number" id="aadhar_number_field" class="form-control auth-input" maxlength="12" placeholder="Enter 12-digit Aadhar" required>
                                    <span class="input-hint">12 digits</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-file-text me-2"></i>PAN Number
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" name="pan_number" id="regPAN" class="form-control auth-input" maxlength="10" placeholder="Enter PAN number" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-upload me-2"></i>Upload Aadhar Document
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="aadhar_document" id="aadhar_document" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label for="aadhar_document" class="file-label">
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        <span class="file-text">Choose Aadhar File</span>
                                    </label>
                                    <div class="file-name d-none" id="aadharFileName"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-upload me-2"></i>Upload PAN Document
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="pan_document" id="pan_document" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label for="pan_document" class="file-label">
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        <span class="file-text">Choose PAN File</span>
                                    </label>
                                    <div class="file-name d-none" id="panFileName"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="prevStep">
                            <i class="bi bi-arrow-left me-2"></i>Previous
                        </button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-outline-info me-2" id="extractAadharButton">
                                <i class="bi bi-magic me-2"></i>Extract Aadhar
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitRegBtn">
                                <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Register</span>
                                <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2"></span>Registering...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="text-center mb-0">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="auth-link">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .register-wrapper {
        max-width: 700px;
    }

    .register-card {
        padding: 2.5rem;
    }

    .progress-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #718096;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .step-label {
        margin-top: 0.5rem;
        font-size: 0.75rem;
        color: #718096;
        font-weight: 500;
    }

    .step.active .step-label {
        color: #667eea;
        font-weight: 600;
    }

    .step-line {
        width: 100px;
        height: 2px;
        background: #e2e8f0;
        margin: 0 1rem;
        margin-top: -20px;
    }

    .form-step {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .step-title {
        color: #2d3748;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.75rem;
    }

    .password-strength {
        margin-top: 0.5rem;
    }

    .strength-bar {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        background: #ef4444;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .strength-fill.weak {
        width: 33%;
        background: #ef4444;
    }

    .strength-fill.medium {
        width: 66%;
        background: #f59e0b;
    }

    .strength-fill.strong {
        width: 100%;
        background: #10b981;
    }

    .strength-text {
        color: #718096;
        font-size: 0.75rem;
    }

    .match-icon {
        color: #10b981;
    }

    .mismatch-icon {
        color: #ef4444;
    }

    .file-upload-wrapper {
        position: relative;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .file-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        border: 2px dashed #cbd5e0;
        border-radius: 10px;
        background: #f7fafc;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #4a5568;
    }

    .file-label:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
        color: #667eea;
    }

    .file-name {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: #e6fffa;
        border-radius: 5px;
        font-size: 0.875rem;
        color: #10b981;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .register-card {
            padding: 1.5rem;
        }

        .step-line {
            width: 50px;
        }

        .form-actions {
            flex-direction: column;
            gap: 1rem;
        }

        .form-actions .ms-auto {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ mix('js/registration.js') }}"></script>
@endpush
