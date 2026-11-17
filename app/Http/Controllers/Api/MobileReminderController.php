<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriFilterOliMesin;
use App\Models\User;
use App\Services\ReminderPreferences;
use App\Services\ReminderReader;
use App\Support\ReminderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dashboard Pengingat di aplikasi: membaca rekap & pengaturan yang sama dengan web
 * (ReminderReader, ReminderPreferences), sehingga isi dan urutan selalu sama.
 */
class MobileReminderController extends Controller
{
    private const PER_PAGE = 25;

    public function __construct(private ReminderReader $reader, private ReminderPreferences $preferences) {}

    /** Semua tabel milik role (urut pengaturan) + jumlah tabel aktif; total = lencana beranda. */
    public function index(Request $request): JsonResponse
    {
        abort_if(ReminderRegistry::for($request->user()) === [], 403);

        return response()->json($this->summary($request->user()));
    }

    public function show(Request $request, string $type): JsonResponse
    {
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $user = $request->user();
        $page = max(1, $request->integer('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;
        $total = $this->reader->query($user, $type)->count();
        $rows = $this->reader->rows($user, $type, $offset, self::PER_PAGE);
        if ($type === ReminderRegistry::FILTER_OIL) {
            // HP: hanya kategori yang perlu perhatian, berurutan seperti kolom web.
            $names = KategoriFilterOliMesin::orderBy('id')->pluck('nama', 'id');
            $rows = array_map(fn (array $row): array => $row + ['kategori' => $names
                ->filter(fn (string $name, int $id): bool => isset($row['sisa'][$id]))
                ->map(fn (string $name, int $id): array => ['id' => $id, 'nama' => $name, 'sisa' => $row['sisa'][$id]])->values()->all()], $rows);
        }

        return response()->json([
            'data' => array_map(fn (array $row, int $index): array => ['number' => $offset + $index + 1] + $row, $rows, array_keys($rows)),
            'meta' => ['current_page' => $page, 'last_page' => max(1, (int) ceil($total / self::PER_PAGE)), 'total' => $total,
                'refreshed_at' => $this->reader->refreshedAt($type)?->toIso8601String()],
        ]);
    }

    /** Simpan urutan & tabel yang tampil (berlaku juga di web). */
    public function settings(Request $request): JsonResponse
    {
        abort_if(ReminderRegistry::for($request->user()) === [], 403);
        $data = $request->validate([
            'order' => ['required', 'array', 'list'], 'order.*' => ['string', 'distinct'],
            'enabled' => ['present', 'array', 'list'], 'enabled.*' => ['string', 'distinct'],
        ]);
        $this->preferences->save($request->user(), $data['order'], $data['enabled']);

        return response()->json($this->summary($request->user()) + ['message' => 'Pengaturan tampilan pengingat disimpan.']);
    }

    private function summary(User $user): array
    {
        $tables = array_map(function (array $table) use ($user): array {
            $refreshed = $this->reader->refreshedAt($table['key']);

            // Tabel nonaktif tidak di-query (count null), sama seperti web.
            return $table + ['count' => $table['enabled'] ? $this->reader->query($user, $table['key'])->count() : null,
                'refreshed_at' => $refreshed?->toIso8601String()];
        }, $this->preferences->layout($user));

        return ['data' => $tables, 'meta' => ['total' => array_sum(array_map(fn (array $table): int => (int) $table['count'], $tables)),
            'attendance_enabled' => (bool) config('attendance.enabled')]];
    }
}
