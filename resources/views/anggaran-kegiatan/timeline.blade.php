@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Timeline Persetujuan</h1>
        <a href="{{ route('anggaran-kegiatan.show', $anggaran->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Anggaran Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Anggaran</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
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
                            <td class="font-weight-bold">Status</td>
                            <td>:</td>
                            <td>
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
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Riwayat Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($timeline as $item)
                            <div class="timeline-item {{ $item['completed'] ? 'completed' : '' }}">
                                <div class="timeline-marker {{ $item['completed'] ? 'bg-success' : 'bg-muted' }}">
                                    <i class="fas fa-{{ $item['completed'] ? 'check-circle' : 'circle' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $item['label'] }}</h6>
                                    @if($item['user'])
                                        <p class="mb-0 text-muted small">Oleh: {{ $item['user'] }}</p>
                                    @endif
                                    @if($item['date'])
                                        <p class="mb-0 text-muted small">
                                            {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y H:i') }}
                                        </p>
                                    @endif
                                    @if($item['catatan'])
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small>{{ $item['catatan'] }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Timeline Styling */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: calc(100% - 20px);
    background-color: #e3e6f0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-item.completed::before {
    background-color: #1cc88a;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.timeline-marker.bg-success {
    background-color: #1cc88a;
}

.timeline-marker.bg-muted {
    background-color: #858796;
}

.timeline-content {
    background: #f8f9fc;
    border-radius: 5px;
    padding: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .timeline-item {
        padding-left: 50px;
    }
    
    .timeline-marker {
        width: 35px;
        height: 35px;
    }
}
</style>
@endpush