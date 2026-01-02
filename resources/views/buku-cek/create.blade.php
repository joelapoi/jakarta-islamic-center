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
        <h1 class="h3 mb-0 text-gray-800">Buat Buku Cek</h1>
        <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Buku Cek</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('buku-cek.store') }}" method="POST" id="formBukuCek">
                        @csrf
                        
                        <!-- Rekap Pengajuan (Hidden) -->
                        <input type="hidden" name="rekap_pengajuan_id" value="{{ $selectedRekap->id }}">

                        <!-- Rekap Information -->
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Informasi Rekap Pengajuan:</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="40%">Nomor Rekap</td>
                                    <td width="5%">:</td>
                                    <td><strong>{{ $selectedRekap->nomor_rekap }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Kegiatan</td>
                                    <td>:</td>
                                    <td>{{ $selectedRekap->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Total Pengeluaran</td>
                                    <td>:</td>
                                    <td>Rp {{ number_format($selectedRekap->total_pengeluaran, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sisa Dana</td>
                                    <td>:</td>
                                    <td class="font-weight-bold text-success">Rp {{ number_format($selectedRekap->sisa_dana, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>

                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-money-check"></i> Informasi Cek (Opsional)
                        </h6>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Nomor cek dan tanggal cek dapat diisi sekarang atau nanti setelah cek diterbitkan oleh bank.
                        </p>

                        <!-- Nomor Cek -->
                        <div class="form-group">
                            <label for="nomor_cek">Nomor Cek</label>
                            <input type="text" 
                                   class="form-control @error('nomor_cek') is-invalid @enderror" 
                                   id="nomor_cek" 
                                   name="nomor_cek" 
                                   value="{{ old('nomor_cek') }}"
                                   placeholder="Masukkan nomor cek (jika sudah ada)">
                            <small class="form-text text-muted">Nomor cek dari bank. Dapat diisi nanti saat edit.</small>
                            @error('nomor_cek')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Cek -->
                        <div class="form-group">
                            <label for="tanggal_cek">Tanggal Cek</label>
                            <input type="date" 
                                   class="form-control @error('tanggal_cek') is-invalid @enderror" 
                                   id="tanggal_cek" 
                                   name="tanggal_cek" 
                                   value="{{ old('tanggal_cek') }}">
                            <small class="form-text text-muted">Tanggal penerbitan cek. Dapat diisi nanti saat edit.</small>
                            @error('tanggal_cek')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-university"></i> Informasi Bank & Penerima
                        </h6>

                        <!-- Nama Bank -->
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_bank') is-invalid @enderror" 
                                   id="nama_bank" 
                                   name="nama_bank" 
                                   value="{{ old('nama_bank') }}"
                                   placeholder="Contoh: Bank BNI, Bank Mandiri, BCA" 
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
                                   value="{{ old('nomor_rekening') }}"
                                   placeholder="Contoh: 1234567890" 
                                   required>
                            <small class="form-text text-muted">Nomor rekening penerima dana</small>
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
                                   value="{{ old('nama_penerima') }}"
                                   placeholder="Nama lengkap sesuai rekening" 
                                   required>
                            <small class="form-text text-muted">Nama penerima harus sesuai dengan nama di rekening</small>
                            @error('nama_penerima')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-dollar-sign"></i> Jumlah & Keperluan
                        </h6>

                        <!-- Jumlah Dana / Nominal -->
                        <div class="form-group">
                            <label for="nominal_display">Jumlah Dana <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" 
                                       class="form-control" 
                                       id="nominal_display" 
                                       value="{{ number_format($selectedRekap->sisa_dana, 0, ',', '.') }}"
                                       readonly 
                                       style="background-color: #e9ecef;">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Jumlah otomatis terisi dari sisa dana rekap pengajuan
                            </small>
                        </div>

                        <!-- Hidden field for actual number value -->
                        <input type="hidden" name="nominal" value="{{ $selectedRekap->sisa_dana }}">

                        <!-- Keperluan -->
                        <div class="form-group">
                            <label for="keperluan">Keperluan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('keperluan') is-invalid @enderror" 
                                      id="keperluan" 
                                      name="keperluan" 
                                      rows="4" 
                                      placeholder="Jelaskan keperluan pencairan dana secara detail..."
                                      required>{{ old('keperluan') }}</textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- Submit Buttons -->
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Simpan Buku Cek
                            </button>
                            <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Alur Buku Cek:</h6>
                    <ol class="pl-3">
                        <li><s>Draft dibuat</s> (otomatis dilewati)</li>
                        <li><strong>Menunggu Tanda Tangan Kepala JIC</strong></li>
                        <li>Ditandatangani</li>
                        <li>Dikonfirmasi Bank</li>
                    </ol>

                    <hr>

                    <h6 class="font-weight-bold">Catatan Penting:</h6>
                    <ul class="pl-3 mb-0">
                        <li><strong>Buku cek langsung diajukan</strong> setelah dibuat (tidak ada draft)</li>
                        <li><strong>Nomor cek</strong> dan <strong>tanggal cek</strong> dapat diisi sekarang atau nanti</li>
                        <li>Pastikan data bank dan penerima sudah benar</li>
                        <li>Jumlah dana otomatis dari sisa dana rekap</li>
                        <li>Setelah dibuat, <strong>langsung menunggu tanda tangan Kepala JIC</strong></li>
                        <li>Edit hanya bisa dilakukan oleh admin (emergency)</li>
                    </ul>
                </div>
            </div>

            <!-- Rekap Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice"></i> Detail Rekap
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Nomor:</dt>
                        <dd class="col-sm-7">{{ $selectedRekap->nomor_rekap }}</dd>

                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-success">{{ ucfirst($selectedRekap->status) }}</span>
                        </dd>

                        <dt class="col-sm-5">Tanggal:</dt>
                        <dd class="col-sm-7">{{ \Carbon\Carbon::parse($selectedRekap->created_at)->format('d M Y') }}</dd>

                        <dt class="col-sm-5">Pembuat:</dt>
                        <dd class="col-sm-7">{{ $selectedRekap->pencairanDana->creator->name ?? '-' }}</dd>
                    </dl>

                    <hr>

                    <a href="{{ route('rekap-pengajuan.show', $selectedRekap->id) }}" 
                       class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-eye"></i> Lihat Detail Rekap
                    </a>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <i class="fas fa-lightbulb fa-2x"></i>
                    </div>
                    <h6 class="font-weight-bold text-warning">Tips</h6>
                    <p class="small mb-0">
                        Jika nomor cek belum diterbitkan oleh bank, Anda dapat menyimpan terlebih dahulu 
                        tanpa mengisi nomor cek. Nomor cek dapat ditambahkan nanti saat proses edit.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Format nomor rekening - only allow numbers
    $('#nomor_rekening').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
    });

    // Format nomor cek - allow alphanumeric
    $('#nomor_cek').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
    });

    // Character counter for keperluan
    $('#keperluan').on('input', function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        // Remove existing counter
        $(this).siblings('.char-counter').remove();
        
        // Add counter
        if (remaining < 100) {
            const counterClass = remaining < 50 ? 'text-danger' : 'text-warning';
            $(this).after(`<small class="form-text ${counterClass} char-counter">${remaining} karakter tersisa</small>`);
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Validate form before submit
    $('#formBukuCek').on('submit', function(e) {
        // Check required fields
        const namaBank = $('#nama_bank').val().trim();
        const nomorRekening = $('#nomor_rekening').val().trim();
        const namaPenerima = $('#nama_penerima').val().trim();
        const keperluan = $('#keperluan').val().trim();

        if (!namaBank || !nomorRekening || !namaPenerima || !keperluan) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi (*)');
            return false;
        }

        // Confirm submission
        const confirmed = confirm('Apakah Anda yakin ingin membuat buku cek ini?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });

    // Set max date for tanggal_cek to today
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_cek').attr('max', today);
});
</script>
@endpush