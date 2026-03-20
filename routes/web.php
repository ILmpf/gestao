<?php

use App\Http\Controllers\Config\AcaoCalendarioController;
use App\Http\Controllers\Config\ArtigoController;
use App\Http\Controllers\Config\EmpresaController;
use App\Http\Controllers\Config\FuncaoContatoController;
use App\Http\Controllers\Config\PaisController;
use App\Http\Controllers\Config\TaxaIvaController;
use App\Http\Controllers\Config\TipoCalendarioController;
use App\Http\Controllers\ContaBancariaController;
use App\Http\Controllers\ContaCorrenteClienteController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\EncomendaClienteController;
use App\Http\Controllers\EncomendaFornecedorController;
use App\Http\Controllers\EntidadeController;
use App\Http\Controllers\FaturaFornecedorController;
use App\Http\Controllers\FileServeController;
use App\Http\Controllers\NotaCreditoFornecedorController;
use App\Http\Controllers\PropostaController;
use App\Http\Controllers\ViesController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // VIES 
    Route::get('vies/lookup', [ViesController::class, 'lookup'])->name('vies.lookup');

    // Clientes 
    Route::get('clientes', [EntidadeController::class, 'index'])->name('clientes.index');
    Route::delete('clientes/{entidade}', [EntidadeController::class, 'destroy'])->name('clientes.destroy');

    // Fornecedores 
    Route::get('fornecedores', [EntidadeController::class, 'index'])->name('fornecedores.index');
    Route::delete('fornecedores/{entidade}', [EntidadeController::class, 'destroy'])->name('fornecedores.destroy');

    // Entidades 
    Route::get('entidades', [EntidadeController::class, 'index'])->name('entidades.index');
    Route::get('entidades/create', [EntidadeController::class, 'create'])->name('entidades.create');
    Route::post('entidades', [EntidadeController::class, 'store'])->name('entidades.store');
    Route::get('entidades/{entidade}/edit', [EntidadeController::class, 'edit'])->name('entidades.edit');
    Route::put('entidades/{entidade}', [EntidadeController::class, 'update'])->name('entidades.update');
    Route::delete('entidades/{entidade}', [EntidadeController::class, 'destroy'])->name('entidades.destroy');

    Route::get('files/{path}', [FileServeController::class, 'show'])
        ->where('path', '.+')
        ->name('files.show');

    // Contatos
    Route::get('contactos', [ContatoController::class, 'index'])->name('contactos.index');
    Route::get('contactos/create', [ContatoController::class, 'create'])->name('contactos.create');
    Route::post('contactos', [ContatoController::class, 'store'])->name('contactos.store');
    Route::get('contactos/{contato}/edit', [ContatoController::class, 'edit'])->name('contactos.edit');
    Route::put('contactos/{contato}', [ContatoController::class, 'update'])->name('contactos.update');
    Route::delete('contactos/{contato}', [ContatoController::class, 'destroy'])->name('contactos.destroy');

    // Propostas
    Route::get('propostas', [PropostaController::class, 'index'])->name('propostas.index');
    Route::get('propostas/create', [PropostaController::class, 'create'])->name('propostas.create');
    Route::post('propostas', [PropostaController::class, 'store'])->name('propostas.store');
    Route::get('propostas/{proposta}/edit', [PropostaController::class, 'edit'])->name('propostas.edit');
    Route::put('propostas/{proposta}', [PropostaController::class, 'update'])->name('propostas.update');
    Route::delete('propostas/{proposta}', [PropostaController::class, 'destroy'])->name('propostas.destroy');
    Route::get('propostas/{proposta}/pdf', [PropostaController::class, 'pdf'])->name('propostas.pdf');
    Route::post('propostas/{proposta}/converter', [PropostaController::class, 'converter'])->name('propostas.converter');

    Route::inertia('calendario', 'Placeholder')->name('calendario.index');

    // Operacional
    Route::get('encomendas/clientes', [EncomendaClienteController::class, 'index'])->name('encomendas.clientes.index');
    Route::get('encomendas/clientes/create', [EncomendaClienteController::class, 'create'])->name('encomendas.clientes.create');
    Route::post('encomendas/clientes', [EncomendaClienteController::class, 'store'])->name('encomendas.clientes.store');
    Route::get('encomendas/clientes/{encomendaCliente}/edit', [EncomendaClienteController::class, 'edit'])->name('encomendas.clientes.edit');
    Route::put('encomendas/clientes/{encomendaCliente}', [EncomendaClienteController::class, 'update'])->name('encomendas.clientes.update');
    Route::delete('encomendas/clientes/{encomendaCliente}', [EncomendaClienteController::class, 'destroy'])->name('encomendas.clientes.destroy');
    Route::get('encomendas/clientes/{encomendaCliente}/pdf', [EncomendaClienteController::class, 'pdf'])->name('encomendas.clientes.pdf');
    Route::get('encomendas/clientes/{encomendaCliente}/fatura', [EncomendaClienteController::class, 'fatura'])->name('encomendas.clientes.fatura');
    Route::get('encomendas/clientes/{encomendaCliente}/nota-credito', [EncomendaClienteController::class, 'notaCredito'])->name('encomendas.clientes.nota-credito');
    Route::post('encomendas/clientes/{encomendaCliente}/converter-fornecedor', [EncomendaClienteController::class, 'converterParaFornecedor'])->name('encomendas.clientes.converter-fornecedor');

    Route::get('encomendas/fornecedores', [EncomendaFornecedorController::class, 'index'])->name('encomendas.fornecedores.index');
    Route::get('encomendas/fornecedores/{encomendaFornecedor}', [EncomendaFornecedorController::class, 'show'])->name('encomendas.fornecedores.show');
    Route::delete('encomendas/fornecedores/{encomendaFornecedor}', [EncomendaFornecedorController::class, 'destroy'])->name('encomendas.fornecedores.destroy');
    Route::get('encomendas/fornecedores/{encomendaFornecedor}/pdf', [EncomendaFornecedorController::class, 'pdf'])->name('encomendas.fornecedores.pdf');

    Route::prefix('financeiro')->name('financeiro.')->group(function () {
        // Faturas Fornecedores
        Route::get('faturas-fornecedores', [FaturaFornecedorController::class, 'index'])->name('faturas-fornecedores.index');
        Route::get('faturas-fornecedores/create', [FaturaFornecedorController::class, 'create'])->name('faturas-fornecedores.create');
        Route::post('faturas-fornecedores', [FaturaFornecedorController::class, 'store'])->name('faturas-fornecedores.store');
        Route::get('faturas-fornecedores/{faturaFornecedor}/edit', [FaturaFornecedorController::class, 'edit'])->name('faturas-fornecedores.edit');
        Route::post('faturas-fornecedores/{faturaFornecedor}', [FaturaFornecedorController::class, 'update'])->name('faturas-fornecedores.update');
        Route::delete('faturas-fornecedores/{faturaFornecedor}', [FaturaFornecedorController::class, 'destroy'])->name('faturas-fornecedores.destroy');

        // Contas Bancárias
        Route::get('contas-bancarias', [ContaBancariaController::class, 'index'])->name('contas-bancarias.index');
        Route::get('contas-bancarias/create', [ContaBancariaController::class, 'create'])->name('contas-bancarias.create');
        Route::post('contas-bancarias', [ContaBancariaController::class, 'store'])->name('contas-bancarias.store');
        Route::get('contas-bancarias/{contaBancaria}/edit', [ContaBancariaController::class, 'edit'])->name('contas-bancarias.edit');
        Route::put('contas-bancarias/{contaBancaria}', [ContaBancariaController::class, 'update'])->name('contas-bancarias.update');
        Route::delete('contas-bancarias/{contaBancaria}', [ContaBancariaController::class, 'destroy'])->name('contas-bancarias.destroy');

        // Conta Corrente Clientes
        Route::get('conta-corrente-clientes', [ContaCorrenteClienteController::class, 'index'])->name('conta-corrente-clientes.index');
        Route::post('conta-corrente-clientes', [ContaCorrenteClienteController::class, 'store'])->name('conta-corrente-clientes.store');
        Route::get('conta-corrente-clientes/{entidade}/create', [ContaCorrenteClienteController::class, 'create'])->name('conta-corrente-clientes.create');
        Route::get('conta-corrente-clientes/{entidade}', [ContaCorrenteClienteController::class, 'show'])->name('conta-corrente-clientes.show');
        Route::get('conta-corrente-clientes/{contaCorrenteCliente}/edit', [ContaCorrenteClienteController::class, 'edit'])->name('conta-corrente-clientes.edit');
        Route::put('conta-corrente-clientes/{contaCorrenteCliente}', [ContaCorrenteClienteController::class, 'update'])->name('conta-corrente-clientes.update');
        Route::delete('conta-corrente-clientes/{contaCorrenteCliente}', [ContaCorrenteClienteController::class, 'destroy'])->name('conta-corrente-clientes.destroy');

        // Notas de Crédito Fornecedores
        Route::get('notas-credito-fornecedores', [NotaCreditoFornecedorController::class, 'index'])->name('notas-credito-fornecedores.index');
        Route::get('notas-credito-fornecedores/create', [NotaCreditoFornecedorController::class, 'create'])->name('notas-credito-fornecedores.create');
        Route::post('notas-credito-fornecedores', [NotaCreditoFornecedorController::class, 'store'])->name('notas-credito-fornecedores.store');
        Route::get('notas-credito-fornecedores/{notaCreditoFornecedor}/edit', [NotaCreditoFornecedorController::class, 'edit'])->name('notas-credito-fornecedores.edit');
        Route::put('notas-credito-fornecedores/{notaCreditoFornecedor}', [NotaCreditoFornecedorController::class, 'update'])->name('notas-credito-fornecedores.update');
        Route::delete('notas-credito-fornecedores/{notaCreditoFornecedor}', [NotaCreditoFornecedorController::class, 'destroy'])->name('notas-credito-fornecedores.destroy');
    });

    // Arquivo
    Route::inertia('arquivo-digital', 'Placeholder')->name('arquivo-digital.index');

    // Gestão de Acessos
    Route::inertia('gestao-acessos/utilizadores', 'Placeholder')->name('gestao-acessos.utilizadores.index');
    Route::inertia('gestao-acessos/permissoes', 'Placeholder')->name('gestao-acessos.permissoes.index');

    // Configurações
    Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
        Route::resource('paises', PaisController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['paises' => 'pais']);
        Route::resource('funcoes-contacto', FuncaoContatoController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['funcoes-contacto' => 'funcaoContato']);
        Route::resource('tipos-calendario', TipoCalendarioController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tipos-calendario' => 'tipoCalendario']);
        Route::resource('accoes-calendario', AcaoCalendarioController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['accoes-calendario' => 'acaoCalendario']);
        Route::resource('taxas-iva', TaxaIvaController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['taxas-iva' => 'taxaIva']);
        Route::get('empresa', [EmpresaController::class, 'edit'])
            ->name('empresa.edit');
        Route::put('empresa', [EmpresaController::class, 'update'])
            ->name('empresa.update');
        Route::get('artigos', [ArtigoController::class, 'index'])->name('artigos.index');
        Route::get('artigos/create', [ArtigoController::class, 'create'])->name('artigos.create');
        Route::post('artigos', [ArtigoController::class, 'store'])->name('artigos.store');
        Route::get('artigos/{artigo}/edit', [ArtigoController::class, 'edit'])->name('artigos.edit');
        Route::put('artigos/{artigo}', [ArtigoController::class, 'update'])->name('artigos.update');
        Route::delete('artigos/{artigo}', [ArtigoController::class, 'destroy'])->name('artigos.destroy');
        Route::inertia('logs', 'Placeholder')->name('logs.index');
    });

});

require __DIR__.'/settings.php';
