<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Ativos e Bens Patrimoniais
        Schema::create('pat_ativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('pro_itens')->onDelete('set null');
            $table->foreignId('custodiante_atual_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('codigo_patrimonio', 50); // Ex: PAT-2026-000001
            $table->string('nome', 150);
            $table->string('categoria', 50)->default('FERRAMENTA'); // FERRAMENTA, MAQUINA, EQUIPAMENTO_TI, VEICULO, MOBILIARIO
            $table->string('numero_serie', 100)->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->decimal('valor_aquisicao', 12, 2)->default(0.00);
            $table->decimal('taxa_depreciacao_anual', 5, 2)->default(0.00); // Ex: 10.00% ao ano
            $table->enum('status', ['DISPONIVEL', 'EM_CUSTODIA', 'EM_MANUTENCAO', 'AVARIADO', 'BAIXADO'])->default('DISPONIVEL');
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo_patrimonio']);
        });

        // 2. Histórico de Cautela e Movimentação de Ferramentas
        Schema::create('pat_cautelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('ativo_id')->constrained('pat_ativos')->onDelete('cascade');
            $table->foreignId('responsavel_entrega_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('custodiante_id')->constrained('users')->onDelete('cascade');

            $table->dateTime('data_retirada');
            $table->dateTime('data_devolucao_prevista')->nullable();
            $table->dateTime('data_devolucao_real')->nullable();
            $table->enum('status', ['EM_CUSTODIA', 'DEVOLVIDO', 'AVARIADO'])->default('EM_CUSTODIA');
            $table->string('ip_assinatura', 45)->nullable();
            $table->text('motivo_uso')->nullable();
            $table->text('observacoes_devolucao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pat_cautelas');
        Schema::dropIfExists('pat_ativos');
    }
};