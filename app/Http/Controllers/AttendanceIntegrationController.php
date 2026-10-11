<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployeeSync;
use App\Services\AttendanceConnector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AttendanceIntegrationController extends Controller
{
    public function index(Request $request, AttendanceConnector $connector): View
    {
        $ready = Schema::hasTable('attendance_employee_syncs');
        $filters = $request->validate(['resource' => ['nullable', 'in:attendances,leaves,leave-accounts'], 'source_employee_id' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'], 'result_page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
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
            }
        }

        return view('database.karyawan.attendance', [
            'results' => $results, 'resultError' => $resultError, 'resource' => $filters['resource'] ?? null,
            'ready' => $ready, 'configured' => $connector->configured(),
            'syncs' => $ready ? AttendanceEmployeeSync::orderByDesc('updated_at')->orderByDesc('id')->paginate(25) : null,
            'pending' => $ready ? AttendanceEmployeeSync::whereColumn('source_version', '>', 'delivered_version')->count() : 0,
            'synced' => $ready ? AttendanceEmployeeSync::whereColumn('source_version', '=', 'delivered_version')->where('source_version', '>', 0)->count() : 0,
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
