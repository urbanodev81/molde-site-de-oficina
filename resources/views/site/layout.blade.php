@php
    $naHome = ($pagina ?? 'home') === 'home';
    $raiz = $naHome ? '' : '/';
@endphp
<!doctype html>
<html lang="pt-BR" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $meta['titulo'] }}</title>
<meta name="description" content="{{ $meta['descricao'] }}">
<link rel="canonical" href="{{ $meta['canonical'] }}">
<meta name="robots" content="{{ $meta['robots'] }}">
<meta name="author" content="A melhor oficina">
<meta name="theme-color" content="#262626" id="cor-do-tema">
<meta name="color-scheme" content="dark light">

<meta name="geo.region" content="BR-SP">
<meta name="geo.placename" content="São Paulo, SP, Brasil">
<meta name="geo.position" content="{{ $oficina->valor('latitude') }};{{ $oficina->valor('longitude') }}">
<meta name="ICBM" content="{{ $oficina->valor('latitude') }}, {{ $oficina->valor('longitude') }}">

<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="A melhor oficina">
<meta property="og:title" content="{{ $meta['og_titulo'] }}">
<meta property="og:description" content="Revisão, freios, suspensão, motor e injeção eletrônica. Diagnóstico por scanner e orçamento aprovado antes de mexer no carro.">
<meta property="og:url" content="{{ $meta['canonical'] }}">
<meta property="og:image" content="https://amelhoroficina.example/assets/img/og.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Selo da oficina, especializados em carros">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="A melhor oficina — especializados em carros">
<meta name="twitter:description" content="Oficina mecânica em São Paulo. Orçamento pelo WhatsApp.">
<meta name="twitter:image" content="https://amelhoroficina.example/assets/img/og.jpg">

<link rel="icon" href="/assets/img/favicon-32.png" sizes="32x32">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@600;700;800;900&family=Rajdhani:wght@500;600;700&family=Barlow:wght@400;500;600&display=swap">
<link rel="stylesheet" href="/assets/css/style.css?v={{ @filemtime(public_path('assets/css/style.css')) }}">
<link rel="stylesheet" href="/assets/css/a11y.css?v=20261006">

<link rel="stylesheet" href="/assets/css/tema-claro.css?v={{ @filemtime(public_path('assets/css/tema-claro.css')) }}">

<script>(function(d){d.className=d.className.replace('no-js','js');try{if(localStorage.getItem('amelhoroficina-tema')==='claro'){d.setAttribute('data-tema','claro')}}catch(e){}})(document.documentElement);</script>

@stack('head')
</head>

