<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BukuCek extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buku_cek';

    protected $fillable = [
        'rekap_pengajuan_id',
        'nomor_cek',
        'nominal',
        'tanggal_cek',
        'bank_name',
        'nomor_rekening',
        'penerima',
        'keterangan',
        'notes',
        'status',
        'signed_at',
        'cashed_at',
    ];

    protected $casts = [
        'tanggal_cek' => 'date',
        'signed_at' => 'datetime',
        'cashed_at' => 'datetime',
        'nominal' => 'decimal:0',
    ];

    public function rekapPengajuan()
    {
        return $this->belongsTo(RekapPengajuan::class);
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }
}