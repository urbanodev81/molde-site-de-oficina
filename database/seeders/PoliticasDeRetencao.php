<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PoliticaRetencao;
use Illuminate\Database\Seeder;

class PoliticasDeRetencao extends Seeder
{
    public function run(): void
    {
        $politicas = [
            [
                'recurso' => 'auditoria',
                'meses' => 24,
                'justificativa' => 'Rastreabilidade de quem alterou a galeria e as contas de acesso.',
                'nunca_expurgar' => false,
            ],
        ];

        foreach ($politicas as $politica) {
            PoliticaRetencao::updateOrCreate(['recurso' => $politica['recurso']], $politica);
        }
    }
}
