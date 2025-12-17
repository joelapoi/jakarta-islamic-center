@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Rekap Pengajuan Kegiatan</h1>
        <button type="button" class="btn btn-primary shadow-sm" id="btnTambah">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Rekap Pengajuan
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row" id="statisticsCards">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengeluaran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPengeluaran">Rp 0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sisa Dana</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSisaDana">Rp 0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu</div>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Disetujui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalApproved">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Rekap Pengajuan</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search -->
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control form-control-sm" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari rekap...">
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
                            <th width="12%">Nomor Rekap</th>
                            <th width="15%">Pencairan</th>
                            <th width="18%">Kegiatan</th>
                            <th width="12%">Total Pengeluaran</th>
                            <th width="12%">Sisa Dana</th>
                            <th width="12%">Status</th>
                            <th width="14%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
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

<!-- Modal Pilih Pencairan -->
<div class="modal fade" id="pencairanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pencairan Dana untuk Rekap</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchPencairan" placeholder="Cari pencairan...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nomor</th>
                                <th>Kegiatan</th>
                                <th>Jumlah</th>
                                <th>Tanggal Cair</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pencairanList">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
                <p>Apakah Anda yakin ingin menghapus rekap pengajuan ini?</p>
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

    // Show pencairan modal
    $('#btnTambah').on('click', function() {
        loadPencairanList();
        $('#pencairanModal').modal('show');
    });

    // Search pencairan
    let searchPencairanTimeout;
    $('#searchPencairan').on('keyup', function() {
        clearTimeout(searchPencairanTimeout);
        searchPencairanTimeout = setTimeout(function() {
            loadPencairanList();
        }, 500);
    });

    // Load pencairan list (only dicairkan status)
    function loadPencairanList() {
        const search = $('#searchPencairan').val();

        $.ajax({
            url: '/api/pencairan-dana',
            method: 'GET',
            data: {
                status: 'dicairkan',
                search: search,
                per_page: 100
            },
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderPencairanList(response.data.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading pencairan:', xhr);
                $('#pencairanList').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data pencairan</td></tr>');
            }
        });
    }

    // Render pencairan list
    function renderPencairanList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Tidak ada pencairan dana yang sudah dicairkan</td></tr>';
        } else {
            data.forEach(function(item) {
                const kegiatanNama = item.anggaran_kegiatan ? item.anggaran_kegiatan.nama_kegiatan : '-';
                const tanggalCair = item.disbursed_at ? formatDate(item.disbursed_at) : '-';
                
                html += `
                    <tr>
                        <td>${item.nomor_pencairan}</td>
                        <td>${kegiatanNama}</td>
                        <td>${formatRupiah(item.jumlah_pencairan)}</td>
                        <td>${tanggalCair}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-pencairan" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#pencairanList').html(html);
    }

    // Select pencairan
    $(document).on('click', '.btn-select-pencairan', function() {
        const pencairanId = $(this).data('id');
        window.location.href = `/rekap-pengajuan/create?pencairan_id=${pencairanId}`;
    });

    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: '/api/rekap-pengajuan/statistics',
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalPengeluaran').text(formatRupiah(stats.total_pengeluaran));
                    $('#totalSisaDana').text(formatRupiah(stats.total_sisa_dana));
                    $('#totalPending').text(stats.by_status.diajukan);
                    $('#totalApproved').text(stats.by_status.disetujui);
                }
            }
        });
    }

    // Load data function
    function loadData() {
        const search = $('#searchInput').val();
        const status = $('#filterStatus').val();

        $.ajax({
            url: '/api/rekap-pengajuan',
            method: 'GET',
            data: {
                page: currentPage,
                per_page: 10,
                search: search,
                status: status
            },
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
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
                $('#tableBody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
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
                const pencairanNomor = item.pencairan_dana ? item.pencairan_dana.nomor_pencairan : '-';
                const kegiatanNama = item.pencairan_dana?.anggaran_kegiatan?.nama_kegiatan || '-';
                
                html += `
                    <tr>
                        <td>${startIndex + index + 1}</td>
                        <td>${item.nomor_rekap}</td>
                        <td>${pencairanNomor}</td>
                        <td>${kegiatanNama}</td>
                        <td>${formatRupiah(item.total_pengeluaran)}</td>
                        <td class="text-success font-weight-bold">${formatRupiah(item.sisa_dana)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/rekap-pengajuan/${item.id}" class="btn btn-info" title="Detail">
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

    // Render action buttons based on status
    function renderActionButtons(item) {
        let buttons = '';
        
        // Edit button - only for draft and ditolak
        if (item.status === 'draft' || item.status === 'ditolak') {
            buttons += `
                <a href="/rekap-pengajuan/${item.id}/edit" class="btn btn-warning" title="Edit">
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
        
        if (confirm('Apakah Anda yakin ingin mengajukan rekap pengajuan ini untuk disetujui?')) {
            $.ajax({
                url: `/api/rekap-pengajuan/${id}/submit`,
                method: 'POST',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadData();
                        loadStatistics();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal mengajukan rekap pengajuan';
                    showAlert('error', message);
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
                url: `/api/rekap-pengajuan/${deleteId}`,
                method: 'DELETE',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        showAlert('success', response.message);
                        loadData();
                        loadStatistics();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal menghapus rekap pengajuan';
                    showAlert('error', message);
                }
            });
        }
    });

    // Helper: Get status badge
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui': '<span class="badge badge-success">Disetujui</span>',
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