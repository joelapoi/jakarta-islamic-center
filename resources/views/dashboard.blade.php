@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Welcome Card -->
        <div class="card mb-4" style="border-left: 4px solid #2d8659;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle fa-3x me-3" style="color: #2d8659;"></i>
                    <div>
                        <h5 class="mb-1">
                            Assalamualaikum, <strong>{{ $user->name }}</strong>!
                        </h5>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-user-tag me-1"></i>
                            Your roles: 
                            @foreach($userRoles as $role)
                                <span class="badge" style="background-color: #2d8659; color: #ffffffff ">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <!-- Total Anggaran -->
            <div class="col-md-3 mb-3">
                <div class="card text-white h-100" style="background: linear-gradient(135deg, #2d8659 0%, #48a578 100%); border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Total Anggaran
                                </h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($stats['anggaran']['total']) }}</h2>
                                <small class="text-white-50">
                                    <i class="fas fa-circle text-warning me-1" style="font-size: 6px;"></i>
                                    Draft: {{ $stats['anggaran']['draft'] }} | 
                                    Diajukan: {{ $stats['anggaran']['diajukan'] }}
                                </small>
                            </div>
                            <i class="fas fa-money-bill fa-2x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('anggaran-kegiatan.index') }}" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Total Pencairan -->
            <div class="col-md-3 mb-3">
                <div class="card text-white h-100" style="background: linear-gradient(135deg, #48a578 0%, #5cb888 100%); border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">
                                    <i class="fas fa-hand-holding-usd me-1"></i>
                                    Total Pencairan
                                </h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($stats['pencairan']['total']) }}</h2>
                                <small class="text-white-50">
                                    <i class="fas fa-circle text-warning me-1" style="font-size: 6px;"></i>
                                    Pending: {{ $stats['pencairan']['pending'] }} | 
                                    Dicairkan: {{ $stats['pencairan']['disbursed'] }}
                                </small>
                            </div>
                            <i class="fas fa-hand-holding-usd fa-2x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('pencairan-dana.index') }}" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Total Rekap -->
            <div class="col-md-3 mb-3">
                <div class="card text-white h-100" style="background: linear-gradient(135deg, #5cb888 0%, #70c99a 100%); border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    Total Rekap
                                </h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($stats['rekap']['total']) }}</h2>
                                <small class="text-white-50">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Rekap Pengajuan Dana
                                </small>
                            </div>
                            <i class="fas fa-file-invoice fa-2x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('rekap-pengajuan.index') }}" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Total LPJ -->
            <div class="col-md-3 mb-3">
                <div class="card text-white h-100" style="background: linear-gradient(135deg, #d4af37 0%, #e5c158 100%); border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">
                                    <i class="fas fa-clipboard-check me-1"></i>
                                    Total LPJ
                                </h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($stats['lpj']['total']) }}</h2>
                                <small class="text-white-50">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Laporan Pertanggungjawaban
                                </small>
                            </div>
                            <i class="fas fa-clipboard-check fa-2x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('lpj-kegiatan.index') }}" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Alert (Only for Approvers) -->
        @if(!empty($pendingApprovals))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #ffc107;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-bell me-1"></i>
                            Menunggu Approval Anda
                        </h5>
                        <div class="mb-0">
                            @if(isset($pendingApprovals['anggaran']) && $pendingApprovals['anggaran'] > 0)
                                <div class="mb-1">
                                    <i class="fas fa-file-alt me-1"></i>
                                    <strong>Anggaran Kegiatan:</strong> 
                                    <span class="badge bg-warning text-dark">{{ $pendingApprovals['anggaran'] }} item</span>
                                    <a href="{{ route('anggaran-kegiatan.index', ['status' => 'diajukan']) }}" class="ms-2 text-decoration-none">
                                        <small>Lihat <i class="fas fa-arrow-right"></i></small>
                                    </a>
                                </div>
                            @endif
                            @if(isset($pendingApprovals['pencairan']) && $pendingApprovals['pencairan'] > 0)
                                <div>
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <strong>Pencairan Dana:</strong> 
                                    <span class="badge bg-warning text-dark">{{ $pendingApprovals['pencairan'] }} item</span>
                                    <a href="{{ route('pencairan-dana.index', ['status' => 'diajukan']) }}" class="ms-2 text-decoration-none">
                                        <small>Lihat <i class="fas fa-arrow-right"></i></small>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Recent Activities Section -->
        <div class="row mt-4">
            <!-- Recent Anggaran Kegiatan -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white" style="border-bottom: 2px solid #2d8659;">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2" style="color: #2d8659;"></i>
                            Aktivitas Terbaru - Anggaran Kegiatan
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentAnggaran as $item)
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-file-alt" style="color: #2d8659;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <a href="{{ route('anggaran-kegiatan.show', $item->id) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ $item->nama_kegiatan }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-code me-1"></i>{{ $item->kode_kegiatan }}
                                    </small>
                                    <div class="mt-1">
                                        <span class="badge 
                                            @if($item->status == 'draft') bg-secondary
                                            @elseif($item->status == 'diajukan') bg-primary
                                            @elseif($item->status == 'ditolak') bg-danger
                                            @elseif(str_contains($item->status, 'disetujui')) bg-success
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-user me-1"></i>{{ $item->creator->name }}
                                        </small>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-clock me-1"></i>{{ $item->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Belum ada aktivitas</p>
                            </div>
                        @endforelse
                        
                        @if($recentAnggaran->count() > 0)
                            <div class="text-center mt-3">
                                <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>Lihat Semua
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Pencairan Dana -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white" style="border-bottom: 2px solid #48a578;">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2" style="color: #48a578;"></i>
                            Aktivitas Terbaru - Pencairan Dana
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentPencairan as $item)
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-money-bill-wave" style="color: #48a578;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <a href="{{ route('pencairan-dana.show', $item->id) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ $item->anggaranKegiatan->nama_kegiatan ?? 'N/A' }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-code me-1"></i>{{ $item->nomor_pencairan }}
                                    </small>
                                    <div class="mt-1">
                                        <span class="badge 
                                            @if($item->status == 'draft') bg-secondary
                                            @elseif($item->status == 'diajukan') bg-primary
                                            @elseif($item->status == 'ditolak') bg-danger
                                            @elseif($item->status == 'dicairkan') bg-success
                                            @elseif(str_contains($item->status, 'disetujui')) bg-info
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-user me-1"></i>{{ $item->creator->name }}
                                        </small>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-clock me-1"></i>{{ $item->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Belum ada aktivitas</p>
                            </div>
                        @endforelse
                        
                        @if($recentPencairan->count() > 0)
                            <div class="text-center mt-3">
                                <a href="{{ route('pencairan-dana.index') }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>Lihat Semua
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header bg-white" style="border-bottom: 2px solid #2d8659;">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2" style="color: #2d8659;"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('anggaran-kegiatan.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus-circle me-2"></i>Buat Anggaran Baru
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('pencairan-dana.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus-circle me-2"></i>Buat Pencairan Baru
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('lpj-kegiatan.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus-circle me-2"></i>Buat LPJ Baru
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-user-cog me-2"></i>Kelola Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
    
    .alert {
        border-radius: 8px;
    }
</style>
@endpush