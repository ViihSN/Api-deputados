<?php

namespace App\Http\Controllers;

use App\Models\Deputado;
use App\Services\CamaraApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class DeputadoController extends Controller
{
    private $camaraApiService;

    public function __construct(CamaraApiService $camaraApiService)
    {
        $this->camaraApiService = $camaraApiService;
    }

    /**
     * Listar deputados
     */
    public function index(Request $request)
    {
        $query = Deputado::query();

        // Filtros
        if ($request->filled('uf')) {
            $query->porUf($request->uf);
        }

        if ($request->filled('partido')) {
            $query->porPartido($request->partido);
        }

        if ($request->filled('legislatura')) {
            $query->porLegislatura($request->legislatura);
        }

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        $ordenacao = $request->get('ordenar', 'nome');
        $direcao = $request->get('direcao', 'asc');
        
        $query->orderBy($ordenacao, $direcao);

        $deputados = $query->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $deputados
            ]);
        }

        return view('deputados.index', compact('deputados'));
    }

    /**
     * Mostrar deputado específico
     */
    public function show($id, Request $request)
    {
        $deputado = Deputado::where('deputado_id', $id)->firstOrFail();
        
        $despesasQuery = $deputado->despesas();

        if ($request->filled('ano')) {
            $despesasQuery->porAno($request->ano);
        }

        if ($request->filled('mes')) {
            $despesasQuery->porMes($request->mes);
        }

        if ($request->filled('tipo')) {
            $despesasQuery->porTipo($request->tipo);
        }

        $despesas = $despesasQuery->orderBy('data_documento', 'desc')->paginate(15);

        $estatisticas = [
            'total_despesas' => $deputado->despesas()->sum('valor_liquido'),
            'total_documentos' => $deputado->despesas()->count(),
            'maior_despesa' => $deputado->despesas()->max('valor_liquido'),
            'despesas_por_tipo' => $deputado->despesas()
                ->selectRaw('tipo_despesa, SUM(valor_liquido) as total, COUNT(*) as quantidade')
                ->groupBy('tipo_despesa')
                ->orderBy('total', 'desc')
                ->get()
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'deputado' => $deputado,
                    'despesas' => $despesas,
                    'estatisticas' => $estatisticas
                ]
            ]);
        }

        return view('deputados.show', compact('deputado', 'despesas', 'estatisticas'));
    }

    /**
     * Sincronizar deputados da API
     */
    public function sincronizar(Request $request)
    {
        try {
            $params = [];
            
            if ($request->filled('legislatura')) {
                $params['idLegislatura'] = $request->legislatura;
            }

            $deputadosApi = $this->camaraApiService->getDeputados($params);
            $sincronizados = 0;
            $erros = [];

            foreach ($deputadosApi as $deputadoData) {
                try {
                    Deputado::updateOrCreate(
                        ['deputado_id' => $deputadoData['id']],
                        [
                            'nome' => $deputadoData['nome'],
                            'sigla_partido' => $deputadoData['siglaPartido'] ?? null,
                            'sigla_uf' => $deputadoData['siglaUf'],
                            'id_legislatura' => $deputadoData['idLegislatura'],
                            'url_foto' => $deputadoData['urlFoto'] ?? null,
                            'email' => $deputadoData['email'] ?? null
                        ]
                    );
                    $sincronizados++;
                } catch (\Exception $e) {
                    $erros[] = [
                        'deputado_id' => $deputadoData['id'],
                        'nome' => $deputadoData['nome'],
                        'erro' => $e->getMessage()
                    ];
                    Log::error('Erro ao sincronizar deputado', [
                        'deputado_id' => $deputadoData['id'],
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            $resultado = [
                'success' => true,
                'message' => "Sincronização concluída. {$sincronizados} deputados sincronizados.",
                'sincronizados' => $sincronizados,
                'erros' => count($erros),
                'detalhes_erros' => $erros
            ];

            if ($request->expectsJson()) {
                return response()->json($resultado);
            }

            return redirect()->route('deputados.index')
                ->with('success', $resultado['message']);

        } catch (\Exception $e) {
            Log::error('Erro na sincronização de deputados', [
                'erro' => $e->getMessage()
            ]);

            $erro = [
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
            ];

            if ($request->expectsJson()) {
                return response()->json($erro, 500);
            }

            return redirect()->route('deputados.index')
                ->with('error', $erro['message']);
        }
    }

    /**
     * API: Listar deputados (JSON)
     */
    public function apiIndex(Request $request)
    {
        return $this->index($request);
    }

    /**
     * API: Mostrar deputado (JSON)
     */
    public function apiShow($id, Request $request)
    {
        return $this->show($id, $request);
    }
}

