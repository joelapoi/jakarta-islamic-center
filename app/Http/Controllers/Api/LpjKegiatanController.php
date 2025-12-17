<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LpjKegiatan;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LpjKegiatanController extends Controller
{
    /**
     * Display a listing of LPJ kegiatan
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
            $anggaranId = $request->input('anggaran_kegiatan_id');

            $query = LpjKegiatan::with(['anggaranKegiatan', 'creator', 'approver']);

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_lpj', 'ILIKE', "%{$search}%")
                      ->orWhereHas('anggaranKegiatan', function($q2) use ($search) {
                          $q2->where('nama_kegiatan', 'ILIKE', "%{$search}%")
                             ->orWhere('kode_kegiatan', 'ILIKE', "%{$search}%");
                      });
                });
            }

            // Status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Anggaran filter
            if ($anggaranId) {
                $query->where('anggaran_kegiatan_id', $anggaranId);
            }

            // Filter by user role
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                $query->where('created_by', $user->id);
            }

            $lpj = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan retrieved successfully',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created LPJ kegiatan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'anggaran_kegiatan_id' => 'required|exists:anggaran_kegiatan,id',
            'total_realisasi' => 'required|numeric|min:0',
            'laporan_kegiatan' => 'required|string',
        ], [
            'anggaran_kegiatan_id.required' => 'Anggaran kegiatan is required',
            'anggaran_kegiatan_id.exists' => 'Anggaran kegiatan not found',
            'total_realisasi.required' => 'Total realisasi is required',
            'total_realisasi.min' => 'Total realisasi must be greater than or equal to 0',
            'laporan_kegiatan.required' => 'Laporan kegiatan is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if anggaran is approved
        $anggaran = AnggaranKegiatan::find($request->anggaran_kegiatan_id);
        if ($anggaran->status !== 'disetujui_kepala_jic') {
            return response()->json([
                'success' => false,
                'message' => 'Anggaran kegiatan must be fully approved first'
            ], 403);
        }

        // Check permission - only creator of anggaran can create LPJ
        if ($anggaran->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create LPJ for this anggaran kegiatan'
            ], 403);
        }

        // Check if LPJ already exists for this anggaran
        $existingLpj = LpjKegiatan::where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id)->first();
        if ($existingLpj) {
            return response()->json([
                'success' => false,
                'message' => 'LPJ already exists for this anggaran kegiatan'
            ], 403);
        }

        // Check if total realisasi doesn't exceed budget
        if ($request->total_realisasi > $anggaran->anggaran_disetujui) {
            return response()->json([
                'success' => false,
                'message' => 'Total realisasi exceeds approved budget',
                'approved_budget' => $anggaran->anggaran_disetujui,
                'requested_amount' => $request->total_realisasi
            ], 403);
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

            $lpj->load(['anggaranKegiatan', 'creator']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan created successfully',
                'data' => $lpj
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified LPJ kegiatan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $lpj = LpjKegiatan::with([
                'anggaranKegiatan.pencairanDana',
                'creator',
                'approver',
                'documents'
            ])->find($id);

            if (!$lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ kegiatan not found'
                ], 404);
            }

            // Check permission
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                if ($lpj->created_by !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to view this LPJ kegiatan'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan retrieved successfully',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified LPJ kegiatan
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $lpj = LpjKegiatan::with('anggaranKegiatan')->find($id);

        if (!$lpj) {
            return response()->json([
                'success' => false,
                'message' => 'LPJ kegiatan not found'
            ], 404);
        }

        // Check permission
        if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this LPJ kegiatan'
            ], 403);
        }

        // Check if can edit
        if (!in_array($lpj->status, ['draft', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit LPJ kegiatan with status: ' . $lpj->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'total_realisasi' => 'sometimes|required|numeric|min:0',
            'laporan_kegiatan' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];

            if ($request->has('total_realisasi')) {
                // Check if total realisasi doesn't exceed budget
                if ($request->total_realisasi > $lpj->anggaranKegiatan->anggaran_disetujui) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total realisasi exceeds approved budget',
                        'approved_budget' => $lpj->anggaranKegiatan->anggaran_disetujui
                    ], 403);
                }

                $updateData['total_realisasi'] = $request->total_realisasi;
                $updateData['sisa_anggaran'] = $lpj->anggaranKegiatan->anggaran_disetujui - $request->total_realisasi;
            }

            if ($request->has('laporan_kegiatan')) {
                $updateData['laporan_kegiatan'] = $request->laporan_kegiatan;
            }

            // Reset status to draft if it was rejected
            if ($lpj->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $lpj->update($updateData);
            $lpj->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan updated successfully',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified LPJ kegiatan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $lpj = LpjKegiatan::find($id);

            if (!$lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ kegiatan not found'
                ], 404);
            }

            // Check permission
            if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this LPJ kegiatan'
                ], 403);
            }

            // Only draft can be deleted
            if ($lpj->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft LPJ kegiatan can be deleted'
                ], 403);
            }

            $lpj->delete();

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit LPJ kegiatan for approval
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit($id)
    {
        try {
            $lpj = LpjKegiatan::find($id);

            if (!$lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ kegiatan not found'
                ], 404);
            }

            // Check permission
            if ($lpj->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this LPJ kegiatan'
                ], 403);
            }

            // Check if can be submitted
            if (!in_array($lpj->status, ['draft', 'ditolak'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected LPJ kegiatan can be submitted'
                ], 403);
            }

            $lpj->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            $lpj->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan submitted successfully',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve LPJ kegiatan
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lpj = LpjKegiatan::find($id);

            if (!$lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ kegiatan not found'
                ], 404);
            }

            $user = auth()->user();

            // Check authorization
            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Kadiv Umum or Kepala JIC can approve'
                ], 403);
            }

            // Check if can be approved
            if ($lpj->status !== 'diajukan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted LPJ kegiatan can be approved'
                ], 403);
            }

            $lpj->update([
                'status' => 'disetujui',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'catatan' => $request->input('catatan')
            ]);

            $lpj->load(['anggaranKegiatan', 'creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan approved successfully',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject LPJ kegiatan
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Catatan is required when rejecting'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lpj = LpjKegiatan::find($id);

            if (!$lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ kegiatan not found'
                ], 404);
            }

            $user = auth()->user();

            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reject'
                ], 403);
            }

            $lpj->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            $lpj->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'LPJ kegiatan rejected',
                'data' => $lpj
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject LPJ kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LPJ kegiatan statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            
            $query = LpjKegiatan::query();

            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                $query->where('created_by', $user->id);
            }

            $stats = [
                'total' => $query->count(),
                'by_status' => [
                    'draft' => (clone $query)->where('status', 'draft')->count(),
                    'diajukan' => (clone $query)->where('status', 'diajukan')->count(),
                    'disetujui' => (clone $query)->where('status', 'disetujui')->count(),
                    'ditolak' => (clone $query)->where('status', 'ditolak')->count(),
                ],
                'total_realisasi' => $query->sum('total_realisasi'),
                'total_sisa_anggaran' => $query->sum('sisa_anggaran'),
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