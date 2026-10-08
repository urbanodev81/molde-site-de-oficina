<?php

declare(strict_types=1);

use App\Models\Foto;
use App\Models\User;
use App\Models\Video;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(PerfisESeguranca::class);
    Storage::fake('public');
});

function daOficina(): User
{
    $usuario = User::create([
        'name' => 'Beto', 'email' => 'beto@teste.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);
    $usuario->syncRoles([Perfis::OFICINA]);

    return $usuario;
}

function video(array $dados = []): Video
{
    return Video::create(['titulo' => 'Correia dentada', 'provedor' => 'youtube', 'codigo' => 'dQw4w9WgXcQ', ...$dados]);
}

function foto(array $dados = []): Foto
{
    return Foto::create(['titulo' => 'Pastilhas novas', 'arquivo_path' => 'fotos/a.jpg', ...$dados]);
}

it('mostra na galeria só o que está publicado', function () {
    video(['titulo' => 'Vídeo no ar']);
    video(['titulo' => 'Vídeo guardado', 'codigo' => 'M7lc1UVf-VE', 'publicado' => false]);
    foto(['titulo' => 'Foto no ar', 'descricao' => 'Discos e pastilhas dianteiros.']);
    foto(['titulo' => 'Foto guardada', 'arquivo_path' => 'fotos/b.jpg', 'publicada' => false]);

    $this->get('/galeria')->assertOk()
        ->assertSee('Vídeo no ar')->assertSee('Foto no ar')->assertSee('Discos e pastilhas dianteiros.')
        ->assertDontSee('Vídeo guardado')->assertDontSee('Foto guardada');
});

it('entrega o vídeo como link comum, com o endereço do player no atributo', function () {
    video();

    $this->get('/galeria')->assertOk()
        ->assertSee('href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"', false)
        ->assertSee('data-embed="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1&amp;rel=0"', false)

        ->assertDontSee('<iframe', false);
});

it('escapa o que a pessoa digitou no título e na descrição', function () {
    video(['titulo' => '<script>alert(1)</script>', 'descricao' => '"><img src=x onerror=alert(1)>']);

    $this->get('/galeria')->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x', false);
});

it('põe na home só os vídeos marcados, e no máximo dois', function () {
    video(['titulo' => 'Primeiro', 'na_home' => true, 'ordem' => 1]);
    video(['titulo' => 'Segundo', 'codigo' => 'M7lc1UVf-VE', 'na_home' => true, 'ordem' => 2]);
    video(['titulo' => 'Terceiro', 'codigo' => 'AAAAAAAAAAA', 'na_home' => true, 'ordem' => 3]);
    video(['titulo' => 'Fora da home', 'codigo' => 'BBBBBBBBBBB']);

    $this->get('/')->assertOk()
        ->assertSee('Primeiro')->assertSee('Segundo')
        ->assertDontSee('Terceiro')->assertDontSee('Fora da home');
});

it('esconde a seção de galeria da home quando não há vídeo marcado', function () {
    video();

    $this->get('/')->assertOk()->assertDontSee('id="galeria-titulo"', false);
});

it('mantém a galeria fora do buscador enquanto houver conteúdo de exemplo ou nada', function () {
    $this->get('/galeria')->assertOk()->assertSee('noindex,follow', false);

    $real = video();
    $this->get('/galeria')->assertSee('index,follow,max-image-preview:large', false)->assertDontSee('noindex', false);

    foto(['exemplo' => true]);
    $this->get('/galeria')->assertSee('noindex,follow', false)->assertSee('conteúdo de exemplo');

    expect($real->exemplo)->toBeFalse();
});

it('leva o endereço antigo da galeria para o novo', function () {
    $this->get('/galeria.html')->assertStatus(301)->assertRedirect('/galeria');
});

it('guarda a foto enviada, com as medidas', function () {
    $this->actingAs(daOficina())->post('/painel/fotos', [
        'arquivo' => UploadedFile::fake()->image('freio.jpg', 1200, 900),
        'titulo' => 'Troca de pastilhas',
        'descricao' => 'Dianteiras, com sangria.',
        'publicada' => true,
    ])->assertSessionHasNoErrors();

    $foto = Foto::sole();

    expect($foto->titulo)->toBe('Troca de pastilhas')
        ->and($foto->largura)->toBe(1200)->and($foto->altura)->toBe(900)
        ->and($foto->exemplo)->toBeFalse();
    Storage::disk('public')->assertExists($foto->arquivo_path);
});

it('recusa SVG e arquivo que não é imagem', function (string $nome, string $mime) {
    $this->actingAs(daOficina())->post('/painel/fotos', [
        'arquivo' => UploadedFile::fake()->create($nome, 10, $mime),
        'titulo' => 'Tentativa',
    ])->assertSessionHasErrors('arquivo');

    expect(Foto::count())->toBe(0);
})->with([
    ['desenho.svg', 'image/svg+xml'],
    ['pagina.html', 'text/html'],
    ['script.php', 'application/x-php'],
]);

it('arquiva a foto e guarda o arquivo, em vez de apagar', function () {
    $beto = daOficina();

    $this->actingAs($beto)->post('/painel/fotos', [
        'arquivo' => UploadedFile::fake()->image('a.jpg'), 'titulo' => 'Vai sair',
    ]);
    $foto = Foto::sole();

    $this->actingAs($beto)->delete("/painel/fotos/{$foto->uuid}")->assertSessionHasNoErrors();

    expect(Foto::count())->toBe(0)->and(Foto::onlyTrashed()->count())->toBe(1);
    Storage::disk('public')->assertExists($foto->arquivo_path);
});

it('inclui vídeo pelo código de incorporação e traz a capa para o disco', function () {
    Http::fake(['i.ytimg.com/*' => Http::response('jpeg-de-mentira', 200, ['Content-Type' => 'image/jpeg'])]);

    $this->actingAs(daOficina())->post('/painel/videos', [
        'link' => '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" allowfullscreen></iframe>',
        'titulo' => 'Correia dentada',
        'na_home' => true,
    ])->assertSessionHasNoErrors();

    $video = Video::sole();

    expect($video->provedor)->toBe('youtube')->and($video->codigo)->toBe('dQw4w9WgXcQ')
        ->and($video->na_home)->toBeTrue()
        ->and($video->capa_path)->toBe('videos/capas/youtube-dQw4w9WgXcQ.jpg');
    Storage::disk('public')->assertExists($video->capa_path);
});

it('inclui o vídeo mesmo quando a capa não vem', function () {
    Http::fake(['*' => Http::response('', 404)]);

    $this->actingAs(daOficina())->post('/painel/videos', [
        'link' => 'https://youtu.be/dQw4w9WgXcQ', 'titulo' => 'Sem capa',
    ])->assertSessionHasNoErrors();

    expect(Video::sole()->capa_path)->toBeNull()
        ->and(Video::sole()->capa())->toBe(Video::CAPA_PADRAO);
});

it('recusa link que não é de provedor aceito', function () {
    Http::fake();

    $this->actingAs(daOficina())->post('/painel/videos', [
        'link' => '<iframe src="https://malicioso.example/embed/1"></iframe>', 'titulo' => 'Não entra',
    ])->assertSessionHasErrors('link');

    expect(Video::count())->toBe(0);
    Http::assertNothingSent();
});

it('mantém o vídeo quando a edição vem sem link', function () {
    $video = video();

    $this->actingAs(daOficina())->put("/painel/videos/{$video->uuid}", [
        'link' => '', 'titulo' => 'Título novo', 'publicado' => false,
    ])->assertSessionHasNoErrors();

    expect($video->fresh())->titulo->toBe('Título novo')->codigo->toBe('dQw4w9WgXcQ')->publicado->toBeFalse();
});
