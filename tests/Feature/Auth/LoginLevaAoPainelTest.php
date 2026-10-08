<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;

beforeEach(fn () => $this->seed(PerfisESeguranca::class));

function contaParaLogin(string $perfil): User
{
    $usuario = User::create([
        'name' => 'Login '.$perfil,
        'email' => $perfil.'@login.local',
        'password' => 'senha-de-teste-123',
        'ativo' => true,
        'email_verified_at' => now(),
    ]);

    $usuario->syncRoles([$perfil]);

    return $usuario;
}

it('leva ao painel depois de entrar', function (string $perfil) {
    $usuario = contaParaLogin($perfil);

    $this->post('/login', ['email' => $usuario->email, 'password' => 'senha-de-teste-123'])
        ->assertRedirect(route('painel.inicio', absolute: false));

    $this->assertAuthenticatedAs($usuario);
})->with([Perfis::ADMINISTRADOR, Perfis::OFICINA]);

it('devolve para onde a pessoa queria ir', function () {
    $usuario = contaParaLogin(Perfis::ADMINISTRADOR);

    $this->get('/painel/fotos')->assertRedirect(route('login'));

    $this->post('/login', ['email' => $usuario->email, 'password' => 'senha-de-teste-123'])
        ->assertRedirect('/painel/fotos');
});

it('carimba o último acesso', function () {
    $usuario = contaParaLogin(Perfis::OFICINA);

    expect($usuario->ultimo_acesso_em)->toBeNull();

    $this->post('/login', ['email' => $usuario->email, 'password' => 'senha-de-teste-123']);

    expect($usuario->fresh()->ultimo_acesso_em)->not->toBeNull();
});
