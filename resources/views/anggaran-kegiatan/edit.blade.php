@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Anggaran Kegiatan</h1>
        <div>
            <a href="{{ route('anggaran-kegiatan.show', $anggaran->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('anggaran-kegiatan.update', $anggaran->id) }}" method="POST" id="formAnggaranKegiatan">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="kode_kegiatan">Kode Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_kegiatan" name="kode_kegiatan" 
                                   value="{{ old('kode_kegiatan', $anggaran->kode_kegiatan) }}"
                                   readonly disabled>
                            <small class="form-text text-muted">Kode kegiatan otomatis dan tidak dapat diubah</small>
                        </div>

                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="nama_kegiatan" 
                                   name="nama_kegiatan" 
                                   value="{{ old('nama_kegiatan', $anggaran->nama_kegiatan) }}"
                                   placeholder="Masukkan nama kegiatan" 
                                   required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Kegiatan</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4" 
                                      placeholder="Deskripsi singkat tentang kegiatan">{{ old('deskripsi', $anggaran->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="anggaran_disetujui">Anggaran yang Diajukan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" 
                                       class="form-control @error('anggaran_disetujui') is-invalid @enderror" 
                                       id="anggaran_disetujui" 
                                       name="anggaran_disetujui" 
                                       value="{{ old('anggaran_disetujui', number_format($anggaran->anggaran_disetujui, 0, ',', '.')) }}"
                                       placeholder="0" 
                                       required>
                                @error('anggaran_disetujui')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Masukkan nominal anggaran yang dibutuhkan</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           id="tanggal_mulai" 
                                           name="tanggal_mulai" 
                                           value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($anggaran->tanggal_mulai)->format('Y-m-d')) }}"
                                           required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           id="tanggal_selesai" 
                                           name="tanggal_selesai" 
                                           value="{{ old('tanggal_selesai', \Carbon\Carbon::parse($anggaran->tanggal_selesai)->format('Y-m-d')) }}"
                                           min="{{ old('tanggal_mulai', \Carbon\Carbon::parse($anggaran->tanggal_mulai)->format('Y-m-d')) }}"
                                           required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> <strong>Status:</strong> 
                            @if($anggaran->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @elseif($anggaran->status === 'diajukan')
                                <span class="badge badge-info">Diajukan</span>
                            @elseif($anggaran->status === 'disetujui_kadiv')
                                <span class="badge badge-primary">Disetujui Kadiv</span>
                            @elseif($anggaran->status === 'disetujui_kadiv_umum')
                                <span class="badge badge-primary">Disetujui Kadiv Umum</span>
                            @elseif($anggaran->status === 'disetujui_kepala_jic')
                                <span class="badge badge-success">Disetujui Kepala JIC</span>
                            @elseif($anggaran->status === 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif

                            @if($anggaran->status === 'ditolak' && $anggaran->catatan)
                                <div class="mt-2">
                                    <strong>Catatan Penolakan:</strong>
                                    <div class="alert alert-danger mb-0 mt-1">{{ $anggaran->catatan }}</div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" name="submit_action" value="save" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="submit" name="submit_action" value="submit" class="btn btn-success" 
                                    onclick="return confirm('Apakah Anda yakin ingin menyimpan perubahan dan langsung mengajukan anggaran kegiatan ini untuk disetujui?')">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            <a href="{{ route('anggaran-kegiatan.show', $anggaran->id) }}" class="btn btn-secondary">
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
                        <li>Hanya anggaran dengan status <strong>Draft</strong> atau <strong>Ditolak</strong> yang dapat diedit</li>
                        <li>Setelah disetujui, data tidak dapat diubah</li>
                        <li>Jika ditolak, perbaiki sesuai catatan penolakan</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pastikan semua data terisi dengan benar</li>
                        <li>Anggaran harus sesuai dengan proposal kegiatan</li>
                        <li>Tanggal selesai harus setelah tanggal mulai</li>
                    </ul>
                </div>
            </div>

            <!-- History Card -->
            @if($anggaran->creator)
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
                            <td>{{ \Carbon\Carbon::parse($anggaran->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Oleh:</td>
                            <td>{{ $anggaran->creator->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Update Terakhir:</td>
                            <td>{{ \Carbon\Carbon::parse($anggaran->updated_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
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
    // Format currency input
    $('#anggaran_disetujui').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

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

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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