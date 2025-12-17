<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLpjKegiatanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lpj_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_kegiatan_id')->constrained('anggaran_kegiatan')->onDelete('cascade');
            $table->string('nomor_lpj')->unique();
            $table->decimal('total_realisasi', 15, 2);
            $table->decimal('sisa_anggaran', 15, 2);
            $table->text('laporan_kegiatan');
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lpj_kegiatan');
    }
}
