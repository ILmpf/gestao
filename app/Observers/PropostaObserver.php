<?php

declare(strict_types=1);

namespace App\Observers;

class PropostaObserver
{
    use LogsActivity;

    public string $menu = 'Propostas';
}
