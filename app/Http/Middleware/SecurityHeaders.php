<?php

namespace App\Http\Middleware;

use App\Support\MapaIncorporado;
use App\Support\VideoIncorporado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public const HASH_DO_SCRIPT_DO_SITE = 'sha256-0BVSEn8UIDWgcpc+EcS8OGqU2/agCZ86uaxFuxsNhZY=';

    private const VLIBRAS = 'https://vlibras.gov.br https://cdn.jsdelivr.net';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->routeIs('site.*')) {
            $response->headers->set('X-Frame-Options', 'DENY');

            if (app()->isProduction()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }

            $response->headers->set('Content-Security-Policy', "default-src 'self'; "
                ."script-src 'self' '".self::HASH_DO_SCRIPT_DO_SITE."' ".self::VLIBRAS.'; '
                ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com ".self::VLIBRAS.'; '
                ."font-src 'self' https://fonts.gstatic.com ".self::VLIBRAS.'; '
                ."img-src 'self' data: ".self::VLIBRAS.'; '
                ."connect-src 'self' ".self::VLIBRAS.'; '

                .'frame-src '.implode(' ', [...VideoIncorporado::DOMINIOS_DE_INCORPORACAO, MapaIncorporado::DOMINIO, 'https://vlibras.gov.br']).'; '
                ."manifest-src 'self'; "
                ."object-src 'none'; "
                ."base-uri 'self'; "
                ."form-action 'self'; "
                ."frame-ancestors 'none'");

            return $response;
        }

        if (app()->isProduction()) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

            $csp = "default-src 'self'; "
                ."script-src 'self' 'unsafe-inline' https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."img-src 'self' data: blob: https:; "
                ."font-src 'self' data: https://fonts.bunny.net https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."connect-src 'self' https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."frame-src 'self' https://vlibras.gov.br https://cdn.jsdelivr.net ".MapaIncorporado::DOMINIO.'; '
                ."media-src 'self' blob:; "
                ."object-src 'none'; "

                ."worker-src 'self' blob:; "
                ."manifest-src 'self'; "
                ."base-uri 'self'; "
                ."form-action 'self';";
        } else {
            $vitePort = env('VITE_PORT', 5183);
            $viteOrigin = "http://localhost:{$vitePort}";
            $viteWs = "ws://localhost:{$vitePort}";

            $csp = "default-src 'self' {$viteOrigin} {$viteWs}; "
                ."script-src 'self' 'unsafe-inline' 'unsafe-eval' {$viteOrigin} https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."style-src 'self' 'unsafe-inline' {$viteOrigin} https://fonts.bunny.net https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."img-src 'self' data: blob: {$viteOrigin} https:; "
                ."font-src 'self' data: {$viteOrigin} https://fonts.bunny.net https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."connect-src 'self' {$viteOrigin} {$viteWs} https://vlibras.gov.br https://cdn.jsdelivr.net; "
                ."frame-src 'self' https://vlibras.gov.br https://cdn.jsdelivr.net ".MapaIncorporado::DOMINIO.'; '
                ."media-src 'self' blob:; "
                ."object-src 'none'; "

                ."worker-src 'self' blob:; "
                ."manifest-src 'self'; "
                ."base-uri 'self'; "
                ."form-action 'self';";
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
