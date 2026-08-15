<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Parâmetros Operacionais por Empresa
        Schema::create('sis_empresa_parametros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->string('chave', 100);
            $table->text('valor')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'chave']);
        });

        // 2. Tabela de Trilha de Auditoria (Activity Log)
        Schema::create('sis_auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('acao', 50); // CRIACAO, ATUALIZACAO, EXCLUSAO, LOGIN
            $table->string('tabela', 100);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('dados_antigos')->nullable();
            $table->json('dados_novos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_auditoria_logs');
        Schema::dropIfExists('sis_empresa_parametros');
    }
};