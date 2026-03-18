<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileServeController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        $fullPath = 'private/'.$path;

        abort_unless(Storage::exists($fullPath), 404);

        return Storage::download($fullPath);
    }
}
