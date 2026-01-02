@extends('layouts.app')

@section('content')
<div class="container-fluid">
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
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Buku Cek</h1>
        <div>
            <a href="{{ route('buku-cek.show', $bukuCek->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Buku Cek</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('buku-cek.update', $bukuCek->id) }}" method="POST" id="formBukuCek">
                        @csrf
                        @method('PUT')
                        
                        <!-- Nomor Buku Cek (Read Only) -->
                        <div class="form-group">
                            <label>Nomor Buku Cek</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $bukuCek->nomor_buku_cek }}"
                                   readonly 
                                   style="background-color: #e9ecef;">
                        </div>

                        <!-- Rekap Pengajuan (Read Only) -->
                        <div class="form-group">
                            <label>Rekap Pengajuan</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $bukuCek->rekapPengajuan->nomor_rekap }}"
                                   readonly 
                                   style="background-color: #e9ecef;">
                        </div>

                        <!-- Rekap Information (Read Only) -->
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Informasi Rekap Pengajuan:</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="40%">Nomor Rekap</td>
                                    <td width="5%">:</td>
                                    <td><strong>{{ $bukuCek->rekapPengajuan->nomor_rekap }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Kegiatan</td>
                                    <td>:</td>
                                    <td>{{ $bukuCek->rekapPengajuan->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sisa Dana</td>
                                    <td>:</td>
                                    <td class="font-weight-bold text-success">Rp {{ number_format($bukuCek->rekapPengajuan->sisa_dana, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Nama Bank -->
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_bank') is-invalid @enderror" 
                                   id="nama_bank" 
                                   name="nama_bank" 
                                   value="{{ old('nama_bank', $bukuCek->nama_bank) }}"
                                   placeholder="Contoh: Bank BNI" 
                                   required>
                            @error('nama_bank')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nomor Rekening -->
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nomor_rekening') is-invalid @enderror" 
                                   id="nomor_rekening" 
                                   name="nomor_rekening" 
                                   value="{{ old('nomor_rekening', $bukuCek->nomor_rekening) }}"
                                   placeholder="Contoh: 1234567890" 
                                   required>
                            @error('nomor_rekening')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Penerima -->
                        <div class="form-group">
                            <label for="nama_penerima">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_penerima') is-invalid @enderror" 
                                   id="nama_penerima" 
                                   name="nama_penerima" 
                                   value="{{ old('nama_penerima', $bukuCek->nama_penerima) }}"
                                   placeholder="Nama lengkap penerima" 
                                   required>
                            @error('nama_penerima')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jumlah Dana (Read Only) -->
                        <div class="form-group">
                            <label for="jumlah_display">Jumlah Dana</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" 
                                       class="form-control" 
                                       id="jumlah_display" 
                                       value="{{ number_format($bukuCek->jumlah, 0, ',', '.') }}"
                                       readonly 
                                       style="background-color: #e9ecef;">
                            </div>
                            <small class="form-text text-muted">Jumlah otomatis dari sisa dana rekap pengajuan (tidak dapat diubah)</small>
                        </div>

                        <!-- Hidden field for actual number value -->
                        <input type="hidden" name="jumlah" value="{{ $bukuCek->jumlah }}">

                        <!-- Keperluan -->
                        <div class="form-group">
                            <label for="keperluan">Keperluan</label>
                            <textarea class="form-control @error('keperluan') is-invalid @enderror" 
                                      id="keperluan" 
                                      name="keperluan" 
                                      rows="4" 
                                      placeholder="Jelaskan keperluan pencairan dana...">{{ old('keperluan', $bukuCek->keperluan) }}</textarea>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Info -->
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> <strong>Status:</strong> 
                            @switch($bukuCek->status)
                                @case('draft')
                                    <span class="badge badge-secondary">Draft</span>
                                    @break
                                @case('menunggu_ttd_kepala_jic')
                                    <span class="badge badge-warning">Menunggu TTD</span>
                                    @break
                                @case('ditandatangani')
                                    <span class="badge badge-primary">Ditandatangani</span>
                                    @break
                                @case('dikonfirmasi_bank')
                                    <span class="badge badge-success">Dikonfirmasi Bank</span>
                                    @break
                                @case('ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $bukuCek->status }}</span>
                            @endswitch
                        </div>

                        <hr>

                        <!-- Submit Buttons -->
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('buku-cek.show', $bukuCek->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
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
                        <li>Hanya buku cek dengan status <strong>Draft</strong> yang dapat diedit</li>
                        <li>Setelah ditandatangani, data tidak dapat diubah</li>
                        <li>Nomor buku cek tidak dapat diubah</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan data bank benar</li>
                        <li>Periksa nama penerima dengan teliti</li>
                        <li>Jumlah akan otomatis dari sisa dana</li>
                        <li>Status saat ini: 
                            @switch($bukuCek->status)
                                @case('draft')
                                    <span class="badge badge-secondary">Draft</span>
                                    @break
                                @case('menunggu_ttd_kepala_jic')
                                    <span class="badge badge-warning">Menunggu TTD</span>
                                    @break
                                @case('ditandatangani')
                                    <span class="badge badge-primary">Ditandatangani</span>
                                    @break
                                @case('dikonfirmasi_bank')
                                    <span class="badge badge-success">Dikonfirmasi Bank</span>
                                    @break
                                @case('ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                    @break
                            @endswitch
                        </li>
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
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="font-weight-bold" width="40%">Dibuat:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Update Terakhir:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->updated_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @if($bukuCek->submitted_at)
                        <tr>
                            <td class="font-weight-bold">Diajukan:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->submitted_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->signed_at)
                        <tr>
                            <td class="font-weight-bold">Ditandatangani:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->signed_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->confirmed_at)
                        <tr>
                            <td class="font-weight-bold">Dikonfirmasi:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->confirmed_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->rejected_at)
                        <tr>
                            <td class="font-weight-bold">Ditolak:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->rejected_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Rejection Reason Card (if rejected) -->
            @if($bukuCek->status === 'ditolak' && $bukuCek->alasan_penolakan)
            <div class="card shadow mb-4 border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-times-circle"></i> Alasan Penolakan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $bukuCek->alasan_penolakan }}</p>
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
    // Format number input on typing (optional, for better UX)
    $('#nomor_rekening').on('input', function() {
        // Remove non-numeric characters
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Confirm before submit
    $('#formBukuCek').on('submit', function(e) {
        const confirmed = confirm('Apakah Anda yakin ingin menyimpan perubahan ini?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });
});
</script>
@endpush