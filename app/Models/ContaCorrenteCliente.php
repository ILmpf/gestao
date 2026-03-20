<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ContaCorrenteClienteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ContaCorrenteClienteObserver::class)]
class ContaCorrenteCliente extends Model
{
    protected $table = 'conta_corrente_clientes';

    protected $fillable = [
        'entidade_id',
        'encomenda_cliente_id',
        'descricao',
        'tipo',
        'debito',
        'credito',
        'saldo',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'debito' => 'decimal:2',
            'credito' => 'decimal:2',
            'saldo' => 'decimal:2',
            'data' => 'date',
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


    public static function proximoSaldo(int $entidadeId, float $debito, float $credito): float
    {
        $ultimoSaldo = static::where('entidade_id', $entidadeId)
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->value('saldo') ?? 0.0;

        return round((float) $ultimoSaldo + $debito - $credito, 2);
    }
}
