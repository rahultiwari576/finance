@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row g-4">
    <!-- Header -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Admin Dashboard</h4>
            <a href="{{ route('home') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="col-12">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon mb-2">
                            <i class="bi bi-people display-6"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $users->count() }}</h3>
                        <p class="mb-0 opacity-75">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon mb-2">
                            <i class="bi bi-shield-check display-6"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $users->where('role', 'admin')->count() }}</h3>
                        <p class="mb-0 opacity-75">Admins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon mb-2">
                            <i class="bi bi-person display-6"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $users->where('role', 'user')->count() }}</h3>
                        <p class="mb-0 opacity-75">Regular Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon mb-2">
                            <i class="bi bi-bank display-6"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $loans->count() }}</h3>
                        <p class="mb-0 opacity-75">Total Loans</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-people me-2"></i>Users Management
                    </h6>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-plus-circle"></i> Create User
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-plus-circle me-2"></i>Create New User
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th class="text-center" style="width: 100px;">Role</th>
                                <th class="text-center" style="width: 80px;">Loans</th>
                                <th class="text-center" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="text-center">{{ $user->id }}</td>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center"><span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'primary' }}">{{ ucfirst($user->role) }}</span></td>
                                <td class="text-center">{{ $user->loans_count }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-info view-user" data-user-id="{{ $user->id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-warning edit-user" data-user-id="{{ $user->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button class="btn btn-danger delete-user" data-user-id="{{ $user->id }}" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Loans Management -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bi bi-bank me-2"></i>Loans Management
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>User</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                            <tr>
                                <td class="text-center">{{ $loan->id }}</td>
                                <td><strong>{{ $loan->user->name }}</strong></td>
                                <td class="text-end">₹{{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="text-center"><span class="badge bg-{{ $loan->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($loan->status) }}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger delete-loan" data-loan-id="{{ $loan->id }}">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No loans found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number" maxlength="10">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_aadhar_number" class="form-label">Aadhar Number</label>
                            <input type="text" class="form-control" id="edit_aadhar_number" name="aadhar_number" maxlength="12">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_pan_number" class="form-label">PAN Number</label>
                            <input type="text" class="form-control" id="edit_pan_number" name="pan_number" maxlength="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="edit_area" name="area">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="edit_city" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_state" class="form-label">State</label>
                            <input type="text" class="form-control" id="edit_state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="edit_zip_code" name="zip_code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_profession" class="form-label">Profession</label>
                            <input type="text" class="form-control" id="edit_profession" name="profession">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_education" class="form-label">Education</label>
                        <input type="text" class="form-control" id="edit_education" name="education">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password" name="password" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_phone_number" name="phone_number" required maxlength="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_age" class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="create_age" name="age" required min="18" max="120">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_aadhar_number" class="form-label">Aadhar Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_aadhar_number" name="aadhar_number" required maxlength="12">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_pan_number" class="form-label">PAN Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_pan_number" name="pan_number" required maxlength="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_address" class="form-label">Address</label>
                        <textarea class="form-control" id="create_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="create_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="create_area" name="area">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="create_city" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_state" class="form-label">State</label>
                            <input type="text" class="form-control" id="create_state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="create_zip_code" name="zip_code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_profession" class="form-label">Profession</label>
                            <input type="text" class="form-control" id="create_profession" name="profession">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_education" class="form-label">Education</label>
                        <input type="text" class="form-control" id="create_education" name="education">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Details Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-3" id="userDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                            Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button">
                            Loans
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reminders-tab" data-bs-toggle="tab" data-bs-target="#reminders" type="button">
                            Reminders
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button">
                            Loan Summary
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="userDetailsTabContent">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <div id="userProfileContent">
                            <p class="text-muted">Loading profile...</p>
                        </div>
                    </div>

                    <!-- Loans Tab -->
                    <div class="tab-pane fade" id="loans" role="tabpanel">
                        <div id="userLoansContent">
                            <p class="text-muted">Loading loans...</p>
                        </div>
                    </div>

                    <!-- Reminders Tab -->
                    <div class="tab-pane fade" id="reminders" role="tabpanel">
                        <div id="userRemindersContent">
                            <p class="text-muted">Loading reminders...</p>
                        </div>
                    </div>

                    <!-- Summary Tab -->
                    <div class="tab-pane fade" id="summary" role="tabpanel">
                        <div id="userSummaryContent">
                            <p class="text-muted">Loading summary...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // Create User
    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');
        
        axios.post('/admin/users', formData)
            .then(({ data }) => {
                Swal.fire('Success!', data.message, 'success').then(() => {
                    window.location.reload();
                });
            })
            .catch((error) => {
                let errorMessage = 'Failed to create user.';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response && error.response.data && error.response.data.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                Swal.fire('Error', errorMessage, 'error');
            })
            .finally(() => {
                submitBtn.prop('disabled', false).html(originalText);
            });
    });

    // View User Details
    $('.view-user').on('click', function() {
        const userId = $(this).data('user-id');
        window.currentViewUserId = userId;
        
        // Reset tabs
        $('#userDetailsTabs button').first().tab('show');
        
        // Load profile
        loadUserProfile(userId);
        loadUserLoans(userId);
        loadUserReminders(userId);
        loadUserSummary(userId);
        
        new bootstrap.Modal(document.getElementById('viewUserModal')).show();
    });

    // Load User Profile
    const loadUserProfile = (userId) => {
        axios.get(`/admin/users/${userId}`)
            .then(({ data }) => {
                const user = data.user;
                const profileHtml = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Name:</strong> ${user.name}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong> ${user.email}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Role:</strong> <span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'}">${user.role}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phone:</strong> ${user.phone_number || 'N/A'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Aadhar:</strong> ${user.aadhar_number || 'N/A'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>PAN:</strong> ${user.pan_number || 'N/A'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Age:</strong> ${user.age || 'N/A'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Total Loans:</strong> ${user.loans_count || 0}
                        </div>
                        ${user.address ? `<div class="col-12 mb-3"><strong>Address:</strong> ${user.address}</div>` : ''}
                        ${user.city || user.state ? `<div class="col-12 mb-3"><strong>Location:</strong> ${[user.city, user.state].filter(Boolean).join(', ')}</div>` : ''}
                        ${user.profession ? `<div class="col-md-6 mb-3"><strong>Profession:</strong> ${user.profession}</div>` : ''}
                        ${user.education ? `<div class="col-md-6 mb-3"><strong>Education:</strong> ${user.education}</div>` : ''}
                    </div>
                `;
                $('#userProfileContent').html(profileHtml);
            })
            .catch(() => {
                $('#userProfileContent').html('<p class="text-danger">Failed to load profile.</p>');
            });
    };

    // Load User Loans
    const loadUserLoans = (userId) => {
        axios.get(`/admin/users/${userId}/loans`)
            .then(({ data }) => {
                if (!data.loans || data.loans.length === 0) {
                    $('#userLoansContent').html('<p class="text-muted">No loans found.</p>');
                    return;
                }
                
                const loansHtml = data.loans.map(loan => {
                    const paidCount = loan.installments.filter(i => i.status === 'paid').length;
                    const unpaidCount = loan.installments.filter(i => i.status !== 'paid').length;
                    return `
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Loan #${loan.id} - ₹${Number(loan.principal_amount).toFixed(2)}</span>
                                <span class="badge bg-${loan.status === 'active' ? 'success' : 'secondary'}">${loan.status}</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>EMI:</strong> ₹${Number(loan.emi_amount).toFixed(2)}</p>
                                <p class="mb-1"><strong>Total Repayment:</strong> ₹${Number(loan.total_repayment).toFixed(2)}</p>
                                <p class="mb-1"><strong>Paid:</strong> ${paidCount} | <strong>Pending:</strong> ${unpaidCount}</p>
                                <p class="mb-0"><strong>Next Due:</strong> ${loan.next_due_date || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                $('#userLoansContent').html(loansHtml);
            })
            .catch(() => {
                $('#userLoansContent').html('<p class="text-danger">Failed to load loans.</p>');
            });
    };

    // Load User Reminders
    const loadUserReminders = (userId) => {
        axios.get(`/admin/users/${userId}/reminders`)
            .then(({ data }) => {
                if (!data.reminders || data.reminders.length === 0) {
                    $('#userRemindersContent').html('<p class="text-muted">No pending reminders.</p>');
                    return;
                }
                
                const remindersHtml = data.reminders.map(reminder => {
                    const isOverdue = reminder.status === 'overdue';
                    const rowClass = isOverdue ? 'table-danger' : 'table-warning';
                    return `
                        <tr class="${rowClass}">
                            <td>Loan #${reminder.loan_id}</td>
                            <td>${reminder.due_date}</td>
                            <td>₹${Number(reminder.amount).toFixed(2)}</td>
                            <td>₹${Number(reminder.penalty_amount).toFixed(2)}</td>
                            <td>₹${Number(reminder.amount + reminder.penalty_amount).toFixed(2)}</td>
                            <td>
                                <span class="badge bg-${isOverdue ? 'danger' : 'warning text-dark'}">${reminder.status}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary customize-emi" 
                                        data-installment-id="${reminder.installment_id || ''}"
                                        data-loan-id="${reminder.loan_id}"
                                        data-due-date="${reminder.due_date}"
                                        data-amount="${reminder.amount}"
                                        data-penalty="${reminder.penalty_amount}">
                                    Customize Payment
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                const tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Loan ID</th>
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
                        </table>
                    </div>
                `;
                $('#userRemindersContent').html(tableHtml);
            })
            .catch(() => {
                $('#userRemindersContent').html('<p class="text-danger">Failed to load reminders.</p>');
            });
    };

    // Load User Summary
    const loadUserSummary = (userId) => {
        axios.get(`/admin/users/${userId}/summary`)
            .then(({ data }) => {
                const summary = data.summary;
                const summaryHtml = `
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>₹${Number(summary.total_principal).toFixed(2)}</h5>
                                    <p class="mb-0">Total Principal</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>₹${Number(summary.total_repayment).toFixed(2)}</h5>
                                    <p class="mb-0">Total Repayment</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h5>₹${Number(summary.pending_amount).toFixed(2)}</h5>
                                    <p class="mb-0">Pending Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>${summary.pending_installments}</h5>
                                    <p class="mb-0">Pending EMIs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#userSummaryContent').html(summaryHtml);
            })
            .catch(() => {
                $('#userSummaryContent').html('<p class="text-danger">Failed to load summary.</p>');
            });
    };

    // Customize EMI Payment
    $(document).on('click', '.customize-emi', function() {
        const installmentId = $(this).data('installment-id');
        const loanId = $(this).data('loan-id');
        const dueDate = $(this).data('due-date');
        const amount = $(this).data('amount');
        const penalty = $(this).data('penalty');
        
        Swal.fire({
            title: 'Customize EMI Payment',
            html: `
                <div class="text-start">
                    <p><strong>Loan ID:</strong> ${loanId}</p>
                    <p><strong>Due Date:</strong> ${dueDate}</p>
                    <p><strong>EMI Amount:</strong> ₹${Number(amount).toFixed(2)}</p>
                    <p><strong>Current Penalty:</strong> ₹${Number(penalty).toFixed(2)}</p>
                </div>
                <div class="mt-3">
                    <label class="form-label">New Penalty Amount (₹)</label>
                    <input type="number" id="customPenaltyInput" class="form-control" value="${penalty}" min="0" step="0.01">
                </div>
                <div class="mt-3">
                    <label class="form-check-label">
                        <input type="checkbox" id="markAsPaidCheck" class="form-check-input"> Mark as Paid
                    </label>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update Payment',
            preConfirm: () => {
                return {
                    penalty: document.getElementById('customPenaltyInput').value,
                    markAsPaid: document.getElementById('markAsPaidCheck').checked
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const payload = {
                    custom_penalty_amount: result.value.penalty,
                    mark_as_paid: result.value.markAsPaid
                };

                // Add loan_id and due_date if installment_id is not available
                if (!installmentId) {
                    payload.loan_id = loanId;
                    payload.due_date = dueDate;
                }

                const url = installmentId 
                    ? `/admin/installments/${installmentId}/customize`
                    : `/admin/installments/customize`;

                axios.post(url, payload)
                .then(({ data }) => {
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        // Reload reminders
                        if (window.currentViewUserId) {
                            loadUserReminders(window.currentViewUserId);
                            loadUserSummary(window.currentViewUserId);
                            loadUserLoans(window.currentViewUserId);
                        }
                    });
                })
                .catch((error) => {
                    const message = error.response?.data?.message || 'Failed to update payment.';
                    Swal.fire('Error', message, 'error');
                });
            }
        });
    });

    // Edit User
    $('.edit-user').on('click', function() {
        const userId = $(this).data('user-id');
        
        axios.get(`/admin/users/${userId}`)
            .then(({ data }) => {
                const user = data.user;
                $('#edit_user_id').val(user.id);
                $('#edit_name').val(user.name);
                $('#edit_email').val(user.email);
                $('#edit_role').val(user.role);
                $('#edit_phone_number').val(user.phone_number || '');
                $('#edit_aadhar_number').val(user.aadhar_number || '');
                $('#edit_pan_number').val(user.pan_number || '');
                $('#edit_address').val(user.address || '');
                $('#edit_area').val(user.area || '');
                $('#edit_city').val(user.city || '');
                $('#edit_state').val(user.state || '');
                $('#edit_zip_code').val(user.zip_code || '');
                $('#edit_profession').val(user.profession || '');
                $('#edit_education').val(user.education || '');
                $('#edit_password').val('');
                
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            })
            .catch(() => {
                Swal.fire('Error', 'Failed to load user data.', 'error');
            });
    });

    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#edit_user_id').val();
        const formData = $(this).serialize();
        
        axios.put(`/admin/users/${userId}`, formData)
            .then(({ data }) => {
                Swal.fire('Success!', data.message, 'success').then(() => {
                    window.location.reload();
                });
            })
            .catch((error) => {
                let errorMessage = 'Failed to update user.';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response && error.response.data && error.response.data.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                Swal.fire('Error', errorMessage, 'error');
            });
    });

    // Delete User
    $('.delete-user').on('click', function() {
        const userId = $(this).data('user-id');
        
        Swal.fire({
            title: 'Delete User?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/users/${userId}`)
                    .then(({ data }) => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    })
                    .catch((error) => {
                        const message = error.response?.data?.message || 'Failed to delete user.';
                        Swal.fire('Error', message, 'error');
                    });
            }
        });
    });

    // Delete Loan
    $('.delete-loan').on('click', function() {
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
                axios.delete(`/admin/loans/${loanId}`)
                    .then(({ data }) => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Failed to delete loan.', 'error');
                    });
            }
        });
    });

    // Loan Summary Cards Interactive
    $('.loan-summary-card').on('click', function() {
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 1000);
    });

    // Add smooth scroll to tables
    $('.table-responsive').on('scroll', function() {
        $(this).addClass('scrolling');
    });
});
</script>
@endpush

@push('styles')
<style>
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

    .loan-summary-card .card-body {
        padding: 1.5rem;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .card-header.bg-primary,
    .card-header.bg-success {
        border-radius: 0;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .table tbody tr {
        transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        vertical-align: middle;
    }

    .btn-group-sm .btn {
        border-radius: 0.25rem;
        margin: 0 2px;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa !important;
    }

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
    }
</style>
@endpush

