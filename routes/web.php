<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiaristaRegistrationController;
use App\Http\Controllers\DiaristaProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyRequestController;
use App\Http\Controllers\DailyShiftController;
use App\Http\Controllers\DailyDemandController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\Admin\EmpresaController;

Route::get('/', [AuthController::class, 'showLoginAndRegister'])
    ->name('auth.show');

Route::get('/login', [AuthController::class, 'showLoginAndRegister'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::get('/cadastro', [DiaristaRegistrationController::class, 'create'])
    ->name('diaristas.register');

Route::post('/cadastro', [DiaristaRegistrationController::class, 'store'])
    ->name('diaristas.store');

Route::get('/convite/{token}', [InvitationController::class, 'show'])
    ->name('invites.show');

Route::post('/convite/{token}', [InvitationController::class, 'accept'])
    ->name('invites.accept');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.update');

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('mercadopago.webhook');
Route::get('/webhooks/mercadopago', function () {
    return response()->json(['ok' => true]);
})->name('mercadopago.webhook.ping');

    Route::middleware(['auth', 'admin'])
        ->prefix('admin')
        ->group(function () {
        Route::get('/empresas', [EmpresaController::class, 'index'])
            ->name('admin.empresas.index');
        Route::post('/empresas', [EmpresaController::class, 'store'])
            ->name('admin.empresas.store');
        Route::get('/empresas/{empresa}', [EmpresaController::class, 'show'])
            ->name('admin.empresas.show');
        Route::post('/empresas/convites/{convite}/reenviar', [EmpresaController::class, 'resendInvite'])
            ->name('admin.empresas.convites.resend');
        Route::post('/porteiros', [EmpresaController::class, 'storeSupervisor'])
            ->name('admin.porteiros.store');
        Route::post('/diaristas/filial', [EmpresaController::class, 'updateDiaristaFilial'])
            ->name('admin.diaristas.filial');
    });

Route::middleware(['auth', 'empresa.ativa'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/equipe', [EquipeController::class, 'index'])
        ->name('equipe.index');
    Route::post('/equipe/rh', [EquipeController::class, 'storeRh'])
        ->name('equipe.rh.store');
    Route::post('/equipe/porteiros', [EquipeController::class, 'storeSupervisor'])
        ->name('equipe.porteiros.store');
    Route::post('/equipe/diaristas/filial', [EquipeController::class, 'updateDiaristaFilial'])
        ->name('equipe.diaristas.filial');
    Route::post('/equipe/convites/{convite}/reenviar', [EquipeController::class, 'resendInvite'])
        ->name('equipe.convites.resend');

    Route::get('/meus-dados', [DiaristaProfileController::class, 'edit'])
        ->name('diaristas.profile.edit');
    Route::post('/meus-dados', [DiaristaProfileController::class, 'update'])
        ->name('diaristas.profile.update');

    Route::get('/calendar/events', [DashboardController::class, 'getEvents'])
        ->name('calendar.events');

Route::post('/daily-requests', [DailyRequestController::class, 'store'])
        ->name('daily_requests.store');

    Route::post('/daily-requests/{id}/status', [DailyRequestController::class, 'updateStatus'])
        ->name('daily_requests.updateStatus');

    Route::post('/daily-requests/accept-all', [DailyRequestController::class, 'acceptAll'])
        ->name('daily_requests.acceptAll');

    Route::get('/daily-requests/my', [DailyRequestController::class, 'myRequests'])
        ->name('daily_requests.my');

    Route::get('/daily-requests', [DailyRequestController::class, 'index'])
        ->name('daily_requests.index');

Route::post('/daily-shifts', [DailyShiftController::class, 'store'])
        ->name('daily_shifts.store');

    Route::put('/daily-shifts/{dailyShift}', [DailyShiftController::class, 'update'])
        ->name('daily_shifts.update');

    Route::get('/daily-shifts/{data_diaria}', [DailyShiftController::class, 'getByDate'])
        ->name('daily_shifts.byDate');

Route::get('/presenca/escalados', [AttendanceController::class, 'listarEscalados'])
        ->name('presenca.escalados');

    Route::post('/presenca/escalados/{userId}', [AttendanceController::class, 'registrarManual'])
        ->name('presenca.escalados.registrar');

Route::get('/reports', [ReportsController::class, 'index'])
        ->name('reports.index');

Route::get('/pagamentos', [PagamentoController::class, 'index'])
        ->name('pagamentos.index');
    Route::get('/pagamentos/pendentes', [PagamentoController::class, 'pendentes'])
        ->name('pagamentos.pendentes');
    Route::post('/pagamentos', [PagamentoController::class, 'store'])
        ->name('pagamentos.store');
    Route::get('/pagamentos/{pagamento}/comprovante', [PagamentoController::class, 'comprovante'])
        ->name('pagamentos.comprovante');

Route::get('/billing', [BillingController::class, 'index'])
        ->name('billing.index');
    Route::post('/billing/create', [BillingController::class, 'createPreapproval'])
        ->name('billing.create');
    Route::post('/billing/sync', [BillingController::class, 'sync'])
        ->name('billing.sync');
    Route::get('/billing/return', [BillingController::class, 'handleReturn'])
        ->name('billing.return');
});
