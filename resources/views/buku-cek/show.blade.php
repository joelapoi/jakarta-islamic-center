@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Buku Cek</h1>
        <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Buku Cek</h6>
                    <div id="statusBadge"></div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Buku Cek</td>
                            <td width="5%">:</td>
                            <td id="nomorBukuCek">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Rekap Pengajuan</td>
                            <td>:</td>
                            <td>
                                <div id="rekapNomor">-</div>
                                <small class="text-muted">
                                    <a href="#" id="linkRekap" class="text-primary">Lihat Detail Rekap</a>
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
                            <td colspan="3"><hr></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Bank</td>
                            <td>:</td>
                            <td id="namaBank">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nomor Rekening</td>
                            <td>:</td>
                            <td id="nomorRekening">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Penerima</td>
                            <td>:</td>
                            <td id="namaPenerima">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah</td>
                            <td>:</td>
                            <td id="jumlah" class="text-success font-weight-bold h5">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Keperluan</td>
                            <td>:</td>
                            <td id="keperluan">-</td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Dibuat</td>
                            <td>:</td>
                            <td id="createdAt">-</td>
                        </tr>
                        <tr id="ttdRow" style="display: none;">
                            <td class="font-weight-bold">Ditandatangani Pada</td>
                            <td>:</td>
                            <td id="tanggalTtd">-</td>
                        </tr>
                        <tr id="ttdByRow" style="display: none;">
                            <td class="font-weight-bold">Ditandatangani Oleh</td>
                            <td>:</td>
                            <td id="ttdBy">-</td>
                        </tr>
                        <tr id="konfirmasiRow" style="display: none;">
                            <td class="font-weight-bold">Dikonfirmasi Bank Pada</td>
                            <td>:</td>
                            <td id="tanggalKonfirmasi">-</td>
                        </tr>
                        <tr id="konfirmasiByRow" style="display: none;">
                            <td class="font-weight-bold">Dikonfirmasi Oleh</td>
                            <td>:</td>
                            <td id="konfirmasiBy">-</td>
                        </tr>
                        <tr id="catatanRow" style="display: none;">
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td id="catatan">-</td>
                        </tr>
                    </table>
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

            <!-- Progress Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Progress
                    </h6>
                </div>
                <div class="card-body">
                    <div id="progressSteps">
                        <!-- Progress will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tanda Tangan Buku Cek</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Dengan menandatangani buku cek ini, Anda menyetujui pencairan dana sebesar:</p>
                <h4 class="text-center text-success" id="signatureAmount">Rp 0</h4>
                <p class="text-center">Kepada: <strong id="signatureReceiver">-</strong></p>
                <hr>
                <form id="signatureForm">
                    <div class="form-group">
                        <label for="signatureCatatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="signatureCatatan" rows="3" 
                                  placeholder="Masukkan catatan jika diperlukan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmSignature">
                    <i class="fas fa-signature"></i> Tanda Tangan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bank Confirmation Modal -->
