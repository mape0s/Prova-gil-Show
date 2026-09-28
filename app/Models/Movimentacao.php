<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Movimentacao extends Model implements Auditable
{
    use AuditableTrait;
    protected $table = 'movimentacoes';

    protected $fillable = ['conta_id', 'tipo', 'valor', 'natureza', 'descricao'];

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }
}
