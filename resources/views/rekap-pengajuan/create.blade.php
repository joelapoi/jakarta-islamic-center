@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Rekap Pengajuan</h1>
        <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    <form id="formRekapPengajuan">
                        <div class="form-group">
                            <label for="pencairan_dana_id">Pencairan Dana <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" id="pencairan_dana_id" name="pencairan_dana_id">
                                <input type="text" class="form-control" id="pencairan_display" readonly 
                                       placeholder="Pilih pencairan dana">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="btnPilihPencairan">
                                        <i class="fas fa-search"></i> Pilih
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="pencairanInfo" style="display: none;">
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
                                    <tr>
                                        <td>Tanggal Dicairkan</td>
                                        <td>:</td>
                                        <td id="info_tanggal">-</td>
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

                        <div id="sisaDanaInfo" style="display: none;">
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

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
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
                        <li>Disetujui Kadiv Umum / Kepala JIC</li>
                        <li>Proses Buku Cek</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pilih pencairan dana yang sudah dicairkan</li>
                        <li>Total pengeluaran tidak boleh melebihi jumlah pencairan</li>
                        <li>Jelaskan rincian pengeluaran dengan detail</li>
                        <li>Sisa dana akan dihitung otomatis</li>
                        <li>Setelah diajukan, data tidak dapat diubah kecuali ditolak</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Pencairan -->
<div class="modal fade" id="pencairanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pencairan Dana</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchPencairan" placeholder="Cari pencairan...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nomor</th>
                                <th>Kegiatan</th>
                                <th>Jumlah</th>
                                <th>Tanggal Cair</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pencairanList">
                            <tr>
                                <td colspan="5" class="text-center">
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
    let selectedPencairan = null;
    let jumlahPencairan = 0;

    // Check if pencairan_id from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const pencairanIdParam = urlParams.get('pencairan_id');
    
    if (pencairanIdParam) {
        loadPencairanDetail(pencairanIdParam);
    }

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
                $('#sisaDanaInfo').hide();
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
                $('#sisa_dana_display').text(formatRupiah(sisaDana));
                $('#sisaDanaInfo').show();
            }
        }
    });

    // Show/hide submit for approval button
    $('#formRekapPengajuan input, #formRekapPengajuan textarea').on('input', function() {
        if (isFormValid()) {
            $('#btnSubmitApproval').show();
        } else {
            $('#btnSubmitApproval').hide();
        }
    });

    // Show pencairan modal
    $('#btnPilihPencairan').on('click', function() {
        loadPencairanList();
        $('#pencairanModal').modal('show');
    });

    // Search pencairan
    let searchPencairanTimeout;
    $('#searchPencairan').on('keyup', function() {
        clearTimeout(searchPencairanTimeout);
        searchPencairanTimeout = setTimeout(function() {
            loadPencairanList();
        }, 500);
    });

    // Load pencairan list (only dicairkan status)
    function loadPencairanList() {
        const search = $('#searchPencairan').val();

        $.ajax({
            url: '/api/pencairan-dana',
            method: 'GET',
            data: {
                status: 'dicairkan',
                search: search,
                per_page: 100
            },
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderPencairanList(response.data.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading pencairan:', xhr);
                $('#pencairanList').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data pencairan</td></tr>');
            }
        });
    }

    // Render pencairan list
    function renderPencairanList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Tidak ada pencairan dana yang sudah dicairkan</td></tr>';
        } else {
            data.forEach(function(item) {
                const kegiatanNama = item.anggaran_kegiatan ? item.anggaran_kegiatan.nama_kegiatan : '-';
                const tanggalCair = item.disbursed_at ? formatDate(item.disbursed_at) : '-';
                
                html += `
                    <tr>
                        <td>${item.nomor_pencairan}</td>
                        <td>${kegiatanNama}</td>
                        <td>${formatRupiah(item.jumlah_pencairan)}</td>
                        <td>${tanggalCair}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-pencairan" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#pencairanList').html(html);
    }

    // Select pencairan
    $(document).on('click', '.btn-select-pencairan', function() {
        const pencairanId = $(this).data('id');
        loadPencairanDetail(pencairanId);
        $('#pencairanModal').modal('hide');
    });

    // Load pencairan detail
    function loadPencairanDetail(pencairanId) {
        $.ajax({
            url: `/api/pencairan-dana/${pencairanId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    selectedPencairan = response.data;
                    jumlahPencairan = response.data.jumlah_pencairan;
                    
                    $('#pencairan_dana_id').val(selectedPencairan.id);
                    $('#pencairan_display').val(`${selectedPencairan.nomor_pencairan}`);
                    
                    $('#info_nomor').text(selectedPencairan.nomor_pencairan);
                    
                    if (selectedPencairan.anggaran_kegiatan) {
                        $('#info_kegiatan').text(selectedPencairan.anggaran_kegiatan.nama_kegiatan);
                    }
                    
                    $('#info_jumlah').text(formatRupiah(selectedPencairan.jumlah_pencairan));
                    $('#info_tanggal').text(formatDateTime(selectedPencairan.disbursed_at));
                    
                    $('#pencairanInfo').show();
                }
            },
            error: function(xhr) {
                showAlert('error', 'Gagal memuat detail pencairan dana');
            }
        });
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
        
        if (confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan rekap pengajuan ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate
        if (!$('#pencairan_dana_id').val()) {
            showAlert('error', 'Silakan pilih pencairan dana terlebih dahulu');
            return;
        }

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
            pencairan_dana_id: parseInt($('#pencairan_dana_id').val()),
            total_pengeluaran: totalPengeluaran,
            catatan: $('#catatan').val()
        };

        // Create rekap pengajuan
        $.ajax({
            url: '/api/rekap-pengajuan',
            method: 'POST',
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
                        submitForApproval(response.data.id);
                    } else {
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.href = '/rekap-pengajuan';
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
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan rekap pengajuan';
                    showAlert('error', message);
                }
            }
        });
    }

    function submitForApproval(id) {
        $.ajax({
            url: `/api/rekap-pengajuan/${id}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rekap pengajuan berhasil disimpan dan diajukan untuk persetujuan');
                    setTimeout(function() {
                        window.location.href = '/rekap-pengajuan';
                    }, 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Rekap pengajuan berhasil disimpan, namun gagal diajukan';
                showAlert('warning', message);
                setTimeout(function() {
                    window.location.href = '/rekap-pengajuan';
                }, 2000);
            }
        });
    }

    function isFormValid() {
        const pencairanId = $('#pencairan_dana_id').val();
        const pengeluaran = $('#total_pengeluaran').val().replace(/[^0-9]/g, '');

        return pencairanId && pengeluaran && parseFloat(pengeluaran) > 0 && parseFloat(pengeluaran) <= jumlahPencairan;
    }

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Helper: Format Date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric' 
        });
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