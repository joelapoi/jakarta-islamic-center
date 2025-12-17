@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Buku Cek</h1>
        <div>
            <a href="/buku-cek/{{ $id }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Buku Cek</h6>
                </div>
                <div class="card-body">
                    <form id="formBukuCek">
                        <div class="form-group">
                            <label>Nomor Buku Cek</label>
                            <input type="text" class="form-control" id="nomor_buku_cek" readonly>
                        </div>

                        <div class="form-group">
                            <label>Rekap Pengajuan</label>
                            <input type="text" class="form-control" id="rekap_display" readonly>
                        </div>

                        <div id="rekapInfo">
                            <div class="alert alert-info">
                                <h6 class="font-weight-bold mb-2">Informasi Rekap Pengajuan:</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Nomor Rekap</td>
                                        <td width="5%">:</td>
                                        <td id="info_nomor">-</td>
                                    </tr>
                                    <tr>
                                        <td>Kegiatan</td>
                                        <td>:</td>
                                        <td id="info_kegiatan">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Sisa Dana</td>
                                        <td>:</td>
                                        <td class="font-weight-bold text-success" id="info_sisa">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_bank" name="nama_bank" 
                                   placeholder="Contoh: Bank BNI" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" 
                                   placeholder="Contoh: 1234567890" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="nama_penerima">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" 
                                   placeholder="Nama lengkap penerima" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="jumlah">Jumlah Dana <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control" id="jumlah" name="jumlah" 
                                       placeholder="0" required readonly>
                            </div>
                            <small class="form-text text-muted">Jumlah otomatis dari sisa dana rekap pengajuan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="keperluan">Keperluan</label>
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="4" 
                                      placeholder="Jelaskan keperluan pencairan dana..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="alert alert-info" role="alert" id="statusInfo" style="display: none;">
                            <i class="fas fa-info-circle"></i> <strong>Status:</strong> <span id="currentStatus"></span>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="/buku-cek/{{ $id }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Aturan Edit:</h6>
                    <ul class="pl-3">
                        <li>Hanya buku cek dengan status <strong>Draft</strong> yang dapat diedit</li>
                        <li>Setelah ditandatangani, data tidak dapat diubah</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan data bank benar</li>
                        <li>Periksa nama penerima dengan teliti</li>
                        <li>Jumlah akan otomatis dari sisa dana</li>
                    </ul>
                </div>
            </div>

            <!-- History Card -->
            <div class="card shadow mb-4" id="historyCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Riwayat
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td id="createdAt">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Update Terakhir:</td>
                            <td id="updatedAt">-</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const bukuCekId = {{ $id }};
    let originalData = null;

    // Load existing data
    loadBukuCekData();

    // Load buku cek data
    function loadBukuCekData() {
        $.ajax({
            url: `/api/buku-cek/${bukuCekId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    originalData = response.data;
                    populateForm(originalData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Buku cek tidak ditemukan');
                    setTimeout(() => window.location.href = '/buku-cek', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses untuk mengedit buku cek ini');
                    setTimeout(() => window.location.href = '/buku-cek', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data buku cek');
                }
            }
        });
    }

    // Populate form with existing data
    function populateForm(data) {
        $('#nomor_buku_cek').val(data.nomor_buku_cek);
        $('#nama_bank').val(data.nama_bank);
        $('#nomor_rekening').val(data.nomor_rekening);
        $('#nama_penerima').val(data.nama_penerima);
        $('#jumlah').val(formatNumber(data.jumlah));
        $('#keperluan').val(data.keperluan);

        // Rekap info
        if (data.rekap_pengajuan) {
            $('#rekap_display').val(data.rekap_pengajuan.nomor_rekap);
            $('#info_nomor').text(data.rekap_pengajuan.nomor_rekap);
            $('#info_sisa').text(formatRupiah(data.rekap_pengajuan.sisa_dana));

            if (data.rekap_pengajuan.pencairan_dana?.anggaran_kegiatan) {
                $('#info_kegiatan').text(data.rekap_pengajuan.pencairan_dana.anggaran_kegiatan.nama_kegiatan);
            }
        }

        // Show status info
        $('#statusInfo').show();
        $('#currentStatus').html(getStatusBadge(data.status));

        // Show history
        $('#historyCard').show();
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#updatedAt').text(formatDateTime(data.updated_at));

        // Check if can edit
        if (data.status !== 'draft') {
            showAlert('warning', 'Buku cek ini tidak dapat diedit karena sudah dalam proses atau telah ditandatangani');
            $('input:not([readonly]), textarea').prop('readonly', true);
            $('#btnSubmit').prop('disabled', true);
        }
    }

    // Submit form
    $('#btnSubmit').on('click', function(e) {
        e.preventDefault();
        submitForm();
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate
        if (!$('#nama_bank').val().trim()) {
            $('#nama_bank').addClass('is-invalid');
            $('#nama_bank').siblings('.invalid-feedback').text('Nama bank harus diisi');
            return;
        }

        if (!$('#nomor_rekening').val().trim()) {
            $('#nomor_rekening').addClass('is-invalid');
            $('#nomor_rekening').siblings('.invalid-feedback').text('Nomor rekening harus diisi');
            return;
        }

        if (!$('#nama_penerima').val().trim()) {
            $('#nama_penerima').addClass('is-invalid');
            $('#nama_penerima').siblings('.invalid-feedback').text('Nama penerima harus diisi');
            return;
        }

        // Disable submit button
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            nama_bank: $('#nama_bank').val().trim(),
            nomor_rekening: $('#nomor_rekening').val().trim(),
            nama_penerima: $('#nama_penerima').val().trim(),
            jumlah: parseFloat($('#jumlah').val().replace(/[^0-9]/g, '')),
            keperluan: $('#keperluan').val().trim()
        };

        // Update buku cek
        $.ajax({
            url: `/api/buku-cek/${bukuCekId}`,
            method: 'PUT',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.href = `/buku-cek/${bukuCekId}`;
                    }, 1500);
                }
            },
            error: function(xhr) {
                // Enable submit button
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                    showAlert('error', 'Terdapat kesalahan pada form. Silakan periksa kembali.');
                } else {
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan perubahan';
                    showAlert('error', message);
                }
            }
        });
    }

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

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Helper: Format DateTime
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

    // Helper: Show alert
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
@endpush