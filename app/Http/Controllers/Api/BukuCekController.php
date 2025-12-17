<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukuCek;
use App\Models\RekapPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BukuCekController extends Controller
{
    /**
     * Display a listing of buku cek
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');
            $rekapId = $request->input('rekap_pengajuan_id');

            $query = BukuCek::with(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_cek', 'ILIKE', "%{$search}%")
                      ->orWhere('penerima', 'ILIKE', "%{$search}%")
                      ->orWhere('bank_name', 'ILIKE', "%{$search}%");
                });
            }

            // Status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Rekap filter
            if ($rekapId) {
                $query->where('rekap_pengajuan_id', $rekapId);
            }

            // Filter by user role
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
                $query->whereHas('rekapPengajuan.pencairanDana', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            }

            $bukuCek = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Buku cek retrieved successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve buku cek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created buku cek
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rekap_pengajuan_id' => 'required|exists:rekap_pengajuan,id',
            'jumlah' => 'required|numeric|min:0',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|max:255',
            'nama_penerima' => 'required|string|max:255',
            'keperluan' => 'nullable|string|max:1000',
        ], [
            'rekap_pengajuan_id.required' => 'Rekap pengajuan is required',
            'rekap_pengajuan_id.exists' => 'Rekap pengajuan not found',
            'jumlah.required' => 'Jumlah dana is required',
            'jumlah.numeric' => 'Jumlah dana harus berupa angka',
            'jumlah.min' => 'Jumlah dana harus lebih dari atau sama dengan 0',
            'nama_bank.required' => 'Nama bank is required',
            'nomor_rekening.required' => 'Nomor rekening is required',
            'nama_penerima.required' => 'Nama penerima is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if rekap is approved
        $rekap = RekapPengajuan::find($request->rekap_pengajuan_id);
        if ($rekap->status !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Rekap pengajuan must be approved first'
            ], 403);
        }

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create buku cek for this rekap pengajuan'
            ], 403);
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

            $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku cek created successfully',
                'data' => $bukuCek
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create buku cek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified buku cek
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $bukuCek = BukuCek::with([
                'rekapPengajuan.pencairanDana.anggaranKegiatan',
                'rekapPengajuan.pencairanDana.creator',
                'documents'
            ])->find($id);

            if (!$bukuCek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku cek not found'
                ], 404);
            }

            // Check permission
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
                if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to view this buku cek'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Buku cek retrieved successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve buku cek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified buku cek
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $bukuCek = BukuCek::with('rekapPengajuan.pencairanDana')->find($id);

        if (!$bukuCek) {
            return response()->json([
                'success' => false,
                'message' => 'Buku cek not found'
            ], 404);
        }

        // Check permission
        if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this buku cek'
            ], 403);
        }

        // Check if can edit
        if ($bukuCek->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit buku cek with status: ' . $bukuCek->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nominal' => 'sometimes|required|numeric|min:0',
            'tanggal_cek' => 'sometimes|required|date',
            'bank_name' => 'sometimes|required|string|max:255',
            'penerima' => 'sometimes|required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['nominal', 'tanggal_cek', 'bank_name', 'penerima', 'keterangan']);
            $bukuCek->update($updateData);
            $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Buku cek updated successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update buku cek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified buku cek
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $bukuCek = BukuCek::with('rekapPengajuan.pencairanDana')->find($id);

            if (!$bukuCek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku cek not found'
                ], 404);
            }

            // Check permission
            if ($bukuCek->rekapPengajuan->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this buku cek'
                ], 403);
            }

            // Only pending can be deleted
            if ($bukuCek->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending buku cek can be deleted'
                ], 403);
            }

            $bukuCek->delete();

            return response()->json([
                'success' => true,
                'message' => 'Buku cek deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete buku cek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sign the check (Kepala JIC only)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sign($id)
    {
        try {
            $bukuCek = BukuCek::find($id);

            if (!$bukuCek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku cek not found'
                ], 404);
            }

            $user = auth()->user();

            // Only Kepala JIC can sign
            if (!$user->hasRole('kepala_jic') && !$user->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Kepala JIC can sign checks'
                ], 403);
            }

            // Check if can be signed
            if ($bukuCek->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending checks can be signed'
                ], 403);
            }

            $bukuCek->update([
                'status' => 'ditandatangani',
                'signed_at' => now()
            ]);

            $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Check signed successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign check',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cash the check
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cash($id)
    {
        try {
            $bukuCek = BukuCek::find($id);

            if (!$bukuCek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku cek not found'
                ], 404);
            }

            $user = auth()->user();

            // Check authorization
            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cash checks'
                ], 403);
            }

            // Check if can be cashed
            if ($bukuCek->status !== 'ditandatangani') {
                return response()->json([
                    'success' => false,
                    'message' => 'Check must be signed first'
                ], 403);
            }

            $bukuCek->update([
                'status' => 'dicairkan',
                'cashed_at' => now()
            ]);

            $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Check cashed successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cash check',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the check
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            $bukuCek = BukuCek::find($id);

            if (!$bukuCek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku cek not found'
                ], 404);
            }

            $user = auth()->user();

            // Check authorization
            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cancel checks'
                ], 403);
            }

            // Cannot cancel cashed check
            if ($bukuCek->status === 'dicairkan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel cashed check'
                ], 403);
            }

            $bukuCek->update(['status' => 'batal']);
            $bukuCek->load(['rekapPengajuan.pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Check cancelled successfully',
                'data' => $bukuCek
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel check',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get buku cek statistics
     * 
     * @return \Illuminate\Http\JsonResponse
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