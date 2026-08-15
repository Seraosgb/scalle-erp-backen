<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Plano;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ExportacaoContabilTest extends TestCase
{
    public function test_usuario_pro_consegue_exportar_movimentacao_contabil_em_json_e_csv(): void
    {
        DB::beginTransaction();

        try {
            $planoPro = Plano::where('slug', 'pro')->first();

            $empresa = Empresa::create([
                'razao_social' => 'Empresa Contábil Teste LTDA',
                'cpf_cnpj' => '55555555000166',
                'crt' => 1,
                'plano_id' => $planoPro->id,
                'status_assinatura' => 'ATIVO',
                'ativo' => true,
            ]);

            $user = User::create([
                'empresa_id' => $empresa->id,
                'name' => 'Financeiro Pro',
                'email' => 'fin.pro.' . uniqid() . '@scalle.test',
                'password' => Hash::make('password123'),
                'role' => 'FINANCEIRO',
                'ativo' => true,
            ]);

            // Cria Lançamento Financeiro para teste
            LancamentoFinanceiro::create([
                'empresa_id' => $empresa->id,
                'tipo' => 'RECEITA',
                'descricao' => 'Contrato Manutenção Preventiva Q3',
                'valor' => 4500.00,
                'data_vencimento' => '2026-08-15',
                'status' => 'PAGO',
                'forma_pagamento' => 'PIX',
                'parcela_atual' => 1,
                'total_parcelas' => 1,
            ]);

            // 1. Testa Exportação JSON
            $responseJson = $this->actingAs($user, 'sanctum')->getJson('/api/v1/exportacao-contabil?data_inicio=2026-08-01&data_fim=2026-08-31&formato=JSON');

            $responseJson->assertStatus(200);
            $responseJson->assertJsonPath('status', 'success');
            $this->assertNotEmpty($responseJson->json('data.financeiro'));

            // 2. Testa Exportação CSV
            $responseCsv = $this->actingAs($user, 'sanctum')->get('/api/v1/exportacao-contabil?data_inicio=2026-08-01&data_fim=2026-08-31&formato=CSV');

            $responseCsv->assertStatus(200);
            $this->assertStringContainsString('Contrato Manutenção Preventiva Q3', $responseCsv->getContent());
        } finally {
            DB::rollBack();
        }
    }
}