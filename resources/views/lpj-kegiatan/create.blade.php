@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat LPJ Kegiatan</h1>
        <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div id="alertContainer"></div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form LPJ Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form id="formLPJ">
                        <div class="form-group">
                            <label for="anggaran_kegiatan_id">Kegiatan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" id="anggaran_kegiatan_id" name="anggaran_kegiatan_id">
                                <input type="text" class="form-control" id="kegiatan_display" readonly 
                                       placeholder="Pilih kegiatan">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="btnPilihKegiatan">
                                        <i class="fas fa-search"></i> Pilih
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="kegiatanInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="font-weight-bold mb-2">Informasi Kegiatan:</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Kode Kegiatan</td>
                                        <td width="5%">:</td>
                                        <td id="info_kode">-</td>
                                    </tr>
                                    <tr>
                                        <td>Periode</td>
                                        <td>:</td>
                                        <td id="info_periode">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Anggaran Disetujui</td>
                                        <td>:</td>
                                        <td class="font-weight-bold text-success" id="info_anggaran">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="total_realisasi">Total Realisasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control" id="total_realisasi" name="total_realisasi" 
                                       placeholder="0" required>
                            </div>
                            <small class="form-text text-muted">Total realisasi penggunaan anggaran</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="sisaAnggaranInfo" style="display: none;">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div><strong>Sisa Anggaran:</strong></div>
                                    <div><h4 class="mb-0" id="sisa_anggaran_display">Rp 0</h4></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="laporan_kegiatan">Laporan Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="laporan_kegiatan" name="laporan_kegiatan" rows="8" 
                                      placeholder="Jelaskan pelaksanaan kegiatan, hasil yang dicapai, dan pertanggungjawaban penggunaan dana..." required></textarea>
                            <small class="form-text text-muted">Laporan lengkap pertanggungjawaban kegiatan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Alur LPJ:</h6>
                    <ol class="pl-3">
                        <li>Draft dibuat</li>
                        <li>Diajukan untuk persetujuan</li>
                        <li>Disetujui Bendahara</li>
                        <li>Pengarsipan</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pilih kegiatan yang sudah selesai</li>
                        <li>Total realisasi ≤ Anggaran disetujui</li>
                        <li>Jelaskan laporan dengan detail</li>
                        <li>Sertakan hasil dan dokumentasi</li>
                    </ul>
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
                <h5 class="modal-title">Pilih Kegiatan</h5>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kegiatanList">
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let submitAndApprove = false;
    let selectedKegiatan = null;
    let anggaranDisetujui = 0;

    const urlParams = new URLSearchParams(window.location.search);
    const anggaranIdParam = urlParams.get('anggaran_id');
    
    if (anggaranIdParam) {
        loadKegiatanDetail(anggaranIdParam);
    }

    $('#total_realisasi').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        if (anggaranDisetujui > 0) {
            const realisasi = parseFloat(value) || 0;
            const sisaAnggaran = anggaranDisetujui - realisasi;
            
            if (realisasi > anggaranDisetujui) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text(`Total realisasi melebihi anggaran (${formatRupiah(anggaranDisetujui)})`);
                $('#sisaAnggaranInfo').hide();
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
                $('#sisa_anggaran_display').text(formatRupiah(sisaAnggaran));
                $('#sisaAnggaranInfo').show();
            }
        }
    });

    $('#formLPJ input, #formLPJ textarea').on('input', function() {
        if (isFormValid()) {
            $('#btnSubmitApproval').show();
        } else {
            $('#btnSubmitApproval').hide();
        }
    });

    $('#btnPilihKegiatan').on('click', function() {
        loadKegiatanList();
        $('#kegiatanModal').modal('show');
    });

    let searchKegiatanTimeout;
    $('#searchKegiatan').on('keyup', function() {
        clearTimeout(searchKegiatanTimeout);
        searchKegiatanTimeout = setTimeout(function() {
            loadKegiatanList();
        }, 500);
    });

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
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderKegiatanList(response.data.data);
                }
            }
        });
    }

    function renderKegiatanList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="4" class="text-center">Tidak ada kegiatan yang disetujui</td></tr>';
        } else {
            data.forEach(function(item) {
                html += `
                    <tr>
                        <td>${item.kode_kegiatan}</td>
                        <td>${item.nama_kegiatan}</td>
                        <td>${formatRupiah(item.anggaran_disetujui)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-kegiatan" data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#kegiatanList').html(html);
    }

    $(document).on('click', '.btn-select-kegiatan', function() {
        const kegiatanId = $(this).data('id');
        loadKegiatanDetail(kegiatanId);
        $('#kegiatanModal').modal('hide');
    });

    function loadKegiatanDetail(kegiatanId) {
        $.ajax({
            url: `/api/anggaran-kegiatan/${kegiatanId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    selectedKegiatan = response.data;
                    anggaranDisetujui = response.data.anggaran_disetujui;
                    
                    $('#anggaran_kegiatan_id').val(selectedKegiatan.id);
                    $('#kegiatan_display').val(`${selectedKegiatan.kode_kegiatan} - ${selectedKegiatan.nama_kegiatan}`);
                    
                    $('#info_kode').text(selectedKegiatan.kode_kegiatan);
                    $('#info_periode').text(`${formatDate(selectedKegiatan.tanggal_mulai)} s/d ${formatDate(selectedKegiatan.tanggal_selesai)}`);
                    $('#info_anggaran').text(formatRupiah(selectedKegiatan.anggaran_disetujui));
                    
                    $('#kegiatanInfo').show();
                }
            }
        });
    }

    $('#btnSubmit').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = false;
        submitForm();
    });

    $('#btnSubmitApproval').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = true;
        
        if (confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan LPJ ini?')) {
            submitForm();
        }
    });

    function submitForm() {
        if (!$('#anggaran_kegiatan_id').val()) {
            showAlert('error', 'Silakan pilih kegiatan terlebih dahulu');
            return;
        }

        const totalRealisasi = parseFloat($('#total_realisasi').val().replace(/[^0-9]/g, ''));
        if (totalRealisasi > anggaranDisetujui) {
            showAlert('error', 'Total realisasi melebihi anggaran');
            return;
        }

        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        const formData = {
            anggaran_kegiatan_id: parseInt($('#anggaran_kegiatan_id').val()),
            total_realisasi: totalRealisasi,
            laporan_kegiatan: $('#laporan_kegiatan').val()
        };

        $.ajax({
            url: '/api/lpj-kegiatan',
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    if (submitAndApprove) {
                        submitForApproval(response.data.id);
                    } else {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = '/lpj-kegiatan', 1500);
                    }
                }
            },
            error: function(xhr) {
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan sebagai Draft');
                $('#btnSubmitApproval').prop('disabled', false);
                showAlert('error', xhr.responseJSON?.message || 'Gagal menyimpan LPJ');
            }
        });
    }

    function submitForApproval(id) {
        $.ajax({
            url: `/api/lpj-kegiatan/${id}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                showAlert('success', 'LPJ berhasil disimpan dan diajukan');
                setTimeout(() => window.location.href = '/lpj-kegiatan', 1500);
            }
        });
    }

    function isFormValid() {
        const kegiatanId = $('#anggaran_kegiatan_id').val();
        const realisasi = $('#total_realisasi').val().replace(/[^0-9]/g, '');
        const laporan = $('#laporan_kegiatan').val().trim();

        return kegiatanId && realisasi && laporan && parseFloat(realisasi) <= anggaranDisetujui;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

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
        $('#alertContainer').html(alertHtml);
        setTimeout(() => $('.alert').fadeOut('slow', function() { $(this).remove(); }), 5000);
    }
});
</script>
@endpush