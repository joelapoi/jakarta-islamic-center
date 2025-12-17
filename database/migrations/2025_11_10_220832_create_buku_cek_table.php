<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBukuCekTable extends Migration
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
            $table->foreignId('rekap_pengajuan_id')->constrained('rekap_pengajuan')->onDelete('cascade');
            $table->string('nomor_cek')->unique();
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_cek');
            $table->string('bank_name');
            $table->string('nomor_rekening')->nullable();
            $table->string('penerima');
            $table->text('keterangan')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'ditandatangani', 'dicairkan', 'batal'])->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('cashed_at')->nullable();
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
        Schema::dropIfExists('buku_cek');
    }
}
