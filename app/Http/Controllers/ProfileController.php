<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $totalOrders = Order::where('user_id', $user->id)->count();
        $completedOrders = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('profile', [
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $disk = Storage::disk(config('filesystems.default'));

        if ($request->hasFile('avatar') && Schema::hasColumn('users', 'avatar')) {
            $path = $request->file('avatar')->store('avatars', config('filesystems.default'));
            if (!empty($user->avatar) && $disk->exists($user->avatar)) {
                $disk->delete($user->avatar);
            }
            $user->avatar = $path;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (Schema::hasColumn('users', 'phone')) {
            $user->phone = $data['phone'] ?? null;
        }
        if (Schema::hasColumn('users', 'birthdate')) {
            $user->birthdate = $data['birthdate'] ?? null;
        }
        if (Schema::hasColumn('users', 'address')) {
            $user->address = $data['address'] ?? null;
        }
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->with('error', 'Password saat ini tidak sesuai.');
        }

        $user->password = $data['password'];
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Password berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        Auth::logout();

        if (Schema::hasColumn('users', 'avatar') && !empty($user->avatar)) {
            Storage::disk(config('filesystems.default'))->delete($user->avatar);
        }

        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Akun Anda telah dihapus.');
    }
}
