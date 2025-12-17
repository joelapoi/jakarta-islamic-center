<?php

namespace Database\Seeders;

use App\Models\LpjKegiatan;
use App\Models\AnggaranKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LpjKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $anggarans = AnggaranKegiatan::where('status', 'disetujui_kepala_jic')->get();
        $user = User::where('email', 'user1@jic.test')->first();

        if ($anggarans->isEmpty() || !$user) {
            $this->command->error('Tidak ada anggaran yang disetujui atau user tidak ditemukan.');
            return;
        }

        $lpjs = [];
        foreach ($anggarans as $index => $anggaran) {
            $lpjs[] = [
                'anggaran_kegiatan_id' => $anggaran->id,
                'nomor_lpj' => 'LPJ-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'total_realisasi' => $anggaran->anggaran_disetujui * 0.8,
                'sisa_anggaran' => $anggaran->anggaran_disetujui * 0.2,
                'laporan_kegiatan' => 'Laporan pertanggungjawaban untuk kegiatan ' . $anggaran->nama_kegiatan,
                'status' => 'draft',
                'created_by' => $user->id,
                'approved_by' => null,
                'approved_at' => null,
                'catatan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        foreach ($lpjs as $lpj) {
            LpjKegiatan::firstOrCreate(
                ['nomor_lpj' => $lpj['nomor_lpj']],
                $lpj
            );
        }

        $this->command->info('LpjKegiatan seeder completed!');
    }
}
