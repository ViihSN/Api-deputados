<?php

namespace App\Http\Controllers;

use App\Models\Deputado;
use App\Models\Despesa;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Página inicial com estatísticas gerais
     */
    public function index()
    {
        $estatisticas = [
            'total_deputados' => Deputado::count(),
            'total_despesas' => Despesa::count(),
            'valor_total' => Despesa::sum('valor_liquido'),
            'ultima_atualizacao' => Deputado::latest('updated_at')->value('updated_at')?->format('d/m/Y H:i') ?? 'Nunca'
        ];

        $estatisticas['por_estado'] = Deputado::select('sigla_uf')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('sigla_uf')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $estatisticas['por_tipo_despesa'] = Despesa::select('tipo_despesa')
            ->selectRaw('SUM(valor_liquido) as total_valor')
            ->selectRaw('COUNT(*) as total_documentos')
            ->groupBy('tipo_despesa')
            ->orderBy('total_valor', 'desc')
            ->limit(10)
            ->get();

        $deputados_recentes = Deputado::latest('created_at')
            ->limit(10)
            ->get();

        $maiores_despesas = Despesa::with('deputado')
            ->orderBy('valor_liquido', 'desc')
            ->limit(10)
            ->get();

        return view('home', compact(
            'estatisticas',
            'deputados_recentes',
            'maiores_despesas'
        ));
    }
}

