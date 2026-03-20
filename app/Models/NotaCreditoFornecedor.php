<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\NotaCreditoFornecedorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(NotaCreditoFornecedorObserver::class)]
class NotaCreditoFornecedor extends Model
{
    use SoftDeletes;

    protected $table = 'notas_credito_fornecedores';

    protected $fillable = [
        'numero',
        'data_nota_credito',
        'entidade_id',
        'encomenda_fornecedor_id',
        'fatura_fornecedor_id',
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

    public function encomendaFornecedor(): BelongsTo
    {
        return $this->belongsTo(EncomendaFornecedor::class);
    }

    public function faturaFornecedor(): BelongsTo
    {
        return $this->belongsTo(FaturaFornecedor::class);
    }
}
