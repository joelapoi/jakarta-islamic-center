<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PencairanDana extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pencairan_dana';

    protected $fillable = [
        'nomor_pencairan',
        'anggaran_kegiatan_id',
        'jumlah_pencairan',
        'keperluan',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'disbursed_at',
        'catatan',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'jumlah_pencairan' => 'decimal:0',
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

    public function rekapPengajuan()
    {
        return $this->hasMany(RekapPengajuan::class);
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }
}