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
        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->date('data_proposta')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->date('validade')->nullable();
            $table->enum('estado', ['apresentada', 'fechada', 'rejeitada'])->default('apresentada');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('linhas_proposta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposta_id')->constrained('propostas')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->cascadeOnDelete();
            $table->foreignId('entidade_fornecedor_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('taxa_iva_id')->nullable()->constrained('taxas_iva')->nullOnDelete();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linhas_proposta');
        Schema::dropIfExists('propostas');
    }
};
