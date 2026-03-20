<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ArtigoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ArtigoObserver::class)]
class Artigo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'artigos';

    protected $fillable = [
        'referencia',
        'nome',
        'descricao',
        'preco',
        'taxa_iva_id',
        'imagem_artigo',
        'notas',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
        ];
    }

    public function taxaIva(): BelongsTo
    {
        return $this->belongsTo(TaxaIva::class, 'taxa_iva_id');
    }
}
