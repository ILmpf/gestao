<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contato;
use App\Models\Entidade;
use App\Models\FuncaoContato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contato>
 */
class ContatoFactory extends Factory
{
    protected $model = Contato::class;

    public function definition(): array
    {
        return [
            'numero' => fake()->unique()->numberBetween(1, 99999),
            'entidade_id' => Entidade::factory(),
            'primeiro_nome' => fake()->firstName(),
            'apelido' => fake()->lastName(),
            'funcao_contacto_id' => FuncaoContato::inRandomOrder()->value('id'),
            'telefone' => fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
            'notas' => fake()->optional(0.3)->sentence(8),
            'estado' => 'ativo',
        ];
    }
}
