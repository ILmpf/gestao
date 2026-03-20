<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\EncomendaFornecedorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(EncomendaFornecedorObserver::class)]
class EncomendaFornecedor extends Model
{
    use SoftDeletes;

    protected $table = 'encomendas_fornecedores';

    protected $fillable = [
        'numero',
        'data_encomenda',
        'entidade_id',
        'encomenda_cliente_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'data_encomenda' => 'date',
        ];
    }

    public function entidade(): BelongsTo
    {
        return $this->belongsTo(Entidade::class);
    }

    public function encomendaCliente(): BelongsTo
    {
        return $this->belongsTo(EncomendaCliente::class);
    }

    public function linhas(): HasMany
    {
        return $this->hasMany(LinhaEncomendaFornecedor::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->linhas->sum('subtotal');
    }
}
