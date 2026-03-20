<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Artigo;
use App\Models\ContaBancaria;
use App\Models\ContaCorrenteCliente;
use App\Models\Contato;
use App\Models\Empresa;
use App\Models\EncomendaCliente;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\FaturaCliente;
use App\Models\FaturaFornecedor;
use App\Models\FuncaoContato;
use App\Models\LinhaEncomendaCliente;
use App\Models\LinhaEncomendaFornecedor;
use App\Models\LinhaProposta;
use App\Models\NotaCreditoCliente;
use App\Models\Pais;
use App\Models\Proposta;
use App\Models\TaxaIva;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedReferenceData();

        // Users
        User::factory()->count(8)->create();

        // Entidades
        $clientes = Entidade::factory()->cliente()->count(30)->create();
        $fornecedores = Entidade::factory()->fornecedor()->count(8)->create();

        $clientes->values()->each(fn ($e, $i) => $e->updateQuietly(['numero_cliente' => $i + 1]));
        $fornecedores->values()->each(fn ($e, $i) => $e->updateQuietly(['numero_fornecedor' => $i + 1]));

        // Contatos
        $clientes->merge($fornecedores)->each(
            fn (Entidade $e) => Contato::factory()
                ->count(fake()->numberBetween(1, 2))
                ->for($e, 'entidade')
                ->create()
        );

        // Artigos
        $artigos = Artigo::factory()->count(40)->create();

        // Contas Bancárias
        ContaBancaria::factory()->count(4)->create();

        // Propostas
        $clientes->random(15)->values()->each(function (Entidade $cliente, int $i) use ($artigos): void {
            $data = now()->subDays(fake()->numberBetween(10, 180));

            $proposta = Proposta::create([
                'numero' => 'PR-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'data_proposta' => $data->toDateString(),
                'entidade_id' => $cliente->id,
                'validade' => $data->copy()->addDays(30)->toDateString(),
                'estado' => fake()->randomElement(['apresentada', 'concluida', 'rejeitada']),
            ]);

            $artigos->random(fake()->numberBetween(2, 5))->each(function (Artigo $artigo) use ($proposta): void {
                $qty = fake()->numberBetween(1, 10);
                $price = (float) $artigo->preco;

                LinhaProposta::create([
                    'proposta_id' => $proposta->id,
                    'artigo_id' => $artigo->id,
                    'taxa_iva_id' => $artigo->taxa_iva_id,
                    'quantidade' => $qty,
                    'preco_unitario' => $price,
                    'preco_custo' => round($price * fake()->randomFloat(2, 0.4, 0.75), 2),
                    'subtotal' => round($qty * $price, 2),
                ]);
            });
        });

        // Encomendas Clientes
        $clientes->random(25)->values()->each(
            function (Entidade $cliente, int $i) use ($artigos, $fornecedores): void {
                $estado = fake()->randomElement([
                    'em_progresso', 'em_progresso',
                    'concluida', 'concluida', 'concluida',
                    'cancelada',
                ]);
                $data = now()->subDays(fake()->numberBetween(1, 180));

                $enc = EncomendaCliente::create([
                    'numero' => 'EC-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                    'data_encomenda' => $data->toDateString(),
                    'entidade_id' => $cliente->id,
                    'estado' => $estado,
                ]);

                $artigos->random(fake()->numberBetween(2, 6))->each(
                    function (Artigo $artigo) use ($enc, $fornecedores): void {
                        $qty = fake()->numberBetween(1, 10);
                        $price = (float) $artigo->preco;

                        LinhaEncomendaCliente::create([
                            'encomenda_cliente_id' => $enc->id,
                            'artigo_id' => $artigo->id,
                            'entidade_fornecedor_id' => $fornecedores->random()->id,
                            'taxa_iva_id' => $artigo->taxa_iva_id,
                            'quantidade' => $qty,
                            'preco_unitario' => $price,
                            'subtotal' => round($qty * $price, 2),
                        ]);
                    }
                );

                $enc->load('linhas.taxaIva');
                $valorTotal = $enc->valor_total;

                if (in_array($estado, ['concluida', 'cancelada'], true)) {
                    ContaCorrenteCliente::create([
                        'entidade_id' => $enc->entidade_id,
                        'encomenda_cliente_id' => $enc->id,
                        'descricao' => "Encomenda {$enc->numero}",
                        'tipo' => 'encomenda',
                        'debito' => $valorTotal,
                        'credito' => 0,
                        'saldo' => ContaCorrenteCliente::proximoSaldo((int) $enc->entidade_id, $valorTotal, 0),
                        'data' => $data->toDateString(),
                    ]);

                    if ($estado === 'concluida') {
                        FaturaCliente::create([
                            'numero' => 'FT '.str_pad((string) $enc->id, 3, '0', STR_PAD_LEFT).'/'.$data->year,
                            'data_fatura' => $data->copy()->addDay()->toDateString(),
                            'entidade_id' => $enc->entidade_id,
                            'encomenda_cliente_id' => $enc->id,
                            'valor_total' => $valorTotal,
                            'estado' => fake()->randomElement(['pendente', 'pendente', 'paga']),
                        ]);
                    }
                }

                if ($estado === 'cancelada') {
                    $dataNc = $data->copy()->addDays(fake()->numberBetween(2, 10));

                    NotaCreditoCliente::create([
                        'numero' => 'NC '.str_pad((string) $enc->id, 3, '0', STR_PAD_LEFT).'/'.$data->year,
                        'data_nota_credito' => $dataNc->toDateString(),
                        'entidade_id' => $enc->entidade_id,
                        'encomenda_cliente_id' => $enc->id,
                        'fatura_cliente_id' => null,
                        'valor_total' => $valorTotal,
                        'motivo' => 'Encomenda cancelada',
                        'estado' => fake()->randomElement(['pendente', 'processada']),
                    ]);

                    ContaCorrenteCliente::create([
                        'entidade_id' => $enc->entidade_id,
                        'encomenda_cliente_id' => $enc->id,
                        'descricao' => "Nota de Crédito — Encomenda {$enc->numero}",
                        'tipo' => 'nota_credito',
                        'debito' => 0,
                        'credito' => $valorTotal,
                        'saldo' => ContaCorrenteCliente::proximoSaldo((int) $enc->entidade_id, 0, $valorTotal),
                        'data' => $dataNc->toDateString(),
                    ]);
                }
            }
        );

        // Encomendas Fornecedores
        $fornecedores->values()->each(function (Entidade $forn, int $i) use ($artigos): void {
            $estado = fake()->randomElement(['em_progresso', 'concluida', 'concluida']);
            $data = now()->subDays(fake()->numberBetween(5, 120));

            $ef = EncomendaFornecedor::create([
                'numero' => 'EF-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'data_encomenda' => $data->toDateString(),
                'entidade_id' => $forn->id,
                'estado' => $estado,
            ]);

            $artigos->random(fake()->numberBetween(2, 5))->each(function (Artigo $artigo) use ($ef): void {
                $qty = fake()->numberBetween(1, 20);
                $price = (float) $artigo->preco;

                LinhaEncomendaFornecedor::create([
                    'encomenda_fornecedor_id' => $ef->id,
                    'artigo_id' => $artigo->id,
                    'quantidade' => $qty,
                    'preco_unitario' => $price,
                    'subtotal' => round($qty * $price, 2),
                ]);
            });

            if ($estado === 'concluida') {
                $ef->loadMissing('linhas');
                $valorTotal = round($ef->linhas->sum('subtotal') * 1.23, 2);

                FaturaFornecedor::create([
                    'numero' => 'FF '.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'/'.$data->year,
                    'data_fatura' => $data->copy()->addDays(3)->toDateString(),
                    'data_vencimento' => $data->copy()->addDays(33)->toDateString(),
                    'entidade_id' => $forn->id,
                    'encomenda_fornecedor_id' => $ef->id,
                    'valor_total' => $valorTotal,
                    'estado' => fake()->randomElement(['pendente', 'paga']),
                ]);
            }
        });
    }

    private function seedReferenceData(): void
    {
        Empresa::firstOrCreate(['id' => 1], [
            'nome' => 'A Minha Empresa',
            'logo' => null,
            'morada' => null,
            'codigo_postal' => null,
            'cidade' => null,
            'nif' => null,
        ]);

        foreach ([
            ['nome' => 'Portugal',       'codigo' => 'PT'],
            ['nome' => 'Espanha',        'codigo' => 'ES'],
            ['nome' => 'França',         'codigo' => 'FR'],
            ['nome' => 'Alemanha',       'codigo' => 'DE'],
            ['nome' => 'Itália',         'codigo' => 'IT'],
            ['nome' => 'Reino Unido',    'codigo' => 'GB'],
            ['nome' => 'Países Baixos',  'codigo' => 'NL'],
            ['nome' => 'Bélgica',        'codigo' => 'BE'],
            ['nome' => 'Suíça',          'codigo' => 'CH'],
            ['nome' => 'Brasil',         'codigo' => 'BR'],
            ['nome' => 'Angola',         'codigo' => 'AO'],
            ['nome' => 'Moçambique',     'codigo' => 'MZ'],
            ['nome' => 'Cabo Verde',     'codigo' => 'CV'],
            ['nome' => 'Estados Unidos', 'codigo' => 'US'],
            ['nome' => 'China',          'codigo' => 'CN'],
        ] as $pais) {
            Pais::firstOrCreate(['codigo' => $pais['codigo']], array_merge($pais, ['ativo' => true]));
        }

        foreach ([
            ['nome' => 'Isento (0%)',            'taxa' => 0.00],
            ['nome' => 'Taxa Reduzida (6%)',      'taxa' => 6.00],
            ['nome' => 'Taxa Intermédia (13%)',   'taxa' => 13.00],
            ['nome' => 'Taxa Normal (23%)',       'taxa' => 23.00],
        ] as $taxa) {
            TaxaIva::firstOrCreate(['taxa' => $taxa['taxa']], array_merge($taxa, ['ativo' => true]));
        }

        foreach (['Diretor Geral', 'Comercial', 'Financeiro', 'Técnico', 'Administrativo'] as $f) {
            FuncaoContato::firstOrCreate(['nome' => $f], ['nome' => $f, 'ativo' => true]);
        }

        User::firstOrCreate(
            ['email' => 'admin@gestao.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'estado' => 'ativo',
            ],
        );
    }
}
