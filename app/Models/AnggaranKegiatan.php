<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnggaranKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'anggaran_kegiatan';

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'deskripsi',
        'anggaran_disetujui',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
        'anggaran_disetujui' => 'decimal:0',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pencairanDana()
    {
        return $this->hasMany(PencairanDana::class);
    }

    public function lpjKegiatan()
    {
        return $this->hasOne(LpjKegiatan::class);
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }
}