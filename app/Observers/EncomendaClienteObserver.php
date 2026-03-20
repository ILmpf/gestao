<?php

declare(strict_types=1);

namespace App\Observers;

class EncomendaClienteObserver
{
    use LogsActivity;

    public string $menu = 'Encomendas Clientes';
}
