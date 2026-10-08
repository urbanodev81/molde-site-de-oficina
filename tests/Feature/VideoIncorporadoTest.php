<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityHeaders;
use App\Support\VideoIncorporado;

it('reconhece link e código de incorporação', function (string $entrada, string $provedor, string $codigo) {
    expect(VideoIncorporado::reconhecer($entrada))->toBe(['provedor' => $provedor, 'codigo' => $codigo]);
})->with([
    'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ'],
    'youtube curto' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'youtube', 'dQw4w9WgXcQ'],
    'youtube shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ'],
    'youtube iframe' => [
        '<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ?si=x&amp;start=3" title="YouTube video player" allowfullscreen></iframe>',
        'youtube', 'dQw4w9WgXcQ',
    ],
    'youtube nocookie' => ['<iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"></iframe>', 'youtube', 'dQw4w9WgXcQ'],
    'vimeo' => ['https://vimeo.com/123456789', 'vimeo', '123456789'],
    'vimeo iframe' => ['<iframe src="https://player.vimeo.com/video/123456789?h=abc"></iframe>', 'vimeo', '123456789'],
    'instagram reel' => ['https://www.instagram.com/reel/Cxyz_123-AB/?igsh=1', 'instagram', 'reel/Cxyz_123-AB'],
    'tiktok' => ['https://www.tiktok.com/@oficina/video/7300000000000000000', 'tiktok', '7300000000000000000'],
]);

it('recusa o que não é vídeo de provedor conhecido', function (string $entrada) {
    expect(VideoIncorporado::reconhecer($entrada))->toBeNull();
})->with([
    'vazio' => [''],
    'texto solto' => ['dQw4w9WgXcQ'],
    'outro site' => ['https://exemplo.com/video/123'],
    'iframe de outro site' => ['<iframe src="https://malicioso.example/embed/1"></iframe>'],
    'esquema javascript' => ['javascript:alert(1)'],
    'domínio parecido' => ['https://youtube.com.malicioso.example/watch?v=dQw4w9WgXcQ'],
    'iframe sem src' => ['<iframe></iframe>'],
]);

it('monta o iframe só em domínio que a CSP do site libera', function (string $provedor, string $codigo) {
    $embed = VideoIncorporado::embed($provedor, $codigo);
    $origem = parse_url($embed, PHP_URL_SCHEME).'://'.parse_url($embed, PHP_URL_HOST);

    expect(VideoIncorporado::DOMINIOS_DE_INCORPORACAO)->toContain($origem);
})->with([
    ['youtube', 'dQw4w9WgXcQ'],
    ['vimeo', '123456789'],
    ['instagram', 'reel/Cxyz'],
    ['tiktok', '7300000000000000000'],
]);

it('tem um endereço de incorporação para todo provedor aceito', function () {
    foreach (array_keys(VideoIncorporado::PROVEDORES) as $provedor) {
        expect(VideoIncorporado::embed($provedor, '123456789'))->not->toBeNull();
    }
});

it('mantém o hash da CSP igual ao script embutido do site', function () {
    $html = $this->get('/')->assertOk()->getContent();

    preg_match_all('#<script>(.*?)</script>#s', $html, $achados);

    expect($achados[1])->toHaveCount(1);
    expect('sha256-'.base64_encode(hash('sha256', $achados[1][0], true)))->toBe(SecurityHeaders::HASH_DO_SCRIPT_DO_SITE);
});

it('serve o site com a CSP estrita, sem unsafe-inline nem unsafe-eval em script', function (string $rota) {
    $csp = $this->get($rota)->assertOk()->headers->get('Content-Security-Policy');

    preg_match('#script-src ([^;]+)#', $csp, $m);

    expect($m[1])->not->toContain('unsafe-inline')->not->toContain('unsafe-eval')
        ->and($csp)->toContain("frame-ancestors 'none'")
        ->and($csp)->toContain('https://www.youtube-nocookie.com')

        ->and($m[1])->toContain('https://vlibras.gov.br')->toContain('https://cdn.jsdelivr.net');
})->with(['/', '/galeria']);
