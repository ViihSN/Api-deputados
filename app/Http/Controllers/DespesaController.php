<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Deputado;
use App\Services\CamaraApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class DespesaController extends Controller
{
    private $camaraApiService;

    public function __construct(CamaraApiService $camaraApiService)
    {
        $this->camaraApiService = $camaraApiService;
    }

    /**
     * Listar despesas
     */
    public function index(Request $request)
    {
        $query = Despesa::with('deputado');

        if ($request->filled('deputado_id')) {
            $query->where('deputado_id', $request->deputado_id);
        }

        if ($request->filled('ano')) {
            $query->porAno($request->ano);
        }

        if ($request->filled('mes')) {
            $query->porMes($request->mes);
        }

        if ($request->filled('tipo')) {
            $query->porTipo($request->tipo);
        }

        if ($request->filled('valor_min')) {
            $query->where('valor_liquido', '>=', $request->valor_min);
        }

        if ($request->filled('valor_max')) {
            $query->where('valor_liquido', '<=', $request->valor_max);
        }

        if ($request->filled('fornecedor')) {
            $query->where('nome_fornecedor', 'like', '%' . $request->fornecedor . '%');
        }

        $ordenacao = $request->get('ordenar', 'data_documento');
        $direcao = $request->get('direcao', 'desc');
        
        $query->orderBy($ordenacao, $direcao);

        // Paginação
        $despesas = $query->paginate(20);

        $estatisticas = [
            'total_valor' => $query->sum('valor_liquido'),
            'total_documentos' => $query->count(),
            'maior_despesa' => $query->max('valor_liquido'),
            'tipos_despesa' => Despesa::selectRaw('tipo_despesa, COUNT(*) as quantidade')
                ->groupBy('tipo_despesa')
                ->orderBy('quantidade', 'desc')
                ->limit(10)
                ->get()
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'despesas' => $despesas,
                    'estatisticas' => $estatisticas
                ]
            ]);
        }

        return view('despesas.index', compact('despesas', 'estatisticas'));
    }

    /**
     * Mostrar despesa específica
     */
    public function show($id, Request $request)
    {
        $despesa = Despesa::with('deputado')->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $despesa
            ]);
        }

        return view('despesas.show', compact('despesa'));
    }

    /**
     * Sincronizar despesas de um deputado
     */
    public function sincronizarDeputado($deputadoId, Request $request)
    {
        try {
            $deputado = Deputado::where('deputado_id', $deputadoId)->firstOrFail();
            
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes');

            $despesasApi = $this->camaraApiService->getTodasDespesasDeputado($deputadoId, $ano, $mes);
            $sincronizadas = 0;
            $erros = [];

            foreach ($despesasApi as $despesaData) {
                try {
                    $identificador = [
                        'deputado_id' => $deputadoId,
                        'cod_documento' => $despesaData['codDocumento'] ?? null,
                        'num_documento' => $despesaData['numDocumento'] ?? null,
                        'data_documento' => $despesaData['dataDocumento'] ?? null,
                        'valor_documento' => $despesaData['valorDocumento'] ?? 0
                    ];

                    Despesa::updateOrCreate(
                        $identificador,
                        [
                            'ano' => $despesaData['ano'],
                            'mes' => $despesaData['mes'],
                            'tipo_despesa' => $despesaData['tipoDespesa'],
                            'tipo_documento' => $despesaData['tipoDocumento'] ?? null,
                            'cod_tipo_documento' => $despesaData['codTipoDocumento'] ?? null,
                            'valor_liquido' => $despesaData['valorLiquido'] ?? $despesaData['valorDocumento'],
                            'valor_glosa' => $despesaData['valorGlosa'] ?? 0,
                            'nome_fornecedor' => $despesaData['nomeFornecedor'] ?? null,
                            'cnpj_cpf_fornecedor' => $despesaData['cnpjCpfFornecedor'] ?? null,
                            'url_documento' => $despesaData['urlDocumento'] ?? null,
                            'num_ressarcimento' => $despesaData['numRessarcimento'] ?? null,
                            'cod_lote' => $despesaData['codLote'] ?? null,
                            'parcela' => $despesaData['parcela'] ?? null
                        ]
                    );
                    $sincronizadas++;
                } catch (\Exception $e) {
                    $erros[] = [
                        'documento' => $despesaData['numDocumento'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ];
                    Log::error('Erro ao sincronizar despesa', [
                        'deputado_id' => $deputadoId,
                        'despesa' => $despesaData,
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            $resultado = [
                'success' => true,
                'message' => "Sincronização concluída. {$sincronizadas} despesas sincronizadas para {$deputado->nome}.",
                'sincronizadas' => $sincronizadas,
                'erros' => count($erros),
                'detalhes_erros' => $erros
            ];

            if ($request->expectsJson()) {
                return response()->json($resultado);
            }

            return redirect()->route('deputados.show', $deputadoId)
                ->with('success', $resultado['message']);

        } catch (\Exception $e) {
            Log::error('Erro na sincronização de despesas', [
                'deputado_id' => $deputadoId,
                'erro' => $e->getMessage()
            ]);

            $erro = [
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
            ];

            if ($request->expectsJson()) {
                return response()->json($erro, 500);
            }

            return redirect()->route('deputados.show', $deputadoId)
                ->with('error', $erro['message']);
        }
    }

    /**
     * API: Listar despesas (JSON)
     */
    public function apiIndex(Request $request)
    {
        return $this->index($request);
    }

    /**
     * API: Mostrar despesa (JSON)
     */
    public function apiShow($id, Request $request)
    {
        return $this->show($id, $request);
    }
}

