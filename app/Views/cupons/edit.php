<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Cupom</h4>
            </div>
            <div class="card-body">
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código do Cupom *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required 
                                       style="text-transform: uppercase;"
                                       value="<?= htmlspecialchars($_POST['codigo'] ?? $cupom['codigo']) ?>"
                                       placeholder="Ex: DESCONTO10">
                                <small class="form-text text-muted">O código será convertido automaticamente para maiúsculas</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_desconto" class="form-label">Tipo de Desconto *</label>
                                <select class="form-select" id="tipo_desconto" name="tipo_desconto" required onchange="updateDescontoLabel()">
                                    <option value="percentual" <?= ($_POST['tipo_desconto'] ?? $cupom['tipo_desconto']) === 'percentual' ? 'selected' : '' ?>>Percentual (%)</option>
                                    <option value="fixo" <?= ($_POST['tipo_desconto'] ?? $cupom['tipo_desconto']) === 'fixo' ? 'selected' : '' ?>>Valor Fixo (R$)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="desconto" class="form-label" id="desconto-label">Desconto (%) *</label>
                                <input type="number" class="form-control" id="desconto" name="desconto" 
                                       step="0.01" min="0" required 
                                       value="<?= htmlspecialchars($_POST['desconto'] ?? $cupom['desconto']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valor_minimo" class="form-label">Valor Mínimo do Pedido</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor_minimo" name="valor_minimo" 
                                           step="0.01" min="0" 
                                           value="<?= htmlspecialchars($_POST['valor_minimo'] ?? $cupom['valor_minimo']) ?>">
                                </div>
                                <small class="form-text text-muted">Valor mínimo do carrinho para aplicar o cupom</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_validade" class="form-label">Data de Validade *</label>
                                <input type="date" class="form-control" id="data_validade" name="data_validade" required 
                                       value="<?= htmlspecialchars($_POST['data_validade'] ?? $cupom['data_validade']) ?>">
                                <small class="form-text text-muted">O cupom será válido até o final desta data</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                           <?= (isset($_POST['ativo']) ? $_POST['ativo'] : $cupom['ativo']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ativo">
                                        Cupom Ativo
                                    </label>
                                </div>
                                <small class="form-text text-muted">Desmarque para desativar temporariamente o cupom</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informações do Cupom:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Criado em: <?= date('d/m/Y H:i', strtotime($cupom['created_at'])) ?></li>
                            <li>Última atualização: <?= date('d/m/Y H:i', strtotime($cupom['updated_at'])) ?></li>
                            <?php if (strtotime($cupom['data_validade']) < time()): ?>
                                <li class="text-danger">⚠️ Este cupom está expirado</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Atualizar Cupom
                        </button>
                        <a href="/cupons" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateDescontoLabel() {
    const tipo = document.getElementById('tipo_desconto').value;
    const label = document.getElementById('desconto-label');
    const input = document.getElementById('desconto');
    
    if (tipo === 'percentual') {
        label.textContent = 'Desconto (%) *';
        input.max = '100';
        input.placeholder = 'Ex: 10';
    } else {
        label.textContent = 'Desconto (R$) *';
        input.removeAttribute('max');
        input.placeholder = 'Ex: 25.00';
    }
}

// Converter código para maiúsculas em tempo real
document.getElementById('codigo').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Inicializar o label correto
document.addEventListener('DOMContentLoaded', function() {
    updateDescontoLabel();
});
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>