(function () {
  'use strict';

  var raiz = document.documentElement;
  var botao = document.getElementById('tema');
  var meta = document.getElementById('cor-do-tema');

  if (!botao) return;

  function vestir(claro) {
    if (claro) raiz.setAttribute('data-tema', 'claro');
    else raiz.removeAttribute('data-tema');

    botao.setAttribute('aria-pressed', String(claro));
    botao.setAttribute('aria-label', claro ? 'Usar o tema escuro' : 'Usar o tema claro');

    if (meta) meta.setAttribute('content', claro ? '#ECECEC' : '#262626');
  }

  vestir(raiz.getAttribute('data-tema') === 'claro');
  botao.hidden = false;

  botao.addEventListener('click', function () {
    var claro = raiz.getAttribute('data-tema') !== 'claro';

    vestir(claro);

    try {
      localStorage.setItem('amelhoroficina-tema', claro ? 'claro' : 'escuro');
    } catch (e) {

    }
  });
})();
