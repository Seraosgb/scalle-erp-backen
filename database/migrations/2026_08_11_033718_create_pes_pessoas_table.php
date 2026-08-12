<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::create('pes_pessoas', function (Blueprint $table) {
        $table->id();
        $table->enum('tipo_pessoa', ['F', 'J'])->default('F');
        $table->string('nome_razao');
        $table->string('nome_fantasia')->nullable();
        $table->string('cpf_cnpj', 18)->unique();
        $table->string('email')->nullable();
        $table->string('telefone', 20)->nullable();
        $table->boolean('is_cliente')->default(true);
        $table->boolean('is_fornecedor')->default(false);
        $table->boolean('ativo')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pes_pessoas');
    }
};
