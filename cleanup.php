<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AnggaranKegiatan;

// Delete duplicate
$ag = AnggaranKegiatan::where('kode_kegiatan', 'KEG-2025-0002')->first();
if ($ag) {
    $ag->delete();
    echo "Deleted KEG-2025-0002\n";
} else {
    echo "Not found\n";
}

// Show all kode for 2025
$kodes = AnggaranKegiatan::where('kode_kegiatan', 'LIKE', 'KEG-2025-%')
    ->orderBy('kode_kegiatan')
    ->pluck('kode_kegiatan')
    ->toArray();

echo "Existing codes for 2025:\n";
print_r($kodes);