<body>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs>
    <symbol id="i-revisao" viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2.8h6V4M8.5 11l2.2 2.2L15.5 8.5M8.5 17h7"/></symbol>
    <symbol id="i-freio" viewBox="0 0 24 24"><circle cx="11" cy="13" r="8"/><circle cx="11" cy="13" r="2.4"/><path d="M11 5v1.5M3 13h1.5M11 21v-1.5M18.5 4.8a10 10 0 0 1 2.7 6.2l-3.4.3a6.6 6.6 0 0 0-1.7-4.1z"/></symbol>
    <symbol id="i-suspensao" viewBox="0 0 24 24"><path d="M7 2.5h10M7 21.5h10M8 5.5l8 2-8 2 8 2-8 2 8 2-8 2 8 2"/></symbol>
    <symbol id="i-motor" viewBox="0 0 24 24"><path d="M3 10v6M3 13h2M5 9h2V7h6v2h2l2 2h2v-2h2v8h-2v-2h-2v3H9l-2-2H5z"/><path d="M9 7V5h4v2"/></symbol>
    <symbol id="i-injecao" viewBox="0 0 24 24"><path d="M13 2 4.5 13.5H11L10 22l8.5-11.5H12z"/></symbol>
    <symbol id="i-oleo" viewBox="0 0 24 24"><path d="M12 3s6 6.6 6 11a6 6 0 0 1-12 0c0-4.4 6-11 6-11z"/><path d="M9.2 14.5a2.8 2.8 0 0 0 2.8 2.8"/></symbol>
    <symbol id="i-cambio" viewBox="0 0 24 24"><circle cx="5" cy="5" r="2"/><circle cx="12" cy="5" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="12" cy="19" r="2"/><path d="M5 7v10M12 7v10M19 7v5H5"/></symbol>
    <symbol id="i-arrefecimento" viewBox="0 0 24 24"><path d="M10 13.5V4a2 2 0 1 1 4 0v9.5a4 4 0 1 1-4 0z"/><path d="M12 9v6.5M17.5 6h3M17.5 10h3"/></symbol>
    <symbol id="i-scanner" viewBox="0 0 24 24"><rect x="2.5" y="4" width="19" height="13" rx="2"/><path d="M5.5 11h3l1.5-3 2.5 6 1.5-3h4.5M8 21h8M12 17v4"/></symbol>
    <symbol id="i-zap" viewBox="0 0 24 24"><path d="M20.5 11.6a8.6 8.6 0 0 1-12.7 7.5L3.5 20.5l1.4-4.2a8.6 8.6 0 1 1 15.6-4.7z"/><path d="M9 8.3c0 3.4 2.9 6.6 6.4 6.8l1-1.7-2-1-1 .9c-1-.5-2-1.5-2.4-2.4l.9-1-1-2z"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21.5s-7-6.3-7-12a7 7 0 0 1 14 0c0 5.7-7 12-7 12z"/><circle cx="12" cy="9.5" r="2.5"/></symbol>
    <symbol id="i-relogio" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></symbol>
    <symbol id="i-insta" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".6"/></symbol>
    <symbol id="i-email" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6.5L20.5 7"/></symbol>
    <symbol id="i-youtube" viewBox="0 0 24 24"><rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9.2 5 2.8-5 2.8z"/></symbol>
    <symbol id="i-vimeo" viewBox="0 0 24 24"><path d="M3 8.5c2-1.8 3.4-2.9 4.4-2.4 2 1 1.600 7.400 3.400 10.400 1 1.700 2.500.5 4.800-2.500 2.400-3.200 4.600-7.800 2.700-9.200-1.500-1.100-3.800.2-4.500 2.700"/></symbol>
    <symbol id="i-tiktok" viewBox="0 0 24 24"><path d="M14 3.500v10.700a3.800 3.800 0 1 1-3.800-3.800"/><path d="M14 3.500c.3 2.600 2 4.300 4.700 4.600"/></symbol>
    <symbol id="i-tel" viewBox="0 0 24 24"><path d="M5 3.5h3.5l1.8 4.6-2.3 1.4a11 11 0 0 0 6.5 6.5l1.4-2.3 4.6 1.8V19a2 2 0 0 1-2 2A16.5 16.5 0 0 1 3 5.5a2 2 0 0 1 2-2z"/></symbol>
    <symbol id="i-seta" viewBox="0 0 24 24"><path d="M4 12h15M13 6l6 6-6 6"/></symbol>
    <symbol id="i-escudo" viewBox="0 0 24 24"><path d="M12 2.5 4 5.5v6c0 5 3.4 8.8 8 10 4.6-1.2 8-5 8-10v-6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></symbol>
    <symbol id="i-peca" viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 1-5.4 5.4L4 17a2.1 2.1 0 0 0 3 3l5.3-5.3a4 4 0 0 1 5.4-5.4l-2.6 2.6-2.1-.5-.5-2.1z"/></symbol>
    <symbol id="i-carro" viewBox="0 0 24 24"><path d="M3.5 16.5v-4l2-5a2 2 0 0 1 1.9-1.3h9.2a2 2 0 0 1 1.9 1.3l2 5v4z"/><path d="M3.5 12.5h17M3.5 16.5V19h3v-2.5M17.5 16.5V19h3v-2.5"/><circle cx="7.5" cy="14.5" r=".7"/><circle cx="16.5" cy="14.5" r=".7"/></symbol>
  </defs>
</svg>

