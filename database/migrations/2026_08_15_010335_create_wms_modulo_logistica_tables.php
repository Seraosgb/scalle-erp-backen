<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Depósitos / Almoxarifados
        Schema::create('wms_depositos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->onDelete('set null'); // Técnico / Almoxarife
            
            $table->string('nome', 100);
            $table->enum('tipo', ['FISICO', 'VOLANTE_TECNICO', 'QUARENTENA_AVARIA'])->default('FISICO');
            $table->boolean('is_padrao')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 2. Tabela de Saldo Fracionado por Depósito
        Schema::create('wms_estoque_deposito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('deposito_id')->constrained('wms_depositos')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('pro_itens')->onDelete('cascade');
            
            $table->decimal('quantidade', 12, 4)->default(0.0000);
            $table->timestamps();

            $table->unique(['deposito_id', 'item_id']);
        });

        // 3. Tabela de Transferências Internas
        Schema::create('wms_transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('deposito_origem_id')->constrained('wms_depositos')->onDelete('cascade');
            $table->foreignId('deposito_destino_id')->constrained('wms_depositos')->onDelete('cascade');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recebedor_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('numero_transferencia', 30)->unique();
            $table->enum('status', ['SOLICITADA', 'EM_TRANSITO', 'CONCLUIDA', 'CANCELADA'])->default('CONCLUIDA');
            $table->text('observacoes')->nullable();
            $table->dateTime('data_envio')->nullable();
            $table->dateTime('data_recebimento')->nullable();
            $table->timestamps();
        });

        // 4. Itens da Transferência
        Schema::create('wms_transferencia_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transferencia_id')->constrained('wms_transferencias')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('pro_itens')->onDelete('cascade');
            $table->decimal('quantidade', 12, 4);
            $table->timestamps();
        });

        // 🔄 POPULAÇÃO RETROCOMPATÍVEL: Cria depósito padrão para as empresas existentes e aloca estoques atuais
        $empresas = DB::table('sis_empresas')->get();
        foreach ($empresas as $empresa) {
            $depositoId = DB::table('wms_depositos')->insertGetId([
                'empresa_id' => $empresa->id,
                'nome' => 'Almoxarifado Principal',
                'tipo' => 'FISICO',
                'is_padrao' => true,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $itens = DB::table('pro_itens')->where('empresa_id', $empresa->id)->where('tipo', 'P')->get();
            foreach ($itens as $item) {
                DB::table('wms_estoque_deposito')->insert([
                    'empresa_id' => $empresa->id,
                    'deposito_id' => $depositoId,
                    'item_id' => $item->id,
                    'quantidade' => $item->estoque_atual ?? 0.0000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wms_transferencia_itens');
        Schema::dropIfExists('wms_transferencias');
        Schema::dropIfExists('wms_estoque_deposito');
        Schema::dropIfExists('wms_depositos');
    }
};