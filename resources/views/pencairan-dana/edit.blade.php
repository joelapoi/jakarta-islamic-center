@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Pencairan Dana</h1>
        <div>
            <a href="/pencairan-dana/{{ $id }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('pencairan-dana.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Pencairan Dana</h6>
                </div>
                <div class="card-body">
                    <form id="formPencairanDana">
                        <div class="form-group">
                            <label>Nomor Pencairan</label>
                            <input type="text" class="form-control" id="nomor_pencairan" readonly>
                        </div>

                        <div class="form-group">
                            <label>Kegiatan</label>
                            <input type="text" class="form-control" id="kegiatan_display" readonly>
                        </div>

                        <div id="kegiatanInfo">
                            <div class="alert alert-info">
                                <h6 class="font-weight-bold mb-2">Informasi Anggaran Kegiatan:</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Kode Kegiatan</td>
                                        <td width="5%">:</td>
                                        <td id="info_kode">-</td>
                                    </tr>
                                    <tr>
                                        <td>Total Anggaran</td>
                                        <td>:</td>
                                        <td id="info_total_anggaran">-</td>
                                    </tr>
                                    <tr>
                                        <td>Total Pencairan</td>
                                        <td>:</td>
                                        <td id="info_total_pencairan">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Sisa Anggaran</td>
                                        <td>:</td>
                                        <td class="font-weight-bold text-success" id="info_sisa_anggaran">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="jumlah_pencairan">Jumlah Pencairan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control" id="jumlah_pencairan" name="jumlah_pencairan" 
                                       placeholder="0" required>
                            </div>
                            <small class="form-text text-muted">Jumlah yang akan dicairkan dari anggaran kegiatan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="keperluan">Keperluan / Keterangan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="5" 
                                      placeholder="Jelaskan keperluan pencairan dana ini..." required></textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="alert alert-info" role="alert" id="statusInfo" style="display: none;">
                            <i class="fas fa-info-circle"></i> <strong>Status:</strong> <span id="currentStatus"></span>
                            <div id="rejectionNote" style="display: none;" class="mt-2">
                                <strong>Catatan Penolakan:</strong>
                                <div id="rejectionText" class="mt-1"></div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="/pencairan-dana/{{ $id }}" class="btn btn-secondary">
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
                        <li>Hanya pencairan dengan status <strong>Draft</strong> atau <strong>Ditolak</strong> yang dapat diedit</li>
                        <li>Setelah disetujui, data tidak dapat diubah</li>
                        <li>Jika ditolak, perbaiki sesuai catatan penolakan</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan semua data terisi dengan benar</li>
                        <li>Jumlah pencairan tidak boleh melebihi sisa anggaran</li>
                        <li>Jelaskan keperluan dengan detail</li>
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
                            <td class="font-weight-bold">Oleh:</td>
                            <td id="createdBy">-</td>
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
    const pencairanId = {{ $id }};
    let submitAndApprove = false;
    let originalData = null;
    let sisaAnggaran = 0;
    let originalJumlah = 0;

    // Load existing data
    loadPencairanData();

    // Format currency input
    $('#jumlah_pencairan').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Check if exceeds sisa anggaran (exclude original amount)
        const jumlah = parseFloat(value);
        const availableBudget = sisaAnggaran + originalJumlah;
        
        if (jumlah > availableBudget) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text(`Jumlah melebihi sisa anggaran (${formatRupiah(availableBudget)})`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        }
    });

    // Load pencairan data
    function loadPencairanData() {
        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    originalData = response.data;
                    originalJumlah = response.data.jumlah_pencairan;
                    populateForm(originalData);
                    
                    // Load kegiatan budget info
                    if (originalData.anggaran_kegiatan) {
                        loadKegiatanBudget(originalData.anggaran_kegiatan.id);
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Pencairan dana tidak ditemukan');
                    setTimeout(() => window.location.href = '/pencairan-dana', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses untuk mengedit pencairan dana ini');
                    setTimeout(() => window.location.href = '/pencairan-dana', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data pencairan dana');
                }
            }
        });
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
                    sisaAnggaran = data.sisa_anggaran || data.anggaran_disetujui;
                    
                    $('#info_kode').text(data.kode_kegiatan);
                    $('#info_total_anggaran').text(formatRupiah(data.anggaran_disetujui));
                    $('#info_total_pencairan').text(formatRupiah(data.total_pencairan || 0));
                    $('#info_sisa_anggaran').text(formatRupiah(sisaAnggaran));
                }
            }
        });
    }

    // Populate form with existing data
    function populateForm(data) {
        $('#nomor_pencairan').val(data.nomor_pencairan);
        $('#jumlah_pencairan').val(formatNumber(data.jumlah_pencairan));
        $('#keperluan').val(data.keperluan);

        // Kegiatan info
        if (data.anggaran_kegiatan) {
            const kegiatan = data.anggaran_kegiatan;
            $('#kegiatan_display').val(`${kegiatan.kode_kegiatan} - ${kegiatan.nama_kegiatan}`);
        }

        // Show status info
        $('#statusInfo').show();
        $('#currentStatus').html(getStatusBadge(data.status));

        // Show rejection note if status is ditolak
        if (data.status === 'ditolak' && data.catatan) {
            $('#rejectionNote').show();
            $('#rejectionText').html(`<div class="alert alert-danger mb-0">${data.catatan}</div>`);
        }

        // Show history
        if (data.creator) {
            $('#historyCard').show();
            $('#createdBy').text(data.creator.name);
            $('#createdAt').text(formatDateTime(data.created_at));
            $('#updatedAt').text(formatDateTime(data.updated_at));
        }

        // Check if can edit
        if (data.status !== 'draft' && data.status !== 'ditolak') {
            showAlert('warning', 'Pencairan dana ini tidak dapat diedit karena sudah dalam proses persetujuan atau telah disetujui');
            $('input:not([readonly]), textarea').prop('readonly', true);
            $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        }
    }

    // Submit as draft
    $('#btnSubmit').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = false;
        submitForm();
    });

    // Submit and request approval
    $('#btnSubmitApproval').on('click', function(e) {
        e.preventDefault();
        submitAndApprove = true;
        
        if (confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan pencairan dana ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate
        const jumlahPencairan = parseFloat($('#jumlah_pencairan').val().replace(/[^0-9]/g, ''));
        const availableBudget = sisaAnggaran + originalJumlah;
        
        if (jumlahPencairan > availableBudget) {
            showAlert('error', 'Jumlah pencairan melebihi sisa anggaran');
            return;
        }

        // Disable submit buttons
        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $('#btnSubmitApproval').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            jumlah_pencairan: jumlahPencairan,
            keperluan: $('#keperluan').val()
        };

        // Update pencairan dana
        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}`,
            method: 'PUT',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    // If submit and approve, call submit endpoint
                    if (submitAndApprove) {
                        submitForApproval();
                    } else {
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.href = `/pencairan-dana/${pencairanId}`;
                        }, 1500);
                    }
                }
            },
            error: function(xhr) {
                // Enable submit buttons
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
                $('#btnSubmitApproval').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Simpan & Ajukan');

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

    function submitForApproval() {
        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Perubahan berhasil disimpan dan diajukan untuk persetujuan');
                    setTimeout(function() {
                        window.location.href = `/pencairan-dana/${pencairanId}`;
                    }, 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Perubahan berhasil disimpan, namun gagal diajukan';
                showAlert('warning', message);
                setTimeout(function() {
                    window.location.href = `/pencairan-dana/${pencairanId}`;
                }, 2000);
            }
        });
    }

    // Helper: Get status badge
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