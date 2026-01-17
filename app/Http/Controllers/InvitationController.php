<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function show(string $token)
    {
        $convite = Convite::with('user')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (! $convite || ! $convite->user) {
            return view('auth.invite_invalid');
        }

        return view('auth.invite_accept', [
            'token' => $token,
            'user' => $convite->user,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $convite = Convite::with('user')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (! $convite || ! $convite->user) {
            return view('auth.invite_invalid');
        }

        $validated = $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = $convite->user;
        $user->password = Hash::make($validated['password']);
        $user->email_verified_at = now();
        $user->setRememberToken(Str::random(60));
        $user->save();

        $convite->accepted_at = now();
        $convite->save();

        Auth::login($user);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Convite aceito. Conta ativada.');
    }
}
