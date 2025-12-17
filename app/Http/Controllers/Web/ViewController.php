<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    /**
     * Show login page
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        return view('dashboard');
    }

    /**
     * Show users index
     */
    public function users()
    {
        return view('users.index');
    }

    /**
     * Show user create form
     */
    public function userCreate()
    {
        return view('users.create');
    }

    /**
     * Show user edit form
     */
    public function userEdit($id)
    {
        return view('users.edit', compact('id'));
    }

    /**
     * Show anggaran kegiatan index
     */
    public function anggaranKegiatan()
    {
        return view('anggaran-kegiatan.index');
    }

    /**
     * Show anggaran kegiatan create
     */
    public function anggaranKegiatanCreate()
    {
        return view('anggaran-kegiatan.create');
    }

    /**
     * Show anggaran kegiatan detail
     */
    public function anggaranKegiatanShow($id)
    {
        return view('anggaran-kegiatan.show', compact('id'));
    }

    /**
     * Show anggaran kegiatan edit form
     */
    public function anggaranKegiatanEdit($id)
    {
        return view('anggaran-kegiatan.edit', compact('id'));
    }

    /**
     * Show pencairan dana index
     */
    public function pencairanDana()
    {
        return view('pencairan-dana.index');
    }

    /**
     * Show pencairan dana create form
     */
    public function pencairanDanaCreate()
    {
        return view('pencairan-dana.create');
    }

    /**
     * Show pencairan dana detail
     */
    public function pencairanDanaShow($id)
    {
        return view('pencairan-dana.show', compact('id'));
    }

    /**
     * Show pencairan dana edit form
     */
    public function pencairanDanaEdit($id)
    {
        return view('pencairan-dana.edit', compact('id'));
    }

    /**
     * Show rekap pengajuan index
     */
    public function rekapPengajuan()
    {
        return view('rekap-pengajuan.index');
    }
    /**
     * Show rekap pengajuan create form
     */
    public function rekapPengajuanCreate()
    {
        return view('rekap-pengajuan.create');
    }
    /**
     * Show rekap pengajuan detail
     */
    public function rekapPengajuanShow($id)
    {
        return view('rekap-pengajuan.show', compact('id'));
    }
    /**
     * Show rekap pengajuan edit form
     */
    public function rekapPengajuanEdit($id)
    {
        return view('rekap-pengajuan.edit', compact('id'));
    }

    /**
     * Show buku cek index
     */
    public function bukuCek()
    {
        return view('buku-cek.index');
    }
    /**
     * Show buku cek create
     */
    public function bukuCekCreate()
    {
        return view('buku-cek.create');
    }
    /**
     * Show buku cek detail
     */
    public function bukuCekShow($id)
    {
        return view('buku-cek.show', compact('id'));
    }
    /**
     * Show buku cek edit form
     */
    public function bukuCekEdit($id)
    {
        return view('buku-cek.edit', compact('id'));
    }

    /**
     * Show LPJ kegiatan index
     */
    public function lpjKegiatan()
    {
        return view('lpj-kegiatan.index');
    }
    /**
     * Show LPJ kegiatan create form
     */
    public function lpjKegiatanCreate()
    {
        return view('lpj-kegiatan.create');
    }
    /**
     * Show LPJ kegiatan detail
     */
    public function lpjKegiatanShow($id)
    {
        return view('lpj-kegiatan.show', compact('id'));
    }
    /**
     * Show LPJ kegiatan edit form
     */
    public function lpjKegiatanEdit($id)
    {
        return view('lpj-kegiatan.edit', compact('id'));
    }
}