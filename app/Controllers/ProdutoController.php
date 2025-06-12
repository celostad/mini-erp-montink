<?php

// ProdutoController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Estoque;

class ProdutoController extends Controller
{
    private $produtoModel;
    private $estoqueModel;

    public function __construct()
    {
        $this->produtoModel = new Produto();
        $this->estoqueModel = new Estoque();
    }

    public function index()
    {
        $produtos = $this->produtoModel->getWithEstoque();
        $this->view('produtos/index', ['produtos' => $produtos]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $preco = $_POST['preco'] ?? 0;
            $variacoes = $_POST['variacoes'] ?? [];
            $quantidades = $_POST['quantidades'] ?? [];

            if (empty($nome) || $preco <= 0) {
                $this->view('produtos/create', ['erro' => 'Nome e preço são obrigatórios']);
                return;
            }

            try {
                // Criar produto
                $produtoId = $this->produtoModel->create([
                    'nome' => $nome,
                    'preco' => $preco
                ]);

                // Criar estoque para cada variação
                if (!empty($variacoes)) {
                    foreach ($variacoes as $index => $variacao) {
                        if (!empty($variacao)) {
                            $this->estoqueModel->create([
                                'produto_id' => $produtoId,
                                'variacao' => $variacao,
                                'quantidade' => $quantidades[$index] ?? 0
                            ]);
                        }
                    }
                } else {
                    // Produto sem variação
                    $this->estoqueModel->create([
                        'produto_id' => $produtoId,
                        'variacao' => null,
                        'quantidade' => $_POST['quantidade_geral'] ?? 0
                    ]);
                }

                $this->redirect('/produtos');
            } catch (\Exception $e) {
                $this->view('produtos/create', ['erro' => 'Erro ao criar produto: ' . $e->getMessage()]);
            }
        } else {
            $this->view('produtos/create');
        }
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/produtos');
        }

        $produto = $this->produtoModel->findById($id);
        if (!$produto) {
            $this->redirect('/produtos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $preco = $_POST['preco'] ?? 0;

            if (empty($nome) || $preco <= 0) {
                $estoque = $this->produtoModel->getEstoqueByProduto($id);
                $this->view('produtos/edit', ['produto' => $produto, 'estoque' => $estoque, 'erro' => 'Nome e preço são obrigatórios']);
                return;
            }

            try {
                // Atualizar produto
                $this->produtoModel->update($id, [
                    'nome' => $nome,
                    'preco' => $preco,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Atualizar estoque existente
                $estoqueIds = $_POST['estoque_ids'] ?? [];
                $quantidades = $_POST['quantidades'] ?? [];

                foreach ($estoqueIds as $index => $estoqueId) {
                    if (isset($quantidades[$index])) {
                        $this->estoqueModel->update($estoqueId, [
                            'quantidade' => $quantidades[$index],
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                // Adicionar novas variações se fornecidas
                $novasVariacoes = $_POST['novas_variacoes'] ?? [];
                $novasQuantidades = $_POST['novas_quantidades'] ?? [];

                foreach ($novasVariacoes as $index => $variacao) {
                    if (!empty($variacao)) {
                        $this->estoqueModel->create([
                            'produto_id' => $id,
                            'variacao' => $variacao,
                            'quantidade' => $novasQuantidades[$index] ?? 0
                        ]);
                    }
                }

                $this->redirect('/produtos');
            } catch (\Exception $e) {
                $estoque = $this->produtoModel->getEstoqueByProduto($id);
                $this->view('produtos/edit', ['produto' => $produto, 'estoque' => $estoque, 'erro' => 'Erro ao atualizar produto: ' . $e->getMessage()]);
            }
        } else {
            $estoque = $this->produtoModel->getEstoqueByProduto($id);
            $this->view('produtos/edit', ['produto' => $produto, 'estoque' => $estoque]);
        }
    }

    public function addToCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $produtoId = $_POST['produto_id'] ?? null;
        $variacao = $_POST['variacao'] ?? null;
        $quantidade = (int)($_POST['quantidade'] ?? 1);

        if (!$produtoId || $quantidade <= 0) {
            $this->json(['erro' => 'Dados inválidos'], 400);
        }

        // Verificar estoque disponível
        $estoque = $this->estoqueModel->findByProdutoAndVariacao($produtoId, $variacao);
        if (!$estoque || $estoque['quantidade'] < $quantidade) {
            $this->json(['erro' => 'Estoque insuficiente'], 400);
        }

        // Inicializar sessão se não existir
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Inicializar carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        // Chave única para o item no carrinho
        $chaveItem = $produtoId . '_' . ($variacao ?? 'sem_variacao');

        // Adicionar ou atualizar item no carrinho
        if (isset($_SESSION['carrinho'][$chaveItem])) {
            $_SESSION['carrinho'][$chaveItem]['quantidade'] += $quantidade;
        } else {
            $produto = $this->produtoModel->findById($produtoId);
            $_SESSION['carrinho'][$chaveItem] = [
                'produto_id' => $produtoId,
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'variacao' => $variacao,
                'quantidade' => $quantidade
            ];
        }

        // Verificar se a quantidade total não excede o estoque
        if ($_SESSION['carrinho'][$chaveItem]['quantidade'] > $estoque['quantidade']) {
            $_SESSION['carrinho'][$chaveItem]['quantidade'] = $estoque['quantidade'];
            $this->json(['aviso' => 'Quantidade ajustada para o estoque disponível', 'carrinho' => $_SESSION['carrinho']]);
        }

        $this->json(['sucesso' => 'Produto adicionado ao carrinho', 'carrinho' => $_SESSION['carrinho']]);
    }
}

