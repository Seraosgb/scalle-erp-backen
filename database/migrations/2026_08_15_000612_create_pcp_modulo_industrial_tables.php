<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Auxiliar de Motivos de Perda/Refugo
        Schema::create('pcp_motivos_perda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->string('nome', 100);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 2. Ficha Técnica / Estrutura do Produto (BOM)
        Schema::create('pcp_fichas_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('produto_pai_id')->constrained('pro_itens')->onDelete('cascade'); // Produto Acabado
            $table->foreignId('insumo_id')->nullable()->constrained('pro_itens')->onDelete('set null'); // Matéria-Prima
            $table->enum('tipo_componente', ['INSUMO', 'MAO_DE_OBRA', 'CUSTO_INDIRETO'])->default('INSUMO');
            $table->string('descricao_custo')->nullable(); // Para custos indiretos sem item cadastrado
            $table->decimal('quantidade_necessaria', 12, 4)->default(1.0000);
            $table->decimal('custo_estimado', 12, 4)->default(0.0000);
            $table->timestamps();
        });

        // 3. Ordens de Produção (OP)
        Schema::create('pcp_ordens_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('produto_acabado_id')->constrained('pro_itens')->onDelete('cascade');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('numero_op', 30)->unique();
            $table->enum('status', ['PLANEJADA', 'EM_PRODUCAO', 'CONCLUIDA', 'CANCELADA'])->default('PLANEJADA');

            $table->decimal('quantidade_planejada', 12, 4);
            $table->decimal('quantidade_produzida', 12, 4)->default(0.0000);

            $table->decimal('custo_total_insumos', 12, 2)->default(0.00);
            $table->decimal('custo_total_adicional', 12, 2)->default(0.00); // Mão de obra / CIF
            $table->decimal('custo_total_producao', 12, 2)->default(0.00);
            $table->decimal('custo_unitario_final', 12, 4)->default(0.0000);

            $table->dateTime('data_inicio_prevista')->nullable();
            $table->dateTime('data_conclusao_prevista')->nullable();
            $table->dateTime('data_inicio_real')->nullable();
            $table->dateTime('data_conclusao_real')->nullable();

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // 4. Apontamento de Perdas & Refugos da OP
        Schema::create('pcp_apontamentos_perda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('ordem_producao_id')->constrained('pcp_ordens_producao')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('pro_itens')->onDelete('cascade');
            $table->foreignId('motivo_perda_id')->nullable()->constrained('pcp_motivos_perda')->onDelete('set null');

            $table->decimal('quantidade_perdida', 12, 4);
            $table->decimal('custo_perda', 12, 2)->default(0.00);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pcp_apontamentos_perda');
        Schema::dropIfExists('pcp_ordens_producao');
        Schema::dropIfExists('pcp_fichas_tecnicas');
        Schema::dropIfExists('pcp_motivos_perda');
    }
};