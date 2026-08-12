<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela Principal de OS (Cabeçalho)
        Schema::create('os_ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('pes_pessoas')->onDelete('restrict');
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('numero_os', 20); // Ex: OS-2026-00001
            $table->enum('status', ['ABERTO', 'EM_ANDAMENTO', 'AGUARDANDO_PECA', 'CONCLUIDO', 'CANCELADO'])->default('ABERTO');

            // Datas do Ciclo
            $table->dateTime('data_abertura');
            $table->dateTime('previsao_entrega')->nullable();
            $table->dateTime('data_conclusao')->nullable();

            // Detalhamento do Atendimento
            $table->text('defeito_relatado')->nullable();
            $table->text('laudo_tecnico')->nullable();
            $table->text('observacoes_internas')->nullable();
            $table->text('termos_garantia')->nullable();

            // Totalizadores Financeiros
            $table->decimal('valor_servicos', 12, 2)->default(0.00);
            $table->decimal('valor_produtos', 12, 2)->default(0.00);
            $table->decimal('valor_desconto', 12, 2)->default(0.00);
            $table->decimal('valor_total', 12, 2)->default(0.00);

            $table->timestamps();

            $table->unique(['empresa_id', 'numero_os']);
        });

        // Tabela de Itens da OS (Serviços Executados e Peças Utilizadas)
        Schema::create('os_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('os_ordens_servico')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('pro_itens')->onDelete('restrict');

            $table->string('descricao_item'); // Cópia do nome do item para histórico estático
            $table->decimal('quantidade', 12, 3)->default(1.000);
            $table->decimal('valor_unitario', 12, 2)->default(0.00);
            $table->decimal('valor_subtotal', 12, 2)->default(0.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('os_itens');
        Schema::dropIfExists('os_ordens_servico');
    }
};