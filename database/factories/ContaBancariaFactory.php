<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContaBancaria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContaBancaria>
 */
class ContaBancariaFactory extends Factory
{
    protected $model = ContaBancaria::class;

    private static array $bancos = [
        ['nome' => 'Caixa Geral de Depósitos',  'bic' => 'CGDIPTPL'],
        ['nome' => 'Millennium BCP',             'bic' => 'BCOMPTPL'],
        ['nome' => 'Novo Banco',                 'bic' => 'BESCPTPL'],
        ['nome' => 'Banco Santander Totta',      'bic' => 'TOTAPTPL'],
        ['nome' => 'BPI — Banco Português de Investimento', 'bic' => 'BBPIPTPL'],
        ['nome' => 'Montepio Geral',             'bic' => 'MPIOPTPL'],
        ['nome' => 'Banco CTT',                  'bic' => 'CTTVPTPL'],
    ];

    public function definition(): array
    {
        $banco = fake()->randomElement(self::$bancos);

        return [
            'nome' => $banco['nome'],
            'iban' => 'PT50'.fake()->unique()->numerify('#####################'),
            'bic' => $banco['bic'],
            'ativa' => true,
        ];
    }
}
