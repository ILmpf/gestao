<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ViesController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pais' => ['required', 'string', 'size:2'],
            'vat_number' => ['required', 'string', 'max:20'],
        ]);

        $countryCode = strtoupper((string) $validated['pais']);
        $vatNumber = (string) preg_replace('/[\s\-]/', '', (string) $validated['vat_number']);

        if (str_starts_with(strtoupper($vatNumber), $countryCode)) {
            $vatNumber = substr($vatNumber, strlen($countryCode));
        }

        $url = sprintf(
            'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/%s/vat/%s',
            $countryCode,
            $vatNumber,
        );

        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            return response()->json([
                'valid' => false,
                'message' => 'Serviço VIES temporariamente indisponível. Tente novamente.',
            ], 503);
        }

        $data = $response->json();

        if (empty($data['isValid'])) {
            return response()->json([
                'valid' => false,
                'message' => 'NIF inválido ou não encontrado no VIES.',
            ]);
        }

        $nome = isset($data['name']) && $data['name'] !== '---' ? trim((string) $data['name']) : null;
        $morada = isset($data['address']) && $data['address'] !== '---' ? trim((string) $data['address']) : null;

        return response()->json([
            'valid' => true,
            'nome' => $nome,
            'morada' => $morada,
        ]);
    }
}
