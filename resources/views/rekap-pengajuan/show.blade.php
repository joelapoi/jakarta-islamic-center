@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Rekap Pengajuan</h1>
        <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Rekap Pengajuan</h6>
                    <div id="statusBadge"></div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Rekap</td>
                            <td width="5%">:</td>
                            <td id="nomorRekap">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pencairan Dana</td>
                            <td>:</td>
                            <td>
                                <div id="pencairanNomor">-</div>
                                <small class="text-muted">
                                    <a href="#" id="linkPencairan" class="text-primary">Lihat Detail Pencairan</a>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div id="kegiatanNama">-</div>
                                <small class="text-muted">
                                    <a href="#" id="linkKegiatan" class="text-primary">Lihat Detail Kegiatan</a>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pencairan</td>
                            <td>:</td>
                            <td id="jumlahPencairan" class="text-primary font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Pengeluaran</td>
                            <td>:</td>
                            <td id="totalPengeluaran" class="text-danger font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Dana</td>
                            <td>:</td>
                            <td id="sisaDana" class="text-success font-weight-bold h5">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td id="catatan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Pada</td>
                            <td>:</td>
                            <td id="createdAt">-</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Dana</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Jumlah Pencairan</small>
                                <h4 class="text-primary font-weight-bold mb-0" id="summaryPencairan">Rp 0</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Total Pengeluaran</small>
                                <h4 class="text-danger font-weight-bold mb-0" id="summaryPengeluaran">Rp 0</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Sisa Dana</small>
                                <h4 class="text-success font-weight-bold mb-0" id="summarySisa">Rp 0</h4>
                            </div>
                        </div>
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

            <!-- Buku Cek Card -->
            <div class="card shadow mb-4" id="bukuCekCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Buku Cek</h6>
                </div>
                <div class="card-body">
                    <div id="bukuCekInfo"></div>
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
                        <i class="fas fa-check-circle"></i> Status Persetujuan
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
                <h5 class="modal-title">Setujui Rekap Pengajuan</h5>
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
                <h5 class="modal-title">Tolak Rekap Pengajuan</h5>
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
    const rekapId = {{ $id }};
    let rekapData = null;
    let userRole = null;

    // Load user role first, then load data
    getUserRole();

    // Get user role
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
                        if (typeof first === 'object' && first.name) {
                            userRole = first.name.toLowerCase().trim().replace(/\s+/g, '_');
                        } else if (typeof first === 'string') {
                            userRole = first.toLowerCase().trim().replace(/\s+/g, '_');
                        }
                        console.log('User role (normalized from API):', userRole);
                    }
                }
                loadRekapDetail();
            },
            error: function(xhr) {
                console.log('Failed to fetch user role from /api/auth/me');
                try {
                    const user = JSON.parse(localStorage.getItem('user'));
                    const roles = user && user.roles ? user.roles : [];
                    if (roles.length > 0) {
                        const first = roles[0];
                        if (typeof first === 'object' && first.name) {
                            userRole = first.name.toLowerCase().trim().replace(/\s+/g, '_');
                        } else if (typeof first === 'string') {
                            userRole = first.toLowerCase().trim().replace(/\s+/g, '_');
                        }
                        console.log('User role (from localStorage, normalized):', userRole);
                    }
                } catch (e) {
                    console.log('No user in localStorage');
                }
                loadRekapDetail();
            }
        });
    }

    // Load rekap detail
    function loadRekapDetail() {
        $.ajax({
            url: `/api/rekap-pengajuan/${rekapId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    rekapData = response.data;
                    renderDetail(rekapData);
                    renderActionButtons(rekapData);
                    renderApprovalProgress(rekapData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Rekap pengajuan tidak ditemukan');
                    setTimeout(() => window.location.href = '/rekap-pengajuan', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data rekap pengajuan');
                }
            }
        });
    }

    // Render detail
    function renderDetail(data) {
        $('#nomorRekap').text(data.nomor_rekap);
        $('#totalPengeluaran').text(formatRupiah(data.total_pengeluaran));
        $('#sisaDana').text(formatRupiah(data.sisa_dana));
        $('#catatan').text(data.catatan || '-');
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#statusBadge').html(getStatusBadge(data.status));

        // Summary
        $('#summaryPengeluaran').text(formatRupiah(data.total_pengeluaran));
        $('#summarySisa').text(formatRupiah(data.sisa_dana));

        // Pencairan info
        if (data.pencairan_dana) {
            const pencairan = data.pencairan_dana;
            $('#pencairanNomor').text(pencairan.nomor_pencairan);
            $('#linkPencairan').attr('href', `/pencairan-dana/${pencairan.id}`);
            $('#jumlahPencairan').text(formatRupiah(pencairan.jumlah_pencairan));
            $('#summaryPencairan').text(formatRupiah(pencairan.jumlah_pencairan));

            // Kegiatan info
            if (pencairan.anggaran_kegiatan) {
                $('#kegiatanNama').text(pencairan.anggaran_kegiatan.nama_kegiatan);
                $('#linkKegiatan').attr('href', `/anggaran-kegiatan/${pencairan.anggaran_kegiatan.id}`);
            }
        }

        // Show documents if exists
        if (data.documents && data.documents.length > 0) {
            $('#documentsCard').show();
            renderDocuments(data.documents);
        }

        // Show buku cek if exists
        if (data.buku_cek) {
            $('#bukuCekCard').show();
            renderBukuCek(data.buku_cek);
        }
    }

    // Render action buttons based on status and user role
    function renderActionButtons(data) {
        let buttons = '';
        const status = data.status;

        // Edit button for draft or rejected (by creator)
        if (status === 'draft' || status === 'ditolak') {
            buttons += `
                <a href="/rekap-pengajuan/${data.id}/edit" class="btn btn-warning btn-block mb-2">
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

        // Create Buku Cek button (if approved and no buku cek yet)
        if (status === 'disetujui' && !data.buku_cek) {
            if (userRole === 'kadiv_umum' || userRole === 'kepala_jic' || userRole === 'super_admin' || userRole === 'admin') {
                buttons += `
                    <a href="/buku-cek/create?rekap_id=${data.id}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-book"></i> Buat Buku Cek
                    </a>
                `;
            }
        }

        if (buttons === '') {
            buttons = '<p class="text-muted mb-0">Tidak ada aksi tersedia</p>';
        }

        $('#actionButtons').html(buttons);
    }

    // Check if user can approve based on status and role
    function canApprove(status, role) {
        let r = '';
        if (role) {
            if (typeof role === 'object' && role.name) {
                r = role.name.toLowerCase().trim().replace(/\s+/g, '_');
            } else {
                r = String(role).toLowerCase().trim().replace(/\s+/g, '_');
            }
        }
        console.log('canApprove check:', { status, role: r });
        if (status === 'diajukan' && (r === 'kadiv_umum' || r === 'kepala_jic')) return true;
        if (r === 'super_admin' || r === 'admin') return true;
        return false;
    }

    // Render approval progress
    function renderApprovalProgress(data) {
        const steps = [
            { status: 'draft', label: 'Draft', icon: 'file-alt' },
            { status: 'diajukan', label: 'Diajukan', icon: 'paper-plane' },
            { status: 'disetujui', label: 'Disetujui', icon: 'check-circle' }
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
                            ${data.catatan ? `<div class="alert alert-danger mt-2 mb-0"><small>${data.catatan}</small></div>` : ''}
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

    // Render buku cek info
    function renderBukuCek(bukuCek) {
        let html = `
            <p><strong>Nomor Buku Cek:</strong> ${bukuCek.nomor_buku_cek}</p>
            <p><strong>Status:</strong> ${getStatusBadge(bukuCek.status)}</p>
            <a href="/buku-cek/${bukuCek.id}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> Lihat Detail Buku Cek
            </a>
        `;
        $('#bukuCekInfo').html(html);
    }

    // Submit for approval
    $(document).on('click', '#btnSubmit', function() {
        if (confirm('Apakah Anda yakin ingin mengajukan rekap pengajuan ini untuk persetujuan?')) {
            $.ajax({
                url: `/api/rekap-pengajuan/${rekapId}/submit`,
                method: 'POST',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => loadRekapDetail(), 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal mengajukan rekap pengajuan');
                }
            });
        }
    });

    // Delete
    $(document).on('click', '#btnDelete', function() {
        if (confirm('Apakah Anda yakin ingin menghapus rekap pengajuan ini? Data yang dihapus tidak dapat dikembalikan!')) {
            $.ajax({
                url: `/api/rekap-pengajuan/${rekapId}`,
                method: 'DELETE',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = '/rekap-pengajuan', 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menghapus rekap pengajuan');
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
            url: `/api/rekap-pengajuan/${rekapId}/approve`,
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
                    setTimeout(() => loadRekapDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menyetujui rekap pengajuan');
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
            url: `/api/rekap-pengajuan/${rekapId}/reject`,
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
                    setTimeout(() => loadRekapDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menolak rekap pengajuan');
            },
            complete: function() {
                $('#btnConfirmReject').prop('disabled', false).html('<i class="fas fa-times"></i> Tolak');
            }
        });
    });

    // Helper functions
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui': '<span class="badge badge-success">Disetujui</span>',
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