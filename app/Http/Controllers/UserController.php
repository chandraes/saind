<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public const ROLES = [
        'admin' => 'Admin', 'user' => 'User', 'asisten-user' => 'Asisten User',
        'operasional' => 'Operasional', 'investor' => 'Investor', 'vendor' => 'Vendor',
        'vendor-induk' => 'Vendor Induk', 'vendor-operational' => 'Vendor Operational', 'customer' => 'Customer', 'customer-admin' => 'Customer Admin', 'driver' => 'Driver', 'hrd' => 'HRD', 'general-manager' => 'General Manager', 'direksi' => 'Direksi',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $isSu = $request->user()->role === 'su';
        if (! $request->ajax()) {
            return view('pengguna.index', ['roles' => self::ROLES, 'isSu' => $isSu]);
        }

        $inactive = $request->input('account_scope') === 'inactive';
        abort_if($inactive && ! $isSu, 403);

        $request->merge(['length' => min(100, max(1, $request->integer('length', 10))), 'start' => max(0, $request->integer('start')), 'columns' => []]);
        $query = DB::table('users')
            ->leftJoin('vendors', 'users.vendor_id', '=', 'vendors.id')
            ->leftJoin('customers', 'users.customer_id', '=', 'customers.id')
            ->leftJoin('drivers', 'users.driver_id', '=', 'drivers.id')
            ->where('users.role', '!=', 'su')
            ->whereNull('users.deactivated_at', not: $inactive)
            ->select('users.id', 'users.username', 'users.name', 'users.role', 'vendors.nama as vendor_name', 'customers.nama as customer_name', 'drivers.nama as driver_name');

        return DataTables::of($query)
            ->filter(function (Builder $query) use ($request): void {
                if ($request->filled('role_filter')) {
                    $query->where('users.role', $request->string('role_filter')->toString());
                }
                $search = mb_substr($request->string('search.value')->trim()->toString(), 0, 100);
                if ($search !== '') {
                    $query->where(function (Builder $query) use ($search): void {
                        foreach (['users.username', 'users.name', 'users.role', 'vendors.nama', 'customers.nama', 'drivers.nama'] as $column) {
                            $query->orWhere($column, 'like', '%'.$search.'%');
                        }
                    });
                }
            })
            ->order(function (Builder $query) use ($request): void {
                $columns = [1 => 'users.username', 2 => 'users.name', 3 => 'users.role'];
                $column = $columns[$request->integer('order.0.column', 3)] ?? 'users.role';
                $direction = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
                $query->orderBy($column, $direction)->orderBy('users.id');
            })
            ->addIndexColumn()
            ->addColumn('action', fn (object $user): string => view('pengguna.actions', ['user' => $user, 'inactive' => $inactive, 'isSu' => $isSu])->render())
            ->escapeColumns([])
            ->rawColumns(['action'])
            ->toJson();
    }

    public function options(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => ['required', Rule::in(['vendor', 'customer', 'driver'])], 'q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = match ($data['type']) {
            'vendor' => Vendor::query()->where('status', 'aktif'),
            'customer' => Customer::query()->where('status', true),
            'driver' => Driver::query()->where('status', 'aktif'),
        };
        $page = min(1000, $request->integer('page', 1));
        $items = $query->select('id', 'nama')->when($request->filled('q'), fn ($query) => $query->where('nama', 'like', '%'.$data['q'].'%'))
            ->orderBy('nama')->orderBy('id')->offset(($page - 1) * 20)->limit(21)->get();

        return response()->json(['results' => $items->take(20)->map(fn ($item): array => ['id' => $item->id, 'text' => $item->nama])->values(), 'pagination' => ['more' => $items->count() > 20]]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('pengguna.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        User::create($this->validatedData($request));

        return $this->success($request, 'Pengguna berhasil ditambahkan.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = User::where('role', '!=', 'su')->when($request->user()->role !== 'su', fn ($query) => $query->whereNull('deactivated_at'))->select('id', 'name', 'username', 'role', 'email', 'vendor_id', 'customer_id', 'driver_id')
            ->with(['vendor:id,nama', 'customer:id,nama', 'driver:id,nama'])->findOrFail($id);

        return response()->json(['data' => $user]);
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('pengguna.index');
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $activeAdmins = $this->lockActiveAdmins();
            $user = User::where('role', '!=', 'su')->whereNull('deactivated_at')->lockForUpdate()->findOrFail($id);
            $data = $this->validatedData($request, $user);
            if ($user->role === 'admin' && $data['role'] !== 'admin' && $activeAdmins <= 1) {
                throw ValidationException::withMessages(['role' => 'Minimal satu pengguna dengan role Admin harus tetap tersedia.']);
            }
            $user->update($data);
        });

        return $this->success($request, 'Pengguna berhasil diperbarui.');
    }

    public function deactivate(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $activeAdmins = $this->lockActiveAdmins();
            $user = User::where('role', '!=', 'su')->whereNull('deactivated_at')->lockForUpdate()->findOrFail($id);
            $this->ensureRemovalAllowed($request->user(), $user, $activeAdmins);

            $user->forceFill([
                'deactivated_at' => now(),
                'remember_token' => null,
                'account_session_version' => (int) $user->account_session_version + 1,
            ])->save();
            $user->tokens()->delete();
        });

        return $this->success($request, 'Pengguna berhasil dinonaktifkan.');
    }

    public function reactivate(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $user = User::where('role', '!=', 'su')->whereNotNull('deactivated_at')->lockForUpdate()->findOrFail($id);
            $user->forceFill(['deactivated_at' => null])->save();
        });

        return $this->success($request, 'Pengguna berhasil diaktifkan kembali.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id): void {
                $activeAdmins = $this->lockActiveAdmins();
                $user = User::where('role', '!=', 'su')->lockForUpdate()->findOrFail($id);
                $this->ensureRemovalAllowed($request->user(), $user, $activeAdmins);

                $user->delete();
                $user->tokens()->delete();
            });
        } catch (QueryException $exception) {
            [$state, $code, $detail] = ($exception->errorInfo ?? []) + [null, null, ''];
            $blockedByReference = $state === '23000' && ((int) $code === 1451
                || ((int) $code === 19 && str_contains($detail, 'FOREIGN KEY constraint failed')));
            if (! $blockedByReference) {
                throw $exception;
            }

            throw ValidationException::withMessages(['user' => 'Akun masih terhubung ke riwayat atau data lain. Gunakan Nonaktifkan untuk menghentikan akses dan menyimpan riwayat.']);
        }

        return $this->success($request, 'Pengguna berhasil dihapus.');
    }

    private function lockActiveAdmins(): int
    {
        return User::where('role', 'admin')->whereNull('deactivated_at')->orderBy('id')->lockForUpdate()->get(['id'])->count();
    }

    private function ensureRemovalAllowed(User $actor, User $target, int $activeAdmins): void
    {
        if ($target->is($actor)) {
            throw ValidationException::withMessages(['user' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan atau dihapus.']);
        }
        if ($target->role === 'admin' && $target->isActive() && $activeAdmins <= 1) {
            throw ValidationException::withMessages(['user' => 'Admin aktif terakhir tidak dapat dinonaktifkan atau dihapus.']);
        }
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'vendor_id' => ['exclude_unless:role,vendor,vendor-induk,vendor-operational', 'required', 'integer', 'exists:vendors,id'],
            'driver_id' => ['exclude_unless:role,driver', 'required', 'integer', 'exists:drivers,id', Rule::unique('users', 'driver_id')->ignore($user?->id)],
            'customer_id' => ['exclude_unless:role,customer,customer-admin', 'required', 'integer', 'exists:customers,id'],
        ], [
            'username.unique' => 'Username sudah digunakan.', 'email.unique' => 'Email sudah digunakan.',
            'vendor_id.required' => 'Pilih vendor untuk role ini.', 'customer_id.required' => 'Pilih customer untuk role ini.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);
        $data['driver_id'] = $data['driver_id'] ?? null;
        $data['vendor_id'] = $data['vendor_id'] ?? null;
        $data['customer_id'] = $data['customer_id'] ?? null;
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function success(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson() ? response()->json(['message' => $message]) : redirect()->route('pengguna.index')->with('success', $message);
    }
}
