-- database/schema.sql

-- Tabela de Usuários (Base para login e role)
-- Contém credenciais de login e o papel principal (admin, seller, client).
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'client', -- Pode ser 'admin', 'seller', 'client'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Produtos
-- Armazena os detalhes dos produtos disponíveis para venda.
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    category TEXT,
    description TEXT,
    image_url TEXT,
    stock INTEGER NOT NULL,
    seller_id INTEGER NOT NULL,
    reserved INTEGER DEFAULT 0, -- Quantidade de itens reservados (para o controle de estoque)
    reserved_at INTEGER, -- Timestamp da reserva (Unix timestamp), para controle de expiração
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Endereços
-- Contém as informações básicas de endereço, conforme o diagrama de classes.
CREATE TABLE IF NOT EXISTS addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    city TEXT NOT NULL,
    zip_code TEXT NOT NULL
);

-- Tabela de Clientes (Detalhes específicos do perfil do cliente)
-- Armazena informações adicionais do perfil do cliente (nome, sobrenome),
-- podendo ser vinculado a um `user` para fins de login ou existir como convidado.
CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE, -- Chave estrangeira para a tabela users (pode ser NULL se for cliente convidado)
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT UNIQUE, -- Email de contato do cliente. Pode ser o mesmo do user_id, ou diferente se for convidado.
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Pedidos (Ordens)
-- Registra cada compra realizada no sistema.
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL, -- Referencia o perfil do cliente que realizou a compra
    status TEXT NOT NULL DEFAULT 'PENDING', -- Status do pedido (PENDING, CONFIRMED, SHIPPED, DELIVERED, CANCELED)
    total_amount REAL NOT NULL, -- Valor total do pedido
    payment_method TEXT,
    address_id INTEGER NOT NULL, -- Endereço de entrega para este pedido
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE CASCADE
);

-- Tabela de Itens do Pedido
-- Detalha os produtos incluídos em cada pedido.
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price REAL NOT NULL,
    image_url TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabela de Logs (para auditoria)
-- Registra eventos importantes no sistema para fins de rastreamento e depuração.
CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL, -- Tipo de log (e.g., "PURCHASE", "ERROR", "ADMIN_ACTION")
    user_id INTEGER, -- ID do usuário relacionado à ação, se houver
    order_id INTEGER, -- ID do pedido relacionado à ação, se houver
    action TEXT NOT NULL, -- Descrição da ação
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    details TEXT, -- Detalhes adicionais, pode ser uma string JSON
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Tabela de Carrinho
-- Armazena os itens que um cliente (logado ou não) deseja comprar antes de finalizar o pedido.
CREATE TABLE IF NOT EXISTS carts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER UNIQUE, -- Referencia o perfil do cliente na tabela clients (se logado)
    session_id TEXT UNIQUE, -- Usado para carrinhos de usuários não logados, associado a uma sessão/cookie
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    total REAL DEFAULT 0.0, -- Valor total atual do carrinho
    coupon_code TEXT, -- Código de cupom aplicado ao carrinho
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Tabela de Itens do Carrinho
-- Detalha os produtos adicionados a cada carrinho.
CREATE TABLE IF NOT EXISTS cart_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cart_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price REAL NOT NULL,
    image_url TEXT,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabela de Cupons
-- Gerencia os códigos de desconto/cupom disponíveis no sistema.
CREATE TABLE IF NOT EXISTS coupons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL, -- Código único do cupom
    discount REAL NOT NULL, -- Valor do desconto
    type TEXT NOT NULL, -- Tipo de desconto (e.g., "percentage", "fixed")
    expiration_date DATETIME, -- Data de expiração do cupom
    min_cart_value REAL DEFAULT 0.0, -- Valor mínimo do carrinho para aplicar o cupom
    is_active BOOLEAN DEFAULT 1 -- Indica se o cupom está ativo
);