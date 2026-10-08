<?php

declare(strict_types=1);

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Foto;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PainelController extends Controller
{
    public function index(Request $request): Response
    {
        $usuario = $request->user();

        $fotos = Foto::query()->where('publicada', true)->count();
        $videos = Video::query()->where('publicado', true)->count();
        $videosNaHome = Video::query()->where('publicado', true)->where('na_home', true)->count();
        $exemplos = Foto::query()->where('exemplo', true)->count() + Video::query()->where('exemplo', true)->count();

        $avisos = [];

        if ($exemplos > 0) {
            $avisos[] = [
                'chave' => 'conteudo-de-exemplo',
                'titulo' => $exemplos === 1 ? 'Há um item de exemplo na galeria' : "Há {$exemplos} itens de exemplo na galeria",
                'detalhe' => 'Vídeo de terceiro e foto desenhada servem só para mostrar o formato. Enquanto existirem, '
                    .'a página Galeria fica fora do Google. Apague-os quando o conteúdo de verdade entrar.',
                'rota' => $usuario?->can('videos.ver') ? 'painel.videos.index' : null,
            ];
        }

        if ($fotos === 0 && $videos === 0) {
            $avisos[] = [
                'chave' => 'galeria-vazia',
                'titulo' => 'A galeria está vazia',
                'detalhe' => 'O site segue no ar, mas a página Galeria não mostra nada e a página inicial não exibe a seção de vídeos.',
                'rota' => $usuario?->can('fotos.ver') ? 'painel.fotos.index' : null,
            ];
        } elseif ($videosNaHome === 0) {
            $avisos[] = [
                'chave' => 'home-sem-video',
                'titulo' => 'Nenhum vídeo na página inicial',
                'detalhe' => 'A seção Galeria da página inicial só aparece com ao menos um vídeo marcado como "Na página inicial".',
                'rota' => $usuario?->can('videos.ver') ? 'painel.videos.index' : null,
            ];
        }

        return Inertia::render('Painel/Inicio', [
            'kpis' => [
                'fotos' => $fotos,
                'videos' => $videos,
                'videosNaHome' => min($videosNaHome, Video::LIMITE_NA_HOME),
                'limiteNaHome' => Video::LIMITE_NA_HOME,
            ],
            'avisos' => $avisos,
        ]);
    }
}
