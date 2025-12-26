@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Rekap Pengajuan</h1>
        <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Rekap Pengajuan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('rekap-pengajuan.store') }}" method="POST" id="formRekapPengajuan">
                        @csrf
                        
                        <div class="form-group">
                            <label for="pencairan_dana_id">Pencairan Dana <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('pencairan_dana_id') is-invalid @enderror" 
                                       value="{{ $selectedPencairan ? $selectedPencairan->nomor_pencairan : 'Pilih pencairan dana' }}" 
                                       readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            data-toggle="modal" data-target="#pencairanModal">
                                        <i class="fas fa-search"></i> Pilih
                                    </button>
                                </div>
                            </div>
                            @error('pencairan_dana_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($selectedPencairan)
                            <input type="hidden" name="pencairan_dana_id" value="{{ $selectedPencairan->id }}">
                            
                            <div class="alert alert-info">
                                <h6 class="font-weight-bold mb-2">Informasi Pencairan Dana:</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%">Nomor Pencairan</td>
                                        <td width="5%">:</td>
                                        <td>{{ $selectedPencairan->nomor_pencairan }}</td>
                                    </tr>
                                    <tr>
                                        <td>Kegiatan</td>
                                        <td>:</td>
                                        <td>{{ $selectedPencairan->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Jumlah Pencairan</td>
                                        <td>:</td>
                                        <td class="font-weight-bold text-success">
                                            Rp {{ number_format($selectedPencairan->jumlah_pencairan, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Dicairkan</td>
                                        <td>:</td>
                                        <td>{{ \Carbon\Carbon::parse($selectedPencairan->disbursed_at)->isoFormat('DD MMMM YYYY, HH:mm') }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="total_pengeluaran">Total Pengeluaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" 
                                       class="form-control @error('total_pengeluaran') is-invalid @enderror" 
                                       id="total_pengeluaran" 
                                       name="total_pengeluaran" 
                                       placeholder="0" 
                                       value="{{ old('total_pengeluaran') }}"
                                       required>
                            </div>
                            <small class="form-text text-muted">Total pengeluaran dari dana yang telah dicairkan</small>
                            @error('total_pengeluaran')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($selectedPencairan)
                            <div id="sisaDanaInfo" style="display: none;">
                                <div class="alert alert-success">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Sisa Dana:</strong>
                                        </div>
                                        <div>
                                            <h4 class="mb-0" id="sisa_dana_display">Rp 0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="catatan">Catatan / Keterangan</label>
                            <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                      id="catatan" 
                                      name="catatan" 
                                      rows="5" 
                                      placeholder="Jelaskan rincian pengeluaran dan penggunaan dana...">{{ old('catatan') }}</textarea>
                            <small class="form-text text-muted">Maksimal 1000 karakter</small>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" name="submit_type" value="draft" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan sebagai Draft
                            </button>
                            @if($selectedPencairan)
                                <button type="submit" name="submit_type" value="submit" class="btn btn-success" id="btnSubmitApproval">
                                    <i class="fas fa-paper-plane"></i> Simpan & Ajukan
                                </button>
                            @endif
                            <a href="{{ route('rekap-pengajuan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Alur Persetujuan:</h6>
                    <ol class="pl-3">
                        <li>Draft dibuat</li>
                        <li>Diajukan untuk persetujuan</li>
                        <li>Disetujui Kadiv Umum / Kepala JIC</li>
                        <li>Proses Buku Cek</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Catatan:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Pilih pencairan dana yang sudah dicairkan</li>
                        <li>Total pengeluaran tidak boleh melebihi jumlah pencairan</li>
                        <li>Jelaskan rincian pengeluaran dengan detail</li>
                        <li>Sisa dana akan dihitung otomatis</li>
                        <li>Setelah diajukan, data tidak dapat diubah kecuali ditolak</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Pencairan -->
<div class="modal fade" id="pencairanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pencairan Dana</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search Form -->
                <form method="GET" action="{{ route('rekap-pengajuan.create') }}" class="mb-3">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Cari nomor pencairan atau kegiatan..."
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('rekap-pengajuan.create') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nomor</th>
                                <th>Kegiatan</th>
                                <th>Jumlah</th>
                                <th>Tanggal Cair</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pencairanList as $item)
                                <tr>
                                    <td>{{ $item->nomor_pencairan }}</td>
                                    <td>{{ $item->anggaranKegiatan->nama_kegiatan ?? '-' }}</td>
                                    <td>Rp {{ number_format($item->jumlah_pencairan, 0, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->disbursed_at)->isoFormat('DD MMM YYYY') }}</td>
                                    <td>
                                        <a href="{{ route('rekap-pengajuan.create', ['pencairan_id' => $item->id, 'search' => request('search')]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-check"></i> Pilih
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        @if(request('search'))
                                            Tidak ada pencairan dana yang sesuai dengan pencarian
                                        @else
                                            Tidak ada pencairan dana yang sudah dicairkan
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($pencairanList->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $pencairanList->links() }}
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($selectedPencairan)
        const jumlahPencairan = {{ $selectedPencairan->jumlah_pencairan }};
    @else
        const jumlahPencairan = 0;
    @endif

    // Format currency input
    $('#total_pengeluaran').on('keyup', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
        
        // Calculate and display sisa dana
        if (jumlahPencairan > 0 && value) {
            const pengeluaran = parseFloat(value) || 0;
            const sisaDana = jumlahPencairan - pengeluaran;
            
            if (pengeluaran > jumlahPencairan) {
                $(this).addClass('is-invalid');
                $('#sisaDanaInfo').hide();
                showValidationMessage('Total pengeluaran melebihi jumlah pencairan');
            } else {
                $(this).removeClass('is-invalid');
                hideValidationMessage();
                $('#sisa_dana_display').text(formatRupiah(sisaDana));
                $('#sisaDanaInfo').show();
                
                // Show/hide submit button
                if (pengeluaran > 0) {
                    $('#btnSubmitApproval').show();
                } else {
                    $('#btnSubmitApproval').hide();
                }
            }
        } else {
            $('#sisaDanaInfo').hide();
            $('#btnSubmitApproval').hide();
        }
    });

    // Trigger calculation on page load if there's old input
    @if(old('total_pengeluaran'))
        $('#total_pengeluaran').trigger('keyup');
    @endif

    // Show modal if search parameter exists
    @if(request()->has('search') || request()->has('pencairan_id'))
        @if(!$selectedPencairan && request()->has('search'))
            $('#pencairanModal').modal('show');
        @endif
    @endif

    // Helper: Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Helper: Format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Show validation message
    function showValidationMessage(message) {
        let feedback = $('#total_pengeluaran').siblings('.invalid-feedback');
        if (feedback.length === 0) {
            $('#total_pengeluaran').after('<div class="invalid-feedback d-block">' + message + '</div>');
        } else {
            feedback.text(message).addClass('d-block');
        }
    }

    // Hide validation message
    function hideValidationMessage() {
        $('#total_pengeluaran').siblings('.invalid-feedback').removeClass('d-block');
    }

    // Confirm before submit
    $('#formRekapPengajuan').on('submit', function(e) {
        const submitType = $('button[type="submit"]:focus').val();
        
        if (submitType === 'submit') {
            if (!confirm('Apakah Anda yakin ingin menyimpan dan langsung mengajukan rekap pengajuan ini untuk disetujui?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush