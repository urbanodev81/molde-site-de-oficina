<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Auditoria\Auditable;
use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    use Auditable;

    protected $table = 'oficina';

    protected $guarded = ['id'];

    public const BLOCOS = [
        'email' => ['email'],
        'instagram' => ['instagram'],
        'endereco' => ['logradouro', 'bairro', 'cidade', 'uf', 'cep'],
        'horario' => ['horario'],
    ];

    private const DIAS = [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];

    private const DIAS_SCHEMA = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

    private const ORDEM = [1, 2, 3, 4, 5, 6, 0];

    private static ?self $daRequisicao = null;

    protected function casts(): array
    {
        return [
            'horario' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        $esquecer = fn () => self::$daRequisicao = null;

        static::saved($esquecer);
        static::deleted($esquecer);
    }

    public static function atual(): self
    {
        return self::$daRequisicao ??= self::query()->first() ?? new self;
    }

    public static function esquecer(): void
    {
        self::$daRequisicao = null;
    }

    public function valor(string $campo): mixed
    {
        $doPainel = $this->getAttribute($campo);

        if ($doPainel !== null && $doPainel !== '' && $doPainel !== []) {
            return $doPainel;
        }

        return config("oficina.confirmado.{$campo}") ?? config("oficina.exemplo.{$campo}");
    }

    public function exemplo(string $bloco): bool
    {
        foreach (self::BLOCOS[$bloco] ?? [] as $campo) {
            $doPainel = $this->getAttribute($campo);
            $vazio = $doPainel === null || $doPainel === '' || $doPainel === [];

            if ($vazio && config("oficina.confirmado.{$campo}") === null && config("oficina.exemplo.{$campo}") !== null) {
                return true;
            }
        }

        return false;
    }

    public function temExemplo(): bool
    {
        foreach (array_keys(self::BLOCOS) as $bloco) {
            if ($this->exemplo($bloco)) {
                return true;
            }
        }

        return false;
    }

    public function numeroDoWhatsapp(): string
    {
        return (string) $this->valor('whatsapp');
    }

    public function numeroDeLigar(): string
    {
        return (string) ($this->valor('telefone') ?: $this->numeroDoWhatsapp());
    }

    public static function formatar(string $digitos): string
    {
        $local = str_starts_with($digitos, '55') && strlen($digitos) >= 12 ? substr($digitos, 2) : $digitos;

        return preg_match('/^(\d{2})(\d{4,5})(\d{4})$/', $local, $p) === 1 ? "({$p[1]}) {$p[2]}-{$p[3]}" : $digitos;
    }

    public static function formatarInternacional(string $digitos): string
    {
        return preg_match('/^55(\d{2})(\d{4,5})(\d{4})$/', $digitos, $p) === 1 ? "+55-{$p[1]}-{$p[2]}-{$p[3]}" : '+'.$digitos;
    }

    public function linkWhatsapp(): string
    {
        return 'https://wa.me/'.$this->numeroDoWhatsapp();
    }

    public function instagramUrl(): string
    {
        return 'https://www.instagram.com/'.$this->valor('instagram').'/';
    }

    public function enderecoLinha1(): string
    {
        return implode(' — ', array_filter([$this->valor('logradouro'), $this->valor('bairro')]));
    }

    public function enderecoLinha2(): string
    {
        return implode(' · ', array_filter([$this->valor('cidade'), $this->valor('uf'), $this->valor('cep')]));
    }

    public function enderecoCompleto(): string
    {
        return $this->enderecoLinha1().', '.$this->valor('cidade').' · '.$this->valor('uf');
    }

    public function linkDoMapa(): string
    {
        $busca = $this->exemplo('endereco')
            ? 'A melhor oficina '.$this->valor('cidade')
            : 'A melhor oficina, '.$this->valor('logradouro').', '.$this->valor('cidade').' - '.$this->valor('uf');

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($busca);
    }

    public function semana(): array
    {
        $horario = $this->valor('horario') ?? [];
        $semana = [];

        foreach (array_keys(self::DIAS) as $dia) {
            $do = $horario[$dia] ?? $horario[(string) $dia] ?? null;
            $semana[$dia] = is_array($do) && filled($do['abre'] ?? null) && filled($do['fecha'] ?? null)
                ? ['abre' => (string) $do['abre'], 'fecha' => (string) $do['fecha']]
                : null;
        }

        return $semana;
    }

    public function horarioAgrupado(): array
    {
        $semana = $this->semana();
        $grupos = [];

        foreach (self::ORDEM as $dia) {
            $ultimo = array_key_last($grupos);

            if ($ultimo !== null && $grupos[$ultimo]['horas'] === $semana[$dia]) {
                $grupos[$ultimo]['dias'][] = $dia;

                continue;
            }

            $grupos[] = ['dias' => [$dia], 'horas' => $semana[$dia]];
        }

        return array_map(function (array $grupo): array {
            $dias = $grupo['dias'];
            $primeiro = self::DIAS[$dias[0]];
            $ultimoDia = self::DIAS[$dias[array_key_last($dias)]];

            $rotulo = match (count($dias)) {
                1 => $primeiro,
                2 => $primeiro.' e '.mb_strtolower($ultimoDia),
                default => $primeiro.' a '.mb_strtolower($ultimoDia),
            };

            if ($grupo['horas'] === null && in_array(0, $dias, true)) {
                $rotulo .= ' e feriado';
            }

            return [
                'dias' => $dias,
                'rotulo' => $rotulo,
                'texto' => $grupo['horas'] === null ? 'Fechado' : self::hora($grupo['horas']['abre']).' às '.self::hora($grupo['horas']['fecha']),
                'abre' => $grupo['horas']['abre'] ?? null,
                'fecha' => $grupo['horas']['fecha'] ?? null,
            ];
        }, $grupos);
    }

    public static function hora(string $hhmm): string
    {
        [$h, $m] = array_pad(explode(':', $hhmm), 2, '00');

        return $h.'h'.($m === '00' ? '' : $m);
    }

    public function horarioEmFrase(): string
    {
        return implode('; ', array_map(
            fn (array $g) => mb_strtolower($g['rotulo']).', '.mb_strtolower($g['texto']),
            $this->horarioAgrupado(),
        )).'.';
    }

    public function paraOJavascript(): array
    {
        return [
            'contato' => [
                'whatsapp' => $this->numeroDoWhatsapp(),
                'telefone' => '+'.$this->numeroDeLigar(),
                'instagram' => $this->instagramUrl(),
            ],
            'endereco' => [
                'linha' => $this->enderecoCompleto(),
                'mapa' => $this->linkDoMapa(),
            ],
            'horario' => array_values(array_map(
                fn (array $g) => ['dias' => $g['dias'], 'abre' => $g['abre'], 'fecha' => $g['fecha']],
                array_filter($this->horarioAgrupado(), fn (array $g) => $g['abre'] !== null),
            )),
        ];
    }

    public function paraOJsonLd(): array
    {
        return [
            'telephone' => self::formatarInternacional($this->numeroDeLigar()),
            'email' => $this->valor('email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->valor('logradouro'),
                'addressLocality' => $this->valor('cidade'),
                'addressRegion' => $this->valor('uf'),
                'postalCode' => $this->valor('cep'),
                'addressCountry' => 'BR',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $this->valor('latitude'),
                'longitude' => $this->valor('longitude'),
            ],
            'openingHoursSpecification' => array_values(array_map(
                fn (array $g) => [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => array_map(fn (int $d) => self::DIAS_SCHEMA[$d], $g['dias']),
                    'opens' => $g['abre'],
                    'closes' => $g['fecha'],
                ],
                array_filter($this->horarioAgrupado(), fn (array $g) => $g['abre'] !== null),
            )),
            'sameAs' => [$this->instagramUrl()],
        ];
    }

    public function trechoDoJsonLd(): string
    {
        return substr(json_encode(
            $this->paraOJsonLd(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        ), 1, -1);
    }
}
