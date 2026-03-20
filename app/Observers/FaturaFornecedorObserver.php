<?php

declare(strict_types=1);

namespace App\Observers;

class FaturaFornecedorObserver
{
    use LogsActivity;

    public string $menu = 'Faturas Fornecedores';
}
