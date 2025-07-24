<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deputado extends Model
{
    use HasFactory;

    protected $table = 'deputados';

    protected $fillable = [
        'deputado_id',
        'nome',
        'sigla_partido',
        'sigla_uf',
        'id_legislatura',
        'url_foto',
        'email'
    ];

    protected $casts = [
        'deputado_id' => 'integer',
        'id_legislatura' => 'integer',
    ];

    /**
     * Relacionamento com despesas
     */
    public function despesas()
    {
        return $this->hasMany(Despesa::class, 'deputado_id', 'deputado_id');
    }

    /**
     * Scope para buscar por UF
     */
    public function scopePorUf($query, $uf)
    {
        return $query->where('sigla_uf', $uf);
    }

    /**
     * Scope para buscar por partido
     */
    public function scopePorPartido($query, $partido)
    {
        return $query->where('sigla_partido', $partido);
    }

    /**
     * Scope para buscar por legislatura
     */
    public function scopePorLegislatura($query, $legislatura)
    {
        return $query->where('id_legislatura', $legislatura);
    }

    /**
     * Accessor para nome formatado
     */
    public function getNomeFormatadoAttribute()
    {
        return ucwords(strtolower($this->nome));
    }

    /**
     * Calcular total de despesas do deputado
     */
    public function getTotalDespesasAttribute()
    {
        return $this->despesas()->sum('valor_liquido');
    }
}

