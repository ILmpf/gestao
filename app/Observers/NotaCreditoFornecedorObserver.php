<?php

declare(strict_types=1);

namespace App\Observers;

class NotaCreditoFornecedorObserver
{
    use LogsActivity;

    public string $menu = 'Notas de Crédito Fornecedores';
}
