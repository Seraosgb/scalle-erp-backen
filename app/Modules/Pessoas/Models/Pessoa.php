<?php

namespace App\Modules\Pessoas\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    protected $table = 'pes_pessoas';

    protected $fillable = [
        'empresa_id',
        'tipo_pessoa',
        'nome_razao',
        'nome_fantasia',
        'cpf_cnpj',
        'email',
        'telefone',
        'is_cliente',
        'is_fornecedor',
        'ativo',
    ];
}