<header class="topo" id="topo-barra">
  <a class="topo__marca" href="{{ $naHome ? '#topo' : '/' }}">
    <picture><img src="/assets/img/selo-96.webp" width="40" height="40" alt="" aria-hidden="true"></picture>
    <span>OFICINA <b>Mecânica</b></span>
  </a>
  <nav aria-label="Principal">
    <ul class="topo__menu">
      <li><a class="topo__link" href="{{ $raiz }}#servicos">Serviços</a></li>
      <li><a class="topo__link" href="{{ $raiz }}#como-funciona">Como funciona</a></li>
      <li><a class="topo__link" @if ($naHome) href="#galeria" @else href="/galeria" aria-current="page" @endif>Galeria</a></li>
      <li><a class="topo__link" href="{{ $raiz }}#orcamento">Orçamento</a></li>
      <li><a class="topo__link" href="{{ $raiz }}#duvidas">Dúvidas</a></li>
      <li><a class="topo__link" href="{{ $raiz }}#onde">Onde estamos</a></li>
    </ul>
  </nav>

  <button class="tema" id="tema" type="button" hidden aria-pressed="false" aria-label="Usar o tema claro">
    <svg class="tema__lua" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.500 10.500z"/></svg>
    <svg class="tema__sol" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2.500v2M12 19.500v2M4.600 4.600 6 6M18 18l1.400 1.400M2.500 12h2M19.500 12h2M4.600 19.400 6 18M18 6l1.400-1.400"/></svg>
  </button>
  <a class="btn btn--cromo topo__cta" data-wa="Olá! Vim pelo site e quero um orçamento." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>WhatsApp</a>
  <button class="hamburguer" id="hamburguer" type="button" aria-expanded="false" aria-controls="gaveta" aria-label="Abrir menu"><i></i></button>
</header>

<div class="veu" id="veu" hidden></div>
<nav class="gaveta" id="gaveta" aria-label="Menu" aria-hidden="true">
  <p class="gaveta__titulo">Menu</p>
  <a class="gaveta__link" href="{{ $raiz }}#servicos"><span>01</span> Serviços</a>
  <a class="gaveta__link" href="{{ $raiz }}#como-funciona"><span>02</span> Como funciona</a>
  <a class="gaveta__link" @if ($naHome) href="#galeria" @else href="/galeria" aria-current="page" @endif><span>03</span> Galeria</a>
  <a class="gaveta__link" href="{{ $raiz }}#orcamento"><span>04</span> Orçamento</a>
  <a class="gaveta__link" href="{{ $raiz }}#duvidas"><span>05</span> Dúvidas</a>
  <a class="gaveta__link" href="{{ $raiz }}#onde"><span>06</span> Onde estamos</a>
  <div class="gaveta__cta">
    <a class="btn btn--cromo btn--bloco" data-wa="Olá! Vim pelo site e quero um orçamento." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>Chamar no WhatsApp</a>
  </div>
</nav>

@yield('conteudo')

<footer class="rodape" id="contato">
  <div class="wrap rodape__grade">
    <div class="rodape__marca">
      <picture><img src="/assets/img/selo-360.webp" width="120" height="120" alt="A melhor oficina" loading="lazy"></picture>
      <p>Oficina mecânica especializada em carros. São Paulo, SP.</p>
    </div>
    <ul class="rodape__contatos">
      <li><a data-wa="Olá! Vim pelo site." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>WhatsApp {{ \App\Models\Oficina::formatar($oficina->numeroDoWhatsapp()) }}</a></li>
      <li><a data-tel href="tel:+{{ $oficina->numeroDeLigar() }}"><svg class="ico"><use href="#i-tel"/></svg>Ligar para a oficina</a></li>
      <li><a data-insta href="{{ $oficina->instagramUrl() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-insta"/></svg>{{ '@'.$oficina->valor('instagram') }}@if ($oficina->exemplo('instagram')) <i class="marcador">exemplo</i>@endif</a></li>
      <li><a href="{{ $raiz }}#onde"><svg class="ico"><use href="#i-pin"/></svg>{{ $oficina->valor('logradouro') }} — {{ $oficina->valor('cidade') }}</a></li>
      <li><a href="mailto:{{ $oficina->valor('email') }}"><svg class="ico"><use href="#i-email"/></svg>{{ $oficina->valor('email') }}@if ($oficina->exemplo('email')) <i class="marcador">exemplo</i>@endif</a></li>
    </ul>
  </div>
  <div class="wrap rodape__base">
    <p>© <span id="ano">2026</span> A melhor oficina. Todos os direitos reservados.</p>
    <p>Site por <a href="https://exemplo.test" target="_blank" rel="noopener">Exemplo</a></p>
  </div>
</footer>

<button class="a11y-fab" id="a11y-fab" type="button"
        aria-controls="a11y-painel" aria-expanded="false"
        aria-label="Abrir opções de acessibilidade">
  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    <circle cx="12" cy="4" r="2" fill="currentColor"/>
    <path fill="currentColor" d="M20 7.5a1 1 0 0 0-1.2-.7L15 7.7a12 12 0 0 1-6 0L5.2 6.8A1 1 0 0 0 4.8 8.8L8 9.6v2.2l-2.3 7a1 1 0 0 0 1.9.6L9.6 14h4.8l2 5.4a1 1 0 0 0 1.9-.6l-2.3-7V9.6l3.2-.8a1 1 0 0 0 .8-1.3z"/>
  </svg>
