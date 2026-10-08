<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Arquivos;
use App\Support\Auditoria\Auditable;
use App\Support\TemUuid;
use App\Support\VideoIncorporado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use Auditable;
    use SoftDeletes;
    use TemUuid;

    public const LIMITE_NA_HOME = 2;

    public const CAPA_PADRAO = '/assets/img/video-sem-capa.svg';

    protected $guarded = ['id'];

    protected $attributes = ['publicado' => true, 'na_home' => false, 'exemplo' => false];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'na_home' => 'boolean',
            'publicado' => 'boolean',
            'exemplo' => 'boolean',
        ];
    }

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('publicado', true)->orderBy('ordem')->orderByDesc('created_at');
    }

    public function scopeDaHome(Builder $query): Builder
    {
        return $query->publicados()->where('na_home', true)->limit(self::LIMITE_NA_HOME);
    }

    public function url(): string
    {
        return VideoIncorporado::url($this->provedor, $this->codigo);
    }

    public function embed(): ?string
    {
        return VideoIncorporado::embed($this->provedor, $this->codigo);
    }

    public function capa(): string
    {
        return Arquivos::url($this->capa_path) ?? self::CAPA_PADRAO;
    }
}
