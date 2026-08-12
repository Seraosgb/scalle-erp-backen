<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::create([
            'razao_social' => 'Aliados da Manutenção Ltda',
            'nome_fantasia' => 'Aliados Tech',
            'cpf_cnpj' => '12345678000199',
            'ativo' => true,
        ]);

        User::create([
            'empresa_id' => $empresa->id,
            'name' => 'Bruno Soares',
            'email' => 'bruno@scalle.com',
            'password' => Hash::make('12345678'),
            'ativo' => true,
        ]);
    }
}