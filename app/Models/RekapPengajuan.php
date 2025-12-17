<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekapPengajuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekap_pengajuan';

    protected $fillable = [
        'pencairan_dana_id',
        'nomor_rekap',
        'total_pengeluaran',
        'sisa_dana',
        'status',
        'catatan',
    ];

    protected $casts = [
        'total_pengeluaran' => 'decimal:0',
        'sisa_dana' => 'decimal:0',
    ];

    public function pencairanDana()
    {
        return $this->belongsTo(PencairanDana::class);
    }

    public function bukuCek()
    {
        return $this->hasMany(BukuCek::class);
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }
}