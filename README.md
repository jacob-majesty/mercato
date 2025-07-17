
# Mercato

**Mercato** é um sistema simples e funcional de **compra e venda de ingressos ou produtos**, com controle de usuários, clientes e estoque. O projeto foi desenvolvido com foco em simplicidade, organização e boas práticas de desenvolvimento backend em **PHP puro**, utilizando **MySQL** como banco de dados e **Bootstrap** no frontend para uma interface responsiva.

## Funcionalidades

* Cadastro e gerenciamento de produtos
* Controle de estoque
* Venda de ingressos ou produtos
* Cadastro e gestão de clientes
* Controle de usuários do sistema (login e autenticação)
* Relatórios básicos de vendas

## Tecnologias Utilizadas

* **PHP 8+ (POO)**
* **Arquitetura MVC simplificada**
* **MySQL**
* **Composer**
* **Servidor local (Nginx)**
* **Docker e Docker Compose**
* **Bootstrap** (interface responsiva com HTML/CSS)
* **HTML5 e CSS3**

## Estrutura e configuração do Projeto

* Arquitetura MVC (Model, Controller, Service, DTO, View)

* Banco de dados configurável (config/database.php)

* Autoload via Composer

* Docker Compose com PHP + MySQL + Nginx
````
mercato/
├── app/
│   ├── Controller/           # Lógica de controle e rotas (ex: ProdutoController.php)
│   ├── Model/                # Modelos de dados (ex: Produto.php, Cliente.php)
│   ├── Service/              # Regras de negócio (ex: CompraService.php)
│   ├── DTO/                  # Objetos de Transferência de Dados (ex: ProdutoDTO.php)
│   └── View/                 # Templates HTML/Bootstrap organizados por tela
│       ├── cliente/
│       ├── usuario/
│       ├── produto/
│       └── layouts/
├── config/
│   └── database.php          # Configuração da conexão com MySQL/SQLite
├── public/
│   ├── index.php             # Front controller (ponto de entrada da aplicação)
│   └── assets/               # Arquivos estáticos (CSS, JS, imagens)
├── routes/
│   └── web.php               # Definição das rotas da aplicação
├── tests/
│   ├── ProdutoTest.php       # Teste de unidade para Produto
│   ├── ClienteTest.php       # Teste de unidade para Cliente
│   └── CompraTest.php        # Teste de unidade para Compra
├── docker/
│   ├── php/
│   │   └── Dockerfile        # Dockerfile para o container PHP
│   └── nginx/
│       └── default.conf      # Configuração do Nginx apontando para /public
├── .env                      # Variáveis de ambiente (DB, paths, etc.)
├── composer.json             # Gerenciador de dependências PHP
├── composer.lock             # Lock file do composer
├── docker-compose.yml        # Define os serviços Docker (app, web, db)
├── README.md                 # Documentação do projeto
└── .gitignore                # Arquivos e pastas ignoradas no Git

````

## Como Rodar o Projeto

1. **Clone o repositório**

```
git clone https://github.com/jacob-majesty/mercato.git 
```

2. **Acesse a pasta do projeto**

```
cd mercato
```

3. **Suba o servidor local**

Com PHP embutido:

```
php -S localhost:8000 -t public
```

Ou configure em um servidor Apache/Nginx apontando para a pasta `public/`.

4. **Acesse no navegador**

```
http://localhost:8000
```

### Configurar variáveis de ambiente

Crie um arquivo `.env` com as variáveis necessárias (exemplo: banco, path, etc.)

### Instalar dependências

```bash
composer install
```

### Configurar o banco de dados

* Configure `config/database.php` com os dados do seu banco.
* Importe o script `.sql` para criar as tabelas (se for MySQL).

### Executar com Docker (opcional)

```bash
docker-compose up -d
```

Acesse em: [http://localhost](http://localhost)

---

## ▶️ Executando o sistema (sem Docker)

1. Execute o servidor embutido do PHP:

```bash
php -S localhost:8000 -t public
```

2. Acesse: [http://localhost:8000](http://localhost:8000)

---

## 🧭 Diagrama de Funcionamento

```text
Visitante
   ↓
[Home / Produtos Públicos]
   ↓
[Login ou Cadastro] ←—— Recuperar senha
   ↓
[Cliente ou Admin?]
   ↓
───────────────────────────────────────────────
|                Se Cliente                  |
|--------------------------------------------|
| → Carrinho (sessão ou persistido)          |
| → Checkout → Pagamento → Confirmação       |
| → Histórico e repetição de compras         |
| → Comprovante em PDF                       |
───────────────────────────────────────────────
|              Se Admin/Vendedor             |
|--------------------------------------------|
| → Dashboard com resumo                     |
| → Gerenciar Produtos (CRUD)                |
| → Gerenciar Clientes                       |
| → Visualizar Logs                          |
───────────────────────────────────────────────
```

---

## 🔍 Checklist de Funcionalidades Implementadas

### Estrutura e Configuração

* [x] Arquitetura MVC (Model, Controller, Service, DTO, View)
* [x] Banco de dados configurável (`config/database.php`)
* [x] Autoload via Composer
* [x] Docker Compose com PHP + MySQL + Nginx

### Funcionalidades Públicas (Antes do Login)

* [x] Lista de produtos na home
* [x] Filtro por nome, categoria e preço
* [x] Detalhes do produto (sem compra)
* [x] Carrinho com `$_SESSION['carrinho']`
* [x] Cadastro e login de clientes
* [x] Recuperação de senha por e-mail

### Autenticação e Sessão

* [x] Login diferenciando cliente e admin
* [x] Dados salvos na sessão (`id`, `email`, `tipo`)
* [x] Sessões verificadas em rotas protegidas
* [x] Feedback visual pós-login e exibição do nome

### Funcionalidades do Cliente (Pós-login)

* [x] Carrinho persistido no banco
* [x] Controle de quantidade de itens e soma em tempo real
* [x] Checkout com PIX, débito, crédito
* [x] Reserva de estoque do último item
* [x] Cancelamento de reserva após 2 minutos
* [x] Histórico de compras e repetição
* [x] Geração de comprovante em PDF

### Funcionalidades do Usuário Admin/Vendedor

* [x] Dashboard com resumo
* [x] Aviso de estoque baixo
* [x] CRUD de produtos próprios (HTML `POST`)
* [x] CRUD de clientes vinculados às compras
* [x] Logs de compra e ações
* [x] Restrição de visualização (somente próprios dados)

### Cupons e Descontos

* [x] Estrutura de cupons com usos restantes
* [x] Aplicação de cupons no checkout
* [x] Descontos em tempo real

  * "BEMVINDO15" — Primeira compra (15%)
  * R\$50 OFF (para produtos acima de R\$500)
  * Frete grátis acima de R\$200

### Segurança

* [x] Sanitização e validação dos dados
* [x] Verificação de permissões (editar/deletar)
* [x] Sessão protegida em todas as rotas privadas
* [x] Controle de acesso por tipo de usuário

### Testes

* [x] PHPUnit configurado
* [x] Testes para Produto, Cliente e Compra
* [x] Cobertura de regras de negócio (reserva, carrinho, compra)

---

## 💡 Bônus Implementados (Opcional)

* [x] Sistema de logs administrativos
* [x] Timer visual para reserva de estoque
* [x] Envio simulado de e-mails
* [x] Feedback visual com Bootstrap (alertas)

---


---

## 🔗 Repositório

[https://github.com/jacob-majesty/mercato](https://github.com/jacob-majesty/mercato)

---

## Licença

Este projeto é livre para fins educacionais e de demonstração.
