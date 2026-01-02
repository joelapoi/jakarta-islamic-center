@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">LPJ Kegiatan</h1>
        @if(auth()->user()->hasRole('staff'))
        <a href="{{ route('lpj-kegiatan.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat LPJ Kegiatan
        </a>
        @endif
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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total LPJ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Diajukan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['diajukan'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['disetujui'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
            <h6 class="m-0 font-weight-bold text-primary">Daftar LPJ Kegiatan</h6>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('lpj-kegiatan.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nomor LPJ atau kegiatan..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="anggaran_kegiatan_id" class="form-control">
                                <option value="">Semua Kegiatan</option>
                                @foreach($anggaranList as $anggaran)
                                    <option value="{{ $anggaran->id }}" {{ request('anggaran_kegiatan_id') == $anggaran->id ? 'selected' : '' }}>
                                        {{ $anggaran->nama_kegiatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Nomor LPJ</th>
                            <th width="20%">Kegiatan</th>
                            <th width="12%">Tanggal Selesai</th>
                            <th width="15%">Total Realisasi</th>
                            <th width="12%">Tanggal LPJ</th>
                            <th width="12%">Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lpj as $index => $item)
                        <tr>
                            <td>{{ $lpj->firstItem() + $index }}</td>
                            <td>{{ $item->nomor_lpj }}</td>
                            <td>
                                @if($item->anggaranKegiatan)
                                    <strong>{{ $item->anggaranKegiatan->kode_kegiatan }}</strong><br>
                                    {{ $item->anggaranKegiatan->nama_kegiatan }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->anggaranKegiatan)
                                    {{ \Carbon\Carbon::parse($item->anggaranKegiatan->tanggal_selesai)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->total_realisasi, 0, ',', '.') }}</td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                @if($item->status == 'draft')
                                    <span class="badge badge-secondary">Draft</span>
                                @elseif($item->status == 'diajukan')
                                    <span class="badge badge-info">Diajukan</span>
                                @elseif($item->status == 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($item->status == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('lpj-kegiatan.show', $item->id) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(auth()->user()->hasRole('staff') && ($item->created_by == auth()->id() || auth()->user()->hasAnyRole(['super_admin', 'admin'])))
                                        @if(in_array($item->status, ['draft', 'ditolak']))
                                            <a href="{{ route('lpj-kegiatan.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if(in_array($item->status, ['draft', 'ditolak']))
                                            <form action="{{ route('lpj-kegiatan.submit', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan LPJ ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" title="Submit">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($item->status == 'draft')
                                            <form action="{{ route('lpj-kegiatan.destroy', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus LPJ ini? Data yang dihapus tidak dapat dikembalikan!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data LPJ kegiatan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="text-muted">
                        Menampilkan {{ $lpj->firstItem() ?? 0 }} - {{ $lpj->lastItem() ?? 0 }} dari {{ $lpj->total() }} data
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="float-right">
                        {{ $lpj->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection