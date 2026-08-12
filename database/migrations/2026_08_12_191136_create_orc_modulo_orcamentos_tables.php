<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Principal de Orçamentos (Cabeçalho)
        Schema::create('orc_orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('pes_pessoas')->onDelete('restrict');
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('numero_orcamento', 30); // Ex: ORC-2026-000001
            $table->enum('status', ['EM_ABERTO', 'APROVADO', 'RECUSADO', 'CONVERTIDO'])->default('EM_ABERTO');
            
            $table->dateTime('data_emissao');
            $table->date('data_validade');

            $table->decimal('valor_subtotal', 12, 2)->default(0.00);
            $table->decimal('valor_desconto', 12, 2)->default(0.00);
            $table->decimal('valor_total', 12, 2)->default(0.00);

            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'numero_orcamento']);
        });

        // 2. Itens do Orçamento
        Schema::create('orc_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orc_orcamentos')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('pro_itens')->onDelete('restrict');

            $table->string('descricao_item');
            $table->decimal('quantidade', 12, 3);
            $table->decimal('valor_unitario', 12, 2);
            $table->decimal('valor_subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orc_itens');
        Schema::dropIfExists('orc_orcamentos');
    }
};