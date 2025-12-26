@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Rekap Pengajuan</h1>
        <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Rekap Pengajuan</h6>
                    <div>
                        @if($rekap->status === 'draft')
                            <span class="badge badge-secondary">Draft</span>
                        @elseif($rekap->status === 'diajukan')
                            <span class="badge badge-info">Diajukan</span>
                        @elseif($rekap->status === 'disetujui')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($rekap->status === 'ditolak')
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Rekap</td>
                            <td width="5%">:</td>
                            <td>{{ $rekap->nomor_rekap }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pencairan Dana</td>
                            <td>:</td>
                            <td>
                                <div>{{ $rekap->pencairanDana->nomor_pencairan }}</div>
                                <small class="text-muted">
                                    <a href="{{ route('pencairan-dana.show', $rekap->pencairanDana->id) }}" class="text-primary">
                                        Lihat Detail Pencairan
                                    </a>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div>{{ $rekap->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</div>
                                @if($rekap->pencairanDana->anggaranKegiatan)
                                    <small class="text-muted">
                                        <a href="{{ route('anggaran-kegiatan.show', $rekap->pencairanDana->anggaranKegiatan->id) }}" class="text-primary">
                                            Lihat Detail Kegiatan
                                        </a>
                                    </small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pencairan</td>
                            <td>:</td>
                            <td class="text-primary font-weight-bold">
                                Rp {{ number_format($rekap->pencairanDana->jumlah_pencairan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Pengeluaran</td>
                            <td>:</td>
                            <td class="text-danger font-weight-bold">
                                Rp {{ number_format($rekap->total_pengeluaran, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Dana</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold h5">
                                Rp {{ number_format($rekap->sisa_dana, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td>{{ $rekap->catatan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Pada</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($rekap->created_at)->isoFormat('DD MMMM YYYY, HH:mm') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Dana</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Jumlah Pencairan</small>
                                <h4 class="text-primary font-weight-bold mb-0">
                                    Rp {{ number_format($rekap->pencairanDana->jumlah_pencairan, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Total Pengeluaran</small>
                                <h4 class="text-danger font-weight-bold mb-0">
                                    Rp {{ number_format($rekap->total_pengeluaran, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Sisa Dana</small>
                                <h4 class="text-success font-weight-bold mb-0">
                                    Rp {{ number_format($rekap->sisa_dana, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Card -->
            @if($rekap->documents && $rekap->documents->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($rekap->documents as $document)
                                <a href="{{ $document->file_url }}" target="_blank" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-pdf text-danger"></i> {{ $document->nama_dokumen }}
                                    <small class="text-muted float-right">
                                        {{ \Carbon\Carbon::parse($document->created_at)->isoFormat('DD MMM YYYY') }}
                                    </small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Buku Cek Card -->
            @if($rekap->bukuCek)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Buku Cek</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Nomor Buku Cek:</strong> {{ $rekap->bukuCek->nomor_buku_cek }}</p>
                        <p>
                            <strong>Status:</strong> 
                            @if($rekap->bukuCek->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @elseif($rekap->bukuCek->status === 'diajukan')
                                <span class="badge badge-info">Diajukan</span>
                            @elseif($rekap->bukuCek->status === 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($rekap->bukuCek->status === 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif
                        </p>
                        <a href="{{ route('buku-cek.show', $rekap->bukuCek->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Detail Buku Cek
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($canEdit)
                            <a href="{{ route('rekap-pengajuan.edit', $rekap->id) }}" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if(in_array($rekap->status, ['draft', 'ditolak']) && ($canEdit || $canDelete))
                            <form action="{{ route('rekap-pengajuan.submit', $rekap->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block mb-2" onclick="return confirm('Apakah Anda yakin ingin mengajukan rekap pengajuan ini untuk persetujuan?')">
                                    <i class="fas fa-paper-plane"></i> Ajukan Persetujuan
                                </button>
                            </form>
                        @endif

                        @if($canDelete)
                            <form action="{{ route('rekap-pengajuan.destroy', $rekap->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block mb-2" onclick="return confirm('Apakah Anda yakin ingin menghapus rekap pengajuan ini? Data yang dihapus tidak dapat dikembalikan!')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif

                        @if($canApprove)
                            <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#approvalModal">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                            <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        @endif

                        @if($rekap->status === 'disetujui' && !$rekap->bukuCek)
                            @if(auth()->user()->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin']))
                                <a href="{{ route('buku-cek.create', ['rekap_id' => $rekap->id]) }}" class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-book"></i> Buat Buku Cek
                                </a>
                            @endif
                        @endif

                        @if(!$canEdit && !$canDelete && !$canApprove && ($rekap->status !== 'disetujui' || $rekap->bukuCek))
                            <p class="text-muted mb-0">Tidak ada aksi tersedia</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Status Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="approval-steps">
                        <!-- Draft Step -->
                        <div class="step {{ $rekap->status === 'draft' ? 'current' : 'completed' }} mb-3">
                            <div class="d-flex align-items-center">
                                <div class="step-icon mr-3">
                                    <i class="fas fa-file-alt fa-2x {{ $rekap->status === 'draft' ? 'text-primary' : 'text-success' }}"></i>
                                </div>
                                <div class="step-info">
                                    <div class="font-weight-bold">Draft</div>
                                    <small class="text-muted">
                                        {{ $rekap->status === 'draft' ? 'Sedang Diproses' : 'Selesai' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Diajukan Step -->
                        <div class="step {{ $rekap->status === 'diajukan' ? 'current' : ($rekap->status === 'disetujui' ? 'completed' : 'pending') }} mb-3">
                            <div class="d-flex align-items-center">
                                <div class="step-icon mr-3">
                                    <i class="fas fa-paper-plane fa-2x {{ $rekap->status === 'diajukan' ? 'text-primary' : ($rekap->status === 'disetujui' ? 'text-success' : 'text-muted') }}"></i>
                                </div>
                                <div class="step-info">
                                    <div class="font-weight-bold">Diajukan</div>
                                    <small class="text-muted">
                                        @if($rekap->status === 'diajukan')
                                            Sedang Diproses
                                        @elseif($rekap->status === 'disetujui')
                                            Selesai
                                        @else
                                            Menunggu
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Disetujui Step -->
                        <div class="step {{ $rekap->status === 'disetujui' ? 'current' : 'pending' }} mb-3">
                            <div class="d-flex align-items-center">
                                <div class="step-icon mr-3">
                                    <i class="fas fa-check-circle fa-2x {{ $rekap->status === 'disetujui' ? 'text-success' : 'text-muted' }}"></i>
                                </div>
                                <div class="step-info">
                                    <div class="font-weight-bold">Disetujui</div>
                                    <small class="text-muted">
                                        {{ $rekap->status === 'disetujui' ? 'Selesai' : 'Menunggu' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Rejected Step (if applicable) -->
                        @if($rekap->status === 'ditolak')
                            <div class="step rejected mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="step-icon mr-3">
                                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                                    </div>
                                    <div class="step-info">
                                        <div class="font-weight-bold text-danger">Ditolak</div>
                                        <small class="text-muted">Pengajuan ditolak</small>
                                        @if($rekap->catatan)
                                            <div class="alert alert-danger mt-2 mb-0">
                                                <small>{{ $rekap->catatan }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('rekap-pengajuan.approve', $rekap->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Rekap Pengajuan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approvalCatatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="approvalCatatan" name="catatan" rows="3" 
                                  placeholder="Masukkan catatan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('rekap-pengajuan.reject', $rekap->id) }}" method="POST" id="rejectForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Tolak Rekap Pengajuan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectCatatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                  id="rejectCatatan" 
                                  name="catatan" 
                                  rows="4" 
                                  placeholder="Masukkan alasan penolakan" 
                                  required>{{ old('catatan') }}</textarea>
                        <small class="form-text text-muted">Catatan wajib diisi saat menolak pengajuan</small>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Approval Steps Styling */
.approval-steps .step {
    position: relative;
    padding-left: 0;
}

.approval-steps .step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 30px;
    background-color: #e3e6f0;
}

.approval-steps .step.completed:not(:last-child)::after {
    background-color: #1cc88a;
}

.approval-steps .step.current:not(:last-child)::after {
    background-color: #4e73df;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Show reject modal with validation error if exists
    @if($errors->has('catatan'))
        $('#rejectModal').modal('show');
    @endif

    // Validate reject form before submit
    $('#rejectForm').on('submit', function(e) {
        const catatan = $('#rejectCatatan').val().trim();
        if (!catatan) {
            e.preventDefault();
            alert('Alasan penolakan harus diisi!');
            $('#rejectCatatan').focus();
            return false;
        }
    });
});
</script>
@endpush