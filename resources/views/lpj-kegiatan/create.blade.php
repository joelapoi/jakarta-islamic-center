@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat LPJ Kegiatan</h1>
        <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form LPJ Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('lpj-kegiatan.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="anggaran_kegiatan_id">Pilih Kegiatan <span class="text-danger">*</span></label>
                            <select name="anggaran_kegiatan_id" id="anggaran_kegiatan_id" class="form-control @error('anggaran_kegiatan_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kegiatan --</option>
                                @forelse($anggaranList as $anggaran)
                                    <option value="{{ $anggaran->id }}" 
                                            data-anggaran="{{ $anggaran->anggaran_disetujui }}"
                                            data-kode="{{ $anggaran->kode_kegiatan }}"
                                            data-periode="{{ \Carbon\Carbon::parse($anggaran->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($anggaran->tanggal_selesai)->format('d M Y') }}"
                                            {{ old('anggaran_kegiatan_id', $selectedAnggaran->id ?? '') == $anggaran->id ? 'selected' : '' }}>
                                        {{ $anggaran->kode_kegiatan }} - {{ $anggaran->nama_kegiatan }} 
                                        (Anggaran: Rp {{ number_format($anggaran->anggaran_disetujui, 0, ',', '.') }})
                                    </option>
                                @empty
                                    <option value="" disabled>Tidak ada kegiatan yang tersedia</option>
                                @endforelse
                            </select>
                            @error('anggaran_kegiatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($anggaranList->isEmpty())
                                <small class="form-text text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Tidak ada kegiatan yang disetujui atau semua kegiatan sudah memiliki LPJ
                                </small>
                            @endif
                        </div>

                        <div id="anggaranInfo" class="alert alert-info" style="display: none;">
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

                        <div class="form-group">
                            <label for="total_realisasi">Total Realisasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="total_realisasi" id="total_realisasi" 
                                       class="form-control @error('total_realisasi') is-invalid @enderror" 
                                       value="{{ old('total_realisasi') }}" 
                                       min="0" step="1" required>
                            </div>
                            @error('total_realisasi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan total realisasi penggunaan anggaran dalam Rupiah</small>
                        </div>

                        <div id="sisaAnggaranInfo" class="alert alert-success" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div><strong>Sisa Anggaran:</strong></div>
                                <div><h4 class="mb-0" id="sisa_anggaran_display">Rp 0</h4></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="laporan_kegiatan">Laporan Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="laporan_kegiatan" id="laporan_kegiatan" rows="10" 
                                      class="form-control @error('laporan_kegiatan') is-invalid @enderror" 
                                      placeholder="Jelaskan pelaksanaan kegiatan, hasil yang dicapai, dan pertanggungjawaban penggunaan dana secara detail..."
                                      required>{{ old('laporan_kegiatan') }}</textarea>
                            @error('laporan_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tuliskan laporan lengkap pertanggungjawaban kegiatan</small>
                        </div>

                        <hr>

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-info-circle"></i> Perhatian:</strong> 
                            LPJ akan langsung diajukan setelah disimpan dan menunggu persetujuan dari Kadiv Umum atau Kepala JIC.
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" {{ $anggaranList->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan LPJ
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
            <!-- Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Alur LPJ:</h6>
                    <ol class="pl-3">
                        <li>LPJ dibuat & langsung diajukan</li>
                        <li>Menunggu persetujuan</li>
                        <li>Disetujui/Ditolak</li>
                        <li>Jika ditolak, bisa diedit & diajukan kembali</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan Penting:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pilih kegiatan yang sudah selesai</li>
                        <li>Total realisasi tidak boleh melebihi anggaran yang disetujui</li>
                        <li>Laporan harus detail dan lengkap</li>
                        <li>LPJ langsung diajukan setelah disimpan</li>
                        <li>Jika ditolak, Anda dapat mengedit dan mengajukan kembali</li>
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
    let anggaranDisetujui = 0;

    // Show anggaran info when kegiatan is selected
    $('#anggaran_kegiatan_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const anggaran = selectedOption.data('anggaran');
        const kode = selectedOption.data('kode');
        const periode = selectedOption.data('periode');
        
        if (anggaran) {
            anggaranDisetujui = anggaran;
            $('#info_kode').text(kode);
            $('#info_periode').text(periode);
            $('#info_anggaran').text('Rp ' + new Intl.NumberFormat('id-ID').format(anggaran));
            $('#anggaranInfo').slideDown();
            calculateSisa();
        } else {
            $('#anggaranInfo').slideUp();
            $('#sisaAnggaranInfo').slideUp();
            anggaranDisetujui = 0;
        }
    });

    // Calculate sisa anggaran on realisasi input
    $('#total_realisasi').on('input', function() {
        calculateSisa();
    });

    function calculateSisa() {
        const realisasi = parseFloat($('#total_realisasi').val()) || 0;
        
        if (anggaranDisetujui > 0 && realisasi > 0) {
            const sisaAnggaran = anggaranDisetujui - realisasi;
            
            if (realisasi > anggaranDisetujui) {
                $('#total_realisasi').addClass('is-invalid');
                $('#sisaAnggaranInfo').removeClass('alert-success').addClass('alert-danger');
                $('#sisa_anggaran_display').text('Melebihi Anggaran!');
                $('#sisaAnggaranInfo').slideDown();
            } else {
                $('#total_realisasi').removeClass('is-invalid');
                $('#sisaAnggaranInfo').removeClass('alert-danger').addClass('alert-success');
                $('#sisa_anggaran_display').text('Rp ' + new Intl.NumberFormat('id-ID').format(sisaAnggaran));
                $('#sisaAnggaranInfo').slideDown();
            }
        } else {
            $('#sisaAnggaranInfo').slideUp();
        }
    }

    // Trigger change on page load if there's a selected value
    if ($('#anggaran_kegiatan_id').val()) {
        $('#anggaran_kegiatan_id').trigger('change');
    }

    // Form validation before submit
    $('form').on('submit', function(e) {
        const realisasi = parseFloat($('#total_realisasi').val()) || 0;
        
        if (realisasi > anggaranDisetujui) {
            e.preventDefault();
            alert('Total realisasi tidak boleh melebihi anggaran yang disetujui!');
            return false;
        }
        
        if (!$('#anggaran_kegiatan_id').val()) {
            e.preventDefault();
            alert('Silakan pilih kegiatan terlebih dahulu!');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });
});
</script>
@endpush