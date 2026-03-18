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
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->dateTime('inicio');
            $table->dateTime('fim')->nullable();
            $table->unsignedInteger('duracao')->nullable();
            $table->boolean('partilha')->default(false);
            $table->boolean('conhecimento')->default(false);
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('tipo_calendario_id')->nullable()->constrained('tipos_calendario')->nullOnDelete();
            $table->foreignId('acao_calendario_id')->nullable()->constrained('acoes_calendario')->nullOnDelete();
            $table->text('descricao')->nullable();
            $table->string('estado', 50)->default('agendado');
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('evento_calendario_utilizador', function (Blueprint $table) {
            $table->foreignId('evento_calendario_id')->constrained('eventos_calendario')->cascadeOnDelete();
            $table->foreignId('utilizador_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['evento_calendario_id', 'utilizador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_calendario_utilizador');
        Schema::dropIfExists('eventos_calendario');
    }
};
