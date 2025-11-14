@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Profile</h5>
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary">Back to Home</a>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $user->phone_number }}" maxlength="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" value="{{ $user->age }}" min="18" max="120">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2">{{ $user->address }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="area" name="area" value="{{ $user->area }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="{{ $user->state }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" value="{{ $user->zip_code }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profession" class="form-label">Profession</label>
                            <input type="text" class="form-control" id="profession" name="profession" value="{{ $user->profession }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="education" class="form-label">Education</label>
                        <input type="text" class="form-control" id="education" name="education" value="{{ $user->education }}" placeholder="e.g., B.Tech, MBA, etc.">
                    </div>

                    <div class="mb-3">
                        <label for="additional_info" class="form-label">Additional Information</label>
                        <textarea class="form-control" id="additional_info" name="additional_info" rows="3">{{ $user->additional_info }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6">
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
        
        axios.put('{{ route("profile.update") }}', formData)
            .then(({ data }) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch((error) => {
                let errorMessage = 'Failed to update profile.';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response && error.response.data && error.response.data.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                });
            })
            .finally(() => {
                submitBtn.prop('disabled', false).html(originalText);
            });
    });
});
</script>
@endpush

