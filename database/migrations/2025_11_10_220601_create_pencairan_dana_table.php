<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePencairanDanaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pencairan_dana', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pencairan')->unique();
            $table->foreignId('anggaran_kegiatan_id')->constrained('anggaran_kegiatan')->onDelete('cascade');
            $table->decimal('jumlah_pencairan', 15, 2);
            $table->text('keperluan');
            $table->enum('status', ['draft', 'diajukan', 'disetujui_kadiv_umum', 'disetujui_kepala_jic', 'dicairkan', 'ditolak'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
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
        Schema::dropIfExists('pencairan_dana');
    }
}
