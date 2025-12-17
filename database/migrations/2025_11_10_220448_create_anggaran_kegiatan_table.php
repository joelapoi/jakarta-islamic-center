<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnggaranKegiatanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anggaran_kegiatan', function (Blueprint $table) {
            $table->id();
             $table->string('kode_kegiatan')->unique();
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->decimal('anggaran_disetujui', 15, 2)->default(0);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'diajukan', 'disetujui_kadiv', 'disetujui_kadiv_umum', 'disetujui_kepala_jic', 'ditolak'])->default('draft');
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
        Schema::dropIfExists('anggaran_kegiatan');
    }
}
