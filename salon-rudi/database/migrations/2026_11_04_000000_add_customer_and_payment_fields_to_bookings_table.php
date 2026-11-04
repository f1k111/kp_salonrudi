<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'email')) $table->string('email')->nullable();
            if (! Schema::hasColumn('bookings', 'kode_booking')) $table->string('kode_booking', 12)->nullable()->unique();
            if (! Schema::hasColumn('bookings', 'status')) $table->string('status')->default('menunggu');
            if (! Schema::hasColumn('bookings', 'status_pembayaran')) $table->string('status_pembayaran')->default('belum_lunas');
            if (! Schema::hasColumn('bookings', 'jumlah_pembayaran')) $table->unsignedBigInteger('jumlah_pembayaran')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['kode_booking']);
            $table->dropColumn(['email', 'kode_booking', 'status', 'status_pembayaran', 'jumlah_pembayaran']);
        });
    }
};