<div class="modal fade" id="bankConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Bank</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Konfirmasi bahwa dana telah dicairkan ke bank:</p>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">Bank</td>
                        <td>:</td>
                        <td id="confirmBank">-</td>
                    </tr>
                    <tr>
                        <td>No. Rekening</td>
                        <td>:</td>
                        <td id="confirmRekening">-</td>
                    </tr>
                    <tr>
                        <td>Penerima</td>
                        <td>:</td>
                        <td id="confirmPenerima">-</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>:</td>
                        <td class="text-success font-weight-bold" id="confirmAmount">Rp 0</td>
                    </tr>
                </table>
                <hr>
                <form id="confirmForm">
                    <div class="form-group">
                        <label for="confirmCatatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="confirmCatatan" rows="3" 
                                  placeholder="Masukkan catatan konfirmasi"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmBank">
                    <i class="fas fa-check"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const bukuCekId = {{ $id }};
    let bukuCekData = null;
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
                loadBukuCekDetail();
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
                loadBukuCekDetail();
            }
        });
    }

    // Load buku cek detail
    function loadBukuCekDetail() {
        $.ajax({
            url: `/api/buku-cek/${bukuCekId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    bukuCekData = response.data;
                    renderDetail(bukuCekData);
                    renderActionButtons(bukuCekData);
                    renderProgress(bukuCekData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Buku cek tidak ditemukan');
                    setTimeout(() => window.location.href = '/buku-cek', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data buku cek');
                }
            }
        });
    }

    // Render detail
    function renderDetail(data) {
        $('#nomorBukuCek').text(data.nomor_cek);
        $('#namaBank').text(data.bank_name);
        $('#nomorRekening').text(data.nomor_rekening || '-');
        $('#namaPenerima').text(data.penerima);
        $('#jumlah').text(formatRupiah(data.nominal));
        $('#keperluan').text(data.keterangan || '-');
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#statusBadge').html(getStatusBadge(data.status));

        // Rekap info
        if (data.rekap_pengajuan) {
            const rekap = data.rekap_pengajuan;
            $('#rekapNomor').text(rekap.nomor_rekap);
            $('#linkRekap').attr('href', `/rekap-pengajuan/${rekap.id}`);

            // Kegiatan info
            if (rekap.pencairan_dana?.anggaran_kegiatan) {
                $('#kegiatanNama').text(rekap.pencairan_dana.anggaran_kegiatan.nama_kegiatan);
                $('#linkKegiatan').attr('href', `/anggaran-kegiatan/${rekap.pencairan_dana.anggaran_kegiatan.id}`);
            }
        }

        // Show signature info if signed
        if (data.signed_at) {
            $('#ttdRow, #ttdByRow').show();
            $('#tanggalTtd').text(formatDateTime(data.signed_at));
            // Note: signed_by user info will need to be added to controller response
            $('#ttdBy').text(data.signed_by?.name || 'Kepala JIC');
        }

        // Show confirmation info if confirmed (cashed)
        if (data.cashed_at) {
            $('#konfirmasiRow, #konfirmasiByRow').show();
            $('#tanggalKonfirmasi').text(formatDateTime(data.cashed_at));
            // Note: cashed_by user info will need to be added to controller response
            $('#konfirmasiBy').text(data.cashed_by?.name || 'Bank');
        }

        // Show catatan if exists (using notes field if available)
        if (data.notes) {
            $('#catatanRow').show();
            $('#catatan').html(`<div class="alert alert-info mb-0">${data.notes}</div>`);
        }

        // Show documents if exists
        if (data.documents && data.documents.length > 0) {
            $('#documentsCard').show();
            renderDocuments(data.documents);
        }
    }

    // Render action buttons based on status and user role
    function renderActionButtons(data) {
        let buttons = '';
        const status = data.status;

        // Edit button for draft
        if (status === 'draft') {
            buttons += `
                <a href="/buku-cek/${data.id}/edit" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
            `;
        }

        // Delete button for draft
        if (status === 'draft') {
            buttons += `
                <button type="button" class="btn btn-danger btn-block mb-2" id="btnDelete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            `;
        }

        // Signature button for Kepala JIC
        if (status === 'menunggu_ttd_kepala_jic' && (userRole === 'kepala_jic' || userRole === 'super_admin' || userRole === 'admin')) {
            buttons += `
                <button type="button" class="btn btn-success btn-block mb-2" id="btnSign">
                    <i class="fas fa-signature"></i> Tanda Tangan
                </button>
            `;
        }

        // Bank confirmation button
        if (status === 'ditandatangani' && (userRole === 'kadiv_umum' || userRole === 'kepala_jic' || userRole === 'super_admin' || userRole === 'admin')) {
            buttons += `
                <button type="button" class="btn btn-primary btn-block mb-2" id="btnConfirm">
                    <i class="fas fa-check-double"></i> Konfirmasi Bank
                </button>
            `;
        }

        if (buttons === '') {
            buttons = '<p class="text-muted mb-0">Tidak ada aksi tersedia</p>';
        }

        $('#actionButtons').html(buttons);
    }

    // Render progress
    function renderProgress(data) {
        const steps = [
            { status: 'draft', label: 'Draft Dibuat', icon: 'file-alt' },
            { status: 'menunggu_ttd_kepala_jic', label: 'Menunggu TTD', icon: 'signature' },
            { status: 'ditandatangani', label: 'Ditandatangani', icon: 'check' },
            { status: 'dikonfirmasi_bank', label: 'Dikonfirmasi Bank', icon: 'check-double' }
        ];

        const currentStatus = data.status;
        const statusIndex = steps.findIndex(s => s.status === currentStatus);

        let html = '<div class="progress-steps">';
        
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
                            <small class="text-muted">Buku cek ditolak</small>
                        </div>
                    </div>
                </div>
            `;
        }

        html += '</div>';
        $('#progressSteps').html(html);
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

    // Delete
    $(document).on('click', '#btnDelete', function() {
        if (confirm('Apakah Anda yakin ingin menghapus buku cek ini? Data yang dihapus tidak dapat dikembalikan!')) {
            $.ajax({
                url: `/api/buku-cek/${bukuCekId}`,
                method: 'DELETE',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = '/buku-cek', 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menghapus buku cek');
                }
            });
        }
    });

    // Show signature modal
    $(document).on('click', '#btnSign', function() {
        $('#signatureAmount').text(formatRupiah(bukuCekData.nominal));
        $('#signatureReceiver').text(bukuCekData.penerima);
        $('#signatureCatatan').val('');
        $('#signatureModal').modal('show');
    });

    // Confirm signature
    $('#btnConfirmSignature').on('click', function() {
        const catatan = $('#signatureCatatan').val();
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menandatangani...');

        $.ajax({
            url: `/api/buku-cek/${bukuCekId}/sign`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#signatureModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadBukuCekDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menandatangani buku cek');
            },
            complete: function() {
                $('#btnConfirmSignature').prop('disabled', false).html('<i class="fas fa-signature"></i> Tanda Tangan');
            }
        });
    });

    // Show bank confirm modal
    $(document).on('click', '#btnConfirm', function() {
        $('#confirmBank').text(bukuCekData.bank_name);
        $('#confirmRekening').text(bukuCekData.nomor_rekening || '-');
        $('#confirmPenerima').text(bukuCekData.penerima);
        $('#confirmAmount').text(formatRupiah(bukuCekData.nominal));
        $('#confirmCatatan').val('');
        $('#bankConfirmModal').modal('show');
    });

    // Confirm bank
    $('#btnConfirmBank').on('click', function() {
        const catatan = $('#confirmCatatan').val();
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengkonfirmasi...');

        $.ajax({
            url: `/api/buku-cek/${bukuCekId}/confirm-bank`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#bankConfirmModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadBukuCekDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal mengkonfirmasi bank');
            },
            complete: function() {
                $('#btnConfirmBank').prop('disabled', false).html('<i class="fas fa-check"></i> Konfirmasi');
            }
        });
    });

    // Helper functions
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
/* Progress Steps Styling */
.progress-steps .step {
    position: relative;
    padding-left: 0;
}

.progress-steps .step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 30px;
    background-color: #e3e6f0;
}

.progress-steps .step.completed:not(:last-child)::after {
    background-color: #1cc88a;
}

.progress-steps .step.current:not(:last-child)::after {
    background-color: #4e73df;
}
</style>
@endpush