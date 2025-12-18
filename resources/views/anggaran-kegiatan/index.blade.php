@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
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
        <h1 class="h3 mb-0 text-gray-800">Anggaran Kegiatan</h1>
        @if($isStaff)
            <a href="{{ route('anggaran-kegiatan.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Anggaran Kegiatan
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Anggaran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($statistics['total_anggaran'], 0, ',', '.') }}
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disetujui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total_approved'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total_pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total_rejected'] }}</div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Anggaran Kegiatan</h6>
                </div>
                <div class="col-md-6">
                    <!-- Filter & Search Form -->
                    <form method="GET" action="{{ route('anggaran-kegiatan.index') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                    <option value="disetujui_kadiv" {{ request('status') == 'disetujui_kadiv' ? 'selected' : '' }}>Disetujui Kadiv</option>
                                    <option value="disetujui_kadiv_umum" {{ request('status') == 'disetujui_kadiv_umum' ? 'selected' : '' }}>Disetujui Kadiv Umum</option>
                                    <option value="disetujui_kepala_jic" {{ request('status') == 'disetujui_kepala_jic' ? 'selected' : '' }}>Disetujui Kepala JIC</option>
                                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" name="search" 
                                       placeholder="Cari kegiatan..." value="{{ request('search') }}">
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
                            <th width="12%">Kode Kegiatan</th>
                            <th width="20%">Nama Kegiatan</th>
                            <th width="15%">Anggaran</th>
                            <th width="12%">Tanggal Mulai</th>
                            <th width="12%">Tanggal Selesai</th>
                            <th width="12%">Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anggaran as $index => $item)
                            <tr>
                                <td>{{ $anggaran->firstItem() + $index }}</td>
                                <td>{{ $item->kode_kegiatan }}</td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td>Rp {{ number_format($item->anggaran_disetujui, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}</td>
                                <td>
                                    @switch($item->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @case('diajukan')
                                            <span class="badge badge-info">Diajukan</span>
                                            @break
                                        @case('disetujui_kadiv')
                                            <span class="badge badge-primary">Disetujui Kadiv</span>
                                            @break
                                        @case('disetujui_kadiv_umum')
                                            <span class="badge badge-primary">Disetujui Kadiv Umum</span>
                                            @break
                                        @case('disetujui_kepala_jic')
                                            <span class="badge badge-success">Disetujui Kepala JIC</span>
                                            @break
                                        @case('ditolak')
                                            <span class="badge badge-danger">Ditolak</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $item->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('anggaran-kegiatan.show', $item->id) }}" 
                                           class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($isStaff)
                                            {{-- Edit button - only for draft and ditolak --}}
                                            @if(in_array($item->status, ['draft', 'ditolak']))
                                                <a href="{{ route('anggaran-kegiatan.edit', $item->id) }}" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            {{-- Submit button - only for draft and ditolak --}}
                                            @if(in_array($item->status, ['draft', 'ditolak']))
                                                <form action="{{ route('anggaran-kegiatan.submit', $item->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin mengajukan anggaran kegiatan ini untuk disetujui?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" title="Submit">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Delete button - only for draft --}}
                                            @if($item->status === 'draft')
                                                <button type="button" class="btn btn-danger" 
                                                        data-toggle="modal" 
                                                        data-target="#deleteModal{{ $item->id }}" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Delete Confirmation Modal for each item --}}
                            @if($isStaff && $item->status === 'draft')
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus anggaran kegiatan ini?</p>
                                                <p><strong>Kode:</strong> {{ $item->kode_kegiatan }}</p>
                                                <p><strong>Nama:</strong> {{ $item->nama_kegiatan }}</p>
                                                <p class="text-danger"><strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan!</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <form action="{{ route('anggaran-kegiatan.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($anggaran->hasPages())
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div>
                            Menampilkan {{ $anggaran->firstItem() }} - {{ $anggaran->lastItem() }} dari {{ $anggaran->total() }} data
                        </div>
                    </div>
                    <div class="col-md-6">
                        <nav>
                            {{ $anggaran->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Submit form on search input enter key
    $('input[name="search"]').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });

    // Optional: Auto-submit search after typing stops (debounce)
    let searchTimeout;
    $('input[name="search"]').on('keyup', function(e) {
        if (e.which === 13) return; // Skip if Enter key
        
        clearTimeout(searchTimeout);
        const form = $(this).closest('form');
        
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 800); // Submit after 800ms of no typing
    });
});
</script>
@endpush