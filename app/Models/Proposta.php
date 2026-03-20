<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\PropostaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(PropostaObserver::class)]
class Proposta extends Model
{
    use SoftDeletes;

    protected $table = 'propostas';

    protected $fillable = [
        'numero',
        'data_proposta',
        'entidade_id',
        'validade',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'data_proposta' => 'date',
            'validade' => 'date',
        ];
    }

    public function entidade(): BelongsTo
    {
        return $this->belongsTo(Entidade::class);
    }

    public function linhas(): HasMany
    {
        return $this->hasMany(LinhaProposta::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->linhas->sum('subtotal');
    }
}
