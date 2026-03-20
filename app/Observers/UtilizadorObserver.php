<?php

declare(strict_types=1);

namespace App\Observers;

class UtilizadorObserver
{
    use LogsActivity;

    public string $menu = 'Utilizadores';
}
