<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Planos SaaS
        if (!Schema::hasTable('sis_planos')) {
            Schema::create('sis_planos', function (Blueprint $table) {
                $table->id();
                $table->string('nome', 50); // MEI, PRO, ENTERPRISE
                $table->string('slug', 50)->unique();
                $table->decimal('preco_mensal', 10, 2)->default(0.00);
                $table->integer('limite_usuarios')->default(1);
                $table->bigInteger('limite_storage_bytes')->default(3221225472); // 3 GB padrão MEI
                $table->json('modulos_habilitados'); // ['os', 'financeiro', 'vendas', 'fiscal', 'frotas', 'rh', 'pcp', 'wms', 'portal_cliente']
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });

            // Insere Planos Padrão do Scalle ERP
            DB::table('sis_planos')->insert([
                [
                    'nome' => 'Plano MEI (Básico)',
                    'slug' => 'mei',
                    'preco_mensal' => 49.90,
                    'limite_usuarios' => 1,
                    'limite_storage_bytes' => 3 * 1024 * 1024 * 1024, // 3 GB
                    'modulos_habilitados' => json_encode(['os', 'financeiro', 'vendas', 'pix', 'portal_cliente']),
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nome' => 'Plano Pro (PMEs)',
                    'slug' => 'pro',
                    'preco_mensal' => 149.90,
                    'limite_usuarios' => 10,
                    'limite_storage_bytes' => 20 * 1024 * 1024 * 1024, // 20 GB
                    'modulos_habilitados' => json_encode(['os', 'financeiro', 'vendas', 'pix', 'fiscal', 'frotas', 'ativos', 'dp', 'rh_estrategico', 'exportacao_contabil', 'evidencias', 'mensageria', 'portal_cliente']),
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nome' => 'Plano Enterprise (Corporativo)',
                    'slug' => 'enterprise',
                    'preco_mensal' => 399.90,
                    'limite_usuarios' => 999,
                    'limite_storage_bytes' => 100 * 1024 * 1024 * 1024, // 100 GB
                    'modulos_habilitados' => json_encode(['os', 'financeiro', 'vendas', 'pix', 'fiscal', 'frotas', 'ativos', 'dp', 'rh_estrategico', 'pcp', 'wms', 'exportacao_contabil', 'evidencias', 'mensageria', 'portal_cliente', 'mfa', 'multi_filial']),
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Adiciona colunas de plano, status de assinatura e uso de storage na tabela sis_empresas
        if (Schema::hasTable('sis_empresas')) {
            Schema::table('sis_empresas', function (Blueprint $table) {
                if (!Schema::hasColumn('sis_empresas', 'plano_id')) {
                    $table->foreignId('plano_id')->nullable()->after('ativo')->constrained('sis_planos')->onDelete('set null');
                }
                if (!Schema::hasColumn('sis_empresas', 'status_assinatura')) {
                    $table->enum('status_assinatura', ['TRIAL', 'ATIVO', 'INADIMPLENTE', 'CANCELADO'])->default('TRIAL')->after('plano_id');
                }
                if (!Schema::hasColumn('sis_empresas', 'storage_utilizado_bytes')) {
                    $table->bigInteger('storage_utilizado_bytes')->default(0)->after('status_assinatura');
                }
                if (!Schema::hasColumn('sis_empresas', 'gateway_customer_id')) {
                    $table->string('gateway_customer_id', 100)->nullable()->after('storage_utilizado_bytes');
                }
            });

            // Atribui o plano MEI padrão para empresas existentes
            DB::table('sis_empresas')->whereNull('plano_id')->update([
                'plano_id' => 1,
                'status_assinatura' => 'ATIVO'
            ]);
        }

        // 3. Tabela de Registro de Uploads para Auditoria de Cotas
        if (!Schema::hasTable('sis_storage_arquivos')) {
            Schema::create('sis_storage_arquivos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('modulo', 50); // OS_EVIDENCIA, NF_XML, ATIVO_FOTO, RH_DOC
                $table->string('nome_original', 255);
                $table->string('caminho_storage', 500);
                $table->bigInteger('tamanho_bytes');
                $table->string('mime_type', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_storage_arquivos');
        if (Schema::hasTable('sis_empresas')) {
            Schema::table('sis_empresas', function (Blueprint $table) {
                $table->dropForeign(['plano_id']);
                $table->dropColumn(['plano_id', 'status_assinatura', 'storage_utilizado_bytes', 'gateway_customer_id']);
            });
        }
        Schema::dropIfExists('sis_planos');
    }
};