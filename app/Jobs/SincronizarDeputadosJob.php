<?php

namespace App\Jobs;

use App\Models\Deputado;
use App\Services\CamaraApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SincronizarDeputadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $legislatura;
    protected $forcarAtualizacao;

    /**
     * Create a new job instance.
     */
    public function __construct($legislatura = null, $forcarAtualizacao = false)
    {
        $this->legislatura = $legislatura;
        $this->forcarAtualizacao = $forcarAtualizacao;
    }

    /**
     * Execute the job.
     */
    public function handle(CamaraApiService $camaraApiService): void
    {

        try {
            $params = [];

            if ($this->legislatura) {
                $params['idLegislatura'] = $this->legislatura;
            }

            $deputadosApi = $camaraApiService->getDeputados($params);

            $sincronizados = 0;
            $atualizados = 0;
            $erros = 0;

            foreach ($deputadosApi as $deputadoData) {
                try {
                    $deputadoExistente = Deputado::where('deputado_id', $deputadoData['id'])->first();

                    if ($deputadoExistente && !$this->forcarAtualizacao) {
                        // Deputado já existe e não é para forçar atualização
                        continue;
                    }

                    $dadosDeputado = [
                        'nome' => $deputadoData['nome'],
                        'sigla_partido' => $deputadoData['siglaPartido'] ?? null,
                        'sigla_uf' => $deputadoData['siglaUf'],
                        'id_legislatura' => $deputadoData['idLegislatura'],
                        'url_foto' => $deputadoData['urlFoto'] ?? null,
                        'email' => $deputadoData['email'] ?? null
                    ];

                    if ($deputadoExistente) {
                        $deputadoExistente->update($dadosDeputado);
                        $atualizados++;
                    } else {
                        Deputado::create(array_merge(
                            ['deputado_id' => $deputadoData['id']],
                            $dadosDeputado
                        ));
                        $sincronizados++;
                    }
                } catch (\Exception $e) {
                    $erros++;
                    Log::error('Erro ao sincronizar deputado', [
                        'deputado_id' => $deputadoData['id'],
                        'nome' => $deputadoData['nome'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            if ($sincronizados > 0 || $atualizados > 0) {
                $deputadosParaSincronizar = Deputado::query();

                if ($this->legislatura) {
                    $deputadosParaSincronizar->where('id_legislatura', $this->legislatura);
                }

                $deputadosParaSincronizar = $deputadosParaSincronizar->pluck('deputado_id')->toArray();

                $lotes = array_chunk($deputadosParaSincronizar, 10);

                foreach ($lotes as $lote) {
                    SincronizarDespesasJob::dispatch($lote, date('Y'))
                        ->delay(now()->addMinutes(2));
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro crítico na sincronização de deputados', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de sincronização de deputados falhou', [
            'legislatura' => $this->legislatura,
            'erro' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
