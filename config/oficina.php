<?php

declare(strict_types=1);

return [

    'dominio' => env('OFICINA_DOMINIO', 'https://amelhoroficina.example'),

    'confirmado' => [
        'whatsapp' => '5511900000000',
    ],

    'exemplo' => [
        'email' => 'contato@amelhoroficina.example',
        'instagram' => 'amelhoroficina',
        'logradouro' => 'Rua Exemplo, 000',
        'bairro' => 'Bairro',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'cep' => '00000-000',
        'latitude' => -23.5505,
        'longitude' => -46.6333,

        'horario' => [
            1 => ['abre' => '08:00', 'fecha' => '18:00'],
            2 => ['abre' => '08:00', 'fecha' => '18:00'],
            3 => ['abre' => '08:00', 'fecha' => '18:00'],
            4 => ['abre' => '08:00', 'fecha' => '18:00'],
            5 => ['abre' => '08:00', 'fecha' => '18:00'],
            6 => ['abre' => '08:00', 'fecha' => '12:00'],
        ],
    ],

];
