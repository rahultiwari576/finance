@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Sign in to continue to LaraFinance</p>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-pills auth-tabs" id="loginTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-login" type="button" role="tab">
                        <i class="bi bi-envelope me-2"></i>Email & Password
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="aadhar-tab" data-bs-toggle="tab" data-bs-target="#aadhar-login" type="button" role="tab">
                        <i class="bi bi-card-text me-2"></i>Aadhar & OTP
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content auth-tab-content" id="loginTabsContent">
                <!-- Email Login -->
                <div class="tab-pane fade show active" id="email-login" role="tabpanel">
                    <form id="emailLoginForm" action="{{ route('login.email') }}" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                            <div class="input-wrapper">
                                <input type="email" name="email" id="email" class="form-control auth-input" placeholder="Enter your email" required>
                                <i class="bi bi-check-circle-fill input-icon success-icon d-none"></i>
                                <i class="bi bi-x-circle-fill input-icon error-icon d-none"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <div class="input-wrapper">
                                <input type="password" name="password" id="password" class="form-control auth-input" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary auth-submit-btn w-100" id="emailLoginBtn">
                            <span class="btn-text">Login</span>
                            <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2"></span>Logging in...</span>
                        </button>
                    </form>
                </div>

                <!-- Aadhar Login -->
                <div class="tab-pane fade" id="aadhar-login" role="tabpanel">
                    <form id="aadharLoginForm" action="{{ route('login.aadhar') }}" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label for="aadhar_number" class="form-label">
                                <i class="bi bi-card-text me-2"></i>Aadhar Number
                            </label>
                            <div class="input-wrapper">
                                <input type="text" name="aadhar_number" id="aadhar_number" class="form-control auth-input" maxlength="12" placeholder="Enter 12-digit Aadhar number" required>
                                <span class="input-hint">12 digits</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark auth-submit-btn w-100" id="aadharLoginBtn">
                            <span class="btn-text"><i class="bi bi-send me-2"></i>Send OTP</span>
                            <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2"></span>Sending...</span>
                        </button>
                    </form>

                    <!-- OTP Verification -->
                    <form id="otpVerificationForm" action="{{ route('login.otp') }}" class="auth-form mt-4 d-none" id="otpForm">
                        @csrf
                        <input type="hidden" name="otp_id" id="otp_id">
                        <div class="form-group">
                            <label class="form-label text-center w-100 mb-3">
                                <i class="bi bi-shield-check me-2"></i>Enter OTP
                            </label>
                            <div class="otp-container">
                                <input type="text" name="code" id="otp_code" class="otp-input" maxlength="1" required>
                                <input type="text" name="code2" class="otp-input" maxlength="1" required>
                                <input type="text" name="code3" class="otp-input" maxlength="1" required>
                                <input type="text" name="code4" class="otp-input" maxlength="1" required>
                                <input type="text" name="code5" class="otp-input" maxlength="1" required>
                                <input type="text" name="code6" class="otp-input" maxlength="1" required>
                            </div>
                            <input type="hidden" name="code" id="otp_full_code">
                            <div class="otp-timer mt-3 text-center">
                                <small class="text-muted">OTP expires in <span id="otpTimer">5:00</span></small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success auth-submit-btn w-100" id="otpVerifyBtn">
                            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Verify OTP</span>
                            <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2"></span>Verifying...</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="text-center mb-0">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="auth-link">Create Account</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .auth-container::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: moveBackground 20s linear infinite;
    }

    @keyframes moveBackground {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }

    .auth-wrapper {
        width: 100%;
        max-width: 450px;
        position: relative;
        z-index: 1;
    }

    .auth-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        padding: 2.5rem;
        animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .auth-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2.5rem;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .auth-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .auth-subtitle {
        color: #718096;
        font-size: 0.9rem;
    }

    .auth-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 2rem;
        justify-content: center;
    }

    .auth-tabs .nav-link {
        border: none;
        border-radius: 0;
        color: #718096;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        background: transparent;
    }

    .auth-tabs .nav-link:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
    }

    .auth-tabs .nav-link.active {
        color: #667eea;
        background: transparent;
        border-bottom: 3px solid #667eea;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-label i {
        color: #667eea;
    }

    .input-wrapper {
        position: relative;
    }

    .auth-input {
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .auth-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        outline: none;
    }

    .input-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
    }

    .success-icon {
        color: #10b981;
    }

    .error-icon {
        color: #ef4444;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #718096;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0;
    }

    .password-toggle:hover {
        color: #667eea;
    }

    .input-hint {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: #a0aec0;
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .form-check-label {
        color: #4a5568;
        font-size: 0.9rem;
    }

    .forgot-link {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .auth-submit-btn {
        padding: 0.875rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: none;
    }

    .auth-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .auth-submit-btn:active {
        transform: translateY(0);
    }

    .otp-container {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .otp-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .otp-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        outline: none;
    }

    .otp-timer {
        color: #ef4444;
        font-weight: 600;
    }

    .auth-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .auth-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .auth-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 576px) {
        .auth-card {
            padding: 1.5rem;
        }

        .auth-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }

        .auth-title {
            font-size: 1.5rem;
        }

        .otp-input {
            width: 40px;
            height: 50px;
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ mix('js/auth.js') }}"></script>
@endpush

