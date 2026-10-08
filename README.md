# Site de oficina — molde

Site público e painel de manutenção para uma oficina mecânica: serviços,
galeria de fotos e vídeos, dados de contato e endereço.

- **Site público em Blade**: o conteúdo chega no HTML, para buscador e motor
  generativo lerem sem executar JavaScript. Tema escuro e claro.
- **Painel em Vue 3 + Inertia**, com perfis e permissões por rota.
- Laravel · PHP 8.4 · PostgreSQL · Tailwind 3 · Pest.

A oficina ("A melhor oficina"), as pessoas e as imagens deste molde são
inventadas. Troque em `config/oficina.php`, em `public/assets/img/` e nos
dados da oficina, pelo painel.

## Rodar

```bash
cp .env.example .env
composer install && npm install
php artisan key:generate && php artisan migrate
php artisan db:seed --class=PerfisESeguranca
php artisan db:seed --class=GaleriaDeExemplo
npm run build && php artisan serve
```

## Testes

```bash
php vendor/bin/pest --testsuite=Feature
```

## Licença

Proprietária, todos os direitos reservados. Ver `LICENSE`.
