<?php

declare(strict_types=1);

namespace App\Observers;

class ContaCorrenteClienteObserver
{
    use LogsActivity;

    public string $menu = 'Conta Corrente Clientes';
}
