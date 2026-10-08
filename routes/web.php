<?php

declare(strict_types=1);

use App\Http\Controllers\CaptchaChallengeController;
use App\Http\Controllers\ExportarDadosDoTitularController;
use App\Http\Controllers\FeedbackDeTelaController;
use App\Http\Controllers\Painel\AcaoEmLoteController;
use App\Http\Controllers\Painel\AcaoEmLoteFotoController;
use App\Http\Controllers\Painel\AuditoriaController;
use App\Http\Controllers\Painel\EnviarListaController;
use App\Http\Controllers\Painel\EnviarListaDeFotosController;
use App\Http\Controllers\Painel\FotoController;
use App\Http\Controllers\Painel\ImprimirFotosController;
use App\Http\Controllers\Painel\ImprimirListaController;
use App\Http\Controllers\Painel\OficinaController;
use App\Http\Controllers\Painel\PainelController;
use App\Http\Controllers\Painel\UsuarioController;
use App\Http\Controllers\Painel\VideoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ReportarErroController;
use App\Http\Controllers\Site\SiteController;
use App\Support\Lote\ListasEmLote;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/galeria', [SiteController::class, 'galeria'])->name('site.galeria');

Route::get('/dados-da-oficina.js', [SiteController::class, 'dados'])->name('site.dados');
Route::get('/llms.txt', [SiteController::class, 'llms'])->name('site.llms');

Route::permanentRedirect('/galeria.html', '/galeria');
Route::permanentRedirect('/index.html', '/');

Route::get('/captcha/desafio', CaptchaChallengeController::class)
    ->middleware('throttle:60,1')
    ->name('captcha.desafio');

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');
Route::get('/inicio', [PwaController::class, 'inicio'])->name('pwa.inicio');

Route::middleware(['auth', 'verified'])->prefix('painel')->name('painel.')->group(function () {
    Route::get('/', [PainelController::class, 'index'])->name('inicio');

    foreach (ListasEmLote::todas() as $slug => $lista) {
        Route::get("{$slug}/imprimir", ImprimirListaController::class)
            ->middleware("can:{$lista['ver']}")->defaults('lista', $slug)->name("{$slug}.imprimir");

        Route::post("{$slug}/lote", AcaoEmLoteController::class)
            ->middleware("can:{$lista['gerenciar']}")->defaults('lista', $slug)->name("{$slug}.lote");

        if ($lista['publicos'] !== null) {
            Route::post("{$slug}/lote/enviar", EnviarListaController::class)
                ->middleware(["can:{$lista['gerenciar']}", 'throttle:6,1'])->defaults('lista', $slug)->name("{$slug}.lote.enviar");
        }
    }

    Route::get('fotos', [FotoController::class, 'index'])->middleware('can:fotos.ver')->name('fotos.index');
    Route::get('fotos/imprimir', ImprimirFotosController::class)->middleware('can:fotos.ver')->name('fotos.imprimir');
    Route::middleware('can:fotos.gerenciar')->group(function () {
        Route::post('fotos', [FotoController::class, 'store'])->name('fotos.store');
        Route::put('fotos/{foto}', [FotoController::class, 'update'])->name('fotos.update');
        Route::delete('fotos/{foto}', [FotoController::class, 'destroy'])->name('fotos.destroy');

        Route::post('fotos/lote', AcaoEmLoteFotoController::class)->name('fotos.lote');

        Route::post('fotos/lote/enviar', EnviarListaDeFotosController::class)
            ->middleware('throttle:6,1')->name('fotos.lote.enviar');
    });

    Route::get('videos', [VideoController::class, 'index'])->middleware('can:videos.ver')->name('videos.index');
    Route::middleware('can:videos.gerenciar')->group(function () {
        Route::post('videos', [VideoController::class, 'store'])->name('videos.store');
        Route::put('videos/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    });

    Route::get('oficina', [OficinaController::class, 'edit'])->middleware('can:oficina.ver')->name('oficina.edit');
    Route::put('oficina', [OficinaController::class, 'update'])->middleware('can:oficina.gerenciar')->name('oficina.update');

    Route::get('usuarios', [UsuarioController::class, 'index'])
        ->middleware('can:usuarios.ver')->name('usuarios.index');
    Route::middleware('can:usuarios.gerenciar')->group(function () {
        Route::get('usuarios/criar/novo', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::get('auditoria', AuditoriaController::class)
        ->middleware('can:auditoria.ver')->name('auditoria.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/perfil/meus-dados', ExportarDadosDoTitularController::class)->name('perfil.dados');

    Route::post('/push/inscrever', [PushSubscriptionController::class, 'store'])->name('push.store');
    Route::delete('/push/inscrever', [PushSubscriptionController::class, 'destroy'])->name('push.destroy');

    Route::post('/feedback-de-tela', FeedbackDeTelaController::class)
        ->middleware('throttle:10,1')->name('feedback-de-tela');
    Route::post('/reportar-erro', ReportarErroController::class)
        ->middleware('throttle:10,1')->name('reportar-erro');
});

require __DIR__.'/auth.php';
