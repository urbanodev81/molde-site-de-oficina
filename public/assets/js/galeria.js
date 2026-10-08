(function () {
  'use strict';

  var videos = document.querySelectorAll('[data-video]');
  var fotos = Array.prototype.slice.call(document.querySelectorAll('[data-foto]'));
  if (!videos.length && !fotos.length) return;
  if (typeof HTMLDialogElement === 'undefined') return;

  var palco = document.createElement('dialog');
  palco.className = 'palco';
  palco.setAttribute('data-lenis-prevent', '');
  palco.setAttribute('aria-labelledby', 'palco-titulo');
  palco.innerHTML =
    '<div class="palco__midia">' +
      '<button class="palco__botao palco__antes" type="button" aria-label="Foto anterior" hidden>&#8249;</button>' +
      '<button class="palco__botao palco__depois" type="button" aria-label="Próxima foto" hidden>&#8250;</button>' +
    '</div>' +

    '<div class="palco__texto"><div><p class="palco__conta" hidden></p><h2 id="palco-titulo"></h2><p class="palco__descricao"></p></div>' +
    '<button class="palco__botao palco__fechar" type="button" aria-label="Fechar">&times;</button></div>';
  document.body.appendChild(palco);

  var caixa = palco.querySelector('.palco__midia');
  var antes = palco.querySelector('.palco__antes');
  var depois = palco.querySelector('.palco__depois');
  var conta = palco.querySelector('.palco__conta');
  var titulo = palco.querySelector('#palco-titulo');
  var descricao = palco.querySelector('.palco__descricao');
  var atual = -1;
  var origem = null;

  function limpa() {
    var m = caixa.querySelector('iframe, img');
    if (m) caixa.removeChild(m);
  }

  function texto(el) {
    titulo.textContent = el.getAttribute('data-titulo') || '';
    descricao.textContent = el.getAttribute('data-descricao') || '';
  }

  function abre(el) {
    origem = origem || el;
    if (!palco.open) palco.showModal();
  }

  function abreVideo(el) {
    limpa();
    atual = -1;
    caixa.classList.remove('palco__midia--foto');
    antes.hidden = depois.hidden = conta.hidden = true;
    var f = document.createElement('iframe');

    f.src = el.getAttribute('data-embed') ||
      'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(el.getAttribute('data-video')) + '?autoplay=1&rel=0';
    f.title = el.getAttribute('data-titulo') || 'Vídeo';
    f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    f.setAttribute('allowfullscreen', '');
    f.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
    caixa.appendChild(f);
    texto(el);
    abre(el);
  }

  function abreFoto(k) {
    limpa();
    atual = (k + fotos.length) % fotos.length;
    var el = fotos[atual];
    caixa.classList.add('palco__midia--foto');
    var img = document.createElement('img');
    img.src = el.getAttribute('href');
    img.alt = (el.querySelector('img') || {}).alt || '';
    caixa.appendChild(img);
    var varias = fotos.length > 1;
    antes.hidden = depois.hidden = conta.hidden = !varias;
    conta.textContent = (atual + 1) + ' / ' + fotos.length;
    texto(el);
    abre(el);
  }

  Array.prototype.forEach.call(videos, function (el) {
    el.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      origem = el;
      abreVideo(el);
    });
  });
  fotos.forEach(function (el, k) {
    el.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      origem = el;
      abreFoto(k);
    });
  });

  antes.addEventListener('click', function () { abreFoto(atual - 1); });
  depois.addEventListener('click', function () { abreFoto(atual + 1); });
  palco.querySelector('.palco__fechar').addEventListener('click', function () { palco.close(); });

  palco.addEventListener('click', function (e) { if (e.target === palco) palco.close(); });
  palco.addEventListener('keydown', function (e) {
    if (atual < 0) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); abreFoto(atual - 1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); abreFoto(atual + 1); }
  });

  palco.addEventListener('close', function () {
    limpa();
    if (origem && origem.focus) origem.focus({ preventScroll: true });
    origem = null;
  });
})();
