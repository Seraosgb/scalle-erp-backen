<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Principal de Compras / Entrada de Nota
        Schema::create('com_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('pes_pessoas')->onDelete('restrict');

            $table->string('numero_nota', 50)->nullable(); // Número da NF ou Pedido de Compra
            $table->enum('status', ['RASCUNHO', 'RECEBIDO', 'CANCELADO'])->default('RASCUNHO');
            $table->dateTime('data_compra');

            $table->decimal('valor_total', 12, 2)->default(0.00);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });

        // 2. Itens da Compra
        Schema::create('com_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('com_compras')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('pro_itens')->onDelete('restrict');

            $table->decimal('quantidade', 12, 3);
            $table->decimal('valor_unitario', 12, 2); // Preço de Custo na compra
            $table->decimal('valor_subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('com_itens');
        Schema::dropIfExists('com_compras');
    }
};