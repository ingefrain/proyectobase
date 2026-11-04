<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile');
    }

    public function update(Request $request, AdminAuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $user = $request->user();
        $before = ['name' => $user->name];
        $user->update($validated);
        if ($before['name'] !== $user->name) {
            $audit->record('user.profile_updated', 'user', $user->id, $before, ['name' => $user->name]);
        }

        return back()->with('status', 'Perfil actualizado.');
    }

    public function updatePassword(Request $request, AdminAuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        DB::transaction(function () use ($user, $validated, $currentSessionId, $request, $audit): void {
            $user->forceFill([
                'password' => $validated['password'],
                'remember_token' => Str::random(60),
                'session_version' => $user->session_version + 1,
            ])->save();
            DB::table('sessions')->where('user_id', $user->id)->where('id', '<>', $currentSessionId)->delete();
            Auth::guard()->setUser($user);
            $request->session()->put('auth_session_version', $user->session_version);
            $request->session()->regenerate();
            $audit->record('user.password_changed', 'user', $user->id);
        });

        return back()->with('status', 'Contraseña actualizada; las demás sesiones se cerraron.');
    }
}
