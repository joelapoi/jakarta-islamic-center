<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnggaranKegiatan;
use App\Models\PencairanDana;
use App\Models\RekapPengajuan;
use App\Models\BukuCek;
use App\Models\LpjKegiatan;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userRoles = Session::get('user_roles', []);
        $isAdmin = count(array_intersect($userRoles, ['super_admin', 'admin', 'kepala_jic', 'kadiv_umum'])) > 0;

        // Statistics
        $stats = [
            'anggaran' => [
                'total' => $isAdmin ? AnggaranKegiatan::count() : AnggaranKegiatan::where('created_by', $user->id)->count(),
                'draft' => $isAdmin ? AnggaranKegiatan::where('status', 'draft')->count() : AnggaranKegiatan::where('created_by', $user->id)->where('status', 'draft')->count(),
                'diajukan' => $isAdmin ? AnggaranKegiatan::where('status', 'diajukan')->count() : AnggaranKegiatan::where('created_by', $user->id)->where('status', 'diajukan')->count(),
                'disetujui' => $isAdmin ? AnggaranKegiatan::where('status', 'disetujui_kepala_jic')->count() : AnggaranKegiatan::where('created_by', $user->id)->where('status', 'disetujui_kepala_jic')->count(),
            ],
            'pencairan' => [
                'total' => $isAdmin ? PencairanDana::count() : PencairanDana::where('created_by', $user->id)->count(),
                'pending' => $isAdmin ? PencairanDana::where('status', 'diajukan')->count() : PencairanDana::where('created_by', $user->id)->where('status', 'diajukan')->count(),
                'approved' => $isAdmin ? PencairanDana::where('status', 'disetujui_kepala_jic')->count() : PencairanDana::where('created_by', $user->id)->where('status', 'disetujui_kepala_jic')->count(),
                'disbursed' => $isAdmin ? PencairanDana::where('status', 'dicairkan')->count() : PencairanDana::where('created_by', $user->id)->where('status', 'dicairkan')->count(),
            ],
            'rekap' => [
                'total' => $isAdmin ? RekapPengajuan::count() : RekapPengajuan::where('created_by', $user->id)->count(),
            ],
            'lpj' => [
                'total' => $isAdmin ? LpjKegiatan::count() : LpjKegiatan::where('created_by', $user->id)->count(),
            ],
        ];

        // Recent activities
        $recentAnggaran = $isAdmin 
            ? AnggaranKegiatan::with('creator')->latest()->take(5)->get()
            : AnggaranKegiatan::with('creator')->where('created_by', $user->id)->latest()->take(5)->get();

        $recentPencairan = $isAdmin
            ? PencairanDana::with(['creator', 'anggaranKegiatan'])->latest()->take(5)->get()
            : PencairanDana::with(['creator', 'anggaranKegiatan'])->where('created_by', $user->id)->latest()->take(5)->get();

        // Pending approvals (for approvers)
        $pendingApprovals = [];
        if (in_array('kadiv', $userRoles)) {
            $pendingApprovals['anggaran'] = AnggaranKegiatan::where('status', 'diajukan')->count();
        }
        if (in_array('kadiv_umum', $userRoles)) {
            $pendingApprovals['anggaran'] = AnggaranKegiatan::where('status', 'disetujui_kadiv')->count();
            $pendingApprovals['pencairan'] = PencairanDana::where('status', 'diajukan')->count();
        }
        if (in_array('kepala_jic', $userRoles)) {
            $pendingApprovals['anggaran'] = AnggaranKegiatan::where('status', 'disetujui_kadiv_umum')->count();
            $pendingApprovals['pencairan'] = PencairanDana::where('status', 'disetujui_kadiv_umum')->count();
        }

        return view('dashboard', compact('stats', 'recentAnggaran', 'recentPencairan', 'pendingApprovals', 'userRoles', 'user'));
    }
}