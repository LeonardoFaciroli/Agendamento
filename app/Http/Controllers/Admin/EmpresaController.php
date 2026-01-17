<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmpresaInviteMail;
use App\Models\Convite;
use App\Models\Diarista;
use App\Models\Empresa;
use App\Models\Filial;
use App\Models\Gestor;
use App\Models\Rh;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::with('filiais')
            ->orderBy('nome')
            ->get();

        $filiais = Filial::with('empresa')
            ->orderBy('nome')
            ->get();

        $diaristas = Diarista::with(['user', 'filial', 'empresa'])
            ->orderBy('nome')
            ->get();

        $convitesPendentes = Convite::with(['user.empresa', 'user.filial'])
            ->whereNull('accepted_at')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.empresas.index', [
            'empresas' => $empresas,
            'filiais' => $filiais,
            'diaristas' => $diaristas,
            'convitesPendentes' => $convitesPendentes,
        ]);
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('filiais');

        $diaristasCount = Diarista::where('empresa_id', $empresa->id)->count();
        $gestoresCount = Gestor::where('empresa_id', $empresa->id)->count();
        $rhsCount = Rh::where('empresa_id', $empresa->id)->count();
        $supervisoresCount = Supervisor::where('empresa_id', $empresa->id)->count();

        $diaristasPorFilial = Diarista::select('filial_id', DB::raw('count(*) as total'))
            ->where('empresa_id', $empresa->id)
            ->groupBy('filial_id')
            ->pluck('total', 'filial_id');

        return view('admin.empresas.show', [
            'empresa' => $empresa,
            'diaristasCount' => $diaristasCount,
            'gestoresCount' => $gestoresCount,
            'rhsCount' => $rhsCount,
            'supervisoresCount' => $supervisoresCount,
            'diaristasPorFilial' => $diaristasPorFilial,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_nome' => 'required|string|max:255',
            'filial_nome' => 'required|string|max:255',
            'filial_cidade' => 'nullable|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
        ]);

        $empresaNome = trim($validated['empresa_nome']);
        $filialNome = trim($validated['filial_nome']);
        $filialCidade = $validated['filial_cidade'] ? trim($validated['filial_cidade']) : null;

        $empresa = Empresa::firstOrCreate([
            'nome' => $empresaNome,
        ]);

        $filial = Filial::firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'nome' => $filialNome,
            ],
            [
                'cidade' => $filialCidade,
            ]
        );

        $token = Str::random(64);

        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'gestor',
            'empresa_id' => $empresa->id,
            'filial_id' => $filial->id,
        ]);

        Gestor::create([
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'filial_id' => $filial->id,
        ]);

        $convite = Convite::create([
            'user_id' => $user->id,
            'token' => $token,
        ]);

        Mail::to($user->email)->send(new EmpresaInviteMail($convite, $empresa, $filial));

        return back()->with('success', 'Convite enviado para o cliente.');
    }

    public function resendInvite(Convite $convite)
    {
        $convite->load(['user.empresa', 'user.filial']);

        if (! $convite->user) {
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

        return back()->with('success', 'Convite reenviado para o cliente.');
    }

    public function storeSupervisor(Request $request)
    {
        $validated = $request->validate([
            'supervisor_nome' => 'required|string|max:255',
            'supervisor_email' => 'required|email|unique:users,email',
            'supervisor_filial_id' => 'required|exists:filiais,id',
        ]);

        $filial = Filial::with('empresa')->findOrFail($validated['supervisor_filial_id']);

        $user = User::create([
            'name' => $validated['supervisor_nome'],
            'email' => $validated['supervisor_email'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'supervisor',
            'empresa_id' => $filial->empresa_id,
            'filial_id' => $filial->id,
        ]);

        Supervisor::create([
            'user_id' => $user->id,
            'empresa_id' => $filial->empresa_id,
            'filial_id' => $filial->id,
        ]);

        $convite = Convite::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
        ]);

        Mail::to($user->email)->send(new EmpresaInviteMail($convite, $filial->empresa, $filial));

        return back()->with('success', 'Convite enviado para o porteiro.');
    }

    public function updateDiaristaFilial(Request $request)
    {
        $validated = $request->validate([
            'diarista_id' => 'required|exists:diaristas,id',
            'filial_id' => 'required|exists:filiais,id',
        ]);

        $diarista = Diarista::with('user')->findOrFail($validated['diarista_id']);
        $filial = Filial::with('empresa')->findOrFail($validated['filial_id']);

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
}
