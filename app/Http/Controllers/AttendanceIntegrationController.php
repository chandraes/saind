<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployeeSync;
use App\Services\AttendanceConnector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AttendanceIntegrationController extends Controller
{
    public function index(Request $request, AttendanceConnector $connector): View|JsonResponse
    {
        $ready = Schema::hasTable('attendance_employee_syncs');
        if ($request->ajax() && $request->input('table') === 'syncs') {
            return $this->syncData($request, $ready);
        }
        $filters = $request->validate(['resource' => ['nullable', 'in:attendances,leaves,leave-accounts'], 'source_employee_id' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])], 'result_page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $results = null;
        $resultError = null;
        if ($request->filled('resource')) {
            try {
                $results = $connector->results($filters['resource'], array_filter([
                    'source_employee_id' => $filters['source_employee_id'] ?? null, 'from' => $filters['from'] ?? null,
                    'to' => $filters['to'] ?? null, 'page' => (int) ($filters['result_page'] ?? 1),
                ], fn ($value) => $value !== null && $value !== ''));
            } catch (\RuntimeException $exception) {
                $resultError = $exception->getMessage();
                if ($request->ajax()) {
                    return response()->json(['message' => $resultError], 503);
                }
            }
        }

        if ($request->ajax()) {
            return response()->json($results ?? ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0]);
        }

        return view('database.karyawan.attendance', [
            'results' => $results, 'resultError' => $resultError, 'resource' => $filters['resource'] ?? null,
            'ready' => $ready, 'configured' => $connector->configured(),
            'syncs' => $ready ? AttendanceEmployeeSync::orderByDesc('updated_at')->orderByDesc('id')->limit(25)->get() : null,
            'pending' => $ready ? AttendanceEmployeeSync::whereColumn('source_version', '>', 'delivered_version')->count() : 0,
            'synced' => $ready ? AttendanceEmployeeSync::whereColumn('source_version', '=', 'delivered_version')->where('source_version', '>', 0)->count() : 0,
        ]);
    }

    private function syncData(Request $request, bool $ready): JsonResponse
    {
        $start = max(0, $request->integer('start'));
        $length = min(100, max(1, $request->integer('length', 25)));
        $total = $ready ? AttendanceEmployeeSync::count() : 0;
        $rows = $ready ? AttendanceEmployeeSync::orderByDesc('updated_at')->orderByDesc('id')
            ->offset($start)->limit($length)->get(['source_employee_id', 'payload', 'source_version', 'delivered_version', 'last_synced_at', 'last_error']) : collect();

        return response()->json([
            'draw' => max(0, $request->integer('draw')), 'recordsTotal' => $total, 'recordsFiltered' => $total,
            'data' => $rows->map(fn (AttendanceEmployeeSync $sync, int $index): array => [
                'number' => $start + $index + 1,
                'employee' => ($sync->payload['employee_number'] ?? $sync->source_employee_id).' · '.($sync->payload['name'] ?? '—'),
                'status' => ($sync->payload['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif',
                'delivery' => $sync->source_version === $sync->delivered_version ? 'Tersinkron' : 'Menunggu',
                'last_synced' => $sync->last_synced_at?->format('d/m/Y H:i') ?? '—',
                'error' => $sync->last_error ?? '—',
            ])->all(),
        ]);
    }

    public function sync(AttendanceConnector $connector): RedirectResponse
    {
        if (! $connector->configured() || ! Schema::hasTable('attendance_employee_syncs')) {
            return back()->with('error', 'Integrasi belum siap. Periksa migrasi, URL HTTPS, dan token absensi.');
        }
        $count = $connector->reconcile();
        $result = $connector->flush(100, true);

        return to_route('attendance-integration.index')->with('success', $count.' pegawai diperiksa; '.$result['sent'].' terkirim; '.$result['pending'].' menunggu percobaan ulang.');
    }
}
