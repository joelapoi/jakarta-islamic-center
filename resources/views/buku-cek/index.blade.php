@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buku Cek</h1>
        @if(auth()->check() && auth()->user()->role === 'staff')
        <button type="button" class="btn btn-primary shadow-sm" id="btnTambah">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Buku Cek
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Buku Cek</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBukuCek">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu TTD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPending">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-signature fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dikonfirmasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalConfirmed">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Buku Cek</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search -->
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control form-control-sm" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="menunggu_ttd_kepala_jic">Menunggu TTD Kepala JIC</option>
                                <option value="ditandatangani">Ditandatangani</option>
                                <option value="dikonfirmasi_bank">Dikonfirmasi Bank</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari buku cek...">
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
                            <th width="12%">Nomor Buku Cek</th>
                            <th width="12%">Nomor Rekap</th>
                            <th width="20%">Kegiatan</th>
                            <th width="12%">Jumlah</th>
                            <th width="12%">Tanggal</th>
                            <th width="12%">Status</th>
                            <th width="15%">Aksi</th>
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

<!-- Modal Pilih Rekap -->
<div class="modal fade" id="rekapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Rekap Pengajuan untuk Buku Cek</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchRekap" placeholder="Cari rekap...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nomor Rekap</th>
                                <th>Kegiatan</th>
                                <th>Sisa Dana</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapList">
                            <tr>
                                <td colspan="4" class="text-center">
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
                <p>Apakah Anda yakin ingin menghapus buku cek ini?</p>
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
    console.log('User Role:', userRole);
    const authUser = @json(auth()->user());

console.log("User Auth Object:", authUser);
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

    // Show rekap modal - only for staff
    $('#btnTambah').on('click', function() {
        if (userRole === 'staff') {
            loadRekapList();
            $('#rekapModal').modal('show');
        }
    });

    // Search rekap
    let searchRekapTimeout;
    $('#searchRekap').on('keyup', function() {
        clearTimeout(searchRekapTimeout);
        searchRekapTimeout = setTimeout(function() {
            loadRekapList();
        }, 500);
    });

    // Load rekap list (only disetujui status without buku cek)
    function loadRekapList() {
        const search = $('#searchRekap').val();

        $.ajax({
            url: '/api/rekap-pengajuan',
            method: 'GET',
            data: {
                status: 'disetujui',
                search: search,
                per_page: 100
            },
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    // Filter only rekap without buku cek
                    const filteredData = response.data.data.filter(item => !item.buku_cek);
                    renderRekapList(filteredData);
                }
            },
            error: function(xhr) {
                console.error('Error loading rekap:', xhr);
                $('#rekapList').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data rekap</td></tr>');
            }
        });
    }

    // Render rekap list
    function renderRekapList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="4" class="text-center">Tidak ada rekap pengajuan yang disetujui</td></tr>';
        } else {
            data.forEach(function(item) {
                const kegiatanNama = item.pencairan_dana?.anggaran_kegiatan?.nama_kegiatan || '-';
                
                html += `
                    <tr>
                        <td>${item.nomor_rekap}</td>
                        <td>${kegiatanNama}</td>
                        <td class="text-success font-weight-bold">${formatRupiah(item.sisa_dana)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-rekap" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#rekapList').html(html);
    }

    // Select rekap - only for staff
    $(document).on('click', '.btn-select-rekap', function() {
        if (userRole === 'staff') {
            const rekapId = $(this).data('id');
            window.location.href = `/buku-cek/create?rekap_id=${rekapId}`;
        }
    });

    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: '/api/buku-cek/statistics',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalBukuCek').text(stats.total);
                    $('#totalPending').text(stats.by_status.menunggu_ttd_kepala_jic || 0);
                    $('#totalConfirmed').text(stats.by_status.dikonfirmasi_bank || 0);
                    $('#totalRejected').text(stats.by_status.ditolak || 0);
                }
            }
        });
    }

    // Load data function
    function loadData() {
        const search = $('#searchInput').val();
        const status = $('#filterStatus').val();

        $.ajax({
            url: '/api/buku-cek',
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
                const rekapNomor = item.rekap_pengajuan?.nomor_rekap || '-';
                const kegiatanNama = item.rekap_pengajuan?.pencairan_dana?.anggaran_kegiatan?.nama_kegiatan || '-';
                const jumlah = item.rekap_pengajuan?.sisa_dana || 0;
                
                html += `
                    <tr>
                        <td>${startIndex + index + 1}</td>
                        <td>${item.nomor_buku_cek}</td>
                        <td>${rekapNomor}</td>
                        <td>${kegiatanNama}</td>
                        <td>${formatRupiah(jumlah)}</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/buku-cek/${item.id}" class="btn btn-info" title="Detail">
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
        
        // Only staff can edit and delete
        if (userRole === 'staff') {
            // Edit button - only for draft
            if (item.status === 'draft') {
                buttons += `
                    <a href="/buku-cek/${item.id}/edit" class="btn btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
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
                url: `/api/buku-cek/${deleteId}`,
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
                    const message = xhr.responseJSON?.message || 'Gagal menghapus buku cek';
                    showAlert('error', message);
                }
            });
        }
    });

    // Helper: Get status badge
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'menunggu_ttd_kepala_jic': '<span class="badge badge-warning">Menunggu TTD</span>',
            'ditandatangani': '<span class="badge badge-primary">Ditandatangani</span>',
            'dikonfirmasi_bank': '<span class="badge badge-success">Dikonfirmasi Bank</span>',
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