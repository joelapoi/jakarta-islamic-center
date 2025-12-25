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
                    <h6 class="m-0 font-weight-bold text-primary">Form Pengajuan Pencairan Dana</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pencairan-dana.store') }}" method="POST" id="formPencairanDana">
                        @csrf

                        <div class="form-group">
                            <label for="anggaran_kegiatan_id">Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control @error('anggaran_kegiatan_id') is-invalid @enderror" 
                                    id="anggaran_kegiatan_id" 
                                    name="anggaran_kegiatan_id" 
                                    required>
                                <option value="">-- Pilih Kegiatan --</option>
                                @foreach($anggaranList as $anggaran)
                                    <option value="{{ $anggaran->id }}" 
                                            data-kode="{{ $anggaran->kode_kegiatan }}"
                                            data-anggaran="{{ $anggaran->anggaran_disetujui }}"
                                            data-sisa="{{ $anggaran->sisa_anggaran }}"
                                            {{ old('anggaran_kegiatan_id') == $anggaran->id ? 'selected' : '' }}>
                                        {{ $anggaran->kode_kegiatan }} - {{ $anggaran->nama_kegiatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('anggaran_kegiatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <input type="text" 
                                       class="form-control @error('jumlah_pencairan') is-invalid @enderror" 
                                       id="jumlah_pencairan" 
                                       name="jumlah_pencairan" 
                                       value="{{ old('jumlah_pencairan') }}"
                                       placeholder="0" 
                                       required>
                                @error('jumlah_pencairan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Jumlah yang akan dicairkan dari anggaran kegiatan</small>
                        </div>

                        <div class="form-group">
                            <label for="keperluan">Keperluan / Keterangan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('keperluan') is-invalid @enderror" 
                                      id="keperluan" 
                                      name="keperluan" 
                                      rows="5" 
                                      placeholder="Jelaskan keperluan pencairan dana ini..." 
                                      required>{{ old('keperluan') }}</textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" name="action" value="draft" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-success"
                                    onclick="return confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan pencairan dana ini untuk disetujui?')">
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

            <!-- Available Budget Card -->
            @if($anggaranList->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-check-circle"></i> Kegiatan Tersedia
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Total kegiatan yang dapat diajukan pencairan:</p>
                    <h3 class="text-success mb-0">{{ $anggaranList->count() }}</h3>
                </div>
            </div>
            @else
            <div class="card shadow mb-4 border-warning">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> Perhatian
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">Tidak ada kegiatan yang tersedia untuk pencairan dana. Pastikan ada kegiatan dengan status <strong>Disetujui Kepala JIC</strong> dan masih memiliki sisa anggaran.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let sisaAnggaran = 0;

    // Format currency input
    $('#jumlah_pencairan').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Check if exceeds sisa anggaran
        const jumlah = parseFloat(value);
        if (sisaAnggaran > 0 && jumlah > sisaAnggaran) {
            $(this).addClass('is-invalid');
            let feedbackDiv = $(this).siblings('.invalid-feedback');
            if (feedbackDiv.length === 0) {
                feedbackDiv = $('<div class="invalid-feedback"></div>');
                $(this).after(feedbackDiv);
            }
            feedbackDiv.text(`Jumlah melebihi sisa anggaran (${formatRupiah(sisaAnggaran)})`);
        } else {
            $(this).removeClass('is-invalid');
            if (!$(this).hasClass('is-invalid')) {
                $(this).siblings('.invalid-feedback:not(.d-block)').text('');
            }
        }
    });

    // Handle kegiatan selection
    $('#anggaran_kegiatan_id').on('change', function() {
        const selected = $(this).find('option:selected');
        
        if (selected.val()) {
            const kode = selected.data('kode');
            const totalAnggaran = selected.data('anggaran');
            sisaAnggaran = selected.data('sisa');
            const totalPencairan = totalAnggaran - sisaAnggaran;
            
            $('#info_kode').text(kode);
            $('#info_total_anggaran').text(formatRupiah(totalAnggaran));
            $('#info_total_pencairan').text(formatRupiah(totalPencairan));
            $('#info_sisa_anggaran').text(formatRupiah(sisaAnggaran));
            
            $('#kegiatanInfo').slideDown();
            
            // Revalidate jumlah pencairan if already filled
            if ($('#jumlah_pencairan').val()) {
                $('#jumlah_pencairan').trigger('keyup');
            }
        } else {
            $('#kegiatanInfo').slideUp();
            sisaAnggaran = 0;
        }
    });

    // Trigger change if there's old value
    @if(old('anggaran_kegiatan_id'))
        $('#anggaran_kegiatan_id').trigger('change');
    @endif

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush