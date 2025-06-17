<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlamatPengiriman; // Import model AlamatPengiriman
use App\Models\Pelanggan; // Pastikan model Pelanggan ada
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Services\GoogleMapsService; // Import GoogleMapsService

class ProfileController extends Controller
{
    protected GoogleMapsService $googleMapsService;

    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->googleMapsService = $googleMapsService;
    }
    /**
     * Menampilkan halaman profil pengguna.
     */
    public function show(Request $request)
    {
        if (!Session::has('pelanggan')) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        $pelangganSessionData = Session::get('pelanggan');
        // Asumsikan session 'pelanggan' menyimpan array dengan 'id_pelanggan'
        // atau objek model Pelanggan itu sendiri.
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        if (!$pelangganId) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $pelanggan = Pelanggan::find($pelangganId);

        if (!$pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Data pelanggan tidak ditemukan. Silakan login kembali.');
        }

        // Ambil daftar alamat pengiriman pelanggan
        $alamatList = AlamatPengiriman::where('id_pelanggan', $pelangganId)->orderBy('is_utama', 'desc')->orderBy('created_at', 'desc')->get();

        return view('frontend.profile', ['pelanggan' => $pelanggan, 'alamatList' => $alamatList]);
    }

    /**
     * Memperbarui data profil pengguna.
     */
    public function update(Request $request)
    {
        if (!Session::has('pelanggan')) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        $pelangganSessionData = Session::get('pelanggan');
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        $pelanggan = Pelanggan::find($pelangganId);

        if (!$pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Data pelanggan tidak ditemukan. Silakan login kembali.');
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'nullable|string|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed', // Tambahkan validasi password
            'profile_photo' => 'nullable|image|max:2048', // Tambahkan validasi foto (max 2MB)
        ]);

        if ($validator->fails()) {
            return redirect()->route('profile.show')->withErrors($validator)->withInput();
        }

        // Siapkan data untuk update
        $updateData = $request->only(['nama', 'no_telepon', 'alamat']); // Sesuaikan field yang diambil

        // Handle Password Update
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        // Handle Profile Photo Upload
        if ($request->hasFile('profile_photo')) {
            // Hapus foto lama jika ada
            if ($pelanggan->profile_photo_path) {
                \Storage::disk('public')->delete($pelanggan->profile_photo_path);
            }

            // Simpan foto baru
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $updateData['profile_photo_path'] = $path;
        }

        // Lakukan update
        $pelanggan->update($updateData);

        // Update data di session
        // Ambil data pelanggan terbaru setelah update
        $updatedPelanggan = Pelanggan::find($pelangganId);
        Session::put('pelanggan', $updatedPelanggan); // Update data di session dengan objek Pelanggan

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Menyimpan alamat pengiriman baru.
     */
    public function storeAlamat(Request $request)
    {
        if (!Session::has('pelanggan')) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk menambahkan alamat.');
        }

        $pelangganSessionData = Session::get('pelanggan');
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        if (!$pelangganId) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $validator = Validator::make($request->all(), [
            'nama_penerima' => 'required|string|max:100',
            'nomor_telepon' => 'required|string|max:20',
            'label_alamat' => 'nullable|string|max:50',
            'alamat_lengkap' => 'required|string|max:500',
            'provinsi' => 'required|string|max:100',
            'kota' => 'required|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            // 'latitude' => 'nullable|numeric|between:-90,90', // Kembalikan input manual
            // 'longitude' => 'nullable|numeric|between:-180,180', // Kembalikan input manual
            'is_utama' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('profile.show')->withErrors($validator, 'alamat')->withInput();
        }

        $isUtama = $request->has('is_utama') ? true : false;

        // Jika alamat baru diatur sebagai utama, nonaktifkan status utama alamat lain
        if ($isUtama) {
            AlamatPengiriman::where('id_pelanggan', $pelangganId)->update(['is_utama' => false]);
        }

        // // Geocode alamat (dinonaktifkan sementara)
        $fullAddress = $request->input('alamat_lengkap') . ', ' . $request->input('kota') . ', ' . $request->input('provinsi') . ' ' . $request->input('kode_pos');
        $coordinates = $this->googleMapsService->geocodeAddress($fullAddress);
        $latitude = $coordinates['lat'] ?? null;
        $longitude = $coordinates['lng'] ?? null;
        // $latitude = $request->input('latitude'); // Ambil dari input manual
        // $longitude = $request->input('longitude'); // Ambil dari input manual

        // Jika geocoding gagal, latitude dan longitude akan null.

        $alamatData = [
            'id_pelanggan' => $pelangganId,
            'nama_penerima' => $request->input('nama_penerima'),
            'nomor_telepon' => $request->input('nomor_telepon'),
            'label_alamat' => $request->input('label_alamat'),
            'alamat_lengkap' => $request->input('alamat_lengkap'),
            'provinsi' => $request->input('provinsi'),
            'kota' => $request->input('kota'),
            'kode_pos' => $request->input('kode_pos'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_utama' => $isUtama,
        ];

        AlamatPengiriman::create($alamatData);


        return redirect()->route('profile.show')->with('success', 'Alamat baru berhasil ditambahkan.');
    }
    /**
     * Memperbarui alamat pengiriman yang sudah ada.
     */
    public function updateAlamat(Request $request, AlamatPengiriman $alamat)
    {
        $pelangganSessionData = Session::get('pelanggan');
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        if (!$pelangganId || $alamat->id_pelanggan !== $pelangganId) {
            return redirect()->route('profile.show')->with('error', 'Aksi tidak diizinkan.');
        }

        $validator = Validator::make($request->all(), [
            'edit_nama_penerima' => 'required|string|max:100',
            'edit_nomor_telepon' => 'required|string|max:20',
            'edit_label_alamat' => 'nullable|string|max:50',
            'edit_alamat_lengkap' => 'required|string|max:500',
            'edit_provinsi' => 'required|string|max:100',
            'edit_kota' => 'required|string|max:100',
            'kode_pos' => 'nullable|string|max:10', // Ubah menjadi required jika memang wajib
            // 'edit_latitude' => 'nullable|numeric|between:-90,90', // Tidak lagi input manual
            // 'edit_longitude' => 'nullable|numeric|between:-180,180', // Tidak lagi input manual
            'edit_is_utama' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            // Redirect kembali dengan error dan input, serta ID modal agar terbuka lagi
            return redirect()->route('profile.show')
                ->withErrors($validator, 'editAlamat_' . $alamat->id) // Error bag dengan ID alamat
                ->withInput()
                ->with('open_modal_edit_id', $alamat->id); // Kirim ID alamat untuk membuka modal
        }

        $isUtama = $request->has('edit_is_utama') ? true : false;

        if ($isUtama) {
            AlamatPengiriman::where('id_pelanggan', $pelangganId)
                ->where('id', '!=', $alamat->id) // Kecuali alamat yang sedang diedit
                ->update(['is_utama' => false]);
        }

        // Geocode alamat yang diupdate
        $fullAddress = $request->input('edit_alamat_lengkap') . ', ' . $request->input('edit_kota') . ', ' . $request->input('edit_provinsi') . ' ' . $request->input('kode_pos');
        $coordinates = $this->googleMapsService->geocodeAddress($fullAddress);
        $latitude = $coordinates['lat'] ?? $alamat->latitude; // Gunakan lat lama jika geocode gagal
        $longitude = $coordinates['lng'] ?? $alamat->longitude; // Gunakan lng lama jika geocode gagal
        // $latitude = $request->input('edit_latitude'); // Komentari atau hapus baris ini
        // $longitude = $request->input('edit_longitude'); // Komentari atau hapus baris ini

        $updateData = [
            'nama_penerima' => $request->input('edit_nama_penerima'),
            'nomor_telepon' => $request->input('edit_nomor_telepon'),
            'label_alamat' => $request->input('edit_label_alamat'),
            'alamat_lengkap' => $request->input('edit_alamat_lengkap'),
            'provinsi' => $request->input('edit_provinsi'),
            'kota' => $request->input('edit_kota'),
            'kode_pos' => $request->input('edit_kode_pos'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_utama' => $isUtama,
        ];

        $alamat->update($updateData);

        return redirect()->route('profile.show')->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Menghapus alamat pengiriman.
     */
    public function destroyAlamat(AlamatPengiriman $alamat)
    {
        $pelangganSessionData = Session::get('pelanggan');
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        if (!$pelangganId || $alamat->id_pelanggan !== $pelangganId) {
            return redirect()->route('profile.show')->with('error', 'Aksi tidak diizinkan.');
        }

        // Logika tambahan: jika alamat yang dihapus adalah utama,
        // dan masih ada alamat lain, jadikan alamat lain (misal yang terbaru) sebagai utama.
        if ($alamat->is_utama) {
            $alamatLain = AlamatPengiriman::where('id_pelanggan', $pelangganId)
                ->where('id', '!=', $alamat->id)
                ->orderBy('created_at', 'desc') // atau kriteria lain
                ->first();
            if ($alamatLain) {
                $alamatLain->update(['is_utama' => true]);
            }
        }

        $alamat->delete();

        return redirect()->route('profile.show')->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * Menjadikan alamat sebagai alamat utama.
     */
    public function setAlamatUtama(AlamatPengiriman $alamat)
    {
        $pelangganSessionData = Session::get('pelanggan');
        $pelangganId = is_array($pelangganSessionData) ? ($pelangganSessionData['id_pelanggan'] ?? null) : ($pelangganSessionData->id_pelanggan ?? null);

        if (!$pelangganId || $alamat->id_pelanggan !== $pelangganId) {
            return redirect()->route('profile.show')->with('error', 'Aksi tidak diizinkan.');
        }

        // Set semua alamat lain milik pelanggan ini menjadi tidak utama
        AlamatPengiriman::where('id_pelanggan', $pelangganId)->update(['is_utama' => false]);

        // Set alamat yang dipilih menjadi utama
        $alamat->update(['is_utama' => true]);

        return redirect()->route('profile.show')->with('success', 'Alamat utama berhasil diubah.');
    }
}
