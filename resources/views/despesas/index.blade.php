@extends('layouts.app')

@section('title', 'Despesas - API Câmara dos Deputados')

@section('content')
    <div class="d-flex justify-content-start align-items-center mb-4">
        <h1>
            <i class="fas fa-money-bill-wave me-2"></i>
            Despesas
        </h1>
    </div>

    <!-- Estatísticas Rápidas -->
    @if (isset($estatisticas))
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card card-stats success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title text-muted">Total de Despesas</h5>
                                <h3 class="text-success">{{ number_format($estatisticas['total_documentos']) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-invoice fa-2x text-success"></i>
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
                                <h3 class="text-danger">R$ {{ number_format($estatisticas['total_valor'], 2, ',', '.') }}
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
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
                                <h5 class="card-title text-muted">Maior Despesa</h5>
                                <h3 class="text-warning">R$ {{ number_format($estatisticas['maior_despesa'], 2, ',', '.') }}
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-up fa-2x text-warning"></i>
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
                                <h5 class="card-title text-muted">Média por Despesa</h5>
                                <h3 class="text-primary">R$
                                    {{ number_format($estatisticas['total_documentos'] > 0 ? $estatisticas['total_valor'] / $estatisticas['total_documentos'] : 0, 2, ',', '.') }}
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
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('despesas.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="deputado_id" class="form-label">ID Deputado</label>
                    <input type="number" class="form-control" id="deputado_id" name="deputado_id"
                        value="{{ request('deputado_id') }}" placeholder="Ex: 204554">
                </div>

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

                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Despesa</label>
                    <input type="text" class="form-control" id="tipo" name="tipo" value="{{ request('tipo') }}"
                        placeholder="Digite o tipo...">
                </div>

                <div class="col-md-3">
                    <label for="fornecedor" class="form-label">Fornecedor</label>
                    <input type="text" class="form-control" id="fornecedor" name="fornecedor"
                        value="{{ request('fornecedor') }}" placeholder="Nome do fornecedor...">
                </div>

                <div class="col-md-2">
                    <label for="valor_min" class="form-label">Valor Mín (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_min" name="valor_min"
                        value="{{ request('valor_min') }}" placeholder="0.00">
                </div>

                <div class="col-md-2">
                    <label for="valor_max" class="form-label">Valor Máx (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_max" name="valor_max"
                        value="{{ request('valor_max') }}" placeholder="999999.99">
                </div>

                <div class="col-md-3">
                    <label for="ordenar" class="form-label">Ordenar por</label>
                    <div class="input-group">
                        <select class="form-select" id="ordenar" name="ordenar">
                            <option value="data_documento" {{ request('ordenar') == 'data_documento' ? 'selected' : '' }}>
                                Data</option>
                            <option value="valor_liquido" {{ request('ordenar') == 'valor_liquido' ? 'selected' : '' }}>
                                Valor</option>
                            <option value="tipo_despesa" {{ request('ordenar') == 'tipo_despesa' ? 'selected' : '' }}>Tipo
                            </option>
                            <option value="nome_fornecedor"
                                {{ request('ordenar') == 'nome_fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                        </select>
                        <select class="form-select" name="direcao" style="max-width: 100px;">
                            <option value="desc" {{ request('direcao') == 'desc' ? 'selected' : '' }}>↓</option>
                            <option value="asc" {{ request('direcao') == 'asc' ? 'selected' : '' }}>↑</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="{{ route('despesas.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                Resultados ({{ $despesas->total() }} despesas)
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
                                <th>Deputado</th>
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

                                        <small
                                            class="text-muted">{{ str_pad($despesa->mes, 2, '0', STR_PAD_LEFT) }}/{{ $despesa->ano }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($despesa->deputado)
                                            <a href="{{ route('deputados.show', $despesa->deputado->deputado_id) }}"
                                                class="text-decoration-none">
                                                <strong>{{ $despesa->deputado->nome }}</strong>
                                            </a>

                                            <small class="text-muted">{{ $despesa->deputado->sigla_partido }} -
                                                {{ $despesa->deputado->sigla_uf }}</small>
                                        @else
                                            <span class="text-muted">ID: {{ $despesa->deputado_id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $despesa->tipo_despesa }}</span>
                                        @if ($despesa->tipo_documento)
                                            <small class="text-muted">{{ $despesa->tipo_documento }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($despesa->nome_fornecedor)
                                            <strong>{{ Str::limit($despesa->nome_fornecedor, 30) }}</strong>
                                            @if ($despesa->cnpj_cpf_fornecedor)
                                                <small class="text-muted">{{ $despesa->cnpj_cpf_fornecedor }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-danger">{{ $despesa->valor_formatado }}</strong>
                                        @if ($despesa->valor_glosa > 0)
                                            <small class="text-warning">Glosa: R$
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
                                            <small class="text-muted">Cód: {{ $despesa->cod_documento }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if ($despesa->url_documento)
                                                <a href="{{ $despesa->url_documento }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary" title="Ver documento">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                            @if ($despesa->deputado)
                                                <a href="{{ route('deputados.show', $despesa->deputado->deputado_id) }}"
                                                    class="btn btn-sm btn-outline-info" title="Ver deputado">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            @endif
                                        </div>
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
                        @if (request()->hasAny(['deputado_id', 'ano', 'mes', 'tipo', 'fornecedor', 'valor_min', 'valor_max']))
                            Tente ajustar os filtros ou
                            <a href="{{ route('despesas.index') }}">remover todos os filtros</a>.
                        @else
                            Execute a sincronização para carregar os dados das despesas.
                        @endif
                    </p>
                    @if (!request()->hasAny(['deputado_id', 'ano', 'mes', 'tipo', 'fornecedor', 'valor_min', 'valor_max']))
                        <a href="{{ route('deputados.index') }}" class="btn btn-primary">
                            <i class="fas fa-users me-2"></i>Ver Deputados
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
