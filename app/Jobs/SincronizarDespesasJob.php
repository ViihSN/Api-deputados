<?php

namespace App\Jobs;

use App\Models\Deputado;
use App\Models\Despesa;
use App\Services\CamaraApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SincronizarDespesasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deputadosIds;
    protected $ano;
    protected $mes;
    protected $forcarAtualizacao;

    /**
     * Create a new job instance.
     */
    public function __construct($deputadosIds, $ano = null, $mes = null, $forcarAtualizacao = false)
    {
        $this->deputadosIds = is_array($deputadosIds) ? $deputadosIds : [$deputadosIds];
        $this->ano = $ano ?? date('Y');
        $this->mes = $mes;
        $this->forcarAtualizacao = $forcarAtualizacao;
    }

    /**
     * Execute the job.
     */
    public function handle(CamaraApiService $camaraApiService): void
    {
        $totalSincronizadas = 0;
        $totalAtualizadas = 0;
        $totalErros = 0;

        foreach ($this->deputadosIds as $deputadoId) {
            try {
                $deputado = Deputado::where('deputado_id', $deputadoId)->first();
                
                if (!$deputado) {
                    continue;
                }

                if (!$this->forcarAtualizacao) {
                    $despesasExistentes = Despesa::where('deputado_id', $deputadoId)
                        ->where('ano', $this->ano);
                    
                    if ($this->mes) {
                        $despesasExistentes->where('mes', $this->mes);
                    }
                    
                    if ($despesasExistentes->exists()) {
                        continue;
                    }
                }

                $despesasApi = $camaraApiService->getTodasDespesasDeputado(
                    $deputadoId, 
                    $this->ano, 
                    $this->mes
                );

                $sincronizadas = 0;
                $atualizadas = 0;
                $erros = 0;

                foreach ($despesasApi as $despesaData) {
                    try {
                        $identificador = [
                            'deputado_id' => $deputadoId,
                            'cod_documento' => $despesaData['codDocumento'] ?? null,
                            'num_documento' => $despesaData['numDocumento'] ?? null,
                            'data_documento' => $despesaData['dataDocumento'] ?? null,
                            'valor_documento' => $despesaData['valorDocumento'] ?? 0
                        ];

                        $dadosDespesa = [
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
                        ];

                        $despesaExistente = Despesa::where($identificador)->first();

                        if ($despesaExistente) {
                            if ($this->forcarAtualizacao) {
                                $despesaExistente->update($dadosDespesa);
                                $atualizadas++;
                            }
                        } else {
                            Despesa::create(array_merge($identificador, $dadosDespesa));
                            $sincronizadas++;
                        }

                    } catch (\Exception $e) {
                        $erros++;
                        Log::error('Erro ao sincronizar despesa individual', [
                            'deputado_id' => $deputadoId,
                            'documento' => $despesaData['numDocumento'] ?? 'N/A',
                            'erro' => $e->getMessage()
                        ]);
                    }
                }

                $totalSincronizadas += $sincronizadas;
                $totalAtualizadas += $atualizadas;
                $totalErros += $erros;

                sleep(1);

            } catch (\Exception $e) {
                $totalErros++;
                Log::error('Erro ao sincronizar despesas do deputado', [
                    'deputado_id' => $deputadoId,
                    'erro' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de sincronização de despesas falhou', [
            'deputados_ids' => $this->deputadosIds,
            'ano' => $this->ano,
            'mes' => $this->mes,
            'erro' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}

