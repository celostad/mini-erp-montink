<?php

// Database.php

class Database
{
    private $pdo;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = 'sqlite:' . $config['database'];
        
        try {
            $this->pdo = new \PDO($dsn);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch (\PDOException $e) {
            die('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    private function createTables()
    {
        // Tabela produtos
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS produtos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(255) NOT NULL,
                preco DECIMAL(10,2) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabela estoque
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS estoque (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                produto_id INTEGER NOT NULL,
                variacao VARCHAR(255) DEFAULT NULL,
                quantidade INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
            )
        ");

        // Tabela cupons
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS cupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo VARCHAR(50) NOT NULL UNIQUE,
                desconto DECIMAL(10,2) NOT NULL,
                tipo_desconto VARCHAR(20) DEFAULT 'percentual',
                valor_minimo DECIMAL(10,2) DEFAULT 0,
                data_validade DATE NOT NULL,
                ativo BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabela pedidos
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS pedidos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                subtotal DECIMAL(10,2) NOT NULL,
                frete DECIMAL(10,2) NOT NULL,
                desconto DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) NOT NULL,
                cupom_id INTEGER DEFAULT NULL,
                status VARCHAR(50) DEFAULT 'pendente',
                nome_cliente VARCHAR(255) NOT NULL,
                email_cliente VARCHAR(255) NOT NULL,
                cep VARCHAR(10) NOT NULL,
                endereco TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cupom_id) REFERENCES cupons(id)
            )
        ");

        // Tabela itens_pedido
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS itens_pedido (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pedido_id INTEGER NOT NULL,
                produto_id INTEGER NOT NULL,
                variacao VARCHAR(255) DEFAULT NULL,
                quantidade INTEGER NOT NULL,
                preco_unitario DECIMAL(10,2) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
                FOREIGN KEY (produto_id) REFERENCES produtos(id)
            )
        ");
    }
}

