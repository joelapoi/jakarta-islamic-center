<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Upload a document
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120', // 5MB
            'attachable_type' => 'required|string',
            'attachable_id' => 'required|integer',
            'document_type' => 'required|string|in:proposal,rekap,bukti,lpj,cek,lainnya',
        ], [
            'file.required' => 'File wajib dipilih',
            'file.mimes' => 'File harus berformat: pdf, doc, docx, xls, xlsx, jpg, jpeg, png',
            'file.max' => 'Ukuran file maksimal 5MB',
            'attachable_type.required' => 'Tipe dokumen wajib diisi',
            'attachable_id.required' => 'ID dokumen wajib diisi',
            'document_type.required' => 'Jenis dokumen wajib dipilih',
        ]);

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $fileName = Str::slug($fileNameWithoutExt) . '_' . time() . '.' . $extension;
            
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil diupload',
                    'data' => $document
                ]);
            }

            return back()->with('success', 'Dokumen berhasil diupload');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupload dokumen: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal mengupload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified document
     */
    public function download($id)
    {
        try {
            $document = DocumentAttachment::findOrFail($id);
            
            $filePath = storage_path('app/public/' . $document->file_path);

            if (!file_exists($filePath)) {
                return back()->with('error', 'File tidak ditemukan di server');
            }

            return response()->download($filePath, $document->file_name);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }

    /**
     * View the document in browser
     */
    public function view($id)
    {
        try {
            $document = DocumentAttachment::findOrFail($id);
            
            $filePath = storage_path('app/public/' . $document->file_path);

            if (!file_exists($filePath)) {
                return back()->with('error', 'File tidak ditemukan di server');
            }

            $mimeType = mime_content_type($filePath);

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menampilkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy($id)
    {
        try {
            $document = DocumentAttachment::findOrFail($id);

            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil dihapus'
                ]);
            }

            return back()->with('success', 'Dokumen berhasil dihapus');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }
}