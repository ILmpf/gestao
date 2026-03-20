<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ContatoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ContatoObserver::class)]
class Contato extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contatos';

    protected $fillable = [
        'numero',
        'entidade_id',
        'primeiro_nome',
        'apelido',
        'funcao_contacto_id',
        'telefone',
        'telemovel',
        'email',
        'notas',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'telefone' => 'encrypted',
            'telemovel' => 'encrypted',
            'email' => 'encrypted',
        ];
    }

    public function entidade(): BelongsTo
    {
        return $this->belongsTo(Entidade::class);
    }

    public function funcaoContacto(): BelongsTo
    {
        return $this->belongsTo(FuncaoContato::class, 'funcao_contacto_id');
    }
}
