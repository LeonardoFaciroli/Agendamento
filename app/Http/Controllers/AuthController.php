<?php
// Indica que o arquivo contém código PHP
namespace App\Http\Controllers; // Define o namespace da classe dentro da pasta Controllers

// Importa classes necessárias
use App\Models\Empresa;                     // Importa o model Empresa
use App\Models\User;                        // Importa o model User
use Illuminate\Http\Request;                // Importa a classe Request para lidar com dados de formulário
use Illuminate\Support\Facades\Auth;        // Importa a fachada Auth para autenticação
use Illuminate\Support\Facades\Hash;        // Importa a fachada Hash para criptografar a senha

// Declara a classe AuthController que controlará login e cadastro
class AuthController extends Controller
{
    // Método para exibir a tela de login e cadastro juntos
    public function showLoginAndRegister()
    {
        // Retorna a view 'auth.login_register' para o usuário
        return view('auth.login_register');
    }

    // Método para processar o cadastro de um novo usuário
    public function register(Request $request)
    {
        // Valida os dados enviados pelo formulário
        $validated = $request->validate([
            'name'             => 'required|string|max:255',      // Nome é obrigatório e texto
            'email'            => 'required|email|unique:users',   // E-mail é obrigatório, formato e único
            'password'         => 'required|min:4|confirmed',      // Senha obrigatória, min 4 caracteres, com confirmação
            'empresa_nome'     => 'required|string|max:255',      // Nome da empresa é obrigatório
            'role'             => 'required|in:empresa', // Define se é empresa ou funcionário
        ]);

        // Cria ou encontra a empresa pelo nome informado
        $empresa = Empresa::firstOrCreate(
            // Condições para localizar a empresa
            ['nome' => $validated['empresa_nome']],
            // Dados para criar se não existir
            ['nome' => $validated['empresa_nome']]
        );

        // Cria um novo usuário com os dados validados
        $user = User::create([
            'name'       => $validated['name'],               // Nome do usuário
            'email'      => $validated['email'],              // E-mail do usuário
            'password'   => Hash::make($validated['password']), // Criptografa a senha
            'role'       => $validated['role'],               // Nível de acesso
            'empresa_id' => $empresa->id,                     // Liga o usuário à empresa criada/encontrada
        ]);

        // Faz login automático do usuário recém criado
        Auth::login($user);

        // Redireciona o usuário autenticado para a rota 'dashboard'
        return redirect()->route('dashboard');
    }

    // Método para processar o login de um usuário
    public function login(Request $request)
    {
        // Valida os dados do formulário de login
        $credentials = $request->validate([
            'email'    => 'required|email', // E-mail obrigatório e válido
            'password' => 'required',       // Senha obrigatória
        ]);

        // Tenta autenticar o usuário com as credenciais
        if (Auth::attempt($credentials)) {
            // Regenera a sessão por segurança (evita fixation)
            $request->session()->regenerate();

            // Redireciona para o dashboard caso o login tenha sucesso
            return redirect()->route('dashboard');
        }

        // Se falhar, volta para a mesma página com mensagem de erro
        return back()->withErrors([
            'email' => 'Credenciais inválidas.', // Mensagem de erro genérica
        ])->onlyInput('email'); // Mantém o e-mail preenchido no formulário
    }

    // Método para fazer logout
    public function logout(Request $request)
    {
        // Faz o logout do usuário atual
        Auth::logout();

        // Invalida a sessão atual
        $request->session()->invalidate();

        // Regenera o token de CSRF
        $request->session()->regenerateToken();

        // Redireciona para a tela inicial de login/cadastro
        return redirect()->route('auth.show');
    }
}

