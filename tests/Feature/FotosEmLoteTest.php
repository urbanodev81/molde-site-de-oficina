<?php

declare(strict_types=1);

use App\Mail\ListaDeFotos;
use App\Models\Foto;
use App\Models\User;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(PerfisESeguranca::class);

    $this->beto = User::create([
        'name' => 'Beto', 'email' => 'beto@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);
    $this->beto->syncRoles([Perfis::OFICINA]);

    $this->semPerfil = User::create([
        'name' => 'Sem perfil', 'email' => 'semperfil@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);
});

function fotoNoDisco(array $atributos = []): Foto
{
    $caminho = 'fotos/'.uniqid('', true).'.jpg';
    Storage::disk('public')->put($caminho, 'imagem');

    return Foto::create(['titulo' => 'Pastilhas novas', 'arquivo_path' => $caminho, ...$atributos]);
}

it('a foto arquivada guarda o arquivo, sai da lista e aparece na aba de arquivadas', function () {
    $foto = fotoNoDisco(['titulo' => 'Correia trocada']);

    $this->actingAs($this->beto)->delete(route('painel.fotos.destroy', $foto))->assertRedirect();

    Storage::disk('public')->assertExists($foto->arquivo_path);

    $this->actingAs($this->beto)->get(route('painel.fotos.index'))
        ->assertInertia(fn (AssertableInertia $p) => $p->where('fotos.data', [])->where('totais.arquivadas', 1));

    $this->actingAs($this->beto)->get(route('painel.fotos.index', ['arquivadas' => 1]))
        ->assertInertia(fn (AssertableInertia $p) => $p
            ->where('arquivadas', true)
            ->where('fotos.data.0.uuid', (string) $foto->uuid)
            ->where('fotos.data.0.arquivada', true));
});

it('a foto arquivada sai da galeria do site', function () {
    $foto = fotoNoDisco(['titulo' => 'Embreagem nova']);
    $this->get('/galeria')->assertSee('Embreagem nova');

    $this->actingAs($this->beto)->delete(route('painel.fotos.destroy', $foto));
    auth()->logout();

    $this->get('/galeria')->assertDontSee('Embreagem nova');
});

it('em lote: tira do site, publica, arquiva e restaura — e nada some do disco', function () {
    $a = fotoNoDisco();
    $b = fotoNoDisco();
    $ids = [(string) $a->uuid, (string) $b->uuid];
    $lote = fn (string $acao) => $this->actingAs($this->beto)
        ->post(route('painel.fotos.lote'), ['acao' => $acao, 'ids' => $ids])
        ->assertRedirect()->assertSessionHasNoErrors();

    $lote('despublicar');
    expect(Foto::where('publicada', false)->count())->toBe(2);

    $lote('publicar');
    expect(Foto::where('publicada', true)->count())->toBe(2);

    $lote('arquivar')->assertSessionHas('sucesso', '2 fotos arquivadas.');
    expect(Foto::count())->toBe(0)->and(Foto::onlyTrashed()->count())->toBe(2);
    Storage::disk('public')->assertExists($a->arquivo_path);

    $lote('restaurar')->assertSessionHas('sucesso', '2 fotos restauradas.');
    expect(Foto::count())->toBe(2);
});

it('não existe ação de excluir em lote', function () {
    $foto = fotoNoDisco();

    $this->actingAs($this->beto)
        ->post(route('painel.fotos.lote'), ['acao' => 'excluir', 'ids' => [(string) $foto->uuid]])
        ->assertSessionHasErrors('acao');

    expect(Foto::count())->toBe(1);
});

it('quem não gerencia a galeria não roda ação em lote nem envia lista', function () {
    $foto = fotoNoDisco();
    Mail::fake();

    $this->actingAs($this->semPerfil)
        ->post(route('painel.fotos.lote'), ['acao' => 'arquivar', 'ids' => [(string) $foto->uuid]])
        ->assertForbidden();

    $this->actingAs($this->semPerfil)
        ->post(route('painel.fotos.lote.enviar'), ['ids' => [(string) $foto->uuid], 'email' => 'x@teste.local'])
        ->assertForbidden();

    expect(Foto::count())->toBe(1);
    Mail::assertNothingSent();
});

it('a folha de impressão traz só as fotos marcadas', function () {
    $marcada = fotoNoDisco(['titulo' => 'Entra na folha']);
    fotoNoDisco(['titulo' => 'Fica de fora']);

    $this->actingAs($this->beto)
        ->get(route('painel.fotos.imprimir', ['ids' => [(string) $marcada->uuid]]))
        ->assertOk()
        ->assertSee('Entra na folha')
        ->assertDontSee('Fica de fora');
});

it('o e-mail leva só o que está publicado no site', function () {
    Mail::fake();

    $noAr = fotoNoDisco(['titulo' => 'Freio revisado']);
    $guardada = fotoNoDisco(['titulo' => 'Ainda não', 'publicada' => false]);

    $this->actingAs($this->beto)->post(route('painel.fotos.lote.enviar'), [
        'ids' => [(string) $noAr->uuid, (string) $guardada->uuid],
        'email' => 'cliente@teste.local',
        'mensagem' => 'Segue o serviço.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    Mail::assertSent(ListaDeFotos::class, fn (ListaDeFotos $m) => $m->hasTo('cliente@teste.local')
        && $m->hasReplyTo('beto@teste.local')
        && array_column($m->fotos, 'titulo') === ['Freio revisado']);
});

it('marcar só foto fora do site não envia e-mail nenhum', function () {
    Mail::fake();
    $guardada = fotoNoDisco(['publicada' => false]);

    $this->actingAs($this->beto)
        ->post(route('painel.fotos.lote.enviar'), ['ids' => [(string) $guardada->uuid], 'email' => 'cliente@teste.local'])
        ->assertSessionHasErrors('ids');

    Mail::assertNothingSent();
});

it('o e-mail renderiza com o link de cada foto', function () {
    $html = (new ListaDeFotos(
        [['titulo' => 'Freio revisado', 'descricao' => 'Discos e pastilhas.', 'url' => 'https://exemplo.test/f.jpg']],
        $this->beto,
        'Segue.',
    ))->render();

    expect($html)->toContain('https://exemplo.test/f.jpg')->toContain('Freio revisado')->toContain('Segue.');
});
