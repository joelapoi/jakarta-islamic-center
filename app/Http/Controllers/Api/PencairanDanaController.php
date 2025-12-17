<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PencairanDana;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PencairanDanaController extends Controller
{
    /**
     * Display a listing of pencairan dana
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

            $query = PencairanDana::with(['anggaranKegiatan', 'creator', 'approver']);

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_pencairan', 'ILIKE', "%{$search}%")
                      ->orWhere('keperluan', 'ILIKE', "%{$search}%");
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
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'staff'])) {
                $query->where('created_by', $user->id);
            }

            $pencairan = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana retrieved successfully',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pencairan dana
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'anggaran_kegiatan_id' => 'required|exists:anggaran_kegiatan,id',
            'jumlah_pencairan' => 'required|numeric|min:0',
            'keperluan' => 'required|string|max:1000',
        ], [
            'anggaran_kegiatan_id.required' => 'Anggaran kegiatan is required',
            'anggaran_kegiatan_id.exists' => 'Anggaran kegiatan not found',
            'jumlah_pencairan.required' => 'Jumlah pencairan is required',
            'jumlah_pencairan.min' => 'Jumlah pencairan must be greater than or equal to 0',
            'keperluan.required' => 'Keperluan is required',
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

        // Check if pencairan amount doesn't exceed available budget
        $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $request->anggaran_kegiatan_id)
            ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
            ->sum('jumlah_pencairan');

        $sisaAnggaran = $anggaran->anggaran_disetujui - $totalPencairan;

        if ($request->jumlah_pencairan > $sisaAnggaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pencairan amount exceeds available budget',
                'available_budget' => $sisaAnggaran,
                'requested_amount' => $request->jumlah_pencairan
            ], 403);
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
                'anggaran_kegiatan_id' => $request->anggaran_kegiatan_id,
                'jumlah_pencairan' => $request->jumlah_pencairan,
                'keperluan' => $request->keperluan,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $pencairan->load(['anggaranKegiatan', 'creator']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana created successfully',
                'data' => $pencairan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pencairan dana
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $pencairan = PencairanDana::with([
                'anggaranKegiatan',
                'creator',
                'approver',
                'rekapPengajuan',
                'documents'
            ])->find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            // Check permission
            $user = auth()->user();
            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                if ($pencairan->created_by !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to view this pencairan dana'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana retrieved successfully',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pencairan dana
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $pencairan = PencairanDana::find($id);

        if (!$pencairan) {
            return response()->json([
                'success' => false,
                'message' => 'Pencairan dana not found'
            ], 404);
        }

        // Check permission
        if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this pencairan dana'
            ], 403);
        }

        // Check if can edit
        if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit pencairan dana with status: ' . $pencairan->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'jumlah_pencairan' => 'sometimes|required|numeric|min:0',
            'keperluan' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check budget if jumlah_pencairan is being updated
            if ($request->has('jumlah_pencairan')) {
                $totalPencairan = PencairanDana::where('anggaran_kegiatan_id', $pencairan->anggaran_kegiatan_id)
                    ->where('id', '!=', $id)
                    ->whereIn('status', ['disetujui_kepala_jic', 'dicairkan'])
                    ->sum('jumlah_pencairan');

                $sisaAnggaran = $pencairan->anggaranKegiatan->anggaran_disetujui - $totalPencairan;

                if ($request->jumlah_pencairan > $sisaAnggaran) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pencairan amount exceeds available budget',
                        'available_budget' => $sisaAnggaran
                    ], 403);
                }
            }

            $updateData = $request->only(['jumlah_pencairan', 'keperluan']);
            
            // Reset status to draft if it was rejected
            if ($pencairan->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $pencairan->update($updateData);
            $pencairan->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana updated successfully',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pencairan dana
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $pencairan = PencairanDana::find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            // Check permission
            if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this pencairan dana'
                ], 403);
            }

            // Only draft can be deleted
            if ($pencairan->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft pencairan dana can be deleted'
                ], 403);
            }

            $pencairan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit pencairan dana for approval
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit($id)
    {
        try {
            $pencairan = PencairanDana::find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            // Check permission
            if ($pencairan->created_by !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this pencairan dana'
                ], 403);
            }

            // Check if can be submitted
            if (!in_array($pencairan->status, ['draft', 'ditolak'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected pencairan dana can be submitted'
                ], 403);
            }

            $pencairan->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            $pencairan->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana submitted successfully',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve pencairan dana
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
            $pencairan = PencairanDana::find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            $user = auth()->user();
            $currentStatus = $pencairan->status;
            $newStatus = null;
            $message = '';

            // Determine next status based on current status and user role
            switch ($currentStatus) {
                case 'diajukan':
                    if ($user->hasRole('kadiv_umum')) {
                        $newStatus = 'disetujui_kadiv_umum';
                        $message = 'Pencairan dana approved by Kadiv Umum';
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Kadiv Umum can approve at this stage'
                        ], 403);
                    }
                    break;

                case 'disetujui_kadiv_umum':
                    if ($user->hasRole('kepala_jic')) {
                        $newStatus = 'disetujui_kepala_jic';
                        $message = 'Pencairan dana fully approved by Kepala JIC';
                        
                        // Final approval
                        $pencairan->approved_by = $user->id;
                        $pencairan->approved_at = now();
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Kepala JIC can approve at this stage'
                        ], 403);
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot approve pencairan dana with status: ' . $currentStatus
                    ], 403);
            }

            $pencairan->status = $newStatus;
            $pencairan->catatan = $request->input('catatan');
            $pencairan->save();

            $pencairan->load(['anggaranKegiatan', 'creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject pencairan dana
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
            $pencairan = PencairanDana::find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            $user = auth()->user();

            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to reject'
                ], 403);
            }

            $pencairan->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            $pencairan->load(['anggaranKegiatan', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana rejected',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disburse pencairan dana (mark as disbursed)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function disburse($id)
    {
        try {
            $pencairan = PencairanDana::find($id);

            if (!$pencairan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan dana not found'
                ], 404);
            }

            $user = auth()->user();

            if (!$user->hasAnyRole(['kadiv_umum', 'kepala_jic', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to disburse'
                ], 403);
            }

            if ($pencairan->status !== 'disetujui_kepala_jic') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pencairan must be fully approved first'
                ], 403);
            }

            $pencairan->update([
                'status' => 'dicairkan',
                'disbursed_at' => now()
            ]);

            $pencairan->load(['anggaranKegiatan', 'creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => 'Pencairan dana disbursed successfully',
                'data' => $pencairan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disburse pencairan dana',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pencairan dana statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            
            $query = PencairanDana::query();

            if (!$user->hasAnyRole(['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) {
                $query->where('created_by', $user->id);
            }

            $stats = [
                'total' => $query->count(),
                'by_status' => [
                    'draft' => (clone $query)->where('status', 'draft')->count(),
                    'diajukan' => (clone $query)->where('status', 'diajukan')->count(),
                    'disetujui_kadiv_umum' => (clone $query)->where('status', 'disetujui_kadiv_umum')->count(),
                    'disetujui_kepala_jic' => (clone $query)->where('status', 'disetujui_kepala_jic')->count(),
                    'dicairkan' => (clone $query)->where('status', 'dicairkan')->count(),
                    'ditolak' => (clone $query)->where('status', 'ditolak')->count(),
                ],
                'total_amount' => $query->sum('jumlah_pencairan'),
                'total_disbursed' => (clone $query)->where('status', 'dicairkan')->sum('jumlah_pencairan'),
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