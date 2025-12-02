<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $query = User::with('role');

        // Filters
        $q = request('q');
        $roleId = request('role');

        if ($q) {
            $lq = mb_strtolower($q);
            $query->where(function ($qBuilder) use ($lq) {
                $qBuilder->whereRaw('LOWER(name) LIKE ?', ["%{$lq}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$lq}%"]);
            });
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        $users = $query->orderBy('name')->paginate(12)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'filter' => [
                'q' => $q,
                'role' => $roleId,
            ],
        ]);
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', [
            'user' => $user->load('role'),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $payload = [
            'role_id' => $validated['role_id'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
            $payload['email_verified_at'] = $user->email_verified_at ?? now();
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-delete
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Prevent delete if has orders (due to FK restriction)
        if ($user->orders()->exists()) {
            return back()->with('error', 'Pengguna memiliki order dan tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:delete,change_role'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
            'role_id' => ['nullable', 'required_if:action,change_role', 'integer', 'exists:roles,id'],
        ]);

        $ids = collect($validated['ids'])->unique()->values();
        $currentId = Auth::id();

        $affected = 0;
        $skipped = 0;

        if ($validated['action'] === 'delete') {
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                // skip self and has orders
                if ($user->id === $currentId || $user->orders()->exists()) {
                    $skipped++;
                    continue;
                }
                $user->delete();
                $affected++;
            }
            return back()->with('success', "Berhasil menghapus {$affected} pengguna; dilewati {$skipped}.");
        }

        if ($validated['action'] === 'change_role') {
            $roleId = (int) $validated['role_id'];
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                // allow changing own role? better skip for safety
                if ($user->id === $currentId) {
                    $skipped++;
                    continue;
                }
                $user->update(['role_id' => $roleId]);
                $affected++;
            }
            return back()->with('success', "Berhasil ubah role {$affected} pengguna; dilewati {$skipped}.");
        }

        return back();
    }

    public function sendReset(User $user): RedirectResponse
    {
        if (! $user->email) {
            return back()->with('error', 'Pengguna tidak memiliki email.');
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link reset password telah dikirim.');
        }

        return back()->with('error', __($status));
    }
}
