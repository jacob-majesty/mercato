````markdown
# 🛍️ Mercato: Sistema de Compra e Venda de Produtos

Mercato é um sistema simples de compra e venda de produtos/ingressos, desenvolvido em PHP puro, seguindo os princípios de Orientação a Objetos (POO) e uma arquitetura modular. Ele gerencia usuários (clientes, vendedores, administradores), produtos, estoque, carrinhos de compra e pedidos, incluindo funcionalidades de logs e aplicação de cupons.

## ✨ Funcionalidades

O sistema Mercato abrange as seguintes funcionalidades principais:

### Gestão de Usuários:

- Registro de novos clientes.
- Autenticação e autorização baseada em papéis (cliente, vendedor, administrador) via sistema de sessão.
- Visualização e gestão de usuários por administradores.

### Gestão de Produtos:

- Listagem de produtos disponíveis para clientes (com paginação).
- Detalhes de produtos individuais.
- Criação, edição e exclusão de produtos por vendedores (apenas seus próprios produtos).
- Visualização e gestão de todos os produtos por administradores.
- Controle de estoque, incluindo decremento na compra e liberação no cancelamento.

### Carrinho de Compras:

- Adição e remoção de múltiplos produtos ao carrinho.
- Atualização de quantidades de itens no carrinho.
- Visualização do carrinho e seu total.

### Pedidos:

- Criação de pedidos a partir do carrinho de compras.
- Aplicação de cupons de desconto durante o checkout.
- Histórico de pedidos para clientes.
- Visualização e atualização de status de pedidos por administradores.
- Cancelamento de pedidos (com reversão de estoque).
- Geração de comprovantes de pedido em PDF.

### Logs do Sistema:

- Registro detalhado de ações e eventos importantes (compras, logins, erros, etc.).
- Visualização de logs por administradores e logs específicos por usuário/vendedor.

### Segurança Básica:

- Validação de entrada de dados via DTOs.
- Uso de Prepared Statements via PDO para prevenir SQL Injection.
- Criptografia de senhas (Bcrypt).
- Verificação de permissões via Middleware.

### Interface do Usuário:

- Design responsivo e elegante utilizando Bootstrap 5.
- CSS e JavaScript personalizados para aprimoramentos visuais e interativos.

## 🏛️ Arquitetura

O projeto segue uma arquitetura modular baseada nos princípios de Orientação a Objetos e Separação de Preocupações:

- `public/`: Contém o ponto de entrada da aplicação (index.php) e todos os assets públicos (CSS, JS, imagens).
- `src/`: Contém o código-fonte principal da aplicação, dividido em:
  - `Config/`: Configurações da aplicação, como a conexão com o banco de dados (Database.php).
  - `Model/`: Classes que representam as entidades de negócio (ex: User, Product, Order, Cart, Coupon, Log, Address). Contêm dados e lógica de negócio específica da entidade.
  - `DTO/`: Data Transfer Objects. Classes simples para encapsular e validar dados de entrada/saída entre camadas, garantindo a integridade dos dados.
  - `Repository/`: Classes e interfaces responsáveis pela persistência dos dados no banco de dados (CRUD). Cada modelo tem seu repositório correspondente.
  - `Service/`: Classes que contêm a lógica de negócio principal da aplicação. Orquestram as operações usando Repositórios e outros Serviços, implementando as regras de negócio.
  - `Controller/`: Classes que manipulam as requisições HTTP, interagem com os Serviços e retornam as respostas (HTML, JSON, redirecionamentos).
  - `Core/`: Componentes fundamentais do framework interno, como:
    - `Router.php`: Gerencia o roteamento de URLs para Controladores e ações.
    - `Request.php`: Abstrai a requisição HTTP.
    - `Response.php`: Abstrai a resposta HTTP.
    - `Authenticator.php`: Gerencia a autenticação e sessão do usuário.
  - `Middleware/`: Classes que executam lógica antes que a requisição chegue ao controlador (ex: verificação de autenticação e autorização/permissões).
  - `routes/`: Arquivos que definem as rotas da aplicação (web.php).
  - `Utility/`: Classes utilitárias diversas, como PdfGenerator.php para geração de PDF.
- `views/`: Arquivos de template HTML/PHP para renderizar a interface do usuário. Inclui um layout base (layout/main.php) e views específicas para cada página/funcionalidade.

## 🚀 Como Configurar e Executar

### Pré-requisitos

- PHP 8.1 ou superior
- Servidor Web (Apache, Nginx, ou PHP embutido para desenvolvimento)
- MySQL/MariaDB
- Composer (recomendado para gerenciamento de dependências, incluindo Dompdf)

### 1. Clonar o Repositório

```bash
git clone <URL_DO_SEU_REPOSITORIO>
cd seu_projeto
```
````

### 2. Instalar Dependências (via Composer)

