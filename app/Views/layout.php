<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini ERP - Montink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-nav{
            --bs-nav-link-color: rgba(255, 255, 255, 0.89) !important;
            --bs-nav-link-hover-color: rgba(58, 68, 123, 0.86) !important;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #FFFFFF !important;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .carrinho-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .carrinho-container {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/produtos">
                <i class="fas fa-store"></i> Mini ERP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/produtos">
                            <i class="fas fa-box"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/cupons">
                            <i class="fas fa-ticket-alt"></i> Cupons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pedidos">
                            <i class="fas fa-shopping-cart"></i> Pedidos
                        </a>
                    </li>
                </ul>
                <div class="carrinho-container">
                    <button class="btn btn-outline-light" onclick="toggleCarrinho()">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="carrinho-badge" class="carrinho-badge" style="display: none;">0</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Carrinho Sidebar -->
    <div id="carrinho-sidebar" class="position-fixed top-0 end-0 h-100 bg-white shadow-lg" style="width: 400px; z-index: 1050; transform: translateX(100%); transition: transform 0.3s;">
        <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Carrinho de Compras</h5>
                <button class="btn-close" onclick="toggleCarrinho()"></button>
            </div>
        </div>
        <div id="carrinho-content" class="p-3">
            <p class="text-muted">Carrinho vazio</p>
        </div>
        <div class="p-3 border-top mt-auto">
            <div class="d-grid">
                <button class="btn btn-primary" onclick="finalizarPedido()" disabled id="btn-finalizar">
                    Finalizar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div id="carrinho-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 1040; display: none;" onclick="toggleCarrinho()"></div>

    <div class="container mt-4">
        <?php echo $content ?? ''; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let carrinhoAberto = false;

        function toggleCarrinho() {
            const sidebar = document.getElementById('carrinho-sidebar');
            const overlay = document.getElementById('carrinho-overlay');
            
            if (carrinhoAberto) {
                sidebar.style.transform = 'translateX(100%)';
                overlay.style.display = 'none';
                carrinhoAberto = false;
            } else {
                sidebar.style.transform = 'translateX(0)';
                overlay.style.display = 'block';
                carrinhoAberto = true;
                atualizarCarrinho();
            }
        }

        function atualizarCarrinho() {
            fetch('/carrinho/get')
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('carrinho-content');
                    const badge = document.getElementById('carrinho-badge');
                    const btnFinalizar = document.getElementById('btn-finalizar');
                    
                    if (data.itens && data.itens.length > 0) {
                        let html = '';
                        let total = 0;
                        let totalItens = 0;
                        
                        data.itens.forEach(item => {
                            const subtotal = item.preco * item.quantidade;
                            total += subtotal;
                            totalItens += item.quantidade;
                            
                            html += `
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>${item.nome}</strong>
                                            ${item.variacao ? `<br><small class="text-muted">${item.variacao}</small>` : ''}
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="removerItem('${item.produto_id}_${item.variacao || 'sem_variacao'}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm" onclick="alterarQuantidade('${item.produto_id}_${item.variacao || 'sem_variacao'}', -1)">-</button>
                                            <input type="text" class="form-control form-control-sm text-center" value="${item.quantidade}" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="alterarQuantidade('${item.produto_id}_${item.variacao || 'sem_variacao'}', 1)">+</button>
                                        </div>
                                        <span>R$ ${subtotal.toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += `
                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between">
                                    <strong>Subtotal:</strong>
                                    <strong>R$ ${total.toFixed(2)}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Frete:</span>
                                    <span id="valor-frete">R$ ${calcularFrete(total).toFixed(2)}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <strong>Total:</strong>
                                    <strong>R$ ${(total + calcularFrete(total)).toFixed(2)}</strong>
                                </div>
                            </div>
                        `;
                        
                        content.innerHTML = html;
                        badge.textContent = totalItens;
                        badge.style.display = 'block';
                        btnFinalizar.disabled = false;
                    } else {
                        content.innerHTML = '<p class="text-muted">Carrinho vazio</p>';
                        badge.style.display = 'none';
                        btnFinalizar.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar carrinho:', error);
                });
        }

        function calcularFrete(subtotal) {
            if (subtotal >= 200) return 0;
            if (subtotal >= 52 && subtotal <= 166.59) return 15;
            return 20;
        }

        function adicionarAoCarrinho(produtoId, variacao = null) {
            const formData = new FormData();
            formData.append('produto_id', produtoId);
            if (variacao) formData.append('variacao', variacao);
            formData.append('quantidade', 1);

            fetch('/produtos/add-to-cart', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert(data.erro);
                } else {
                    atualizarCarrinho();
                    if (data.aviso) {
                        alert(data.aviso);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar ao carrinho:', error);
                alert('Erro ao adicionar produto ao carrinho');
            });
        }

        function removerItem(chave) {
            fetch('/carrinho/remover', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'chave=' + encodeURIComponent(chave)
            })
            .then(response => response.json())
            .then(data => {
                atualizarCarrinho();
            })
            .catch(error => {
                console.error('Erro ao remover item:', error);
            });
        }

        function alterarQuantidade(chave, delta) {
            fetch('/carrinho/alterar-quantidade', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'chave=' + encodeURIComponent(chave) + '&delta=' + delta
            })
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert(data.erro);
                }
                atualizarCarrinho();
            })
            .catch(error => {
                console.error('Erro ao alterar quantidade:', error);
            });
        }

        function finalizarPedido() {
            window.location.href = '/checkout';
        }

        // Atualizar carrinho ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            atualizarCarrinho();
        });
    </script>
</body>
</html>

