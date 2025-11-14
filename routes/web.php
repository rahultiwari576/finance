<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login/email', [LoginController::class, 'loginWithEmail'])->name('login.email')->middleware('guest');
Route::post('/login/aadhar', [LoginController::class, 'loginWithAadhar'])->name('login.aadhar')->middleware('guest');
Route::post('/login/otp', [LoginController::class, 'verifyOtp'])->name('login.otp')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', [RegistrationController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register/extract-aadhar', [RegistrationController::class, 'extractAadhar'])->name('register.extract-aadhar')->middleware('guest');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.submit')->middleware('guest');

Route::get('/emi-calculator', [LoanController::class, 'guestCalculator'])->name('emi.guest');
Route::post('/emi-calculator', [LoanController::class, 'calculate'])->name('emi.calculate');

Route::get('/home', HomeController::class)->name('home')->middleware('auth');

Route::prefix('profile')->middleware('auth')->group(function () {
    Route::get('/edit', [ProfileController::class, 'show'])->name('profile.edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('loans')->middleware('auth')->group(function () {
    Route::post('/', [LoanController::class, 'apply'])->name('loans.apply');
    Route::get('/', [LoanController::class, 'list'])->name('loans.list');
    Route::get('{loan}/installments', [LoanController::class, 'installments'])->name('loans.installments');
    Route::post('installments/{installment}/pay', [LoanController::class, 'markInstallmentPaid'])->name('loans.installments.pay');
    Route::delete('installments/{installment}', [LoanController::class, 'deleteInstallment'])->name('loans.installments.delete');
    Route::get('/reminders/list', [LoanController::class, 'reminders'])->name('loans.reminders');
    Route::delete('{loan}', [LoanController::class, 'delete'])->name('loans.delete');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{user}', [AdminController::class, 'getUser'])->name('admin.users.get');
    Route::post('/users', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/users/{user}/loans', [AdminController::class, 'getUserLoans'])->name('admin.users.loans');
    Route::get('/users/{user}/reminders', [AdminController::class, 'getUserReminders'])->name('admin.users.reminders');
    Route::get('/users/{user}/summary', [AdminController::class, 'getUserLoanSummary'])->name('admin.users.summary');
    Route::get('/loans', [AdminController::class, 'allLoans'])->name('admin.loans');
    Route::delete('/loans/{loan}', [AdminController::class, 'deleteLoan'])->name('admin.loans.delete');
    Route::post('/installments/customize', [AdminController::class, 'customizeEmiPayment'])->name('admin.installments.customize');
    Route::post('/installments/{installment}/customize', [AdminController::class, 'customizeEmiPayment'])->name('admin.installments.customize.id');
});

