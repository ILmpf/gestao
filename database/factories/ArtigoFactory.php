<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Artigo;
use App\Models\TaxaIva;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artigo>
 */
class ArtigoFactory extends Factory
{
    protected $model = Artigo::class;

    private static array $produtos = [
        'SRV' => [
            'Consultoria Técnica', 'Suporte e Manutenção', 'Desenvolvimento Web',
            'Formação Profissional', 'Auditoria de Sistemas', 'Gestão de Projeto',
            'Assessoria TI', 'Implementação de Software', 'Migração de Dados',
            'Análise e Desenvolvimento', 'Consultoria de Segurança', 'Helpdesk Mensal',
        ],
        'PRD' => [
            'Equipamento Informático', 'Licença de Software Anual', 'Servidor Cloud Mensal',
            'Switch de Rede 24 portas', 'Disco SSD Externo', 'Monitor LED 27"',
            'Impressora Laser', 'Antivírus Empresarial', 'UPS de Proteção 1000VA',
            'Router Empresarial', 'NAS de Armazenamento', 'Webcam HD',
        ],
        'SUP' => [
            'Material de Escritório', 'Papel A4 (caixa 5 resmas)', 'Tinteiros Originais',
            'Cabo de Rede Cat6 (10m)', 'Adaptador HDMI 4K', 'Hub USB-C 7 portas',
            'Etiquetas Adesivas A4', 'Pastas e Arquivadores', 'Canetas e Marcadores',
            'Bateria Universal', 'Mouse Wireless', 'Teclado Bluetooth',
        ],
    ];

    public function definition(): array
    {
        $category = fake()->randomElement(array_keys(self::$produtos));
        $nome = fake()->randomElement(self::$produtos[$category]);

        return [
            'referencia' => strtoupper($category).'-'.fake()->unique()->numerify('###'),
            'nome' => $nome,
            'descricao' => fake()->sentence(10),
            'preco' => fake()->randomFloat(2, 4, 2000),
            'taxa_iva_id' => TaxaIva::inRandomOrder()->value('id'),
            'estado' => 'ativo',
        ];
    }
}
