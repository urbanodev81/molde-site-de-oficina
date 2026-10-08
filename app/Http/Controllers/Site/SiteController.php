<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Foto;
use App\Models\Oficina;
use App\Models\Video;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'pagina' => 'home',
            'meta' => [
                'titulo' => 'A melhor oficina — oficina mecânica especializada em carros em São Paulo',
                'descricao' => 'Oficina mecânica especializada em carros. Revisão, freios, suspensão, motor, injeção eletrônica e diagnóstico por scanner. Orçamento pelo WhatsApp antes de qualquer serviço.',
                'canonical' => $this->endereco('/'),
                'robots' => 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
                'og_titulo' => 'A melhor oficina — especializados em carros',
            ],
            'videos' => Video::query()->daHome()->get(),
        ]);
    }

    public function galeria(): View
    {
        $videos = Video::query()->publicados()->get();
        $fotos = Foto::query()->publicadas()->get();

        $temExemplo = $videos->contains('exemplo', true) || $fotos->contains('exemplo', true);
        $vazia = $videos->isEmpty() && $fotos->isEmpty();

        return view('site.galeria', [
            'pagina' => 'galeria',
            'meta' => [
                'titulo' => 'Galeria — A melhor oficina',
                'descricao' => 'Fotos e vídeos dos serviços da oficina, com a explicação do que foi feito em cada carro.',
                'canonical' => $this->endereco('/galeria'),

                'robots' => $temExemplo || $vazia ? 'noindex,follow' : 'index,follow,max-image-preview:large',
                'og_titulo' => 'Galeria — A melhor oficina',
            ],
            'videos' => $videos,
            'fotos' => $fotos,
            'temExemplo' => $temExemplo,
        ]);
    }

    public function dados(): Response
    {
        $json = json_encode(Oficina::atual()->paraOJavascript(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_THROW_ON_ERROR);

        return response("window.OFICINA = Object.assign(window.OFICINA || {}, {$json});\n", 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function llms(): Response
    {
        return response(view('site.llms')->render(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    private function endereco(string $caminho): string
    {
        return rtrim((string) config('oficina.dominio'), '/').$caminho;
    }
}
