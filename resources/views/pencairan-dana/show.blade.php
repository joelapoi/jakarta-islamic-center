@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pencairan Dana</h1>
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pencairan Dana</h6>
                    <div>
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
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Pencairan</td>
                            <td width="5%">:</td>
                            <td>{{ $pencairan->nomor_pencairan }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div>{{ $pencairan->anggaranKegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">Kode: {{ $pencairan->anggaranKegiatan->kode_kegiatan }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pencairan</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold h5">
                                Rp {{ number_format($pencairan->jumlah_pencairan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Keperluan</td>
                            <td>:</td>
                            <td style="white-space: pre-wrap;">{{ $pencairan->keperluan }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Pengajuan</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($pencairan->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                        @if($pencairan->disbursed_at)
                        <tr>
                            <td class="font-weight-bold">Tanggal Dicairkan</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($pencairan->disbursed_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="font-weight-bold">Dibuat Oleh</td>
                            <td>:</td>
                            <td>{{ $pencairan->creator->name ?? '-' }}</td>
                        </tr>
                        @if($pencairan->approved_by && $pencairan->approver)
                        <tr>
                            <td class="font-weight-bold">Disetujui Oleh</td>
                            <td>:</td>
                            <td>{{ $pencairan->approver->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Disetujui</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($pencairan->approved_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                        </tr>
                        @endif
                        @if($pencairan->catatan)
                        <tr>
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td>
                                <div class="alert alert-info mb-0">{{ $pencairan->catatan }}</div>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Anggaran Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Anggaran Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Total Anggaran</small>
                                <h5 class="font-weight-bold text-primary">
                                    Rp {{ number_format($pencairan->anggaranKegiatan->anggaran_disetujui, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Total Pencairan</small>
                                <h5 class="font-weight-bold text-warning">
                                    Rp {{ number_format($totalPencairan, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Sisa Anggaran</small>
                                <h5 class="font-weight-bold text-success">
                                    Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('anggaran-kegiatan.show', $pencairan->anggaranKegiatan) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Detail Kegiatan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Documents Card -->
            @if($pencairan->documents && $pencairan->documents->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($pencairan->documents as $doc)
                        <a href="{{ $doc->file_url }}" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf text-danger"></i> {{ $doc->nama_dokumen }}
                            <small class="text-muted float-right">
                                {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                            </small>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Rekap Pengajuan Card -->
            @if($pencairan->rekapPengajuan && $pencairan->rekapPengajuan->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    @foreach($pencairan->rekapPengajuan as $rekap)
                    <p><strong>Nomor Rekap:</strong> {{ $rekap->nomor_rekap }}</p>
                    <p><strong>Status:</strong> 
                        @if($rekap->status === 'draft')
                            <span class="badge badge-secondary">Draft</span>
                        @elseif($rekap->status === 'diajukan')
                            <span class="badge badge-info">Diajukan</span>
                        @elseif($rekap->status === 'disetujui')
                            <span class="badge badge-success">Disetujui</span>
                        @endif
                    </p>
                    <a href="{{ route('rekap-pengajuan.show', $rekap) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> Lihat Detail Rekap
                    </a>
                    @endforeach
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
                        @php
                            $user = auth()->user();
                            $isCreator = $pencairan->created_by === $user->id;
                            $canEdit = in_array($pencairan->status, ['draft', 'ditolak']) && ($isCreator || $user->hasAnyRole(['super_admin', 'admin']));
                            $canDelete = $pencairan->status === 'draft' && ($isCreator || $user->hasAnyRole(['super_admin', 'admin']));
                            $canSubmit = in_array($pencairan->status, ['draft', 'ditolak']) && ($isCreator || $user->hasAnyRole(['super_admin', 'admin']));
                            
                            // Check approval permission
                            $canApprove = false;
                            if ($pencairan->status === 'diajukan' && ($user->hasRole('kadiv_umum') || $user->hasAnyRole(['super_admin', 'admin']))) {
                                $canApprove = true;
                            } elseif ($pencairan->status === 'disetujui_kadiv_umum' && ($user->hasRole('kepala_jic') || $user->hasAnyRole(['super_admin', 'admin']))) {
                                $canApprove = true;
                            }
                            
                            $canDisburse = $pencairan->status === 'disetujui_kepala_jic' && $user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin']);
                        @endphp

                        @if($canEdit)
                        <a href="{{ route('pencairan-dana.edit', $pencairan) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endif

                        @if($canSubmit)
                        <form action="{{ route('pencairan-dana.submit', $pencairan) }}" method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin mengajukan pencairan dana ini untuk persetujuan?')">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-paper-plane"></i> Ajukan Persetujuan
                            </button>
                        </form>
                        @endif

                        @if($canDelete)
                        <form action="{{ route('pencairan-dana.destroy', $pencairan) }}" method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pencairan dana ini? Data yang dihapus tidak dapat dikembalikan!')">
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

                        @if($canDisburse)
                        <form action="{{ route('pencairan-dana.disburse', $pencairan) }}" method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin mencairkan dana ini? Pastikan semua proses sudah selesai.')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-money-bill-wave"></i> Cairkan Dana
                            </button>
                        </form>
                        @endif

                        @if(!$canEdit && !$canDelete && !$canSubmit && !$canApprove && !$canDisburse)
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
                    @php
                        $steps = [
                            ['status' => 'draft', 'label' => 'Draft', 'icon' => 'file-alt'],
                            ['status' => 'diajukan', 'label' => 'Diajukan', 'icon' => 'paper-plane'],
                            ['status' => 'disetujui_kadiv_umum', 'label' => 'Kadiv Umum', 'icon' => 'user-check'],
                            ['status' => 'disetujui_kepala_jic', 'label' => 'Kepala JIC', 'icon' => 'check-circle'],
                            ['status' => 'dicairkan', 'label' => 'Dicairkan', 'icon' => 'money-bill-wave']
                        ];
                        
                        $currentStatus = $pencairan->status;
                        $statusIndex = collect($steps)->search(function($step) use ($currentStatus) {
                            return $step['status'] === $currentStatus;
                        });
                    @endphp

                    <div class="approval-steps">
                        @foreach($steps as $index => $step)
                            @php
                                if ($currentStatus === 'ditolak') {
                                    $stepClass = $index < $statusIndex ? 'completed' : 'pending';
                                    $iconColor = $index < $statusIndex ? 'text-success' : 'text-muted';
                                    $stepStatus = $index < $statusIndex ? 'Selesai' : 'Menunggu';
                                } else {
                                    if ($index < $statusIndex) {
                                        $stepClass = 'completed';
                                        $iconColor = 'text-success';
                                        $stepStatus = 'Selesai';
                                    } elseif ($index === $statusIndex) {
                                        $stepClass = 'current';
                                        $iconColor = 'text-primary';
                                        $stepStatus = 'Sedang Diproses';
                                    } else {
                                        $stepClass = 'pending';
                                        $iconColor = 'text-muted';
                                        $stepStatus = 'Menunggu';
                                    }
                                }
                            @endphp

                            <div class="step {{ $stepClass }} mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="step-icon mr-3">
                                        <i class="fas fa-{{ $step['icon'] }} fa-2x {{ $iconColor }}"></i>
                                    </div>
                                    <div class="step-info">
                                        <div class="font-weight-bold">{{ $step['label'] }}</div>
                                        <small class="text-muted">{{ $stepStatus }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($currentStatus === 'ditolak')
                        <div class="step rejected mb-3">
                            <div class="d-flex align-items-center">
                                <div class="step-icon mr-3">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <div class="step-info">
                                    <div class="font-weight-bold text-danger">Ditolak</div>
                                    <small class="text-muted">Pengajuan ditolak</small>
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
            <form action="{{ route('pencairan-dana.approve', $pencairan) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Pencairan Dana</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" 
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
            <form action="{{ route('pencairan-dana.reject', $pencairan) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Tolak Pencairan Dana</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reject_catatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                  id="reject_catatan" 
                                  name="catatan" 
                                  rows="4" 
                                  placeholder="Masukkan alasan penolakan" 
                                  required></textarea>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>

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