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
        Schema::create('contatos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->string('primeiro_nome', 100);
            $table->string('apelido', 100);
            $table->foreignId('funcao_contacto_id')->nullable()->constrained('funcoes_contato')->nullOnDelete();
            $table->string('telefone')->nullable();
            $table->string('telemovel')->nullable();
            $table->string('email')->nullable();
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
        Schema::dropIfExists('contatos');
    }
};
