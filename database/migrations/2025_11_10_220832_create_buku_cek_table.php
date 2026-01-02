<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buku_cek', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('rekap_pengajuan_id')->constrained('rekap_pengajuan')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Nomor & Identifikasi
            $table->string('nomor_buku_cek')->unique();
            $table->string('nomor_cek')->nullable(); // Keep for backward compatibility
            
            // Informasi Keuangan
            $table->decimal('jumlah', 15, 2); // Primary field
            $table->decimal('nominal', 15, 2)->nullable(); // Keep for backward compatibility
            $table->date('tanggal_cek')->nullable();
            
            // Informasi Bank & Penerima
            $table->string('nama_bank'); // Primary field
            $table->string('bank_name')->nullable(); // Keep for backward compatibility
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_penerima'); // Primary field
            $table->string('penerima')->nullable(); // Keep for backward compatibility
            
            // Keterangan
            $table->text('keperluan')->nullable(); // Primary field
            $table->text('keterangan')->nullable(); // Keep for backward compatibility
            $table->text('notes')->nullable();
            
            // Status - Updated enum values
            $table->enum('status', [
                'draft',
                'menunggu_ttd_kepala_jic',
                'ditandatangani',
                'dikonfirmasi_bank',
                'ditolak',
                // Keep old values for backward compatibility
                'pending',
                'dicairkan',
                'batal'
            ])->default('draft');
            
            // Workflow Timestamps & Users
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cashed_at')->nullable(); // Keep for backward compatibility
            
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('alasan_penolakan')->nullable();
            
            // Standard Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('status');
            $table->index('rekap_pengajuan_id');
            $table->index('created_by');
            $table->index('signed_by');
            $table->index('confirmed_by');
            $table->index('rejected_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buku_cek');
    }
};