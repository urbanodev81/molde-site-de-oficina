<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Fotos selecionadas · {{ config('app.name') }}</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 24px; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #111; background: #fff; }
        header { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 16px; }
        h1 { font-size: 18px; margin: 0; }
        header p { margin: 0; font-size: 12px; color: #444; }
        .grade { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        figure { margin: 0; border: 1px solid #bbb; break-inside: avoid; }
        img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
        figcaption { padding: 8px; font-size: 11px; line-height: 1.4; }
        figcaption strong { display: block; font-size: 12px; }
        .vazio { font-size: 14px; }
        button { min-height: 44px; padding: 0 16px; font: inherit; cursor: pointer; }
        @media print { .so-tela { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} · fotos selecionadas</h1>
        <p>{{ $fotos->count() }} {{ $fotos->count() === 1 ? 'foto' : 'fotos' }} · {{ now()->format('d/m/Y H:i') }}</p>
    </header>

    @if ($fotos->isEmpty())
        <p class="vazio">Nenhuma foto selecionada. Volte às fotos do painel, marque as fotos e escolha "Imprimir".</p>
    @else
        <p class="so-tela"><button type="button" onclick="window.print()">Imprimir</button></p>
        <div class="grade">
            @foreach ($fotos as $foto)
                <figure>
                    <img src="{{ $foto['url'] }}" alt="{{ $foto['titulo'] }}">
                    <figcaption>
                        <strong>{{ $foto['titulo'] }}</strong>
                        @if ($foto['descricao']){{ $foto['descricao'] }}<br>@endif
                        @if ($foto['quando'])Enviada em {{ $foto['quando'] }}<br>@endif
                        {{ $foto['situacao'] }}
                    </figcaption>
                </figure>
            @endforeach
        </div>
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
