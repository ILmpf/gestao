<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'logo',
        'morada',
        'codigo_postal',
        'cidade',
        'nif',
    ];

    protected function casts(): array
    {
        return [
            'nif' => 'encrypted',
        ];
    }
}
