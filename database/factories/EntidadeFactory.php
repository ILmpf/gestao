<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entidade;
use App\Models\Pais;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entidade>
 */
class EntidadeFactory extends Factory
{
    protected $model = Entidade::class;

    private static array $cidades = [
        'Lisboa', 'Porto', 'Braga', 'Coimbra', 'Aveiro', 'Faro',
        'Setúbal', 'Évora', 'Viseu', 'Guimarães', 'Leiria', 'Funchal',
        'Castelo Branco', 'Viana do Castelo', 'Beja', 'Portalegre',
    ];

    private static array $sufixos = [
        ', Lda', ', SA', ', Unipessoal Lda', ' & Associados', ', SRL', ' — Serviços',
    ];

    public function definition(): array
    {
        return [
            'tipos' => ['cliente'],
            'numero_cliente' => null,
            'numero_fornecedor' => null,
            'nif' => fake()->unique()->numerify('5########'),
            'nome' => fake()->company().fake()->randomElement(self::$sufixos),
            'morada' => fake()->streetAddress(),
            'codigo_postal' => fake()->numerify('####-###'),
            'cidade' => fake()->randomElement(self::$cidades),
            'pais_id' => Pais::where('codigo', 'PT')->value('id'),
            'telefone' => fake()->numerify('2########'),
            'email' => fake()->unique()->safeEmail(),
            'prazo_pagamento_dias' => fake()->randomElement([null, 30, 45, 60, 90]),
            'estado' => 'ativo',
        ];
    }

    public function cliente(): static
    {
        return $this->state(fn () => ['tipos' => ['cliente']]);
    }

    public function fornecedor(): static
    {
        return $this->state(fn () => ['tipos' => ['fornecedor']]);
    }
}
