<?php

declare(strict_types=1);

namespace App\Observers;

class ConfigObserver
{
    use LogsActivity;

    public string $menu = 'Configurações';
}
