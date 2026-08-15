<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cadastro de Veículos da Frota
        Schema::create('fro_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('motorista_padrao_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('placa', 10);
            $table->string('modelo', 100);
            $table->string('marca', 50);
            $table->integer('ano_fabricacao')->nullable();
            $table->string('combustivel_tipo', 30)->default('FLEX'); // GASOLINA, ETANOL, DIESEL, GNV, FLEX, ELETRICO
            $table->decimal('km_atual', 12, 2)->default(0.00);
            $table->enum('status', ['DISPONIVEL', 'EM_USO', 'EM_MANUTENCAO', 'INATIVO'])->default('DISPONIVEL');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'placa']);
        });

        // 2. Registro de Abastecimentos & Controle de Consumo
        Schema::create('fro_abastecimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('veiculo_id')->constrained('fro_veiculos')->onDelete('cascade');
            $table->foreignId('motorista_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lancamento_financeiro_id')->nullable()->constrained('fin_lancamentos')->onDelete('set null');

            $table->dateTime('data_abastecimento');
            $table->decimal('km_odometro', 12, 2);
            $table->decimal('litros', 10, 3);
            $table->decimal('valor_litro', 10, 3);
            $table->decimal('valor_total', 12, 2);
            $table->string('posto_combustivel', 150)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // 3. Adiciona vínculo veicular opcional na tabela de OS
        if (Schema::hasTable('os_ordens_servico') && !Schema::hasColumn('os_ordens_servico', 'veiculo_id')) {
            Schema::table('os_ordens_servico', function (Blueprint $table) {
                $table->foreignId('veiculo_id')->nullable()->after('tecnico_id')->constrained('fro_veiculos')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('os_ordens_servico') && Schema::hasColumn('os_ordens_servico', 'veiculo_id')) {
            Schema::table('os_ordens_servico', function (Blueprint $table) {
                $table->dropForeign(['veiculo_id']);
                $table->dropColumn('veiculo_id');
            });
        }

        Schema::dropIfExists('fro_abastecimentos');
        Schema::dropIfExists('fro_veiculos');
    }
};