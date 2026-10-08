<?php

declare(strict_types=1);

namespace App\Support;

final class VideoIncorporado
{
    public const PROVEDORES = [
        'youtube' => 'YouTube',
        'vimeo' => 'Vimeo',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
    ];

    public const DOMINIOS_DE_INCORPORACAO = [
        'https://www.youtube-nocookie.com',
        'https://player.vimeo.com',
        'https://www.instagram.com',
        'https://www.tiktok.com',
    ];

    public static function reconhecer(?string $entrada): ?array
    {
        $entrada = trim((string) $entrada);

        if (stripos($entrada, '<iframe') !== false) {
            if (preg_match('#<iframe[^>]*\ssrc\s*=\s*["\']([^"\']+)["\']#i', $entrada, $m) !== 1) {
                return null;
            }
            $entrada = html_entity_decode($m[1]);
        }

        if (str_starts_with($entrada, '//')) {
            $entrada = 'https:'.$entrada;
        }

        if (preg_match('#^https?://#i', $entrada) !== 1) {
            return null;
        }

        $host = strtolower((string) parse_url($entrada, PHP_URL_HOST));
        $caminho = (string) parse_url($entrada, PHP_URL_PATH);
        parse_str((string) parse_url($entrada, PHP_URL_QUERY), $consulta);

        if (preg_match('#(^|\.)(youtube\.com|youtube-nocookie\.com)$#', $host) === 1) {
            if (preg_match('#^/(?:embed|shorts|live)/([A-Za-z0-9_-]{6,20})#', $caminho, $m) === 1) {
                return self::montar('youtube', $m[1]);
            }
            $v = $consulta['v'] ?? null;

            return is_string($v) && preg_match('#^[A-Za-z0-9_-]{6,20}$#', $v) === 1
                ? self::montar('youtube', $v)
                : null;
        }

        if ($host === 'youtu.be' && preg_match('#^/([A-Za-z0-9_-]{6,20})#', $caminho, $m) === 1) {
            return self::montar('youtube', $m[1]);
        }

        if (preg_match('#(^|\.)vimeo\.com$#', $host) === 1
            && preg_match('#/(?:video/)?(\d{6,12})(?:/|$)#', $caminho, $m) === 1) {
            return self::montar('vimeo', $m[1]);
        }

        if (preg_match('#(^|\.)instagram\.com$#', $host) === 1
            && preg_match('#^/(p|reel|reels|tv)/([A-Za-z0-9_-]{5,40})#', $caminho, $m) === 1) {
            return self::montar('instagram', ($m[1] === 'reels' ? 'reel' : $m[1]).'/'.$m[2]);
        }

        if (preg_match('#(^|\.)tiktok\.com$#', $host) === 1
            && preg_match('#/(?:video|embed/v2|embed)/(\d{10,25})#', $caminho, $m) === 1) {
            return self::montar('tiktok', $m[1]);
        }

        return null;
    }

    public static function url(string $provedor, string $codigo): string
    {
        return match ($provedor) {
            'youtube' => 'https://www.youtube.com/watch?v='.$codigo,
            'vimeo' => 'https://vimeo.com/'.$codigo,
            'instagram' => 'https://www.instagram.com/'.$codigo.'/',
            'tiktok' => 'https://www.tiktok.com/embed/v2/'.$codigo,
            default => '#',
        };
    }

    public static function embed(string $provedor, string $codigo): ?string
    {
        return match ($provedor) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$codigo.'?autoplay=1&rel=0',
            'vimeo' => 'https://player.vimeo.com/video/'.$codigo.'?dnt=1&autoplay=1',
            'instagram' => 'https://www.instagram.com/'.$codigo.'/embed',
            'tiktok' => 'https://www.tiktok.com/embed/v2/'.$codigo,
            default => null,
        };
    }

    public static function capaRemota(string $provedor, string $codigo): ?string
    {
        return $provedor === 'youtube' ? 'https://i.ytimg.com/vi/'.$codigo.'/hqdefault.jpg' : null;
    }

    public static function rotulo(?string $provedor): string
    {
        return self::PROVEDORES[$provedor] ?? 'vídeo';
    }

    private static function montar(string $provedor, string $codigo): array
    {
        return compact('provedor', 'codigo');
    }
}
