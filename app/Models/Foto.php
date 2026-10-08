<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Arquivos;
use App\Support\Auditoria\Auditable;
use App\Support\TemUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Foto extends Model
{
    use Auditable;
    use SoftDeletes;
    use TemUuid;

    protected $guarded = ['id'];

    protected $attributes = ['publicada' => true, 'exemplo' => false];

    protected function casts(): array
    {
        return [
            'largura' => 'integer',
            'altura' => 'integer',
            'ordem' => 'integer',
            'publicada' => 'boolean',
            'exemplo' => 'boolean',
        ];
    }

    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('publicada', true)->orderBy('ordem')->orderByDesc('created_at');
    }

    public function url(): ?string
    {
        return Arquivos::url($this->arquivo_path);
    }
}
