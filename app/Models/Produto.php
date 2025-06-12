<?php

// Produto.php

namespace App\Models;

use App\Core\Model;

class Produto extends Model
{
    protected $table = 'produtos';

    public function getWithEstoque()
    {
        $stmt = $this->db->prepare("
            SELECT p.*, e.id as estoque_id, e.variacao, e.quantidade 
            FROM produtos p 
            LEFT JOIN estoque e ON p.id = e.produto_id 
            ORDER BY p.id, e.variacao
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEstoqueByProduto($produtoId)
    {
        $stmt = $this->db->prepare("SELECT * FROM estoque WHERE produto_id = :produto_id");
        $stmt->bindParam(':produto_id', $produtoId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

