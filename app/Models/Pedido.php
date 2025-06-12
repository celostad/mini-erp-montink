<?php

// Pedido.php

namespace App\Models;

use App\Core\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    public function createWithItens($dadosPedido, $itens)
    {
        try {
            $this->db->beginTransaction();
            
            // Criar o pedido
            $pedidoId = $this->create($dadosPedido);
            
            // Criar os itens do pedido
            foreach ($itens as $item) {
                $item['pedido_id'] = $pedidoId;
                $stmt = $this->db->prepare("
                    INSERT INTO itens_pedido (pedido_id, produto_id, variacao, quantidade, preco_unitario) 
                    VALUES (:pedido_id, :produto_id, :variacao, :quantidade, :preco_unitario)
                ");
                $stmt->execute($item);
            }
            
            $this->db->commit();
            return $pedidoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getItensPedido($pedidoId)
    {
        $stmt = $this->db->prepare("
            SELECT ip.*, p.nome as produto_nome 
            FROM itens_pedido ip 
            JOIN produtos p ON ip.produto_id = p.id 
            WHERE ip.pedido_id = :pedido_id
        ");
        $stmt->bindParam(':pedido_id', $pedidoId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

