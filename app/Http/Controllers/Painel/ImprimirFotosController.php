<?php

declare(strict_types=1);

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Foto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ImprimirFotosController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dados = $request->validate([
            'ids' => ['nullable', 'array', 'max:100'],
            'ids.*' => ['uuid'],
        ]);

        $fotos = Foto::withTrashed()
            ->whereIn('uuid', $dados['ids'] ?? [])
            ->orderBy('ordem')->orderByDesc('created_at')
            ->get()
            ->map(fn (Foto $f) => [
                'titulo' => $f->titulo,
                'descricao' => $f->descricao,
                'url' => $f->url(),
                'quando' => $f->created_at?->format('d/m/Y'),
                'situacao' => $f->trashed() ? 'Arquivada' : ($f->publicada ? 'Publicada' : 'Fora do site'),
            ]);

        return view('painel.imprimir-fotos', ['fotos' => $fotos]);
    }
}
