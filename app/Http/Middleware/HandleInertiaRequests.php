<?php

namespace App\Http\Middleware;

use App\Support\Captcha\CaptchaVerifier;
use App\Support\Perfis;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $usuario = $request->user();

        return [
            ...parent::share($request),

            'captcha' => app(CaptchaVerifier::class)->frontendConfig(),

            'push' => [
                'chave_publica' => config('webpush.vapid.public_key'),
            ],
            'auth' => [
                'user' => $usuario,

                'perfil' => Perfis::rotulo($usuario?->getRoleNames()->first()),

                'permissoes' => [
                    'videos' => (bool) $usuario?->can('videos.ver'),
                    'fotos' => (bool) $usuario?->can('fotos.ver'),
                    'oficina' => (bool) $usuario?->can('oficina.ver'),
                    'usuarios' => (bool) $usuario?->can('usuarios.ver'),
                    'auditoria' => (bool) $usuario?->can('auditoria.ver'),
                ],
            ],

            'flash' => [
                'sucesso' => fn () => $request->session()->get('sucesso'),

                'erro' => fn () => $request->session()->get('erro'),
            ],
        ];
    }
}
