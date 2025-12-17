@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Dashboard</h1>
        
        <div class="row" id="stats-container">
            <div class="col-md-3 mb-3">
                <div class="card text-white" style="background: linear-gradient(135deg, #2d8659 0%, #48a578 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Anggaran</h6>
                                <h2 class="mb-0" id="total-anggaran">-</h2>
                            </div>
                            <i class="fas fa-money-bill fa-2x" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white" style="background: linear-gradient(135deg, #48a578 0%, #5cb888 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Pencairan</h6>
                                <h2 class="mb-0" id="total-pencairan">-</h2>
                            </div>
                            <i class="fas fa-hand-holding-usd fa-2x" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white" style="background: linear-gradient(135deg, #5cb888 0%, #70c99a 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Rekap</h6>
                                <h2 class="mb-0" id="total-rekap">-</h2>
                            </div>
                            <i class="fas fa-file-invoice fa-2x" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white" style="background: linear-gradient(135deg, #d4af37 0%, #e5c158 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total LPJ</h6>
                                <h2 class="mb-0" id="total-lpj">-</h2>
                            </div>
                            <i class="fas fa-clipboard-check fa-2x" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4" style="border-left: 4px solid #2d8659;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle fa-3x me-3" style="color: #2d8659;"></i>
                    <div>
                        <h5 class="mb-1">Assalamualaikum, <span id="user-name-display"></span>!</h5>
                        <p class="mb-0 text-muted">Your roles: <span id="user-roles"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const user = getUser();
        
        if (user) {
            document.getElementById('user-name-display').textContent = user.name;
            document.getElementById('user-roles').innerHTML = user.roles.map(role => 
                `<span class="badge" style="background-color: #2d8659; color: #ffffffff">${role}</span>`
            ).join(' ');
        }
        
        // Load statistics
        await loadStatistics();
    });
    
    async function loadStatistics() {
        // Load statistics without showing loading spinner (better UX for dashboard)
        
        // Load Anggaran statistics
        const anggaranStats = await fetch(`${API_BASE_URL}/anggaran-kegiatan/statistics`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        }).then(res => res.json()).catch(() => null);
        
        if (anggaranStats && anggaranStats.success) {
            document.getElementById('total-anggaran').textContent = anggaranStats.data.total;
        }
        
        // Load Pencairan statistics
        const pencairanStats = await fetch(`${API_BASE_URL}/pencairan-dana/statistics`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        }).then(res => res.json()).catch(() => null);
        
        if (pencairanStats && pencairanStats.success) {
            document.getElementById('total-pencairan').textContent = pencairanStats.data.total;
        }
        
        // Load Rekap statistics
        const rekapStats = await fetch(`${API_BASE_URL}/rekap-pengajuan/statistics`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        }).then(res => res.json()).catch(() => null);
        
        if (rekapStats && rekapStats.success) {
            document.getElementById('total-rekap').textContent = rekapStats.data.total;
        }
        
        // Load LPJ statistics
        const lpjStats = await fetch(`${API_BASE_URL}/lpj-kegiatan/statistics`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        }).then(res => res.json()).catch(() => null);
        
        if (lpjStats && lpjStats.success) {
            document.getElementById('total-lpj').textContent = lpjStats.data.total;
        }
    }
</script>
@endpush