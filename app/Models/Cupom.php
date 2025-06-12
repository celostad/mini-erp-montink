<?php

// Cupom.php

namespace App\Models;

use App\Core\Model;

class Cupom extends Model
{
    protected $table = 'cupons';

    public function findByCodigo($codigo)
    {
        $stmt = $this->db->prepare("SELECT * FROM cupons WHERE codigo = :codigo AND ativo = 1");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function isValid($cupom, $subtotal)
    {
        if (!$cupom) return false;
        
        // Verifica se o cupom ainda está válido
        if (strtotime($cupom['data_validade']) < time()) {
            return false;
        }
        
        // Verifica se o subtotal atende ao valor mínimo
        if ($subtotal < $cupom['valor_minimo']) {
            return false;
        }
        
        return true;
    }

    public function calcularDesconto($cupom, $subtotal)
    {
        if (!$this->isValid($cupom, $subtotal)) {
            return 0;
        }
        
        if ($cupom['tipo_desconto'] === 'percentual') {
            return ($subtotal * $cupom['desconto']) / 100;
        } else {
            return $cupom['desconto'];
        }
    }
}

