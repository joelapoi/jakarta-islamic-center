@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
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
            <strong>Terdapat kesalahan pada form:</strong>
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

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Anggaran Kegiatan</h1>
        <div>
            <form action="{{ route('anggaran-kegiatan.cancel-draft', $anggaran->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan draft ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Batalkan Draft
                </button>
            </form>
            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Draft Info Alert -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Draft dibuat:</strong> Kode Kegiatan <strong>{{ $anggaran->kode_kegiatan }}</strong>
        <br>
        <small>Draft akan otomatis tersimpan. Anda dapat melanjutkan pengisian nanti.</small>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('anggaran-kegiatan.store') }}" method="POST" id="formAnggaranKegiatan">
                        @csrf
                        
                        {{-- Hidden field untuk anggaran_id --}}
                        <input type="hidden" name="anggaran_id" value="{{ $anggaran->id }}">
                        
                        {{-- Hidden field untuk submit action --}}
                        <input type="hidden" name="submit_action" id="submit_action" value="draft">
                        
                        <div class="form-group">
                            <label for="kode_kegiatan">Kode Kegiatan</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="kode_kegiatan" 
                                   value="{{ $anggaran->kode_kegiatan }}"
                                   disabled>
                            <small class="form-text text-muted">Kode kegiatan dibuat otomatis</small>
                        </div>

                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="nama_kegiatan" 
                                   name="nama_kegiatan" 
                                   value="{{ old('nama_kegiatan', strpos($anggaran->nama_kegiatan, 'Draft - ') === 0 ? '' : $anggaran->nama_kegiatan) }}"
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
                                       value="{{ old('anggaran_disetujui', $anggaran->anggaran_disetujui > 0 ? number_format($anggaran->anggaran_disetujui, 0, ',', '.') : '') }}"
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
                                           value="{{ old('tanggal_mulai', $anggaran->tanggal_mulai) }}"
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
                                           value="{{ old('tanggal_selesai', $anggaran->tanggal_selesai) }}"
                                           required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            {{-- Button Simpan Draft --}}
                            <button type="submit" class="btn btn-primary" id="btnSaveDraft">
                                <i class="fas fa-save"></i> Simpan Draft
                            </button>
                            
                            {{-- Button Simpan & Ajukan --}}
                            <button type="button" class="btn btn-success" id="btnSaveAndSubmit">
                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                            </button>
                            
                            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
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
                    <h6 class="font-weight-bold">Status Saat Ini:</h6>
                    <span class="badge badge-secondary badge-lg">{{ strtoupper($anggaran->status) }}</span>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Alur Persetujuan:</h6>
                    <ol class="pl-3">
                        <li><strong>Draft dibuat</strong> (Anda di sini)</li>
                        <li>Diajukan untuk persetujuan</li>
                        <li>Disetujui Kadiv</li>
                        <li>Disetujui Kadiv Umum</li>
                        <li>Disetujui Kepala JIC</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li><strong>Simpan Draft:</strong> Data tersimpan sebagai draft, dapat diedit lagi nanti</li>
                        <li><strong>Simpan & Ajukan:</strong> Data langsung diajukan untuk persetujuan</li>
                        <li>Pastikan semua data terisi dengan benar sebelum mengajukan</li>
                        <li>Tanggal selesai harus setelah tanggal mulai</li>
                        <li>Setelah diajukan, data tidak dapat diubah kecuali ditolak</li>
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-info)').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Format currency input
    $('#anggaran_disetujui').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

    // Set minimum date for tanggal_mulai to today
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_mulai').attr('min', today);

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

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Button Simpan Draft - submit as draft
    $('#btnSaveDraft').on('click', function() {
        $('#submit_action').val('draft');
        $('#btnSaveDraft').prop('disabled', true)
                         .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });

    // Button Simpan & Ajukan - submit and request approval
    $('#btnSaveAndSubmit').on('click', function() {
        if (confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan anggaran kegiatan ini untuk disetujui?')) {
            $('#submit_action').val('submit');
            $('#btnSaveAndSubmit').prop('disabled', true)
                                  .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan & Mengajukan...');
            $('#formAnggaranKegiatan').submit();
        }
    });

    // Handle form submit
    $('#formAnggaranKegiatan').on('submit', function() {
        // Disable all buttons to prevent double submit
        $('#btnSaveDraft, #btnSaveAndSubmit').prop('disabled', true);
    });
});
</script>
@endpush