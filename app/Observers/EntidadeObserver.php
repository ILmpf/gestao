<?php

declare(strict_types=1);

namespace App\Observers;

class EntidadeObserver
{
    use LogsActivity;

    public string $menu = 'Entidades';
}
