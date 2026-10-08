<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Perfis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PerfisESeguranca::class,
            PoliticasDeRetencao::class,
        ]);

        if (app()->environment('production')) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@amelhoroficina.example'],
            [
                'name' => 'Administração',
                'password' => Hash::make('password'),
                'ativo' => true,
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles([Perfis::ADMINISTRADOR]);
    }
}
