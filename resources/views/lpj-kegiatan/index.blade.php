@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">LPJ Kegiatan</h1>
        @if(auth()->check() && auth()->user()->role === 'staff')
        <button type="button" class="btn btn-primary shadow-sm" id="btnTambah">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat LPJ Kegiatan
        </button>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="row" id="statisticsCards">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total LPJ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalLPJ">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Diarsipkan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalArchived">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-archive fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Daftar LPJ Kegiatan</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search -->
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control form-control-sm" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="disetujui">Disetujui Bendahara</option>
                                <option value="diarsipkan">Diarsipkan</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari LPJ...">
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
                            <th width="12%">Nomor LPJ</th>
                            <th width="20%">Kegiatan</th>
                            <th width="12%">Tanggal Selesai</th>
                            <th width="15%">Total Realisasi</th>
                            <th width="12%">Tanggal LPJ</th>
                            <th width="12%">Status</th>
                            <th width="12%">Aksi</th>
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

<!-- Modal Pilih Kegiatan -->
<div class="modal fade" id="kegiatanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Kegiatan untuk LPJ</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchKegiatan" placeholder="Cari kegiatan...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Kegiatan</th>
                                <th>Anggaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kegiatanList">
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
                <p>Apakah Anda yakin ingin menghapus LPJ kegiatan ini?</p>
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
    const userRole = '{{ auth()->check() ? auth()->user()->role : "" }}'; // Get user role from backend

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

    // Show kegiatan modal - only for staff
    $('#btnTambah').on('click', function() {
        if (userRole === 'staff') {
            loadKegiatanList();
            $('#kegiatanModal').modal('show');
        }
    });

    // Search kegiatan
    let searchKegiatanTimeout;
    $('#searchKegiatan').on('keyup', function() {
        clearTimeout(searchKegiatanTimeout);
        searchKegiatanTimeout = setTimeout(function() {
            loadKegiatanList();
        }, 500);
    });

    // Load kegiatan list (only approved without LPJ)
    function loadKegiatanList() {
        const search = $('#searchKegiatan').val();

        $.ajax({
            url: '/api/anggaran-kegiatan',
            method: 'GET',
            data: {
                status: 'disetujui_kepala_jic',
                search: search,
                per_page: 100
            },
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderKegiatanList(response.data.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading kegiatan:', xhr);
                $('#kegiatanList').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data kegiatan</td></tr>');
            }
        });
    }

    // Render kegiatan list
    function renderKegiatanList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Tidak ada kegiatan yang disetujui</td></tr>';
        } else {
            data.forEach(function(item) {
                html += `
                    <tr>
                        <td>${item.kode_kegiatan}</td>
                        <td>${item.nama_kegiatan}</td>
                        <td>${formatRupiah(item.anggaran_disetujui)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-kegiatan" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#kegiatanList').html(html);
    }

    // Select kegiatan - only for staff
    $(document).on('click', '.btn-select-kegiatan', function() {
        if (userRole === 'staff') {
            const kegiatanId = $(this).data('id');
            window.location.href = `/lpj-kegiatan/create?anggaran_id=${kegiatanId}`;
        }
    });

    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: '/api/lpj-kegiatan/statistics',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalLPJ').text(stats.total);
                    $('#totalPending').text(stats.by_status.diajukan);
                    $('#totalApproved').text(stats.by_status.disetujui);
                    $('#totalArchived').text(0); // Belum ada status diarsipkan di controller
                }
            }
        });
    }

    // Load data function
    function loadData() {
        const search = $('#searchInput').val();
        const status = $('#filterStatus').val();

        $.ajax({
            url: '/api/lpj-kegiatan',
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
                const kegiatanNama = item.anggaran_kegiatan ? item.anggaran_kegiatan.nama_kegiatan : '-';
                const tanggalSelesai = item.anggaran_kegiatan ? formatDate(item.anggaran_kegiatan.tanggal_selesai) : '-';
                
                html += `
                    <tr>
                        <td>${startIndex + index + 1}</td>
                        <td>${item.nomor_lpj}</td>
                        <td>${kegiatanNama}</td>
                        <td>${tanggalSelesai}</td>
                        <td>${formatRupiah(item.total_realisasi)}</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/lpj-kegiatan/${item.id}" class="btn btn-info" title="Detail">
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
        if (userRole === 'staff') {
            // Edit button - only for draft and ditolak
            if (item.status === 'draft' || item.status === 'ditolak') {
                buttons += `
                    <a href="/lpj-kegiatan/${item.id}/edit" class="btn btn-warning" title="Edit">
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

    // Submit handler - only for staff
    $(document).on('click', '.btn-submit', function() {
        if (userRole !== 'staff') return;
        
        const id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin mengajukan LPJ kegiatan ini untuk disetujui?')) {
            $.ajax({
                url: `/api/lpj-kegiatan/${id}/submit`,
                method: 'POST',
                headers: {
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
                    const message = xhr.responseJSON?.message || 'Gagal mengajukan LPJ kegiatan';
                    showAlert('error', message);
                }
            });
        }
    });

    // Delete handler - only for staff
    $(document).on('click', '.btn-delete', function() {
        if (userRole === 'staff') {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        }
    });

    $('#confirmDelete').on('click', function() {
        if (deleteId && userRole === 'staff') {
            $.ajax({
                url: `/api/lpj-kegiatan/${deleteId}`,
                method: 'DELETE',
                headers: {
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
                    const message = xhr.responseJSON?.message || 'Gagal menghapus LPJ kegiatan';
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
            'disetujui_kepala_jic': '<span class="badge badge-success">Disetujui Kepala JIC</span>',
            'diarsipkan': '<span class="badge badge-primary">Diarsipkan</span>',
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