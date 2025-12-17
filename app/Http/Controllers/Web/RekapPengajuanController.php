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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_rekap', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pencairan_dana_id')) {
            $query->where('pencairan_dana_id', $request->pencairan_dana_id);
        }

        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
            $query->whereHas('pencairanDana', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        $rekap = $query->latest()->paginate(15);
        $pencairanList = PencairanDana::where('status', 'dicairkan')
            ->with('anggaranKegiatan')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rekap-pengajuan.index', compact('rekap', 'pencairanList'));
    }

    public function create()
    {
        $pencairanList = PencairanDana::where('status', 'dicairkan')
            ->with('anggaranKegiatan')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('rekap-pengajuan.create', compact('pencairanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pencairan_dana_id' => 'required|exists:pencairan_dana,id',
            'total_pengeluaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'pencairan_dana_id.required' => 'Pencairan dana wajib dipilih',
            'total_pengeluaran.required' => 'Total pengeluaran wajib diisi',
            'total_pengeluaran.min' => 'Total pengeluaran harus lebih dari atau sama dengan 0',
        ]);

        $pencairan = PencairanDana::findOrFail($request->pencairan_dana_id);
        
        if ($pencairan->status !== 'dicairkan') {
            return back()->with('error', 'Pencairan dana harus sudah dicairkan terlebih dahulu')
                ->withInput();
        }

        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat rekap pengajuan ini')
                ->withInput();
        }

        if ($request->total_pengeluaran > $pencairan->jumlah_pencairan) {
            return back()
                ->with('error', "Total pengeluaran melebihi jumlah pencairan. Jumlah pencairan: Rp " . number_format($pencairan->jumlah_pencairan, 0, ',', '.'))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $year = date('Y');
            $lastRekap = RekapPengajuan::whereYear('created_at', $year)->latest('id')->first();
            $number = $lastRekap ? (intval(substr($lastRekap->nomor_rekap, -4)) + 1) : 1;
            $nomorRekap = 'RK-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $sisaDana = $pencairan->jumlah_pencairan - $request->total_pengeluaran;

            $rekap = RekapPengajuan::create([
                'pencairan_dana_id' => $request->pencairan_dana_id,
                'nomor_rekap' => $nomorRekap,
                'total_pengeluaran' => $request->total_pengeluaran,
                'sisa_dana' => $sisaDana,
                'status' => 'draft',
                'catatan' => $request->catatan,
            ]);

            DB::commit();

            return redirect()->route('rekap-pengajuan.show', $rekap->id)
                ->with('success', 'Rekap pengajuan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat rekap pengajuan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $rekap = RekapPengajuan::with([
            'pencairanDana.anggaranKegiatan',
            'pencairanDana.creator',
            'bukuCek',
            'documents'
        ])->findOrFail($id);

        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            if ($rekap->pencairanDana->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke rekap pengajuan ini');
            }
        }

        return view('rekap-pengajuan.show', compact('rekap'));
    }

    public function edit($id)
    {
        $rekap = RekapPengajuan::with('pencairanDana')->findOrFail($id);

        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit rekap pengajuan dengan status: ' . $rekap->status);
        }

        return view('rekap-pengajuan.edit', compact('rekap'));
    }

    public function update(Request $request, $id)
    {
        $rekap = RekapPengajuan::with('pencairanDana')->findOrFail($id);

        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit rekap pengajuan dengan status: ' . $rekap->status);
        }

        $request->validate([
            'total_pengeluaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($request->total_pengeluaran > $rekap->pencairanDana->jumlah_pencairan) {
            return back()
                ->with('error', "Total pengeluaran melebihi jumlah pencairan. Jumlah pencairan: Rp " . number_format($rekap->pencairanDana->jumlah_pencairan, 0, ',', '.'))
                ->withInput();
        }

        try {
            $updateData = [
                'total_pengeluaran' => $request->total_pengeluaran,
                'sisa_dana' => $rekap->pencairanDana->jumlah_pencairan - $request->total_pengeluaran,
                'catatan' => $request->catatan,
            ];

            if ($rekap->status === 'ditolak') {
                $updateData['status'] = 'draft';
            }

            $rekap->update($updateData);

            return redirect()->route('rekap-pengajuan.show', $rekap->id)
                ->with('success', 'Rekap pengajuan berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui rekap pengajuan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $rekap = RekapPengajuan::with('pencairanDana')->findOrFail($id);

        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if ($rekap->status !== 'draft') {
            return back()->with('error', 'Hanya rekap pengajuan dengan status draft yang dapat dihapus');
        }

        try {
            $rekap->delete();
            return redirect()->route('rekap-pengajuan.index')
                ->with('success', 'Rekap pengajuan berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $rekap = RekapPengajuan::with('pencairanDana')->findOrFail($id);

        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Hanya rekap pengajuan draft atau ditolak yang dapat diajukan');
        }

        try {
            $rekap->update(['status' => 'diajukan']);

            return redirect()->route('rekap-pengajuan.show', $rekap->id)
                ->with('success', 'Rekap pengajuan berhasil diajukan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $rekap = RekapPengajuan::findOrFail($id);
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

            return redirect()->route('rekap-pengajuan.show', $rekap->id)
                ->with('success', 'Rekap pengajuan berhasil disetujui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui rekap pengajuan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $rekap = RekapPengajuan::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        try {
            $rekap->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('rekap-pengajuan.show', $rekap->id)
                ->with('success', 'Rekap pengajuan berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak rekap pengajuan: ' . $e->getMessage());
        }
    }
}