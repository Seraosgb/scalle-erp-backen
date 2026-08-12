<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Categorias Financeiras (Plano de Contas)
        Schema::create('fin_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->string('nome'); // Ex: Receita de Serviços, Venda de Peças, Aluguel
            $table->enum('tipo', ['RECEITA', 'DESPESA']);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 2. Tabela Principal de Lançamentos (Contas a Receber e A Pagar)
        Schema::create('fin_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('pessoa_id')->nullable()->constrained('pes_pessoas')->onDelete('set null'); // Cliente ou Fornecedor
            $table->foreignId('categoria_id')->nullable()->constrained('fin_categorias')->onDelete('set null');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('os_ordens_servico')->onDelete('set null');

            $table->enum('tipo', ['RECEITA', 'DESPESA']); // RECEITA = Contas a Receber | DESPESA = Contas a Pagar
            $table->string('descricao');
            $table->decimal('valor', 12, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();

            $table->enum('status', ['PENDENTE', 'PAGO', 'CANCELADO'])->default('PENDENTE');
            $table->enum('forma_pagamento', ['PIX', 'DINHEIRO', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'BOLETO', 'TRANSFERENCIA'])->nullable();

            $table->integer('parcela_atual')->default(1);
            $table->integer('total_parcelas')->default(1);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_lancamentos');
        Schema::dropIfExists('fin_categorias');
    }
};