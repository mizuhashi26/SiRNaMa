<?php

use App\Http\Controllers\Admin\CampusInformationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LiveChatController;
use App\Http\Controllers\Admin\PaymentReminderController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\WebChatController;
use App\Http\Controllers\Webhook\FonnteWebhookController;
use App\Http\Middleware\AutoLoginLocalAdmin;
use Illuminate\Support\Facades\Route;

// Root URL langsung masuk ke Dashboard Admin CRM
Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');

// Kanal Website Publik & Informasi Kampus
Route::get('/portal', [PublicPortalController::class, 'index'])->name('public.index');
Route::get('/informasi/{slug}', [PublicPortalController::class, 'show'])->name('public.show');

// Endpoint Chatbot Website (JSON untuk Widget)
Route::get('/chat/session', [WebChatController::class, 'session'])->name('webchat.session');
Route::post('/chat/message', [WebChatController::class, 'sendMessage'])->name('webchat.message');

// Endpoint Webhook Fonnte (WhatsApp Masuk)
Route::post('/api/webhook/fonnte', [FonnteWebhookController::class, 'handle'])->name('webhook.fonnte');

// Autentikasi Admin
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard & Modul Admin CRM
Route::prefix('admin')
    ->name('admin.')
    ->middleware(AutoLoginLocalAdmin::class)
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Informasi Kampus
        Route::resource('informations', CampusInformationController::class)->except(['show']);

        // CRUD Mahasiswa Dummy
        Route::resource('students', StudentController::class)->except(['show']);

        // CRUD & Kirim Manual Reminder Pembayaran Umum
        Route::resource('reminders', PaymentReminderController::class);
        Route::post('reminders/{reminder}/send-now', [PaymentReminderController::class, 'sendNow'])
            ->name('reminders.send-now');

        // Live Chat Terpusat (Website & WhatsApp)
        Route::get('live-chat', [LiveChatController::class, 'index'])->name('live-chat.index');
        Route::post('live-chat/{conversation}/take-over', [LiveChatController::class, 'takeOver'])
            ->name('live-chat.take-over');
        Route::post('live-chat/{conversation}/reply', [LiveChatController::class, 'reply'])
            ->name('live-chat.reply');
        Route::post('live-chat/{conversation}/close', [LiveChatController::class, 'close'])
            ->name('live-chat.close');
        Route::get('live-chat/{conversation}/poll', [LiveChatController::class, 'poll'])
            ->name('live-chat.poll');
    });
