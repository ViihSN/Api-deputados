@extends('layouts.app')

@section('title', 'Deputados - API Câmara dos Deputados')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="fas fa-users me-2"></i>
            Deputados
        </h1>
        <button onclick="sincronizarDeputados()" class="btn btn-sync">
            <i class="fas fa-sync me-2"></i>
            Sincronizar
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('deputados.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="{{ request('nome') }}"
                        placeholder="Digite o nome...">
                </div>

                <div class="col-md-2">
                    <label for="uf" class="form-label">Estado (UF)</label>
                    <select class="form-select" id="uf" name="uf">
                        <option value="">Todos</option>
                        @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $estado)
                            <option value="{{ $estado }}" {{ request('uf') == $estado ? 'selected' : '' }}>
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="partido" class="form-label">Partido</label>
                    <input type="text" class="form-control" id="partido" name="partido"
                        value="{{ request('partido') }}" placeholder="Ex: PT, PSDB...">
                </div>

                <div class="col-md-2">
                    <label for="legislatura" class="form-label">Legislatura</label>
                    <input type="number" class="form-control" id="legislatura" name="legislatura"
                        value="{{ request('legislatura') }}" placeholder="Ex: 57">
                </div>

                <div class="col-md-3">
                    <label for="ordenar" class="form-label">Ordenar por</label>
                    <div class="input-group">
                        <select class="form-select" id="ordenar" name="ordenar">
                            <option value="nome" {{ request('ordenar') == 'nome' ? 'selected' : '' }}>Nome</option>
                            <option value="sigla_uf" {{ request('ordenar') == 'sigla_uf' ? 'selected' : '' }}>Estado
                            </option>
                            <option value="sigla_partido" {{ request('ordenar') == 'sigla_partido' ? 'selected' : '' }}>
                                Partido</option>
                            <option value="created_at" {{ request('ordenar') == 'created_at' ? 'selected' : '' }}>Data
                                Cadastro</option>
                        </select>
                        <select class="form-select" name="direcao" style="max-width: 100px;">
                            <option value="asc" {{ request('direcao') == 'asc' ? 'selected' : '' }}>↑</option>
                            <option value="desc" {{ request('direcao') == 'desc' ? 'selected' : '' }}>↓</option>
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('deputados.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                Resultados ({{ $deputados->total() }} deputados)
            </h5>
            <small class="text-muted">
                Página {{ $deputados->currentPage() }} de {{ $deputados->lastPage() }}
            </small>
        </div>
        <div class="card-body p-0">
            @if ($deputados->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Partido</th>
                                <th>Estado</th>
                                <th>Legislatura</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deputados as $deputado)
                                <tr>
                                    <td>
                                        @if ($deputado->url_foto)
                                            <img src="{{ $deputado->url_foto }}" alt="{{ $deputado->nome }}"
                                                class="rounded-circle" width="40" height="40"
                                                onerror="this.src='https://via.placeholder.com/40x40/cccccc/666666?text=N/A'">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $deputado->nome_formatado }}</strong>
                                        @if ($deputado->email)
                                            <br><small class="text-muted">{{ $deputado->email }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($deputado->sigla_partido)
                                            <span class="badge bg-primary">{{ $deputado->sigla_partido }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $deputado->sigla_uf }}</span>
                                    </td>
                                    <td>{{ $deputado->id_legislatura }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('deputados.show', $deputado->deputado_id) }}"
                                                class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="sincronizarDespesasDeputado({{ $deputado->deputado_id }})"
                                                class="btn btn-sm btn-outline-success" title="Sincronizar despesas">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="card-footer">
                    {{ $deputados->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum deputado encontrado</h5>
                    <p class="text-muted">
                        @if (request()->hasAny(['nome', 'uf', 'partido', 'legislatura']))
                            Tente ajustar os filtros ou
                            <a href="{{ route('deputados.index') }}">remover todos os filtros</a>.
                        @else
                            Execute a sincronização para carregar os dados dos deputados.
                        @endif
                    </p>
                    @if (!request()->hasAny(['nome', 'uf', 'partido', 'legislatura']))
                        <button onclick="sincronizarDeputados()" class="btn btn-primary">
                            <i class="fas fa-sync me-2"></i>Sincronizar Deputados
                        </button>
                    @endif
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
    </script>
@endpush
