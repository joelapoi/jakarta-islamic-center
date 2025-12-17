@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengajuan Pencairan Dana</h1>
        <a href="{{ route('pencairan-dana.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Pengajuan Pencairan Dana</h6>
                </div>
                <div class="card-body">
                    <form id="formPencairanDana">
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
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="kegiatanInfo" style="display: none;">
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

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            <button type="button" class="btn btn-success" id="btnSubmitApproval" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('pencairan-dana.index') }}" class="btn btn-secondary">
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
                        <li>Disetujui Kadiv Umum</li>
                        <li>Disetujui Kepala JIC</li>
                        <li>Dana dicairkan</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan kegiatan sudah disetujui</li>
                        <li>Jumlah pencairan tidak boleh melebihi sisa anggaran</li>
                        <li>Jelaskan keperluan dengan detail</li>
                        <li>Setelah diajukan, data tidak dapat diubah kecuali ditolak</li>
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
                                <th>Sisa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kegiatanList">
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
    let selectedKegiatan = null;
    let sisaAnggaran = 0;

    // Check if anggaran_id from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const anggaranIdParam = urlParams.get('anggaran_id');
    
    if (anggaranIdParam) {
        // Check token before attempting to load
        const token = getToken();
        if (!token) {
            showAlert('error', 'Anda belum login atau session sudah kadaluarsa. Silakan login kembali.');
            setTimeout(() => window.location.href = '/login', 2000);
        } else {
            loadKegiatanDetail(anggaranIdParam);
        }
    }

    // Format currency input
    $('#jumlah_pencairan').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Check if exceeds sisa anggaran
        const jumlah = parseFloat(value);
        if (jumlah > sisaAnggaran) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text(`Jumlah melebihi sisa anggaran (${formatRupiah(sisaAnggaran)})`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        }
    });

    // Show/hide submit for approval button
    $('#formPencairanDana input, #formPencairanDana textarea').on('input', function() {
        if (isFormValid()) {
            $('#btnSubmitApproval').show();
        } else {
            $('#btnSubmitApproval').hide();
        }
    });

    // Show kegiatan modal
    $('#btnPilihKegiatan').on('click', function() {
        loadKegiatanList();
        $('#kegiatanModal').modal('show');
    });

    // Search kegiatan
    let searchKegiatanTimeout;
    $('#searchKegiatan').on('keyup', function() {
        clearTimeout(searchKegiatanTimeout);
        searchKegiatanTimeout = setTimeout(function() {
            loadKegiatanList();
        }, 500);
    });

    // Load kegiatan list
    function loadKegiatanList() {
        const search = $('#searchKegiatan').val();
        const token = getToken();

        // Check if token exists
        if (!token) {
            showAlert('error', 'Anda belum login atau session sudah kadaluarsa. Silakan login kembali.');
            setTimeout(() => window.location.href = '/login', 2000);
            return;
        }

        $.ajax({
            url: '/api/anggaran-kegiatan',
            method: 'GET',
            data: {
                status: 'disetujui_kepala_jic',
                search: search,
                per_page: 100
            },
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    renderKegiatanList(response.data.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading kegiatan:', xhr);
                
                if (xhr.status === 401) {
                    showAlert('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                    setTimeout(() => window.location.href = '/login', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses ke data ini');
                } else {
                    showAlert('error', 'Gagal memuat data kegiatan');
                }
                
                $('#kegiatanList').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data kegiatan</td></tr>');
            }
        });
    }

    // Render kegiatan list
    function renderKegiatanList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Tidak ada kegiatan yang disetujui</td></tr>';
        } else {
            data.forEach(function(item) {
                const sisaAnggaran = item.sisa_anggaran || item.anggaran_disetujui;
                
                html += `
                    <tr>
                        <td>${item.kode_kegiatan}</td>
                        <td>${item.nama_kegiatan}</td>
                        <td>${formatRupiah(item.anggaran_disetujui)}</td>
                        <td class="font-weight-bold text-success">${formatRupiah(sisaAnggaran)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-kegiatan" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#kegiatanList').html(html);
    }

    // Select kegiatan
    $(document).on('click', '.btn-select-kegiatan', function() {
        const kegiatanId = $(this).data('id');
        loadKegiatanDetail(kegiatanId);
        $('#kegiatanModal').modal('hide');
    });

    // Load kegiatan detail
    function loadKegiatanDetail(kegiatanId) {
        const token = getToken();

        // Check if token exists
        if (!token) {
            showAlert('error', 'Anda belum login atau session sudah kadaluarsa. Silakan login kembali.');
            setTimeout(() => window.location.href = '/login', 2000);
            return;
        }

        $.ajax({
            url: `/api/anggaran-kegiatan/${kegiatanId}`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    selectedKegiatan = response.data;
                    sisaAnggaran = response.data.sisa_anggaran || response.data.anggaran_disetujui;
                    
                    $('#anggaran_kegiatan_id').val(selectedKegiatan.id);
                    $('#kegiatan_display').val(`${selectedKegiatan.kode_kegiatan} - ${selectedKegiatan.nama_kegiatan}`);
                    
                    $('#info_kode').text(selectedKegiatan.kode_kegiatan);
                    $('#info_total_anggaran').text(formatRupiah(selectedKegiatan.anggaran_disetujui));
                    $('#info_total_pencairan').text(formatRupiah(response.data.total_pencairan || 0));
                    $('#info_sisa_anggaran').text(formatRupiah(sisaAnggaran));
                    
                    $('#kegiatanInfo').show();
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    showAlert('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                    setTimeout(() => window.location.href = '/login', 2000);
                } else if (xhr.status === 403) {
                    showAlert('error', 'Anda tidak memiliki akses ke data ini');
                } else {
                    showAlert('error', 'Gagal memuat detail kegiatan');
                }
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
        
        if (confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan pencairan dana ini untuk disetujui?')) {
            submitForm();
        }
    });

    function submitForm() {
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate
        if (!$('#anggaran_kegiatan_id').val()) {
            showAlert('error', 'Silakan pilih kegiatan terlebih dahulu');
            return;
        }

        const jumlahPencairan = parseFloat($('#jumlah_pencairan').val().replace(/[^0-9]/g, ''));
        if (jumlahPencairan > sisaAnggaran) {
            showAlert('error', 'Jumlah pencairan melebihi sisa anggaran');
            return;
        }

        // Disable submit buttons
        $('#btnSubmit, #btnSubmitApproval').prop('disabled', true);
        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $('#btnSubmitApproval').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare data
        const formData = {
            anggaran_kegiatan_id: parseInt($('#anggaran_kegiatan_id').val()),
            jumlah_pencairan: jumlahPencairan,
            keperluan: $('#keperluan').val()
        };

        // Create pencairan dana
        $.ajax({
            url: '/api/pencairan-dana',
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
                            window.location.href = '/pencairan-dana';
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
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan pencairan dana';
                    showAlert('error', message);
                }
            }
        });
    }

    function submitForApproval(id) {
        $.ajax({
            url: `/api/pencairan-dana/${id}/submit`,
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Pencairan dana berhasil disimpan dan diajukan untuk persetujuan');
                    setTimeout(function() {
                        window.location.href = '/pencairan-dana';
                    }, 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Pencairan dana berhasil disimpan, namun gagal diajukan';
                showAlert('warning', message);
                setTimeout(function() {
                    window.location.href = '/pencairan-dana';
                }, 2000);
            }
        });
    }

    function isFormValid() {
        const kegiatanId = $('#anggaran_kegiatan_id').val();
        const jumlah = $('#jumlah_pencairan').val().replace(/[^0-9]/g, '');
        const keperluan = $('#keperluan').val().trim();

        return kegiatanId && jumlah && keperluan && parseFloat(jumlah) <= sisaAnggaran;
    }

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
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