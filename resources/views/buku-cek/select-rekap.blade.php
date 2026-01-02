@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pilih Rekap Pengajuan</h1>
        <a href="{{ route('buku-cek.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cari Rekap Pengajuan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('buku-cek.create') }}">
                <div class="form-group">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Cari berdasarkan nomor rekap atau nama kegiatan..." 
                           value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    <!-- Rekap List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Rekap Pengajuan yang Disetujui</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Nomor Rekap</th>
                            <th>Kegiatan</th>
                            <th>Sisa Dana</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapList as $rekap)
                        <tr>
                            <td>{{ $rekap->nomor_rekap }}</td>
                            <td>{{ $rekap->pencairanDana->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                            <td class="text-success font-weight-bold">
                                Rp {{ number_format($rekap->sisa_dana, 0, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('buku-cek.create', ['rekap_id' => $rekap->id]) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i> Pilih
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                Tidak ada rekap pengajuan yang disetujui atau semua rekap sudah memiliki buku cek
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
            $(this).closest('form').submit();
        }.bind(this), 500);
    });
});
</script>
@endpush