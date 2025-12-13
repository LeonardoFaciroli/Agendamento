<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyRequestController;
use App\Http\Controllers\DailyShiftController;
use App\Http\Controllers\DailyDemandController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\MercadoPagoWebhookController;

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem login)
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLoginAndRegister'])
    ->name('auth.show');

Route::get('/login', [AuthController::class, 'showLoginAndRegister'])
    ->name('login');

Route::post('/register', [AuthController::class, 'register'])
    ->name('auth.register');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

/*
|--------------------------------------------------------------------------
| Rotas que exigem login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'empresa.ativa'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Rota usada pelo FullCalendar para buscar os eventos (turnos)
    Route::get('/calendar/events', [DashboardController::class, 'getEvents'])
        ->name('calendar.events');

    /*
    |--------------------------------------------------------------------------
    | Requisições de Diária (funcionário e empresa)
    |--------------------------------------------------------------------------
    */

    Route::post('/daily-requests', [DailyRequestController::class, 'store'])
        ->name('daily_requests.store');

    Route::post('/daily-requests/{id}/status', [DailyRequestController::class, 'updateStatus'])
        ->name('daily_requests.updateStatus');

    Route::get('/daily-requests/my', [DailyRequestController::class, 'myRequests'])
        ->name('daily_requests.my');

    Route::get('/daily-requests', [DailyRequestController::class, 'index'])
        ->name('daily_requests.index');

    /*
    |--------------------------------------------------------------------------
    | Turnos / Demanda de Diárias (empresa)
    |--------------------------------------------------------------------------
    */

    Route::post('/daily-shifts', [DailyShiftController::class, 'store'])
        ->name('daily_shifts.store');

    Route::get('/daily-shifts/{data_diaria}', [DailyShiftController::class, 'getByDate'])
        ->name('daily_shifts.byDate');

    /*
    |--------------------------------------------------------------------------
    | QR Code de presença
    |--------------------------------------------------------------------------
    */

    Route::get('/presenca/scanner', [AttendanceController::class, 'scanner'])
        ->name('presenca.scanner');

    Route::get('/presenca/qr/{token}', [AttendanceController::class, 'registrarViaQr'])
        ->name('presenca.registrar_via_qr');

    Route::get('/presenca/escalados', [AttendanceController::class, 'listarEscalados'])
        ->name('presenca.escalados');

    Route::post('/presenca/escalados/{userId}', [AttendanceController::class, 'registrarManual'])
        ->name('presenca.escalados.registrar');

    /*
    |--------------------------------------------------------------------------
    | Perfil do funcionário com o QR
    |--------------------------------------------------------------------------
    */

    Route::get('/funcionarios/{id}', [UserController::class, 'show'])
        ->name('funcionarios.show');

    /*
    |--------------------------------------------------------------------------
    | Relatórios
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [ReportsController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/pendentes/pdf', [ReportsController::class, 'pendentesPdf'])
        ->name('reports.pendentes.pdf');

    /*
    |--------------------------------------------------------------------------
    | Billing / Assinatura
    |--------------------------------------------------------------------------
    */
    Route::get('/billing', [BillingController::class, 'index'])
        ->name('billing.index');
    Route::post('/billing/preapproval', [BillingController::class, 'createPreapproval'])
        ->name('billing.create');
    Route::post('/billing/sync', [BillingController::class, 'sync'])
        ->name('billing.sync');
    Route::get('/billing/return', [BillingController::class, 'handleReturn'])
        ->name('billing.return');
});

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('billing.webhook');
