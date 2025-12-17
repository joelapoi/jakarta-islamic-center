<?php

namespace Database\Seeders;

use App\Models\AnggaranKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AnggaranKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $creator = User::where('email', 'user1@jic.test')->first();
        $approver = User::where('email', 'kepala@jic.test')->first();

        if (!$creator || !$approver) {
            $this->command->error('Please run UserSeeder first!');
            return;
        }

        $kegiatans = [
            [
                'kode_kegiatan' => 'KEG-001',
                'nama_kegiatan' => 'Pengadaan Al-Quran',
                'deskripsi' => 'Pengadaan Al-Quran untuk perpustakaan Islamic Center',
                'anggaran_disetujui' => 5000000,
                'tanggal_mulai' => Carbon::now()->startOfMonth(),
                'tanggal_selesai' => Carbon::now()->addMonth(),
                'status' => 'disetujui_kepala_jic',
                'created_by' => $creator->id,
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now(),
                'catatan' => 'Disetujui untuk pengadaan Al-Quran 50 eksemplar'
            ],
            [
                'kode_kegiatan' => 'KEG-002',
                'nama_kegiatan' => 'Program Tahfiz',
                'deskripsi' => 'Program Tahfidzul Quran untuk anak-anak',
                'anggaran_disetujui' => 3000000,
                'tanggal_mulai' => Carbon::now()->startOfMonth(),
                'tanggal_selesai' => Carbon::now()->addMonth(),
                'status' => 'disetujui_kepala_jic',
                'created_by' => $creator->id,
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now(),
                'catatan' => 'Disetujui untuk program tahfiz 3 bulan'
            ],
            [
                'kode_kegiatan' => 'KEG-003',
                'nama_kegiatan' => 'Pemeliharaan Fasilitas',
                'deskripsi' => 'Pemeliharaan rutin fasilitas Islamic Center',
                'anggaran_disetujui' => 2000000,
                'tanggal_mulai' => Carbon::now()->startOfMonth(),
                'tanggal_selesai' => Carbon::now()->addMonth(),
                'status' => 'draft',
                'created_by' => $creator->id,
                'approved_by' => null,
                'approved_at' => null,
                'catatan' => null
            ],
        ];

        foreach ($kegiatans as $kegiatan) {
            AnggaranKegiatan::firstOrCreate(
                ['kode_kegiatan' => $kegiatan['kode_kegiatan']],
                $kegiatan
            );
        }

        $this->command->info('AnggaranKegiatan seeder completed!');
    }
}
