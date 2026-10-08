<?php

declare(strict_types=1);

namespace App\Support\Lote;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class ListasEmLote
{
    public static function todas(): array
    {
        return [
            'videos' => [
                'modelo' => Video::class,
                'ver' => 'videos.ver',
                'gerenciar' => 'videos.gerenciar',
                'nome' => ['vídeo', 'vídeos', 'o'],
                'coluna' => 'publicado',
                'ligado' => 'Publicado',
                'desligado' => 'Fora do site',
                'ordem' => fn (Builder $q) => $q->orderBy('ordem')->orderByDesc('created_at'),
                'linha' => fn (Video $v) => ['titulo' => $v->titulo, 'detalhe' => $v->descricao, 'link' => $v->url()],
                'publicos' => fn (Builder $q) => $q->where('publicado', true),
            ],
            'usuarios' => [
                'modelo' => User::class,
                'ver' => 'usuarios.ver',
                'gerenciar' => 'usuarios.gerenciar',
                'nome' => ['conta', 'contas', 'a'],
                'coluna' => 'ativo',
                'ligado' => 'Ativa',
                'desligado' => 'Inativa',
                'ordem' => fn (Builder $q) => $q->orderBy('name'),
                'linha' => fn (User $u) => ['titulo' => $u->name, 'detalhe' => $u->email, 'link' => null],
                'publicos' => null,

                'intocavel' => fn (Model $u, Request $r, string $acao) => $u->getKey() === $r->user()?->getKey(),
                'aviso' => 'A sua própria conta ficou como estava.',
            ],
        ];
    }

    public static function de(string $lista): array
    {
        return self::todas()[$lista] ?? abort(404);
    }

    public static function chave(array $lista): string
    {
        return $lista['chave'] ?? 'uuid';
    }

    public static function regraDaChave(array $lista): string
    {
        return self::chave($lista) === 'id' ? 'integer' : 'uuid';
    }

    public static function abas(string $lista, Request $request): array
    {
        $modelo = self::de($lista)['modelo'];

        return [
            'arquivados' => $request->boolean('arquivados'),
            'totais' => [$modelo::query()->count(), $modelo::onlyTrashed()->count()],
        ];
    }

    public static function frase(array $lista, int $quantas, string $estado): string
    {
        [$um, $varios, $genero] = $lista['nome'];

        if ($quantas === 0) {
            return $genero === 'a' ? "Nenhuma {$um} precisou ser alterada." : "Nenhum {$um} precisou ser alterado.";
        }

        return $quantas === 1 ? "1 {$um} {$estado}{$genero}." : "{$quantas} {$varios} {$estado}{$genero}s.";
    }

    public static function situacao(array $lista, Model $item): string
    {
        if ($item->trashed()) {
            return $lista['nome'][2] === 'a' ? 'Arquivada' : 'Arquivado';
        }

        if ($lista['coluna'] === null) {
            return '';
        }

        return $item->{$lista['coluna']} ? $lista['ligado'] : $lista['desligado'];
    }
}
