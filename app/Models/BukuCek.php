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
        'nomor_buku_cek',
        'nomor_cek',
        'nominal',
        'tanggal_cek',
        'bank_name',
        'nama_bank',
        'nomor_rekening',
        'penerima',
        'nama_penerima',
        'keperluan',
        'keterangan',
        'notes',
        'status',
        'created_by',
        'submitted_at',
        'signed_at',
        'signed_by',
        'confirmed_at',
        'confirmed_by',
        'rejected_at',
        'rejected_by',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_cek' => 'date',
        'submitted_at' => 'datetime',
        'signed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'nominal' => 'decimal:2',
    ];

    public function rekapPengajuan()
    {
        return $this->belongsTo(RekapPengajuan::class, 'rekap_pengajuan_id');
    }

    public function documents()
    {
        return $this->morphMany(DocumentAttachment::class, 'attachable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('nomor_buku_cek', 'ILIKE', "%{$search}%")
                  ->orWhere('nomor_cek', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_penerima', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_bank', 'ILIKE', "%{$search}%");
            });
        }
        return $query;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'menunggu_ttd_kepala_jic' => '<span class="badge badge-warning">Menunggu TTD</span>',
            'ditandatangani' => '<span class="badge badge-primary">Ditandatangani</span>',
            'dikonfirmasi_bank' => '<span class="badge badge-success">Dikonfirmasi Bank</span>',
            'ditolak' => '<span class="badge badge-danger">Ditolak</span>'
        ];
        
        return $badges[$this->status] ?? '<span class="badge badge-secondary">' . $this->status . '</span>';
    }

    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    // Accessor untuk kompatibilitas dengan view yang menggunakan 'jumlah'
    public function getJumlahAttribute()
    {
        return $this->nominal;
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    public function canBeDeleted()
    {
        return $this->status === 'draft';
    }

    public function canBeSubmitted()
    {
        return $this->status === 'draft';
    }

    public function canBeSigned()
    {
        return $this->status === 'menunggu_ttd_kepala_jic';
    }

    public function canBeConfirmed()
    {
        return $this->status === 'ditandatangani';
    }

    public function canBeRejected()
    {
        return !in_array($this->status, ['dikonfirmasi_bank', 'ditolak']);
    }
}