<?php

ontroller;
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pencairan', 'ILIKE', "%{$search}%")
                  ->orWhere('keperluan', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('anggaran_kegiatan_id')) {
            $query->where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id);
        }

        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
            $query->where('created_by', $user->id);
        }

        $pencairan = $query->latest()->paginate(15);
        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('pencairan-dana.index', compact('pencairan', 'anggaranList'));
    }

    public function create()
    {
        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->orderBy('nama_kegiatan')
            ->get();
            
        return view('pencairan-dana.create', compact('anggaranList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggaran_kegiatan_id' => 'required|exists:anggaran_kegiatan,id',
            'jumlah_pencairan' => 'required|numeric|min:0',
            'keperluan' => 'required|string|max:1000',
        ], [
            'anggaran_kegiatan_id.required' => 'Anggaran kegiatan wajib dipilih',
            'jumlah_pencairan.required' => 'Jumlah pencairan wajib diisi',
            'jumlah_pencairan.min' => 'Jumlah pencairan harus lebih dari atau sama dengan 0',
            'keperluan.required' => 'Keperluan wajib diisi',
        ]);

        $anggaran = AnggaranKegiatan::findOrFail($request->anggaran_kegiatan_id);
        
        if ($anggaran->status !== 'disetujui_kepala_jic') {
            return back()->with('error', 'Anggaran kegiatan harus disetujui penuh terlebih dahulu')
                ->withInput();
        }

        // Check available budget
        $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id)
            ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
            ->sum('jumlah_pencairan');

        $sisaAnggaran = $anggaran->anggaran_disetujui - $totalPencairan;

        if ($request->jumlah_pencairan > $sisaAnggaran) {
            return back()
                ->with('error', "Jumlah pencairan melebihi sisa anggaran. Sisa anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.'))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $year = date('Y');
            $lastPencairan = PencairanDana::whereYear('created_at', $year)->latest('id')->first();
            $number = $lastPencairan ? (intval(substr($lastPencairan->nomor_pencairan, -4)) + 1) : 1;
            $nomorPencairan = 'PC-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $pencairan = PencairanDana::create([
                'nomor_pencairan' => $nomorPencairan,
                'anggaran_kegiatan_id' => $request->anggaran_kegiatan_id,
                'jumlah_pencairan' => $request->jumlah_pencairan,
                'keperluan' => $request->keperluan,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', 'Pencairan dana berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat pencairan dana: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $pencairan = PencairanDana::with([
            'anggaranKegiatan',
            'creator',
            'approver',
            'rekapPengajuan',
            'documents'
        ])->findOrFail($id);

        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
            if ($pencairan->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pencairan dana ini');
            }
        }

        return view('pencairan-dana.show', compact('pencairan'));
    }

    public function edit($id)
    {
        $pencairan = PencairanDana::findOrFail($id);

        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit pencairan dana dengan status: ' . $pencairan->status);
        }

        $anggaranList = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('pencairan-dana.edit', compact('pencairan', 'anggaranList'));
    }

    public function update(Request $request, $id)
    {
        $pencairan = PencairanDana::findOrFail($id);

        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengedit pencairan dana dengan status: ' . $pencairan->status);
        }

        $request->validate([
            'jumlah_pencairan' => 'required|numeric|min:0',
            'keperluan' => 'required|string|max:1000',
        ]);

        // Check budget if amount is being updated
        if ($request->jumlah_pencairan != $pencairan->jumlah_pencairan) {
            $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $pencairan->anggaran_kegiatan_id)
                ->where('id', '!=', $id)
                ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
                ->sum('jumlah_pencairan');

            $sisaAnggaran = $pencairan->anggaranKegiatan->anggaran_disetujui - $totalPencairan;

            if ($request->jumlah_pencairan > $sisaAnggaran) {
                return back()
                    ->with('error', "Jumlah pencairan melebihi sisa anggaran. Sisa anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.'))
                    ->withInput();
            }
        }

        try {
            $updateData = $request->only(['jumlah_pencairan', 'keperluan']);
            
            if ($pencairan->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $pencairan->update($updateData);

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', 'Pencairan dana berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui pencairan dana: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $pencairan = PencairanDana::findOrFail($id);

        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if ($pencairan->status !== 'draft') {
            return back()->with('error', 'Hanya pencairan dana dengan status draft yang dapat dihapus');
        }

        try {
            $pencairan->delete();
            return redirect()->route('pencairan-dana.index')
                ->with('success', 'Pencairan dana berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pencairan dana: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $pencairan = PencairanDana::findOrFail($id);

        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Hanya pencairan dana draft atau ditolak yang dapat diajukan');
        }

        try {
            $pencairan->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', 'Pencairan dana berhasil diajukan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan pencairan dana: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $pencairan = PencairanDana::findOrFail($id);
        $user = auth()->user();
        $currentStatus = $pencairan->status;
        $newStatus = null;
        $message = '';

        switch ($currentStatus) {
            case 'diajukan':
                if ($user->hasRole('kadiv_umum')) {
                    $newStatus = 'disetujui_kadiv_umum';
                    $message = 'Pencairan dana berhasil disetujui oleh Kadiv Umum';
                } else {
                    return back()->with('error', 'Hanya Kadiv Umum yang dapat menyetujui pada tahap ini');
                }
                break;

            case 'disetujui_kadiv_umum':
                if ($user->hasRole('kepala_jic')) {
                    $newStatus = 'disetujui_kepala_jic';
                    $message = 'Pencairan dana berhasil disetujui penuh oleh Kepala JIC';
                    
                    $pencairan->approved_by = $user->id;
                    $pencairan->approved_at = now();
                } else {
                    return back()->with('error', 'Hanya Kepala JIC yang dapat menyetujui pada tahap ini');
                }
                break;

            default:
                return back()->with('error', 'Tidak dapat menyetujui pencairan dana dengan status: ' . $currentStatus);
        }

        try {
            $pencairan->status = $newStatus;
            $pencairan->catatan = $request->catatan;
            $pencairan->save();

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui pencairan dana: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan wajib diisi saat menolak'
        ]);

        $pencairan = PencairanDana::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        try {
            $pencairan->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', 'Pencairan dana berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak pencairan dana: ' . $e->getMessage());
        }
    }

    public function disburse($id)
    {
        $pencairan = PencairanDana::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
            abort(403);
        }

        if ($pencairan->status !== 'disetujui_kepala_jic') {
            return back()->with('error', 'Pencairan dana harus disetujui penuh terlebih dahulu');
        }

        try {
            $pencairan->update([
                'status' => 'dicairkan',
                'disbursed_at' => now()
            ]);

            return redirect()->route('pencairan-dana.show', $pencairan->id)
                ->with('success', 'Pencairan dana berhasil dicairkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencairkan dana: ' . $e->getMessage());
        }
    }
}