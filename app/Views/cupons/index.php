<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-ticket-alt"></i> Cupons de Desconto</h1>
    <a href="/cupons/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Cupom
    </a>
</div>

<?php if (empty($cupons)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum cupom cadastrado ainda.
        <a href="/cupons/create" class="alert-link">Clique aqui para cadastrar o primeiro cupom</a>.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Desconto</th>
                            <th>Valor Mínimo</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cupons as $cupom): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cupom['codigo']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($cupom['tipo_desconto'] === 'percentual'): ?>
                                        <?= number_format($cupom['desconto'], 0) ?>%
                                    <?php else: ?>
                                        R$ <?= number_format($cupom['desconto'], 2, ',', '.') ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    R$ <?= number_format($cupom['valor_minimo'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($cupom['data_validade'])) ?>
                                    <?php if (strtotime($cupom['data_validade']) < time()): ?>
                                        <br><small class="text-danger">Expirado</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="status_<?= $cupom['id'] ?>"
                                               <?= $cupom['ativo'] ? 'checked' : '' ?>
                                               onchange="toggleStatus(<?= $cupom['id'] ?>)">
                                        <label class="form-check-label" for="status_<?= $cupom['id'] ?>">
                                            <?= $cupom['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($cupom['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/cupons/edit?id=<?= $cupom['id'] ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="confirmarExclusao(<?= $cupom['id'] ?>, '<?= htmlspecialchars($cupom['codigo']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleStatus(id) {
    fetch('/cupons/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            alert(data.erro);
            // Reverter o switch
            const checkbox = document.getElementById('status_' + id);
            checkbox.checked = !checkbox.checked;
        } else {
            // Atualizar o label
            const label = document.querySelector('label[for="status_' + id + '"]');
            label.textContent = data.ativo ? 'Ativo' : 'Inativo';
        }
    })
    .catch(error => {
        console.error('Erro ao alterar status:', error);
        alert('Erro ao alterar status do cupom');
        // Reverter o switch
        const checkbox = document.getElementById('status_' + id);
        checkbox.checked = !checkbox.checked;
    });
}

function confirmarExclusao(id, codigo) {
    if (confirm(`Tem certeza que deseja excluir o cupom "${codigo}"?`)) {
        window.location.href = '/cupons/delete?id=' + id;
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

