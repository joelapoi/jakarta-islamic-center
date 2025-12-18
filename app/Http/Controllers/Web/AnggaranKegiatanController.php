<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AnggaranKegiatanController extends Controller
{
    /**
     * Display a listing of anggaran kegiatan
     */
    public function index(Request $request)
    {
        $query = AnggaranKegiatan::with(['creator', 'approver']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_kegiatan', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'ILIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_mulai', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_selesai', '<=', $request->end_date);
        }

        // Filter by user role
        $userRoles = Session::get('user_roles', []);
        $viewAllRoles = ['super_admin', 'admin', 'kadiv', 'kadiv_umum', 'kepala_jic'];
        
        if (!count(array_intersect($userRoles, $viewAllRoles))) {
            $query->where('created_by', auth()->id());
        }

        $anggaran = $query->latest()->paginate(10);

        // Calculate statistics
        $statistics = $this->getStatistics();

        // Check if user is staff
        $isStaff = in_array('staff', $userRoles);

        return view('anggaran-kegiatan.index', compact('anggaran', 'statistics', 'isStaff', 'userRoles'));
    }

    /**
     * Get statistics for dashboard
     */
    private function getStatistics()
    {
        $baseQuery = AnggaranKegiatan::query();

        // Filter by user role
        $userRoles = Session::get('user_roles', []);
        $viewAllRoles = ['super_admin', 'admin', 'kadiv', 'kadiv_umum', 'kepala_jic'];
        
        if (!count(array_intersect($userRoles, $viewAllRoles))) {
            $baseQuery->where('created_by', auth()->id());
        }

        $totalAnggaran = (clone $baseQuery)->sum('anggaran_disetujui');
        
        $byStatus = [
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'diajukan' => (clone $baseQuery)->where('status', 'diajukan')->count(),
            'disetujui_kadiv' => (clone $baseQuery)->where('status', 'disetujui_kadiv')->count(),
            'disetujui_kadiv_umum' => (clone $baseQuery)->where('status', 'disetujui_kadiv_umum')->count(),
            'disetujui_kepala_jic' => (clone $baseQuery)->where('status', 'disetujui_kepala_jic')->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $totalApproved = $byStatus['disetujui_kepala_jic'];
        $totalPending = $byStatus['diajukan'] + $byStatus['disetujui_kadiv'] + $byStatus['disetujui_kadiv_umum'];
        $totalRejected = $byStatus['ditolak'];

        return [
            'total_anggaran' => $totalAnggaran,
            'total_approved' => $totalApproved,
            'total_pending' => $totalPending,
            'total_rejected' => $totalRejected,
            'by_status' => $byStatus
        ];
    }

    /**
     * Show the form for creating new anggaran kegiatan
     */
    public function create()
    {
        // Check if user is staff
        $userRoles = Session::get('user_roles', []);
        if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
            abort(403, 'Hanya staff yang dapat membuat anggaran kegiatan');
        }

        return view('anggaran-kegiatan.create');
    }

    /**
     * Store a newly created anggaran kegiatan
     */
    public function store(Request $request)
    {
        // Check if user is staff
        $userRoles = Session::get('user_roles', []);
        if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
            abort(403);
        }

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran_disetujui' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi',
            'anggaran_disetujui.required' => 'Anggaran wajib diisi',
            'anggaran_disetujui.min' => 'Anggaran harus lebih dari atau sama dengan 0',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
        ]);

        DB::beginTransaction();
        try {
            // Generate kode_kegiatan
            $year = date('Y');
            $prefix = 'KEG-' . $year . '-';
            
            $result = DB::selectOne(
                "SELECT COUNT(*) as count FROM anggaran_kegiatan WHERE kode_kegiatan LIKE ?",
                [$prefix . '%']
            );
            
            $nextNumber = ($result->count ?? 0) + 1;
            $kodeKegiatan = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $anggaran = AnggaranKegiatan::create([
                'kode_kegiatan' => $kodeKegiatan,
                'nama_kegiatan' => $request->nama_kegiatan,
                'deskripsi' => $request->deskripsi,
                'anggaran_disetujui' => $request->anggaran_disetujui,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('success', 'Anggaran kegiatan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat anggaran kegiatan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified anggaran kegiatan
     */
    public function show($id)
    {
        $anggaran = AnggaranKegiatan::with([
            'creator', 
            'approver', 
            'pencairanDana.creator',
            'pencairanDana.approver',
            'lpjKegiatan',
            'documents'
        ])->findOrFail($id);

        // Check permission
        $userRoles = Session::get('user_roles', []);
        $viewAllRoles = ['super_admin', 'admin', 'kadiv', 'kepala_jic', 'kadiv_umum', 'staff'];
        
        if (!count(array_intersect($userRoles, $viewAllRoles))) {
            if ($anggaran->created_by !== auth()->id()) {
                abort(403, 'Anda tidak memiliki akses ke anggaran kegiatan ini');
            }
        }

        // Calculate total pencairan
        $totalPencairan = $anggaran->pencairanDana()
            ->whereIn('status', ['disetujui_kepala_jic', 'disetujui_kadiv_umum', 'dicairkan'])
            ->sum('jumlah_pencairan');

        $sisaAnggaran = $anggaran->anggaran_disetujui - $totalPencairan;

        return view('anggaran-kegiatan.show', compact('anggaran', 'totalPencairan', 'sisaAnggaran'));
    }

    /**
     * Show the form for editing anggaran kegiatan
     */
    public function edit($id)
    {
        $anggaran = AnggaranKegiatan::findOrFail($id);

        // Check permission - only staff or admin can edit
        $userRoles = Session::get('user_roles', []);
        
        // Only creator with staff role or admin/super_admin can edit
        if ($anggaran->created_by === auth()->id()) {
            if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit anggaran kegiatan ini');
            }
        } else {
            if (!count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit anggaran kegiatan ini');
            }
        }

        // Check if can edit based on status
        if (!in_array($anggaran->status, ['draft', 'ditolak'])) {
            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('error', 'Tidak dapat mengedit anggaran kegiatan dengan status: ' . $anggaran->status);
        }

        return view('anggaran-kegiatan.edit', compact('anggaran'));
    }

    /**
     * Update the specified anggaran kegiatan
     */
    public function update(Request $request, $id)
    {
        $anggaran = AnggaranKegiatan::findOrFail($id);

        // Check permission
        $userRoles = Session::get('user_roles', []);
        
        if ($anggaran->created_by === auth()->id()) {
            if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        } else {
            if (!count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        }

        // Check if can edit
        if (!in_array($anggaran->status, ['draft', 'ditolak'])) {
            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('error', 'Tidak dapat mengedit anggaran kegiatan dengan status: ' . $anggaran->status);
        }

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran_disetujui' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $updateData = $request->only([
                'nama_kegiatan',
                'deskripsi',
                'anggaran_disetujui',
                'tanggal_mulai',
                'tanggal_selesai'
            ]);

            // Reset status to draft if it was rejected
            if ($anggaran->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $anggaran->update($updateData);

            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('success', 'Anggaran kegiatan berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui anggaran kegiatan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified anggaran kegiatan
     */
    public function destroy($id)
    {
        $anggaran = AnggaranKegiatan::findOrFail($id);

        // Check permission - only staff or admin can delete
        $userRoles = Session::get('user_roles', []);
        
        if ($anggaran->created_by === auth()->id()) {
            if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        } else {
            if (!count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        }

        // Only draft can be deleted
        if ($anggaran->status !== 'draft') {
            return redirect()->route('anggaran-kegiatan.index')
                ->with('error', 'Hanya anggaran kegiatan dengan status draft yang dapat dihapus');
        }

        try {
            $anggaran->delete();
            return redirect()->route('anggaran-kegiatan.index')
                ->with('success', 'Anggaran kegiatan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('anggaran-kegiatan.index')
                ->with('error', 'Gagal menghapus anggaran kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Submit anggaran kegiatan for approval
     */
    public function submit($id)
    {
        $anggaran = AnggaranKegiatan::findOrFail($id);

        // Check permission - only staff or admin can submit
        $userRoles = Session::get('user_roles', []);
        
        if ($anggaran->created_by === auth()->id()) {
            if (!in_array('staff', $userRoles) && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        } else {
            if (!count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                abort(403);
            }
        }

        // Check if can be submitted
        if (!in_array($anggaran->status, ['draft', 'ditolak'])) {
            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('error', 'Hanya anggaran kegiatan draft atau ditolak yang dapat diajukan');
        }

        try {
            $anggaran->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('success', 'Anggaran kegiatan berhasil diajukan');
        } catch (\Exception $e) {
            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('error', 'Gagal mengajukan anggaran kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Approve anggaran kegiatan (multi-level approval)
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $anggaran = AnggaranKegiatan::findOrFail($id);
        $userRoles = Session::get('user_roles', []);
        $currentStatus = $anggaran->status;
        $newStatus = null;
        $message = '';

        // Determine next status based on current status and user role
        switch ($currentStatus) {
            case 'diajukan':
                if (in_array('kadiv', $userRoles)) {
                    $newStatus = 'disetujui_kadiv';
                    $message = 'Anggaran kegiatan berhasil disetujui oleh Kadiv';
                } else {
                    return back()->with('error', 'Hanya Kadiv yang dapat menyetujui pada tahap ini');
                }
                break;

            case 'disetujui_kadiv':
                if (in_array('kadiv_umum', $userRoles)) {
                    $newStatus = 'disetujui_kadiv_umum';
                    $message = 'Anggaran kegiatan berhasil disetujui oleh Kadiv Umum';
                } else {
                    return back()->with('error', 'Hanya Kadiv Umum yang dapat menyetujui pada tahap ini');
                }
                break;

            case 'disetujui_kadiv_umum':
                if (in_array('kepala_jic', $userRoles)) {
                    $newStatus = 'disetujui_kepala_jic';
                    $message = 'Anggaran kegiatan berhasil disetujui penuh oleh Kepala JIC';
                    
                    $anggaran->approved_by = auth()->id();
                    $anggaran->approved_at = now();
                } else {
                    return back()->with('error', 'Hanya Kepala JIC yang dapat menyetujui pada tahap ini');
                }
                break;

            default:
                return back()->with('error', 'Tidak dapat menyetujui anggaran kegiatan dengan status: ' . $currentStatus);
        }

        try {
            $anggaran->status = $newStatus;
            $anggaran->catatan = $request->catatan;
            $anggaran->save();

            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui anggaran kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Reject anggaran kegiatan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $anggaran = AnggaranKegiatan::findOrFail($id);
        $userRoles = Session::get('user_roles', []);

        // Check permission
        $canReject = false;
        $currentStatus = $anggaran->status;

        if ($currentStatus === 'diajukan' && in_array('kadiv', $userRoles)) {
            $canReject = true;
        } elseif ($currentStatus === 'disetujui_kadiv' && in_array('kadiv_umum', $userRoles)) {
            $canReject = true;
        } elseif ($currentStatus === 'disetujui_kadiv_umum' && in_array('kepala_jic', $userRoles)) {
            $canReject = true;
        }

        if (!$canReject && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
            abort(403, 'Anda tidak memiliki wewenang untuk menolak pada tahap ini');
        }

        try {
            $anggaran->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('anggaran-kegiatan.show', $anggaran->id)
                ->with('success', 'Anggaran kegiatan berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak anggaran kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Get approval timeline
     */
    public function timeline($id)
    {
        $anggaran = AnggaranKegiatan::with(['creator', 'approver'])->findOrFail($id);

        $timeline = [
            [
                'status' => 'draft',
                'label' => 'Draft Dibuat',
                'user' => $anggaran->creator->name ?? null,
                'date' => $anggaran->created_at,
                'completed' => true,
            ],
            [
                'status' => 'diajukan',
                'label' => 'Diajukan untuk Persetujuan',
                'user' => $anggaran->creator->name ?? null,
                'date' => $anggaran->status !== 'draft' ? $anggaran->updated_at : null,
                'completed' => $anggaran->status !== 'draft',
            ],
            [
                'status' => 'disetujui_kadiv',
                'label' => 'Disetujui oleh Kadiv',
                'user' => null,
                'date' => null,
                'completed' => in_array($anggaran->status, ['disetujui_kadiv', 'disetujui_kadiv_umum', 'disetujui_kepala_jic']),
            ],
            [
                'status' => 'disetujui_kadiv_umum',
                'label' => 'Disetujui oleh Kadiv Umum',
                'user' => null,
                'date' => null,
                'completed' => in_array($anggaran->status, ['disetujui_kadiv_umum', 'disetujui_kepala_jic']),
            ],
            [
                'status' => 'disetujui_kepala_jic',
                'label' => 'Disetujui oleh Kepala JIC',
                'user' => $anggaran->approver->name ?? null,
                'date' => $anggaran->approved_at,
                'completed' => $anggaran->status === 'disetujui_kepala_jic',
            ],
        ];

        if ($anggaran->status === 'ditolak') {
            $timeline[] = [
                'status' => 'ditolak',
                'label' => 'Ditolak',
                'user' => null,
                'date' => $anggaran->updated_at,
                'catatan' => $anggaran->catatan,
                'completed' => true,
            ];
        }

        return view('anggaran-kegiatan.timeline', compact('anggaran', 'timeline'));
    }
}