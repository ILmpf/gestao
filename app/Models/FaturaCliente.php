<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\FaturaClienteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(FaturaClienteObserver::class)]
class FaturaCliente extends Model
{
    use SoftDeletes;

    protected $table = 'faturas_clientes';

    protected $fillable = [
        'numero',
        'data_fatura',
        'data_vencimento',
        'entidade_id',
        'encomenda_cliente_id',
        'valor_total',
        'caminho_documento',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'data_fatura' => 'date',
            'data_vencimento' => 'date',
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

    public function notasCredito(): HasMany
    {
        return $this->hasMany(NotaCreditoCliente::class);
    }
}
