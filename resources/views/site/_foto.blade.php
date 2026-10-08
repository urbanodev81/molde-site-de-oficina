<li class="midia revela">
  <a class="midia__capa midia__capa--foto" href="{{ $foto->url() }}" data-foto data-titulo="{{ $foto->titulo }}" data-descricao="{{ $foto->descricao }}">
    <img src="{{ $foto->url() }}" width="{{ $foto->largura ?? 800 }}" height="{{ $foto->altura ?? 600 }}" loading="lazy" decoding="async" alt="{{ $foto->exemplo ? 'Foto de exemplo: ' : '' }}{{ $foto->titulo }}">
  </a>
  <h3>{{ $foto->titulo }}@if ($foto->exemplo) <i class="marcador">exemplo</i>@endif</h3>
  @if (filled($foto->descricao))
  <p>{{ $foto->descricao }}</p>
  @endif
</li>
