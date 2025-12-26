@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Rekap Pengajuan</h1>
        <div>
            <a href="{{ route('rekap-pengajuan.show', $rekap->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('rekap-pengajuan.update', $rekap->id) }}" method="POST" id="formRekapPengajuan">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Nomor Rekap</label>
                            <input type="text" class="form-control" value="{{ $rekap->nomor_rekap }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Pencairan Dana</label>
                            <input type="text" class="form-control" value="{{ $rekap->pencairanDana->nomor_pencairan }}" readonly>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Informasi Pencairan Dana:</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="40%">Nomor Pencairan</td>
                                    <td width="5%">:</td>
                                    <td>{{ $rekap->pencairanDana->nomor_pencairan }}</td>
                                </tr>
                                <tr>
                                    <td>Kegiatan</td>
                                    <td>:</td>
                                    <td>{{ $rekap->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Jumlah Pencairan</td>
                                    <td>:</td>
                                    <td class="font-weight-bold text-success">
                                        Rp {{ number_format($rekap->pencairanDana->jumlah_pencairan, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="form-group">
                            <label for="total_pengeluaran">Total Pengeluaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" 
                                       class="form-control @error('total_pengeluaran') is-invalid @enderror" 
                                       id="total_pengeluaran" 
                                       name="total_pengeluaran" 
                                       placeholder="0"
                                       value="{{ old('total_pengeluaran', number_format($rekap->total_pengeluaran, 0, '', '.')) }}"
                                       required>
                            </div>
                            <small class="form-text text-muted">Total pengeluaran dari dana yang telah dicairkan</small>
                            @error('total_pengeluaran')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="sisaDanaInfo">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Sisa Dana:</strong>
                                    </div>
                                    <div>
                                        <h4 class="mb-0" id="sisa_dana_display">
                                            Rp {{ number_format($rekap->sisa_dana, 0, ',', '.') }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="catatan">Catatan / Keterangan</label>
                            <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                      id="catatan" 
                                      name="catatan" 
                                      rows="5" 
                                      placeholder="Jelaskan rincian pengeluaran dan penggunaan dana...">{{ old('catatan', $rekap->catatan) }}</textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Status:</strong> 
                            @if($rekap->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @elseif($rekap->status === 'diajukan')
                                <span class="badge badge-info">Diajukan</span>
                            @elseif($rekap->status === 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($rekap->status === 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif

                            @if($rekap->status === 'ditolak' && $rekap->catatan)
                                <div class="mt-2">
                                    <strong>Catatan Penolakan:</strong>
                                    <div class="alert alert-danger mt-1 mb-0">
                                        {{ $rekap->catatan }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" name="submit_type" value="draft" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="submit" name="submit_type" value="submit" class="btn btn-success" id="btnSubmitApproval">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('rekap-pengajuan.show', $rekap->id) }}" class="btn btn-secondary">
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Riwayat
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td>{{ \Carbon\Carbon::parse($rekap->created_at)->isoFormat('DD MMM YYYY, HH:mm') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Update Terakhir:</td>
                            <td>{{ \Carbon\Carbon::parse($rekap->updated_at)->isoFormat('DD MMM YYYY, HH:mm') }}</td>
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
    const jumlahPencairan = {{ $rekap->pencairanDana->jumlah_pencairan }};

    // Format currency input
    $('#total_pengeluaran').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Calculate and display sisa dana
        if (jumlahPencairan > 0 && value) {
            const pengeluaran = parseFloat(value) || 0;
            const sisaDana = jumlahPencairan - pengeluaran;
            
            if (pengeluaran > jumlahPencairan) {
                $(this).addClass('is-invalid');
                showValidationMessage('Total pengeluaran melebihi jumlah pencairan (Rp ' + formatNumber(jumlahPencairan) + ')');
                $('#sisaDanaInfo').hide();
            } else {
                $(this).removeClass('is-invalid');
                hideValidationMessage();
                $('#sisa_dana_display').text(formatRupiah(sisaDana));
                $('#sisaDanaInfo').show();
            }
        }
    });

    // Trigger calculation on page load
    @if(old('total_pengeluaran'))
        $('#total_pengeluaran').trigger('keyup');
    @endif

    // Confirm before submit with approval
    $('#formRekapPengajuan').on('submit', function(e) {
        const submitType = $('button[type="submit"]:focus').val();
        
        if (submitType === 'submit') {
            if (!confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan rekap pengajuan ini untuk disetujui?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Show validation message
    function showValidationMessage(message) {
        let feedback = $('#total_pengeluaran').siblings('.invalid-feedback');
        if (feedback.length === 0) {
            $('#total_pengeluaran').after('<div class="invalid-feedback d-block">' + message + '</div>');
        } else {
            feedback.text(message).addClass('d-block');
        }
    }

    // Hide validation message
    function hideValidationMessage() {
        $('#total_pengeluaran').siblings('.invalid-feedback').removeClass('d-block');
    }

    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush