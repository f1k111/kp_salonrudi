<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel bookings beserta kolom-kolomnya.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Menyimpan nama lengkap pelanggan
            $table->string('nama_lengkap');

            // Nomor WhatsApp disimpan sebagai teks agar angka 0 di awal tidak hilang
            $table->string('nomor_whatsapp');

            $table->string('email');
            $table->string('kode_booking', 12)->unique();

            // Menyimpan nama treatment yang dipilih
            $table->string('treatment');

            // Menyimpan tanggal kedatangan
            $table->date('tanggal_kedatangan');

            // Menyimpan jam kedatangan
            $table->time('jam_kedatangan');

            $table->string('status')->default('menunggu');
            $table->string('status_pembayaran')->default('belum_lunas');
            $table->unsignedBigInteger('jumlah_pembayaran')->default(0);

            // Membuat kolom created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel bookings jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
