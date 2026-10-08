<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $nome = config('app.name', 'A melhor oficina');

        return response()->json([

            'id' => '/',
            'name' => $nome,
            'short_name' => 'OFICINA',
            'description' => 'Painel da oficina A melhor oficina: fotos e vídeos da galeria do site.',
            'lang' => 'pt-BR',
            'dir' => 'ltr',

            'start_url' => '/inicio',

            'scope' => '/',

            'display' => 'standalone',
            'orientation' => 'any',

            'theme_color' => '#141414',
            'background_color' => '#141414',

            'shortcuts' => [
                [
                    'name' => 'Enviar foto',
                    'short_name' => 'Fotos',
                    'description' => 'Subir a foto de um serviço para a galeria',
                    'url' => '/painel/fotos',
                ],
                [
                    'name' => 'Incluir vídeo',
                    'short_name' => 'Vídeos',
                    'description' => 'Colar o link de um vídeo da oficina',
                    'url' => '/painel/videos',
                ],
            ],

            'icons' => [
                ['src' => '/assets/img/icone-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/img/icone-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ],
        ])->withHeaders([
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function inicio(Request $request): RedirectResponse
    {
        return redirect()->route($request->user() === null ? 'login' : 'painel.inicio');
    }

    public function offline(): Response
    {
        return response()
            ->view('pwa.offline')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function serviceWorker(): Response
    {
        $codigo = str_replace(
            '__VERSAO__',
            $this->versaoDoBuild(),
            (string) file_get_contents(resource_path('pwa/sw.js')),
        );

        return response($codigo, 200, [
            'Content-Type' => 'text/javascript; charset=UTF-8',

            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    private function versaoDoBuild(): string
    {
        $manifesto = public_path('build/manifest.json');

        return is_file($manifesto)
            ? substr(md5_file($manifesto), 0, 12)
            : 'dev';
    }
}
