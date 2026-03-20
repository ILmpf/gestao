<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\NotaCreditoClienteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(NotaCreditoClienteObserver::class)]
class NotaCreditoCliente extends Model
{
    use SoftDeletes;

    protected $table = 'notas_credito_clientes';

    protected $fillable = [
        'numero',
        'data_nota_credito',
        'entidade_id',
        'encomenda_cliente_id',
        'fatura_cliente_id',
        'valor_total',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'data_nota_credito' => 'date',
            'valor_total' => 'decimal:2',
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

    public function faturaCliente(): BelongsTo
    {
        return $this->belongsTo(FaturaCliente::class);
    }
}
