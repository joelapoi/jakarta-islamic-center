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

    <!-- Debug Info (hanya untuk development) -->
    @if(config('app.debug'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-info-circle"></i> Debug Info:</strong><br>
        User ID: {{ auth()->id() }}<br>
        User Roles: {{ auth()->user()->roles->pluck('name')->implode(', ') }}<br>
        Status Buku Cek: <strong>{{ $bukuCek->status }}</strong><br>
        Can Be Signed: {{ $bukuCek->canBeSigned() ? 'Yes ✓' : 'No ✗' }}<br>
        Can Sign Permission: {{ $canSign ? 'Yes ✓' : 'No ✗' }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Buku Cek</h1>
        <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Buku Cek</h6>
                    <div>
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
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nomor Buku Cek</td>
                            <td width="5%">:</td>
                            <td>{{ $bukuCek->nomor_buku_cek }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Rekap Pengajuan</td>
                            <td>:</td>
                            <td>
                                <div>{{ $bukuCek->rekapPengajuan->nomor_rekap }}</div>
                                <small class="text-muted">
                                    <a href="{{ route('rekap-pengajuan.show', $bukuCek->rekapPengajuan->id) }}" class="text-primary">
                                        Lihat Detail Rekap
                                    </a>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kegiatan</td>
                            <td>:</td>
                            <td>
                                <div>{{ $bukuCek->rekapPengajuan->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</div>
                                @if($bukuCek->rekapPengajuan->pencairanDana->anggaranKegiatan ?? false)
                                <small class="text-muted">
                                    <a href="{{ route('anggaran-kegiatan.show', $bukuCek->rekapPengajuan->pencairanDana->anggaranKegiatan->id) }}" class="text-primary">
                                        Lihat Detail Kegiatan
                                    </a>
                                </small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Bank</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nama_bank }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nomor Rekening</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nomor_rekening ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Penerima</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nama_penerima }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold h5">
                                Rp {{ number_format($bukuCek->jumlah, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Keperluan</td>
                            <td>:</td>
                            <td>{{ $bukuCek->keperluan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Dibuat</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->created_at)->format('d F Y H:i') }}</td>
                        </tr>
                        @if($bukuCek->submitted_at)
                        <tr>
                            <td class="font-weight-bold">Diajukan Pada</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->submitted_at)->format('d F Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->signed_at)
                        <tr>
                            <td class="font-weight-bold">Ditandatangani Pada</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->signed_at)->format('d F Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->signed_by)
                        <tr>
                            <td class="font-weight-bold">Ditandatangani Oleh</td>
                            <td>:</td>
                            <td>{{ $bukuCek->signedBy->name ?? 'Kepala JIC' }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->confirmed_at)
                        <tr>
                            <td class="font-weight-bold">Dikonfirmasi Bank Pada</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->confirmed_at)->format('d F Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->confirmed_by)
                        <tr>
                            <td class="font-weight-bold">Dikonfirmasi Oleh</td>
                            <td>:</td>
                            <td>{{ $bukuCek->confirmedBy->name ?? 'Bank' }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->rejected_at)
                        <tr>
                            <td class="font-weight-bold">Ditolak Pada</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($bukuCek->rejected_at)->format('d F Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($bukuCek->rejected_by)
                        <tr>
                            <td class="font-weight-bold">Ditolak Oleh</td>
                            <td>:</td>
                            <td>{{ $bukuCek->rejectedBy->name ?? '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Rejection Reason Card -->
            @if($bukuCek->status === 'ditolak' && $bukuCek->alasan_penolakan)
            <div class="card shadow mb-4 border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-times-circle"></i> Alasan Penolakan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger mb-0">
                        {{ $bukuCek->alasan_penolakan }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Documents Card -->
            @if($bukuCek->documents && $bukuCek->documents->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($bukuCek->documents as $doc)
                        <a href="{{ $doc->file_url }}" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf text-danger"></i> {{ $doc->nama_dokumen }}
                            <small class="text-muted float-right">{{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}</small>
                        </a>
                        @endforeach
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
                        {{-- Edit Button --}}
                        @if($canEdit)
                        <a href="{{ route('buku-cek.edit', $bukuCek->id) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endif

                        {{-- Submit for Approval --}}
                        @if($canSubmit)
                        <form action="{{ route('buku-cek.submit', $bukuCek->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Yakin ingin mengajukan buku cek ini untuk ditandatangani?')">
                            @csrf
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-paper-plane"></i> Ajukan untuk TTD
                            </button>
                        </form>
                        @endif

                        {{-- Sign Button (Kepala JIC only) --}}
                        @if($canSign)
                        <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#signatureModal">
                            <i class="fas fa-signature"></i> Tanda Tangan
                        </button>
                        @endif

                        {{-- Bank Confirmation Button (Kadiv Umum) --}}
                        @if($canConfirm)
                        <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#bankConfirmModal">
                            <i class="fas fa-check-double"></i> Konfirmasi Bank
                        </button>
                        @endif

                        {{-- Cancel/Reject Button --}}
                        @if($canReject)
                        <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#cancelModal">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                        @endif

                        {{-- Delete Button --}}
                        @if($canDelete)
                        <form action="{{ route('buku-cek.destroy', $bukuCek->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku cek ini? Data yang dihapus tidak dapat dikembalikan!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endif

                        @if(!$canEdit && !$canSubmit && !$canSign && !$canConfirm && !$canReject && !$canDelete)
                        <p class="text-muted mb-0 text-center">Tidak ada aksi tersedia</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Progress
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress-steps">
                        @php
                            $steps = [
                                ['status' => 'draft', 'label' => 'Draft Dibuat', 'icon' => 'file-alt'],
                                ['status' => 'menunggu_ttd_kepala_jic', 'label' => 'Menunggu TTD', 'icon' => 'signature'],
                                ['status' => 'ditandatangani', 'label' => 'Ditandatangani', 'icon' => 'check'],
                                ['status' => 'dikonfirmasi_bank', 'label' => 'Dikonfirmasi Bank', 'icon' => 'check-double']
                            ];
                            
                            $currentStatusIndex = collect($steps)->search(function($step) use ($bukuCek) {
                                return $step['status'] === $bukuCek->status;
                            });
                        @endphp

                        @foreach($steps as $index => $step)
                            @php
                                $stepClass = '';
                                $iconColor = 'text-muted';
                                $stepStatus = 'Menunggu';

                                if ($bukuCek->status === 'ditolak') {
                                    if ($index < $currentStatusIndex) {
                                        $stepClass = 'completed';
                                        $iconColor = 'text-success';
                                        $stepStatus = 'Selesai';
                                    } else {
                                        $stepClass = 'pending';
                                        $iconColor = 'text-muted';
                                        $stepStatus = 'Menunggu';
                                    }
                                } else {
                                    if ($index < $currentStatusIndex) {
                                        $stepClass = 'completed';
                                        $iconColor = 'text-success';
                                        $stepStatus = 'Selesai';
                                    } elseif ($index == $currentStatusIndex) {
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

                        @if($bukuCek->status === 'ditolak')
                        <div class="step rejected mb-3">
                            <div class="d-flex align-items-center">
                                <div class="step-icon mr-3">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <div class="step-info">
                                    <div class="font-weight-bold text-danger">Ditolak</div>
                                    <small class="text-muted">Buku cek ditolak</small>
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

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('buku-cek.sign', $bukuCek->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Buku Cek</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Dengan menandatangani buku cek ini, Anda menyetujui pencairan dana sebesar:</p>
                    <h4 class="text-center text-success">Rp {{ number_format($bukuCek->jumlah, 0, ',', '.') }}</h4>
                    <p class="text-center">Kepada: <strong>{{ $bukuCek->nama_penerima }}</strong></p>
                    <hr>
                    <div class="alert alert-info">
                        <strong>Detail:</strong>
                        <ul class="mb-0">
                            <li>Bank: {{ $bukuCek->nama_bank }}</li>
                            <li>No. Rekening: {{ $bukuCek->nomor_rekening }}</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-signature"></i> Tanda Tangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bank Confirmation Modal -->
<div class="modal fade" id="bankConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('buku-cek.cash', $bukuCek->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Bank</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Konfirmasi bahwa dana telah dicairkan ke bank:</p>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%">Bank</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nama_bank }}</td>
                        </tr>
                        <tr>
                            <td>No. Rekening</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nomor_rekening }}</td>
                        </tr>
                        <tr>
                            <td>Penerima</td>
                            <td>:</td>
                            <td>{{ $bukuCek->nama_penerima }}</td>
                        </tr>
                        <tr>
                            <td>Jumlah</td>
                            <td>:</td>
                            <td class="text-success font-weight-bold">Rp {{ number_format($bukuCek->jumlah, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel/Reject Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('buku-cek.cancel', $bukuCek->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Tolak Buku Cek</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Anda akan menolak buku cek ini. Pastikan Anda memberikan alasan yang jelas.
                    </div>
                    <div class="form-group">
                        <label for="alasan_penolakan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="alasan_penolakan" 
                                  name="alasan_penolakan" 
                                  rows="4" 
                                  placeholder="Jelaskan alasan penolakan..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Tolak Buku Cek
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').not('.alert-danger').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Confirm signature
    $('form[action*="sign"]').on('submit', function(e) {
        const confirmed = confirm('Apakah Anda yakin ingin menandatangani buku cek ini?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });

    // Confirm bank confirmation
    $('form[action*="cash"]').on('submit', function(e) {
        const confirmed = confirm('Apakah Anda yakin dana telah dikonfirmasi oleh bank?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
/* Progress Steps Styling */
.progress-steps .step {
    position: relative;
    padding-left: 0;
}

.progress-steps .step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 30px;
    background-color: #e3e6f0;
}

.progress-steps .step.completed:not(:last-child)::after {
    background-color: #1cc88a;
}

.progress-steps .step.current:not(:last-child)::after {
    background-color: #4e73df;
}

.d-grid {
    display: grid;
    gap: 0.5rem;
}
</style>
@endpush