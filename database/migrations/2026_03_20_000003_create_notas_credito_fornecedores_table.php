<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_credito_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50);
            $table->date('data_nota_credito');
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_fornecedor_id')->nullable()->constrained('encomendas_fornecedores')->nullOnDelete();
            $table->foreignId('fatura_fornecedor_id')->nullable()->constrained('faturas_fornecedores')->nullOnDelete();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('motivo', 500)->nullable();
            $table->enum('estado', ['pendente', 'processada'])->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_credito_fornecedores');
    }
};
