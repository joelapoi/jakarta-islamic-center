@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Pencairan Dana</h1>
        <div>
            <a href="{{ route('pencairan-dana.show', $pencairan) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('pencairan-dana.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Pencairan Dana</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pencairan-dana.update', $pencairan) }}" method="POST" id="formPencairanDana">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Nomor Pencairan</label>
                            <input type="text" class="form-control" value="{{ $pencairan->nomor_pencairan }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Kegiatan</label>
                            <input type="text" class="form-control" 
                                   value="{{ $pencairan->anggaranKegiatan->kode_kegiatan }} - {{ $pencairan->anggaranKegiatan->nama_kegiatan }}" 
                                   readonly>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Informasi Anggaran Kegiatan:</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="40%">Kode Kegiatan</td>
                                    <td width="5%">:</td>
                                    <td>{{ $pencairan->anggaranKegiatan->kode_kegiatan }}</td>
                                </tr>
                                <tr>
                                    <td>Total Anggaran</td>
                                    <td>:</td>
                                    <td>Rp {{ number_format($pencairan->anggaranKegiatan->anggaran_disetujui, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Total Pencairan</td>
                                    <td>:</td>
                                    <td>Rp {{ number_format($totalPencairan, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sisa Anggaran</td>
                                    <td>:</td>
                                    <td class="font-weight-bold text-success" id="sisaAnggaranDisplay">
                                        Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tersedia untuk Edit</td>
                                    <td>:</td>
                                    <td class="font-weight-bold text-primary">
                                        Rp {{ number_format($availableBudget, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                            <small class="text-muted">* Tersedia untuk Edit = Sisa Anggaran + Jumlah Pencairan Saat Ini</small>
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
                                       value="{{ old('jumlah_pencairan', number_format($pencairan->jumlah_pencairan, 0, ',', '.')) }}"
                                       placeholder="0" 
                                       data-available="{{ $availableBudget }}"
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
                                      required>{{ old('keperluan', $pencairan->keperluan) }}</textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> <strong>Status:</strong> 
                            @if($pencairan->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @elseif($pencairan->status === 'diajukan')
                                <span class="badge badge-info">Diajukan</span>
                            @elseif($pencairan->status === 'disetujui_kadiv_umum')
                                <span class="badge badge-primary">Disetujui Kadiv Umum</span>
                            @elseif($pencairan->status === 'disetujui_kepala_jic')
                                <span class="badge badge-success">Disetujui Kepala JIC</span>
                            @elseif($pencairan->status === 'dicairkan')
                                <span class="badge badge-success">Dicairkan</span>
                            @elseif($pencairan->status === 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif

                            @if($pencairan->status === 'ditolak' && $pencairan->catatan)
                                <div class="mt-2">
                                    <strong>Catatan Penolakan:</strong>
                                    <div class="alert alert-danger mb-0 mt-1">{{ $pencairan->catatan }}</div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" name="action" value="save" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-success"
                                    onclick="return confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan pencairan dana ini untuk disetujui?')">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('pencairan-dana.show', $pencairan) }}" class="btn btn-secondary">
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
                        <li>Jumlah pencairan tidak boleh melebihi sisa anggaran tersedia</li>
                        <li>Jelaskan keperluan dengan detail</li>
                    </ul>
                </div>
            </div>

            <!-- History Card -->
            @if($pencairan->creator)
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
                            <td>{{ \Carbon\Carbon::parse($pencairan->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Oleh:</td>
                            <td>{{ $pencairan->creator->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Update Terakhir:</td>
                            <td>{{ \Carbon\Carbon::parse($pencairan->updated_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                    </table>
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
    const availableBudget = parseInt($('#jumlah_pencairan').data('available'));

    // Format currency input
    $('#jumlah_pencairan').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Check if exceeds available budget
        const jumlah = parseFloat(value);
        
        if (jumlah > availableBudget) {
            $(this).addClass('is-invalid');
            let feedbackDiv = $(this).siblings('.invalid-feedback');
            if (feedbackDiv.length === 0 || !feedbackDiv.hasClass('d-block')) {
                feedbackDiv = $('<div class="invalid-feedback"></div>');
                $(this).after(feedbackDiv);
            }
            feedbackDiv.text(`Jumlah melebihi anggaran yang tersedia (${formatRupiah(availableBudget)})`);
            feedbackDiv.show();
        } else {
            $(this).removeClass('is-invalid');
            // Only hide if not from server validation
            if (!$(this).siblings('.invalid-feedback').hasClass('d-block')) {
                $(this).siblings('.invalid-feedback').text('').hide();
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

    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush