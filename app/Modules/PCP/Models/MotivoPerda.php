<?php

namespace App\Modules\PCP\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\BelongsToTenant;

class MotivoPerda extends Model
{
    protected $table = 'pcp_motivos_perda';
    protected $fillable = ['empresa_id', 'nome', 'ativo'];
}