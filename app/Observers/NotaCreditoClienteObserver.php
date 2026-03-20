<?php

declare(strict_types=1);

namespace App\Observers;

class NotaCreditoClienteObserver
{
    use LogsActivity;

    public string $menu = 'Notas de Crédito Clientes';
}
