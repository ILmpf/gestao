<?php

declare(strict_types=1);

namespace App\Observers;

class FaturaClienteObserver
{
    use LogsActivity;

    public string $menu = 'Faturas Clientes';
}
