<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public function created(Model $model): void
    {
        $this->log($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->log($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted');
    }

    private function log(Model $model, string $event): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->withProperties([
                'ip' => Request::ip(),
                'device' => Request::userAgent(),
                'menu' => $this->menu ?? class_basename($model),
                'action' => $event,
            ])
            ->log($event);
    }
}
