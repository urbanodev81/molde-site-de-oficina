<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Foto;
use App\Models\Video;
use Illuminate\Database\Seeder;

class GaleriaDeExemplo extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('GaleriaDeExemplo não roda em produção.');

            return;
        }

        $videos = [
            ['dQw4w9WgXcQ', 'Vídeo de exemplo 1', 'Aqui entra o vídeo da oficina: o serviço sendo feito, com a explicação do que foi trocado e por quê.'],
            ['M7lc1UVf-VE', 'Vídeo de exemplo 2', 'Cada vídeo ganha um título e um descritivo, e abre grande sem sair do site.'],
        ];

        foreach ($videos as $ordem => [$codigo, $titulo, $descricao]) {
            Video::updateOrCreate(
                ['provedor' => 'youtube', 'codigo' => $codigo],
                [
                    'titulo' => $titulo,
                    'descricao' => $descricao,
                    'capa_path' => "sementes/galeria/video-{$codigo}.jpg",
                    'na_home' => true,
                    'ordem' => $ordem,
                    'publicado' => true,
                    'exemplo' => true,
                ],
            );
        }

        $fotos = [
            ['freios', 'Troca de pastilhas e discos', 'Pastilhas e discos dianteiros novos, com sangria do fluido. O disco antigo voltou com o cliente.'],
            ['suspensao', 'Suspensão dianteira refeita', 'Amortecedores, batentes e bieletas trocados depois do barulho em lombada.'],
            ['motor', 'Correia dentada e tensor', "Kit de correia, tensor e bomba d'água trocados juntos, na quilometragem certa."],
            ['scanner', 'Diagnóstico por scanner', 'Luz da injeção acesa: leitura dos códigos e teste dos sensores antes de trocar qualquer peça.'],
            ['oleo', 'Troca de óleo e filtros', 'Óleo na especificação do manual, filtro de óleo, de ar e de cabine.'],
            ['arrefecimento', 'Limpeza do arrefecimento', 'Radiador limpo, aditivo novo e mangueiras revisadas depois de um superaquecimento.'],
        ];

        foreach ($fotos as $ordem => [$arquivo, $titulo, $descricao]) {
            Foto::updateOrCreate(
                ['arquivo_path' => "sementes/galeria/exemplo-{$arquivo}.svg"],
                [
                    'titulo' => $titulo,
                    'descricao' => $descricao,
                    'largura' => 800,
                    'altura' => 600,
                    'ordem' => $ordem,
                    'publicada' => true,
                    'exemplo' => true,
                ],
            );
        }
    }
}
