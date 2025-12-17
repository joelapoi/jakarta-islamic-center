@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-user-plus me-2" style="color: #2d8659;"></i>
                Create New User
            </h2>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="create-user-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" class="form-control" required>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" class="form-control" required>
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" id="password" class="form-control" required>
                                <div class="invalid-feedback" id="password-error"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">NIP</label>
                                <input type="text" id="nip" class="form-control">
                                <div class="invalid-feedback" id="nip-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" id="phone" class="form-control">
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles <span class="text-danger">*</span></label>
                        <div class="row" id="roles-container">
                            <div class="col-12 text-center">
                                <div class="spinner-border spinner-border-sm text-success"></div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="role_ids-error"></div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let roles = [];

    document.addEventListener('DOMContentLoaded', async function() {
        await loadRoles();
    });

    // Load roles
    async function loadRoles() {
        const response = await apiCall('/roles/list', 'GET', null, false);
        
        if (response && response.data.success) {
            roles = response.data.data;
            displayRoles();
        }
    }

    // Display roles checkboxes
    function displayRoles() {
        const container = document.getElementById('roles-container');
        
        container.innerHTML = roles.map(role => `
            <div class="col-md-4 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="${role.id}" id="role_${role.id}">
                    <label class="form-check-label" for="role_${role.id}">
                        ${role.display_name}
                    </label>
                </div>
            </div>
        `).join('');
    }

    // Clear errors
    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    // Show field error
    function showFieldError(field, message) {
        const input = document.getElementById(field);
        const error = document.getElementById(`${field}-error`);
        if (input && error) {
            input.classList.add('is-invalid');
            error.textContent = message;
        }
    }

    // Handle form submit
    document.getElementById('create-user-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        // Get selected roles
        const roleIds = Array.from(document.querySelectorAll('input[name="role_ids[]"]:checked'))
            .map(cb => parseInt(cb.value));

        if (roleIds.length === 0) {
            showFieldError('role_ids', 'Please select at least one role');
            return;
        }

        const data = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            nip: document.getElementById('nip').value || null,
            phone: document.getElementById('phone').value || null,
            role_ids: roleIds,
            is_active: document.getElementById('is_active').checked
        };

        const response = await apiCall('/users', 'POST', data);

        if (response && response.data.success) {
            showFlash(response.data.message, 'success');
            setTimeout(() => {
                window.location.href = '/users';
            }, 1500);
        } else {
            if (response?.data?.errors) {
                for (const [field, messages] of Object.entries(response.data.errors)) {
                    showFieldError(field, messages[0]);
                }
            } else {
                showFlash(response?.data?.message || 'Failed to create user', 'danger');
            }
        }
    });
</script>
@endpush