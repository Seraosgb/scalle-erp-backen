<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'sis_empresas';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cpf_cnpj',
        'ativo',
    ];
}