<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Produto</h4>
            </div>
            <div class="card-body">
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required 
                               value="<?= htmlspecialchars($_POST['nome'] ?? $produto['nome']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" class="form-control" id="preco" name="preco" 
                                   step="0.01" min="0" required 
                                   value="<?= htmlspecialchars($_POST['preco'] ?? $produto['preco']) ?>">
                        </div>
                    </div>

                    <?php if (!empty($estoque)): ?>
                        <div class="mb-3">
                            <label class="form-label">Estoque Atual</label>
                            <?php foreach ($estoque as $index => $item): ?>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" readonly 
                                               value="<?= $item['variacao'] ? htmlspecialchars($item['variacao']) : 'Padrão' ?>">
                                        <input type="hidden" name="estoque_ids[]" value="<?= $item['id'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="quantidades[]" 
                                               min="0" value="<?= htmlspecialchars($_POST['quantidades'][$index] ?? $item['quantidade']) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Adicionar Novas Variações</label>
                        <div id="novas_variacoes_container">
                            <div class="row mb-2 nova-variacao-item">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="novas_variacoes[]" 
                                           placeholder="Ex: Tamanho G, Cor Verde">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="novas_quantidades[]" 
                                           placeholder="Quantidade" min="0" value="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger" onclick="removerNovaVariacao(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="adicionarNovaVariacao()">
                            <i class="fas fa-plus"></i> Adicionar Variação
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Atualizar Produto
                        </button>
                        <a href="/produtos" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function adicionarNovaVariacao() {
    const container = document.getElementById('novas_variacoes_container');
    const novaVariacao = document.createElement('div');
    novaVariacao.className = 'row mb-2 nova-variacao-item';
    novaVariacao.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control" name="novas_variacoes[]" 
                   placeholder="Ex: Tamanho G, Cor Verde">
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control" name="novas_quantidades[]" 
                   placeholder="Quantidade" min="0" value="0">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger" onclick="removerNovaVariacao(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(novaVariacao);
}

function removerNovaVariacao(button) {
    const container = document.getElementById('novas_variacoes_container');
    if (container.children.length > 1) {
        button.closest('.nova-variacao-item').remove();
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

