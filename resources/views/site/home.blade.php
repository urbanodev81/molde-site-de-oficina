@extends('site.layout')

@push('head')
@verbatim
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@graph":[
    {
      "@type":"AutoRepair",
      "@id":"https://amelhoroficina.example/#oficina",
      "name":"A melhor oficina",
      "slogan":"Especializados em carros",
      "url":"https://amelhoroficina.example/",
      "image":"https://amelhoroficina.example/assets/img/og.jpg",
      "logo":"https://amelhoroficina.example/assets/img/icone-512.png",
      "description":"Oficina mecânica especializada em carros: revisão preventiva, freios, suspensão, motor, injeção eletrônica, arrefecimento, embreagem e diagnóstico por scanner.",
      "priceRange":"$$",
      "areaServed":{"@type":"Place","name":"São Paulo, SP, Brasil"},
@endverbatim
      {!! $oficina->trechoDoJsonLd() !!},
@verbatim
      "hasOfferCatalog":{
        "@type":"OfferCatalog",
        "name":"Serviços da oficina",
        "itemListElement":[
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Revisão preventiva"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Freios"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Suspensão e direção"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Motor"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Injeção eletrônica"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Troca de óleo e filtros"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Embreagem e câmbio"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Arrefecimento"}},
          {"@type":"Offer","itemOffered":{"@type":"Service","name":"Diagnóstico por scanner"}}
        ]
      }
    },
    {
      "@type":"WebSite",
      "@id":"https://amelhoroficina.example/#site",
      "url":"https://amelhoroficina.example/",
      "name":"A melhor oficina",
      "inLanguage":"pt-BR",
      "publisher":{"@id":"https://amelhoroficina.example/#oficina"}
    },
    {
      "@type":"FAQPage",
      "mainEntity":[
        {"@type":"Question","name":"O orçamento é cobrado?",
         "acceptedAnswer":{"@type":"Answer","text":"Não. O orçamento é passado antes de qualquer serviço e nada é feito sem a sua aprovação."}},
        {"@type":"Question","name":"Vocês atendem carro importado?",
         "acceptedAnswer":{"@type":"Answer","text":"Sim. A oficina atende carros nacionais e importados de passeio. Para modelos específicos, confirme pelo WhatsApp."}},
        {"@type":"Question","name":"Preciso agendar?",
         "acceptedAnswer":{"@type":"Answer","text":"Recomendamos agendar pelo WhatsApp para garantir o horário. Emergências são encaixadas conforme a agenda do dia."}},
        {"@type":"Question","name":"As peças trocadas voltam para mim?",
         "acceptedAnswer":{"@type":"Answer","text":"Sim. Toda peça substituída é entregue junto com o carro, para você ver o que foi trocado."}},
        {"@type":"Question","name":"O serviço tem garantia?",
         "acceptedAnswer":{"@type":"Answer","text":"Sim. Mão de obra e peças fornecidas pela oficina têm garantia, com o prazo informado no orçamento."}},
        {"@type":"Question","name":"Quais as formas de pagamento?",
         "acceptedAnswer":{"@type":"Answer","text":"Pix, dinheiro, débito e crédito, com parcelamento a combinar conforme o valor do serviço."}}
      ]
    }
  ]
}
</script>
@endverbatim
@endpush

