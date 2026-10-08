<?php

declare(strict_types=1);

namespace App\Support;

final class MapaIncorporado
{
    public const DOMINIO = 'https://www.google.com';

    private const PREFIXO = 'https://www.google.com/maps/embed?';

    public static function reconhecer(?string $entrada): ?string
    {
        $entrada = trim((string) $entrada);

        if ($entrada === '') {
            return null;
        }

        if (preg_match('/<iframe[^>]*\ssrc\s*=\s*["\']([^"\']+)["\']/i', $entrada, $achado) === 1) {
            $entrada = html_entity_decode($achado[1], ENT_QUOTES | ENT_HTML5);
        }

        if (! str_starts_with($entrada, self::PREFIXO) || strlen($entrada) > 2000) {
            return null;
        }

        return preg_match('/^[A-Za-z0-9\-._~:\/?#\[\]@!$&()*+,;=%]+$/', $entrada) === 1 ? $entrada : null;
    }
}
