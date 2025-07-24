<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    use HasFactory;

    protected $table = 'despesas';

    protected $fillable = [
        'deputado_id',
        'ano',
        'mes',
        'tipo_despesa',
        'cod_documento',
        'tipo_documento',
        'cod_tipo_documento',
        'data_documento',
        'num_documento',
        'valor_documento',
        'url_documento',
        'nome_fornecedor',
        'cnpj_cpf_fornecedor',
        'valor_liquido',
        'valor_glosa',
        'num_ressarcimento',
        'cod_lote',
        'parcela'
    ];

    protected $casts = [
        'deputado_id' => 'integer',
        'ano' => 'integer',
        'mes' => 'integer',
        'cod_tipo_documento' => 'integer',
        'data_documento' => 'date',
        'valor_documento' => 'decimal:2',
        'valor_liquido' => 'decimal:2',
        'valor_glosa' => 'decimal:2',
        'cod_lote' => 'integer',
        'parcela' => 'integer',
    ];

    /**
     * Relacionamento com deputado
     */
    public function deputado()
    {
        return $this->belongsTo(Deputado::class, 'deputado_id', 'deputado_id');
    }

    /**
     * Scope para filtrar por ano
     */
    public function scopePorAno($query, $ano)
    {
        return $query->where('ano', $ano);
    }

    /**
     * Scope para filtrar por mês
     */
    public function scopePorMes($query, $mes)
    {
        return $query->where('mes', $mes);
    }

    /**
     * Scope para filtrar por tipo de despesa
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_despesa', 'like', '%' . $tipo . '%');
    }

    /**
     * Scope para filtrar por período
     */
    public function scopePorPeriodo($query, $anoInicio, $mesInicio, $anoFim, $mesFim)
    {
        return $query->where(function ($q) use ($anoInicio, $mesInicio, $anoFim, $mesFim) {
            $q->where('ano', '>', $anoInicio)
              ->orWhere(function ($q2) use ($anoInicio, $mesInicio) {
                  $q2->where('ano', $anoInicio)->where('mes', '>=', $mesInicio);
              });
        })->where(function ($q) use ($anoFim, $mesFim) {
            $q->where('ano', '<', $anoFim)
              ->orWhere(function ($q2) use ($anoFim, $mesFim) {
                  $q2->where('ano', $anoFim)->where('mes', '<=', $mesFim);
              });
        });
    }

    /**
     * Accessor para valor formatado
     */
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_liquido, 2, ',', '.');
    }

    /**
     * Accessor para data formatada
     */
    public function getDataFormatadaAttribute()
    {
        return $this->data_documento ? $this->data_documento->format('d/m/Y') : '';
    }
}

