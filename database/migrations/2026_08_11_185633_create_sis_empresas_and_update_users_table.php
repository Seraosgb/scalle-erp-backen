<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Tenants / Empresas
        Schema::create('sis_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj', 18)->unique();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Adiciona a FK da empresa na tabela de usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')->after('id')->constrained('sis_empresas')->onDelete('cascade');
            $table->boolean('ativo')->default(true);
        });

        // Adiciona a FK da empresa na tabela de pessoas (Multi-tenant)
        Schema::table('pes_pessoas', function (Blueprint $table) {
            $table->foreignId('empresa_id')->after('id')->constrained('sis_empresas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pes_pessoas', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn(['empresa_id', 'ativo']);
        });

        Schema::dropIfExists('sis_empresas');
    }
};