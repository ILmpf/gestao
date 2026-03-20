<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ConfigObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ConfigObserver::class)]
class TaxaIva extends Model
{
    protected $table = 'taxas_iva';

    protected $fillable = ['nome', 'taxa', 'ativo'];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'taxa' => 'decimal:2',
        ];
    }
}
