@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail LPJ Kegiatan</h1>
        <a href="{{ route('lpj-kegiatan.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- LPJ Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi LPJ</h6>
                    @if($lpj->status == 'draft')
                        <span class="badge badge-secondary badge-lg">Draft</span>
                    @elseif($lpj->status == 'diajukan')
                        <span class="badge badge-info badge-lg">Diajukan</span>
                    @elseif($lpj->status == 'disetujui')
                        <span class="badge badge-success badge-lg">Disetujui</span>
                    @elseif($lpj->status == 'ditolak')
                        <span class="badge badge-danger badge-lg">Ditolak</span>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor LPJ</td>
                            <td width="5%">:</td>
                            <td>{{ $lpj->nomor_lpj }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                @if($lpj->anggaranKegiatan)
                                    <strong>{{ $lpj->anggaranKegiatan->kode_kegiatan }}</strong><br>
                                    {{ $lpj->anggaranKegiatan->nama_kegiatan }}<br>
                                    <small class="text-muted">
                                        <a href="{{ route('anggaran-kegiatan.show', $lpj->anggaranKegiatan->id) }}" class="text-primary">
                                            <i class="fas fa-external-link-alt"></i> Lihat Detail Kegiatan
                                        </a>
                                    </small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Periode Kegiatan</td>
                            <td>:</td>
                            <td>
                                @if($lpj->anggaranKegiatan)
                                    {{ \Carbon\Carbon::parse($lpj->anggaranKegiatan->tanggal_mulai)->format('d M Y') }} 
                                    s/d 
                                    {{ \Carbon\Carbon::parse($lpj->anggaranKegiatan->tanggal_selesai)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Anggaran Disetujui</td>
                            <td>:</td>
                            <td class="text-primary">
                                @if($lpj->anggaranKegiatan)
                                    Rp {{ number_format($lpj->anggaranKegiatan->anggaran_disetujui, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Realisasi</td>
                            <td>:</td>
                            <td class="text-danger font-weight-bold">
                                Rp {{ number_format($lpj->total_realisasi, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Anggaran</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold h5">
                                Rp {{ number_format($lpj->sisa_anggaran, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold align-top">Laporan Kegiatan</td>
                            <td class="align-top">:</td>
                            <td>
                                <div style="white-space: pre-wrap;">{{ $lpj->laporan_kegiatan }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Oleh</td>
                            <td>:</td>
                            <td>
                                @if($lpj->creator)
                                    {{ $lpj->creator->name }}<br>
                                    <small class="text-muted">{{ $lpj->created_at->format('d M Y, H:i') }} WIB</small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @if($lpj->approved_by)
                        <tr>
                            <td class="font-weight-bold">Disetujui Oleh</td>
                            <td>:</td>
                            <td>
                                @if($lpj->approver)
                                    {{ $lpj->approver->name }}<br>
                                    <small class="text-muted">{{ $lpj->approved_at->format('d M Y, H:i') }} WIB</small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($lpj->catatan)
                        <tr>
                            <td class="font-weight-bold align-top">Catatan</td>
                            <td class="align-top">:</td>
                            <td>
                                <div class="alert alert-{{ $lpj->status == 'ditolak' ? 'danger' : 'info' }} mb-0">
                                    {{ $lpj->catatan }}
                                </div>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Documents Card (if any) -->
            @if($lpj->documents && $lpj->documents->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen Lampiran</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lpj->documents as $index => $doc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $doc->original_name }}</td>
                                    <td>{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                                    <td>{{ $doc->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Action Buttons Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Generate PDF Button - available for approved LPJ -->
                    @if($lpj->status == 'disetujui')
                    <a href="{{ route('lpj-kegiatan.pdf', $lpj->id) }}" target="_blank" class="btn btn-danger btn-block mb-2">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </a>
                    <hr>
                    @endif

                    <!-- Edit Button - only for draft and ditolak -->
                    @if(auth()->user()->hasRole('staff') && in_array($lpj->status, ['draft', 'ditolak']))
                    <a href="{{ route('lpj-kegiatan.edit', $lpj->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit LPJ
                    </a>
                    @endif

                    <!-- Submit Button - only for draft and ditolak -->
                    @if(auth()->user()->hasRole('staff') && in_array($lpj->status, ['draft', 'ditolak']))
                    <form action="{{ route('lpj-kegiatan.submit', $lpj->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan LPJ ini?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-paper-plane"></i> Ajukan LPJ
                        </button>
                    </form>
                    @endif

                    <!-- Approve/Reject Buttons - for kadiv_umum and kepala_jic -->
                    @if(auth()->user()->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin']) && $lpj->status == 'diajukan')
                    <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#approvalModal">
                        <i class="fas fa-check"></i> Setujui
                    </button>
                    <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                    <hr>
                    @endif

                    <!-- Delete Button - only for draft -->
                    @if(auth()->user()->hasRole('staff') && $lpj->status == 'draft')
                    <form action="{{ route('lpj-kegiatan.destroy', $lpj->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus LPJ ini? Data yang dihapus tidak dapat dikembalikan!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Hapus LPJ
                        </button>
                    </form>
                    @endif

                    @if(!in_array($lpj->status, ['draft', 'ditolak', 'diajukan']))
                    <p class="text-muted text-center mb-0">Tidak ada aksi yang tersedia</p>
                    @endif
                </div>
            </div>

            <!-- Progress Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Progress LPJ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress-steps">
                        <!-- Step 1: Draft -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-file-alt fa-2x {{ in_array($lpj->status, ['draft', 'diajukan', 'disetujui']) ? 'text-success' : 'text-muted' }}"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold">Draft Dibuat</div>
                                    @if($lpj->created_at)
                                    <small class="text-muted">{{ $lpj->created_at->format('d M Y, H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Diajukan -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-paper-plane fa-2x {{ in_array($lpj->status, ['diajukan', 'disetujui']) ? 'text-success' : ($lpj->status == 'draft' ? 'text-muted' : 'text-muted') }}"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold">Diajukan</div>
                                    @if(in_array($lpj->status, ['diajukan', 'disetujui']))
                                    <small class="text-muted">Menunggu persetujuan</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Disetujui -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-check-circle fa-2x {{ $lpj->status == 'disetujui' ? 'text-success' : 'text-muted' }}"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold">Disetujui</div>
                                    @if($lpj->status == 'disetujui' && $lpj->approved_at)
                                    <small class="text-muted">{{ $lpj->approved_at->format('d M Y, H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Ditolak (if applicable) -->
                        @if($lpj->status == 'ditolak')
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold text-danger">Ditolak</div>
                                    <small class="text-muted">Perlu revisi</small>
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
            <form action="{{ route('lpj-kegiatan.approve', $lpj->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui LPJ Kegiatan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyetujui LPJ Kegiatan ini?</p>
                    <div class="form-group">
                        <label for="approvalCatatan">Catatan (Opsional)</label>
                        <textarea name="catatan" id="approvalCatatan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
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
            <form action="{{ route('lpj-kegiatan.reject', $lpj->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Tolak LPJ Kegiatan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menolak LPJ Kegiatan ini?</p>
                    <div class="form-group">
                        <label for="rejectCatatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan" id="rejectCatatan" class="form-control @error('catatan') is-invalid @enderror" rows="4" placeholder="Jelaskan alasan penolakan" required></textarea>
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