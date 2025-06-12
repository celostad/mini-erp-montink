<?php

// CarrinhoController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Estoque;

class CarrinhoController extends Controller
{
    private $produtoModel;
    private $estoqueModel;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->produtoModel = new Produto();
        $this->estoqueModel = new Estoque();
    }

    public function get()
    {
        $carrinho = $_SESSION['carrinho'] ?? [];
        $itens = [];

        foreach ($carrinho as $chave => $item) {
            $itens[] = $item;
        }

        $this->json(['itens' => $itens]);
    }

    public function remover()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $chave = $_POST['chave'] ?? null;
        if (!$chave) {
            $this->json(['erro' => 'Chave não informada'], 400);
        }

        if (isset($_SESSION['carrinho'][$chave])) {
            unset($_SESSION['carrinho'][$chave]);
        }

        $this->json(['sucesso' => 'Item removido do carrinho']);
    }

    public function alterarQuantidade()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $chave = $_POST['chave'] ?? null;
        $delta = (int)($_POST['delta'] ?? 0);

        if (!$chave || $delta == 0) {
            $this->json(['erro' => 'Dados inválidos'], 400);
        }

        if (!isset($_SESSION['carrinho'][$chave])) {
            $this->json(['erro' => 'Item não encontrado no carrinho'], 404);
        }

        $item = $_SESSION['carrinho'][$chave];
        $novaQuantidade = $item['quantidade'] + $delta;

        if ($novaQuantidade <= 0) {
            unset($_SESSION['carrinho'][$chave]);
            $this->json(['sucesso' => 'Item removido do carrinho']);
        }

        // Verificar estoque disponível
        $estoque = $this->estoqueModel->findByProdutoAndVariacao($item['produto_id'], $item['variacao']);
        if (!$estoque || $novaQuantidade > $estoque['quantidade']) {
            $this->json(['erro' => 'Estoque insuficiente']);
        }

        $_SESSION['carrinho'][$chave]['quantidade'] = $novaQuantidade;
        $this->json(['sucesso' => 'Quantidade atualizada']);
    }

    public function limpar()
    {
        $_SESSION['carrinho'] = [];
        $this->json(['sucesso' => 'Carrinho limpo']);
    }

    public function calcularTotais()
    {
        $carrinho = $_SESSION['carrinho'] ?? [];
        $subtotal = 0;

        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        $frete = $this->calcularFrete($subtotal);
        $total = $subtotal + $frete;

        return [
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $total
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
}

