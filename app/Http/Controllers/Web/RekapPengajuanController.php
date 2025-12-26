<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RekapPengajuan;
use App\Models\PencairanDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapPengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = RekapPengajuan::with(['pencairanDana.anggaranKegiatan', 'pencairanDana.creator']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_rekap', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencairan filter
        if ($request->filled('pencairan_dana_id')) {
            $query->where('pencairan_dana_id', $request->pencairan_dana_id);
        }

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $query->whereHas('pencairanDana', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        $rekap = $query->latest()->paginate(15)->withQueryString();
        
        // Calculate statistics
        $statistics = $this->getStatistics();

        return view('rekap-pengajuan.index', compact('rekap', 'statistics'));
    }

    /**
     * Get statistics for dashboard
     */
    private function getStatistics()
    {
        $baseQuery = RekapPengajuan::query();

        // Filter by user role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            $baseQuery->whereHas('pencairanDana', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        $totalPengeluaran = (clone $baseQuery)->sum('total_pengeluaran');
        $totalSisaDana = (clone $baseQuery)->sum('sisa_dana');
        
        $byStatus = [
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'diajukan' => (clone $baseQuery)->where('status', 'diajukan')->count(),
            'disetujui' => (clone $baseQuery)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        return [
            'total_pengeluaran' => $totalPengeluaran,
            'total_sisa_dana' => $totalSisaDana,
            'by_status' => $byStatus
        ];
    }

    public function create(Request $request)
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['staff', 'super_admin', 'admin'])) {
            abort(403, 'Hanya staff yang dapat membuat rekap pengajuan');
        }

        // Get pencairan list with search functionality
        $query = PencairanDana::where('status', 'dicairkan')
            ->with(['anggaranKegiatan'])
            ->whereDoesntHave('rekapPengajuan')
            ->orderBy('created_at', 'desc');

        // Filter by creator if not admin
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $query->where('created_by', $user->id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pencairan', 'ILIKE', "%{$search}%")
                  ->orWhereHas('anggaranKegiatan', function($subQ) use ($search) {
                      $subQ->where('nama_kegiatan', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $pencairanList = $query->paginate(10)->withQueryString();

        // Get selected pencairan detail if pencairan_id is provided
        $selectedPencairan = null;
        if ($request->filled('pencairan_id')) {
            $selectedPencairan = PencairanDana::with('anggaranKegiatan')
                ->find($request->pencairan_id);
        }
            
        return view('rekap-pengajuan.create', compact('pencairanList', 'selectedPencairan'));
    }

    public function store(Request $request)
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['staff', 'super_admin', 'admin'])) {
            abort(403, 'Hanya staff yang dapat membuat rekap pengajuan');
        }

        // Clean total_pengeluaran from currency format
        $totalPengeluaran = str_replace(['.', ',', 'Rp', ' '], '', $request->total_pengeluaran);

        $request->merge([
            'total_pengeluaran' => $totalPengeluaran
        ]);

        $validated = $request->validate([
            'pencairan_dana_id' => 'required|exists:pencairan_dana,id',
            'total_pengeluaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
            'submit_type' => 'nullable|in:draft,submit' // draft or submit
        ], [
            'pencairan_dana_id.required' => 'Pencairan dana wajib dipilih',
            'pencairan_dana_id.exists' => 'Pencairan dana tidak valid',
            'total_pengeluaran.required' => 'Total pengeluaran wajib diisi',
            'total_pengeluaran.numeric' => 'Total pengeluaran harus berupa angka',
            'total_pengeluaran.min' => 'Total pengeluaran harus lebih dari atau sama dengan 0',
            'catatan.max' => 'Catatan maksimal 1000 karakter',
        ]);

        $pencairan = PencairanDana::findOrFail($validated['pencairan_dana_id']);
        
        if ($pencairan->status !== 'dicairkan') {
            return back()->with('error', 'Pencairan dana harus sudah dicairkan terlebih dahulu')
                ->withInput();
        }

        // Check permission for this specific pencairan
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat rekap pengajuan ini')
                ->withInput();
        }

        // Check if rekap already exists
        $existingRekap = RekapPengajuan::where('pencairan_dana_id', $validated['pencairan_dana_id'])->first();
        if ($existingRekap) {
            return back()
                ->with('error', 'Pencairan dana ini sudah memiliki rekap pengajuan')
                ->withInput();
        }

        if ($validated['total_pengeluaran'] > $pencairan->jumlah_pencairan) {
            return back()
                ->with('error', "Total pengeluaran melebihi jumlah pencairan. Jumlah pencairan: Rp " . number_format($pencairan->jumlah_pencairan, 0, ',', '.'))
                ->withInput();
        }

        if ($validated['total_pengeluaran'] <= 0) {
            return back()
                ->with('error', 'Total pengeluaran harus lebih dari 0')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate nomor rekap
            $year = date('Y');
            $lastRekap = RekapPengajuan::whereYear('created_at', $year)
                ->latest('id')
                ->first();
            
            $number = $lastRekap ? (intval(substr($lastRekap->nomor_rekap, -4)) + 1) : 1;
            $nomorRekap = 'RK-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $sisaDana = $pencairan->jumlah_pencairan - $validated['total_pengeluaran'];

            // Determine status based on submit_type
            $status = ($request->submit_type === 'submit') ? 'diajukan' : 'draft';

            $rekap = RekapPengajuan::create([
                'pencairan_dana_id' => $validated['pencairan_dana_id'],
                'nomor_rekap' => $nomorRekap,
                'total_pengeluaran' => $validated['total_pengeluaran'],
                'sisa_dana' => $sisaDana,
                'status' => $status,
                'catatan' => $validated['catatan'],
            ]);

            DB::commit();

            $message = ($status === 'diajukan') 
                ? 'Rekap pengajuan berhasil dibuat dan diajukan untuk persetujuan'
                : 'Rekap pengajuan berhasil dibuat sebagai draft';

            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Gagal membuat rekap pengajuan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(RekapPengajuan $rekapPengajuan)
    {
        $rekap = $rekapPengajuan->load([
            'pencairanDana.anggaranKegiatan',
            'pencairanDana.creator',
            'bukuCek',
            'documents'
        ]);

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            if ($rekap->pencairanDana->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke rekap pengajuan ini');
            }
        }

        // Check if user can approve
        $canApprove = $this->canUserApprove($rekap, $user);

        // Check if user can edit
        $canEdit = $this->canUserEdit($rekap, $user);

        // Check if user can delete
        $canDelete = $this->canUserDelete($rekap, $user);

        return view('rekap-pengajuan.show', compact('rekap', 'canApprove', 'canEdit', 'canDelete'));
    }

    /**
     * Check if user can approve this rekap
     */
    private function canUserApprove(RekapPengajuan $rekap, $user)
    {
        if ($rekap->status !== 'diajukan') {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum']);
    }

    /**
     * Check if user can edit this rekap
     */
    private function canUserEdit(RekapPengajuan $rekap, $user)
    {
        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $rekap->pencairanDana->created_by === $user->id;
    }

    /**
     * Check if user can delete this rekap
     */
    private function canUserDelete(RekapPengajuan $rekap, $user)
    {
        if ($rekap->status !== 'draft') {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $rekap->pencairanDana->created_by === $user->id;
    }

    public function edit(RekapPengajuan $rekapPengajuan)
    {
        $rekap = $rekapPengajuan->load('pencairanDana.anggaranKegiatan');

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit rekap pengajuan ini');
        }

        // Check if can edit
        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('error', 'Tidak dapat mengedit rekap pengajuan dengan status: ' . $rekap->status);
        }

        return view('rekap-pengajuan.edit', compact('rekap'));
    }

    public function update(Request $request, RekapPengajuan $rekapPengajuan)
    {
        $rekap = $rekapPengajuan->load('pencairanDana');

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit rekap pengajuan ini');
        }

        // Check if can edit
        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('error', 'Tidak dapat mengedit rekap pengajuan dengan status: ' . $rekap->status);
        }

        // Clean total_pengeluaran from currency format
        $totalPengeluaran = str_replace(['.', ',', 'Rp', ' '], '', $request->total_pengeluaran);

        $request->merge([
            'total_pengeluaran' => $totalPengeluaran
        ]);

        $validated = $request->validate([
            'total_pengeluaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
            'submit_type' => 'nullable|in:draft,submit'
        ], [
            'total_pengeluaran.required' => 'Total pengeluaran wajib diisi',
            'total_pengeluaran.numeric' => 'Total pengeluaran harus berupa angka',
            'total_pengeluaran.min' => 'Total pengeluaran harus lebih dari atau sama dengan 0',
            'catatan.max' => 'Catatan maksimal 1000 karakter',
        ]);

        if ($validated['total_pengeluaran'] > $rekap->pencairanDana->jumlah_pencairan) {
            return back()
                ->with('error', "Total pengeluaran melebihi jumlah pencairan. Jumlah pencairan: Rp " . number_format($rekap->pencairanDana->jumlah_pencairan, 0, ',', '.'))
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'total_pengeluaran' => $validated['total_pengeluaran'],
                'sisa_dana' => $rekap->pencairanDana->jumlah_pencairan - $validated['total_pengeluaran'],
                'catatan' => $validated['catatan'],
            ];

            // Determine status
            if ($request->submit_type === 'submit') {
                $updateData['status'] = 'diajukan';
            } elseif ($rekap->status === 'ditolak') {
                $updateData['status'] = 'draft';
            }

            $rekap->update($updateData);

            DB::commit();

            $message = ($request->submit_type === 'submit')
                ? 'Rekap pengajuan berhasil diperbarui dan diajukan'
                : 'Rekap pengajuan berhasil diperbarui';

            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withErrors(['error' => 'Gagal memperbarui rekap pengajuan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(RekapPengajuan $rekapPengajuan)
    {
        $rekap = $rekapPengajuan->load('pencairanDana');

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Only draft can be deleted
        if ($rekap->status !== 'draft') {
            return redirect()->route('rekap-pengajuan.index')
                ->with('error', 'Hanya rekap pengajuan dengan status draft yang dapat dihapus');
        }

        try {
            $rekap->delete();
            
            return redirect()->route('rekap-pengajuan.index')
                ->with('success', 'Rekap pengajuan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('rekap-pengajuan.index')
                ->with('error', 'Gagal menghapus rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function submit(RekapPengajuan $rekapPengajuan)
    {
        $rekap = $rekapPengajuan->load('pencairanDana');

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        // Check if can be submitted
        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('error', 'Hanya rekap pengajuan draft atau ditolak yang dapat diajukan');
        }

        try {
            $rekap->update(['status' => 'diajukan']);

            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('success', 'Rekap pengajuan berhasil diajukan');
        } catch (\Exception $e) {
            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('error', 'Gagal mengajukan rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, RekapPengajuan $rekapPengajuan)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $rekap = $rekapPengajuan;
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        if ($rekap->status !== 'diajukan') {
            return back()->with('error', 'Hanya rekap pengajuan yang diajukan yang dapat disetujui');
        }

        try {
            $rekap->update([
                'status' => 'disetujui',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('success', 'Rekap pengajuan berhasil disetujui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, RekapPengajuan $rekapPengajuan)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $rekap = $rekapPengajuan;
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        try {
            $rekap->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('rekap-pengajuan.show', $rekap)
                ->with('success', 'Rekap pengajuan berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak rekap pengajuan: ' . $e->getMessage());
        }
    }
}