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
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->json('tipos');
            $table->unsignedInteger('numero_cliente')->nullable()->unique();
            $table->unsignedInteger('numero_fornecedor')->nullable()->unique();
            $table->string('nif')->nullable();
            $table->string('nif_hash', 64)->nullable()->index();
            $table->string('nome');
            $table->string('morada')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('paises')->nullOnDelete();
            $table->string('telefone')->nullable();
            $table->string('telemovel')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->boolean('consentimento_rgpd')->default(false);
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
        Schema::dropIfExists('entidades');
    }
};
