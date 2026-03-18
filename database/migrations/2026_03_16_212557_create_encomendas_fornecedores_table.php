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
        Schema::create('encomendas_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->date('data_encomenda')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_cliente_id')->nullable()->constrained('encomendas_clientes')->nullOnDelete();
            $table->enum('status', ['em_progresso', 'concluida', 'cancelada'])->default('em_progresso');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('linhas_encomenda_fornecedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_fornecedor_id')->constrained('encomendas_fornecedores')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->cascadeOnDelete();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linhas_encomenda_fornecedor');
        Schema::dropIfExists('encomendas_fornecedores');
    }
};
