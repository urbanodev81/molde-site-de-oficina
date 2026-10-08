<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Perfis;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PerfisESeguranca extends Seeder
{
    private const PERMISSOES = [
        'Galeria do site' => [
            'fotos.ver',
            'fotos.gerenciar',
            'videos.ver',
            'videos.gerenciar',
        ],
        'Dados da oficina' => [
            'oficina.ver',
            'oficina.gerenciar',
        ],
        'Administração' => [
            'usuarios.ver',
            'usuarios.gerenciar',
            'auditoria.ver',
        ],
    ];

    private const PERFIS = [
        Perfis::ADMINISTRADOR => '*',
        Perfis::OFICINA => [
            'fotos.ver', 'fotos.gerenciar',
            'videos.ver', 'videos.gerenciar',
            'oficina.ver', 'oficina.gerenciar',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $todas = [];

        foreach (self::PERMISSOES as $permissoes) {
            foreach ($permissoes as $nome) {
                Permission::findOrCreate($nome, 'web');
                $todas[] = $nome;
            }
        }

        foreach (self::PERFIS as $perfil => $permissoes) {
            $papel = Role::findOrCreate($perfil, 'web');

            $papel->syncPermissions($permissoes === '*' ? $todas : $permissoes);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function grupos(): array
    {
        return self::PERMISSOES;
    }
}