@section('conteudo')
<main id="conteudo">

  <section class="hero" id="topo" aria-labelledby="hero-titulo">
    <div class="hero__fundo" aria-hidden="true">
      <div class="hero__carbono"></div>
      <div class="hero__luz"></div>
    </div>

    <div class="hero__grade wrap">
      <div class="hero__texto">
        <p class="olho hero__olho"><span class="ponto" aria-hidden="true"></span>Oficina mecânica · São Paulo <i class="marcador">exemplo</i></p>
        <h1 id="hero-titulo" class="hero__titulo">
          <span class="cromo">Seu carro</span>
          <span class="cromo">na mão de quem</span>
          <span class="hero__destaque">entende.</span>
        </h1>
        <p class="hero__sub">Revisão, freios, suspensão, motor e injeção eletrônica. Diagnóstico por scanner, orçamento aprovado antes de mexer e a peça trocada volta com você.</p>
        <div class="hero__acoes">
          <a class="btn btn--cromo btn--grande" href="#orcamento">Pedir orçamento <svg class="ico"><use href="#i-seta"/></svg></a>
          <a class="btn btn--linha btn--grande" href="#servicos">Ver serviços</a>
        </div>
        <ul class="hero__selos" aria-label="Compromissos da oficina">
          <li><svg class="ico"><use href="#i-scanner"/></svg>Diagnóstico por scanner</li>
          <li><svg class="ico"><use href="#i-escudo"/></svg>Serviço com garantia</li>
          <li><svg class="ico"><use href="#i-peca"/></svg>Peça trocada volta com você</li>
        </ul>
      </div>

      <div class="hero__palco" aria-hidden="false">
        <svg class="engrenagem" viewBox="0 0 400 400" aria-hidden="true">
          <path d="M371.1 182.6 L395.5 185.6 L395.5 214.4 L371.1 217.4 L366.7 242.2 L388.6 253.4 L378.8 280.3 L354.9 274.9 L342.3 296.7 L359.0 314.7 L340.5 336.6 L319.9 323.3 L300.6 339.5 L310.2 362.1 L285.3 376.5 L270.5 356.9 L246.8 365.5 L248.1 390.0 L219.8 395.0 L212.6 371.5 L187.4 371.5 L180.2 395.0 L151.9 390.0 L153.2 365.5 L129.5 356.9 L114.7 376.5 L89.8 362.1 L99.4 339.5 L80.1 323.3 L59.5 336.6 L41.0 314.7 L57.7 296.7 L45.1 274.9 L21.2 280.3 L11.4 253.4 L33.3 242.2 L28.9 217.4 L4.5 214.4 L4.5 185.6 L28.9 182.6 L33.3 157.8 L11.4 146.6 L21.2 119.7 L45.1 125.1 L57.7 103.3 L41.0 85.3 L59.5 63.4 L80.1 76.7 L99.4 60.5 L89.8 37.9 L114.7 23.5 L129.5 43.1 L153.2 34.5 L151.9 10.0 L180.2 5.0 L187.4 28.5 L212.6 28.5 L219.8 5.0 L248.1 10.0 L246.8 34.5 L270.5 43.1 L285.3 23.5 L310.2 37.9 L300.6 60.5 L319.9 76.7 L340.5 63.4 L359.0 85.3 L342.3 103.3 L354.9 125.1 L378.8 119.7 L388.6 146.6 L366.7 157.8 Z"/>
          <circle cx="200" cy="200" r="150"/>
        </svg>
        <div class="selo" id="selo">
          <picture>
            <img src="/assets/img/selo.webp" width="636" height="636" alt="Selo da oficina — especializados em carros" fetchpriority="high">
          </picture>
          <span class="selo__brilho" aria-hidden="true"></span>
        </div>
        <span class="faisca faisca--1" aria-hidden="true"></span>
        <span class="faisca faisca--2" aria-hidden="true"></span>
        <span class="faisca faisca--3" aria-hidden="true"></span>
      </div>
    </div>

    <a class="hero__rolar" href="#servicos" aria-label="Rolar para os serviços"><span></span></a>
  </section>

  <div class="faixa" aria-hidden="true">
    <div class="faixa__trilho">
      <span>Revisão</span><span>Freios</span><span>Suspensão</span><span>Motor</span><span>Injeção eletrônica</span><span>Embreagem</span><span>Arrefecimento</span><span>Scanner</span>
      <span>Revisão</span><span>Freios</span><span>Suspensão</span><span>Motor</span><span>Injeção eletrônica</span><span>Embreagem</span><span>Arrefecimento</span><span>Scanner</span>
    </div>
  </div>

  <section class="secao" id="servicos" aria-labelledby="servicos-titulo">
    <div class="wrap">
      <header class="secao__cab revela">
        <p class="olho">O que a gente faz</p>
        <h2 id="servicos-titulo"><span class="cromo">Serviços</span></h2>
        <p class="secao__intro">Do barulho estranho na roda à luz acesa no painel. Carro de passeio, nacional ou importado.</p>
      </header>

      <ul class="servicos">
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-revisao"/></svg>
          <h3>Revisão preventiva</h3>
          <p>Checagem completa por quilometragem, com o que precisa agora e o que pode esperar.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar uma revisão preventiva." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-freio"/></svg>
          <h3>Freios</h3>
          <p>Pastilhas, discos, fluido e sistema ABS. Freio chiando ou pedal baixo, traz que a gente vê.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar um serviço de freio." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-suspensao"/></svg>
          <h3>Suspensão e direção</h3>
          <p>Amortecedores, bandejas, buchas, terminais e caixa de direção. Fim do barulho na lombada.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar suspensão/direção." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-motor"/></svg>
          <h3>Motor</h3>
          <p>Correia dentada, junta, vazamentos, retífica parcial e completa. Motor falhando ou fumaçando.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar um serviço de motor." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-injecao"/></svg>
          <h3>Injeção eletrônica</h3>
          <p>Limpeza de bicos, sensores, corpo de borboleta e ignição. Consumo alto e marcha lenta irregular.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar injeção eletrônica." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-oleo"/></svg>
          <h3>Óleo e filtros</h3>
          <p>Troca com o óleo da especificação do fabricante e filtros de óleo, ar, combustível e cabine.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar troca de óleo e filtros." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-cambio"/></svg>
          <h3>Embreagem e câmbio</h3>
          <p>Kit de embreagem, cabo, atuador e revisão de câmbio manual. Marcha arranhando ou pedal duro.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar embreagem/câmbio." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico revela">
          <svg class="servico__ico"><use href="#i-arrefecimento"/></svg>
          <h3>Arrefecimento</h3>
          <p>Radiador, bomba d'água, válvula termostática e mangueiras. Ponteiro subindo, não arrisca.</p>
          <a class="servico__link" data-wa="Olá! Quero orçar o sistema de arrefecimento." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Orçar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
        <li class="servico servico--destaque revela">
          <svg class="servico__ico"><use href="#i-scanner"/></svg>
          <h3>Diagnóstico por scanner</h3>
          <p>Luz de injeção acesa no painel? O scanner lê o código da falha e a gente te explica o que é, sem chute.</p>
          <a class="servico__link" data-wa="Olá! Quero agendar um diagnóstico por scanner." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener">Agendar <svg class="ico"><use href="#i-seta"/></svg></a>
        </li>
      </ul>
    </div>
  </section>

  <section class="secao secao--metal" id="como-funciona" aria-labelledby="como-titulo">
    <div class="wrap">
      <header class="secao__cab revela">
        <p class="olho">Sem surpresa na hora de pagar</p>
        <h2 id="como-titulo"><span class="cromo">Como funciona</span></h2>
      </header>
      <ol class="passos">
        <li class="passo revela">
          <span class="passo__n">01</span>
          <h3>Você chama</h3>
          <p>Pelo WhatsApp ou no formulário aqui embaixo. Conta o carro e o que está acontecendo.</p>
        </li>
        <li class="passo revela">
          <span class="passo__n">02</span>
          <h3>Diagnóstico</h3>
          <p>O carro passa pela avaliação e pelo scanner quando precisa. Nada de trocar peça no escuro.</p>
        </li>
        <li class="passo revela">
          <span class="passo__n">03</span>
          <h3>Orçamento aprovado</h3>
          <p>Você recebe o valor de peça e mão de obra, separado. Só começa depois do seu OK.</p>
        </li>
        <li class="passo revela">
          <span class="passo__n">04</span>
          <h3>Carro pronto</h3>
          <p>Entrega com teste feito, a peça velha na mão e a garantia do serviço anotada.</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="numeros" aria-label="Diferenciais">
    <div class="wrap numeros__grade">
      <div class="numero revela"><strong class="cromo">16<small>+</small></strong><span>anos de oficina <i class="marcador">exemplo</i></span></div>
      <div class="numero revela"><strong class="cromo">0<small>R$</small></strong><span>para fazer o orçamento</span></div>
      <div class="numero revela"><strong class="cromo">90<small>dias</small></strong><span>de garantia no serviço <i class="marcador">exemplo</i></span></div>
      <div class="numero revela"><strong class="cromo">100<small>%</small></strong><span>das peças trocadas devolvidas</span></div>
    </div>
  </section>

  @if ($videos->isNotEmpty())
  <section class="secao secao--metal" id="galeria" aria-labelledby="galeria-titulo">
    <div class="wrap">
      <header class="secao__cab secao__cab--acao revela">
        <div>
          <p class="olho">Na oficina</p>
          <h2 id="galeria-titulo"><span class="cromo">Galeria</span></h2>
          <p class="secao__intro">O serviço sendo feito, com a explicação do que foi trocado e por quê. Toque para assistir.</p>
        </div>
        <a class="btn btn--linha" href="/galeria">Ver galeria<svg class="ico"><use href="#i-seta"/></svg></a>
      </header>

      <ul class="midias midias--dupla">
        @foreach ($videos as $video)
          @include('site._video', ['video' => $video])
        @endforeach
      </ul>
    </div>
  </section>
  @endif

  <section class="secao" id="orcamento" aria-labelledby="orcamento-titulo">
    <div class="wrap orcamento">
      <header class="orcamento__cab revela">
        <p class="olho">Resposta pelo WhatsApp</p>
        <h2 id="orcamento-titulo"><span class="cromo">Pedir</span> <span class="cromo">orçamento</span></h2>
        <p class="secao__intro">Preenche o básico e o WhatsApp abre com a mensagem pronta. Quanto mais detalhe, mais certo sai o valor.</p>
        <ul class="orcamento__lista">
          <li><svg class="ico"><use href="#i-carro"/></svg>Modelo, ano e motor do carro</li>
          <li><svg class="ico"><use href="#i-scanner"/></svg>Se tem luz acesa no painel</li>
          <li><svg class="ico"><use href="#i-relogio"/></svg>Desde quando o problema aparece</li>
        </ul>
      </header>

      <div class="orcamento__caixa revela" id="app-orcamento">

        <div class="orcamento__semjs">
          <p>Chame direto no WhatsApp e conte o modelo do carro e o que está acontecendo.</p>
          <a class="btn btn--cromo btn--bloco" href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>Chamar no WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

  <section class="secao secao--metal" id="onde" aria-labelledby="onde-titulo">
    <div class="wrap onde">
      <div class="onde__texto revela">
        <p class="olho">Venha até a oficina</p>
        <h2 id="onde-titulo"><span class="cromo">Onde</span> <span class="cromo">estamos</span></h2>
        <address class="onde__end">
          <svg class="ico"><use href="#i-pin"/></svg>
          <span>{{ $oficina->enderecoLinha1() }}<br>{{ $oficina->enderecoLinha2() }}@if ($oficina->exemplo('endereco')) <i class="marcador">exemplo</i>@endif</span>
        </address>
        <a class="btn btn--cromo" id="btn-rota" href="{{ $oficina->linkDoMapa() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-pin"/></svg>Traçar rota</a>
      </div>
      <div class="horario revela">
        <h3><svg class="ico"><use href="#i-relogio"/></svg>Horário @if ($oficina->exemplo('horario'))<i class="marcador">exemplo</i>@endif</h3>
        <dl id="horario">
          @foreach ($oficina->horarioAgrupado() as $grupo)
          <div data-dias="{{ implode(',', $grupo['dias']) }}"><dt>{{ $grupo['rotulo'] }}</dt><dd>{{ $grupo['texto'] }}</dd></div>
          @endforeach
        </dl>
        <p class="horario__agora" id="agora" hidden></p>
      </div>

      @if (filled($oficina->mapa_embed))
      <div class="onde__mapa revela">
        <iframe src="{{ $oficina->mapa_embed }}" title="Mapa: como chegar à A melhor oficina" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
      @endif
    </div>
  </section>

  <section class="secao" id="duvidas" aria-labelledby="duvidas-titulo">
    <div class="wrap duvidas">
      <header class="secao__cab revela">
        <p class="olho">Perguntas de sempre</p>
        <h2 id="duvidas-titulo"><span class="cromo">Dúvidas</span></h2>
      </header>
      <div class="faq">
        <details class="revela"><summary>O orçamento é cobrado?</summary><p>Não. O orçamento é passado antes de qualquer serviço e nada é feito sem a sua aprovação.</p></details>
        <details class="revela"><summary>Vocês atendem carro importado?</summary><p>Sim. A oficina atende carros nacionais e importados de passeio. Para modelos específicos, confirme pelo WhatsApp.</p></details>
        <details class="revela"><summary>Preciso agendar?</summary><p>Recomendamos agendar pelo WhatsApp para garantir o horário. Emergências são encaixadas conforme a agenda do dia.</p></details>
        <details class="revela"><summary>As peças trocadas voltam para mim?</summary><p>Sim. Toda peça substituída é entregue junto com o carro, para você ver o que foi trocado.</p></details>
        <details class="revela"><summary>O serviço tem garantia?</summary><p>Sim. Mão de obra e peças fornecidas pela oficina têm garantia, com o prazo informado no orçamento.</p></details>
        <details class="revela"><summary>Quais as formas de pagamento?</summary><p>Pix, dinheiro, débito e crédito, com parcelamento a combinar conforme o valor do serviço.</p></details>
      </div>
      <p class="nota">Respostas em validação com a oficina.</p>
    </div>
  </section>

  <section class="chamada" aria-labelledby="chamada-titulo">
    <div class="wrap chamada__grade revela">
      <h2 id="chamada-titulo"><span class="cromo">Luz acesa no painel?</span></h2>
      <p>Não espera virar problema maior. Chama agora e já sai com horário marcado.</p>
      <a class="btn btn--cromo btn--grande" data-wa="Olá! Meu carro está com um problema e quero agendar uma avaliação." href="{{ $oficina->linkWhatsapp() }}" target="_blank" rel="noopener"><svg class="ico"><use href="#i-zap"/></svg>Chamar no WhatsApp</a>
    </div>
  </section>
</main>
@endsection
