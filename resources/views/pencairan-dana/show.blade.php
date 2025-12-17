@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pencairan Dana</h1>
        <a href="{{ route('pencairan-dana.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pencairan Dana</h6>
                    <div id="statusBadge"></div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Pencairan</td>
                            <td width="5%">:</td>
                            <td id="nomorPencairan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div id="kegiatanNama">-</div>
                                <small class="text-muted" id="kegiatanKode">-</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pencairan</td>
                            <td>:</td>
                            <td id="jumlahPencairan" class="text-success font-weight-bold h5">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Keperluan</td>
                            <td>:</td>
                            <td id="keperluan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Pengajuan</td>
                            <td>:</td>
                            <td id="tanggalPengajuan">-</td>
                        </tr>
                        <tr id="tanggalCairRow" style="display: none;">
                            <td class="font-weight-bold">Tanggal Dicairkan</td>
                            <td>:</td>
                            <td id="tanggalCair">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Oleh</td>
                            <td>:</td>
                            <td id="creator">-</td>
                        </tr>
                        <tr id="approverRow" style="display: none;">
                            <td class="font-weight-bold">Disetujui Oleh</td>
                            <td>:</td>
                            <td id="approver">-</td>
                        </tr>
                        <tr id="approvedAtRow" style="display: none;">
                            <td class="font-weight-bold">Tanggal Disetujui</td>
                            <td>:</td>
                            <td id="approvedAt">-</td>
                        </tr>
                        <tr id="catatanRow" style="display: none;">
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td id="catatan">-</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Anggaran Info Card -->
            <div class="card shadow mb-4" id="anggaranCard">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Total Anggaran</small>
                                <h5 class="font-weight-bold text-primary" id="totalAnggaran">Rp 0</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Total Pencairan</small>
                                <h5 class="font-weight-bold text-warning" id="totalPencairan">Rp 0</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Sisa Anggaran</small>
                                <h5 class="font-weight-bold text-success" id="sisaAnggaran">Rp 0</h5>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <a href="#" id="linkKegiatan" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Detail Kegiatan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Documents Card -->
            <div class="card shadow mb-4" id="documentsCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                </div>
                <div class="card-body">
                    <div id="documentsList"></div>
                </div>
            </div>

            <!-- Rekap Pengajuan Card -->
            <div class="card shadow mb-4" id="rekapCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    <div id="rekapInfo"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4" id="actionCard">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2" id="actionButtons">
                        <!-- Buttons will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- Approval Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Progress Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    <div id="approvalProgress">
                        <!-- Progress will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setujui Pencairan Dana</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <div class="form-group">
                        <label for="approvalCatatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="approvalCatatan" rows="3" 
                                  placeholder="Masukkan catatan jika diperlukan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmApprove">
                    <i class="fas fa-check"></i> Setujui
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Tolak Pencairan Dana</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <div class="form-group">
                        <label for="rejectCatatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectCatatan" rows="4" 
                                  placeholder="Masukkan alasan penolakan" required></textarea>
                        <small class="form-text text-muted">Catatan wajib diisi saat menolak pengajuan</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReject">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const pencairanId = {{ $id }};
    let pencairanData = null;
    let userRole = null;

    // Load user role first, then load data
    getUserRole();

    // Get user role (normalize and ensure available before loading data)
    function getUserRole() {
        $.ajax({
            url: '/api/auth/me',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const user = response.data || response.user;
                    const roles = user && user.roles ? user.roles : [];
                    if (roles.length > 0) {
                        const first = roles[0];
                        let roleName = (typeof first === 'string') ? first : (first.name || '');
                        roleName = roleName.toLowerCase().trim().replace(/\s+/g, '_');
                        userRole = roleName;
                        console.log('User role (normalized):', userRole);
                    }
                }
                // Load data after role is known
                loadPencairanDetail();
            },
            error: function(xhr) {
                console.log('Failed to fetch user role from /api/auth/me, falling back to localStorage', xhr);
                try {
                    const user = JSON.parse(localStorage.getItem('user'));
                    const roles = user && user.roles ? user.roles : [];
                    if (roles.length > 0) {
                        const first = roles[0];
                        let roleName = (typeof first === 'string') ? first : (first.name || '');
                        roleName = roleName.toLowerCase().trim().replace(/\s+/g, '_');
                        userRole = roleName;
                        console.log('User role (from localStorage, normalized):', userRole);
                    }
                } catch (e) {
                    console.log('No user in localStorage');
                }
                loadPencairanDetail();
            }
        });
    }

    // Load pencairan detail
    function loadPencairanDetail() {
        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    pencairanData = response.data;
                    renderDetail(pencairanData);
                    renderActionButtons(pencairanData);
                    renderApprovalProgress(pencairanData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Pencairan dana tidak ditemukan');
                    setTimeout(() => window.location.href = '/pencairan-dana', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data pencairan dana');
                }
            }
        });
    }

    // Render detail
    function renderDetail(data) {
        $('#nomorPencairan').text(data.nomor_pencairan);
        $('#jumlahPencairan').text(formatRupiah(data.jumlah_pencairan));
        $('#keperluan').text(data.keperluan);
        $('#tanggalPengajuan').text(formatDateTime(data.created_at));
        $('#creator').text(data.creator ? data.creator.name : '-');
        $('#statusBadge').html(getStatusBadge(data.status));

        // Kegiatan info
        if (data.anggaran_kegiatan) {
            const kegiatan = data.anggaran_kegiatan;
            $('#kegiatanNama').text(kegiatan.nama_kegiatan);
            $('#kegiatanKode').text(`Kode: ${kegiatan.kode_kegiatan}`);
            $('#linkKegiatan').attr('href', `/anggaran-kegiatan/${kegiatan.id}`);

            // Load kegiatan detail for budget info
            loadKegiatanBudget(kegiatan.id);
        }

        // Show tanggal cair if disbursed
        if (data.disbursed_at) {
            $('#tanggalCairRow').show();
            $('#tanggalCair').text(formatDateTime(data.disbursed_at));
        }

        // Show approver info if approved
        if (data.approved_by && data.approver) {
            $('#approverRow, #approvedAtRow').show();
            $('#approver').text(data.approver.name);
            $('#approvedAt').text(formatDateTime(data.approved_at));
        }

        // Show catatan if exists
        if (data.catatan) {
            $('#catatanRow').show();
            $('#catatan').html(`<div class="alert alert-info mb-0">${data.catatan}</div>`);
        }

        // Show documents if exists
        if (data.documents && data.documents.length > 0) {
            $('#documentsCard').show();
            renderDocuments(data.documents);
        }

        // Show rekap if exists
        if (data.rekap_pengajuan) {
            $('#rekapCard').show();
            renderRekap(data.rekap_pengajuan);
        }
    }

    // Load kegiatan budget info
    function loadKegiatanBudget(kegiatanId) {
        $.ajax({
            url: `/api/anggaran-kegiatan/${kegiatanId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#totalAnggaran').text(formatRupiah(data.anggaran_disetujui));
                    $('#totalPencairan').text(formatRupiah(data.total_pencairan || 0));
                    $('#sisaAnggaran').text(formatRupiah(data.sisa_anggaran || data.anggaran_disetujui));
                }
            }
        });
    }

    // Render action buttons based on status and user role
    function renderActionButtons(data) {
        let buttons = '';
        const status = data.status;

        // Edit button for draft or rejected (by creator)
        if (status === 'draft' || status === 'ditolak') {
            buttons += `
                <a href="/pencairan-dana/${data.id}/edit" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
            `;
        }

        // Submit button for draft or rejected (by creator)
        if (status === 'draft' || status === 'ditolak') {
            buttons += `
                <button type="button" class="btn btn-primary btn-block mb-2" id="btnSubmit">
                    <i class="fas fa-paper-plane"></i> Ajukan Persetujuan
                </button>
            `;
        }

        // Delete button for draft (by creator)
        if (status === 'draft') {
            buttons += `
                <button type="button" class="btn btn-danger btn-block mb-2" id="btnDelete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            `;
        }

        // Approval buttons based on role and status
        if (canApprove(status, userRole)) {
            buttons += `
                <button type="button" class="btn btn-success btn-block mb-2" id="btnApprove">
                    <i class="fas fa-check"></i> Setujui
                </button>
                <button type="button" class="btn btn-danger btn-block mb-2" id="btnReject">
                    <i class="fas fa-times"></i> Tolak
                </button>
            `;
        }

        // Disburse button for final approved (by kadiv_umum or kepala_jic)
        if (status === 'disetujui_kepala_jic' && (userRole === 'kadiv_umum' || userRole === 'kepala_jic' || userRole === 'super_admin' || userRole === 'admin')) {
            buttons += `
                <button type="button" class="btn btn-success btn-block mb-2" id="btnDisburse">
                    <i class="fas fa-money-bill-wave"></i> Cairkan Dana
                </button>
            `;
        }

        if (buttons === '') {
            buttons = '<p class="text-muted mb-0">Tidak ada aksi tersedia</p>';
        }

        $('#actionButtons').html(buttons);
    }

    // Check if user can approve based on status and role
    function canApprove(status, role) {
        // Normalize role to a consistent string
        const r = role ? String(role).toLowerCase().trim().replace(/\s+/g, '_') : '';
        console.log('canApprove check:', { status, role: r });

        // Kadiv or Kadiv Umum can approve when submitted
        if (status === 'diajukan' && (r === 'kadiv' || r === 'kadiv_umum')) return true;
        // Kepala JIC approves after Kadiv Umum
        if (status === 'disetujui_kadiv_umum' && r === 'kepala_jic') return true;
        // Admins always allowed
        if (r === 'super_admin' || r === 'admin') return true;
        return false;
    }

    // Render approval progress
    function renderApprovalProgress(data) {
        const steps = [
            { status: 'draft', label: 'Draft', icon: 'file-alt' },
            { status: 'diajukan', label: 'Diajukan', icon: 'paper-plane' },
            { status: 'disetujui_kadiv_umum', label: 'Kadiv Umum', icon: 'user-check' },
            { status: 'disetujui_kepala_jic', label: 'Kepala JIC', icon: 'check-circle' },
            { status: 'dicairkan', label: 'Dicairkan', icon: 'money-bill-wave' }
        ];

        const currentStatus = data.status;
        const statusIndex = steps.findIndex(s => s.status === currentStatus);

        let html = '<div class="approval-steps">';
        
        steps.forEach((step, index) => {
            let stepClass = '';
            let iconColor = 'text-muted';

            if (currentStatus === 'ditolak') {
                stepClass = index < statusIndex ? 'completed' : 'pending';
                iconColor = index < statusIndex ? 'text-success' : 'text-muted';
            } else {
                if (index < statusIndex) {
                    stepClass = 'completed';
                    iconColor = 'text-success';
                } else if (index === statusIndex) {
                    stepClass = 'current';
                    iconColor = 'text-primary';
                } else {
                    stepClass = 'pending';
                    iconColor = 'text-muted';
                }
            }

            html += `
                <div class="step ${stepClass} mb-3">
                    <div class="d-flex align-items-center">
                        <div class="step-icon mr-3">
                            <i class="fas fa-${step.icon} fa-2x ${iconColor}"></i>
                        </div>
                        <div class="step-info">
                            <div class="font-weight-bold">${step.label}</div>
                            <small class="text-muted">${getStepStatus(stepClass)}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        if (currentStatus === 'ditolak') {
            html += `
                <div class="step rejected mb-3">
                    <div class="d-flex align-items-center">
                        <div class="step-icon mr-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                        <div class="step-info">
                            <div class="font-weight-bold text-danger">Ditolak</div>
                            <small class="text-muted">Pengajuan ditolak</small>
                        </div>
                    </div>
                </div>
            `;
        }

        html += '</div>';
        $('#approvalProgress').html(html);
    }

    function getStepStatus(stepClass) {
        if (stepClass === 'completed') return 'Selesai';
        if (stepClass === 'current') return 'Sedang Diproses';
        return 'Menunggu';
    }

    // Render documents
    function renderDocuments(documents) {
        let html = '<div class="list-group">';
        documents.forEach(doc => {
            html += `
                <a href="${doc.file_url}" target="_blank" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-pdf text-danger"></i> ${doc.nama_dokumen}
                    <small class="text-muted float-right">${formatDate(doc.created_at)}</small>
                </a>
            `;
        });
        html += '</div>';
        $('#documentsList').html(html);
    }

    // Render rekap
    function renderRekap(rekap) {
        let html = `
            <p><strong>Nomor Rekap:</strong> ${rekap.nomor_rekap}</p>
            <p><strong>Status:</strong> ${getStatusBadge(rekap.status)}</p>
            <a href="/rekap-pengajuan/${rekap.id}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> Lihat Detail Rekap
            </a>
        `;
        $('#rekapInfo').html(html);
    }

    // Submit for approval
    $(document).on('click', '#btnSubmit', function() {
        if (confirm('Apakah Anda yakin ingin mengajukan pencairan dana ini untuk persetujuan?')) {
            $.ajax({
                url: `/api/pencairan-dana/${pencairanId}/submit`,
                method: 'POST',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => loadPencairanDetail(), 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal mengajukan pencairan dana');
                }
            });
        }
    });

    // Delete
    $(document).on('click', '#btnDelete', function() {
        if (confirm('Apakah Anda yakin ingin menghapus pencairan dana ini? Data yang dihapus tidak dapat dikembalikan!')) {
            $.ajax({
                url: `/api/pencairan-dana/${pencairanId}`,
                method: 'DELETE',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = '/pencairan-dana', 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menghapus pencairan dana');
                }
            });
        }
    });

    // Show approve modal
    $(document).on('click', '#btnApprove', function() {
        $('#approvalCatatan').val('');
        $('#approvalModal').modal('show');
    });

    // Confirm approve
    $('#btnConfirmApprove').on('click', function() {
        const catatan = $('#approvalCatatan').val();
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyetujui...');

        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}/approve`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#approvalModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadPencairanDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menyetujui pencairan dana');
            },
            complete: function() {
                $('#btnConfirmApprove').prop('disabled', false).html('<i class="fas fa-check"></i> Setujui');
            }
        });
    });

    // Show reject modal
    $(document).on('click', '#btnReject', function() {
        $('#rejectCatatan').val('');
        $('#rejectModal').modal('show');
    });

    // Confirm reject
    $('#btnConfirmReject').on('click', function() {
        const catatan = $('#rejectCatatan').val().trim();
        
        if (!catatan) {
            alert('Alasan penolakan harus diisi!');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menolak...');

        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}/reject`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadPencairanDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menolak pencairan dana');
            },
            complete: function() {
                $('#btnConfirmReject').prop('disabled', false).html('<i class="fas fa-times"></i> Tolak');
            }
        });
    });

    // Disburse
    $(document).on('click', '#btnDisburse', function() {
        if (confirm('Apakah Anda yakin ingin mencairkan dana ini? Pastikan semua proses sudah selesai.')) {
            $.ajax({
                url: `/api/pencairan-dana/${pencairanId}/disburse`,
                method: 'POST',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => loadPencairanDetail(), 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal mencairkan dana');
                }
            });
        }
    });

    // Helper functions
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui_kadiv_umum': '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
            'disetujui_kepala_jic': '<span class="badge badge-success">Disetujui Kepala JIC</span>',
            'dicairkan': '<span class="badge badge-success">Dicairkan</span>',
            'ditolak': '<span class="badge badge-danger">Ditolak</span>'
        };
        return badges[status] || status;
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric' 
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showAlert(type, message) {
        let alertClass = 'alert-info';
        if (type === 'success') alertClass = 'alert-success';
        if (type === 'error') alertClass = 'alert-danger';
        if (type === 'warning') alertClass = 'alert-warning';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>

<style>
/* Approval Steps Styling */
.approval-steps .step {
    position: relative;
    padding-left: 0;
}

.approval-steps .step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 30px;
    background-color: #e3e6f0;
}

.approval-steps .step.completed:not(:last-child)::after {
    background-color: #1cc88a;
}

.approval-steps .step.current:not(:last-child)::after {
    background-color: #4e73df;
}
</style>
@endpush