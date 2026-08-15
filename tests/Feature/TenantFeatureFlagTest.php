<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Plano;
use App\Services\StorageQuotaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class TenantFeatureFlagTest extends TestCase
{
    public function test_usuario_mei_e_bloqueado_ao_tentar_acessar_modulo_de_frotas(): void
    {
        DB::beginTransaction();

        try {
            $planoMei = Plano::where('slug', 'mei')->first();

            $empresaMei = Empresa::create([
                'razao_social' => 'Prestador Individual MEI',
                'cpf_cnpj' => '33333333000188',
                'crt' => 1,
                'plano_id' => $planoMei->id,
                'status_assinatura' => 'ATIVO',
                'ativo' => true,
            ]);

            $userMei = User::create([
                'empresa_id' => $empresaMei->id,
                'name' => 'Técnico MEI',
                'email' => 'mei.' . uniqid() . '@scalle.test',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
                'ativo' => true,
            ]);

            // Usuário MEI tenta acessar listagem de veículos (módulo não liberado no plano MEI)
            $response = $this->actingAs($userMei, 'sanctum')->getJson('/api/v1/frotas/veiculos');

            $response->assertStatus(403);
            $response->assertJson([
                'status' => 'error',
            ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_bloqueio_de_upload_quando_estoura_a_cota_de_storage(): void
    {
        DB::beginTransaction();

        try {
            Storage::fake('public');

            $planoMei = Plano::where('slug', 'mei')->first();

            // Empresa MEI com cota de 3GB já quase no limite (2.99 GB ocupados)
            $empresa = Empresa::create([
                'razao_social' => 'Empresa Quase Cheia LTDA',
                'cpf_cnpj' => '44444444000177',
                'crt' => 1,
                'plano_id' => $planoMei->id,
                'status_assinatura' => 'ATIVO',
                'storage_utilizado_bytes' => (3 * 1024 * 1024 * 1024) - 500, // Sobram só 500 bytes
                'ativo' => true,
            ]);

            $user = User::create([
                'empresa_id' => $empresa->id,
                'name' => 'Operador',
                'email' => 'quota.' . uniqid() . '@scalle.test',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
                'ativo' => true,
            ]);

            $service = new StorageQuotaService();
            $arquivoFake = UploadedFile::fake()->create('foto_evidencia.jpg', 2048); // 2 MB (ultrapassa os 500 bytes)

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Cota de armazenamento excedida!');

            $service->validarEArmazenar($empresa->id, $user->id, $arquivoFake, 'OS_EVIDENCIA', 'evidencias');
        } finally {
            DB::rollBack();
        }
    }
}