<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fis_documentos_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
            $table->foreignId('venda_id')->nullable()->constrained('ven_vendas')->onDelete('set null');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('os_ordens_servico')->onDelete('set null');

            $table->enum('tipo_doc', ['NFE', 'NFSE', 'NFCE'])->default('NFE');
            $table->string('numero_nota', 20)->nullable();
            $table->string('serie', 5)->default('1');
            $table->string('chave_acesso', 44)->nullable()->unique();
            $table->string('protocolo', 50)->nullable();

            $table->enum('status', ['RASCUNHO', 'PROCESSANDO', 'AUTORIZADO', 'REJEITADO', 'CANCELADO'])->default('RASCUNHO');
            $table->text('mensagem_sefaz')->nullable();

            $table->text('url_pdf')->nullable();
            $table->text('url_xml')->nullable();

            $table->decimal('valor_total', 12, 2)->default(0.00);
            $table->dateTime('data_emissao')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fis_documentos_fiscais');
    }
};