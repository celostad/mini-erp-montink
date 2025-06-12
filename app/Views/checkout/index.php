<?php ob_start(); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Finalizar Pedido</h4>
            </div>
            <div class="card-body">
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/checkout/finalizar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required 
                                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cep" class="form-label">CEP *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" name="cep" required 
                                           maxlength="9" placeholder="00000-000"
                                           value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>">
                                    <button type="button" class="btn btn-outline-secondary" onclick="buscarCep()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço Completo *</label>
                                <textarea class="form-control" id="endereco" name="endereco" rows="3" required 
                                          placeholder="Rua, número, complemento, bairro, cidade, estado"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cupom" class="form-label">Cupom de Desconto</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cupom" placeholder="Digite o código do cupom">
                            <button type="button" class="btn btn-outline-primary" onclick="aplicarCupom()">
                                Aplicar
                            </button>
                        </div>
                        <div id="cupom-feedback" class="mt-2"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Finalizar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumo do Pedido</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($carrinho)): ?>
                    <?php foreach ($carrinho as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <strong><?= htmlspecialchars($item['nome']) ?></strong>
                                <?php if ($item['variacao']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($item['variacao']) ?></small>
                                <?php endif; ?>
                                <br><small>Qtd: <?= $item['quantidade'] ?></small>
                            </div>
                            <span>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="subtotal">R$ <?= number_format($totais['subtotal'], 2, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Frete:</span>
                            <span id="frete">R$ <?= number_format($totais['frete'], 2, ',', '.') ?></span>
                        </div>
                        <div id="desconto-linha" class="d-flex justify-content-between text-success" style="display: none !important;">
                            <span>Desconto:</span>
                            <span id="desconto">- R$ 0,00</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <strong>Total:</strong>
                            <strong id="total">R$ <?= number_format($totais['subtotal'] + $totais['frete'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="fas fa-truck"></i> Informações de Frete</h6>
                <small class="text-muted">
                    • Pedidos acima de R$ 200,00: <strong>Frete Grátis</strong><br>
                    • Pedidos entre R$ 52,00 e R$ 166,59: <strong>R$ 15,00</strong><br>
                    • Outros valores: <strong>R$ 20,00</strong>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function buscarCep() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    const enderecoField = document.getElementById('endereco');
    
    if (cep.length !== 8) {
        alert('CEP deve ter 8 dígitos');
        return;
    }

    fetch('/checkout/verificar-cep', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cep=' + cep
    })
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            alert(data.erro);
        } else {
            const endereco = `${data.endereco.logradouro}, ${data.endereco.bairro}, ${data.endereco.localidade} - ${data.endereco.uf}`;
            enderecoField.value = endereco;
        }
    })
    .catch(error => {
        console.error('Erro ao buscar CEP:', error);
        alert('Erro ao buscar CEP');
    });
}

function aplicarCupom() {
    const codigo = document.getElementById('cupom').value.trim();
    const feedback = document.getElementById('cupom-feedback');
    
    if (!codigo) {
        feedback.innerHTML = '<div class="alert alert-warning alert-sm">Digite um código de cupom</div>';
        return;
    }

    fetch('/checkout/aplicar-cupom', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'codigo=' + encodeURIComponent(codigo)
    })
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            feedback.innerHTML = `<div class="alert alert-danger alert-sm">${data.erro}</div>`;
        } else {
            feedback.innerHTML = `<div class="alert alert-success alert-sm">${data.sucesso}</div>`;
            
            // Atualizar valores na tela
            document.getElementById('desconto').textContent = '- R$ ' + data.desconto.toFixed(2).replace('.', ',');
            document.getElementById('desconto-linha').style.display = 'flex';
            document.getElementById('total').textContent = 'R$ ' + data.total.toFixed(2).replace('.', ',');
            
            // Adicionar botão para remover cupom
            feedback.innerHTML += `
                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removerCupom()">
                    Remover Cupom
                </button>
            `;
        }
    })
    .catch(error => {
        console.error('Erro ao aplicar cupom:', error);
        feedback.innerHTML = '<div class="alert alert-danger alert-sm">Erro ao aplicar cupom</div>';
    });
}

function removerCupom() {
    fetch('/checkout/remover-cupom', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        location.reload(); // Recarregar para atualizar os valores
    })
    .catch(error => {
        console.error('Erro ao remover cupom:', error);
    });
}

// Máscara para CEP
document.getElementById('cep').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    e.target.value = value;
});
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>