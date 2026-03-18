<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::firstOrCreate(['id' => 1], [
            'nome'         => 'A Minha Empresa',
            'logo'         => null,
            'morada'       => null,
            'codigo_postal'=> null,
            'cidade'       => null,
            'nif'          => null,
        ]);
    }
}