<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ConfigObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ConfigObserver::class)]
class AcaoCalendario extends Model
{
    protected $table = 'acoes_calendario';

    protected $fillable = ['nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }
}