</button>

<div class="a11y-painel" id="a11y-painel" data-lenis-prevent role="dialog" aria-modal="true"
     aria-label="Opções de acessibilidade" hidden>
  <div class="a11y-painel__topo">
    <h2>Acessibilidade</h2>
    <button type="button" class="a11y-fechar" id="a11y-fechar" aria-label="Fechar">&times;</button>
  </div>
  <div class="a11y-grupo" role="group" aria-label="Tamanho do texto">
    <span class="a11y-rot">Texto</span>
    <div class="a11y-btns">
      <button type="button" data-a11y="fonte" data-valor="0" aria-pressed="true">Normal</button>
      <button type="button" data-a11y="fonte" data-valor="1" aria-pressed="false">Maior</button>
      <button type="button" data-a11y="fonte" data-valor="2" aria-pressed="false">Muito maior</button>
    </div>
  </div>
  <div class="a11y-grupo" role="group" aria-label="Contraste">
    <span class="a11y-rot">Contraste</span>
    <div class="a11y-btns">
      <button type="button" data-a11y="contraste" data-valor="-2" aria-pressed="false">−2</button>
      <button type="button" data-a11y="contraste" data-valor="-1" aria-pressed="false">−1</button>
      <button type="button" data-a11y="contraste" data-valor="0" aria-pressed="true">0</button>
      <button type="button" data-a11y="contraste" data-valor="1" aria-pressed="false">+1</button>
      <button type="button" data-a11y="contraste" data-valor="2" aria-pressed="false">+2</button>
    </div>
    <p class="a11y-dica">Negativo reduz o brilho, para quem se incomoda com tela clara.</p>
  </div>
  <div class="a11y-grupo">
    <span class="a11y-rot">Ajustes</span>
    <div class="a11y-chaves">
      <label><input type="checkbox" data-a11y="cinza"> Escala de cinza</label>
      <label><input type="checkbox" data-a11y="links"> Sublinhar links</label>
      <label><input type="checkbox" data-a11y="espaco"> Espaçar o texto</label>
      <label><input type="checkbox" data-a11y="parar"> Pausar animações</label>
      <label><input type="checkbox" data-a11y="cursor"> Cursor grande</label>
      <label><input type="checkbox" data-a11y="guia"> Guia de leitura</label>
    </div>
  </div>

  <div class="a11y-grupo" id="a11y-libras" hidden>
    <span class="a11y-rot">Libras</span>
    <button type="button" class="a11y-libras" id="a11y-libras-btn">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M8 13V5.5a1.5 1.5 0 0 1 3 0V11m0-.5v-6a1.5 1.5 0 0 1 3 0v6m0-4.5a1.5 1.5 0 0 1 3 0V13m0-3a1.5 1.5 0 0 1 3 0v5a7 7 0 0 1-7 7h-1.2a6 6 0 0 1-4.6-2.2L3.6 15.4a1.6 1.6 0 0 1 2.4-2L8 15"/></svg>
      <span><strong>Ativar VLibras</strong><small>Tradutor do governo nesta tela</small></span>
    </button>
  </div>
  <button type="button" class="a11y-reset" id="a11y-reset">Desfazer tudo</button>
</div>

<div class="a11y-guia" id="a11y-guia" aria-hidden="true" hidden></div>
<p class="sr-only" role="status" aria-live="polite" id="avisos"></p>

<a class="zap-flutuante" data-wa="Olá! Vim pelo site e quero um orçamento." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener" aria-label="Chamar no WhatsApp"><svg class="ico"><use href="#i-zap"/></svg></a>

<script src="/assets/js/dados.js" defer></script>
<script src="/dados-da-oficina.js" defer></script>
<script src="/assets/js/a11y.js?v=20261006" defer></script>
@if ($naHome)
<script src="/assets/js/vendor/vue.global.prod.js" defer></script>
@endif
<script src="/assets/js/vendor/gsap.min.js" defer></script>
<script src="/assets/js/vendor/ScrollTrigger.min.js" defer></script>
<script src="/assets/js/vendor/lenis.min.js" defer></script>
<script src="/assets/js/tema.js?v={{ @filemtime(public_path('assets/js/tema.js')) }}" defer></script>
<script src="/assets/js/app.js" defer></script>
<script src="/assets/js/galeria.js" defer></script>
</body>
</html>
