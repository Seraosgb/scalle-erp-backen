<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sis_empresas', function (Blueprint $table) {
            $table->string('inscricao_estadual', 20)->nullable()->after('cpf_cnpj');
            $table->string('inscricao_municipal', 20)->nullable()->after('inscricao_estadual');
            $table->tinyInteger('crt')->default(1)->after('inscricao_municipal'); // 1 = Simples Nacional, 2 = Simples Excessão, 3 = Regime Normal

            // Endereço Fiscal do Emitente
            $table->string('cep', 10)->nullable()->after('crt');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
            $table->string('codigo_ibge', 7)->nullable()->after('uf'); // Ex: 3300456 (Belford Roxo)
        });
    }

    public function down(): void
    {
        Schema::table('sis_empresas', function (Blueprint $table) {
            $table->dropColumn([
                'inscricao_estadual',
                'inscricao_municipal',
                'crt',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'uf',
                'codigo_ibge',
            ]);
        });
    }
};