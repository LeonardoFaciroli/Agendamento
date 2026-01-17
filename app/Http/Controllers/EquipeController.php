<?php

namespace App\Http\Controllers;

use App\Mail\EmpresaInviteMail;
use App\Models\Convite;
use App\Models\Diarista;
use App\Models\Filial;
use App\Models\Rh;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EquipeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem acessar a equipe.');
        }

        $filiais = Filial::where('empresa_id', $user->empresa_id)
            ->orderBy('nome')
            ->get();

        $diaristas = Diarista::with(['user', 'filial'])
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('nome')
            ->get();

        $convitesPendentes = Convite::with(['user.empresa', 'user.filial'])
            ->whereNull('accepted_at')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('empresa_id', $user->empresa_id);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('empresa.equipe', [
            'user' => $user,
            'filiais' => $filiais,
            'diaristas' => $diaristas,
            'convitesPendentes' => $convitesPendentes,
        ]);
    }

    public function storeRh(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem cadastrar RH.');
        }

        $rules = [
            'rh_nome' => 'required|string|max:255',
            'rh_email' => 'required|email|unique:users,email',
        ];

        if (! $user->filial_id) {
            $rules['rh_filial_id'] = 'required|exists:filiais,id';
        }

        $validated = $request->validate($rules);

        $filialId = $user->filial_id ?? $validated['rh_filial_id'];

        $filial = Filial::where('id', $filialId)
            ->where('empresa_id', $user->empresa_id)
            ->first();

        if (! $filial) {
            return back()->withErrors(['general' => 'Filial invalida para esta empresa.']);
        }

        $newUser = User::create([
            'name' => $validated['rh_nome'],
            'email' => $validated['rh_email'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'rh',
            'empresa_id' => $user->empresa_id,
            'filial_id' => $filial->id,
        ]);

        Rh::create([
            'user_id' => $newUser->id,
            'empresa_id' => $user->empresa_id,
            'filial_id' => $filial->id,
        ]);

        $convite = Convite::create([
            'user_id' => $newUser->id,
            'token' => Str::random(64),
        ]);

        Mail::to($newUser->email)->send(
            new EmpresaInviteMail($convite, $filial->empresa, $filial)
        );

        return back()->with('success', 'Convite enviado para o RH.');
    }

    public function storeSupervisor(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem cadastrar porteiros.');
        }

        $rules = [
            'supervisor_nome' => 'required|string|max:255',
            'supervisor_email' => 'required|email|unique:users,email',
        ];

        if (! $user->filial_id) {
            $rules['supervisor_filial_id'] = 'required|exists:filiais,id';
        }

        $validated = $request->validate($rules);

        $filialId = $user->filial_id ?? $validated['supervisor_filial_id'];

        $filial = Filial::where('id', $filialId)
            ->where('empresa_id', $user->empresa_id)
            ->first();

        if (! $filial) {
            return back()->withErrors(['general' => 'Filial invalida para esta empresa.']);
        }

        $newUser = User::create([
            'name' => $validated['supervisor_nome'],
            'email' => $validated['supervisor_email'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'supervisor',
            'empresa_id' => $user->empresa_id,
            'filial_id' => $filial->id,
        ]);

        Supervisor::create([
            'user_id' => $newUser->id,
            'empresa_id' => $user->empresa_id,
            'filial_id' => $filial->id,
        ]);

        $convite = Convite::create([
            'user_id' => $newUser->id,
            'token' => Str::random(64),
        ]);

        Mail::to($newUser->email)->send(
            new EmpresaInviteMail($convite, $filial->empresa, $filial)
        );

        return back()->with('success', 'Convite enviado para o porteiro.');
    }

    public function updateDiaristaFilial(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem alterar a filial.');
        }

        $validated = $request->validate([
            'diarista_id' => 'required|exists:diaristas,id',
            'filial_id' => 'required|exists:filiais,id',
        ]);

        $diarista = Diarista::with('user')
            ->where('empresa_id', $user->empresa_id)
            ->findOrFail($validated['diarista_id']);

        $filial = Filial::where('empresa_id', $user->empresa_id)
            ->findOrFail($validated['filial_id']);

        $diarista->empresa_id = $filial->empresa_id;
        $diarista->filial_id = $filial->id;
        $diarista->save();

        if ($diarista->user) {
            $diarista->user->empresa_id = $filial->empresa_id;
            $diarista->user->filial_id = $filial->id;
            $diarista->user->save();
        }

        return back()->with('success', 'Filial do diarista atualizada.');
    }

    public function resendInvite(Convite $convite)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem reenviar convites.');
        }

        $convite->load(['user.empresa', 'user.filial']);

        if (! $convite->user || $convite->user->empresa_id !== $user->empresa_id) {
            abort(404, 'Convite invalido.');
        }

        if ($convite->accepted_at) {
            return back()->withErrors(['general' => 'Este convite ja foi aceito.']);
        }

        $convite->token = Str::random(64);
        $convite->save();

        Mail::to($convite->user->email)->send(
            new EmpresaInviteMail($convite, $convite->user->empresa, $convite->user->filial)
        );

        return back()->with('success', 'Convite reenviado.');
    }
}
