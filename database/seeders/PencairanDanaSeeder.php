<?php

namespace Database\Seeders;

use App\Models\PencairanDana;
use App\Models\AnggaranKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PencairanDanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $anggaran1 = AnggaranKegiatan::where('kode_kegiatan', 'KEG-001')->first();
        $anggaran2 = AnggaranKegiatan::where('kode_kegiatan', 'KEG-002')->first();
        $user = User::where('email', 'user1@jic.test')->first();

        if (!$anggaran1 || !$anggaran2 || !$user) {
            $this->command->error('Please run AnggaranKegiatanSeeder and UserSeeder first!');
            return;
        }

        $pencairans = [
            [
                'anggaran_kegiatan_id' => $anggaran1->id,
                'nomor_pencairan' => 'PEN-2025-001',
                'jumlah_pencairan' => 2000000,
                'keperluan' => 'Pencairan untuk membeli 20 Al-Quran',
                'status' => 'disetujui_kepala_jic',
                'created_by' => $user->id,
                'approved_by' => User::where('email', 'kepala@jic.test')->first()->id,
                'approved_at' => Carbon::now()->subDays(3),
                'disbursed_at' => null,
                'catatan' => 'Disetujui untuk pengadaan Al-Quran',
            ],
            [
                'anggaran_kegiatan_id' => $anggaran1->id,
                'nomor_pencairan' => 'PEN-2025-002',
                'jumlah_pencairan' => 3000000,
                'keperluan' => 'Pencairan untuk membeli 30 Al-Quran',
                'status' => 'disetujui_kepala_jic',
                'created_by' => $user->id,
                'approved_by' => User::where('email', 'kepala@jic.test')->first()->id,
                'approved_at' => Carbon::now()->subDay(),
                'disbursed_at' => null,
                'catatan' => 'Disetujui untuk pengadaan Al-Quran',
            ],
            [
                'anggaran_kegiatan_id' => $anggaran2->id,
                'nomor_pencairan' => 'PEN-2025-003',
                'jumlah_pencairan' => 1500000,
                'keperluan' => 'Pencairan untuk honor pembimbing tahfiz',
                'status' => 'draft',
                'created_by' => $user->id,
                'approved_by' => null,
                'approved_at' => null,
                'disbursed_at' => null,
                'catatan' => null,
            ],
        ];

        foreach ($pencairans as $pencairan) {
            PencairanDana::firstOrCreate(
                ['nomor_pencairan' => $pencairan['nomor_pencairan']],
                $pencairan
            );
        }

        $this->command->info('PencairanDana seeder completed!');
    }
}
