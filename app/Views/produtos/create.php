<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-plus"></i> Novo Produto</h4>
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
                               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" class="form-control" id="preco" name="preco" 
                                   step="0.01" min="0" required 
                                   value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tem_variacoes" onchange="toggleVariacoes()">
                            <label class="form-check-label" for="tem_variacoes">
                                Este produto possui variações (tamanho, cor, etc.)
                            </label>
                        </div>
                    </div>

                    <div id="sem_variacoes" class="mb-3">
                        <label for="quantidade_geral" class="form-label">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="quantidade_geral" name="quantidade_geral" 
                               min="0" value="<?= htmlspecialchars($_POST['quantidade_geral'] ?? '0') ?>">
                    </div>

                    <div id="com_variacoes" class="mb-3" style="display: none;">
                        <label class="form-label">Variações e Estoque</label>
                        <div id="variacoes_container">
                            <div class="row mb-2 variacao-item">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="variacoes[]" 
                                           placeholder="Ex: Tamanho P, Cor Azul">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="quantidades[]" 
                                           placeholder="Quantidade" min="0" value="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger" onclick="removerVariacao(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="adicionarVariacao()">
                            <i class="fas fa-plus"></i> Adicionar Variação
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Produto
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
function toggleVariacoes() {
    const checkbox = document.getElementById('tem_variacoes');
    const semVariacoes = document.getElementById('sem_variacoes');
    const comVariacoes = document.getElementById('com_variacoes');
    
    if (checkbox.checked) {
        semVariacoes.style.display = 'none';
        comVariacoes.style.display = 'block';
    } else {
        semVariacoes.style.display = 'block';
        comVariacoes.style.display = 'none';
    }
}

function adicionarVariacao() {
    const container = document.getElementById('variacoes_container');
    const novaVariacao = document.createElement('div');
    novaVariacao.className = 'row mb-2 variacao-item';
    novaVariacao.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control" name="variacoes[]" 
                   placeholder="Ex: Tamanho P, Cor Azul">
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control" name="quantidades[]" 
                   placeholder="Quantidade" min="0" value="0">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger" onclick="removerVariacao(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(novaVariacao);
}

function removerVariacao(button) {
    const container = document.getElementById('variacoes_container');
    if (container.children.length > 1) {
        button.closest('.variacao-item').remove();
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

