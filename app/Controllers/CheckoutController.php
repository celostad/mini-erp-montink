<?php

// CheckoutController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Estoque;
use App\Models\Pedido;
use App\Models\Cupom;

class CheckoutController extends Controller
{
    private $produtoModel;
    private $estoqueModel;
    private $pedidoModel;
    private $cupomModel;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->produtoModel = new Produto();
        $this->estoqueModel = new Estoque();
        $this->pedidoModel = new Pedido();
        $this->cupomModel = new Cupom();
    }

    public function index()
    {
        $carrinho = $_SESSION['carrinho'] ?? [];
        
        if (empty($carrinho)) {
            $this->redirect('/produtos');
        }

        $totais = $this->calcularTotais();
        $this->view('checkout/index', [
            'carrinho' => $carrinho,
            'totais' => $totais
        ]);
    }

    public function aplicarCupom()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $codigo = $_POST['codigo'] ?? '';
        if (empty($codigo)) {
            $this->json(['erro' => 'Código do cupom é obrigatório'], 400);
        }

        $cupom = $this->cupomModel->findByCodigo($codigo);
        if (!$cupom) {
            $this->json(['erro' => 'Cupom não encontrado'], 404);
        }

        $totais = $this->calcularTotais();
        if (!$this->cupomModel->isValid($cupom, $totais['subtotal'])) {
            $this->json(['erro' => 'Cupom inválido ou expirado'], 400);
        }

        $desconto = $this->cupomModel->calcularDesconto($cupom, $totais['subtotal']);
        $_SESSION['cupom'] = $cupom;
        $_SESSION['desconto'] = $desconto;

        $this->json([
            'sucesso' => 'Cupom aplicado com sucesso',
            'desconto' => $desconto,
            'total' => $totais['subtotal'] + $totais['frete'] - $desconto
        ]);
    }

    public function removerCupom()
    {
        unset($_SESSION['cupom']);
        unset($_SESSION['desconto']);
        $this->json(['sucesso' => 'Cupom removido']);
    }

    public function verificarCep()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        if (strlen($cep) !== 8) {
            $this->json(['erro' => 'CEP inválido'], 400);
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $response = file_get_contents($url);
        
        if ($response === false) {
            $this->json(['erro' => 'Erro ao consultar CEP'], 500);
        }

        $data = json_decode($response, true);
        
        if (isset($data['erro'])) {
            $this->json(['erro' => 'CEP não encontrado'], 404);
        }

        $this->json([
            'sucesso' => 'CEP encontrado',
            'endereco' => $data
        ]);
    }

    public function finalizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
        }

        $carrinho = $_SESSION['carrinho'] ?? [];
        if (empty($carrinho)) {
            $this->redirect('/produtos');
        }

        // Validar dados do formulário
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $endereco = $_POST['endereco'] ?? '';

        if (empty($nome) || empty($email) || empty($cep) || empty($endereco)) {
            $totais = $this->calcularTotais();
            $this->view('checkout/index', [
                'carrinho' => $carrinho,
                'totais' => $totais,
                'erro' => 'Todos os campos são obrigatórios'
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $totais = $this->calcularTotais();
            $this->view('checkout/index', [
                'carrinho' => $carrinho,
                'totais' => $totais,
                'erro' => 'E-mail inválido'
            ]);
            return;
        }

        try {
            // Verificar estoque antes de finalizar
            foreach ($carrinho as $item) {
                $estoque = $this->estoqueModel->findByProdutoAndVariacao($item['produto_id'], $item['variacao']);
                if (!$estoque || $estoque['quantidade'] < $item['quantidade']) {
                    throw new \Exception("Estoque insuficiente para o produto: {$item['nome']}");
                }
            }

            $totais = $this->calcularTotais();
            $desconto = $_SESSION['desconto'] ?? 0;
            $cupomId = isset($_SESSION['cupom']) ? $_SESSION['cupom']['id'] : null;

            // Criar pedido
            $dadosPedido = [
                'subtotal' => $totais['subtotal'],
                'frete' => $totais['frete'],
                'desconto' => $desconto,
                'total' => $totais['subtotal'] + $totais['frete'] - $desconto,
                'cupom_id' => $cupomId,
                'nome_cliente' => $nome,
                'email_cliente' => $email,
                'cep' => $cep,
                'endereco' => $endereco,
                'status' => 'pendente'
            ];

            $itens = [];
            foreach ($carrinho as $item) {
                $itens[] = [
                    'produto_id' => $item['produto_id'],
                    'variacao' => $item['variacao'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco']
                ];
            }

            $pedidoId = $this->pedidoModel->createWithItens($dadosPedido, $itens);

            // Decrementar estoque
            foreach ($carrinho as $item) {
                $this->estoqueModel->decrementarEstoque(
                    $item['produto_id'],
                    $item['variacao'],
                    $item['quantidade']
                );
            }

            // Enviar e-mail (simulado)
            $this->enviarEmailPedido($pedidoId, $dadosPedido, $itens);

            // Limpar carrinho e cupom
            unset($_SESSION['carrinho']);
            unset($_SESSION['cupom']);
            unset($_SESSION['desconto']);

            $this->redirect("/pedido/sucesso?id={$pedidoId}");

        } catch (\Exception $e) {
            $totais = $this->calcularTotais();
            $this->view('checkout/index', [
                'carrinho' => $carrinho,
                'totais' => $totais,
                'erro' => 'Erro ao finalizar pedido: ' . $e->getMessage()
            ]);
        }
    }

    private function calcularTotais()
    {
        $carrinho = $_SESSION['carrinho'] ?? [];
        $subtotal = 0;

        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        $frete = $this->calcularFrete($subtotal);
        
        return [
            'subtotal' => $subtotal,
            'frete' => $frete
        ];
    }

    private function calcularFrete($subtotal)
    {
        if ($subtotal >= 200) {
            return 0; // Frete grátis
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        } else {
            return 20;
        }
    }

    private function enviarEmailPedido($pedidoId, $dadosPedido, $itens)
    {
        // Simulação de envio de e-mail
        // Em um ambiente real, você usaria uma biblioteca como PHPMailer ou SwiftMailer
        
        $assunto = "Pedido #{$pedidoId} - Confirmação";
        $mensagem = "Olá {$dadosPedido['nome_cliente']},\n\n";
        $mensagem .= "Seu pedido #{$pedidoId} foi recebido com sucesso!\n\n";
        $mensagem .= "Detalhes do pedido:\n";
        $mensagem .= "Subtotal: R$ " . number_format($dadosPedido['subtotal'], 2, ',', '.') . "\n";
        $mensagem .= "Frete: R$ " . number_format($dadosPedido['frete'], 2, ',', '.') . "\n";
        if ($dadosPedido['desconto'] > 0) {
            $mensagem .= "Desconto: R$ " . number_format($dadosPedido['desconto'], 2, ',', '.') . "\n";
        }
        $mensagem .= "Total: R$ " . number_format($dadosPedido['total'], 2, ',', '.') . "\n\n";
        $mensagem .= "Endereço de entrega: {$dadosPedido['endereco']}\n\n";
        $mensagem .= "Obrigado pela preferência!";

        // Log do e-mail (para demonstração)
        error_log("E-mail enviado para {$dadosPedido['email_cliente']}: {$assunto}");
        
        return true;
    }
}

