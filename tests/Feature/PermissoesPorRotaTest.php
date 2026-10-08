<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;

beforeEach(function () {
    $this->seed(PerfisESeguranca::class);
});

function usuarioCom(string $perfil): User
{
    $usuario = User::create([
        'name' => 'Teste '.$perfil,
        'email' => $perfil.'@teste.local',
        'password' => 'senha-de-teste-123',
        'ativo' => true,
        'email_verified_at' => now(),
    ]);

    $usuario->syncRoles([$perfil]);

    return $usuario;
}

it('exige autenticação em todo o painel', function (string $rota) {
    $this->get($rota)->assertRedirect(route('login'));
})->with([
    '/painel',
    '/painel/fotos',
    '/painel/videos',
    '/painel/usuarios',
    '/painel/auditoria',
]);

it('deixa a oficina cuidar da galeria', function (string $rota) {
    $this->actingAs(usuarioCom(Perfis::OFICINA))->get($rota)->assertOk();
})->with([
    '/painel',
    '/painel/fotos',
    '/painel/videos',
]);

it('não deixa a oficina mexer em conta de acesso nem ver a trilha', function (string $rota) {
    $this->actingAs(usuarioCom(Perfis::OFICINA))->get($rota)->assertForbidden();
})->with([
    '/painel/usuarios',
    '/painel/auditoria',
]);

it('abre tudo para o administrador', function (string $rota) {
    $this->actingAs(usuarioCom(Perfis::ADMINISTRADOR))->get($rota)->assertOk();
})->with([
    '/painel/fotos',
    '/painel/videos',
    '/painel/usuarios',
    '/painel/auditoria',
]);

it('recusa escrita na galeria a quem não tem a permissão', function (string $metodo, string $rota) {
    $semPapel = User::create([
        'name' => 'Sem papel', 'email' => 'sem-papel@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);

    $this->actingAs($semPapel)->{$metodo}($rota)->assertForbidden();
})->with([
    ['post', '/painel/fotos'],
    ['post', '/painel/videos'],
    ['post', '/painel/videos/lote'],
    ['post', '/painel/videos/lote/enviar'],
    ['get', '/painel/videos/imprimir'],
    ['post', '/painel/usuarios/lote'],
    ['get', '/painel/usuarios/imprimir'],
    ['get', '/painel/fotos'],
    ['get', '/painel/videos'],
]);
