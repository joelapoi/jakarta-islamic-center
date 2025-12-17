<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BukuCek;
use App\Models\RekapPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuCekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BukuCek::with(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_cek', 'ILIKE', "%{$search}%")
                  ->orWhere('penerima', 'ILIKE', "%{$search}%")
                  ->orWhere('bank_name', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Rekap filter
        if ($request->filled('rekap_pengajuan_id')) {
            $query->where('rekap_pengajuan_id', $request->rekap_pengajuan_id);
        }

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
            $query->whereHas('rekapPengajuan.pencairanDana', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        $bukuCek = $query->latest()->paginate(15);

        return view('buku-cek.index', compact('bukuCek'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rekapList = RekapPengajuan::where('status', 'disetujui')
            ->with('pencairanDana.anggaranKegiatan')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-cek.create', compact('rekapList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rekap_pengajuan_id' => 'required|exists:rekap_pengajuan,id',
            'jumlah' => 'required|numeric|min:0',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|max:255',
            'nama_penerima' => 'required|string|max:255',
            'keperluan' => 'nullable|string|max:1000',
        ], [
            'rekap_pengajuan_id.required' => 'Rekap pengajuan wajib dipilih',
            'rekap_pengajuan_id.exists' => 'Rekap pengajuan tidak ditemukan',
            'jumlah.required' => 'Jumlah dana wajib diisi',
            'jumlah.numeric' => 'Jumlah dana harus berupa angka',
            'jumlah.min' => 'Jumlah dana harus lebih dari atau sama dengan 0',
            'nama_bank.required' => 'Nama bank wajib diisi',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi',
            'nama_penerima.required' => 'Nama penerima wajib diisi',
        ]);

        // Check if rekap is approved
        $rekap = RekapPengajuan::findOrFail($request->rekap_pengajuan_id);
        
        if ($rekap->status !== 'disetujui') {
            return back()->with('error', 'Rekap pengajuan harus disetujui terlebih dahulu')
                ->withInput();
        }

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat buku cek ini')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate nomor cek
            $year = date('Y');
            $lastCek = BukuCek::whereYear('created_at', $year)
                ->latest('id')
                ->first();
            
            $number = $lastCek ? (intval(substr($lastCek->nomor_cek, -4)) + 1) : 1;
            $nomorCek = 'CK-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $bukuCek = BukuCek::create([
                'rekap_pengajuan_id' => $request->rekap_pengajuan_id,
                'nomor_cek' => $nomorCek,
                'nominal' => $request->jumlah,
                'tanggal_cek' => now()->toDateString(),
                'bank_name' => $request->nama_bank,
                'nomor_rekening' => $request->nomor_rekening,
                'penerima' => $request->nama_penerima,
                'keterangan' => $request->keperluan,
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Buku cek berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat buku cek: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bukuCek = BukuCek::with([
            'rekapPengajuan.pencairanDana.anggaranKegiatan',
            'rekapPengajuan.pencairanDana.creator',
            'documents'
        ])->findOrFail($id);

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
            if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke buku cek ini');
            }
        }

        return view('buku-cek.show', compact('bukuCek'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bukuCek = BukuCek::with('rekapPengajuan.pencairanDana')->findOrFail($id);

        // Check permission
        if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit buku cek ini');
        }

        // Check if can edit
        if ($bukuCek->status !== 'pending') {
            return back()->with('error', 'Tidak dapat mengedit buku cek dengan status: ' . $bukuCek->status);
        }

        return view('buku-cek.edit', compact('bukuCek'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $bukuCek = BukuCek::with('rekapPengajuan.pencairanDana')->findOrFail($id);

        // Check permission
        if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit buku cek ini');
        }

        // Check if can edit
        if ($bukuCek->status !== 'pending') {
            return back()->with('error', 'Tidak dapat mengedit buku cek dengan status: ' . $bukuCek->status);
        }

        $request->validate([
            'nominal' => 'required|numeric|min:0',
            'tanggal_cek' => 'required|date',
            'bank_name' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'nominal.required' => 'Nominal wajib diisi',
            'tanggal_cek.required' => 'Tanggal cek wajib diisi',
            'bank_name.required' => 'Nama bank wajib diisi',
            'penerima.required' => 'Nama penerima wajib diisi',
        ]);

        try {
            $bukuCek->update($request->only([
                'nominal', 
                'tanggal_cek', 
                'bank_name', 
                'penerima', 
                'keterangan'
            ]));

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Buku cek berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui buku cek: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bukuCek = BukuCek::with('rekapPengajuan.pencairanDana')->findOrFail($id);

        // Check permission
        if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus buku cek ini');
        }

        // Only pending can be deleted
        if ($bukuCek->status !== 'pending') {
            return back()->with('error', 'Hanya buku cek dengan status pending yang dapat dihapus');
        }

        try {
            $bukuCek->delete();
            return redirect()->route('buku-cek.index')
                ->with('success', 'Buku cek berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus buku cek: ' . $e->getMessage());
        }
    }

    /**
     * Sign the check (Kepala JIC only)
     */
    public function sign($id)
    {
        $bukuCek = BukuCek::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if (!$user->hasRole('kepala_jic') && !$user->hasAnyRole(['super_admin', 'admin'])) {
            return back()->with('error', 'Hanya Kepala JIC yang dapat menandatangani cek');
        }

        // Check if can be signed
        if ($bukuCek->status !== 'pending') {
            return back()->with('error', 'Hanya cek pending yang dapat ditandatangani');
        }

        try {
            $bukuCek->update([
                'status' => 'ditandatangani',
                'signed_at' => now()
            ]);

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Cek berhasil ditandatangani');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menandatangani cek: ' . $e->getMessage());
        }
    }

    /**
     * Cash the check
     */
    public function cash($id)
    {
        $bukuCek = BukuCek::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk mencairkan cek');
        }

        // Check if can be cashed
        if ($bukuCek->status !== 'ditandatangani') {
            return back()->with('error', 'Cek harus ditandatangani terlebih dahulu');
        }

        try {
            $bukuCek->update([
                'status' => 'dicairkan',
                'cashed_at' => now()
            ]);

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Cek berhasil dicairkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencairkan cek: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the check
     */
    public function cancel($id)
    {
        $bukuCek = BukuCek::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk membatalkan cek');
        }

        // Cannot cancel cashed check
        if ($bukuCek->status === 'dicairkan') {
            return back()->with('error', 'Tidak dapat membatalkan cek yang sudah dicairkan');
        }

        try {
            $bukuCek->update(['status' => 'batal']);

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Cek berhasil dibatalkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan cek: ' . $e->getMessage());
        }
    }

    /**
     * Get buku cek statistics
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            
            $query = BukuCek::query();

            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                $query->whereHas('rekapPengajuan.pencairanDana', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            }

            $stats = [
                'total' => $query->count(),
                'by_status' => [
                    'pending' => (clone $query)->where('status', 'pending')->count(),
                    'ditandatangani' => (clone $query)->where('status', 'ditandatangani')->count(),
                    'dicairkan' => (clone $query)->where('status', 'dicairkan')->count(),
                    'batal' => (clone $query)->where('status', 'batal')->count(),
                ],
                'total_nominal' => $query->sum('nominal'),
                'total_cashed' => (clone $query)->where('status', 'dicairkan')->sum('nominal'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}