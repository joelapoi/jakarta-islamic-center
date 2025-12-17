<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RekapPengajuan;
use App\Models\PencairanDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RekapPengajuanController extends Controller
{
    /**
     * Display a listing of rekap pengajuan
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
            $pencairanId = $request->input('pencairan_dana_id');

            $query = RekapPengajuan::with(['pencairanDana.anggaranKegiatan', 'pencairanDana.creator']);

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_rekap', 'ILIKE', "%{$search}%")
                      ->orWhere('catatan', 'ILIKE', "%{$search}%");
                });
            }

            // Status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Pencairan filter
            if ($pencairanId) {
                $query->where('pencairan_dana_id', $pencairanId);
            }

            // Filter by user role
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
                $query->whereHas('pencairanDana', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            }

            $rekap = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan retrieved successfully',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created rekap pengajuan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pencairan_dana_id' => 'required|exists:pencairan_dana,id',
            'total_pengeluaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'pencairan_dana_id.required' => 'Pencairan dana is required',
            'pencairan_dana_id.exists' => 'Pencairan dana not found',
            'total_pengeluaran.required' => 'Total pengeluaran is required',
            'total_pengeluaran.min' => 'Total pengeluaran must be greater than or equal to 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if pencairan is disbursed
        $pencairan = PencairanDana::find($request->pencairan_dana_id);
        if ($pencairan->status !== 'dicairkan') {
            return response()->json([
                'success' => false,
                'message' => 'Pencairan dana must be disbursed first'
            ], 403);
        }

        // Check permission - only creator of pencairan can create rekap
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create rekap for this pencairan dana'
            ], 403);
        }

        // Check if total pengeluaran doesn't exceed pencairan amount
        if ($request->total_pengeluaran > $pencairan->jumlah_pencairan) {
            return response()->json([
                'success' => false,
                'message' => 'Total pengeluaran exceeds pencairan amount',
                'pencairan_amount' => $pencairan->jumlah_pencairan,
                'requested_amount' => $request->total_pengeluaran
            ], 403);
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

            // Calculate sisa dana
            $sisaDana = $pencairan->jumlah_pencairan - $request->total_pengeluaran;

            $rekap = RekapPengajuan::create([
                'pencairan_dana_id' => $request->pencairan_dana_id,
                'nomor_rekap' => $nomorRekap,
                'total_pengeluaran' => $request->total_pengeluaran,
                'sisa_dana' => $sisaDana,
                'status' => 'draft',
                'catatan' => $request->catatan,
            ]);

            $rekap->load(['pencairanDana.anggaranKegiatan']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan created successfully',
                'data' => $rekap
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rekap pengajuan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $rekap = RekapPengajuan::with([
                'pencairanDana.anggaranKegiatan',
                'pencairanDana.creator',
                'bukuCek',
                'documents'
            ])->find($id);

            if (!$rekap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap pengajuan not found'
                ], 404);
            }

            // Check permission
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                if ($rekap->pencairanDana->created_by !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to view this rekap pengajuan'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan retrieved successfully',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rekap pengajuan
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rekap = RekapPengajuan::with('pencairanDana')->find($id);

        if (!$rekap) {
            return response()->json([
                'success' => false,
                'message' => 'Rekap pengajuan not found'
            ], 404);
        }

        // Check permission
        if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this rekap pengajuan'
            ], 403);
        }

        // Check if can edit
        if (!in_array($rekap->status, ['draft', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit rekap pengajuan with status: ' . $rekap->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'total_pengeluaran' => 'sometimes|required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
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

            if ($request->has('total_pengeluaran')) {
                // Check if total pengeluaran doesn't exceed pencairan amount
                if ($request->total_pengeluaran > $rekap->pencairanDana->jumlah_pencairan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total pengeluaran exceeds pencairan amount',
                        'pencairan_amount' => $rekap->pencairanDana->jumlah_pencairan
                    ], 403);
                }

                $updateData['total_pengeluaran'] = $request->total_pengeluaran;
                $updateData['sisa_dana'] = $rekap->pencairanDana->jumlah_pencairan - $request->total_pengeluaran;
            }

            if ($request->has('catatan')) {
                $updateData['catatan'] = $request->catatan;
            }

            // Reset status to draft if it was rejected
            if ($rekap->status === 'ditolak') {
                $updateData['status'] = 'draft';
            }

            $rekap->update($updateData);
            $rekap->load(['pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan updated successfully',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified rekap pengajuan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $rekap = RekapPengajuan::with('pencairanDana')->find($id);

            if (!$rekap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap pengajuan not found'
                ], 404);
            }

            // Check permission
            if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this rekap pengajuan'
                ], 403);
            }

            // Only draft can be deleted
            if ($rekap->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft rekap pengajuan can be deleted'
                ], 403);
            }

            $rekap->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit rekap pengajuan for approval
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit($id)
    {
        try {
            $rekap = RekapPengajuan::with('pencairanDana')->find($id);

            if (!$rekap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap pengajuan not found'
                ], 404);
            }

            // Check permission
            if ($rekap->pencairanDana->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this rekap pengajuan'
                ], 403);
            }

            // Check if can be submitted
            if (!in_array($rekap->status, ['draft', 'ditolak'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected rekap pengajuan can be submitted'
                ], 403);
            }

            $rekap->update(['status' => 'diajukan']);
            $rekap->load(['pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan submitted successfully',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve rekap pengajuan
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
            $rekap = RekapPengajuan::find($id);

            if (!$rekap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap pengajuan not found'
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
            if ($rekap->status !== 'diajukan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted rekap pengajuan can be approved'
                ], 403);
            }

            $rekap->update([
                'status' => 'disetujui',
                'catatan' => $request->input('catatan')
            ]);

            $rekap->load(['pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan approved successfully',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject rekap pengajuan
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
            $rekap = RekapPengajuan::find($id);

            if (!$rekap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap pengajuan not found'
                ], 404);
            }

            $user = auth()->user();

            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reject'
                ], 403);
            }

            $rekap->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            $rekap->load(['pencairanDana.anggaranKegiatan']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap pengajuan rejected',
                'data' => $rekap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject rekap pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rekap pengajuan statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            
            $query = RekapPengajuan::query();

            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                $query->whereHas('pencairanDana', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            }

            $stats = [
                'total' => $query->count(),
                'by_status' => [
                    'draft' => (clone $query)->where('status', 'draft')->count(),
                    'diajukan' => (clone $query)->where('status', 'diajukan')->count(),
                    'disetujui' => (clone $query)->where('status', 'disetujui')->count(),
                    'ditolak' => (clone $query)->where('status', 'ditolak')->count(),
                ],
                'total_pengeluaran' => $query->sum('total_pengeluaran'),
                'total_sisa_dana' => $query->sum('sisa_dana'),
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