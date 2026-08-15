<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Recrutamento: Vagas Abertas
        if (!Schema::hasTable('rh_vagas')) {
            Schema::create('rh_vagas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->string('titulo', 150);
                $table->string('departamento', 100);
                $table->integer('quantidade_vagas')->default(1);
                $table->decimal('salario_proposto', 12, 2)->nullable();
                $table->enum('regime_contratacao', ['CLT', 'PJ', 'ESTAGIO'])->default('CLT');
                $table->enum('status', ['ABERTA', 'PAUSADA', 'CONCLUIDA', 'CANCELADA'])->default('ABERTA');
                $table->text('descricao')->nullable();
                $table->text('requisitos')->nullable();
                $table->timestamps();
            });
        }

        // 2. Recrutamento: Funil Kanban de Candidatos
        if (!Schema::hasTable('rh_candidatos')) {
            Schema::create('rh_candidatos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('vaga_id')->constrained('rh_vagas')->onDelete('cascade');
                $table->string('nome_completo', 200);
                $table->string('cpf', 14)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('telefone', 30)->nullable();
                $table->enum('etapa_kanban', ['INSCRITO', 'TRIAGEM', 'ENTREVISTA', 'PROPOSTA', 'CONTRATADO', 'REPROVADO'])->default('INSCRITO');
                $table->text('curriculo_resumo')->nullable();
                $table->text('feedback_entrevista')->nullable();
                $table->timestamps();
            });
        }

        // 3. Avaliação de Desempenho: Ciclos & Competências Customizadas
        if (!Schema::hasTable('rh_avaliacao_ciclos')) {
            Schema::create('rh_avaliacao_ciclos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->string('titulo', 150); // Ex: Avaliação de Desempenho 2026.2
                $table->date('data_inicio');
                $table->date('data_fim');
                $table->enum('status', ['PLANEJADO', 'EM_ANDAMENTO', 'FINALIZADO'])->default('PLANEJADO');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_avaliacao_criterios')) {
            Schema::create('rh_avaliacao_criterios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('ciclo_id')->constrained('rh_avaliacao_ciclos')->onDelete('cascade');
                $table->string('criterio', 150); // Ex: Pontualidade, Qualidade Técnica, Proatividade
                $table->string('descricao', 255)->nullable();
                $table->decimal('peso', 3, 2)->default(1.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_avaliacao_respostas')) {
            Schema::create('rh_avaliacao_respostas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('ciclo_id')->constrained('rh_avaliacao_ciclos')->onDelete('cascade');
                $table->foreignId('colaborador_avaliado_id')->constrained('rh_colaboradores')->onDelete('cascade');
                $table->foreignId('avaliador_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('criterio_id')->constrained('rh_avaliacao_criterios')->onDelete('cascade');
                $table->integer('nota'); // 1 a 5
                $table->text('comentario')->nullable();
                $table->timestamps();

                $table->unique(['ciclo_id', 'colaborador_avaliado_id', 'avaliador_id', 'criterio_id'], 'uk_rh_aval_resp_unic');
            });
        }

        // 4. PDI & Treinamentos
        if (!Schema::hasTable('rh_treinamentos')) {
            Schema::create('rh_treinamentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('colaborador_id')->constrained('rh_colaboradores')->onDelete('cascade');
                $table->string('titulo_treinamento', 150);
                $table->string('instituicao', 100)->nullable();
                $table->integer('carga_horaria_horas')->default(0);
                $table->date('data_inicio')->nullable();
                $table->date('data_conclusao')->nullable();
                $table->enum('status', ['PLANEJADO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO'])->default('PLANEJADO');
                $table->text('objetivo_pdi')->nullable();
                $table->timestamps();
            });
        }

        // 5. Pesquisa de Clima & eNPS (100% Anônimo / LGPD Compliant)
        if (!Schema::hasTable('rh_clima_pesquisas')) {
            Schema::create('rh_clima_pesquisas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->string('titulo', 150); // Ex: Pesquisa de Clima Q3/2026
                $table->date('data_inicio');
                $table->date('data_fim');
                $table->enum('status', ['ABERTA', 'ENCERRADA'])->default('ABERTA');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_clima_respostas')) {
            Schema::create('rh_clima_respostas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('pesquisa_id')->constrained('rh_clima_pesquisas')->onDelete('cascade');
                $table->string('departamento', 100)->default('GERAL');
                $table->integer('nota_enps'); // 0 a 10
                $table->text('comentario_anonimo')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_clima_respostas');
        Schema::dropIfExists('rh_clima_pesquisas');
        Schema::dropIfExists('rh_treinamentos');
        Schema::dropIfExists('rh_avaliacao_respostas');
        Schema::dropIfExists('rh_avaliacao_criterios');
        Schema::dropIfExists('rh_avaliacao_ciclos');
        Schema::dropIfExists('rh_candidatos');
        Schema::dropIfExists('rh_vagas');
    }
};