@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Anggaran Kegiatan</h1>
        <div>
            <a href="/anggaran-kegiatan/{{ $id }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form id="formAnggaranKegiatan">
                        <div class="form-group">
                            <label for="kode_kegiatan">Kode Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_kegiatan" name="kode_kegiatan" 
                                   placeholder="Contoh: KEG-2025-001" required readonly disabled>
                            <small class="form-text text-muted">Kode kegiatan otomatis dan tidak dapat diubah</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" 
                                   placeholder="Masukkan nama kegiatan" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Kegiatan</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Deskripsi singkat tentang kegiatan"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="anggaran_disetujui">Anggaran yang Diajukan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control" id="anggaran_disetujui" name="anggaran_disetujui" 
                                       placeholder="0" required>
                            </div>
                            <small class="form-text text-muted">Masukkan nominal anggaran yang dibutuhkan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
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
                            <a href="/anggaran-kegiatan/{{ $id }}" class="btn btn-secondary">
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
                        <li>Hanya anggaran dengan status <strong>Draft</strong> atau <strong>Ditolak</strong> yang dapat diedit</li>
                        <li>Setelah disetujui, data tidak dapat diubah</li>
                        <li>Jika ditolak, perbaiki sesuai catatan penolakan</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan semua data terisi dengan benar</li>
                        <li>Anggaran harus sesuai dengan proposal kegiatan</li>
                        <li>Tanggal selesai harus setelah tanggal mulai</li>
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
    const anggaranId = {{ $id }};
    let submitAndApprove = false;
    let originalData = null;

    // Load existing data
    loadAnggaranData();

    // Format currency input
    $('#anggaran_disetujui').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

    // Update tanggal_selesai min date when tanggal_mulai changes
    $('#tanggal_mulai').on('change', function() {
        const startDate = $(this).val();
        $('#tanggal_selesai').attr('min', startDate);
        
        // Reset tanggal_selesai if it's before the new start date
        const endDate = $('#tanggal_selesai').val();
        if (endDate && endDate < startDate) {
            $('#tanggal_selesai').val('');
        }
    });

    // Load anggaran data
    function loadAnggaranData() {
        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}`,
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
                    showAlert('error', 'Anggaran kegiatan tidak ditemukan');
                    setTimeout(() => window.location.href = '/anggaran-kegiatan', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses untuk mengedit anggaran kegiatan ini');
                    setTimeout(() => window.location.href = '/anggaran-kegiatan', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data anggaran kegiatan');
                }
            }
        });
    }

    // Populate form with existing data
    function populateForm(data) {
        $('#kode_kegiatan').val(data.kode_kegiatan);
        $('#nama_kegiatan').val(data.nama_kegiatan);
        $('#deskripsi').val(data.deskripsi);
        // Remove any non-digit characters first, then format
        const anggaranValue = String(data.anggaran_disetujui).replace(/[^0-9]/g, '');
        $('#anggaran_disetujui').val(formatNumber(anggaranValue));
        $('#tanggal_mulai').val(formatDateForInput(data.tanggal_mulai));
        $('#tanggal_selesai').val(formatDateForInput(data.tanggal_selesai));

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
            showAlert('warning', 'Anggaran kegiatan ini tidak dapat diedit karena sudah dalam proses persetujuan atau telah disetujui');
            $('input, textarea').prop('readonly', true);
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
        
        if (confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan anggaran kegiatan ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Disable submit buttons
        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $('#btnSubmitApproval').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            kode_kegiatan: $('#kode_kegiatan').val(),
            nama_kegiatan: $('#nama_kegiatan').val(),
            deskripsi: $('#deskripsi').val(),
            anggaran_disetujui: parseInt($('#anggaran_disetujui').val().replace(/[^0-9]/g, '')) || 0,
            tanggal_mulai: $('#tanggal_mulai').val(),
            tanggal_selesai: $('#tanggal_selesai').val()
        };

        // Update anggaran kegiatan
        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}`,
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
                            window.location.href = `/anggaran-kegiatan/${anggaranId}`;
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
            url: `/api/anggaran-kegiatan/${anggaranId}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Perubahan berhasil disimpan dan diajukan untuk persetujuan');
                    setTimeout(function() {
                        window.location.href = `/anggaran-kegiatan/${anggaranId}`;
                    }, 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Perubahan berhasil disimpan, namun gagal diajukan';
                showAlert('warning', message);
                setTimeout(function() {
                    window.location.href = `/anggaran-kegiatan/${anggaranId}`;
                }, 2000);
            }
        });
    }

    // Helper: Get status badge
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui_kadiv': '<span class="badge badge-primary">Disetujui Kadiv</span>',
            'disetujui_kadiv_umum': '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
            'disetujui_kepala_jic': '<span class="badge badge-success">Disetujui Kepala JIC</span>',
            'ditolak': '<span class="badge badge-danger">Ditolak</span>'
        };
        return badges[status] || status;
    }

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format date for input[type="date"] (YYYY-MM-DD format)
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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