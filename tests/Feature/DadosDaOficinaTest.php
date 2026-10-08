<?php

declare(strict_types=1);

use App\Models\Oficina;
use App\Models\User;
use App\Models\Video;
use App\Support\MapaIncorporado;
use App\Support\Perfis;
use Database\Seeders\PerfisESeguranca;

beforeEach(function () {
    $this->seed(PerfisESeguranca::class);
    Oficina::esquecer();
});

function contaDaOficina(string $perfil = Perfis::OFICINA): User
{
    $usuario = User::create([
        'name' => 'Beto', 'email' => $perfil.'@oficina.local', 'password' => 'senha-de-teste-123',
        'ativo' => true, 'email_verified_at' => now(),
    ]);
    $usuario->syncRoles([$perfil]);

    return $usuario;
}

function dadosDaOficina(array $extra = []): array
{
    $semana = [];
    foreach ([1, 2, 3, 4, 5, 6, 0] as $dia) {
        $semana[] = ['dia' => $dia, 'aberto' => $dia !== 0, 'abre' => '07:30', 'fecha' => $dia === 6 ? '13:00' : '17:30'];
    }

    return array_merge([
        'whatsapp' => '(11) 98888-7777', 'telefone' => '(11) 2909-1234', 'email' => 'beto@oficina.com.br',
        'instagram' => '@amelhor.oficina', 'logradouro' => 'Rua das Oficinas, 123', 'bairro' => 'Centro',
        'cidade' => 'São Paulo', 'uf' => 'sp', 'cep' => '01000000', 'latitude' => '-23.51', 'longitude' => '-46.58',
        'mapa' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3658" width="600" height="450"></iframe>',
        'horario_informado' => true, 'semana' => $semana,
    ], $extra);
}

it('sem cadastro, o site mostra o WhatsApp confirmado e marca endereço, horário, instagram e e-mail como exemplo', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('https://wa.me/5511900000000')
        ->toContain('WhatsApp (11) 90000-0000')
        ->toContain('Rua Exemplo, 000 — Bairro')
        ->toContain('00000-000 <i class="marcador">exemplo</i>')
        ->toContain('Horário <i class="marcador">exemplo</i>')
        ->toContain('<dt>Segunda a sexta</dt><dd>08h às 18h</dd>')
        ->toContain('<dt>Domingo e feriado</dt><dd>Fechado</dd>')
        ->not->toContain('onde__mapa');
});

it('o que o painel grava aparece em todos os lugares do site, sem marcador', function () {
    $this->actingAs(contaDaOficina())->put('/painel/oficina', dadosDaOficina())->assertSessionHasNoErrors()->assertRedirect();

    $oficina = Oficina::query()->firstOrFail();
    expect($oficina->whatsapp)->toBe('5511988887777')
        ->and($oficina->instagram)->toBe('amelhor.oficina')
        ->and($oficina->uf)->toBe('SP')
        ->and($oficina->cep)->toBe('01000-000')
        ->and($oficina->mapa_embed)->toBe('https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3658')
        ->and($oficina->temExemplo())->toBeFalse();

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('https://wa.me/5511988887777')
        ->not->toContain('5511900000000')
        ->toContain('WhatsApp (11) 98888-7777')
        ->toContain('href="tel:+551129091234"')
        ->toContain('Rua das Oficinas, 123 — Centro')
        ->toContain('São Paulo · SP · 01000-000')
        ->not->toContain('Rua Exemplo')
        ->toContain('<dt>Segunda a sexta</dt><dd>07h30 às 17h30</dd>')
        ->toContain('<dt>Sábado</dt><dd>07h30 às 13h</dd>')
        ->toContain('@amelhor.oficina')
        ->toContain('mailto:beto@oficina.com.br')
        ->toContain('<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3658"')
        ->toContain('content="-23.51;-46.58"');

    expect($html)->not->toContain('Horário <i class="marcador">exemplo</i>')
        ->not->toContain('01000-000 <i class="marcador">');

    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $achado);
    $oficinaLd = json_decode($achado[1], true, flags: JSON_THROW_ON_ERROR)['@graph'][0];

    expect($oficinaLd['telephone'])->toBe('+55-11-2909-1234')
        ->and($oficinaLd['address']['streetAddress'])->toBe('Rua das Oficinas, 123')
        ->and($oficinaLd['address']['postalCode'])->toBe('01000-000')
        ->and($oficinaLd['openingHoursSpecification'][0]['dayOfWeek'])->toBe(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
        ->and($oficinaLd['openingHoursSpecification'][0]['opens'])->toBe('07:30')
        ->and($oficinaLd['sameAs'])->toBe(['https://www.instagram.com/amelhor.oficina/'])
        ->and($oficinaLd['priceRange'])->toBe('$$');
});

it('entrega os mesmos dados ao javascript do site e ao llms.txt', function () {
    $this->actingAs(contaDaOficina())->put('/painel/oficina', dadosDaOficina())->assertSessionHasNoErrors();

    $js = $this->get('/dados-da-oficina.js')->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=utf-8')->getContent();

    expect($js)->toStartWith('window.OFICINA = Object.assign(window.OFICINA || {}, {')
        ->toContain('"whatsapp":"5511988887777"')
        ->toContain('"telefone":"+551129091234"')
        ->toContain('{"dias":[1,2,3,4,5],"abre":"07:30","fecha":"17:30"}')
        ->toContain('{"dias":[6],"abre":"07:30","fecha":"13:00"}');

    $llms = $this->get('/llms.txt')->assertOk()->getContent();

    expect($llms)->toContain('- WhatsApp: (11) 98888-7777')
        ->toContain('- Endereço: Rua das Oficinas, 123 — Centro, São Paulo, SP')
        ->toContain('segunda a sexta, 07h30 às 17h30; sábado, 07h30 às 13h; domingo e feriado, fechado.')
        ->not->toContain('EXEMPLO');
});

