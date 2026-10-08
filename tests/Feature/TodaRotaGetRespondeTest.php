<?php

declare(strict_types=1);

use App\Models\Foto;
use App\Models\User;
use App\Models\Video;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->seed(PerfisESeguranca::class);

    $this->admin = User::create([
        'name' => 'Admin da varredura',
        'email' => 'varredura@teste.local',
        'password' => 'senha-de-teste-123',
        'ativo' => true,
        'email_verified_at' => now(),
    ]);
    $this->admin->syncRoles([Perfis::ADMINISTRADOR]);
});

function exemplaresParaRota(User $admin): array
{
    $video = Video::create(['titulo' => 'Vídeo de varredura', 'provedor' => 'youtube', 'codigo' => 'dQw4w9WgXcQ', 'na_home' => true]);
    $foto = Foto::create(['titulo' => 'Foto de varredura', 'arquivo_path' => 'fotos/varredura.jpg']);

    return [
        'video' => (string) $video->uuid,
        'foto' => (string) $foto->uuid,
        'usuario' => (string) $admin->uuid,
    ];
}

const ROTAS_FORA_DA_VARREDURA = [

    'sanctum.csrf-cookie' => 'não é tela',

    'storage.local' => 'serve arquivo, não tela',
    'storage.local.upload' => 'serve arquivo, não tela',

    'up' => 'health check do framework',

    'verification.verify' => 'exige link assinado; coberto pelo fluxo de verificação',
    'password.reset' => 'exige token válido; coberto pelo fluxo de senha',

    'password.confirm' => 'tela de confirmação de senha, coberta em Auth',

    'captcha.desafio' => 'devolve JSON, e 404 em testing é o comportamento certo',
];

it('responde em toda rota GET do site e do painel', function () {
    $exemplares = exemplaresParaRota($this->admin);
    $porRota = [];

    $falhas = [];
    $visitadas = 0;

    foreach (Route::getRoutes() as $rota) {
        $nome = $rota->getName();

        if (! in_array('GET', $rota->methods(), true)) {
            continue;
        }

        if ($nome !== null && array_key_exists($nome, ROTAS_FORA_DA_VARREDURA)) {
            continue;
        }

        if ($nome === null || $rota->uri() === 'up') {
            continue;
        }

        $uri = $rota->uri();
        $desconhecidos = [];
        $doCaso = array_merge($exemplares, $porRota[$nome] ?? []);

        foreach ($rota->parameterNames() as $parametro) {
            if (! array_key_exists($parametro, $doCaso)) {
                $desconhecidos[] = $parametro;

                continue;
            }

            $uri = preg_replace('/\{'.$parametro.'(:[^}]+)?\??\}/', $doCaso[$parametro], $uri) ?? $uri;
        }

        if ($desconhecidos !== []) {
            $falhas[] = "{$nome} → sem exemplar para: ".implode(', ', $desconhecidos)
                .' (crie um em `exemplaresParaRota()`)';

            continue;
        }

        $resposta = $this->actingAs($this->admin)->get('/'.ltrim($uri, '/'));
        $visitadas++;

        $codigo = $resposta->baseResponse->getStatusCode();

        if (! in_array($codigo, [200, 301, 302], true)) {
            $falhas[] = "{$nome} ({$uri}) → HTTP {$codigo}";
        }
    }

    expect($falhas)->toBe([], "Rotas que não responderam:\n  ".implode("\n  ", $falhas));
    expect($visitadas)->toBeGreaterThan(15);
});
