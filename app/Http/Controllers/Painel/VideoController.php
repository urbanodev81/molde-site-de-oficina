<?php

declare(strict_types=1);

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Support\Arquivos;
use App\Support\VideoIncorporado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VideoController extends Controller
{
    public function index(Request $request): Response
    {
        $arquivados = $request->boolean('arquivados');

        return Inertia::render('Painel/Videos/Index', [
            'videos' => Video::query()->when($arquivados, fn ($q) => $q->onlyTrashed())
                ->orderBy('ordem')->orderByDesc('created_at')
                ->paginate(24)
                ->withQueryString()
                ->through(fn (Video $v) => [
                    'uuid' => $v->uuid,
                    'titulo' => $v->titulo,
                    'descricao' => $v->descricao,
                    'provedor' => VideoIncorporado::rotulo($v->provedor),
                    'link' => $v->url(),
                    'capa' => $v->capa(),
                    'na_home' => $v->na_home,
                    'ordem' => $v->ordem,
                    'publicado' => $v->publicado,
                    'exemplo' => $v->exemplo,
                    'arquivado' => $v->trashed(),
                ]),
            'arquivados' => $arquivados,
            'totais' => ['ativos' => Video::query()->count(), 'arquivados' => Video::onlyTrashed()->count()],
            'provedores' => array_values(VideoIncorporado::PROVEDORES),
            'limiteNaHome' => Video::LIMITE_NA_HOME,
            'naHome' => Video::query()->where('publicado', true)->where('na_home', true)->count(),
            'podeGerenciar' => $request->user()?->can('videos.gerenciar') ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'link' => ['required', 'string', 'max:2000'],
            ...$this->regrasComuns(),
        ]);

        $video = $this->reconhecer($dados['link']);

        Video::create([
            ...collect($dados)->except('link')->all(),
            ...$video,
            'capa_path' => $this->baixarCapa($video['provedor'], $video['codigo']),
        ]);

        return back()->with('sucesso', 'Vídeo adicionado.');
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $dados = $request->validate([
            'link' => ['nullable', 'string', 'max:2000'],
            ...$this->regrasComuns(),
        ]);

        $mudancas = collect($dados)->except('link')->all();

        if (filled($dados['link'] ?? null)) {
            $novo = $this->reconhecer($dados['link']);

            if ($novo['provedor'] !== $video->provedor || $novo['codigo'] !== $video->codigo) {
                Arquivos::apagar($video->capa_path);
                $mudancas = [
                    ...$mudancas,
                    ...$novo,
                    'capa_path' => $this->baixarCapa($novo['provedor'], $novo['codigo']),
                ];
            }
        }

        $video->update($mudancas);

        return back()->with('sucesso', 'Vídeo atualizado.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $video->delete();

        return back()->with('sucesso', 'Vídeo arquivado. Ele está na aba Arquivados.');
    }

    private function reconhecer(string $entrada): array
    {
        return VideoIncorporado::reconhecer($entrada) ?? throw ValidationException::withMessages([
            'link' => 'Não reconheci esse vídeo. Cole o link ou o código de incorporação de um vídeo do '
                .implode(', ', VideoIncorporado::PROVEDORES).'.',
        ]);
    }

    private function baixarCapa(string $provedor, string $codigo): ?string
    {
        $remota = VideoIncorporado::capaRemota($provedor, $codigo);

        if ($remota === null) {
            return null;
        }

        try {
            $resposta = Http::timeout(8)->get($remota);

            if (! $resposta->successful() || ! str_starts_with((string) $resposta->header('Content-Type'), 'image/jpeg')) {
                return null;
            }

            $caminho = "videos/capas/{$provedor}-{$codigo}.jpg";
            Storage::disk('public')->put($caminho, $resposta->body());

            return $caminho;
        } catch (Throwable) {
            return null;
        }
    }

    private function regrasComuns(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'na_home' => ['boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'publicado' => ['boolean'],
        ];
    }
}
