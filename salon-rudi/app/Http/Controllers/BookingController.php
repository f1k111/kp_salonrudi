<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * Memvalidasi data form, menyimpannya, lalu kembali dengan pesan sukses.
     */
    public function store(Request $request)
    {
        // Memeriksa semua data yang dikirim dari form booking
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nowa' => ['required', 'string', 'max:20'],
            'treatment' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tanggal' => ['bail', 'required', 'date_format:d/m/y', function ($attribute, $value, $fail) {
                if (\Carbon\Carbon::createFromFormat('!d/m/y', $value)->startOfDay()->lt(today())) $fail('Tanggal kunjungan harus hari ini atau setelahnya.');
            }],
            'waktu' => ['required', 'date_format:H:i'],
        ]);

        // Menyesuaikan nama field form dengan nama kolom tabel bookings
        $booking = Booking::create([
            'nama_lengkap' => $data['nama'],
            'nomor_whatsapp' => $data['nowa'],
            'email' => $data['email'],
            'kode_booking' => strtoupper(Str::random(8)),
            'treatment' => $data['treatment'],
            'tanggal_kedatangan' => \Carbon\Carbon::createFromFormat('d/m/y', $data['tanggal'])->format('Y-m-d'),
            'jam_kedatangan' => $data['waktu'],
            'status' => 'menunggu',
            'status_pembayaran' => 'belum_lunas',
            'jumlah_pembayaran' => 0,
        ]);

        // Kembali ke halaman sebelumnya sambil membawa pesan sukses
        return redirect()->back()->with('success', 'Permintaan booking berhasil dicatat. Kode booking Anda: '.$booking->kode_booking);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate(['identifier' => ['required', 'string', 'max:255'], 'email' => ['required', 'email']]);
        $booking = Booking::where('email', $data['email'])->where(function ($query) use ($data) {
            $query->where('kode_booking', strtoupper($data['identifier']))->orWhere('nomor_whatsapp', $data['identifier']);
        })->first();
        if (!$booking) return back()->withErrors(['identifier' => 'Data booking tidak ditemukan. Periksa kembali kode booking dan email Anda.'])->withInput();
        session(['reschedule_booking_id' => $booking->id, 'reschedule_booking_email' => strtolower($booking->email)]);
        return view('booking-reschedule', compact('booking'));
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $data = $request->validate(['tanggal' => ['bail', 'required', 'date_format:d/m/y', function ($attribute, $value, $fail) {
            if (\Carbon\Carbon::createFromFormat('!d/m/y', $value)->startOfDay()->lt(today())) $fail('Tanggal kunjungan harus hari ini atau setelahnya.');
        }], 'waktu' => ['required', 'date_format:H:i']]);
        abort_unless((int) session('reschedule_booking_id') === $booking->id && session('reschedule_booking_email') === strtolower($booking->email), 403);
        if ($booking->status === 'selesai') return back()->withErrors(['tanggal' => 'Booking yang telah selesai tidak dapat dijadwalkan ulang.']);
        $booking->update(['tanggal_kedatangan' => \Carbon\Carbon::createFromFormat('d/m/y', $data['tanggal'])->format('Y-m-d'), 'jam_kedatangan' => $data['waktu']]);
        $request->session()->forget(['reschedule_booking_id', 'reschedule_booking_email']);
        return redirect()->route('booking.manage')->with('success', 'Jadwal booking berhasil diperbarui.');
    }

    public function dashboard()
    {
        $role = auth()->user()->role;
        abort_unless(in_array($role, ['admin', 'manager', 'kasir'], true), 403);
        if ($role === 'kasir') {
            $bookings = Booking::whereDate('tanggal_kedatangan', today())->orderBy('jam_kedatangan')->get();
            $income = Booking::whereDate('tanggal_kedatangan', today())->where('status_pembayaran', 'lunas')->sum('jumlah_pembayaran');
            return view('dashboard', compact('bookings', 'income', 'role'));
        }
        $bookings = Booking::latest('tanggal_kedatangan')->paginate(15);
        $totalBookings = Booking::count();
        return view('dashboard', compact('bookings', 'totalBookings', 'role'));
    }

    public function adminUpdate(Request $request, Booking $booking)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'manager'], true), 403);
        $data = $request->validate(['nama_lengkap' => ['required', 'string', 'max:255'], 'nomor_whatsapp' => ['required', 'string', 'max:30'], 'email' => ['required', 'email', 'max:255'], 'treatment' => ['required', 'string', 'max:255'], 'tanggal_kedatangan' => ['required', 'date_format:d/m/y'], 'jam_kedatangan' => ['required', 'date_format:H:i'], 'jumlah_pembayaran' => ['required', 'integer', 'min:0'], 'status' => ['required', Rule::in(['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'])], 'status_pembayaran' => ['required', Rule::in(['belum_lunas', 'lunas'])]]);
        $data['tanggal_kedatangan'] = \Carbon\Carbon::createFromFormat('d/m/y', $data['tanggal_kedatangan'])->format('Y-m-d');
        $booking->update($data);
        return back()->with('success', 'Data booking berhasil diperbarui.');
    }

    public function adminStore(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'manager'], true), 403);
        $data = $request->validate(['nama_lengkap' => ['required', 'string', 'max:255'], 'nomor_whatsapp' => ['required', 'string', 'max:30'], 'email' => ['required', 'email', 'max:255'], 'treatment' => ['required', 'string', 'max:255'], 'tanggal_kedatangan' => ['required', 'date_format:d/m/y'], 'jam_kedatangan' => ['required', 'date_format:H:i'], 'jumlah_pembayaran' => ['required', 'integer', 'min:0']]);
        $data['tanggal_kedatangan'] = \Carbon\Carbon::createFromFormat('d/m/y', $data['tanggal_kedatangan'])->format('Y-m-d');
        $data['kode_booking'] = strtoupper(Str::random(8));
        $data['status'] = 'menunggu';
        $data['status_pembayaran'] = 'belum_lunas';
        Booking::create($data);
        return back()->with('success', 'Booking pelanggan berhasil dibuat.');
    }

    public function destroy(Booking $booking)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'manager'], true), 403);
        $booking->delete();
        return back()->with('success', 'Booking berhasil dihapus.');
    }

    public function pay(Request $request, Booking $booking)
    {
        abort_unless(auth()->user()->role === 'kasir', 403);
        $data = $request->validate(['jumlah_pembayaran' => ['required', 'integer', 'min:1']]);
        $booking->update(['jumlah_pembayaran' => $data['jumlah_pembayaran'], 'status' => 'selesai', 'status_pembayaran' => 'lunas']);
        return back()->with('success', 'Pembayaran booking berhasil dicatat.');
    }
}
