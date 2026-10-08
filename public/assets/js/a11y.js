(function () {
  'use strict';

  var K_A11Y = 'amelhoroficina-a11y-';
  var CHAVES = ['cinza', 'links', 'espaco', 'parar', 'cursor', 'guia'];

  var raiz = document.documentElement;
  var avisos = document.getElementById('avisos');

  function guarda(chave, valor) {
    try { localStorage.setItem(chave, valor); } catch (e) {  }
  }
  function busca(chave, padrao) {
    try { var v = localStorage.getItem(chave); return v === null ? padrao : v; }
    catch (e) { return padrao; }
  }
  function anuncia(texto) {
    if (avisos) { avisos.textContent = ''; setTimeout(function () { avisos.textContent = texto; }, 60); }
  }

  var a11y = {
    fonte: parseInt(busca(K_A11Y + 'fonte', '0'), 10) || 0,
    contraste: parseInt(busca(K_A11Y + 'contraste', '0'), 10) || 0
  };
  CHAVES.forEach(function (k) { a11y[k] = busca(K_A11Y + k, '0') === '1'; });

  var guia = document.getElementById('a11y-guia');

  function aplicaMovimento() {
    if (window.gsap && window.gsap.globalTimeline) window.gsap.globalTimeline.paused(a11y.parar);
    if (a11y.parar) {
      if (window.OFICINA_ROLAGEM) window.OFICINA_ROLAGEM.desliga();
      Array.prototype.forEach.call(document.querySelectorAll('.revela'),
        function (el) { el.classList.add('visto'); });
    }
  }

  function aplica() {
    raiz.classList.remove('a11y-fonte-1', 'a11y-fonte-2');
    if (a11y.fonte) raiz.classList.add('a11y-fonte-' + a11y.fonte);
    raiz.setAttribute('data-a11y-contraste', String(a11y.contraste));
    CHAVES.forEach(function (k) { raiz.classList.toggle('a11y-' + k, !!a11y[k]); });
    if (guia) guia.hidden = !a11y.guia;

    document.querySelectorAll('[data-a11y="fonte"]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(Number(b.dataset.valor) === a11y.fonte));
    });
    document.querySelectorAll('[data-a11y="contraste"]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(Number(b.dataset.valor) === a11y.contraste));
    });
    CHAVES.forEach(function (k) {
      var c = document.querySelector('input[data-a11y="' + k + '"]');
      if (c) c.checked = !!a11y[k];
    });
    aplicaMovimento();
  }

  function salva() {
    guarda(K_A11Y + 'fonte', String(a11y.fonte));
    guarda(K_A11Y + 'contraste', String(a11y.contraste));
    CHAVES.forEach(function (k) { guarda(K_A11Y + k, a11y[k] ? '1' : '0'); });
  }

  document.querySelectorAll('[data-a11y="fonte"],[data-a11y="contraste"]').forEach(function (b) {
    b.addEventListener('click', function () {
      a11y[b.dataset.a11y] = Number(b.dataset.valor);
      aplica(); salva();
      anuncia(b.dataset.a11y === 'fonte' ? 'Texto ' + b.textContent.toLowerCase() + '.'
                                         : 'Contraste ' + b.dataset.valor + '.');
    });
  });
  CHAVES.forEach(function (k) {
    var c = document.querySelector('input[data-a11y="' + k + '"]');
    if (!c) return;
    c.addEventListener('change', function () {
      a11y[k] = c.checked; aplica(); salva();
      anuncia(c.parentNode.textContent.trim() + (c.checked ? ' ligado.' : ' desligado.'));
    });
  });

  var reset = document.getElementById('a11y-reset');
  if (reset) {
    reset.addEventListener('click', function () {
      a11y.fonte = 0; a11y.contraste = 0;
      CHAVES.forEach(function (k) { a11y[k] = false; });
      aplica(); salva(); anuncia('Acessibilidade restaurada ao padrão.');
    });
  }

  if (guia) {
    var moveGuia = function (y) { guia.style.transform = 'translateY(' + y + 'px)'; };
    document.addEventListener('mousemove', function (e) { if (a11y.guia) moveGuia(e.clientY); });
    document.addEventListener('touchmove', function (e) {
      if (a11y.guia && e.touches[0]) moveGuia(e.touches[0].clientY);
    }, { passive: true });
  }

  var fab = document.getElementById('a11y-fab');
  var painel = document.getElementById('a11y-painel');
  var fechar = document.getElementById('a11y-fechar');

  function abrePainel(aberto) {
    if (!fab || !painel) return;
    painel.hidden = !aberto;
    fab.setAttribute('aria-expanded', String(aberto));
    fab.setAttribute('aria-label', (aberto ? 'Fechar' : 'Abrir') + ' opções de acessibilidade');
    if (aberto) {
      var gaveta = document.getElementById('gaveta');
      if (gaveta && gaveta.classList.contains('aberta')) {
        var ham = document.getElementById('hamburguer'); if (ham) ham.click();
      }
      var p = painel.querySelector('button,input'); if (p) p.focus();
    } else {
      fab.focus();
    }
  }
  if (fab) fab.addEventListener('click', function () { abrePainel(painel.hidden); });
  if (fechar) fechar.addEventListener('click', function () { abrePainel(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && painel && !painel.hidden) abrePainel(false);
  });

  var librasGrupo = document.getElementById('a11y-libras');
  var librasBtn = document.getElementById('a11y-libras-btn');
  function librasNativo() {
    var novo = document.getElementById('vlibras-access-wrapper');
    var b = novo && novo.shadowRoot && novo.shadowRoot.getElementById('vlibras-button');
    if (b) return b;
    var antigo = document.querySelector('[vw-access-button]');
    return antigo && antigo.firstElementChild ? antigo : null;
  }
  function librasPronto() {
    if (!window.VLibras || !librasNativo()) return false;
    raiz.classList.add('dock-vlibras');
    if (librasGrupo) librasGrupo.hidden = false;
    return true;
  }
  if (librasBtn) librasBtn.addEventListener('click', function () {
    var nativo = librasNativo();
    abrePainel(false);
    if (nativo) nativo.click();
  });
  if (librasGrupo && !window.VLibras && !document.getElementById('vlibras-script')) {
    var vl = document.createElement('script');
    vl.id = 'vlibras-script';
    vl.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';
    vl.onload = function () {
      if (!window.VLibras) return;
      new window.VLibras.Widget('https://vlibras.gov.br/app');

      var n = 0, t = setInterval(function () {
        if (librasPronto() || ++n >= 20) clearInterval(t);
      }, 1000);
    };
    document.body.appendChild(vl);
  }

  window.OFICINA_A11Y = { movimentoOk: function () { return !a11y.parar; } };

  aplica();

  window.addEventListener('load', aplicaMovimento);
})();
