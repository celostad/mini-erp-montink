<?php

// index.php - Arquivo principal de roteamento

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../app/Core/Database.php";

use App\Core\Router;

// Inicializar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$router = new Router();

// Rotas de produtos
$router->get('', 'ProdutoController@index');
$router->get('produtos', 'ProdutoController@index');
$router->get('produtos/create', 'ProdutoController@create');
$router->post('produtos/create', 'ProdutoController@create');
$router->get('produtos/edit', 'ProdutoController@edit');
$router->post('produtos/edit', 'ProdutoController@edit');
$router->post('produtos/add-to-cart', 'ProdutoController@addToCart');

// Rotas de carrinho
$router->get('carrinho/get', 'CarrinhoController@get');
$router->post('carrinho/remover', 'CarrinhoController@remover');
$router->post('carrinho/alterar-quantidade', 'CarrinhoController@alterarQuantidade');
$router->post('carrinho/limpar', 'CarrinhoController@limpar');

// Rotas de checkout
$router->get('checkout', 'CheckoutController@index');
$router->post('checkout/aplicar-cupom', 'CheckoutController@aplicarCupom');
$router->post('checkout/remover-cupom', 'CheckoutController@removerCupom');
$router->post('checkout/verificar-cep', 'CheckoutController@verificarCep');
$router->post('checkout/finalizar', 'CheckoutController@finalizar');

// Rotas de cupons
$router->get('cupons', 'CupomController@index');
$router->get('cupons/create', 'CupomController@create');
$router->post('cupons/create', 'CupomController@create');
$router->get('cupons/edit', 'CupomController@edit');
$router->post('cupons/edit', 'CupomController@edit');
$router->get('cupons/delete', 'CupomController@delete');
$router->post('cupons/toggle', 'CupomController@toggle');

// Rotas de pedidos
$router->get('pedidos', 'PedidoController@index');
$router->get('pedidos/view', 'PedidoController@viewPedido');
$router->get('pedido/sucesso', 'PedidoController@sucesso');

// Webhook
$router->post('webhook/pedidos', 'PedidoController@webhook');

$router->dispatch();

