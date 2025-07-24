@extends('layouts.app')

@section('title', 'Início - API Câmara dos Deputados')

@section('content')

    <div id="sincronizacao-automatica" class="alert alert-info text-center" style="display: none;">
        <i class="fas fa-sync-alt fa-spin me-2"></i>
        <strong>Nenhum deputado encontrado. Sincronizando dados, por favor aguarde...</strong>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="jumbotron bg-primary text-white rounded p-5 mb-4">
                <h1 class="display-4">
                    <i class="fas fa-university me-3"></i>
                    API Câmara dos Deputados
                </h1>
                <p class="lead">
                    Sistema de monitoramento e análise dos dados públicos da Câmara dos Deputados do Brasil.
                    Acompanhe informações sobre deputados e suas despesas de forma transparente e organizada.
                </p>
                <hr class="my-4 border-light">
                <p>
                    Sincronização feita através da API oficial:
                    <a href="https://dadosabertos.camara.leg.br" target="_blank" class="text-light">
                        <strong>dadosabertos.camara.leg.br</strong>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-stats success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title text-muted">Total de Deputados</h5>
                            <h2 class="text-success">{{ $estatisticas['total_deputados'] ?? 0 }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-stats warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title text-muted">Total de Despesas</h5>
                            <h2 class="text-warning">{{ number_format($estatisticas['total_despesas'] ?? 0) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-stats danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title text-muted">Valor Total</h5>
                            <h2 class="text-danger">R$ {{ number_format($estatisticas['valor_total'] ?? 0, 2, ',', '.') }}
                            </h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title text-muted">Última Atualização</h5>
                            <h6 class="text-primary">{{ $estatisticas['ultima_atualizacao'] ?? 'Nunca' }}</h6>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Análises -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Deputados por Estado
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEstados" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Despesas por Tipo
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartTiposDespesa" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Atualizações -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Deputados Recentes
                    </h5>
                </div>
                <div class="card-body">
                    @if (isset($deputados_recentes) && count($deputados_recentes) > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($deputados_recentes as $deputado)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $deputado->nome }}</strong><br>
                                        <small class="text-muted">{{ $deputado->sigla_partido }} -
                                            {{ $deputado->sigla_uf }}</small>
                                    </div>
                                    <a href="{{ route('deputados.show', $deputado->deputado_id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Nenhum deputado encontrado. Execute a sincronização.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Maiores Despesas Recentes
                    </h5>
                </div>
                <div class="card-body">
                    @if (isset($maiores_despesas) && count($maiores_despesas) > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($maiores_despesas as $despesa)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $despesa->deputado->nome ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $despesa->tipo_despesa }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-danger">R$
                                            {{ number_format($despesa->valor_liquido, 2, ',', '.') }}</strong><br>
                                        <small class="text-muted">{{ $despesa->data_formatada }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Nenhuma despesa encontrada. Execute a sincronização.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalDeputados = {{ $estatisticas['total_deputados'] ?? 0 }};

            if (totalDeputados === 0) {
                sincronizarAutomaticamente();
            }
        });

        function sincronizarAutomaticamente() {
            document.getElementById('sincronizacao-automatica').style.display = 'block';

            fetch('{{ route('deputados.sincronizar') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('A resposta do servidor indicou uma falha.');
                }
                return response.json();
            })
            .then(data => {
                console.log('Sincronização concluída com sucesso.', data);
                window.location.reload();
            })
            .catch(error => {
                console.error('Erro durante a sincronização:', error);
                const container = document.getElementById('sincronizacao-automatica');
                container.innerHTML = '<strong>Ocorreu um erro ao sincronizar. Por favor, atualize a página e tente novamente.</strong>';
                container.classList.remove('alert-info');
                container.classList.add('alert-danger');
            });
        }
    </script>

    <script>
        // Dados para os gráficos
        const dadosEstados = @json($estatisticas['por_estado'] ?? []);
        const dadosTiposDespesa = @json($estatisticas['por_tipo_despesa'] ?? []);

        // Gráfico de Estados
        if (dadosEstados.length > 0) {
            const ctxEstados = document.getElementById('chartEstados').getContext('2d');
            new Chart(ctxEstados, {
                type: 'doughnut',
                data: {
                    labels: dadosEstados.map(item => item.sigla_uf),
                    datasets: [{
                        data: dadosEstados.map(item => item.total),
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gráfico de Tipos de Despesa
        if (dadosTiposDespesa.length > 0) {
            const ctxTipos = document.getElementById('chartTiposDespesa').getContext('2d');
            new Chart(ctxTipos, {
                type: 'bar',
                data: {
                    labels: dadosTiposDespesa.map(item => item.tipo_despesa.substring(0, 20) + '...'),
                    datasets: [{
                        label: 'Valor Total (R$)',
                        data: dadosTiposDespesa.map(item => item.total_valor),
                        backgroundColor: '#36A2EB',
                        borderColor: '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
@endpush
