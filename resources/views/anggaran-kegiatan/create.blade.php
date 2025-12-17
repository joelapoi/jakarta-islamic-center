@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Anggaran Kegiatan</h1>
        <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form id="formAnggaranKegiatan">
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

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
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
                    <h6 class="font-weight-bold">Alur Persetujuan:</h6>
                    <ol class="pl-3">
                        <li>Draft dibuat</li>
                        <li>Diajukan untuk persetujuan</li>
                        <li>Disetujui Kadiv</li>
                        <li>Disetujui Kadiv Umum</li>
                        <li>Disetujui Kepala JIC</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan semua data terisi dengan benar</li>
                        <li>Anggaran harus sesuai dengan proposal kegiatan</li>
                        <li>Tanggal selesai harus setelah tanggal mulai</li>
                        <li>Setelah diajukan, data tidak dapat diubah kecuali ditolak</li>
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
    let submitAndApprove = false;

    // Format currency input
    $('#anggaran_disetujui').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

    // Set minimum date for tanggal_mulai to today
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_mulai').attr('min', today);

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

    // Show/hide submit for approval button
    $('#formAnggaranKegiatan input, #formAnggaranKegiatan textarea').on('input', function() {
        if (isFormValid()) {
            $('#btnSubmitApproval').show();
        } else {
            $('#btnSubmitApproval').hide();
        }
    });

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
        
        if (confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan anggaran kegiatan ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Disable submit buttons
        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            nama_kegiatan: $('#nama_kegiatan').val(),
            deskripsi: $('#deskripsi').val(),
            anggaran_disetujui: parseFloat($('#anggaran_disetujui').val().replace(/[^0-9]/g, '')),
            tanggal_mulai: $('#tanggal_mulai').val(),
            tanggal_selesai: $('#tanggal_selesai').val()
        };

        // Create anggaran kegiatan
        $.ajax({
            url: '/api/anggaran-kegiatan',
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
                //'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    // If submit and approve, call submit endpoint
                    if (submitAndApprove) {
                        submitForApproval(response.data.id);
                    } else {
                        showAlert('success', 'Anggaran kegiatan berhasil disimpan sebagai draft');
                        setTimeout(function() {
                            window.location.href = '/anggaran-kegiatan';
                        }, 1500);
                    }
                }
            },
            error: function(xhr) {
                // Enable submit buttons
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan sebagai Draft');
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
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan anggaran kegiatan';
                    showAlert('error', message);
                }
            }
        });
    }

    function submitForApproval(id) {
        $.ajax({
            url: `/api/anggaran-kegiatan/${id}/submit`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Anggaran kegiatan berhasil disimpan dan diajukan');
                    setTimeout(function() {
                        window.location.href = '/anggaran-kegiatan';
                    }, 1500);
                }
            },
            error: function(xhr) {
                showAlert('success', 'Anggaran kegiatan berhasil disimpan sebagai draft');
                setTimeout(function() {
                    window.location.href = '/anggaran-kegiatan';
                }, 1500);
            }
        });
    }

    function isFormValid() {
        const namaKegiatan = $('#nama_kegiatan').val().trim();
        const anggaran = $('#anggaran_disetujui').val().replace(/[^0-9]/g, '');
        const tanggalMulai = $('#tanggal_mulai').val();
        const tanggalSelesai = $('#tanggal_selesai').val();

        return namaKegiatan && anggaran && tanggalMulai && tanggalSelesai;
    }

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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