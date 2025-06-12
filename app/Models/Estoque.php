<?php

// Estoque.php

namespace App\Models;

use App\Core\Model;

class Estoque extends Model
{
    protected $table = 'estoque';

    public function findByProdutoAndVariacao($produtoId, $variacao = null)
    {
        if ($variacao) {
            $stmt = $this->db->prepare("SELECT * FROM estoque WHERE produto_id = :produto_id AND variacao = :variacao");
            $stmt->bindParam(':produto_id', $produtoId);
            $stmt->bindParam(':variacao', $variacao);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM estoque WHERE produto_id = :produto_id AND (variacao IS NULL OR variacao = '')");
            $stmt->bindParam(':produto_id', $produtoId);
        }
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateQuantidade($id, $quantidade)
    {
        $stmt = $this->db->prepare("UPDATE estoque SET quantidade = :quantidade, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function decrementarEstoque($produtoId, $variacao, $quantidade)
    {
        $estoque = $this->findByProdutoAndVariacao($produtoId, $variacao);
        if ($estoque && $estoque['quantidade'] >= $quantidade) {
            $novaQuantidade = $estoque['quantidade'] - $quantidade;
            return $this->updateQuantidade($estoque['id'], $novaQuantidade);
        }
        return false;
    }
}

