@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Buku Cek</h1>
        <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Buku Cek</h6>
                </div>
                <div class="card-body">
                    <form id="formBukuCek">
                        <div class="form-group">
                            <label for="rekap_pengajuan_id">Rekap Pengajuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" id="rekap_pengajuan_id" name="rekap_pengajuan_id">
                                <input type="text" class="form-control" id="rekap_display" readonly 
                                       placeholder="Pilih rekap pengajuan">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="btnPilihRekap">
                                        <i class="fas fa-search"></i> Pilih
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="rekapInfo" style="display: none;">
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
                                        <td>Total Pengeluaran</td>
                                        <td>:</td>
                                        <td id="info_pengeluaran">-</td>
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
                                       placeholder="0" required readonly disabled style="cursor: not-allowed; background-color: #e9ecef;">
                            </div>
                            <small class="form-text text-muted">Jumlah akan otomatis terisi dari sisa dana rekap pengajuan</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="keperluan">Keperluan</label>
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="4" 
                                      placeholder="Jelaskan keperluan pencairan dana..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan & Ajukan TTD
                            </button>
                            <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
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
                    <h6 class="font-weight-bold">Alur Buku Cek:</h6>
                    <ol class="pl-3">
                        <li>Draft dibuat</li>
                        <li>Menunggu Tanda Tangan Kepala JIC</li>
                        <li>Ditandatangani</li>
                        <li>Dikonfirmasi Bank</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pilih rekap pengajuan yang sudah disetujui</li>
                        <li>Pastikan data bank benar</li>
                        <li>Jumlah dana otomatis dari sisa dana</li>
                        <li>Setelah dibuat, akan menunggu tanda tangan Kepala JIC</li>
                        <li>Setelah ditandatangani, akan dikonfirmasi ke bank</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Rekap -->
<div class="modal fade" id="rekapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Rekap Pengajuan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchRekap" placeholder="Cari rekap...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nomor Rekap</th>
                                <th>Kegiatan</th>
                                <th>Sisa Dana</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapList">
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
    let selectedRekap = null;
    let sisaDana = 0;

    // Check if rekap_id from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const rekapIdParam = urlParams.get('rekap_id');
    
    if (rekapIdParam) {
        loadRekapDetail(rekapIdParam);
    }

    // Format currency input (readonly, for display only)
    $('#jumlah').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

    // Prevent manual input on disabled field
    $('#jumlah').on('focus', function() {
        $(this).blur();
    });

    // Show rekap modal
    $('#btnPilihRekap').on('click', function() {
        loadRekapList();
        $('#rekapModal').modal('show');
    });

    // Search rekap
    let searchRekapTimeout;
    $('#searchRekap').on('keyup', function() {
        clearTimeout(searchRekapTimeout);
        searchRekapTimeout = setTimeout(function() {
            loadRekapList();
        }, 500);
    });

    // Load rekap list (only disetujui status without buku cek)
    function loadRekapList() {
        const search = $('#searchRekap').val();

        $.ajax({
            url: '/api/rekap-pengajuan',
            method: 'GET',
            data: {
                status: 'disetujui',
                search: search,
                per_page: 100
            },
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    // Filter only rekap without buku cek
                    const filteredData = response.data.data.filter(item => !item.buku_cek);
                    renderRekapList(filteredData);
                }
            },
            error: function(xhr) {
                console.error('Error loading rekap:', xhr);
                $('#rekapList').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data rekap</td></tr>');
            }
        });
    }

    // Render rekap list
    function renderRekapList(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="4" class="text-center">Tidak ada rekap pengajuan yang disetujui</td></tr>';
        } else {
            data.forEach(function(item) {
                const kegiatanNama = item.pencairan_dana?.anggaran_kegiatan?.nama_kegiatan || '-';
                
                html += `
                    <tr>
                        <td>${item.nomor_rekap}</td>
                        <td>${kegiatanNama}</td>
                        <td class="text-success font-weight-bold">${formatRupiah(item.sisa_dana)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-select-rekap" 
                                    data-id="${item.id}">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#rekapList').html(html);
    }

    // Select rekap
    $(document).on('click', '.btn-select-rekap', function() {
        const rekapId = $(this).data('id');
        loadRekapDetail(rekapId);
        $('#rekapModal').modal('hide');
    });

    // Load rekap detail
    function loadRekapDetail(rekapId) {
        $.ajax({
            url: `/api/rekap-pengajuan/${rekapId}`,
            method: 'GET',
            headers: {
                // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    selectedRekap = response.data;
                    sisaDana = response.data.sisa_dana;
                    
                    $('#rekap_pengajuan_id').val(selectedRekap.id);
                    $('#rekap_display').val(`${selectedRekap.nomor_rekap}`);
                    
                    $('#info_nomor').text(selectedRekap.nomor_rekap);
                    $('#info_pengeluaran').text(formatRupiah(selectedRekap.total_pengeluaran));
                    $('#info_sisa').text(formatRupiah(selectedRekap.sisa_dana));
                    
                    // Auto fill jumlah with sisa dana
                    $('#jumlah').val(formatNumber(selectedRekap.sisa_dana));
                    
                    if (selectedRekap.pencairan_dana?.anggaran_kegiatan) {
                        $('#info_kegiatan').text(selectedRekap.pencairan_dana.anggaran_kegiatan.nama_kegiatan);
                    }
                    
                    $('#rekapInfo').show();
                }
            },
            error: function(xhr) {
                showAlert('error', 'Gagal memuat detail rekap pengajuan');
            }
        });
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
        if (!$('#rekap_pengajuan_id').val()) {
            showAlert('error', 'Silakan pilih rekap pengajuan terlebih dahulu');
            return;
        }

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
            rekap_pengajuan_id: parseInt($('#rekap_pengajuan_id').val()),
            nama_bank: $('#nama_bank').val().trim(),
            nomor_rekening: $('#nomor_rekening').val().trim(),
            nama_penerima: $('#nama_penerima').val().trim(),
            jumlah: parseFloat($('#jumlah').val().replace(/[^0-9]/g, '') || 0),
            keperluan: $('#keperluan').val().trim()
        };

        // Create buku cek
        $.ajax({
            url: '/api/buku-cek',
            method: 'POST',
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
                        window.location.href = '/buku-cek';
                    }, 1500);
                }
            },
            error: function(xhr) {
                // Enable submit button
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Ajukan TTD');

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                    showAlert('error', 'Terdapat kesalahan pada form. Silakan periksa kembali.');
                } else {
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan buku cek';
                    showAlert('error', message);
                }
            }
        });
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