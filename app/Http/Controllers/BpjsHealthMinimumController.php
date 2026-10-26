<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BpjsHealthMinimumController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['year' => ['nullable', 'integer', 'between:1901,2155']]);
        $year = (int) ($data['year'] ?? now()->year);
        $settings = DB::table('bpjs_health_minimums')->orderByDesc('year')->get();

        return view('database.bpjs-health', ['year' => $year, 'setting' => $settings->firstWhere('year', $year), 'settings' => $settings]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:1901,2155'],
            'minimum_wage' => ['required', 'integer', 'min:1', 'max:12000000'],
            'basis' => ['required', 'in:UMK,UMP'],
            'reference' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        DB::transaction(function () use ($data, $request): void {
            DB::table('bpjs_health_minimums')->insertOrIgnore([
                ...$data, 'created_at' => now(), 'updated_at' => now(), 'updated_by' => $request->user()->id,
            ]);
            DB::table('bpjs_health_minimums')->where('year', $data['year'])->lockForUpdate()->first();
            DB::table('bpjs_health_minimums')->where('year', $data['year'])->update([
                ...$data, 'updated_at' => now(), 'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('bpjs-health.index', ['year' => $data['year']])->with('success', 'Dasar minimum BPJS Kesehatan tahun '.$data['year'].' berhasil disimpan.');
    }
}
