<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_itens', function (Blueprint $table) {
            $table->string('ncm', 10)->nullable()->after('descricao'); // Nomenclatura Comum do Mercosul (Ex: 8471.30.12)
            $table->string('cest', 10)->nullable()->after('ncm'); // Código Especificador da Substituição Tributária
            $table->string('cfop', 5)->nullable()->after('cest'); // Código Fiscal de Operações e Prestações (Ex: 5102)
            $table->tinyInteger('origem_mercadoria')->default(0)->after('cfop'); // 0 = Nacional, 1 = Estrangeira Importação Direta, etc.
        });
    }

    public function down(): void
    {
        Schema::table('pro_itens', function (Blueprint $table) {
            $table->dropColumn(['ncm', 'cest', 'cfop', 'origem_mercadoria']);
        });
    }
};