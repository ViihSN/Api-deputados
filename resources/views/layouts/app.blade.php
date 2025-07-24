<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'API Câmara dos Deputados')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .navbar-brand {
            font-weight: bold;
        }

        .card-stats {
            border-left: 4px solid #007bff;
        }

        .card-stats.success {
            border-left-color: #28a745;
        }

        .card-stats.warning {
            border-left-color: #ffc107;
        }

        .card-stats.danger {
            border-left-color: #dc3545;
        }

        .table-responsive {
            border-radius: 0.375rem;
        }

        .btn-sync {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
        }

        .btn-sync:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            color: white;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 50px;
        }

        .loading {
            display: none;
        }

        .loading.show {
            display: block;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-university me-2"></i>
                Câmara dos Deputados API
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('deputados.*') ? 'active' : '' }}"
                            href="{{ route('deputados.index') }}">
                            <i class="fas fa-users me-1"></i>Deputados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('despesas.*') ? 'active' : '' }}"
                            href="{{ route('despesas.index') }}">
                            <i class="fas fa-money-bill-wave me-1"></i>Despesas
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <h5 id="loadingMessage">Processando...</h5>
                    <p class="text-muted mb-0">Por favor, aguarde.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container my-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer bg-light mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-university me-2"></i>
                        <strong>API Câmara dos Deputados</strong>
                    </p>
                    <small class="text-muted">
                        Dados obtidos de: <a href="https://dadosabertos.camara.leg.br"
                            target="_blank">dadosabertos.camara.leg.br</a>
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Última atualização: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Função para mostrar loading
        function showLoading(message = 'Processando...') {
            document.getElementById('loadingMessage').textContent = message;
            new bootstrap.Modal(document.getElementById('loadingModal')).show();
        }

        // Função para esconder loading
        function hideLoading() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
            if (modal) {
                modal.hide();
            }
        }

        // Funções de sincronização
        function sincronizarDeputados() {
            if (confirm('Deseja sincronizar os deputados? Esta operação pode demorar alguns minutos.')) {
                showLoading('Sincronizando deputados...');

                fetch('/sincronizacao/deputados', {
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

        // Auto-hide alerts após 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>

</html>
