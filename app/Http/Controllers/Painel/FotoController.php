<?php

declare(strict_types=1);

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Foto;
use App\Support\Arquivos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FotoController extends Controller
{
    public function index(Request $request): Response
    {
        $arquivadas = $request->boolean('arquivadas');

        return Inertia::render('Painel/Fotos/Index', [
            'fotos' => Foto::query()->when($arquivadas, fn ($q) => $q->onlyTrashed())
                ->orderBy('ordem')->orderByDesc('created_at')
                ->paginate(24)
                ->withQueryString()
                ->through(fn (Foto $f) => [
                    'uuid' => $f->uuid,
                    'titulo' => $f->titulo,
                    'descricao' => $f->descricao,
                    'url' => $f->url(),
                    'ordem' => $f->ordem,
                    'publicada' => $f->publicada,
                    'exemplo' => $f->exemplo,
                    'arquivada' => $f->trashed(),
                ]),
            'arquivadas' => $arquivadas,
            'totais' => ['ativas' => Foto::query()->count(), 'arquivadas' => Foto::onlyTrashed()->count()],
            'podeGerenciar' => $request->user()?->can('fotos.gerenciar') ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([

            'arquivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            ...$this->regrasComuns(),
        ]);

        $imagem = $request->file('arquivo');
        $medidas = @getimagesize($imagem->getRealPath());

        Foto::create([
            ...collect($dados)->except('arquivo')->all(),
            'arquivo_path' => Arquivos::guardar($imagem, 'fotos'),
            'largura' => $medidas[0] ?? null,
            'altura' => $medidas[1] ?? null,
        ]);

        return back()->with('sucesso', 'Foto adicionada.');
    }

    public function update(Request $request, Foto $foto): RedirectResponse
    {
        $foto->update($request->validate($this->regrasComuns()));

        return back()->with('sucesso', 'Foto atualizada.');
    }

    public function destroy(Foto $foto): RedirectResponse
    {
        $foto->delete();

        return back()->with('sucesso', 'Foto arquivada. Ela está na aba Arquivadas.');
    }

    private function regrasComuns(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'publicada' => ['boolean'],
        ];
    }
}
