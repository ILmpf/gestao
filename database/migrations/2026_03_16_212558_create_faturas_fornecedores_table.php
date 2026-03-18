<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faturas_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50);
            $table->date('data_fatura');
            $table->date('data_vencimento')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_fornecedor_id')->nullable()->constrained('encomendas_fornecedores')->nullOnDelete();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('caminho_documento')->nullable();
            $table->string('caminho_comprovativo')->nullable();
            $table->enum('estado', ['pendente', 'paga'])->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faturas_fornecedores');
    }
};
