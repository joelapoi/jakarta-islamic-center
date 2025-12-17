@extends('layouts.app')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-users me-2" style="color: #2d8659;"></i>
                Users Management
            </h2>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="Search by name, email, NIP...">
                    </div>
                    <div class="col-md-3">
                        <select id="role-filter" class="form-select">
                            <option value="">All Roles</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="status-filter" class="form-select">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="loadUsers()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>NIP</th>
                                <th>Phone</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border text-success" role="status">
                                        <!-- <span class="visually-hidden">Loading...</span> -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav id="pagination-container" class="mt-3"></nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let roles = [];

    document.addEventListener('DOMContentLoaded', async function() {
        await loadRoles();
        await loadUsers();
    });

    // Load roles for filter
    async function loadRoles() {
        const response = await apiCall('/roles/list', 'GET', null, false);
        if (response && response.data.success) {
            roles = response.data.data;
            
            const roleFilter = document.getElementById('role-filter');
            roles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.display_name;
                roleFilter.appendChild(option);
            });
        }
    }

    // Load users
    async function loadUsers(page = 1) {
        const search = document.getElementById('search').value;
        const roleId = document.getElementById('role-filter').value;
        const isActive = document.getElementById('status-filter').value;

        let endpoint = `/users?page=${page}&per_page=10`;
        if (search) endpoint += `&search=${encodeURIComponent(search)}`;
        if (roleId) endpoint += `&role_id=${roleId}`;
        if (isActive) endpoint += `&is_active=${isActive}`;

        const response = await apiCall(endpoint, 'GET', null, false);
        
        if (response && response.data.success) {
            const users = response.data.data;
            displayUsers(users.data);
            displayPagination(users);
            currentPage = page;
        }
    }

    // Display users in table
    function displayUsers(users) {
        const tbody = document.getElementById('users-table-body');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-2" style="color: #2d8659;"></i>
                        <strong>${user.name}</strong>
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${user.nip || '-'}</td>
                <td>${user.phone || '-'}</td>
                <td>
                    ${user.roles.map(role => 
                        `<span class="badge" style="background-color: #2d8659;">${role.display_name}</span>`
                    ).join(' ')}
                </td>
                <td>
                    ${user.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>'}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="/users/${user.id}/edit" class="btn btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-secondary" onclick="toggleStatus(${user.id}, ${user.is_active})" title="Toggle Status">
                            <i class="fas fa-power-off"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.id}, '${user.name}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Display pagination
    function displayPagination(data) {
        const container = document.getElementById('pagination-container');
        
        if (data.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination mb-0">';
        
        // Previous
        html += `
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadUsers(${data.current_page - 1}); return false;">Previous</a>
            </li>
        `;
        
        // Pages
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a>
                    </li>
                `;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next
        html += `
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadUsers(${data.current_page + 1}); return false;">Next</a>
            </li>
        `;
        
        html += '</ul>';
        container.innerHTML = html;
    }

    // Toggle user status
    async function toggleStatus(userId, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        if (!confirm(`Are you sure you want to ${action} this user?`)) return;

        const response = await apiCall(`/users/${userId}/toggle-status`, 'POST');
        
        if (response && response.data.success) {
            showFlash(response.data.message, 'success');
            await loadUsers(currentPage);
        } else {
            showFlash(response?.data?.message || 'Failed to toggle status', 'danger');
        }
    }

    // Delete user
    async function deleteUser(userId, userName) {
        if (!confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) return;

        const response = await apiCall(`/users/${userId}`, 'DELETE');
        
        if (response && response.data.success) {
            showFlash(response.data.message, 'success');
            await loadUsers(currentPage);
        } else {
            showFlash(response?.data?.message || 'Failed to delete user', 'danger');
        }
    }

    // Search on enter
    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadUsers(1);
        }
    });
</script>
@endpush