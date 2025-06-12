<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-shopping-cart"></i> Pedidos</h1>
</div>

<?php if (empty($pedidos)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum pedido encontrado.
        <a href="/produtos" class="alert-link">Clique aqui para fazer seu primeiro pedido</a>.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>E-mail</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><strong>#<?= $pedido['id'] ?></strong></td>
                                <td><?= htmlspecialchars($pedido['nome_cliente']) ?></td>
                                <td><?= htmlspecialchars($pedido['email_cliente']) ?></td>
                                <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                                <td>
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
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                                <td>
                                    <a href="/pedidos/view?id=<?= $pedido['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>