it('sem cadastro, o llms.txt avisa que endereço e horário são de exemplo', function () {
    expect($this->get('/llms.txt')->assertOk()->getContent())
        ->toContain('Endereço e Horário acima ainda são de EXEMPLO')
        ->not->toContain('E-mail:');
});

it('guarda só o endereço do mapa, e só se for do Google Maps', function (string $colado, ?string $esperado) {
    expect(MapaIncorporado::reconhecer($colado))->toBe($esperado);
})->with([
    'iframe do Google' => ['<iframe src="https://www.google.com/maps/embed?pb=!1m18&amp;x=1" style="border:0"></iframe>', 'https://www.google.com/maps/embed?pb=!1m18&x=1'],
    'só o endereço' => ['https://www.google.com/maps/embed?pb=abc', 'https://www.google.com/maps/embed?pb=abc'],
    'outro site' => ['<iframe src="https://exemplo.com/maps/embed?pb=abc"></iframe>', null],
    'link de compartilhar, que não incorpora' => ['https://maps.app.goo.gl/abc', null],
    'domínio parecido' => ['https://www.google.com.exemplo.com/maps/embed?pb=1', null],
    'tentativa de sair do atributo' => ['https://www.google.com/maps/embed?pb=1" onload="alert(1)', null],
    'javascript' => ['<iframe src="javascript:alert(1)"></iframe>', null],
]);

it('recusa mapa que não é do Google e horário com a hora de fechar antes da de abrir', function () {
    $beto = contaDaOficina();

    $this->actingAs($beto)->put('/painel/oficina', dadosDaOficina(['mapa' => '<iframe src="https://exemplo.com/x"></iframe>']))
        ->assertSessionHasErrors('mapa');

    $dados = dadosDaOficina();
    $dados['semana'][0]['fecha'] = '06:00';
    $this->actingAs($beto)->put('/painel/oficina', $dados)->assertSessionHasErrors('semana.0.abre');

    $this->actingAs($beto)->put('/painel/oficina', dadosDaOficina(['whatsapp' => '1234']))->assertSessionHasErrors('whatsapp');

    expect(Oficina::query()->count())->toBe(0);
});

it('campo apagado volta a mostrar o exemplo com o marcador, e o mapa some', function () {
    $beto = contaDaOficina();
    $this->actingAs($beto)->put('/painel/oficina', dadosDaOficina())->assertSessionHasNoErrors();
    $this->actingAs($beto)->put('/painel/oficina', dadosDaOficina(['cep' => '', 'mapa' => '', 'horario_informado' => false]))->assertSessionHasNoErrors();

    $html = $this->get('/')->getContent();

    expect($html)->toContain('00000-000 <i class="marcador">exemplo</i>')
        ->toContain('Horário <i class="marcador">exemplo</i>')
        ->not->toContain('onde__mapa');
});

it('a CSP do site libera o mapa do Google, os players de vídeo e o avatar do VLibras, e nada mais em frame-src', function () {
    $csp = $this->get('/')->headers->get('Content-Security-Policy');

    expect($csp)->toContain('frame-src https://www.youtube-nocookie.com https://player.vimeo.com https://www.instagram.com https://www.tiktok.com https://www.google.com https://vlibras.gov.br;');
});

it('a oficina abre e grava os dados; conta sem papel não', function () {
    $this->get('/painel/oficina')->assertRedirect(route('login'));
    $this->actingAs(contaDaOficina())->get('/painel/oficina')->assertOk()
        ->assertInertia(fn ($p) => $p->component('Painel/Oficina/Editar')->has('semana', 7)->where('podeGerenciar', true)->where('exemplos.endereco', true));

    $semPapel = User::create(['name' => 'Sem papel', 'email' => 'sem@oficina.local', 'password' => 'senha-de-teste-123', 'ativo' => true, 'email_verified_at' => now()]);

    $this->actingAs($semPapel)->get('/painel/oficina')->assertForbidden();
    $this->actingAs($semPapel)->put('/painel/oficina', dadosDaOficina())->assertForbidden();
    expect(Oficina::query()->count())->toBe(0);
});

it('cada vídeo da galeria leva o emblema do canal de onde vem', function () {
    foreach (['youtube' => 'dQw4w9WgXcQ', 'vimeo' => '76979871', 'instagram' => 'reel/Cabc123', 'tiktok' => '7301234567890123456'] as $provedor => $codigo) {
        Video::create(['titulo' => "Vídeo {$provedor}", 'provedor' => $provedor, 'codigo' => $codigo, 'publicado' => true]);
    }

    $html = $this->get('/galeria')->assertOk()->getContent();

    expect($html)->toContain('midia__canal--youtube')->toContain('#i-youtube')
        ->toContain('midia__canal--vimeo')->toContain('#i-vimeo')
        ->toContain('midia__canal--instagram')->toContain('#i-insta')
        ->toContain('midia__canal--tiktok')->toContain('#i-tiktok')
        ->toContain('Assistir no TikTok: Vídeo tiktok');
});
