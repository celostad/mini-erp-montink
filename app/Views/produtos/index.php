<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-box"></i> Produtos</h1>
    <a href="/produtos/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Produto
    </a>
</div>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum produto cadastrado ainda.
        <a href="/produtos/create" class="alert-link">Clique aqui para cadastrar o primeiro produto</a>.
    </div>
<?php else: ?>
    <div class="row">
        <?php 
        $produtosAgrupados = [];
        foreach ($produtos as $item) {
            if (!isset($produtosAgrupados[$item['id']])) {
                $produtosAgrupados[$item['id']] = [
                    'id' => $item['id'],
                    'nome' => $item['nome'],
                    'preco' => $item['preco'],
                    'created_at' => $item['created_at'],
                    'estoque' => []
                ];
            }
            if ($item['estoque_id']) {
                $produtosAgrupados[$item['id']]['estoque'][] = [
                    'id' => $item['estoque_id'],
                    'variacao' => $item['variacao'],
                    'quantidade' => $item['quantidade']
                ];
            }
        }
        ?>
        
        <?php foreach ($produtosAgrupados as $produto): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                        <p class="card-text">
                            <strong class="text-success">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></strong>
                        </p>
                        
                        <?php if (!empty($produto['estoque'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">Estoque:</small>
                                <?php foreach ($produto['estoque'] as $estoque): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary">
                                            <?= $estoque['variacao'] ? htmlspecialchars($estoque['variacao']) : 'Padrão' ?>
                                        </span>
                                        <span class="<?= $estoque['quantidade'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $estoque['quantidade'] ?> unidades
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="badge bg-warning">Sem estoque configurado</span>
                            </div>
                        <?php endif; ?>
                        
                        <small class="text-muted">
                            Cadastrado em: <?= date('d/m/Y H:i', strtotime($produto['created_at'])) ?>
                        </small>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <a href="/produtos/edit?id=<?= $produto['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            
                            <?php if (!empty($produto['estoque'])): ?>
                                <?php if (count($produto['estoque']) == 1): ?>
                                    <?php $estoque = $produto['estoque'][0]; ?>
                                    <?php if ($estoque['quantidade'] > 0): ?>
                                        <button class="btn btn-success btn-sm flex-fill" 
                                                onclick="adicionarAoCarrinho(<?= $produto['id'] ?>, '<?= $estoque['variacao'] ?>')">
                                            <i class="fas fa-cart-plus"></i> Comprar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                            <i class="fas fa-times"></i> Sem Estoque
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="dropdown flex-fill">
                                        <button class="btn btn-success btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-cart-plus"></i> Comprar
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($produto['estoque'] as $estoque): ?>
                                                <?php if ($estoque['quantidade'] > 0): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="adicionarAoCarrinho(<?= $produto['id'] ?>, '<?= $estoque['variacao'] ?>')">
                                                            <?= $estoque['variacao'] ? htmlspecialchars($estoque['variacao']) : 'Padrão' ?>
                                                            (<?= $estoque['quantidade'] ?> disponíveis)
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                    <i class="fas fa-times"></i> Sem Estoque
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

