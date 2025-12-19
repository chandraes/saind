<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil semua setting dan ubah jadi array [key => value]
        // Contoh hasil: ['app_name' => 'My App', 'app_logo' => 'uploads/logo.png']
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name'    => 'nullable|string|max:255',
            'app_logo'    => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png|max:1024',
        ]);

        // 1. UPDATE NAMA APLIKASI
        // Jika input kosong, simpan NULL ke database (agar fallback ke default config berjalan)
        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $request->app_name ?? null]
        );

        // 2. UPDATE LOGO
        $this->handleFileUpload($request, 'app_logo', 'settings/logo');

        // 3. UPDATE FAVICON
        $this->handleFileUpload($request, 'app_favicon', 'settings/favicon');

        // 4. PENTING: Reset Cache agar data baru terbaca di AppServiceProvider
        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    /**
     * Helper function untuk handle upload dan delete file
     */
    private function handleFileUpload($request, $key, $folder)
    {
        // A. Cek apakah user mencentang "Hapus Gambar" (Reset ke Default)
        if ($request->has('delete_' . $key)) {
            $oldFile = Setting::where('key', $key)->value('value');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
            // Set database jadi NULL
            Setting::updateOrCreate(['key' => $key], ['value' => null]);
            return;
        }

        // B. Jika ada file baru di-upload
        if ($request->hasFile($key)) {
            // 1. Hapus file lama dulu jika ada (agar storage tidak penuh sampah)
            $oldFile = Setting::where('key', $key)->value('value');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }

            // 2. Upload file baru
            $path = $request->file($key)->store($folder, 'public');

            // 3. Simpan path baru ke DB
            Setting::updateOrCreate(['key' => $key], ['value' => $path]);
        }
    }

    public function rekening_pajak()
    {
        $setting = Setting::where('key', 'rekening-pajak')->first();

        // Decode JSON menjadi array agar bisa dibaca oleh Blade
        $data = $setting ? json_decode($setting->value, true) : null;

        return view('pengaturan.rekening-pajak', compact('data'));
    }

    public function rekening_pajak_store(Request $request): RedirectResponse
    {
        // 1. Validasi yang lebih ketat
        $validated = $request->validate([
            'nama_rek' => 'required|string|max:255',
            'no_rek'   => 'required|string|max:50',
            'bank'     => 'required|string|max:100'
        ]);

        // 2. Simpan dengan struktur yang benar
        // Asumsi nama kolom di tabel settings adalah 'key' dan 'value'
        Setting::updateOrCreate(
            ['key' => 'rekening-pajak'], // Pencarian berdasarkan key
            ['value' => json_encode($validated)] // Data yang disimpan/diperbarui
        );

        return redirect()->back()->with('success', 'Data rekening pajak berhasil diperbarui!');
    }
}
