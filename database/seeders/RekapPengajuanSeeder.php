<?php

namespace Database\Seeders;

use App\Models\RekapPengajuan;
use App\Models\PencairanDana;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RekapPengajuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pencairans = PencairanDana::where('status', 'disetujui_kepala_jic')->get();

        if ($pencairans->isEmpty()) {
            $this->command->error('Tidak ada pencairan dana yang disetujui.');
            return;
        }

        $rekaps = [];
        foreach ($pencairans as $index => $pencairan) {
            $rekaps[] = [
                'pencairan_dana_id' => $pencairan->id,
                'nomor_rekap' => 'RK-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'total_pengeluaran' => $pencairan->jumlah_pencairan * 0.8,
                'sisa_dana' => $pencairan->jumlah_pencairan * 0.2,
                'status' => 'disetujui',
                'catatan' => 'Rekap pengeluaran untuk ' . $pencairan->keperluan,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        foreach ($rekaps as $rekap) {
            RekapPengajuan::firstOrCreate(
                ['nomor_rekap' => $rekap['nomor_rekap']],
                $rekap
            );
        }

        $this->command->info('RekapPengajuan seeder completed!');
    }
}
