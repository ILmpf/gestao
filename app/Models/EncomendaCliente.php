<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\EncomendaClienteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(EncomendaClienteObserver::class)]
class EncomendaCliente extends Model
{
    use SoftDeletes;

    protected $table = 'encomendas_clientes';

    protected $fillable = [
        'numero',
        'data_encomenda',
        'entidade_id',
        'proposta_id',
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

    public function proposta(): BelongsTo
    {
        return $this->belongsTo(Proposta::class);
    }

    public function linhas(): HasMany
    {
        return $this->hasMany(LinhaEncomendaCliente::class);
    }

    public function encomendasFornecedor(): HasMany
    {
        return $this->hasMany(EncomendaFornecedor::class);
    }

    public function movimentosCC(): HasMany
    {
        return $this->hasMany(ContaCorrenteCliente::class, 'encomenda_cliente_id');
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(FaturaCliente::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->linhas->sum(function (LinhaEncomendaCliente $linha): float {
            $taxa = $linha->taxaIva?->taxa ?? 0;

            return round((float) $linha->subtotal * (1 + $taxa / 100), 2);
        });
    }
}
