<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\EntidadeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(EntidadeObserver::class)]
class Entidade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entidades';

    protected $fillable = [
        'tipos',
        'numero_cliente',
        'numero_fornecedor',
        'nif',
        'nif_hash',
        'nome',
        'morada',
        'codigo_postal',
        'cidade',
        'pais_id',
        'telefone',
        'telemovel',
        'website',
        'email',
        'notas',
        'prazo_pagamento_dias',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tipos' => 'array',
            'nif' => 'encrypted',
            'nome' => 'encrypted',
            'morada' => 'encrypted',
            'email' => 'encrypted',
            'telefone' => 'encrypted',
            'telemovel' => 'encrypted',
        ];
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    #[Scope]
    protected function clientes(Builder $query): Builder
    {
        return $query->whereJsonContains('tipos', 'cliente');
    }

    #[Scope]
    protected function fornecedores(Builder $query): Builder
    {
        return $query->whereJsonContains('tipos', 'fornecedor');
    }
}
