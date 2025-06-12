<?php

// Migração para criar cupons de exemplo

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Models\Cupom;

$cupomModel = new Cupom();

// Cupons de exemplo
$cupons = [
    [
        'codigo' => 'DESCONTO10',
        'desconto' => 10,
        'tipo_desconto' => 'percentual',
        'valor_minimo' => 50,
        'data_validade' => date('Y-m-d', strtotime('+30 days')),
        'ativo' => 1
    ],
    [
        'codigo' => 'FRETE15',
        'desconto' => 15,
        'tipo_desconto' => 'fixo',
        'valor_minimo' => 100,
        'data_validade' => date('Y-m-d', strtotime('+60 days')),
        'ativo' => 1
    ],
    [
        'codigo' => 'BEMVINDO',
        'desconto' => 5,
        'tipo_desconto' => 'percentual',
        'valor_minimo' => 0,
        'data_validade' => date('Y-m-d', strtotime('+90 days')),
        'ativo' => 1
    ]
];

echo "Criando cupons de exemplo...\n";

foreach ($cupons as $cupom) {
    try {
        // Verificar se o cupom já existe
        $existente = $cupomModel->findByCodigo($cupom['codigo']);
        if (!$existente) {
            $cupomModel->create($cupom);
            echo "Cupom {$cupom['codigo']} criado com sucesso!\n";
        } else {
            echo "Cupom {$cupom['codigo']} já existe.\n";
        }
    } catch (Exception $e) {
        echo "Erro ao criar cupom {$cupom['codigo']}: " . $e->getMessage() . "\n";
    }
}

echo "Migração concluída!\n";

