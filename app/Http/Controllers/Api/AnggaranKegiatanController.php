<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnggaranKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AnggaranKegiatanController extends Controller
{
    /**
     * Display a listing of anggaran kegiatan
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
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = AnggaranKegiatan::with(['creator', 'approver']);

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_kegiatan', 'ILIKE', "%{$search}%")
                      ->orWhere('nama_kegiatan', 'ILIKE', "%{$search}%")
                      ->orWhere('deskripsi', 'ILIKE', "%{$search}%");
                });
            }

            // Status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Date range filter
            if ($startDate) {
                $query->whereDate('tanggal_mulai', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('tanggal_selesai', '<=', $endDate);
            }

            // Filter by user role
            $userRoles = \Session::get('user_roles', []);
            $viewAllRoles = ['super_admin', 'admin', 'kadiv', 'kadiv_umum', 'kepala_jic'];
            
            if (!count(array_intersect($userRoles, $viewAllRoles))) {
                $query->where('created_by', auth()->id());
            }

            $anggaran = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan retrieved successfully',
                'data' => $anggaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created anggaran kegiatan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran_disetujui' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan is required',
            'anggaran_disetujui.required' => 'Anggaran is required',
            'anggaran_disetujui.min' => 'Anggaran must be greater than or equal to 0',
            'tanggal_mulai.required' => 'Tanggal mulai is required',
            'tanggal_selesai.required' => 'Tanggal selesai is required',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai must be after or equal to tanggal mulai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate kode_kegiatan automatically with proper sequence handling
            $year = date('Y');
            $prefix = 'KEG-' . $year . '-';
            
            // Use raw query to get next number safely to avoid duplicates
            $result = DB::selectOne(
                "SELECT COUNT(*) as count FROM anggaran_kegiatan WHERE kode_kegiatan LIKE ?",
                [$prefix . '%']
            );
            
            $nextNumber = ($result->count ?? 0) + 1;
            $kodeKegiatan = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $anggaran = AnggaranKegiatan::create([
                'kode_kegiatan' => $kodeKegiatan,
                'nama_kegiatan' => $request->nama_kegiatan,
                'deskripsi' => $request->deskripsi,
                'anggaran_disetujui' => $request->anggaran_disetujui,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $anggaran->load(['creator', 'approver']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan created successfully',
                'data' => $anggaran
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified anggaran kegiatan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $anggaran = AnggaranKegiatan::with([
                'creator', 
                'approver', 
                'pencairanDana.creator',
                'pencairanDana.approver',
                'lpjKegiatan',
                'documents'
            ])->find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            // Check permission
            $userRoles = \Session::get('user_roles', []);
            $viewAllRoles = ['super_admin', 'admin', 'kadiv', 'kepala_jic', 'kadiv_umum', 'staff'];
            if (!count(array_intersect($userRoles, $viewAllRoles))) {
                if ($anggaran->created_by !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to view this anggaran kegiatan'
                    ], 403);
                }
            }

            // Calculate total pencairan (include pencairan approved by Kadiv Umum)
            $totalPencairan = $anggaran->pencairanDana()
                ->whereIn('status', ['disetujui_kepala_jic', 'disetujui_kadiv_umum', 'dicairkan'])
                ->sum('jumlah_pencairan');

            $sisaAnggaran = $anggaran->anggaran_disetujui - $totalPencairan;

            $response = $anggaran->toArray();
            $response['total_pencairan'] = $totalPencairan;
            $response['sisa_anggaran'] = $sisaAnggaran;

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan retrieved successfully',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified anggaran kegiatan
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $anggaran = AnggaranKegiatan::find($id);

        if (!$anggaran) {
            return response()->json([
                'success' => false,
                'message' => 'Anggaran kegiatan not found'
            ], 404);
        }

        // Check permission - only creator can edit
        $userRoles = \Session::get('user_roles', []);
        if ($anggaran->created_by !== auth()->id() && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this anggaran kegiatan'
            ], 403);
        }

        // Check if can edit based on status
        if (!in_array($anggaran->status, ['draft', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit anggaran kegiatan with status: ' . $anggaran->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'kode_kegiatan' => 'sometimes|required|string|max:50|unique:anggaran_kegiatan,kode_kegiatan,' . $id,
            'nama_kegiatan' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran_disetujui' => 'sometimes|required|numeric|min:0',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
        ], [
            'kode_kegiatan.unique' => 'Kode kegiatan already exists',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai must be after or equal to tanggal mulai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only([
                'kode_kegiatan',
                'nama_kegiatan',
                'deskripsi',
                'anggaran_disetujui',
                'tanggal_mulai',
                'tanggal_selesai'
            ]);

            // Reset status to draft if it was rejected
            if ($anggaran->status === 'ditolak') {
                $updateData['status'] = 'draft';
                $updateData['catatan'] = null;
            }

            $anggaran->update($updateData);
            $anggaran->load(['creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan updated successfully',
                'data' => $anggaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified anggaran kegiatan
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $anggaran = AnggaranKegiatan::find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            // Check permission
            $userRoles = \Session::get('user_roles', []);
            if ($anggaran->created_by !== auth()->id() && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this anggaran kegiatan'
                ], 403);
            }

            // Only draft can be deleted
            if ($anggaran->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft anggaran kegiatan can be deleted'
                ], 403);
            }

            $anggaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit anggaran kegiatan for approval
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit($id)
    {
        try {
            $anggaran = AnggaranKegiatan::find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            // Check permission
            $userRoles = \Session::get('user_roles', []);
            if ($anggaran->created_by !== auth()->id() && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this anggaran kegiatan'
                ], 403);
            }

            // Check if can be submitted
            if (!in_array($anggaran->status, ['draft', 'ditolak'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected anggaran kegiatan can be submitted'
                ], 403);
            }

            $anggaran->update([
                'status' => 'diajukan',
                'catatan' => null
            ]);

            $anggaran->load(['creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan submitted successfully',
                'data' => $anggaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve anggaran kegiatan (multi-level approval)
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
            $anggaran = AnggaranKegiatan::find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            $userRoles = \Session::get('user_roles', []);
            $currentStatus = $anggaran->status;
            $newStatus = null;
            $message = '';

            // Determine next status based on current status and user role
            switch ($currentStatus) {
                case 'diajukan':
                    if (in_array('kadiv', $userRoles)) {
                        $newStatus = 'disetujui_kadiv';
                        $message = 'Anggaran kegiatan approved by Kadiv';
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Kadiv can approve at this stage'
                        ], 403);
                    }
                    break;

                case 'disetujui_kadiv':
                    if (in_array('kadiv_umum', $userRoles)) {
                        $newStatus = 'disetujui_kadiv_umum';
                        $message = 'Anggaran kegiatan approved by Kadiv Umum';
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Kadiv Umum can approve at this stage'
                        ], 403);
                    }
                    break;

                case 'disetujui_kadiv_umum':
                    if (in_array('kepala_jic', $userRoles)) {
                        $newStatus = 'disetujui_kepala_jic';
                        $message = 'Anggaran kegiatan fully approved by Kepala JIC';
                        
                        // Final approval - set approver and approval date
                        $anggaran->approved_by = auth()->id();
                        $anggaran->approved_at = now();
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
                        'message' => 'Cannot approve anggaran kegiatan with status: ' . $currentStatus
                    ], 403);
            }

            // Update status and notes
            $anggaran->status = $newStatus;
            $anggaran->catatan = $request->input('catatan');
            $anggaran->save();

            $anggaran->load(['creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $anggaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject anggaran kegiatan
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
            $anggaran = AnggaranKegiatan::find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            $userRoles = \Session::get('user_roles', []);

            // Check if user has permission to reject based on current status
            $canReject = false;
            $currentStatus = $anggaran->status;

            if ($currentStatus === 'diajukan' && in_array('kadiv', $userRoles)) {
                $canReject = true;
            } elseif ($currentStatus === 'disetujui_kadiv' && in_array('kadiv_umum', $userRoles)) {
                $canReject = true;
            } elseif ($currentStatus === 'disetujui_kadiv_umum' && in_array('kepala_jic', $userRoles)) {
                $canReject = true;
            }

            if (!$canReject && !count(array_intersect($userRoles, ['super_admin', 'admin']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to reject at this stage'
                ], 403);
            }

            // Update to rejected status
            $anggaran->update([
                'status' => 'ditolak',
                'catatan' => $request->catatan
            ]);

            $anggaran->load(['creator', 'approver']);

            return response()->json([
                'success' => true,
                'message' => 'Anggaran kegiatan rejected',
                'data' => $anggaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject anggaran kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get anggaran kegiatan statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $userRoles = \Session::get('user_roles', []);
            
            $query = AnggaranKegiatan::query();

            // Filter by user role
            if (!count(array_intersect($userRoles, ['super_admin', 'admin', 'kepala_jic', 'kadiv_umum']))) {
                $query->where('created_by', auth()->id());
            }

            $stats = [
                'total' => $query->count(),
                'by_status' => [
                    'draft' => (clone $query)->where('status', 'draft')->count(),
                    'diajukan' => (clone $query)->where('status', 'diajukan')->count(),
                    'disetujui_kadiv' => (clone $query)->where('status', 'disetujui_kadiv')->count(),
                    'disetujui_kadiv_umum' => (clone $query)->where('status', 'disetujui_kadiv_umum')->count(),
                    'disetujui_kepala_jic' => (clone $query)->where('status', 'disetujui_kepala_jic')->count(),
                    'ditolak' => (clone $query)->where('status', 'ditolak')->count(),
                ],
                'total_anggaran' => $query->sum('anggaran_disetujui'),
                'total_approved' => (clone $query)->whereIn('status', ['disetujui_kepala_jic', 'disetujui_kadiv_umum'])->sum('anggaran_disetujui'),
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

    /**
     * Get approval history/timeline
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeline($id)
    {
        try {
            $anggaran = AnggaranKegiatan::with(['creator', 'approver'])->find($id);

            if (!$anggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggaran kegiatan not found'
                ], 404);
            }

            $timeline = [
                [
                    'status' => 'draft',
                    'label' => 'Draft Created',
                    'user' => $anggaran->creator ? $anggaran->creator->name : null,
                    'date' => $anggaran->created_at,
                    'completed' => true,
                ],
                [
                    'status' => 'diajukan',
                    'label' => 'Submitted for Approval',
                    'user' => $anggaran->creator ? $anggaran->creator->name : null,
                    'date' => $anggaran->status !== 'draft' ? $anggaran->updated_at : null,
                    'completed' => $anggaran->status !== 'draft',
                ],
                [
                    'status' => 'disetujui_kadiv',
                    'label' => 'Approved by Kadiv',
                    'user' => null,
                    'date' => null,
                    'completed' => in_array($anggaran->status, ['disetujui_kadiv', 'disetujui_kadiv_umum', 'disetujui_kepala_jic']),
                ],
                [
                    'status' => 'disetujui_kadiv_umum',
                    'label' => 'Approved by Kadiv Umum',
                    'user' => null,
                    'date' => null,
                    'completed' => in_array($anggaran->status, ['disetujui_kadiv_umum', 'disetujui_kepala_jic']),
                ],
                [
                    'status' => 'disetujui_kepala_jic',
                    'label' => 'Approved by Kepala JIC',
                    'user' => $anggaran->approver ? $anggaran->approver->name : null,
                    'date' => $anggaran->approved_at,
                    'completed' => $anggaran->status === 'disetujui_kepala_jic',
                ],
            ];

            if ($anggaran->status === 'ditolak') {
                $timeline[] = [
                    'status' => 'ditolak',
                    'label' => 'Rejected',
                    'user' => null,
                    'date' => $anggaran->updated_at,
                    'catatan' => $anggaran->catatan,
                    'completed' => true,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Timeline retrieved successfully',
                'data' => [
                    'anggaran' => $anggaran,
                    'timeline' => $timeline
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve timeline',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}