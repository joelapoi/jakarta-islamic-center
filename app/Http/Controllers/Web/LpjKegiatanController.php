<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LpjKegiatan;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LpjKegiatanController extends Controller
{
    /**
     * Display a listing of LPJ kegiatan
     */
    public function index(Request $request)
    {
        $query = LpjKegiatan::with(['anggaranKegiatan', 'creator', 'approver']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_lpj', 'ILIKE', "%{$search}%")
                  ->orWhereHas('anggaranKegiatan', function($q2) use ($search) {
                      $q2->where('nama_kegiatan', 'ILIKE', "%{$search}%")
                         ->orWhere('kode_kegiatan', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Anggaran filter
        if ($request->filled('anggaran_kegiatan_id')) {
            $query->where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id);
        }

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $query->where('created_by', $user->id);
        }

        $lpj = $query->latest()->paginate(15);
        
        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('lpj-kegiatan.index', compact('lpj', 'anggaranList'));
    }

    /**
     * Show the form for creating a new LPJ kegiatan
     */
    public function create()
    {
        // Get anggaran that are approved and don't have LPJ yet
        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->whereDoesntHave('lpjKegiatan')
            ->orderBy('nama_kegiatan')
            ->get();
            
        return view('lpj-kegiatan.create', compact('anggaranList'));
    }

    /**
     * Store a newly created LPJ kegiatan
     */
    public function store(Request $request)
    {
        $request->validate([
            'anggaran_kegiatan_id' => 'required|exists:anggaran_kegiatan,id',
            'total_realisasi' => 'required|numeric|min:0',
            'laporan_kegiatan' => 'required|string',
        ], [
            'anggaran_kegiatan_id.required' => 'Anggaran kegiatan wajib dipilih',
            'anggaran_kegiatan_id.exists' => 'Anggaran kegiatan tidak ditemukan',
            'total_realisasi.required' => 'Total realisasi wajib diisi',
            'total_realisasi.min' => 'Total realisasi harus lebih dari atau sama dengan 0',
            'laporan_kegiatan.required' => 'Laporan kegiatan wajib diisi',
        ]);

        // Check if anggaran is approved
        $anggaran = AnggaranKegiatan::findOrFail($request->anggaran_kegiatan_id);
        
        if ($anggaran->status !== 'disetujui_kepala_jic') {
            return back()->with('error', 'Anggaran kegiatan harus disetujui penuh terlebih dahulu')
                ->withInput();
        }

        // Check permission - only creator of anggaran can create LPJ
        if ($anggaran->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat LPJ ini')
                ->withInput();
        }

        // Check if LPJ already exists for this anggaran
        $existingLpj = LpjKegiatan::where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id)->first();
        if ($existingLpj) {
            return back()->with('error', 'LPJ sudah ada untuk anggaran kegiatan ini')
                ->withInput();
        }

        // Check if total realisasi doesn't exceed budget
        if ($request->total_realisasi > $anggaran->anggaran_disetujui) {
            return back()
                ->with('error', "Total realisasi melebihi anggaran yang disetujui. Anggaran: Rp " . number_format($anggaran->anggaran_disetujui, 0, ',', '.'))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate nomor LPJ
            $year = date('Y');
            $lastLpj = LpjKegiatan::whereYear('created_at', $year)
                ->latest('id')
                ->first();
            
            $number = $lastLpj ? (intval(substr($lastLpj->nomor_lpj, -4)) + 1) : 1;
            $nomorLpj = 'LPJ-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            // Calculate sisa anggaran
            $sisaAnggaran = $anggaran->anggaran_disetujui - $request->total_realisasi;

            $lpj = LpjKegiatan::create([
                'anggaran_kegiatan_id' => $request->anggaran_kegiatan_id,
                'nomor_lpj' => $nomorLpj,
                'total_realisasi' => $request->total_realisasi,
                'sisa_anggaran' => $sisaAnggaran,
                'laporan_kegiatan' => $request->laporan_kegiatan,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('lpj-kegiatan.show', $lpj->id)
                ->with('success', 'LPJ kegiatan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat LPJ kegiatan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified LPJ kegiatan
     */
    public function show($id)
    {
        $lpj = LpjKegiatan::with([
            'anggaranKegiatan.pencairanDana',
            'creator',
            'approver',
            'documents'
        ])->findOrFail($id);

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            if ($lpj->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke LPJ kegiatan ini');
            }
        }

        return view('lpj-kegiatan.show', compact('lpj'));
    }

    /**
     * Show the form for editing the specified LPJ kegiatan
     */
    public function edit($id)
    {
        $lpj = LpjKegiatan::with('anggaranKegiatan')->findOrFail($id);

        // Check permission
        if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can edit
        if (!in_array($lpj->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit LPJ kegiatan dengan status: ' . $lpj->status);
        }

        return view('lpj-kegiatan.edit', compact('lpj'));
    }

    /**
     * Update the specified LPJ kegiatan
     */
    public function update(Request $request, $id)
    {
        $lpj = LpjKegiatan::with('anggaranKegiatan')->findOrFail($id);

        // Check permission
        if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can edit
        if (!in_array($lpj->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit LPJ kegiatan dengan status: ' . $lpj->status);
        }

        $request->validate([
            'total_realisasi' => 'required|numeric|min:0',
            'laporan_kegiatan' => 'required|string',
        ], [
            'total_realisasi.required' => 'Total realisasi wajib diisi',
            'laporan_kegiatan.required' => 'Laporan kegiatan wajib diisi',
        ]);

        // Check if total realisasi doesn't exceed budget
        if ($request->total_realisasi > $lpj->anggaranKegiatan->anggaran_disetujui) {
            return back()
                ->with('error', "Total realisasi melebihi anggaran yang disetujui. Anggaran: Rp " . number_format($lpj->anggaranKegiatan->anggaran_disetujui, 0, ',', '.'))
                ->withInput();
        }

        try {
            $updateData = [
                'total_realisasi' => $request->total_realisasi,
                'sisa_anggaran' => $lpj->anggaranKegiatan->anggaran_disetujui - $request->total_realisasi,
                'laporan_kegiatan' => $request->laporan_kegiatan,
            ];

            // Reset status to draft if it was rejected
            if ($lpj->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $lpj->update($updateData);

            return redirect()->route('lpj-kegiatan.show', $lpj->id)
                ->with('success', 'LPJ kegiatan berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui LPJ kegiatan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified LPJ kegiatan
     */
    public function destroy($id)
    {
        $lpj = LpjKegiatan::findOrFail($id);

        // Check permission
        if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Only draft can be deleted
        if ($lpj->status !== 'draft') {
            return back()->with('error', 'Hanya LPJ kegiatan dengan status draft yang dapat dihapus');
        }

        try {
            $lpj->delete();
            return redirect()->route('lpj-kegiatan.index')
                ->with('success', 'LPJ kegiatan berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus LPJ kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Submit LPJ kegiatan for approval
     */
    public function submit($id)
    {
        $lpj = LpjKegiatan::findOrFail($id);

        // Check permission
        if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can be submitted
        if (!in_array($lpj->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Hanya LPJ kegiatan draft atau ditolak yang dapat diajukan');
        }

        try {
            $lpj->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            return redirect()->route('lpj-kegiatan.show', $lpj->id)
                ->with('success', 'LPJ kegiatan berhasil diajukan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan LPJ kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Approve LPJ kegiatan
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $lpj = LpjKegiatan::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            return back()->with('error', 'Hanya Kadiv Umum atau Kepala JIC yang dapat menyetujui');
        }

        // Check if can be approved
        if ($lpj->status !== 'diajukan') {
            return back()->with('error', 'Hanya LPJ kegiatan yang diajukan yang dapat disetujui');
        }

        try {
            $lpj->update([
                'status' => 'disetujui',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'catatan' => $request->catatan
            ]);

            return redirect()->route('lpj-kegiatan.show', $lpj->id)
                ->with('success', 'LPJ kegiatan berhasil disetujui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui LPJ kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Reject LPJ kegiatan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $lpj = LpjKegiatan::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menolak');
        }

        try {
            $lpj->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('lpj-kegiatan.show', $lpj->id)
                ->with('success', 'LPJ kegiatan berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak LPJ kegiatan: ' . $e->getMessage());
        }
    }
}