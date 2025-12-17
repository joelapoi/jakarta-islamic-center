<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekapPengajuanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rekap_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pencairan_dana_id')->constrained('pencairan_dana')->onDelete('cascade');
            $table->string('nomor_rekap')->unique();
            $table->decimal('total_pengeluaran', 15, 2);
            $table->decimal('sisa_dana', 15, 2);
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
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
        Schema::dropIfExists('rekap_pengajuan');
    }
}
