@extends('layouts.app')

@section('title', $deputado->nome . ' - Deputados')

@section('content')
    <!-- Cabeçalho do Deputado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @if ($deputado->url_foto)
                                <img src="{{ $deputado->url_foto }}" alt="{{ $deputado->nome }}"
                                    class="rounded-circle img-fluid" style="max-width: 120px;"
                                    onerror="this.src='https://via.placeholder.com/120x120/cccccc/666666?text=N/A'">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 120px; height: 120px;">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h1 class="mb-2">{{ $deputado->nome_formatado }}</h1>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Partido:</strong>
                                        @if ($deputado->sigla_partido)
                                            <span class="badge bg-primary">{{ $deputado->sigla_partido }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                    <p class="mb-1">
                                        <strong>Estado:</strong>
                                        <span class="badge bg-info">{{ $deputado->sigla_uf }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Legislatura:</strong> {{ $deputado->id_legislatura }}
                                    </p>
                                    @if ($deputado->email)
                                        <p class="mb-1">
                                            <strong>Email:</strong>
                                            <a href="mailto:{{ $deputado->email }}">{{ $deputado->email }}</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button onclick="sincronizarDespesasDeputado({{ $deputado->deputado_id }})"
                                class="btn btn-sync mb-2">
                                <i class="fas fa-sync me-2"></i>
                                Sincronizar Despesas
                            </button>
                            <br>
                            <a href="{{ route('deputados.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-stats success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title text-muted">Total de Despesas</h5>
                            <h3 class="text-success">R$ {{ number_format($estatisticas['total_despesas'], 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
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
                            <h5 class="card-title text-muted">Total de Documentos</h5>
                            <h3 class="text-warning">{{ number_format($estatisticas['total_documentos']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice fa-2x text-warning"></i>
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
                            <h5 class="card-title text-muted">Maior Despesa</h5>
                            <h3 class="text-danger">R$
                                {{ number_format($estatisticas['maior_despesa'] ?? 0, 2, ',', '.') }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x text-danger"></i>
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
                            <h5 class="card-title text-muted">Média por Documento</h5>
                            <h3 class="text-primary">
                                R$
                                {{ number_format($estatisticas['total_documentos'] > 0 ? $estatisticas['total_despesas'] / $estatisticas['total_documentos'] : 0, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Despesas por Tipo -->
    @if (count($estatisticas['despesas_por_tipo']) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Despesas por Tipo
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartDespesasTipo" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros de Despesas -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtrar Despesas
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('deputados.show', $deputado->deputado_id) }}" class="row g-3">
                <div class="col-md-2">
                    <label for="ano" class="form-label">Ano</label>
                    <select class="form-select" id="ano" name="ano">
                        <option value="">Todos</option>
                        @for ($ano = date('Y'); $ano >= 2019; $ano--)
                            <option value="{{ $ano }}" {{ request('ano') == $ano ? 'selected' : '' }}>
                                {{ $ano }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="mes" class="form-label">Mês</label>
                    <select class="form-select" id="mes" name="mes">
                        <option value="">Todos</option>
                        @for ($mes = 1; $mes <= 12; $mes++)
                            <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                                {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo de Despesa</label>
                    <input type="text" class="form-control" id="tipo" name="tipo"
                        value="{{ request('tipo') }}" placeholder="Digite o tipo...">
                </div>

                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="{{ route('deputados.show', $deputado->deputado_id) }}"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Despesas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-money-bill-wave me-2"></i>
                Despesas ({{ $despesas->total() }} registros)
            </h5>
            <small class="text-muted">
                Página {{ $despesas->currentPage() }} de {{ $despesas->lastPage() }}
            </small>
        </div>
        <div class="card-body p-0">
            @if ($despesas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo de Despesa</th>
                                <th>Fornecedor</th>
                                <th>Valor</th>
                                <th>Documento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($despesas as $despesa)
                                <tr>
                                    <td>
                                        <strong>{{ $despesa->data_formatada }}</strong>
                                        <br><small
                                            class="text-muted">{{ str_pad($despesa->mes, 2, '0', STR_PAD_LEFT) }}/{{ $despesa->ano }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $despesa->tipo_despesa }}</span>
                                        @if ($despesa->tipo_documento)
                                            <br><small class="text-muted">{{ $despesa->tipo_documento }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($despesa->nome_fornecedor)
                                            <strong>{{ $despesa->nome_fornecedor }}</strong>
                                            @if ($despesa->cnpj_cpf_fornecedor)
                                                <br><small class="text-muted">{{ $despesa->cnpj_cpf_fornecedor }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-danger">{{ $despesa->valor_formatado }}</strong>
                                        @if ($despesa->valor_glosa > 0)
                                            <br><small class="text-warning">Glosa: R$
                                                {{ number_format($despesa->valor_glosa, 2, ',', '.') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($despesa->num_documento)
                                            <strong>{{ $despesa->num_documento }}</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                        @if ($despesa->cod_documento)
                                            <br><small class="text-muted">Cód: {{ $despesa->cod_documento }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($despesa->url_documento)
                                            <a href="{{ $despesa->url_documento }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary" title="Ver documento">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="card-footer">
                    {{ $despesas->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhuma despesa encontrada</h5>
                    <p class="text-muted">
                        @if (request()->hasAny(['ano', 'mes', 'tipo']))
                            Tente ajustar os filtros ou
                            <a href="{{ route('deputados.show', $deputado->deputado_id) }}">remover todos os filtros</a>.
                        @else
                            Execute a sincronização para carregar as despesas deste deputado.
                        @endif
                    </p>
                    <button onclick="sincronizarDespesasDeputado({{ $deputado->deputado_id }})" class="btn btn-primary">
                        <i class="fas fa-sync me-2"></i>Sincronizar Despesas
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function sincronizarDespesasDeputado(deputadoId) {
            if (confirm('Deseja sincronizar as despesas deste deputado? Esta operação pode demorar alguns minutos.')) {
                showLoading('Sincronizando despesas do deputado...');

                fetch(`/deputados/${deputadoId}/sincronizar-despesas`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            alert('Sincronização iniciada com sucesso! ' + data.message);
                            location.reload();
                        } else {
                            alert('Erro na sincronização: ' + data.message);
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        alert('Erro na sincronização: ' + error.message);
                    });
            }
        }

        // Gráfico de despesas por tipo
        @if (count($estatisticas['despesas_por_tipo']) > 0)
            const dadosDespesasTipo = @json($estatisticas['despesas_por_tipo']);

            const ctx = document.getElementById('chartDespesasTipo').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dadosDespesasTipo.map(item => item.tipo_despesa.substring(0, 30) + '...'),
                    datasets: [{
                        data: dadosDespesasTipo.map(item => item.total),
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': R$ ' + context.parsed.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        @endif
    </script>
@endpush
