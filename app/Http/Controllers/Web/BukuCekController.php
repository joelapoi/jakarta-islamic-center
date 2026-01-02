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
        $user = auth()->user();
        
        // Build query with relationships
        $query = BukuCek::with(['rekapPengajuan.pencairanDana.anggaranKegiatan', 'creator']);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by user role
        // Admin, Kepala JIC, dan Kadiv Umum bisa lihat semua
        // Staff hanya bisa lihat yang dia buat (jika ada)
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $query->where('created_by', $user->id);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $bukuCek = $query->latest()->paginate($perPage)->withQueryString();

        // Calculate statistics
        $statsQuery = BukuCek::query();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $statsQuery->where('created_by', $user->id);
        }

        $statistics = [
            'total' => $statsQuery->count(),
            'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
            'menunggu_ttd_kepala_jic' => (clone $statsQuery)->where('status', 'menunggu_ttd_kepala_jic')->count(),
            'ditandatangani' => (clone $statsQuery)->where('status', 'ditandatangani')->count(),
            'dikonfirmasi_bank' => (clone $statsQuery)->where('status', 'dikonfirmasi_bank')->count(),
            'ditolak' => (clone $statsQuery)->where('status', 'ditolak')->count(),
        ];

        return view('buku-cek.index', compact('bukuCek', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Check permission - Hanya Kadiv Umum dan Kepala JIC yang bisa membuat
        $user = auth()->user();
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat buku cek');
        }

        // Get rekap_id from request
        if (!$request->filled('rekap_id')) {
            return redirect()->route('rekap-pengajuan.index')
                ->with('error', 'Pilih rekap pengajuan terlebih dahulu');
        }

        $selectedRekap = RekapPengajuan::with([
            'pencairanDana.anggaranKegiatan',
            'pencairanDana.creator'
        ])->findOrFail($request->rekap_id);

        // Check if rekap is approved
        if ($selectedRekap->status !== 'disetujui') {
            return redirect()->route('rekap-pengajuan.show', $selectedRekap->id)
                ->with('error', 'Hanya rekap pengajuan yang disetujui yang dapat dibuatkan buku cek');
        }

        // Check if buku cek already exists
        if ($selectedRekap->bukuCek) {
            return redirect()->route('buku-cek.show', $selectedRekap->bukuCek->id)
                ->with('info', 'Buku cek untuk rekap pengajuan ini sudah dibuat');
        }

        return view('buku-cek.create', compact('selectedRekap'));
    }

    /**
     * Store a newly created resource in storage.
     * LANGSUNG submit untuk tanda tangan (skip draft)
     */
    public function store(Request $request)
    {
        // Check permission - Hanya Kadiv Umum dan Kepala JIC yang bisa membuat
        $user = auth()->user();
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat buku cek');
        }

        $validated = $request->validate([
            'rekap_pengajuan_id' => 'required|exists:rekap_pengajuan,id',
            'nomor_cek' => 'nullable|string|max:255',
            'tanggal_cek' => 'nullable|date|before_or_equal:today',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|max:255',
            'nama_penerima' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keperluan' => 'required|string|max:1000',
        ], [
            'rekap_pengajuan_id.required' => 'Rekap pengajuan wajib dipilih',
            'rekap_pengajuan_id.exists' => 'Rekap pengajuan tidak valid',
            'nama_bank.required' => 'Nama bank wajib diisi',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi',
            'nama_penerima.required' => 'Nama penerima wajib diisi',
            'nominal.required' => 'Nominal wajib diisi',
            'nominal.numeric' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal harus lebih dari atau sama dengan 0',
            'keperluan.required' => 'Keperluan wajib diisi',
            'keperluan.max' => 'Keperluan maksimal 1000 karakter',
            'tanggal_cek.date' => 'Tanggal cek harus berupa tanggal yang valid',
            'tanggal_cek.before_or_equal' => 'Tanggal cek tidak boleh lebih dari hari ini',
        ]);

        try {
            DB::beginTransaction();

            // Get rekap pengajuan
            $rekap = RekapPengajuan::findOrFail($validated['rekap_pengajuan_id']);

            // Check if rekap is approved
            if ($rekap->status !== 'disetujui') {
                return back()
                    ->with('error', 'Hanya rekap pengajuan yang disetujui yang dapat dibuatkan buku cek')
                    ->withInput();
            }

            // Check if buku cek already exists
            if ($rekap->bukuCek) {
                return redirect()->route('buku-cek.show', $rekap->bukuCek->id)
                    ->with('info', 'Buku cek untuk rekap pengajuan ini sudah dibuat');
            }

            // Validate nominal tidak melebihi sisa dana
            if ($validated['nominal'] > $rekap->sisa_dana) {
                return back()
                    ->with('error', 'Nominal melebihi sisa dana yang tersedia (Rp ' . number_format($rekap->sisa_dana, 0, ',', '.') . ')')
                    ->withInput();
            }

            // Generate nomor buku cek
            $year = date('Y');
            $lastBukuCek = BukuCek::whereYear('created_at', $year)
                ->latest('id')
                ->first();
            
            $number = $lastBukuCek ? (intval(substr($lastBukuCek->nomor_buku_cek, -4)) + 1) : 1;
            $nomorBukuCek = 'BC-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            // Create buku cek
            // LANGSUNG dengan status "menunggu_ttd_kepala_jic" (SKIP DRAFT)
            $bukuCek = BukuCek::create([
                'rekap_pengajuan_id' => $validated['rekap_pengajuan_id'],
                'nomor_buku_cek' => $nomorBukuCek,
                'nomor_cek' => $validated['nomor_cek'] ?? null,
                'tanggal_cek' => $validated['tanggal_cek'] ?? null,
                'nominal' => $validated['nominal'],
                'bank_name' => $validated['nama_bank'],
                'nama_bank' => $validated['nama_bank'],
                'nomor_rekening' => $validated['nomor_rekening'],
                'nama_penerima' => $validated['nama_penerima'],
                'penerima' => $validated['nama_penerima'],
                'keperluan' => $validated['keperluan'],
                'keterangan' => $validated['keperluan'],
                'status' => 'menunggu_ttd_kepala_jic', // ← LANGSUNG SKIP DRAFT
                'created_by' => auth()->id(),
                'submitted_at' => now(), // ← Langsung set submitted_at
            ]);

            DB::commit();

            return redirect()->route('buku-cek.show', $bukuCek->id)
                ->with('success', 'Buku cek berhasil dibuat dan langsung diajukan untuk ditandatangani');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withErrors(['error' => 'Gagal membuat buku cek: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BukuCek $bukuCek)
    {
        $bukuCek->load([
            'rekapPengajuan.pencairanDana.anggaranKegiatan',
            'rekapPengajuan.pencairanDana.creator',
            'creator',
            'signedBy',
            'confirmedBy',
            'rejectedBy',
            'documents'
        ]);

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            if ($bukuCek->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke buku cek ini');
            }
        }

        // Check permissions for actions
        // Edit: TIDAK BISA lagi karena langsung ke status menunggu TTD
        // Hanya admin/super_admin yang bisa edit untuk perbaikan darurat
        $canEdit = $bukuCek->status === 'draft' && 
                   $user->hasAnyRole(['super_admin', 'admin']);
        
        // Delete: TIDAK BISA karena tidak ada draft lagi
        // Hanya admin/super_admin yang bisa hapus
        $canDelete = $bukuCek->status === 'draft' && 
                     $user->hasAnyRole(['super_admin', 'admin']);
        
        // Submit: TIDAK DIPERLUKAN lagi karena otomatis submit saat create
        $canSubmit = false;
        
        // Sign: Hanya Kepala JIC atau admin/super_admin yang bisa tanda tangan
        $canSign = $bukuCek->canBeSigned() && 
                   $user->hasAnyRole(['kepala_jic', 'super_admin', 'admin']);
        
        // Confirm: Hanya Kadiv Umum atau admin/super_admin yang bisa konfirmasi bank
        $canConfirm = $bukuCek->canBeConfirmed() && 
                      $user->hasAnyRole(['kadiv_umum', 'super_admin', 'admin']);
        
        // Reject: Kepala JIC dan Kadiv Umum bisa tolak
        $canReject = $bukuCek->canBeRejected() && 
                     $user->hasAnyRole(['kepala_jic', 'kadiv_umum', 'super_admin', 'admin']);

        return view('buku-cek.show', compact(
            'bukuCek', 
            'canEdit', 
            'canDelete', 
            'canSubmit', 
            'canSign', 
            'canConfirm', 
            'canReject'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * HANYA untuk admin/super_admin sebagai emergency fix
     */
    public function edit(BukuCek $bukuCek)
    {
        // Check if can edit - Hanya admin/super_admin
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Hanya admin yang dapat mengedit buku cek');
        }

        $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

        return view('buku-cek.edit', compact('bukuCek'));
    }

    /**
     * Update the specified resource in storage.
     * HANYA untuk admin/super_admin sebagai emergency fix
     */
    public function update(Request $request, BukuCek $bukuCek)
    {
        // Check permission - Hanya admin/super_admin
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Hanya admin yang dapat mengedit buku cek');
        }

        $validated = $request->validate([
            'nomor_cek' => 'nullable|string|max:255',
            'tanggal_cek' => 'nullable|date|before_or_equal:today',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|max:255',
            'nama_penerima' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keperluan' => 'required|string|max:1000',
        ], [
            'nama_bank.required' => 'Nama bank wajib diisi',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi',
            'nama_penerima.required' => 'Nama penerima wajib diisi',
            'nominal.required' => 'Nominal wajib diisi',
            'keperluan.required' => 'Keperluan wajib diisi',
        ]);

        // Load rekap pengajuan
        $bukuCek->load('rekapPengajuan');

        // Validate nominal tidak melebihi sisa dana
        if ($validated['nominal'] > $bukuCek->rekapPengajuan->sisa_dana) {
            return back()
                ->with('error', 'Nominal melebihi sisa dana yang tersedia (Rp ' . number_format($bukuCek->rekapPengajuan->sisa_dana, 0, ',', '.') . ')')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $bukuCek->update([
                'nomor_cek' => $validated['nomor_cek'] ?? null,
                'tanggal_cek' => $validated['tanggal_cek'] ?? null,
                'nominal' => $validated['nominal'],
                'bank_name' => $validated['nama_bank'],
                'nama_bank' => $validated['nama_bank'],
                'nomor_rekening' => $validated['nomor_rekening'],
                'nama_penerima' => $validated['nama_penerima'],
                'penerima' => $validated['nama_penerima'],
                'keperluan' => $validated['keperluan'],
                'keterangan' => $validated['keperluan'],
            ]);

            DB::commit();

            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('success', 'Buku cek berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withErrors(['error' => 'Gagal memperbarui buku cek: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * HANYA untuk admin/super_admin sebagai emergency
     */
    public function destroy(BukuCek $bukuCek)
    {
        // Check permission - Hanya admin/super_admin
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Hanya admin yang dapat menghapus buku cek');
        }

        try {
            $bukuCek->delete();
            
            return redirect()->route('buku-cek.index')
                ->with('success', 'Buku cek berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('buku-cek.index')
                ->with('error', 'Gagal menghapus buku cek: ' . $e->getMessage());
        }
    }

    /**
     * Submit buku cek for approval
     * METHOD INI TIDAK DIPERLUKAN LAGI karena otomatis submit saat create
     */
    public function submit(BukuCek $bukuCek)
    {
        return redirect()->route('buku-cek.show', $bukuCek)
            ->with('info', 'Buku cek sudah otomatis diajukan saat dibuat');
    }

    /**
     * Sign the check (Kepala JIC only)
     */
    public function sign(BukuCek $bukuCek)
    {
        $user = auth()->user();

        // Check permission - Hanya Kepala JIC atau admin/super_admin
        if (!$user->hasAnyRole(['kepala_jic', 'super_admin', 'admin'])) {
            abort(403, 'Hanya Kepala JIC yang dapat menandatangani buku cek');
        }

        // Check if can be signed
        if (!$bukuCek->canBeSigned()) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Hanya buku cek yang menunggu tanda tangan yang dapat ditandatangani');
        }

        try {
            $bukuCek->update([
                'status' => 'ditandatangani',
                'signed_at' => now(),
                'signed_by' => $user->id
            ]);

            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('success', 'Buku cek berhasil ditandatangani');
        } catch (\Exception $e) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Gagal menandatangani buku cek: ' . $e->getMessage());
        }
    }

    /**
     * Confirm the check by bank (Kadiv Umum)
     */
    public function cash(BukuCek $bukuCek)
    {
        $user = auth()->user();

        // Check permission - Hanya Kadiv Umum atau admin/super_admin
        if (!$user->hasAnyRole(['kadiv_umum', 'super_admin', 'admin'])) {
            abort(403, 'Hanya Kadiv Umum yang dapat mengkonfirmasi buku cek');
        }

        // Check if can be confirmed
        if (!$bukuCek->canBeConfirmed()) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Buku cek harus ditandatangani terlebih dahulu sebelum dikonfirmasi');
        }

        try {
            $bukuCek->update([
                'status' => 'dikonfirmasi_bank',
                'confirmed_at' => now(),
                'confirmed_by' => $user->id
            ]);

            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('success', 'Buku cek berhasil dikonfirmasi oleh bank');
        } catch (\Exception $e) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Gagal mengkonfirmasi buku cek: ' . $e->getMessage());
        }
    }

    /**
     * Reject/Cancel the check (Kadiv Umum, Kepala JIC)
     */
    public function cancel(Request $request, BukuCek $bukuCek)
    {
        $user = auth()->user();

        // Check permission - Kepala JIC dan Kadiv Umum bisa tolak
        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can be rejected
        if (!$bukuCek->canBeRejected()) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Buku cek tidak dapat ditolak pada status: ' . $bukuCek->status);
        }

        // Validate rejection reason
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:1000'
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi'
        ]);

        try {
            $bukuCek->update([
                'status' => 'ditolak',
                'rejected_at' => now(),
                'rejected_by' => $user->id,
                'alasan_penolakan' => $validated['alasan_penolakan']
            ]);

            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('success', 'Buku cek berhasil ditolak');
        } catch (\Exception $e) {
            return redirect()->route('buku-cek.show', $bukuCek)
                ->with('error', 'Gagal menolak buku cek: ' . $e->getMessage());
        }
    }
}