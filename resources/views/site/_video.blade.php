@php($canal = $video->provedor === 'instagram' ? 'insta' : $video->provedor)
<li class="midia revela">
  <a class="midia__capa" href="{{ $video->url() }}" target="_blank" rel="noopener" data-video="{{ $video->codigo }}" data-embed="{{ $video->embed() }}" data-titulo="{{ $video->titulo }}" data-descricao="{{ $video->descricao }}">
    <img src="{{ $video->capa() }}" width="480" height="360" loading="lazy" decoding="async" alt="">
    <span class="midia__canal midia__canal--{{ $video->provedor }}" aria-hidden="true"><svg class="ico"><use href="#i-{{ $canal }}"/></svg>{{ \App\Support\VideoIncorporado::rotulo($video->provedor) }}</span>
    <span class="midia__play" aria-hidden="true"></span>
    <span class="sr-only">Assistir no {{ \App\Support\VideoIncorporado::rotulo($video->provedor) }}: {{ $video->titulo }}</span>
  </a>
  <h3>{{ $video->titulo }}@if ($video->exemplo) <i class="marcador">exemplo</i>@endif</h3>
  @if (filled($video->descricao))
  <p>{{ $video->descricao }}</p>
  @endif
</li>
