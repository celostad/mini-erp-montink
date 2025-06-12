<?php

// CupomController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cupom;

class CupomController extends Controller
{
    private $cupomModel;

    public function __construct()
    {
        $this->cupomModel = new Cupom();
    }

    public function index()
    {
        $cupons = $this->cupomModel->findAll();
        $this->view('cupons/index', ['cupons' => $cupons]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $desconto = $_POST['desconto'] ?? 0;
            $tipoDesconto = $_POST['tipo_desconto'] ?? 'percentual';
            $valorMinimo = $_POST['valor_minimo'] ?? 0;
            $dataValidade = $_POST['data_validade'] ?? '';

            if (empty($codigo) || $desconto <= 0 || empty($dataValidade)) {
                $this->view('cupons/create', ['erro' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Verificar se o código já existe
            $cupomExistente = $this->cupomModel->findByCodigo($codigo);
            if ($cupomExistente) {
                $this->view('cupons/create', ['erro' => 'Código de cupom já existe']);
                return;
            }

            // Validar desconto percentual
            if ($tipoDesconto === 'percentual' && $desconto > 100) {
                $this->view('cupons/create', ['erro' => 'Desconto percentual não pode ser maior que 100%']);
                return;
            }

            try {
                $this->cupomModel->create([
                    'codigo' => $codigo,
                    'desconto' => $desconto,
                    'tipo_desconto' => $tipoDesconto,
                    'valor_minimo' => $valorMinimo,
                    'data_validade' => $dataValidade,
                    'ativo' => 1
                ]);

                $this->redirect('/cupons');
            } catch (\Exception $e) {
                $this->view('cupons/create', ['erro' => 'Erro ao criar cupom: ' . $e->getMessage()]);
            }
        } else {
            $this->view('cupons/create');
        }
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/cupons');
        }

        $cupom = $this->cupomModel->findById($id);
        if (!$cupom) {
            $this->redirect('/cupons');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $desconto = $_POST['desconto'] ?? 0;
            $tipoDesconto = $_POST['tipo_desconto'] ?? 'percentual';
            $valorMinimo = $_POST['valor_minimo'] ?? 0;
            $dataValidade = $_POST['data_validade'] ?? '';
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            if (empty($codigo) || $desconto <= 0 || empty($dataValidade)) {
                $this->view('cupons/edit', ['cupom' => $cupom, 'erro' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Verificar se o código já existe (exceto o próprio cupom)
            $cupomExistente = $this->cupomModel->findByCodigo($codigo);
            if ($cupomExistente && $cupomExistente['id'] != $id) {
                $this->view('cupons/edit', ['cupom' => $cupom, 'erro' => 'Código de cupom já existe']);
                return;
            }

            // Validar desconto percentual
            if ($tipoDesconto === 'percentual' && $desconto > 100) {
                $this->view('cupons/edit', ['cupom' => $cupom, 'erro' => 'Desconto percentual não pode ser maior que 100%']);
                return;
            }

            try {
                $this->cupomModel->update($id, [
                    'codigo' => $codigo,
                    'desconto' => $desconto,
                    'tipo_desconto' => $tipoDesconto,
                    'valor_minimo' => $valorMinimo,
                    'data_validade' => $dataValidade,
                    'ativo' => $ativo,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $this->redirect('/cupons');
            } catch (\Exception $e) {
                $this->view('cupons/edit', ['cupom' => $cupom, 'erro' => 'Erro ao atualizar cupom: ' . $e->getMessage()]);
            }
        } else {
            $this->view('cupons/edit', ['cupom' => $cupom]);
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/cupons');
        }

        try {
            $this->cupomModel->delete($id);
            $this->redirect('/cupons');
        } catch (\Exception $e) {
            // Em caso de erro, redirecionar com mensagem (seria melhor usar sessão para isso)
            $this->redirect('/cupons');
        }
    }

    public function toggle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['erro' => 'Método não permitido'], 405);
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['erro' => 'ID não informado'], 400);
        }

        $cupom = $this->cupomModel->findById($id);
        if (!$cupom) {
            $this->json(['erro' => 'Cupom não encontrado'], 404);
        }

        $novoStatus = $cupom['ativo'] ? 0 : 1;
        
        try {
            $this->cupomModel->update($id, [
                'ativo' => $novoStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->json([
                'sucesso' => 'Status atualizado',
                'ativo' => $novoStatus
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => 'Erro ao atualizar status'], 500);
        }
    }
}

