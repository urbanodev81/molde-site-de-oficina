(function () {
  'use strict';

  var dados = window.OFICINA || {};
  var contato = dados.contato || {};

  var pausado = !!(window.OFICINA_A11Y && !window.OFICINA_A11Y.movimentoOk());
  var calmo = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var temGsap = typeof window.gsap !== 'undefined';
  var temVue = typeof window.Vue !== 'undefined';
  var temLenis = typeof window.Lenis !== 'undefined';
  var rolagem = null;

  function linkZap(texto) {
    var base = 'https://wa.me/' + (contato.whatsapp || '');
    return texto ? base + '?text=' + encodeURIComponent(texto) : base;
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-wa]'), function (a) {
    a.href = linkZap(a.getAttribute('data-wa'));
  });
  if (contato.telefone) Array.prototype.forEach.call(document.querySelectorAll('[data-tel]'),
    function (a) { a.href = 'tel:' + contato.telefone; });
  if (contato.instagram) Array.prototype.forEach.call(document.querySelectorAll('[data-insta]'),
    function (a) { a.href = contato.instagram; });
  var rota = document.getElementById('btn-rota');
  if (rota && dados.endereco && dados.endereco.mapa) rota.href = dados.endereco.mapa;

  var ano = document.getElementById('ano');
  if (ano) ano.textContent = new Date().getFullYear();

  var barra = document.getElementById('topo-barra');
  var aoRolar = function () { barra.classList.toggle('rolou', window.scrollY > 24); };
  window.addEventListener('scroll', aoRolar, { passive: true });
  aoRolar();

  var hamburguer = document.getElementById('hamburguer');
  var gaveta = document.getElementById('gaveta');
  var veu = document.getElementById('veu');

  function menu(abrir) {
    hamburguer.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    hamburguer.setAttribute('aria-label', abrir ? 'Fechar menu' : 'Abrir menu');
    gaveta.setAttribute('aria-hidden', abrir ? 'false' : 'true');
    gaveta.classList.toggle('aberta', abrir);
    veu.hidden = !abrir;
    document.body.classList.toggle('menu-aberto', abrir);
    if (rolagem) { if (abrir) rolagem.stop(); else rolagem.start(); }
    if (abrir) { var p = gaveta.querySelector('a'); if (p) p.focus(); }
  }
  hamburguer.addEventListener('click', function () {
    menu(hamburguer.getAttribute('aria-expanded') !== 'true');
  });
  veu.addEventListener('click', function () { menu(false); });
  gaveta.addEventListener('click', function (e) { if (e.target.closest('a')) menu(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && gaveta.classList.contains('aberta')) { menu(false); hamburguer.focus(); }
  });

  function semAnimacao() {
    document.documentElement.classList.add('sem-gsap');
    Array.prototype.forEach.call(document.querySelectorAll('.revela'),
      function (el) { el.classList.add('visto'); });
  }

  if (pausado || !temGsap) {
    semAnimacao();
  } else try {
    gsap.registerPlugin(ScrollTrigger);

    document.documentElement.classList.add('anima');

    var t = gsap.timeline({ delay: 0.15 });

    if (!document.querySelector('.hero')) {

    } else if (calmo) {

      t.from('.hero__luz, .engrenagem, #selo, .faisca', { opacity: 0, duration: 1, ease: 'power2.out' }, 0);
      t.from('.hero__olho, .hero__titulo > span, .hero__sub, .hero__acoes, .hero__selos li',
        { opacity: 0, duration: 0.6, stagger: 0.06, ease: 'power2.out' }, 0.3);
    } else {
      t.from('.hero__luz', { opacity: 0, duration: 1.4, ease: 'power2.out' }, 0);

      t.from('.engrenagem', { opacity: 0, scale: 0.6, rotate: -120, duration: 1.4,
        ease: 'power3.out' }, 0.1);
      gsap.to('.engrenagem', { rotate: '+=360', duration: 60, ease: 'none', repeat: -1, delay: 1.5 });

      t.from('#selo', { opacity: 0, y: -60, scale: 1.18, duration: 0.9,
        ease: 'back.out(1.6)' }, 0.45);
      t.add(function () {
        var p = document.querySelector('.hero__palco');
        if (p) { p.classList.remove('tranco'); void p.offsetWidth; p.classList.add('tranco'); }
      }, 1.05);
      t.from('.faisca', { opacity: 0, scale: 0, duration: 0.5, stagger: 0.08,
        ease: 'power2.out' }, 1.05);

      t.fromTo('.selo__brilho', { xPercent: -130 }, { xPercent: 130, duration: 1.1,
        ease: 'power2.inOut' }, 1.25);

      t.from('.hero__olho', { opacity: 0, y: 12, duration: 0.5 }, 0.3);
      t.from('.hero__titulo > span', { opacity: 0, y: 40, skewY: 4, duration: 0.7,
        stagger: 0.12, ease: 'power3.out' }, 0.4);
      t.from('.hero__sub, .hero__acoes, .hero__selos li', { opacity: 0, y: 16,
        duration: 0.6, stagger: 0.07, ease: 'power2.out' }, 0.95);

      gsap.fromTo('.selo__brilho', { xPercent: -130 }, { xPercent: 130, duration: 1.2,
        ease: 'power2.inOut', repeat: -1, repeatDelay: 6, delay: 5 });

      gsap.to('.hero__palco', { yPercent: 12, ease: 'none',
        scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true } });
    }

    var mexe = function () { return !window.OFICINA_A11Y || window.OFICINA_A11Y.movimentoOk(); };
    gsap.utils.toArray('.revela').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el, start: 'top 88%', end: 'bottom 12%',
        onEnter: function () { el.classList.remove('acima'); el.classList.add('visto'); },
        onEnterBack: function () { el.classList.add('visto'); },
        onLeave: function () { if (mexe()) { el.classList.add('acima'); el.classList.remove('visto'); } },
        onLeaveBack: function () { if (mexe()) { el.classList.remove('acima', 'visto'); } }
      });
    });

    if (temLenis) {

      rolagem = new Lenis({ lerp: 0.1, respectReducedMotion: false });
      rolagem.on('scroll', ScrollTrigger.update);
      gsap.ticker.add(function (tempo) { if (rolagem) rolagem.raf(tempo * 1000); });
      gsap.ticker.lagSmoothing(0);

      document.addEventListener('click', function (e) {
        if (!rolagem || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey) return;
        var a = e.target.closest('a[href^="#"]');
        var id = a && a.getAttribute('href').slice(1);
        var alvo = id ? document.getElementById(id) : null;
        if (!alvo) return;
        e.preventDefault();
        rolagem.scrollTo(alvo, { force: true, onComplete: function () {
          if (!alvo.hasAttribute('tabindex') && !/^(A|BUTTON|INPUT|SELECT|TEXTAREA)$/.test(alvo.tagName)) {
            alvo.setAttribute('tabindex', '-1');
          }
          alvo.focus({ preventScroll: true });
        } });
        if (history.pushState) history.pushState(null, '', '#' + id);
      });

      window.OFICINA_ROLAGEM = { desliga: function () {
        if (rolagem) { rolagem.destroy(); rolagem = null; }
      } };
    }
  } catch (e) {
    semAnimacao();
  }

  (function () {
    var alvo = document.getElementById('agora');
    if (!alvo || !dados.horario || !window.Intl) return;
    try {
      var partes = new Intl.DateTimeFormat('en-US', { timeZone: 'America/Sao_Paulo',
        weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false })
        .formatToParts(new Date());
      var mapa = {}; partes.forEach(function (p) { mapa[p.type] = p.value; });
      var dia = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(mapa.weekday);
      var agora = (parseInt(mapa.hour, 10) % 24) * 60 + parseInt(mapa.minute, 10);
      var min = function (s) { var x = s.split(':'); return +x[0] * 60 + +x[1]; };
      var aberto = dados.horario.some(function (h) {
        return h.dias.indexOf(dia) > -1 && agora >= min(h.abre) && agora < min(h.fecha);
      });
      alvo.textContent = aberto ? 'Aberto agora' : 'Fechado agora · chame no WhatsApp que a gente responde';
      alvo.classList.add(aberto ? 'aberto' : 'fechado');
      alvo.hidden = false;
      Array.prototype.forEach.call(document.querySelectorAll('#horario [data-dias]'), function (l) {
        if (l.getAttribute('data-dias').split(',').map(Number).indexOf(dia) > -1) l.classList.add('hoje');
      });
    } catch (e) {  }
  })();

  var raiz = document.getElementById('app-orcamento');
  if (raiz && temVue) {
    raiz.innerHTML = '';
    Vue.createApp({
      data: function () {
        return {
          servicos: dados.servicos || [],
          turnos: dados.turnos || [],
          f: { nome: '', carro: '', ano: '', placa: '', servico: '', turno: '', relato: '' },
          tentou: false
        };
      },
      computed: {
        faltando: function () {
          var l = [];
          if (!this.f.nome.trim()) l.push('nome');
          if (!this.f.carro.trim()) l.push('carro');
          if (!this.f.servico) l.push('servico');
          return l;
        },
        mensagem: function () {
          var f = this.f, l = ['Olá, A melhor oficina! Quero um orçamento.', ''];
          if (f.nome) l.push('*Nome:* ' + f.nome.trim());
          if (f.carro) l.push('*Carro:* ' + f.carro.trim() + (f.ano ? ' ' + f.ano : ''));
          if (f.placa) l.push('*Placa:* ' + f.placa.trim().toUpperCase());
          if (f.servico) l.push('*Serviço:* ' + f.servico);
          if (f.turno) l.push('*Melhor turno:* ' + f.turno);
          if (f.relato) { l.push(''); l.push(f.relato.trim()); }
          return l.join('\n');
        }
      },
      methods: {
        erro: function (campo) { return this.tentou && this.faltando.indexOf(campo) > -1; },
        enviar: function () {
          this.tentou = true;
          if (this.faltando.length) {
            var el = this.$el.parentNode.querySelector('[data-campo="' + this.faltando[0] + '"]');
            if (el) el.focus();
            return;
          }
          window.open(linkZap(this.mensagem), '_blank', 'noopener');
        }
      },

      render: function () {
        var h = Vue.h, vm = this, f = vm.f;

        function texto(campo, extra) {
          return h('input', Object.assign({
            value: f[campo],
            onInput: function (e) { f[campo] = e.target.value; }
          }, extra || {}));
        }
        function rotulo(classe, titulo, filho) {
          return h('label', { class: classe }, [h('span', titulo), filho]);
        }
        function chips(nome, lista, legenda, classeExtra, primeiroCampo) {
          return h('fieldset', { class: ['campo campo--chips', classeExtra] }, [
            h('legend', legenda),
            h('div', { class: 'chips' }, lista.map(function (v, i) {
              return h('label', { key: v, class: ['chip', { ativo: f[nome] === v }] }, [
                h('input', {
                  type: 'radio', name: nome, value: v, checked: f[nome] === v,
                  'data-campo': primeiroCampo && i === 0 ? primeiroCampo : null,
                  onChange: function () { f[nome] = v; }
                }),
                v
              ]);
            }))
          ]);
        }

        return h('form', {
          class: 'form', novalidate: true,
          onSubmit: function (e) { e.preventDefault(); vm.enviar(); }
        }, [
          h('div', { class: 'form__linha' }, [
            rotulo({ campo: true, erro: vm.erro('nome') }, 'Seu nome *',
              texto('nome', { 'data-campo': 'nome', autocomplete: 'name', maxlength: 60, placeholder: 'Como a gente te chama' }))
          ]),
          h('div', { class: 'form__linha form__linha--3' }, [
            rotulo({ campo: true, erro: vm.erro('carro') }, 'Carro *',
              texto('carro', { 'data-campo': 'carro', maxlength: 60, placeholder: 'Ex.: Onix 1.0 turbo' })),
            rotulo('campo', 'Ano', texto('ano', { inputmode: 'numeric', maxlength: 4, placeholder: '2019' })),
            rotulo('campo', 'Placa', texto('placa', { maxlength: 8, placeholder: 'Opcional', style: 'text-transform:uppercase' }))
          ]),
          chips('servico', vm.servicos, 'Serviço *', { erro: vm.erro('servico') }, 'servico'),
          rotulo('campo', 'O que está acontecendo?', h('textarea', {
            value: f.relato, rows: 3, maxlength: 600, placeholder: 'Barulho, luz no painel, desde quando...',
            onInput: function (e) { f.relato = e.target.value; }
          })),
          chips('turno', vm.turnos, 'Melhor turno para trazer', null, null),
          vm.tentou && vm.faltando.length
            ? h('p', { class: 'form__aviso', role: 'alert' }, 'Preencha nome, carro e serviço para seguir.')
            : null,
          h('button', { class: 'btn btn--cromo btn--bloco btn--grande', type: 'submit' }, [
            h('svg', { class: 'ico' }, [h('use', { href: '#i-zap' })]),
            'Enviar pelo WhatsApp'
          ]),
          h('details', { class: 'form__previa' }, [
            h('summary', 'Ver a mensagem que vai ser enviada'),
            h('pre', vm.mensagem)
          ])
        ]);
      }
    }).mount(raiz);
  }
})();
