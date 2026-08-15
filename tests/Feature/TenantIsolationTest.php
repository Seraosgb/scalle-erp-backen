<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Modules\Produtos\Models\Item;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TenantIsolationTest extends TestCase
{
    public function test_usuario_nao_consegue_visualizar_itens_de_outro_tenant(): void
    {
        DB::beginTransaction();

        try {
            // 1. Cria Empresa A e Empresa B
            $empresaA = Empresa::create([
                'razao_social' => 'Empresa A Teste LTDA',
                'cpf_cnpj' => '11111111000199',
                'crt' => 1,
                'ativo' => true,
            ]);

            $empresaB = Empresa::create([
                'razao_social' => 'Empresa B Teste LTDA',
                'cpf_cnpj' => '22222222000199',
                'crt' => 1,
                'ativo' => true,
            ]);

            // 2. Cria Usuários das duas empresas
            $userA = User::create([
                'empresa_id' => $empresaA->id,
                'name' => 'Admin Empresa A',
                'email' => 'admin.a.' . uniqid() . '@scalle.test',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
                'ativo' => true,
            ]);

            $userB = User::create([
                'empresa_id' => $empresaB->id,
                'name' => 'Admin Empresa B',
                'email' => 'admin.b.' . uniqid() . '@scalle.test',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
                'ativo' => true,
            ]);

            // 3. Cria Item exclusivo da Empresa B
            $itemB = Item::create([
                'empresa_id' => $empresaB->id,
                'nome' => 'Compressor Scroll Inverter (Empresa B)',
                'tipo' => 'P',
                'preco_venda' => 3500.00,
                'ativo' => true,
            ]);

            // 4. Usuário da Empresa A lista produtos e NÃO deve ver o item da Empresa B
            $response = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/produtos');

            $response->assertStatus(200);
            $itensRetornados = collect($response->json('data.data'))->pluck('id');
            $this->assertFalse($itensRetornados->contains($itemB->id));

            // 5. Usuário da Empresa A tenta acessar diretamente o ID do item da Empresa B (deve retornar 404)
            $responseShow = $this->actingAs($userA, 'sanctum')->getJson("/api/v1/produtos/{$itemB->id}");
            $responseShow->assertStatus(404);
        } finally {
            DB::rollBack();
        }
    }
}