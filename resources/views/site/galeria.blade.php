@extends('site.layout')

@section('conteudo')
<main id="conteudo">

  <section class="secao pagina-topo" aria-labelledby="galeria-titulo">
    <div class="wrap">
      <header class="secao__cab revela">
        <p class="olho">Na oficina</p>
        <h1 id="galeria-titulo"><span class="cromo">Galeria</span></h1>
        <p class="secao__intro">Fotos e vídeos dos serviços, com a explicação do que foi feito em cada carro.@if ($temExemplo) <i class="marcador">conteúdo de exemplo</i>@endif</p>
      </header>

      @if ($videos->isNotEmpty())
      <h2 class="midias__titulo revela" id="videos">Vídeos</h2>
      <ul class="midias midias--dupla">
        @foreach ($videos as $video)
          @include('site._video', ['video' => $video])
        @endforeach
      </ul>
      @endif

      @if ($fotos->isNotEmpty())
      <h2 class="midias__titulo revela" id="fotos">Fotos</h2>
      <ul class="midias">
        @foreach ($fotos as $foto)
          @include('site._foto', ['foto' => $foto])
        @endforeach
      </ul>
      @endif

      @if ($videos->isEmpty() && $fotos->isEmpty())
      <p class="secao__intro revela">Ainda não há fotos nem vídeos publicados. Volte em breve.</p>
      @endif
    </div>
  </section>

  <section class="chamada" aria-labelledby="chamada-titulo">
    <div class="wrap chamada__grade revela">
      <h2 id="chamada-titulo"><span class="cromo">Seu carro é o próximo?</span></h2>
      <p>Chama no WhatsApp, conta o que está acontecendo e já sai com horário marcado.</p>
      <a class="btn btn--cromo btn--grande" data-wa="Olá! Vi a galeria no site e quero um orçamento." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>Chamar no WhatsApp</a>
    </div>
  </section>
</main>
@endsection
