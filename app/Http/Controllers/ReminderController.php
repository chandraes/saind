<?php

namespace App\Http\Controllers;

use App\Models\KategoriFilterOliMesin;
use App\Services\ReminderPreferences;
use App\Services\ReminderReader;
use App\Support\ReminderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Dashboard Pengingat: hanya membaca rekap reminder_items (lihat ReminderRefresher),
 * tabel dimuat per jenis lewat data() dengan infinite scroll. Urutan & tabel yang tampil
 * mengikuti pengaturan akun (ReminderPreferences).
 */
class ReminderController extends Controller
{
    public function __construct(private ReminderReader $reader, private ReminderPreferences $preferences) {}

    public function index(Request $request): View
    {
        abort_if(ReminderRegistry::for($request->user()) === [], 403);
        $layout = $this->preferences->layout($request->user());
        $types = array_column(array_filter($layout, fn (array $table): bool => $table['enabled']), 'key');

        return view('reminders.index', [
            'layout' => $layout,
            'tables' => collect($types)->map(fn (string $type): array => ['key' => $type, 'title' => ReminderRegistry::title($type),
                'count' => $this->reader->query($request->user(), $type)->count(), 'refreshed_at' => $this->reader->refreshedAt($type)])->all(),
            'categories' => in_array(ReminderRegistry::FILTER_OIL, $types, true) ? KategoriFilterOliMesin::orderBy('id')->get(['id', 'nama']) : collect(),
            'attendanceEnabled' => (bool) config('attendance.enabled'),
        ]);
    }

    public function data(Request $request, string $type): JsonResponse
    {
        $request->validate(['start' => ['sometimes', 'integer', 'min:0']]);

        return response()->json([
            'data' => $this->reader->rows($request->user(), $type, $request->integer('start')),
            'recordsFiltered' => $this->reader->query($request->user(), $type)->count(),
        ]);
    }

    /** Simpan urutan & tabel yang ditampilkan untuk akun sendiri. */
    public function settings(Request $request): RedirectResponse
    {
        abort_if(ReminderRegistry::for($request->user()) === [], 403);
        $data = $request->validate([
            'order' => ['required', 'array', 'list'], 'order.*' => ['string', 'distinct'],
            'enabled' => ['sometimes', 'array', 'list'], 'enabled.*' => ['string', 'distinct'],
        ]);
        $this->preferences->save($request->user(), $data['order'], $data['enabled'] ?? []);

        return redirect()->route('reminders.index')->with('success', 'Pengaturan tampilan pengingat disimpan.');
    }
}
