<?php

declare(strict_types=1);

use App\Services\Central\CentralPresenceClient;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

$heartbeat = fn (string $tarefa, string $estado = 'success') => app(CentralPresenceClient::class)
    ->heartbeat("site-de-oficina-{$tarefa}", $estado);

Schedule::command('lgpd:expurgo')
    ->dailyAt('03:20')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(fn () => $heartbeat('lgpd-expurgo'))
    ->onFailure(fn () => $heartbeat('lgpd-expurgo', 'fail'));

Schedule::command('backup:clean')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->environments(['staging', 'production']);

Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->environments(['staging', 'production'])
    ->onSuccess(fn () => Artisan::call('backup:reportar', ['--status' => 'sucesso']))
    ->onFailure(fn () => Artisan::call('backup:reportar', ['--status' => 'falha', '--mensagem' => 'backup:run falhou na A melhor oficina']));

Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(fn () => $heartbeat('fila', 'fail'));

Schedule::command('central:entregar-outbox')->everyMinute()->withoutOverlapping();
