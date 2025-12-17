@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Anggaran Kegiatan</h1>
        <div>
            <button type="button" class="btn btn-info" id="btnTimeline">
                <i class="fas fa-history"></i> Timeline
            </button>
            <a href="{{ route('anggaran-kegiatan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan</h6>
                    <div id="statusBadge"></div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="font-weight-bold">Kode Kegiatan</td>
                            <td width="5%">:</td>
                            <td id="kodeKegiatan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Kegiatan</td>
                            <td>:</td>
                            <td id="namaKegiatan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Deskripsi</td>
                            <td>:</td>
                            <td id="deskripsi">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Anggaran Disetujui</td>
                            <td>:</td>
                            <td id="anggaranDisetujui" class="text-success font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Pencairan</td>
                            <td>:</td>
                            <td id="totalPencairan">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sisa Anggaran</td>
                            <td>:</td>
                            <td id="sisaAnggaran">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Mulai</td>
                            <td>:</td>
                            <td id="tanggalMulai">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Selesai</td>
                            <td>:</td>
                            <td id="tanggalSelesai">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat Oleh</td>
                            <td>:</td>
                            <td id="creator">-</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Dibuat</td>
                            <td>:</td>
                            <td id="createdAt">-</td>
                        </tr>
                        <tr id="approverRow" style="display: none;">
                            <td class="font-weight-bold">Disetujui Oleh</td>
                            <td>:</td>
                            <td id="approver">-</td>
                        </tr>
                        <tr id="approvedAtRow" style="display: none;">
                            <td class="font-weight-bold">Tanggal Disetujui</td>
                            <td>:</td>
                            <td id="approvedAt">-</td>
                        </tr>
                        <tr id="catatanRow" style="display: none;">
                            <td class="font-weight-bold">Catatan</td>
                            <td>:</td>
                            <td id="catatan">-</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Documents Card -->
            <div class="card shadow mb-4" id="documentsCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen Terkait</h6>
                </div>
                <div class="card-body">
                    <div id="documentsList"></div>
                </div>
            </div>

            <!-- Pencairan Dana List -->
            <div class="card shadow mb-4" id="pencairanCard" style="display: none;">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Pencairan Dana</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddPencairan">
                        <i class="fas fa-plus"></i> Ajukan Pencairan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pencairanList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4" id="actionCard">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2" id="actionButtons">
                        <!-- Buttons will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- Approval Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Progress Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    <div id="approvalProgress">
                        <!-- Progress will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> Timeline Persetujuan
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="timelineContent">
                    <!-- Timeline will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">Setujui Anggaran</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <div class="form-group">
                        <label for="approvalCatatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="approvalCatatan" rows="3" 
                                  placeholder="Masukkan catatan jika diperlukan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmApprove">
                    <i class="fas fa-check"></i> Setujui
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Tolak Anggaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <div class="form-group">
                        <label for="rejectCatatan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectCatatan" rows="4" 
                                  placeholder="Masukkan alasan penolakan" required></textarea>
                        <small class="form-text text-muted">Catatan wajib diisi saat menolak pengajuan</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReject">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const anggaranId = {{ $id }};
    let anggaranData = null;
    let userRole = null;
    let currentUserId = null; // TAMBAHAN: untuk tracking user ID

    // Load user role first, then load data
    getUserRole();

    // Load anggaran detail
    function loadAnggaranDetail() {
        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    anggaranData = response.data;
                    renderDetail(anggaranData);
                    renderActionButtons(anggaranData);
                    renderApprovalProgress(anggaranData);
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    showAlert('error', 'Anggaran kegiatan tidak ditemukan');
                    setTimeout(() => window.location.href = '/anggaran-kegiatan', 2000);
                } else {
                    showAlert('error', 'Gagal memuat data anggaran kegiatan');
                }
            }
        });
    }

    // Get user role and ID
    function getUserRole() {
        $.ajax({
            url: '/api/auth/me',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    const user = response.data || response.user;
                    
                    // PERBAIKAN: Simpan user ID
                    currentUserId = user.id;
                    
                    if (user && user.roles && user.roles.length > 0) {
                        const rawRole = user.roles[0];
                        userRole = normalizeRole(rawRole);
                        console.log('User ID:', currentUserId);
                        console.log('User Roles (raw):', user.roles);
                        console.log('User Role (normalized):', userRole);
                    }
                    // Now load anggaran detail after getting user role
                    loadAnggaranDetail();
                }
            },
            error: function(xhr) {
                console.log('Failed to fetch user role');
                loadAnggaranDetail();
            }
        });
    }

    // Render detail
    function renderDetail(data) {
        $('#kodeKegiatan').text(data.kode_kegiatan);
        $('#namaKegiatan').text(data.nama_kegiatan);
        $('#deskripsi').text(data.deskripsi || '-');
        $('#anggaranDisetujui').text(formatRupiah(data.anggaran_disetujui));
        $('#totalPencairan').text(formatRupiah(data.total_pencairan || 0));
        $('#sisaAnggaran').text(formatRupiah(data.sisa_anggaran || data.anggaran_disetujui));
        $('#tanggalMulai').text(formatDate(data.tanggal_mulai));
        $('#tanggalSelesai').text(formatDate(data.tanggal_selesai));
        $('#creator').text(data.creator ? data.creator.name : '-');
        $('#createdAt').text(formatDateTime(data.created_at));
        $('#statusBadge').html(getStatusBadge(data.status));

        if (data.approved_by && data.approver) {
            $('#approverRow, #approvedAtRow').show();
            $('#approver').text(data.approver.name);
            $('#approvedAt').text(formatDateTime(data.approved_at));
        }

        if (data.catatan) {
            $('#catatanRow').show();
            $('#catatan').html(`<div class="alert alert-info mb-0">${data.catatan}</div>`);
        }

        if (data.status === 'disetujui_kepala_jic') {
            $('#pencairanCard').show();
            renderPencairanList(data.pencairan_dana || []);
        }

        if (data.documents && data.documents.length > 0) {
            $('#documentsCard').show();
            renderDocuments(data.documents);
        }
    }

    // PERBAIKAN: Render action buttons with proper role checking
    function renderActionButtons(data) {
        let buttons = '';
        const status = data.status;
        const isCreator = data.created_by === currentUserId;
        const isAdmin = userRole === 'super_admin' || userRole === 'admin';

        console.log('=== Action Buttons Check ===');
        console.log('Status:', status);
        console.log('User Role:', userRole);
        console.log('Current User ID:', currentUserId);
        console.log('Created By:', data.created_by);
        console.log('Is Creator:', isCreator);
        console.log('Is Admin:', isAdmin);

        // EDIT & SUBMIT BUTTONS - Only for CREATOR or ADMIN
        if ((status === 'draft' || status === 'ditolak') && (isCreator || isAdmin)) {
            buttons += `
                <a href="/anggaran-kegiatan/${data.id}/edit" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="btn btn-primary btn-block mb-2" id="btnSubmit">
                    <i class="fas fa-paper-plane"></i> Ajukan Persetujuan
                </button>
            `;
        }

        // DELETE BUTTON - Only for CREATOR or ADMIN, and only DRAFT status
        if (status === 'draft' && (isCreator || isAdmin)) {
            buttons += `
                <button type="button" class="btn btn-danger btn-block mb-2" id="btnDelete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            `;
        }

        // APPROVAL BUTTONS - Only for APPROVERS and NOT the creator
        // Approver tidak boleh approve pengajuan sendiri
        if (canApprove(status, userRole) && !isCreator) {
            buttons += `
                <button type="button" class="btn btn-success btn-block mb-2" id="btnApprove">
                    <i class="fas fa-check"></i> Setujui
                </button>
                <button type="button" class="btn btn-danger btn-block mb-2" id="btnReject">
                    <i class="fas fa-times"></i> Tolak
                </button>
            `;
        }

        if (buttons === '') {
            buttons = '<p class="text-muted mb-0">Tidak ada aksi tersedia</p>';
        }

        $('#actionButtons').html(buttons);
        console.log('Buttons rendered:', buttons !== '');
    }

    // Check if user can approve based on status and role
    function canApprove(status, role) {
        const r = role ? String(role).toLowerCase().trim().replace(/\s+/g, '_') : '';
        
        console.log('canApprove check:', { status, role: r });
        
        // Admin dan Super Admin bisa approve di semua tahap
        if (r === 'super_admin' || r === 'admin') return true;
        
        // Kadiv approve untuk status 'diajukan'
        if (status === 'diajukan' && r === 'kadiv') return true;
        
        // Kadiv Umum approve untuk status 'disetujui_kadiv'
        if (status === 'disetujui_kadiv' && (r === 'kadiv_umum' || r === 'kadiv umum')) return true;
        
        // Kepala JIC approve untuk status 'disetujui_kadiv_umum'
        if (status === 'disetujui_kadiv_umum' && (r === 'kepala_jic' || r === 'kepala jic')) return true;
        
        return false;
    }

    // Normalize role from various formats
    function normalizeRole(roleData) {
        if (!roleData) return '';
        
        if (typeof roleData === 'object' && roleData.name) {
            return roleData.name.toLowerCase().trim().replace(/\s+/g, '_');
        }
        
        if (typeof roleData === 'string') {
            return roleData.toLowerCase().trim().replace(/\s+/g, '_');
        }
        
        return '';
    }

    // Render approval progress
    function renderApprovalProgress(data) {
        const steps = [
            { status: 'draft', label: 'Draft', icon: 'file-alt' },
            { status: 'diajukan', label: 'Diajukan', icon: 'paper-plane' },
            { status: 'disetujui_kadiv', label: 'Kadiv', icon: 'user-check' },
            { status: 'disetujui_kadiv_umum', label: 'Kadiv Umum', icon: 'user-check' },
            { status: 'disetujui_kepala_jic', label: 'Kepala JIC', icon: 'check-circle' }
        ];

        const currentStatus = data.status;
        const statusIndex = steps.findIndex(s => s.status === currentStatus);

        let html = '<div class="approval-steps">';
        
        steps.forEach((step, index) => {
            let stepClass = '';
            let iconColor = 'text-muted';

            if (currentStatus === 'ditolak') {
                stepClass = index < statusIndex ? 'completed' : 'pending';
                iconColor = index < statusIndex ? 'text-success' : 'text-muted';
            } else {
                if (index < statusIndex) {
                    stepClass = 'completed';
                    iconColor = 'text-success';
                } else if (index === statusIndex) {
                    stepClass = 'current';
                    iconColor = 'text-primary';
                } else {
                    stepClass = 'pending';
                    iconColor = 'text-muted';
                }
            }

            html += `
                <div class="step ${stepClass} mb-3">
                    <div class="d-flex align-items-center">
                        <div class="step-icon mr-3">
                            <i class="fas fa-${step.icon} fa-2x ${iconColor}"></i>
                        </div>
                        <div class="step-info">
                            <div class="font-weight-bold">${step.label}</div>
                            <small class="text-muted">${getStepStatus(stepClass)}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        if (currentStatus === 'ditolak') {
            html += `
                <div class="step rejected mb-3">
                    <div class="d-flex align-items-center">
                        <div class="step-icon mr-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                        <div class="step-info">
                            <div class="font-weight-bold text-danger">Ditolak</div>
                            <small class="text-muted">Pengajuan ditolak</small>
                        </div>
                    </div>
                </div>
            `;
        }

        html += '</div>';
        $('#approvalProgress').html(html);
    }

    function getStepStatus(stepClass) {
        if (stepClass === 'completed') return 'Selesai';
        if (stepClass === 'current') return 'Sedang Diproses';
        return 'Menunggu';
    }

    // Render pencairan list
    function renderPencairanList(pencairanData) {
        let html = '';
        if (pencairanData.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Belum ada pencairan dana</td></tr>';
        } else {
            pencairanData.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>${formatRupiah(item.jumlah_pencairan)}</td>
                        <td>${getStatusBadge(item.status)}</td>
                        <td>
                            <a href="/pencairan-dana/${item.id}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                `;
            });
        }
        $('#pencairanList').html(html);
    }

    // Render documents
    function renderDocuments(documents) {
        let html = '<div class="list-group">';
        documents.forEach(doc => {
            html += `
                <a href="${doc.file_url}" target="_blank" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-pdf text-danger"></i> ${doc.nama_dokumen}
                    <small class="text-muted float-right">${formatDate(doc.created_at)}</small>
                </a>
            `;
        });
        html += '</div>';
        $('#documentsList').html(html);
    }

    // Submit for approval
    $(document).on('click', '#btnSubmit', function() {
        if (confirm('Apakah Anda yakin ingin mengajukan anggaran kegiatan ini untuk persetujuan?')) {
            $.ajax({
                url: `/api/anggaran-kegiatan/${anggaranId}/submit`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => loadAnggaranDetail(), 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal mengajukan anggaran kegiatan');
                }
            });
        }
    });

    // Delete
    $(document).on('click', '#btnDelete', function() {
        if (confirm('Apakah Anda yakin ingin menghapus anggaran kegiatan ini? Data yang dihapus tidak dapat dikembalikan!')) {
            $.ajax({
                url: `/api/anggaran-kegiatan/${anggaranId}`,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.href = '/anggaran-kegiatan', 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menghapus anggaran kegiatan');
                }
            });
        }
    });

    // Show approve modal
    $(document).on('click', '#btnApprove', function() {
        $('#approvalCatatan').val('');
        $('#approvalModal').modal('show');
    });

    // Confirm approve
    $('#btnConfirmApprove').on('click', function() {
        const catatan = $('#approvalCatatan').val();
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyetujui...');

        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}/approve`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#approvalModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadAnggaranDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menyetujui anggaran kegiatan');
            },
            complete: function() {
                $('#btnConfirmApprove').prop('disabled', false).html('<i class="fas fa-check"></i> Setujui');
            }
        });
    });

    // Show reject modal
    $(document).on('click', '#btnReject', function() {
        $('#rejectCatatan').val('');
        $('#rejectModal').modal('show');
    });

    // Confirm reject
    $('#btnConfirmReject').on('click', function() {
        const catatan = $('#rejectCatatan').val().trim();
        
        if (!catatan) {
            alert('Alasan penolakan harus diisi!');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menolak...');

        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}/reject`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ catatan: catatan }),
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => loadAnggaranDetail(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Gagal menolak anggaran kegiatan');
            },
            complete: function() {
                $('#btnConfirmReject').prop('disabled', false).html('<i class="fas fa-times"></i> Tolak');
            }
        });
    });

    // Show timeline
    $('#btnTimeline').on('click', function() {
        loadTimeline();
    });

    function loadTimeline() {
        $.ajax({
            url: `/api/anggaran-kegiatan/${anggaranId}/timeline`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken(),
            },
            success: function(response) {
                if (response.success) {
                    renderTimeline(response.data.timeline);
                    $('#timelineModal').modal('show');
                }
            },
            error: function(xhr) {
                showAlert('error', 'Gagal memuat timeline');
            }
        });
    }

    function renderTimeline(timeline) {
        let html = '<div class="timeline">';
        
        timeline.forEach((item, index) => {
            const completed = item.completed;
            const icon = completed ? 'check-circle' : 'circle';
            const color = completed ? 'success' : 'muted';
            
            html += `
                <div class="timeline-item ${completed ? 'completed' : ''}">
                    <div class="timeline-marker bg-${color}">
                        <i class="fas fa-${icon}"></i>
                    </div>
                    <div class="timeline-content">
                        <h6 class="mb-1">${item.label}</h6>
                        ${item.user ? `<p class="mb-0 text-muted small">Oleh: ${item.user}</p>` : ''}
                        ${item.date ? `<p class="mb-0 text-muted small">${formatDateTime(item.date)}</p>` : ''}
                        ${item.catatan ? `<div class="alert alert-info mt-2 mb-0"><small>${item.catatan}</small></div>` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#timelineContent').html(html);
    }

    // Add pencairan button
    $(document).on('click', '#btnAddPencairan', function() {
        window.location.href = `/pencairan-dana/create?anggaran_id=${anggaranId}`;
    });

    // Helper functions
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-secondary">Draft</span>',
            'diajukan': '<span class="badge badge-info">Diajukan</span>',
            'disetujui_kadiv': '<span class="badge badge-primary">Disetujui Kadiv</span>',
            'disetujui_kadiv_umum': '<span class="badge badge-primary">Disetujui Kadiv Umum</span>',
            'disetujui_kepala_jic': '<span class="badge badge-success">Disetujui Kepala JIC</span>',
            'ditolak': '<span class="badge badge-danger">Ditolak</span>'
        };
        return badges[status] || status;
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric' 
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showAlert(type, message) {
        let alertClass = 'alert-info';
        if (type === 'success') alertClass = 'alert-success';
        if (type === 'error') alertClass = 'alert-danger';
        if (type === 'warning') alertClass = 'alert-warning';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>

<style>
/* Approval Steps Styling */
.approval-steps .step {
    position: relative;
    padding-left: 0;
}

.approval-steps .step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 30px;
    background-color: #e3e6f0;
}

.approval-steps .step.completed:not(:last-child)::after {
    background-color: #1cc88a;
}

.approval-steps .step.current:not(:last-child)::after {
    background-color: #4e73df;
}

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

/* Button Styling */
.btn-block {
    width: 100%;
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