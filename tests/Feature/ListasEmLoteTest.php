<?php

declare(strict_types=1);

use App\Mail\ListaDoPainel;
use App\Models\User;
use App\Models\Video;
use App\Support\Lote\ListasEmLote;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(PerfisESeguranca::class);

    $conta = fn (string $nome, ?string $perfil) => tap(User::create([
        'name' => $nome, 'email' => str($nome)->slug().'@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]), fn (User $u) => $perfil && $u->syncRoles([$perfil]));

    $this->admin = $conta('Alex', Perfis::ADMINISTRADOR);
    $this->beto = $conta('Beto', Perfis::OFICINA);
    $this->semPerfil = $conta('Sem perfil', null);
});

function videoDaOficina(array $atributos = []): Video
{
    return Video::create([
        'titulo' => 'Troca da correia', 'provedor' => 'youtube', 'codigo' => 'dQw4w9WgXcQ', ...$atributos,
    ]);
}

it('toda lista do registro tem as rotas com a permissão na ROTA', function () {
    foreach (ListasEmLote::todas() as $slug => $lista) {
        expect(Route::getRoutes()->getByName("painel.{$slug}.lote")->gatherMiddleware())
            ->toContain("can:{$lista['gerenciar']}");
        expect(Route::getRoutes()->getByName("painel.{$slug}.imprimir")->gatherMiddleware())
            ->toContain("can:{$lista['ver']}");
        expect(Route::has("painel.{$slug}.lote.enviar"))->toBe($lista['publicos'] !== null);
    }
});

it('o vídeo arquivado guarda a capa, sai do site e aparece na aba de arquivados', function () {
    Storage::disk('public')->put('videos/capas/youtube-abc.jpg', 'capa');
    $video = videoDaOficina(['titulo' => 'Embreagem nova', 'capa_path' => 'videos/capas/youtube-abc.jpg']);
    $this->get('/galeria')->assertSee('Embreagem nova');

    $this->actingAs($this->beto)->delete(route('painel.videos.destroy', $video))->assertRedirect();

    Storage::disk('public')->assertExists('videos/capas/youtube-abc.jpg');
    expect(Video::onlyTrashed()->count())->toBe(1);

    $this->actingAs($this->beto)->get(route('painel.videos.index', ['arquivados' => 1]))
        ->assertInertia(fn (AssertableInertia $p) => $p
            ->where('arquivados', true)
            ->where('totais.arquivados', 1)
            ->where('videos.data.0.arquivado', true));

    auth()->logout();
    $this->get('/galeria')->assertDontSee('Embreagem nova');
});

it('em lote nos vídeos: tira do site, publica, arquiva e restaura', function () {
    $ids = [(string) videoDaOficina()->uuid, (string) videoDaOficina(['codigo' => 'aaaaaaaaaaa'])->uuid];
    $lote = fn (string $acao) => $this->actingAs($this->beto)
        ->post(route('painel.videos.lote'), ['acao' => $acao, 'ids' => $ids])
        ->assertRedirect()->assertSessionHasNoErrors();

    $lote('desligar');
    expect(Video::where('publicado', false)->count())->toBe(2);

    $lote('ligar');
    expect(Video::where('publicado', true)->count())->toBe(2);

    $lote('arquivar')->assertSessionHas('sucesso', '2 vídeos arquivados.');
    expect(Video::count())->toBe(0)->and(Video::onlyTrashed()->count())->toBe(2);

    $lote('restaurar')->assertSessionHas('sucesso', '2 vídeos restaurados.');
    expect(Video::count())->toBe(2);
});

it('não existe ação de excluir em lote', function () {
    $video = videoDaOficina();

    $this->actingAs($this->beto)
        ->post(route('painel.videos.lote'), ['acao' => 'excluir', 'ids' => [(string) $video->uuid]])
        ->assertSessionHasErrors('acao');

    expect(Video::count())->toBe(1);
});

it('quem não gerencia não roda ação em lote, não imprime e não envia', function () {
    $video = videoDaOficina();
    $ids = [(string) $video->uuid];
    Mail::fake();

    $this->actingAs($this->semPerfil)->post(route('painel.videos.lote'), ['acao' => 'arquivar', 'ids' => $ids])->assertForbidden();
    $this->actingAs($this->semPerfil)->get(route('painel.videos.imprimir', ['ids' => $ids]))->assertForbidden();
    $this->actingAs($this->semPerfil)->post(route('painel.videos.lote.enviar'), ['ids' => $ids, 'email' => 'x@teste.local'])->assertForbidden();

    $this->actingAs($this->beto)
        ->post(route('painel.usuarios.lote'), ['acao' => 'desligar', 'ids' => [(string) $this->semPerfil->uuid]])
        ->assertForbidden();

    expect(Video::count())->toBe(1)->and($this->semPerfil->fresh()->ativo)->toBeTrue();
    Mail::assertNothingSent();
});

it('a folha de impressão traz só os vídeos marcados', function () {
    $marcado = videoDaOficina(['titulo' => 'Entra na folha']);
    videoDaOficina(['titulo' => 'Fica de fora', 'codigo' => 'bbbbbbbbbbb']);

    $this->actingAs($this->beto)
        ->get(route('painel.videos.imprimir', ['ids' => [(string) $marcado->uuid]]))
        ->assertOk()
        ->assertSee('Entra na folha')
        ->assertSee('Publicado')
        ->assertDontSee('Fica de fora');
});

it('o e-mail leva só o vídeo que está publicado no site', function () {
    Mail::fake();

    $noAr = videoDaOficina(['titulo' => 'Freio revisado']);
    $guardado = videoDaOficina(['titulo' => 'Ainda não', 'codigo' => 'ccccccccccc', 'publicado' => false]);
    $arquivado = videoDaOficina(['titulo' => 'Saiu', 'codigo' => 'ddddddddddd']);
    $arquivado->delete();

    $this->actingAs($this->beto)->post(route('painel.videos.lote.enviar'), [
        'ids' => [(string) $noAr->uuid, (string) $guardado->uuid, (string) $arquivado->uuid],
        'email' => 'cliente@teste.local',
    ])->assertRedirect()->assertSessionHasNoErrors();

    Mail::assertSent(ListaDoPainel::class, fn (ListaDoPainel $m) => $m->hasTo('cliente@teste.local')
        && $m->hasReplyTo('beto@teste.local')
        && array_column($m->itens, 'titulo') === ['Freio revisado']);
});

it('marcar só vídeo fora do site não envia e-mail nenhum', function () {
    Mail::fake();
    $guardado = videoDaOficina(['publicado' => false]);

    $this->actingAs($this->beto)
        ->post(route('painel.videos.lote.enviar'), ['ids' => [(string) $guardado->uuid], 'email' => 'cliente@teste.local'])
        ->assertSessionHasErrors('ids');

    Mail::assertNothingSent();
});

it('o e-mail renderiza com o link de cada item', function () {
    $html = (new ListaDoPainel(
        ['vídeo', 'vídeos', 'o'],
        [['titulo' => 'Freio revisado', 'detalhe' => 'Discos e pastilhas.', 'link' => 'https://exemplo.test/v']],
        $this->beto,
        'Segue.',
    ))->render();

    expect($html)->toContain('https://exemplo.test/v')->toContain('Freio revisado')->toContain('Segue.');
});

it('em lote nas contas: desativa, ativa, arquiva e restaura', function () {
    $ids = [(string) $this->beto->uuid, (string) $this->semPerfil->uuid];
    $lote = fn (string $acao) => $this->actingAs($this->admin)
        ->post(route('painel.usuarios.lote'), ['acao' => $acao, 'ids' => $ids])
        ->assertRedirect()->assertSessionHasNoErrors();

    $lote('desligar');
    expect(User::where('ativo', false)->count())->toBe(2);

    $lote('ligar');
    expect(User::where('ativo', false)->count())->toBe(0);

    $lote('arquivar')->assertSessionHas('sucesso', '2 contas arquivadas.');
    expect(User::count())->toBe(1)->and(User::onlyTrashed()->count())->toBe(2);

    $this->actingAs($this->admin)->get(route('painel.usuarios.index', ['arquivadas' => 1]))
        ->assertInertia(fn (AssertableInertia $p) => $p->where('arquivadas', true)->has('usuarios', 2)->where('totais.arquivadas', 2));

    $lote('restaurar')->assertSessionHas('sucesso', '2 contas restauradas.');
    expect(User::count())->toBe(3);
});

it('e-mail de conta arquivada recusa a conta nova com mensagem, não com erro 500', function () {
    $arquivada = User::create([
        'name' => 'Antiga', 'email' => 'antiga@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);
    $arquivada->delete();

    $dados = [
        'name' => 'Nova', 'email' => 'antiga@teste.local', 'perfis' => [Perfis::ADMINISTRADOR], 'ativo' => true,
        'password' => 'Senha-Longa-De-Teste-9731', 'password_confirmation' => 'Senha-Longa-De-Teste-9731',
    ];

    $this->actingAs($this->admin)->post(route('painel.usuarios.store'), $dados)
        ->assertSessionHasErrors(['email' => 'Já existe uma conta arquivada com este e-mail. Restaure-a na aba de arquivadas em vez de criar outra.']);

    $this->actingAs($this->admin)
        ->put(route('painel.usuarios.update', $this->semPerfil), [...$dados, 'password' => null, 'password_confirmation' => null])
        ->assertSessionHasErrors('email');

    expect(User::withTrashed()->where('email', 'antiga@teste.local')->count())->toBe(1);
});

it('ninguém desativa nem arquiva a própria conta em lote', function () {
    $ids = [(string) $this->admin->uuid, (string) $this->beto->uuid];

    $this->actingAs($this->admin)->post(route('painel.usuarios.lote'), ['acao' => 'desligar', 'ids' => $ids]);
    $this->actingAs($this->admin)->post(route('painel.usuarios.lote'), ['acao' => 'arquivar', 'ids' => $ids])
        ->assertSessionHas('sucesso', '1 conta arquivada. A sua própria conta ficou como estava.');

    expect($this->admin->fresh()->ativo)->toBeTrue()
        ->and($this->admin->fresh()->trashed())->toBeFalse()
        ->and($this->beto->fresh()->trashed())->toBeTrue();
});

it('contas de acesso não têm rota de envio por e-mail', function () {
    expect(Route::has('painel.usuarios.lote.enviar'))->toBeFalse();

    $this->actingAs($this->admin)
        ->get(route('painel.usuarios.imprimir', ['ids' => [(string) $this->beto->uuid]]))
        ->assertOk()->assertSee('beto@teste.local');
});
