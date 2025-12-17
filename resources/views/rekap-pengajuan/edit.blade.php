@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Rekap Pengajuan</h1>
        <div>
            <a href="/rekap-pengajuan/{{ $id }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('view.rekap-pengajuan.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    <form id="formRekapPengajuan">
                        <div class="form-group">
                            <label>Nomor Rekap</label>
                            <input type="text" class="form-control" id="nomor_rekap" readonly>
                        </div>

                        <div class="form-group">
                            <label>Pencairan Dana</label>
                            <input type="text" class="form-control" id="pencairan_display" readonly>
                        </div>

                        <div id="pencairanInfo">
                            <div class="alert alert-info">
                                <h6 class="font-weight-bold mb-2">Informasi Pencairan Dana:</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Nomor Pencairan</td>
                                        <td width="5%">:</td>
                                        <td id="info_nomor">-</td>
                                    </tr>
                                    <tr>
                                        <td>Kegiatan</td>
                                        <td>:</td>
                                        <td id="info_kegiatan">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Jumlah Pencairan</td>
                                        <td>:</td>
                                        <td class="font-weight-bold text-success" id="info_jumlah">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="total_pengeluaran">Total Pengeluaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control" id="total_pengeluaran" name="total_pengeluaran" 
                                       placeholder="0" required>
                            </div>
                            <small class="form-text text-muted">Total pengeluaran dari dana yang telah dicairkan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="sisaDanaInfo">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Sisa Dana:</strong>
                                    </div>
                                    <div>
                                        <h4 class="mb-0" id="sisa_dana_display">Rp 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="catatan">Catatan / Keterangan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="5" 
                                      placeholder="Jelaskan rincian pengeluaran dan penggunaan dana..."></textarea>
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
                            <a href="/rekap-pengajuan/{{ $id }}" class="btn btn-secondary">
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
                        <li>Hanya rekap dengan status <strong>Draft</strong> atau <strong>Ditolak</strong> yang dapat diedit</li>
                        <li>Setelah disetujui, data tidak dapat diubah</li>
                        <li>Jika ditolak, perbaiki sesuai catatan penolakan</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan semua data terisi dengan benar</li>
                        <li>Total pengeluaran tidak boleh melebihi jumlah pencairan</li>
                        <li>Jelaskan rincian pengeluaran dengan detail</li>
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
    const rekapId = {{ $id }};
    let submitAndApprove = false;
    let originalData = null;
    let jumlahPencairan = 0;

    // Load existing data
    loadRekapData();

    // Format currency input
    $('#total_pengeluaran').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Calculate and display sisa dana
        if (jumlahPencairan > 0) {
            const pengeluaran = parseFloat(value) || 0;
            const sisaDana = jumlahPencairan - pengeluaran;
            
            if (pengeluaran > jumlahPencairan) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text(`Total pengeluaran melebihi jumlah pencairan (${formatRupiah(jumlahPencairan)})`);
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
            }
            
            $('#sisa_dana_display').text(formatRupiah(sisaDana));
        }
    });

    // Load rekap data
    function loadRekapData() {
        $.ajax({
            url: `/api/rekap-pengajuan/${rekapId}`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: function(response) {
                if (response.success) {
                    originalData = response.data;
                    populateForm(originalData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Rekap pengajuan tidak ditemukan');
                    setTimeout(() => window.location.href = '/rekap-pengajuan', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses untuk mengedit rekap pengajuan ini');
                    setTimeout(() => window.location.href = '/rekap-pengajuan', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data rekap pengajuan');
                }
            }
        });
    }

    // Populate form with existing data
    function populateForm(data) {
        $('#nomor_rekap').val(data.nomor_rekap);
        $('#total_pengeluaran').val(formatNumber(data.total_pengeluaran));
        $('#catatan').val(data.catatan);

        // Calculate initial sisa dana
        if (data.pencairan_dana) {
            jumlahPencairan = data.pencairan_dana.jumlah_pencairan;
            const sisaDana = jumlahPencairan - data.total_pengeluaran;
            $('#sisa_dana_display').text(formatRupiah(sisaDana));

            // Pencairan info
            $('#pencairan_display').val(data.pencairan_dana.nomor_pencairan);
            $('#info_nomor').text(data.pencairan_dana.nomor_pencairan);
            $('#info_jumlah').text(formatRupiah(data.pencairan_dana.jumlah_pencairan));

            if (data.pencairan_dana.anggaran_kegiatan) {
                $('#info_kegiatan').text(data.pencairan_dana.anggaran_kegiatan.nama_kegiatan);
            }
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
        $('#historyCard').show();
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#updatedAt').text(formatDateTime(data.updated_at));

        // Check if can edit
        if (data.status !== 'draft' && data.status !== 'ditolak') {
            showAlert('warning', 'Rekap pengajuan ini tidak dapat diedit karena sudah dalam proses persetujuan atau telah disetujui');
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
        
        if (confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan rekap pengajuan ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate
        const totalPengeluaran = parseFloat($('#total_pengeluaran').val().replace(/[^0-9]/g, ''));
        
        if (totalPengeluaran > jumlahPencairan) {
            showAlert('error', 'Total pengeluaran melebihi jumlah pencairan');
            return;
        }

        if (totalPengeluaran <= 0) {
            showAlert('error', 'Total pengeluaran harus lebih dari 0');
            return;
        }

        // Disable submit buttons
        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $('#btnSubmitApproval').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            total_pengeluaran: totalPengeluaran,
            catatan: $('#catatan').val()
        };

        // Update rekap pengajuan
        $.ajax({
            url: `/api/rekap-pengajuan/${rekapId}`,
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
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
                            window.location.href = `/rekap-pengajuan/${rekapId}`;
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
            url: `/api/rekap-pengajuan/${rekapId}/submit`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Perubahan berhasil disimpan dan diajukan untuk persetujuan');
                    setTimeout(function() {
                        window.location.href = `/rekap-pengajuan/${rekapId}`;
                    }, 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Perubahan berhasil disimpan, namun gagal diajukan';
                showAlert('warning', message);
                setTimeout(function() {
                    window.location.href = `/rekap-pengajuan/${rekapId}`;
                }, 2000);
            }
        });
    }

    // Helper: Get status badge
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui': '<span class="badge badge-success">Disetujui</span>',
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