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
        <h1 class="h3 mb-0 text-gray-800">Buku Cek</h1>
        @if(auth()->user()->hasAnyRole(['kadiv_umum', 'kepala_jic', 'admin', 'super_admin']))
        <a href="{{ route('buku-cek.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Buku Cek
        </a>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Buku Cek</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu TTD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['menunggu_ttd_kepala_jic'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-signature fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dikonfirmasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['dikonfirmasi_bank'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ditolak</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['ditolak'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Buku Cek</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search Form -->
                    <form method="GET" action="{{ route('buku-cek.index') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="menunggu_ttd_kepala_jic" {{ request('status') == 'menunggu_ttd_kepala_jic' ? 'selected' : '' }}>Menunggu TTD Kepala JIC</option>
                                    <option value="ditandatangani" {{ request('status') == 'ditandatangani' ? 'selected' : '' }}>Ditandatangani</option>
                                    <option value="dikonfirmasi_bank" {{ request('status') == 'dikonfirmasi_bank' ? 'selected' : '' }}>Dikonfirmasi Bank</option>
                                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="search" 
                                       placeholder="Cari buku cek..." 
                                       value="{{ request('search') }}">
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
                            <th width="12%">Nomor Buku Cek</th>
                            <th width="12%">Nomor Rekap</th>
                            <th width="20%">Kegiatan</th>
                            <th width="12%">Jumlah</th>
                            <th width="12%">Tanggal</th>
                            <th width="12%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bukuCek as $index => $item)
                        <tr>
                            <td>{{ $bukuCek->firstItem() + $index }}</td>
                            <td>{{ $item->nomor_buku_cek ?? $item->nomor_cek }}</td>
                            <td>{{ $item->rekapPengajuan->nomor_rekap ?? '-' }}</td>
                            <td>{{ $item->rekapPengajuan->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                            <td>{{ $item->formatted_nominal ?? $item->formatted_jumlah ?? 'Rp 0' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                            <td>{!! $item->status_badge !!}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('buku-cek.show', $item->id) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Edit button: Hanya pembuat atau admin yang bisa edit saat status draft --}}
                                    @if($item->canBeEdited() && ($item->created_by === auth()->id() || auth()->user()->hasAnyRole(['admin', 'super_admin'])))
                                        <a href="{{ route('buku-cek.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Delete button: Hanya pembuat atau admin yang bisa hapus saat status draft --}}
                                    @if($item->canBeDeleted() && ($item->created_by === auth()->id() || auth()->user()->hasAnyRole(['admin', 'super_admin'])))
                                        <form action="{{ route('buku-cek.destroy', $item->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku cek ini? Data yang dihapus tidak dapat dikembalikan!')">
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
                            <td colspan="8" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="text-muted">
                        Menampilkan {{ $bukuCek->firstItem() ?? 0 }} - {{ $bukuCek->lastItem() ?? 0 }} dari {{ $bukuCek->total() }} data
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        {{ $bukuCek->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit search form when typing (with delay)
    let searchTimeout;
    $('input[name="search"]').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush