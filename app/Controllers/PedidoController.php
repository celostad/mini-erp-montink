<?php

// PedidoController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pedido;

class PedidoController extends Controller
{
    private $pedidoModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
    }

    public function index()
    {
        $pedidos = $this->pedidoModel->findAll();
        $this->view('pedidos/index', ['pedidos' => $pedidos]);
    }

    public function viewPedido()
    {
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/pedidos');
        }

        $pedido = $this->pedidoModel->findById($id);
        
        if (!$pedido) {
            $this->redirect('/pedidos');
        }

        $itens = $this->pedidoModel->getItensPedido($id);
        // print_r($itens); die;
        $this->view('pedidos/view', ['pedido' => $pedido, 'itens' => $itens]);
    }

    public function sucesso()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/produtos');
        }

        $pedido = $this->pedidoModel->findById($id);
        if (!$pedido) {
            $this->redirect('/produtos');
        }

        $itens = $this->pedidoModel->getItensPedido($id);
        $this->view('pedidos/sucesso', ['pedido' => $pedido, 'itens' => $itens]);
    }

    public function webhook()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
            exit;
        }

        // Ler o corpo da requisição
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos']);
            exit;
        }

        $pedidoId = $data['id'];
        $status = $data['status'];

        // Verificar se o pedido existe
        $pedido = $this->pedidoModel->findById($pedidoId);
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pedido não encontrado']);
            exit;
        }

        try {
            if ($status === 'cancelado') {
                // Remover o pedido se o status for cancelado
                $this->pedidoModel->delete($pedidoId);
                
                // Log da ação
                error_log("Pedido #{$pedidoId} removido via webhook - Status: cancelado");
                
                echo json_encode(['sucesso' => 'Pedido removido']);
            } else {
                // Atualizar o status do pedido
                $this->pedidoModel->updateStatus($pedidoId, $status);
                
                // Log da ação
                error_log("Pedido #{$pedidoId} atualizado via webhook - Novo status: {$status}");
                
                echo json_encode(['sucesso' => 'Status atualizado']);
            }
        } catch (\Exception $e) {
            error_log("Erro no webhook para pedido #{$pedidoId}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
    }
}

