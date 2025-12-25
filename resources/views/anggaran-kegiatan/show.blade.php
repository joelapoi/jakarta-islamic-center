@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Anggaran Kegiatan</h1>
        <div>
            <a href="{{ route('anggaran-kegiatan.timeline', $anggaran->id) }}" class="btn btn-info">
                <i class="fas fa-history"></i> Timeline
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan</h6>
                    <div>
                        @php
                            $statusBadges = [
                                'draft' => '<span class="badge badge-secondary">Draft</span>',
                                'diajukan' => '<span class="badge badge-info">Diajukan</span>',
                                'disetujui_kadiv' => '<span class="badge badge-primary">Disetujui Kadiv</span>',
                                'disetujui_kadiv_umum' => '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
                                'disetujui_kepala_jic' => '<span class="badge badge-success">Disetujui Kepala JIC</span>',
                                'ditolak' => '<span class="badge badge-danger">Ditolak</span>'
                            ];
                        @endphp
                        {!! $statusBadges[$anggaran->status] ?? $anggaran->status !!}
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Kode Kegiatan</td>
                            <td width="5%">:</td>
                            <td>{{ $anggaran->kode_kegiatan }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Kegiatan</td>
                            <td>:</td>
                            <td>{{ $anggaran->nama_kegiatan }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Deskripsi</td>
                            <td>:</td>
                            <td>{{ $anggaran->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Anggaran Disetujui</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold">Rp {{ number_format($anggaran->anggaran_disetujui, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Pencairan</td>
                            <td>:</td>
                            <td>Rp {{ number_format($totalPencairan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Anggaran</td>
                            <td>:</td>
                            <td>Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Mulai</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($anggaran->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Selesai</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($anggaran->tanggal_selesai)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Oleh</td>
                            <td>:</td>
                            <td>{{ $anggaran->creator->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Dibuat</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($anggaran->created_at)->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        @if($anggaran->approved_by && $anggaran->approver)
                            <tr>
                                <td class="font-weight-bold">Disetujui Oleh</td>
                                <td>:</td>
                                <td>{{ $anggaran->approver->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Tanggal Disetujui</td>
                                <td>:</td>
                                <td>{{ \Carbon\Carbon::parse($anggaran->approved_at)->translatedFormat('d F Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($anggaran->catatan)
                            <tr>
                                <td class="font-weight-bold">Catatan</td>
                                <td>:</td>
                                <td>
                                    <div class="alert alert-info mb-0">{{ $anggaran->catatan }}</div>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Documents Card -->
            @if($anggaran->documents && count($anggaran->documents) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($anggaran->documents as $doc)
                                <a href="{{ $doc->file_url }}" target="_blank" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-pdf text-danger"></i> {{ $doc->nama_dokumen }}
                                    <small class="text-muted float-right">{{ \Carbon\Carbon::parse($doc->created_at)->translatedFormat('d M Y') }}</small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Pencairan Dana List -->
            @if($anggaran->status === 'disetujui_kepala_jic')
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Riwayat Pencairan Dana</h6>
                        @if($canAddPencairan)
                            <a href="{{ route('pencairan-dana.create', ['anggaran_id' => $anggaran->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Ajukan Pencairan
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($anggaran->pencairanDana as $index => $pencairan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pencairan->created_at)->translatedFormat('d M Y') }}</td>
                                            <td>Rp {{ number_format($pencairan->jumlah_pencairan, 0, ',', '.') }}</td>
                                            <td>
                                                @php
                                                    $pencairanStatusBadges = [
                                                        'draft' => '<span class="badge badge-secondary">Draft</span>',
                                                        'diajukan' => '<span class="badge badge-info">Diajukan</span>',
                                                        'disetujui_kadiv_umum' => '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
                                                        'disetujui_kepala_jic' => '<span class="badge badge-success">Disetujui Kepala JIC</span>',
                                                        'dicairkan' => '<span class="badge badge-success">Dicairkan</span>',
                                                        'ditolak' => '<span class="badge badge-danger">Ditolak</span>'
                                                    ];
                                                @endphp
                                                {!! $pencairanStatusBadges[$pencairan->status] ?? $pencairan->status !!}
                                            </td>
                                            <td>
                                                <a href="{{ route('pencairan-dana.show', $pencairan->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada pencairan dana</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
                            <a href="{{ route('anggaran-kegiatan.edit', $anggaran->id) }}" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if($canSubmit)
                            <form action="{{ route('anggaran-kegiatan.submit', $anggaran->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan anggaran kegiatan ini untuk persetujuan?')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-paper-plane"></i> Ajukan Persetujuan
                                </button>
                            </form>
                        @endif

                        @if($canDelete)
                            <form action="{{ route('anggaran-kegiatan.destroy', $anggaran->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus anggaran kegiatan ini? Data yang dihapus tidak dapat dikembalikan!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block mb-2">
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

                        @if(!$canEdit && !$canSubmit && !$canDelete && !$canApprove)
                            <p class="text-muted mb-0">Tidak ada aksi tersedia</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Progress Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="approval-steps">
                        @foreach($approvalSteps as $step)
                            <div class="step {{ $step['class'] }} mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="step-icon mr-3">
                                        <i class="fas fa-{{ $step['icon'] }} fa-2x {{ $step['iconColor'] }}"></i>
                                    </div>
                                    <div class="step-info">
                                        <div class="font-weight-bold">{{ $step['label'] }}</div>
                                        <small class="text-muted">{{ $step['statusText'] }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
            <form action="{{ route('anggaran-kegiatan.approve', $anggaran->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Anggaran</h5>
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
            <form action="{{ route('anggaran-kegiatan.reject', $anggaran->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Tolak Anggaran</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectCatatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectCatatan" name="catatan" rows="4" 
                                  placeholder="Masukkan alasan penolakan" required></textarea>
                        <small class="form-text text-muted">Catatan wajib diisi saat menolak pengajuan</small>
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

/* Button Styling */
.btn-block {
    width: 100%;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .approval-steps .step {
        padding-left: 50px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush