<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturas_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50);
            $table->date('data_fatura');
            $table->date('data_vencimento')->nullable();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_cliente_id')->nullable()->constrained('encomendas_clientes')->nullOnDelete();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('caminho_documento')->nullable();
            $table->enum('estado', ['pendente', 'paga'])->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas_clientes');
    }
};
