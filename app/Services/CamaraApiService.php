<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class CamaraApiService
{
    private $client;
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://dadosabertos.camara.leg.br/api/v2/';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'allow_redirects' => true,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Laravel-CamaraAPI/1.0'
            ]
        ]);
    }

    /**
     * Buscar lista de deputados
     */
    public function getDeputados($params = [])
    {
        try {
            $response = $this->client->get('deputados', [
                'query' => array_merge([
                    'pagina' => 1
                ], $params)
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['dados'] ?? [];
        } catch (RequestException $e) {
            Log::error('Erro ao buscar deputados na API da Câmara', [
                'error' => $e->getMessage(),
                'response' => optional($e->getResponse())->getBody()->getContents(),
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Buscar detalhes de um deputado específico
     */
    public function getDeputado($deputadoId)
    {
        try {
            $response = $this->client->get("/deputados/{$deputadoId}");
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['dados'] ?? null;
        } catch (RequestException $e) {
            Log::error('Erro ao buscar deputado na API da Câmara', [
                'deputado_id' => $deputadoId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar despesas de um deputado
     */
    public function getDespesasDeputado($deputadoId, $params = [])
    {
        try {
            $defaultParams = [
                'ordem' => 'ASC',
                'ordenarPor' => 'dataDocumento'
            ];

            $queryParams = array_merge($defaultParams, $params);

            $response = $this->client->get("deputados/{$deputadoId}/despesas", [
                'query' => $queryParams
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['dados'] ?? [];
        } catch (RequestException $e) {
            Log::error('Erro ao buscar despesas na API da Câmara', [
                'deputado_id' => $deputadoId,
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Buscar despesas com paginação
     */
    public function getDespesasComPaginacao($deputadoId, $ano = null, $mes = null, $pagina = 1)
    {
        $params = [
            'pagina' => $pagina,
            'itens' => 100
        ];

        if ($ano) {
            $params['ano'] = $ano;
        }

        if ($mes) {
            $params['mes'] = $mes;
        }

        return $this->getDespesasDeputado($deputadoId, $params);
    }

    /**
     * Buscar todas as despesas de um deputado (com paginação automática)
     */
    public function getTodasDespesasDeputado($deputadoId, $ano = null, $mes = null)
    {
        $todasDespesas = [];
        $pagina = 1;
        $temMaisPaginas = true;

        while ($temMaisPaginas) {
            $despesas = $this->getDespesasComPaginacao($deputadoId, $ano, $mes, $pagina);

            if (empty($despesas)) {
                $temMaisPaginas = false;
            } else {
                $todasDespesas = array_merge($todasDespesas, $despesas);
                $pagina++;

                // Limite de segurança para evitar loops infinitos
                if ($pagina > 50) {
                    break;
                }
            }
        }

        return $todasDespesas;
    }

    /**
     * Buscar legislaturas
     */
    public function getLegislaturas()
    {
        try {
            $response = $this->client->get('/legislaturas');
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['dados'] ?? [];
        } catch (RequestException $e) {
            Log::error('Erro ao buscar legislaturas na API da Câmara', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
