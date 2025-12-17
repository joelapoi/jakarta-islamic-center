<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $attachableType = $request->input('attachable_type');
            $attachableId = $request->input('attachable_id');
            $documentType = $request->input('document_type');

            $query = DocumentAttachment::query();

            // Filter by attachable (polymorphic)
            if ($attachableType && $attachableId) {
                $query->where('attachable_type', $attachableType)
                      ->where('attachable_id', $attachableId);
            }

            // Filter by document type
            if ($documentType) {
                $query->where('document_type', $documentType);
            }

            $documents = $query->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Documents retrieved successfully',
                'data' => $documents
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a document
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120', // 5MB
            'attachable_type' => 'required|string',
            'attachable_id' => 'required|integer',
            'document_type' => 'required|string|in:proposal,rekap,bukti,lpj,cek,lainnya',
        ], [
            'file.required' => 'File is required',
            'file.mimes' => 'File must be: pdf, doc, docx, xls, xlsx, jpg, jpeg, png',
            'file.max' => 'File size must not exceed 5MB',
            'attachable_type.required' => 'Attachable type is required',
            'attachable_id.required' => 'Attachable ID is required',
            'document_type.required' => 'Document type is required',
            'document_type.in' => 'Invalid document type',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $fileName = Str::slug($fileNameWithoutExt) . '_' . time() . '.' . $extension;
            
            // Store file in public/documents/{document_type}
            $path = $file->storeAs(
                'documents/' . $request->document_type,
                $fileName,
                'public'
            );

            // Create document record
            $document = DocumentAttachment::create([
                'attachable_type' => $request->attachable_type,
                'attachable_id' => $request->attachable_id,
                'file_name' => $originalName,
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'document_type' => $request->document_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified document
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $document = DocumentAttachment::with('attachable')->find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Add full URL to response
            $document->file_url = Storage::url($document->file_path);

            return response()->json([
                'success' => true,
                'message' => 'Document retrieved successfully',
                'data' => $document
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified document
     * 
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        try {
            $document = DocumentAttachment::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Check if file exists
            $filePath = storage_path('app/public/' . $document->file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            // Return file download
            return response()->download($filePath, $document->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display (view) the document in browser
     * 
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function view($id)
    {
        try {
            $document = DocumentAttachment::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Check if file exists
            $filePath = storage_path('app/public/' . $document->file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            // Get mime type
            $mimeType = mime_content_type($filePath);

            // Return file for viewing in browser
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to view document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified document
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $document = DocumentAttachment::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple documents at once
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'attachable_type' => 'required|string',
            'attachable_id' => 'required|integer',
            'document_type' => 'required|string|in:proposal,rekap,bukti,lpj,cek,lainnya',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedDocuments = [];
            $errors = [];

            foreach ($request->file('files') as $index => $file) {
                try {
                    // Generate unique filename
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                    $fileName = Str::slug($fileNameWithoutExt) . '_' . time() . '_' . $index . '.' . $extension;
                    
                    // Store file
                    $path = $file->storeAs(
                        'documents/' . $request->document_type,
                        $fileName,
                        'public'
                    );

                    // Create document record
                    $document = DocumentAttachment::create([
                        'attachable_type' => $request->attachable_type,
                        'attachable_id' => $request->attachable_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $extension,
                        'file_size' => $file->getSize(),
                        'document_type' => $request->document_type,
                    ]);

                    $uploadedDocuments[] = $document;

                } catch (\Exception $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            $response = [
                'success' => true,
                'message' => count($uploadedDocuments) . ' document(s) uploaded successfully',
                'data' => $uploadedDocuments
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => DocumentAttachment::count(),
                'by_type' => DocumentAttachment::selectRaw('document_type, count(*) as count')
                    ->groupBy('document_type')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->document_type => $item->count];
                    }),
                'total_size' => DocumentAttachment::sum('file_size'),
                'by_file_type' => DocumentAttachment::selectRaw('file_type, count(*) as count')
                    ->groupBy('file_type')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->file_type => $item->count];
                    }),
            ];

            // Convert total size to human readable format
            $stats['total_size_human'] = $this->formatBytes($stats['total_size']);

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
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}