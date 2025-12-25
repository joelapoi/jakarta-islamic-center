<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PencairanDana;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PencairanDanaController extends Controller
{
    public function index(Request $request)
    {
        $query = PencairanDana::with(['anggaranKegiatan', 'creator', 'approver']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pencairan', 'ILIKE', "%{$search}%")
                  ->orWhere('keperluan', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Anggaran kegiatan filter
        if ($request->filled('anggaran_kegiatan_id')) {
            $query->where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id);
        }

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $query->where('created_by', $user->id);
        }

        $pencairan = $query->latest()->paginate(15)->withQueryString();
        
        // Calculate statistics
        $statistics = $this->getStatistics();

        return view('pencairan-dana.index', compact('pencairan', 'statistics'));
    }

    /**
     * Get statistics for dashboard
     */
    private function getStatistics()
    {
        $baseQuery = PencairanDana::query();

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $baseQuery->where('created_by', $user->id);
        }

        $totalAmount = (clone $baseQuery)->sum('jumlah_pencairan');
        $totalDisbursed = (clone $baseQuery)->where('status', 'dicairkan')->sum('jumlah_pencairan');
        
        $byStatus = [
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'diajukan' => (clone $baseQuery)->where('status', 'diajukan')->count(),
            'disetujui_kadiv_umum' => (clone $baseQuery)->where('status', 'disetujui_kadiv_umum')->count(),
            'disetujui_kepala_jic' => (clone $baseQuery)->where('status', 'disetujui_kepala_jic')->count(),
            'dicairkan' => (clone $baseQuery)->where('status', 'dicairkan')->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $totalPending = $byStatus['diajukan'] + $byStatus['disetujui_kadiv_umum'] + $byStatus['disetujui_kepala_jic'];

        return [
            'total_amount' => $totalAmount,
            'total_disbursed' => $totalDisbursed,
            'total_pending' => $totalPending,
            'by_status' => $byStatus
        ];
    }

    public function create()
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['staff', 'super_admin', 'admin'])) {
            abort(403, 'Hanya staff yang dapat membuat pencairan dana');
        }

        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->with(['pencairanDana' => function($query) {
                $query->whereIn('status', ['disetujui_kepala_jic', 'dicairkan']);
            }])
            ->orderBy('nama_kegiatan')
            ->get()
            ->map(function($anggaran) {
                // Calculate sisa anggaran
                $totalPencairan = $anggaran->pencairanDana->sum('jumlah_pencairan');
                $anggaran->sisa_anggaran = $anggaran->anggaran_disetujui - $totalPencairan;
                return $anggaran;
            })
            ->filter(function($anggaran) {
                // Only show anggaran with remaining budget
                return $anggaran->sisa_anggaran > 0;
            });
            
        return view('pencairan-dana.create', compact('anggaranList'));
    }

    public function store(Request $request)
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['staff', 'super_admin', 'admin'])) {
            abort(403, 'Hanya staff yang dapat membuat pencairan dana');
        }

        // Clean jumlah_pencairan from currency format
        $jumlahPencairan = str_replace('.', '', $request->jumlah_pencairan);

        $request->merge([
            'jumlah_pencairan' => $jumlahPencairan
        ]);

        $validated = $request->validate([
            'anggaran_kegiatan_id' => 'required|exists:anggaran_kegiatan,id',
            'jumlah_pencairan' => 'required|numeric|min:1',
            'keperluan' => 'required|string|max:1000',
        ], [
            'anggaran_kegiatan_id.required' => 'Anggaran kegiatan wajib dipilih',
            'anggaran_kegiatan_id.exists' => 'Anggaran kegiatan tidak valid',
            'jumlah_pencairan.required' => 'Jumlah pencairan wajib diisi',
            'jumlah_pencairan.numeric' => 'Jumlah pencairan harus berupa angka',
            'jumlah_pencairan.min' => 'Jumlah pencairan minimal Rp 1',
            'keperluan.required' => 'Keperluan wajib diisi',
            'keperluan.max' => 'Keperluan maksimal 1000 karakter',
        ]);

        $anggaran = AnggaranKegiatan::findOrFail($validated['anggaran_kegiatan_id']);
        
        if ($anggaran->status !== 'disetujui_kepala_jic') {
            return back()
                ->with('error', 'Anggaran kegiatan harus disetujui penuh terlebih dahulu')
                ->withInput();
        }

        // Check available budget
        $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $validated['anggaran_kegiatan_id'])
            ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
            ->sum('jumlah_pencairan');

        $sisaAnggaran = $anggaran->anggaran_disetujui - $totalPencairan;

        if ($validated['jumlah_pencairan'] > $sisaAnggaran) {
            return back()
                ->with('error', "Jumlah pencairan melebihi sisa anggaran. Sisa anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.'))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate nomor pencairan
            $year = date('Y');
            $lastPencairan = PencairanDana::whereYear('created_at', $year)
                ->latest('id')
                ->first();
            
            $number = $lastPencairan ? (intval(substr($lastPencairan->nomor_pencairan, -4)) + 1) : 1;
            $nomorPencairan = 'PC-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $pencairan = PencairanDana::create([
                'nomor_pencairan' => $nomorPencairan,
                'anggaran_kegiatan_id' => $validated['anggaran_kegiatan_id'],
                'jumlah_pencairan' => $validated['jumlah_pencairan'],
                'keperluan' => $validated['keperluan'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Check if user wants to submit immediately
            $action = $request->input('action', 'draft');
            
            if ($action === 'submit') {
                $pencairan->update([
                    'status' => 'diajukan',
                    'catatan' => null
                ]);
                
                DB::commit();
                
                return redirect()->route('pencairan-dana.show', $pencairan)
                    ->with('success', 'Pencairan dana berhasil dibuat dan diajukan untuk persetujuan');
            }

            DB::commit();

            return redirect()->route('pencairan-dana.show', $pencairan)
                ->with('success', 'Pencairan dana berhasil dibuat sebagai draft');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Gagal membuat pencairan dana: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(PencairanDana $pencairanDana)
    {
        // Load relationships
        $pencairan = $pencairanDana->load([
            'anggaranKegiatan',
            'creator',
            'approver',
            'rekapPengajuan',
            'documents'
        ]);

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
            if ($pencairan->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pencairan dana ini');
            }
        }

        // Calculate total pencairan for this kegiatan (excluding current if status allows)
        $totalPencairanQuery = PencairanDana::where('anggaran_kegiatan_id', $pencairan->anggaran_kegiatan_id)
            ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan']);
        
        // Include current pencairan in total if it's approved or disbursed
        if (!in_array($pencairan->status, ['disetujui_kepala_jic', 'dicairkan'])) {
            $totalPencairanQuery->where('id', '!=', $pencairan->id);
        }
        
        $totalPencairan = $totalPencairanQuery->sum('jumlah_pencairan');
        $sisaAnggaran = $pencairan->anggaranKegiatan->anggaran_disetujui - $totalPencairan;

        return view('pencairan-dana.show', compact('pencairan', 'totalPencairan', 'sisaAnggaran'));
    }

    public function edit(PencairanDana $pencairanDana)
    {
        $pencairan = $pencairanDana->load(['anggaranKegiatan', 'creator']);

        // Check permission
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pencairan dana ini');
        }

        // Check if can edit
        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return redirect()->route('pencairan-dana.show', $pencairan)
                ->with('error', 'Tidak dapat mengedit pencairan dana dengan status: ' . $pencairan->status);
        }

        // Calculate budget info
        // Total pencairan dari kegiatan ini (excluding current pencairan)
        $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $pencairan->anggaran_kegiatan_id)
            ->where('id', '!=', $pencairan->id)
            ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
            ->sum('jumlah_pencairan');

        $sisaAnggaran = $pencairan->anggaranKegiatan->anggaran_disetujui - $totalPencairan;
        
        // Available budget includes current pencairan amount (so user can keep the same amount)
        $availableBudget = $sisaAnggaran + $pencairan->jumlah_pencairan;

        return view('pencairan-dana.edit', compact('pencairan', 'totalPencairan', 'sisaAnggaran', 'availableBudget'));
    }

    public function update(Request $request, PencairanDana $pencairanDana)
    {
        $pencairan = $pencairanDana;

        // Check permission
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pencairan dana ini');
        }

        // Check if can edit
        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return redirect()->route('pencairan-dana.show', $pencairan)
                ->with('error', 'Tidak dapat mengedit pencairan dana dengan status: ' . $pencairan->status);
        }

        // Clean jumlah_pencairan from currency format
        $jumlahPencairan = str_replace('.', '', $request->jumlah_pencairan);

        $request->merge([
            'jumlah_pencairan' => $jumlahPencairan
        ]);

        $validated = $request->validate([
            'jumlah_pencairan' => 'required|numeric|min:1',
            'keperluan' => 'required|string|max:1000',
        ], [
            'jumlah_pencairan.required' => 'Jumlah pencairan wajib diisi',
            'jumlah_pencairan.numeric' => 'Jumlah pencairan harus berupa angka',
            'jumlah_pencairan.min' => 'Jumlah pencairan minimal Rp 1',
            'keperluan.required' => 'Keperluan wajib diisi',
            'keperluan.max' => 'Keperluan maksimal 1000 karakter',
        ]);

        // Check budget if amount is being updated
        if ($validated['jumlah_pencairan'] != $pencairan->jumlah_pencairan) {
            $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $pencairan->anggaran_kegiatan_id)
                ->where('id', '!=', $pencairan->id)
                ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
                ->sum('jumlah_pencairan');

            $sisaAnggaran = $pencairan->anggaranKegiatan->anggaran_disetujui - $totalPencairan;

            if ($validated['jumlah_pencairan'] > $sisaAnggaran) {
                return back()
                    ->with('error', "Jumlah pencairan melebihi sisa anggaran. Sisa anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.'))
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'jumlah_pencairan' => $validated['jumlah_pencairan'],
                'keperluan' => $validated['keperluan'],
            ];
            
            // Get action
            $action = $request->input('action', 'save');
            
            if ($action === 'submit') {
                // Reset status to diajukan and clear catatan
                $updateData['status'] = 'diajukan';
                $updateData['catatan'] = null;
                $message = 'Pencairan dana berhasil diperbarui dan diajukan untuk persetujuan';
            } else {
                // Reset status to draft if it was rejected
                if ($pencairan->status === 'ditolak') {
                    $updateData['status'] = 'draft';
                    $updateData['catatan'] = null;
                }
                $message = 'Pencairan dana berhasil diperbarui';
            }

            $pencairan->update($updateData);

            DB::commit();

            return redirect()->route('pencairan-dana.show', $pencairan)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withErrors(['error' => 'Gagal memperbarui pencairan dana: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $pencairan = PencairanDana::findOrFail($id);

        // Check permission
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Only draft can be deleted
        if ($pencairan->status !== 'draft') {
            return redirect()->route('pencairan-dana.index')
                ->with('error', 'Hanya pencairan dana dengan status draft yang dapat dihapus');
        }

        try {
            $pencairan->delete();
            
            return redirect()->route('pencairan-dana.index')
                ->with('success', 'Pencairan dana berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('pencairan-dana.index')
                ->with('error', 'Gagal menghapus pencairan dana: ' . $e->getMessage());
        }
    }

    public function submit(PencairanDana $pencairanDana)
    {
        // Check permission
        if ($pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can be submitted
        if (!in_array($pencairanDana->status, ['draft', 'ditolak'])) {
            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('error', 'Hanya pencairan dana draft atau ditolak yang dapat diajukan');
        }

        try {
            $pencairanDana->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('success', 'Pencairan dana berhasil diajukan');
        } catch (\Exception $e) {
            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('error', 'Gagal mengajukan pencairan dana: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, PencairanDana $pencairanDana)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $currentStatus = $pencairanDana->status;
        $newStatus = null;
        $message = '';

        switch ($currentStatus) {
            case 'diajukan':
                if ($user->hasRole('kadiv_umum') || $user->hasAnyRole(['super_admin', 'admin'])) {
                    $newStatus = 'disetujui_kadiv_umum';
                    $message = 'Pencairan dana berhasil disetujui oleh Kadiv Umum';
                } else {
                    return back()->with('error', 'Hanya Kadiv Umum yang dapat menyetujui pada tahap ini');
                }
                break;

            case 'disetujui_kadiv_umum':
                if ($user->hasRole('kepala_jic') || $user->hasAnyRole(['super_admin', 'admin'])) {
                    $newStatus = 'disetujui_kepala_jic';
                    $message = 'Pencairan dana berhasil disetujui penuh oleh Kepala JIC';
                    
                    $pencairanDana->approved_by = $user->id;
                    $pencairanDana->approved_at = now();
                } else {
                    return back()->with('error', 'Hanya Kepala JIC yang dapat menyetujui pada tahap ini');
                }
                break;

            default:
                return back()->with('error', 'Tidak dapat menyetujui pencairan dana dengan status: ' . $currentStatus);
        }

        try {
            $pencairanDana->status = $newStatus;
            $pencairanDana->catatan = $request->catatan;
            $pencairanDana->save();

            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui pencairan dana: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, PencairanDana $pencairanDana)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        try {
            $pencairanDana->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('success', 'Pencairan dana berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak pencairan dana: ' . $e->getMessage());
        }
    }

    public function disburse(PencairanDana $pencairanDana)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        if ($pencairanDana->status !== 'disetujui_kepala_jic') {
            return back()->with('error', 'Pencairan dana harus disetujui penuh terlebih dahulu');
        }

        try {
            $pencairanDana->update([
                'status' => 'dicairkan',
                'disbursed_at' => now()
            ]);

            return redirect()->route('pencairan-dana.show', $pencairanDana)
                ->with('success', 'Pencairan dana berhasil dicairkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencairkan dana: ' . $e->getMessage());
        }
    }
}