<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinhaProposta extends Model
{
    protected $table = 'linhas_proposta';

    protected $fillable = [
        'proposta_id',
        'artigo_id',
        'entidade_fornecedor_id',
        'taxa_iva_id',
        'quantidade',
        'preco_unitario',
        'preco_custo',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:2',
            'preco_unitario' => 'decimal:2',
            'preco_custo' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function artigo(): BelongsTo
    {
        return $this->belongsTo(Artigo::class);
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'entidade_fornecedor_id');
    }

    public function taxaIva(): BelongsTo
    {
        return $this->belongsTo(TaxaIva::class, 'taxa_iva_id');
    }
}
