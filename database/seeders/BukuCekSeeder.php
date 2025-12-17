<?php

namespace Database\Seeders;

use App\Models\BukuCek;
use App\Models\RekapPengajuan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BukuCekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rekaps = RekapPengajuan::where('status', 'disetujui')->get();

        if ($rekaps->isEmpty()) {
            $this->command->error('Tidak ada rekap pengajuan yang disetujui.');
            return;
        }

        $bukuCeks = [];
        foreach ($rekaps as $index => $rekap) {
            $bukuCeks[] = [
                'rekap_pengajuan_id' => $rekap->id,
                'nomor_cek' => 'CK-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'nominal' => $rekap->sisa_dana,
                'tanggal_cek' => Carbon::now(),
                'bank_name' => 'Bank Mandiri',
                'nomor_rekening' => '123456789' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'penerima' => 'PT. Supplier Barang',
                'keterangan' => 'Pembayaran untuk ' . $rekap->pencairanDana->keperluan,
                'notes' => null,
                'status' => 'pending',
                'signed_at' => null,
                'cashed_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        foreach ($bukuCeks as $bukuCek) {
            BukuCek::firstOrCreate(
                ['nomor_cek' => $bukuCek['nomor_cek']],
                $bukuCek
            );
        }

        $this->command->info('BukuCek seeder completed!');
    }
}