Se você estiver usando dompdf ou outras bibliotecas, o Composer é essencial.

```bash
composer install
```

### 3. Configurar o Banco de Dados

#### Crie o Banco de Dados:

Acesse seu servidor MySQL (ex: via phpMyAdmin ou linha de comando) e crie um banco de dados chamado mercato.

```sql
CREATE DATABASE IF NOT EXISTS `mercato` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mercato`;
```

#### Execute o Schema SQL:

Importe o conteúdo do arquivo schema.sql (fornecido anteriormente) para o banco de dados mercato. Isso criará todas as tabelas necessárias.

```bash
mysql -u seu_usuario -p mercato < schema.sql
```

(Substitua `seu_usuario` pela sua credencial de usuário MySQL e digite a senha quando solicitado).

#### Configure a Conexão no PHP:

Edite o arquivo `src/Config/Database.php` com suas credenciais de banco de dados:

```php
// src/Config/Database.php
private const DB_HOST = 'localhost';
private const DB_NAME = 'mercato';
private const DB_USER = 'root';    // Seu usuário MySQL
private const DB_PASS = '';        // Sua senha MySQL
private const DB_CHARSET = 'utf8mb4';
```

### 4. Configurar o Servidor Web

#### Opção A: Servidor PHP Embutido (para Desenvolvimento Rápido)

No diretório `public/` do seu projeto, execute:

```bash
php -S localhost:8000
```

Isso iniciará um servidor web na porta 8000. Você pode acessar a aplicação em `http://localhost:8000`.

#### Opção B: Apache / Nginx (Recomendado para Produção)

Configure seu servidor web para apontar o DocumentRoot para a pasta `public/` do seu projeto.

Exemplo para Apache (.htaccess na pasta `public/`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 5. Estrutura de Imagens de Produtos

Certifique-se de que a pasta `public/products/` e suas subpastas (`books/`, `mousepads/`, `luggagetags/`) existem e contêm suas imagens. O arquivo `placeholder.png` também deve estar em `public/products/`.

## 🖥️ Uso da Aplicação

Após a configuração, você pode acessar a aplicação no seu navegador:

- Página Inicial: `http://localhost:8000/` (ou seu domínio)
- Login: `http://localhost:8000/login`
- Registro: `http://localhost:8000/register`
- Produtos: `http://localhost:8000/products`
- Carrinho: `http://localhost:8000/cart` (requer login de cliente)
- Dashboard do Vendedor: `http://localhost:8000/seller/dashboard` (requer login de vendedor)
- Dashboard do Administrador: `http://localhost:8000/admin/dashboard` (requer login de administrador)

### Exemplo de Fluxo:

1. Registrar-se como um novo cliente.
2. Fazer login com a conta recém-criada.
3. Navegar pelos produtos na página inicial ou na lista de produtos.
4. Adicionar produtos ao carrinho.
5. Finalizar a compra no checkout, preenchendo o endereço e método de pagamento.
6. Visualizar o histórico de pedidos e gerar comprovantes em PDF.

## 📈 Próximas Melhorias e Funcionalidades

- **Mecanismo de Expiração de Reserva**: Implementar um cron job ou um processo assíncrono para liberar automaticamente as reservas de produtos após o tempo limite (2 minutos para o último item).
- **Gerenciamento de Usuários (Admin)**: Adicionar funcionalidades de edição e criação de usuários (não apenas clientes) pelo painel administrativo.
- **Gerenciamento de Cupons (Admin/Seller)**: Criar um painel para administradores ou vendedores gerenciarem a criação, edição e ativação/desativação de cupons.
- **Notificações**: Sistema de notificações para usuários (ex: confirmação de pedido, mudança de status).
- **Pesquisa e Filtros**: Implementar funcionalidades de busca e filtragem avançada de produtos.
- **Avaliações de Produtos**: Permitir que clientes avaliem produtos.
- **Sistema de Pagamento Real**: Integrar com gateways de pagamento reais (Stripe, PagSeguro, Mercado Pago, etc.).
- **Imagens de Produtos**: Implementar upload de imagens de produtos em vez de apenas URLs.
- **Testes Automatizados**: Adicionar testes unitários e de integração para garantir a robustez do código.
- **Container de Injeção de Dependência (DIC)**: Utilizar um DIC (como PHP-DI) para gerenciar as dependências de forma mais elegante e escalável, eliminando a instanciação manual nos construtores.
- **Mensagens Flash**: Implementar um sistema de mensagens flash para exibir mensagens de sucesso/erro após redirecionamentos.
- **CSRF Protection**: Adicionar proteção contra Cross-Site Request Forgery (CSRF) para formulários e requisições POST.

Agradecemos por usar o Mercato! Se tiver dúvidas ou sugestões, sinta-se à vontade para entrar em contato.

```

```
