<?php

declare(strict_types=1);

it('entrega o tema claro numa folha à parte, e o botão nasce escondido', function (string $rota) {
    $html = $this->get($rota)->assertOk()->getContent();

    expect($html)
        ->toContain('/assets/css/tema-claro.css')
        ->toContain('/assets/js/tema.js')
        ->toMatch('/<button class="tema" id="tema" type="button" hidden aria-pressed="false" aria-label="Usar o tema claro">/')

        ->toMatch('/<html lang="pt-BR" class="no-js">/');
})->with(['/', '/galeria']);

it('o escuro é o padrão: nada no site lê a preferência de cor do sistema', function () {
    foreach (['assets/css/style.css', 'assets/css/tema-claro.css', 'assets/css/a11y.css', 'assets/js/tema.js', 'assets/js/app.js'] as $arquivo) {
        expect(file_get_contents(public_path($arquivo)))->not->toContain('prefers-color-scheme');
    }

    expect($this->get('/')->getContent())->not->toContain('prefers-color-scheme');
});

it('toda regra de cor do tema claro só vale com data-tema="claro"', function () {
    $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(public_path('assets/css/tema-claro.css')));

    preg_match_all('/(^|})\s*([^{}@]+)\{/m', (string) $css, $achados);

    $soltas = array_values(array_filter(
        array_map('trim', $achados[2]),

        fn (string $seletor) => ! str_contains($seletor, '[data-tema="claro"]') && ! str_starts_with($seletor, '.tema'),
    ));

    expect($soltas)->toBe([]);
});

it('o script do head e o tema.js falam da mesma chave', function () {
    $chave = 'amelhoroficina-tema';

    expect($this->get('/')->getContent())->toContain("localStorage.getItem('{$chave}')")
        ->and(file_get_contents(public_path('assets/js/tema.js')))->toContain("localStorage.setItem('{$chave}'");
});
