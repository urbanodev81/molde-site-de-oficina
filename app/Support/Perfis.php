<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

final class Perfis
{
    public const ADMINISTRADOR = 'administrador';

    public const OFICINA = 'oficina';

    private const ROTULOS = [
        self::ADMINISTRADOR => 'Administrador',
        self::OFICINA => 'Oficina',
    ];

    private const DESCRICOES = [
        self::ADMINISTRADOR => 'Tudo, inclusive contas de acesso e trilha de auditoria.',
        self::OFICINA => 'A galeria do site (fotos e vídeos) e os dados da oficina: contato, endereço, mapa e horário. Não mexe em conta de acesso.',
    ];

    public static function rotulo(?string $nome): ?string
    {
        if ($nome === null || $nome === '') {
            return null;
        }

        return self::ROTULOS[$nome] ?? Str::headline($nome);
    }

    public static function descricao(?string $nome): ?string
    {
        return $nome === null ? null : (self::DESCRICOES[$nome] ?? null);
    }
}
