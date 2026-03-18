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
        Schema::create('artigos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 50)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->foreignId('taxa_iva_id')->nullable()->constrained('taxas_iva')->nullOnDelete();
            $table->string('imagem_artigo')->nullable();
            $table->text('notas')->nullable();
            $table->enum('estado', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artigos');
    }
};
