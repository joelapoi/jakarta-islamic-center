@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Anggaran Kegiatan</h1>
        <div id="addButtonContainer"></div>
    </div>

    <!-- Statistics Cards -->
    <div class="row" id="statisticsCards">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Anggaran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalAnggaran">Rp 0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disetujui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalApproved">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPending">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ditolak</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalRejected">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Anggaran Kegiatan</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search -->
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control form-control-sm" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="disetujui_kadiv">Disetujui Kadiv</option>
                                <option value="disetujui_kadiv_umum">Disetujui Kadiv Umum</option>
                                <option value="disetujui_kepala_jic">Disetujui Kepala JIC</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari kegiatan...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Kode Kegiatan</th>
                            <th width="20%">Nama Kegiatan</th>
                            <th width="15%">Anggaran</th>
                            <th width="12%">Tanggal Mulai</th>
                            <th width="12%">Tanggal Selesai</th>
                            <th width="12%">Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="paginationInfo"></div>
                </div>
                <div class="col-md-6">
                    <nav>
                        <ul class="pagination justify-content-end" id="paginationLinks"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus anggaran kegiatan ini?</p>
                <p class="text-danger"><strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let deleteId = null;
    
    // Get user roles from localStorage
    const getUserRoles = () => {
        try {
            const userStr = localStorage.getItem('user');
            if (userStr) {
                const user = JSON.parse(userStr);
                return user.roles || [];
            }
        } catch (e) {
            console.error('Error parsing user from localStorage:', e);
        }
        return [];
    };

    const userRoles = getUserRoles();
    const hasAddPermission = userRoles.some(role => ['staff', 'admin', 'super_admin'].includes(role));

    // Render add button based on permissions
    if (hasAddPermission) {
        $('#addButtonContainer').html(`
            <a href="/anggaran-kegiatan/create" id="addButtonLink" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Anggaran Kegiatan
            </a>
        `);
    }

    // Load statistics
    loadStatistics();

    // Load data on page load
    loadData();

    // Search functionality
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadData();
        }, 500);
    });

    // Filter by status
    $('#filterStatus').on('change', function() {
        currentPage = 1;
        loadData();
    });

    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: '/api/anggaran-kegiatan/statistics',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalAnggaran').text(formatRupiah(stats.total_anggaran));
                    $('#totalApproved').text(stats.by_status.disetujui_kepala_jic);
                    $('#totalPending').text(
                        stats.by_status.diajukan + 
                        stats.by_status.disetujui_kadiv + 
                        stats.by_status.disetujui_kadiv_umum
                    );
                    $('#totalRejected').text(stats.by_status.ditolak);
                }
            }
        });
    }

    // Load data function
    function loadData() {
        const search = $('#searchInput').val();
        const status = $('#filterStatus').val();

        $.ajax({
            url: '/api/anggaran-kegiatan',
            method: 'GET',
            data: {
                page: currentPage,
                per_page: 10,
                search: search,
                status: status
            },
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderTable(response.data);
                    renderPagination(response.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading data:', xhr);
                showAlert('error', 'Gagal memuat data');
                $('#tableBody').html('<tr><td colspan="8" class="text-center text-danger">Tidak ada data</td></tr>');
            }
        });
    }

    // Render table
    function renderTable(data) {
        let html = '';
        if (data.data.length === 0) {
            html = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
        } else {
            data.data.forEach(function(item, index) {
                const startIndex = (data.current_page - 1) * data.per_page;
                html += `
                    <tr>
                        <td>${startIndex + index + 1}</td>
                        <td>${item.kode_kegiatan}</td>
                        <td>${item.nama_kegiatan}</td>
                        <td>${formatRupiah(item.anggaran_disetujui)}</td>
                        <td>${formatDate(item.tanggal_mulai)}</td>
                        <td>${formatDate(item.tanggal_selesai)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/anggaran-kegiatan/${item.id}" class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                ${renderActionButtons(item)}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        $('#tableBody').html(html);
    }

    // Render action buttons based on status and user role
    function renderActionButtons(item) {
        let buttons = '';
        
        // Only staff can edit, submit, and delete
        const isStaff = userRoles.includes('staff');
        
        if (isStaff) {
            // Edit button - only for draft and ditolak
            if (item.status === 'draft' || item.status === 'ditolak') {
                buttons += `
                    <a href="/anggaran-kegiatan/${item.id}/edit" class="btn btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                `;
            }

            // Submit button - only for draft and ditolak
            if (item.status === 'draft' || item.status === 'ditolak') {
                buttons += `
                    <button class="btn btn-primary btn-submit" data-id="${item.id}" title="Submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                `;
            }

            // Delete button - only for draft
            if (item.status === 'draft') {
                buttons += `
                    <button class="btn btn-danger btn-delete" data-id="${item.id}" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }
        }

        return buttons;
    }

    // Render pagination
    function renderPagination(data) {
        // Pagination info
        const start = (data.current_page - 1) * data.per_page + 1;
        const end = Math.min(start + data.per_page - 1, data.total);
        $('#paginationInfo').html(`Menampilkan ${start} - ${end} dari ${data.total} data`);

        // Pagination links
        let links = '';
        
        // Previous button
        if (data.current_page > 1) {
            links += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`;
        } else {
            links += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
        }

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                links += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                links += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
        }

        // Next button
        if (data.current_page < data.last_page) {
            links += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`;
        } else {
            links += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
        }

        $('#paginationLinks').html(links);
    }

    // Pagination click handler
    $(document).on('click', '#paginationLinks a', function(e) {
        e.preventDefault();
        currentPage = $(this).data('page');
        loadData();
    });

    // Submit handler
    $(document).on('click', '.btn-submit', function() {
        const id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin mengajukan anggaran kegiatan ini untuk disetujui?')) {
            $.ajax({
                url: `/api/anggaran-kegiatan/${id}/submit`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Anggaran berhasil diajukan');
                        loadData();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal mengajukan anggaran kegiatan');
                }
            });
        }
    });

    // Delete handler
    $(document).on('click', '.btn-delete', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (deleteId) {
            $.ajax({
                url: `/api/anggaran-kegiatan/${deleteId}`,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        showAlert('success', 'Anggaran berhasil dihapus');
                        loadData();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menghapus anggaran kegiatan');
                }
            });
        }
    });

    // Helper: Get status badge
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui_kadiv': '<span class="badge badge-primary">Disetujui Kadiv</span>',
            'disetujui_kadiv_umum': '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
            'disetujui_kepala_jic': '<span class="badge badge-success">Disetujui Kepala JIC</span>',
            'ditolak': '<span class="badge badge-danger">Ditolak</span>'
        };
        return badges[status] || status;
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Helper: Format Date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
    }

    // Helper: Show alert
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        $('.container-fluid').prepend(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endpush