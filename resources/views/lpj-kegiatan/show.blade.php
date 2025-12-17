@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail LPJ Kegiatan</h1>
        <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div id="alertContainer"></div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi LPJ</h6>
                    <div id="statusBadge"></div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor LPJ</td>
                            <td width="5%">:</td>
                            <td id="nomorLpj">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div id="kegiatanNama">-</div>
                                <small><a href="#" id="linkKegiatan" class="text-primary">Lihat Detail Kegiatan</a></small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Anggaran Disetujui</td>
                            <td>:</td>
                            <td id="anggaranDisetujui" class="text-primary">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Realisasi</td>
                            <td>:</td>
                            <td id="totalRealisasi" class="text-danger font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Anggaran</td>
                            <td>:</td>
                            <td id="sisaAnggaran" class="text-success font-weight-bold h5">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Laporan Kegiatan</td>
                            <td>:</td>
                            <td><div id="laporanKegiatan" style="white-space: pre-wrap;">-</div></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Pada</td>
                            <td>:</td>
                            <td id="createdAt">-</td>
                        </tr>
                        <tr id="approverRow" style="display: none;">
                            <td class="font-weight-bold">Disetujui Oleh</td>
                            <td>:</td>
                            <td id="approver">-</td>
                        </tr>
                        <tr id="catatanRow" style="display: none;">
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td id="catatan">-</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tasks"></i> Aksi</h6>
                </div>
                <div class="card-body">
                    <div id="actionButtons"></div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-check-circle"></i> Progress</h6>
                </div>
                <div class="card-body">
                    <div id="progressSteps"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setujui LPJ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea class="form-control" id="approvalCatatan" rows="3"></textarea>
                </div>
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
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Tolak LPJ</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectCatatan" rows="4" required></textarea>
                </div>
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
    const lpjId = {{ $id }};
    let lpjData = null;
    let userRole = null;

    loadLpjDetail();
    getUserRole();

    function getUserRole() {
        const user = JSON.parse(localStorage.getItem('user'));
        if (user && user.role) userRole = user.role;
    }

    function loadLpjDetail() {
        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}`,
            method: 'GET',
            headers: { 
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
             },
            success: function(response) {
                if (response.success) {
                    lpjData = response.data;
                    renderDetail(lpjData);
                    renderActionButtons(lpjData);
                    renderProgress(lpjData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'LPJ tidak ditemukan');
                    setTimeout(() => window.location.href = '/lpj-kegiatan', 2000);
                }
            }
        });
    }

    function renderDetail(data) {
        $('#nomorLpj').text(data.nomor_lpj);
        $('#totalRealisasi').text(formatRupiah(data.total_realisasi));
        $('#sisaAnggaran').text(formatRupiah(data.sisa_anggaran));
        $('#laporanKegiatan').text(data.laporan_kegiatan);
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#statusBadge').html(getStatusBadge(data.status));

        if (data.anggaran_kegiatan) {
            $('#kegiatanNama').text(data.anggaran_kegiatan.nama_kegiatan);
            $('#linkKegiatan').attr('href', `/anggaran-kegiatan/${data.anggaran_kegiatan.id}`);
            $('#anggaranDisetujui').text(formatRupiah(data.anggaran_kegiatan.anggaran_disetujui));
        }

        if (data.approved_by && data.approver) {
            $('#approverRow').show();
            $('#approver').text(data.approver.name);
        }

        if (data.catatan) {
            $('#catatanRow').show();
            $('#catatan').html(`<div class="alert alert-info mb-0">${data.catatan}</div>`);
        }
    }

    function renderActionButtons(data) {
        let buttons = '';
        const status = data.status;

        if (status === 'draft' || status === 'ditolak') {
            buttons += `<a href="/lpj-kegiatan/${data.id}/edit" class="btn btn-warning btn-block mb-2"><i class="fas fa-edit"></i> Edit</a>`;
        }

        if (status === 'draft' || status === 'ditolak') {
            buttons += `<button type="button" class="btn btn-primary btn-block mb-2" id="btnSubmit"><i class="fas fa-paper-plane"></i> Ajukan</button>`;
        }

        if (status === 'draft') {
            buttons += `<button type="button" class="btn btn-danger btn-block mb-2" id="btnDelete"><i class="fas fa-trash"></i> Hapus</button>`;
        }

        if (canApprove(status, userRole)) {
            buttons += `<button type="button" class="btn btn-success btn-block mb-2" id="btnApprove"><i class="fas fa-check"></i> Setujui</button>`;
            buttons += `<button type="button" class="btn btn-danger btn-block mb-2" id="btnReject"><i class="fas fa-times"></i> Tolak</button>`;
        }

        $('#actionButtons').html(buttons || '<p class="text-muted mb-0">Tidak ada aksi</p>');
    }

    function canApprove(status, role) {
        if (status === 'diajukan' && (role === 'kadiv_umum' || role === 'kepala_jic')) return true;
        if (role === 'super_admin' || role === 'admin') return true;
        return false;
    }

    function renderProgress(data) {
        const steps = [
            { status: 'draft', label: 'Draft', icon: 'file-alt' },
            { status: 'diajukan', label: 'Diajukan', icon: 'paper-plane' },
            { status: 'disetujui', label: 'Disetujui', icon: 'check-circle' }
        ];

        const currentStatus = data.status;
        const statusIndex = steps.findIndex(s => s.status === currentStatus);

        let html = '<div class="progress-steps">';
        
        steps.forEach((step, index) => {
            let iconColor = 'text-muted';
            if (index < statusIndex) iconColor = 'text-success';
            else if (index === statusIndex) iconColor = 'text-primary';

            html += `
                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3"><i class="fas fa-${step.icon} fa-2x ${iconColor}"></i></div>
                        <div><div class="font-weight-bold">${step.label}</div></div>
                    </div>
                </div>
            `;
        });

        if (currentStatus === 'ditolak') {
            html += `<div class="mb-3"><div class="d-flex align-items-center"><div class="mr-3"><i class="fas fa-times-circle fa-2x text-danger"></i></div><div><div class="font-weight-bold text-danger">Ditolak</div></div></div></div>`;
        }

        html += '</div>';
        $('#progressSteps').html(html);
    }

    $(document).on('click', '#btnSubmit', function() {
        if (confirm('Ajukan LPJ untuk persetujuan?')) {
            $.ajax({
                url: `/api/lpj-kegiatan/${lpjId}/submit`,
                method: 'POST',
                headers: {
                    //  'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                    },
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => loadLpjDetail(), 1500);
                }
            });
        }
    });

    $(document).on('click', '#btnDelete', function() {
        if (confirm('Hapus LPJ ini?')) {
            $.ajax({
                url: `/api/lpj-kegiatan/${lpjId}`,
                method: 'DELETE',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + getToken(),
                 },
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => window.location.href = '/lpj-kegiatan', 1500);
                }
            });
        }
    });

    $(document).on('click', '#btnApprove', function() {
        $('#approvalCatatan').val('');
        $('#approvalModal').modal('show');
    });

    $('#btnConfirmApprove').on('click', function() {
        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}/approve`,
            method: 'POST',
            headers: { 
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json' },
            data: JSON.stringify({ catatan: $('#approvalCatatan').val() }),
            success: function(response) {
                $('#approvalModal').modal('hide');
                showAlert('success', response.message);
                setTimeout(() => loadLpjDetail(), 1500);
            }
        });
    });

    $(document).on('click', '#btnReject', function() {
        $('#rejectCatatan').val('');
        $('#rejectModal').modal('show');
    });

    $('#btnConfirmReject').on('click', function() {
        const catatan = $('#rejectCatatan').val().trim();
        if (!catatan) {
            alert('Alasan penolakan harus diisi!');
            return;
        }

        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}/reject`,
            method: 'POST',
            headers: { 
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'), 
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json' },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                $('#rejectModal').modal('hide');
                showAlert('success', response.message);
                setTimeout(() => loadLpjDetail(), 1500);
            }
        });
    });

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

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#alertContainer').html(`<div class="alert ${alertClass} alert-dismissible fade show">${message}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>`);
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
});
</script>
@endpush