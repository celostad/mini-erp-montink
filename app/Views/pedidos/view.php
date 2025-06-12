<?php ob_start(); ?>

<div class="text-center">         
    
    <div class="alert alert-info">
        <h4>Pedido #<?= $pedido['id'] ?></h4>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detalhes do Pedido</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Cliente:</strong> <?= htmlspecialchars($pedido['nome_cliente']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>E-mail:</strong> <?= htmlspecialchars($pedido['email_cliente']) ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>CEP:</strong> <?= htmlspecialchars($pedido['cep']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> 

                    <?php
                        $statusClass = '';
                        switch ($pedido['status']) {
                            case 'pendente':
                                $statusClass = 'bg-warning';
                                break;
                            case 'processando':
                                $statusClass = 'bg-info';
                                break;
                            case 'enviado':
                                $statusClass = 'bg-primary';
                                break;
                            case 'entregue':
                                $statusClass = 'bg-success';
                                break;
                            case 'cancelado':
                                $statusClass = 'bg-danger';
                                break;
                            default:
                                $statusClass = 'bg-secondary';
                        }
                    ?>



                        <span class="badge <?= $statusClass ?>"><?= ucfirst($pedido['status']) ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Endereço de Entrega:</strong><br>
                    <?= nl2br(htmlspecialchars($pedido['endereco'])) ?>
                </div>

                <h6>Itens do Pedido:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Variação</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                                    <td><?= $item['variacao'] ? htmlspecialchars($item['variacao']) : 'Padrão' ?></td>
                                    <td><?= $item['quantidade'] ?></td>
                                    <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3 pt-3 border-top">
                    <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>R$ <?= number_format($pedido['subtotal'], 2, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Frete:</span>
                            <span>R$ <?= number_format($pedido['frete'], 2, ',', '.') ?></span>
                        </div>
                        <?php if ($pedido['desconto'] > 0): ?>
                            <div class="d-flex justify-content-between text-success">
                                <span>Desconto:</span>
                                <span>- R$ <?= number_format($pedido['desconto'], 2, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <strong>Total:</strong>
                            <strong>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            
            <a href="/pedidos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

