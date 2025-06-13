# Mini ERP - Sistema de Controle de Pedidos

Um sistema completo de ERP desenvolvido em PHP 8.2 puro com SQLite e Bootstrap, seguindo o padrão MVC.

## Funcionalidades

### 📦 Gestão de Produtos
- Cadastro de produtos com nome, preço e variações
- Controle de estoque por variação
- Edição e atualização de produtos e estoque
- Interface intuitiva para gerenciamento

### 🛒 Sistema de Carrinho
- Carrinho em sessão com controle de estoque
- Cálculo automático de frete:
  - Pedidos acima de R$ 200,00: **Frete Grátis**
  - Pedidos entre R$ 52,00 e R$ 166,59: **R$ 15,00**
  - Outros valores: **R$ 20,00**
- Interface lateral responsiva

### 🎫 Sistema de Cupons
- Criação e gerenciamento de cupons de desconto
- Suporte a desconto percentual e valor fixo
- Validação por valor mínimo do pedido
- Controle de validade e ativação/desativação

### 📋 Gestão de Pedidos
- Finalização de pedidos com dados do cliente
- Integração com API ViaCEP para validação de endereços
- Envio automático de e-mail de confirmação
- Controle de status dos pedidos

### 🔗 Webhook
- Endpoint para atualização de status via webhook
- Remoção automática de pedidos cancelados
- Logs de todas as operações

## Tecnologias Utilizadas

- **Backend**: PHP 8.2 puro
- **Banco de Dados**: SQLite
- **Frontend**: Bootstrap 5.3, Font Awesome
- **Arquitetura**: MVC (Model-View-Controller)
- **APIs**: ViaCEP para validação de CEP

# Telas do sistema:
![tela_inicial](https://github.com/user-attachments/assets/e7e9624f-e711-416d-a8eb-bb33be1bde0b)
![tipos_de_variacaoes_produtos](https://github.com/user-attachments/assets/64da996d-d4cb-4a1a-a699-e6395c559090)
![carrinho_compras](https://github.com/user-attachments/assets/9b35ca91-719c-4ea5-bec1-14bb18c017e0)
![finalizando_pedido](https://github.com/user-attachments/assets/d2eee664-3580-4694-85ce-adee4897a8d5)
![finalizacao_pedido](https://github.com/user-attachments/assets/cd3a07b7-a5d3-49ca-9eac-a194e39544ac)
![tela_cupons](https://github.com/user-attachments/assets/5f8b57af-7bc5-43d0-a85b-4b7dba6b6e1f)
![tela_pedidos](https://github.com/user-attachments/assets/654bd59d-5486-41cd-a135-eb62c54589cf)



## Estrutura do Projeto

```
mini_erp/
├── app/
│   ├── Controllers/     # Controladores MVC
│   ├── Models/         # Modelos de dados
│   ├── Views/          # Templates das páginas
│   └── Core/           # Classes base (Router, Database, etc.)
├── config/             # Configurações
├── database/           # Banco SQLite e migrações
├── public/             # Ponto de entrada público
└── vendor/             # Dependências do Composer
```

## Instalação e Configuração

### 1. Requisitos
- PHP 8.1+ (recomendado 8.2)
- Extensões: PDO, SQLite, cURL
- Composer

### 2. Instalação
```bash
# Clone ou extraia o projeto
cd mini-erp-montink

# Instale as dependências
composer install

# Execute a migração de cupons de exemplo (opcional)
php database/migrate_cupons.php
```

### 3. Configuração do Servidor Web

#### Servidor PHP Built-in (Desenvolvimento)
```bash
cd public
php -S localhost:8001
```

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Configuração do Virtual Host (ou arquivo de site) no Apache:
Este arquivo define como o Apache deve servir sua aplicação. Geralmente, esses arquivos estão localizados em:

   -  /etc/apache2/sites-available/ (no Ubuntu/Debian)
   -  /etc/httpd/conf.d/ (no CentOS/RHEL )

Você criaria um novo arquivo, por exemplo, mini_erp.conf (ou mini_erp.vhost), e adicionaria a configuração básica de um Virtual Host. O ponto crucial é definir o DocumentRoot para a pasta public do seu projeto. Exemplo:

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /caminho/completo/para/seu/projeto/mini_erp/public
    ServerName seu_dominio.com
    ServerAlias www.seu_dominio.com

    <Directory /caminho/completo/para/seu/projeto/mini_erp/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## Uso do Sistema

### 1. Gestão de Produtos
- Acesse `/produtos` para ver todos os produtos
- Clique em "Novo Produto" para cadastrar
- Use "Editar" para modificar produtos existentes
- Configure variações e estoque conforme necessário

### 2. Processo de Compra
- Na listagem de produtos, clique em "Comprar"
- Use o carrinho lateral para revisar itens
- Clique em "Finalizar Pedido" no carrinho
- Preencha os dados do cliente e endereço
- Aplique cupons de desconto se disponível
- Confirme o pedido

### 3. Gestão de Cupons
- Acesse `/cupons` para gerenciar cupons
- Crie novos cupons com regras específicas
- Ative/desative cupons conforme necessário
- Configure validade e valores mínimos

### 4. Webhook de Pedidos
Endpoint: `POST /webhook/pedidos`

Payload esperado:
```json
{
    "id": 123,
    "status": "processando"
}
```

Status especial:
- `"cancelado"`: Remove o pedido do sistema
- Outros status: Atualiza o status do pedido

## Cupons de Exemplo

O sistema inclui cupons pré-configurados:
- **DESCONTO10**: 10% de desconto (mín. R$ 50,00)
- **FRETE15**: R$ 15,00 de desconto (mín. R$ 100,00)
- **BEMVINDO**: 5% de desconto (sem valor mínimo)

## Segurança e Boas Práticas

- Validação de dados em todas as entradas
- Proteção contra SQL Injection via PDO
- Sanitização de saídas HTML
- Controle de sessão seguro
- Logs de operações críticas
- Tratamento de erros robusto

## API Externa

### ViaCEP
O sistema integra com a API do ViaCEP para validação automática de endereços:
- Endpoint: `https://viacep.com.br/ws/{cep}/json/`
- Preenchimento automático de endereço
- Validação de CEP em tempo real

## Estrutura do Banco de Dados

### Tabelas Principais
- **produtos**: Informações básicas dos produtos
- **estoque**: Controle de estoque por variação
- **cupons**: Cupons de desconto
- **pedidos**: Pedidos realizados
- **itens_pedido**: Itens de cada pedido

### Relacionamentos
- Produtos 1:N Estoque
- Pedidos 1:N Itens_Pedido
- Cupons 1:N Pedidos (opcional)

## Logs e Monitoramento

O sistema registra logs para:
- Operações de webhook
- Envio de e-mails
- Erros críticos
- Atualizações de status

Logs são salvos via `error_log()` do PHP.

## Suporte e Manutenção

### Backup do Banco
```bash
# Copiar o arquivo SQLite
cp database/database.sqlite database/backup_$(date +%Y%m%d).sqlite
```

### Limpeza de Sessões
As sessões são gerenciadas automaticamente pelo PHP. Para limpeza manual:
```bash
# Limpar sessões antigas (se usando arquivos)
find /tmp -name "sess_*" -mtime +1 -delete
```

## Licença

Este projeto foi desenvolvido como demonstração de um sistema ERP completo em PHP puro, seguindo boas práticas de desenvolvimento e padrões MVC.  
Se esse sitema foi útil pra você, aqui está minha chave pix do café ;)  
E-mail: celostad@gmail.com

