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
        Schema::create('encomendas_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->date('data_encomenda')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->nullOnDelete();
            $table->enum('estado', ['em_progresso', 'concluida', 'cancelada'])->default('em_progresso');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('linhas_encomenda_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_cliente_id')->constrained('encomendas_clientes')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->cascadeOnDelete();
            $table->foreignId('entidade_fornecedor_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('taxa_iva_id')->nullable()->constrained('taxas_iva')->nullOnDelete();
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
        Schema::dropIfExists('linhas_encomenda_cliente');
        Schema::dropIfExists('encomendas_clientes');
    }
};
