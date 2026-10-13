<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
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
        'vendor-operational' => 'Vendor Operational', 'customer' => 'Customer', 'customer-admin' => 'Customer Admin',
    ];

    public function index(Request $request): View|JsonResponse
    {
        if (! $request->ajax()) {
            return view('pengguna.index', ['roles' => self::ROLES]);
        }

        $request->merge(['length' => min(100, max(1, $request->integer('length', 10))), 'start' => max(0, $request->integer('start')), 'columns' => []]);
        $query = DB::table('users')
            ->leftJoin('vendors', 'users.vendor_id', '=', 'vendors.id')
            ->leftJoin('customers', 'users.customer_id', '=', 'customers.id')
            ->where('users.role', '!=', 'su')
            ->select('users.id', 'users.username', 'users.name', 'users.role', 'vendors.nama as vendor_name', 'customers.nama as customer_name');

        return DataTables::of($query)
            ->filter(function (Builder $query) use ($request): void {
                if ($request->filled('role_filter')) {
                    $query->where('users.role', $request->string('role_filter')->toString());
                }
                $search = mb_substr($request->string('search.value')->trim()->toString(), 0, 100);
                if ($search !== '') {
                    $query->where(function (Builder $query) use ($search): void {
                        foreach (['users.username', 'users.name', 'users.role', 'vendors.nama', 'customers.nama'] as $column) {
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
            ->addColumn('action', fn (object $user): string => view('pengguna.actions', compact('user'))->render())
            ->escapeColumns([])
            ->rawColumns(['action'])
            ->toJson();
    }

    public function options(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => ['required', Rule::in(['vendor', 'customer'])], 'q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = $data['type'] === 'vendor'
            ? Vendor::query()->where('status', 'aktif')
            : Customer::query()->where('status', true);
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

    public function show(string $id): JsonResponse
    {
        $user = User::where('role', '!=', 'su')->select('id', 'name', 'username', 'role', 'email', 'vendor_id', 'customer_id')
            ->with(['vendor:id,nama', 'customer:id,nama'])->findOrFail($id);

        return response()->json(['data' => $user]);
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('pengguna.index');
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get(['id']);
            $user = User::where('role', '!=', 'su')->lockForUpdate()->findOrFail($id);
            $data = $this->validatedData($request, $user);
            if ($user->role === 'admin' && $data['role'] !== 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Minimal satu pengguna dengan role Admin harus tetap tersedia.']);
            }
            $user->update($data);
        });

        return $this->success($request, 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get(['id']);
            $user = User::where('role', '!=', 'su')->lockForUpdate()->findOrFail($id);
            if ($user->is($request->user())) {
                throw ValidationException::withMessages(['user' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
            }
            if ($user->role === 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'Admin terakhir tidak dapat dihapus.']);
            }
            $user->delete();
        });

        return $this->success($request, 'Pengguna berhasil dihapus.');
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
            'vendor_id' => ['exclude_unless:role,vendor,vendor-operational', 'required', 'integer', 'exists:vendors,id'],
            'customer_id' => ['exclude_unless:role,customer,customer-admin', 'required', 'integer', 'exists:customers,id'],
        ], [
            'username.unique' => 'Username sudah digunakan.', 'email.unique' => 'Email sudah digunakan.',
            'vendor_id.required' => 'Pilih vendor untuk role ini.', 'customer_id.required' => 'Pilih customer untuk role ini.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);
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
