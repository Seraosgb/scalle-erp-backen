<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Unidades de Medida (UN, KG, M, HR, etc)
        Schema::create('pro_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->string('sigla', 10);
            $table->string('nome', 50);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'sigla']);
        });

        // Tabela de Categorias
        Schema::create('pro_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->string('nome', 100);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Tabela Unificada de Itens (Produtos e Serviços)
        Schema::create('pro_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('pro_categorias')->onDelete('set null');
            $table->foreignId('unidade_id')->nullable()->constrained('pro_unidades')->onDelete('set null');
            
            $table->enum('tipo', ['P', 'S'])->default('P'); // P = Produto, S = Serviço
            $table->string('codigo_sku')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('nome');
            $table->text('descricao')->nullable();
            
            $table->decimal('preco_custo', 12, 2)->default(0.00);
            $table->decimal('preco_venda', 12, 2)->default(0.00);
            $table->decimal('estoque_atual', 12, 3)->default(0.00); // Serviços mantêm 0.00
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo_sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_itens');
        Schema::dropIfExists('pro_categorias');
        Schema::dropIfExists('pro_unidades');
    }
};