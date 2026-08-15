<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Escalas / Jornadas de Trabalho
        if (!Schema::hasTable('rh_escalas')) {
            Schema::create('rh_escalas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->string('nome', 100);
                $table->enum('tipo_escala', ['5X2', '6X1', '12X36', 'FLEXIVEL'])->default('5X2');
                $table->decimal('horas_diarias_padrao', 4, 2)->default(8.80);
                $table->integer('tolerancia_minutos')->default(10);
                $table->enum('politica_extra', ['BANCO_HORAS', 'PAGAMENTO_FOLHA'])->default('BANCO_HORAS');
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }

        // 2. Ficha Funcional do Colaborador
        if (!Schema::hasTable('rh_colaboradores')) {
            Schema::create('rh_colaboradores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('escala_id')->nullable()->constrained('rh_escalas')->onDelete('set null');

                $table->string('matricula', 30)->unique();
                $table->string('nome_completo', 200);
                $table->string('cpf', 14);
                $table->string('cargo', 100);
                $table->string('departamento', 100)->default('OPERACIONAL');
                $table->date('data_admissao');
                $table->date('data_demissao')->nullable();
                $table->decimal('salario_base', 12, 2)->default(0.00);
                $table->enum('tipo_contrato', ['CLT', 'PJ', 'ESTAGIO', 'AUTONOMO'])->default('CLT');
                $table->enum('status', ['ATIVO', 'FERIAS', 'AFASTADO', 'DESLIGADO'])->default('ATIVO');
                $table->timestamps();

                $table->unique(['empresa_id', 'cpf'], 'uk_rh_colab_emp_cpf');
            });
        }

        // 3. Certificações Obrigatórias e Validades (NR-10, NR-35, CNH)
        if (!Schema::hasTable('rh_certificacoes')) {
            Schema::create('rh_certificacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('colaborador_id')->constrained('rh_colaboradores')->onDelete('cascade');

                $table->string('nome_certificacao', 100);
                $table->string('numero_registro', 100)->nullable();
                $table->date('data_emissao');
                $table->date('data_validade');
                $table->string('orgao_emissor', 100)->nullable();
                $table->enum('status', ['VALIDO', 'PRESTES_A_VENCER', 'VENCIDO'])->default('VALIDO');
                $table->text('observacoes')->nullable();
                $table->timestamps();
            });
        }

        // 4. Registro de Ponto Eletrônico Georreferenciado
        if (!Schema::hasTable('rh_pontos')) {
            Schema::create('rh_pontos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('colaborador_id')->constrained('rh_colaboradores')->onDelete('cascade');

                $table->date('data_referencia');
                $table->dateTime('entrada_1');
                $table->dateTime('saida_1')->nullable();
                $table->dateTime('entrada_2')->nullable();
                $table->dateTime('saida_2')->nullable();
                $table->decimal('total_horas_trabalhadas', 5, 2)->default(0.00);
                $table->decimal('saldo_horas_dia', 5, 2)->default(0.00);

                $table->string('latitude', 50)->nullable();
                $table->string('longitude', 50)->nullable();
                $table->string('ip_registro', 45)->nullable();
                $table->enum('status_aprovacao', ['PENDENTE', 'APROVADO', 'AJUSTADO', 'RECUSADO'])->default('APROVADO');
                $table->timestamps();

                $table->unique(['empresa_id', 'colaborador_id', 'data_referencia'], 'uk_rh_ponto_dia');
            });
        }

        // 5. Extrato de Banco de Horas Acumulado
        if (!Schema::hasTable('rh_banco_horas')) {
            Schema::create('rh_banco_horas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('colaborador_id')->constrained('rh_colaboradores')->onDelete('cascade');
                $table->foreignId('ponto_id')->nullable()->constrained('rh_pontos')->onDelete('set null');

                $table->date('data_lancamento');
                $table->enum('tipo', ['CREDITO', 'DEBITO', 'COMPENSACAO', 'PAGAMENTO']);
                $table->decimal('horas', 5, 2);
                $table->string('motivo', 255);
                $table->timestamps();
            });
        }

        // 6. Espelho de Folha / Holerite Interno Integrado ao Financeiro
        if (!Schema::hasTable('rh_holerites')) {
            Schema::create('rh_holerites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('sis_empresas')->onDelete('cascade');
                $table->foreignId('colaborador_id')->constrained('rh_colaboradores')->onDelete('cascade');
                $table->foreignId('lancamento_financeiro_id')->nullable()->constrained('fin_lancamentos')->onDelete('set null');

                $table->string('mes_ano_competencia', 7);
                $table->decimal('proventos_total', 12, 2)->default(0.00);
                $table->decimal('descontos_total', 12, 2)->default(0.00);
                $table->decimal('valor_liquido', 12, 2)->default(0.00);
                $table->json('itens_discriminados')->nullable();
                $table->enum('status', ['GERADO', 'ENVIADO', 'PAGO'])->default('GERADO');
                $table->timestamps();

                // Nome do índice encurtado para respeitar o limite de 64 chars do MySQL
                $table->unique(['empresa_id', 'colaborador_id', 'mes_ano_competencia'], 'uk_rh_holerite_comp');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_holerites');
        Schema::dropIfExists('rh_banco_horas');
        Schema::dropIfExists('rh_pontos');
        Schema::dropIfExists('rh_certificacoes');
        Schema::dropIfExists('rh_colaboradores');
        Schema::dropIfExists('rh_escalas');
    }
};