@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit LPJ Kegiatan</h1>
        <div>
            <a href="/lpj-kegiatan/{{ $id }}" class="btn btn-info"><i class="fas fa-eye"></i> Detail</a>
            <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit LPJ</h6>
                </div>
                <div class="card-body">
                    <form id="formLPJ">
                        <div class="form-group">
                            <label>Nomor LPJ</label>
                            <input type="text" class="form-control" id="nomor_lpj" readonly>
                        </div>

                        <div class="form-group">
                            <label>Kegiatan</label>
                            <input type="text" class="form-control" id="kegiatan_display" readonly>
                        </div>

                        <div id="kegiatanInfo">
                            <div class="alert alert-info">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Anggaran Disetujui</td>
                                        <td width="5%">:</td>
                                        <td class="font-weight-bold text-success" id="info_anggaran">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="total_realisasi">Total Realisasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                <input type="text" class="form-control" id="total_realisasi" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="sisaAnggaranInfo">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between">
                                    <div><strong>Sisa Anggaran:</strong></div>
                                    <div><h4 class="mb-0" id="sisa_anggaran_display">Rp 0</h4></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="laporan_kegiatan">Laporan Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="laporan_kegiatan" rows="8" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="alert alert-info" id="statusInfo" style="display: none;">
                            <strong>Status:</strong> <span id="currentStatus"></span>
                            <div id="rejectionNote" style="display: none;" class="mt-2">
                                <strong>Catatan:</strong>
                                <div id="rejectionText" class="mt-1"></div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="/lpj-kegiatan/{{ $id }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Info</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Aturan Edit:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Hanya Draft/Ditolak yang bisa diedit</li>
                        <li>Total realisasi ≤ Anggaran</li>
                        <li>Jelaskan laporan dengan lengkap</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const lpjId = {{ $id }};
    let submitAndApprove = false;
    let anggaranDisetujui = 0;

    loadLpjData();

    $('#total_realisasi').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        if (anggaranDisetujui > 0) {
            const realisasi = parseFloat(value) || 0;
            const sisaAnggaran = anggaranDisetujui - realisasi;
            
            if (realisasi > anggaranDisetujui) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('Melebihi anggaran');
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
            }
            
            $('#sisa_anggaran_display').text(formatRupiah(sisaAnggaran));
        }
    });

    function loadLpjData() {
        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}`,
            method: 'GET',
            headers: { 
                // 'Authorization': 'Bearer ' + localStorage.getItem('token') 
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    populateForm(response.data);
                }
            }
        });
    }

    function populateForm(data) {
        $('#nomor_lpj').val(data.nomor_lpj);
        $('#total_realisasi').val(formatNumber(data.total_realisasi));
        $('#laporan_kegiatan').val(data.laporan_kegiatan);

        if (data.anggaran_kegiatan) {
            anggaranDisetujui = data.anggaran_kegiatan.anggaran_disetujui;
            $('#kegiatan_display').val(data.anggaran_kegiatan.nama_kegiatan);
            $('#info_anggaran').text(formatRupiah(anggaranDisetujui));
            
            const sisaAnggaran = anggaranDisetujui - data.total_realisasi;
            $('#sisa_anggaran_display').text(formatRupiah(sisaAnggaran));
        }

        $('#statusInfo').show();
        $('#currentStatus').html(getStatusBadge(data.status));

        if (data.status === 'ditolak' && data.catatan) {
            $('#rejectionNote').show();
            $('#rejectionText').html(`<div class="alert alert-danger mb-0">${data.catatan}</div>`);
        }

        if (data.status !== 'draft' && data.status !== 'ditolak') {
            showAlert('warning', 'LPJ ini tidak dapat diedit');
            $('input:not([readonly]), textarea').prop('readonly', true);
            $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        }
    }

    $('#btnSubmit').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = false;
        submitForm();
    });

    $('#btnSubmitApproval').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = true;
        if (confirm('Simpan dan ajukan LPJ?')) submitForm();
    });

    function submitForm() {
        const totalRealisasi = parseFloat($('#total_realisasi').val().replace(/[^0-9]/g, ''));
        
        if (totalRealisasi > anggaranDisetujui) {
            showAlert('error', 'Total realisasi melebihi anggaran');
            return;
        }

        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        const formData = {
            total_realisasi: totalRealisasi,
            laporan_kegiatan: $('#laporan_kegiatan').val()
        };

        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}`,
            method: 'PUT',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json' },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    if (submitAndApprove) {
                        submitForApproval();
                    } else {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = `/lpj-kegiatan/${lpjId}`, 1500);
                    }
                }
            },
            error: function(xhr) {
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                $('#btnSubmitApproval').prop('disabled', false);
                showAlert('error', xhr.responseJSON?.message || 'Gagal menyimpan');
            }
        });
    }

    function submitForApproval() {
        $.ajax({
            url: `/api/lpj-kegiatan/${lpjId}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
             },
            success: function(response) {
                showAlert('success', 'LPJ berhasil disimpan dan diajukan');
                setTimeout(() => window.location.href = `/lpj-kegiatan/${lpjId}`, 1500);
            }
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui': '<span class="badge badge-success">Disetujui</span>',
            'ditolak': '<span class="badge badge-danger">Ditolak</span>'
        };
        return badges[status] || status;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        $('#alertContainer').html(`<div class="alert ${alertClass} alert-dismissible fade show">${message}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>`);
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
});
</script>
@endpush