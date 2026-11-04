<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // Kolom yang boleh diisi melalui proses penyimpanan data
    protected $fillable = [
        'nama_lengkap',
        'nomor_whatsapp',
        'email',
        'kode_booking',
        'treatment',
        'tanggal_kedatangan',
        'jam_kedatangan',
        'status',
        'status_pembayaran',
        'jumlah_pembayaran',
    ];
}
