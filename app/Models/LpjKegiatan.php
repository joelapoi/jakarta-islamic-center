<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LpjKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lpj_kegiatan';

    protected $fillable = [
        'anggaran_kegiatan_id',
        'nomor_lpj',
        'total_realisasi',
        'sisa_anggaran',
        'laporan_kegiatan',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'catatan',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'total_realisasi' => 'decimal:0',
        'sisa_anggaran' => 'decimal:0',
    ];

    public function anggaranKegiatan()
    {
        return $this->belongsTo(AnggaranKegiatan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }
}