@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Rekap Pengajuan Kegiatan</h1>
        @if(auth()->user()->hasAnyRole(['staff', 'super_admin', 'admin']))
        <a href="{{ route('rekap-pengajuan.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Rekap Pengajuan
        </a>
        @endif
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

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengeluaran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($statistics['total_pengeluaran'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sisa Dana</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($statistics['total_sisa_dana'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['by_status']['diajukan'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Disetujui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['by_status']['disetujui'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Rekap Pengajuan</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search Form -->
                    <form action="{{ route('rekap-pengajuan.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Cari rekap...">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Nomor Rekap</th>
                            <th width="15%">Pencairan</th>
                            <th width="18%">Kegiatan</th>
                            <th width="12%">Total Pengeluaran</th>
                            <th width="12%">Sisa Dana</th>
                            <th width="12%">Status</th>
                            <th width="14%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $index => $item)
                        <tr>
                            <td>{{ $rekap->firstItem() + $index }}</td>
                            <td>{{ $item->nomor_rekap }}</td>
                            <td>{{ $item->pencairanDana->nomor_pencairan ?? '-' }}</td>
                            <td>{{ $item->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                            <td>Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                            <td class="text-success font-weight-bold">
                                Rp {{ number_format($item->sisa_dana, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($item->status === 'draft')
                                    <span class="badge badge-secondary">Draft</span>
                                @elseif($item->status === 'diajukan')
                                    <span class="badge badge-info">Diajukan</span>
                                @elseif($item->status === 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($item->status === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('rekap-pengajuan.show', $item) }}" 
                                       class="btn btn-info" 
                                       title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(in_array($item->status, ['draft', 'ditolak']) && ($item->pencairanDana->created_by === auth()->id() || auth()->user()->hasAnyRole(['super_admin', 'admin'])))
                                        <a href="{{ route('rekap-pengajuan.edit', $item) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('rekap-pengajuan.submit', $item) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin mengajukan rekap pengajuan ini untuk disetujui?')">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" title="Submit">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($item->status === 'draft' && ($item->pencairanDana->created_by === auth()->id() || auth()->user()->hasAnyRole(['super_admin', 'admin'])))
                                        <form action="{{ route('rekap-pengajuan.destroy', $item) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekap pengajuan ini? Data yang dihapus tidak dapat dikembalikan!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data rekap pengajuan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div>
                        Menampilkan {{ $rekap->firstItem() ?? 0 }} - {{ $rekap->lastItem() ?? 0 }} dari {{ $rekap->total() }} data
                    </div>
                </div>
                <div class="col-md-6">
                    <nav>
                        {{ $rekap->links() }}
                    </nav>
                </div>
            </div>
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
@endpush