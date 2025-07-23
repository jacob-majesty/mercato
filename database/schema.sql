-- database/schema.sql


-- Tabela de Usuários (Base para login e role)
-- Contém credenciais de login e o papel principal (admin, seller, client).
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'seller', 'client') DEFAULT 'client' NOT NULL, -- Usando ENUM para roles
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Produtos
-- Armazena os detalhes dos produtos disponíveis para venda.
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL, -- Preço com 10 dígitos no total, 2 após a vírgula
    `category` VARCHAR(100),
    `description` TEXT,
    `image_url` VARCHAR(255),
    `stock` INT NOT NULL,
    `seller_id` INT NOT NULL,
    `reserved` INT DEFAULT 0, -- Quantidade de itens reservados (para o controle de estoque)
    `reserved_at` INT, -- Timestamp Unix para a reserva
    CONSTRAINT `fk_products_seller_id` FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Endereços
-- Contém as informações básicas de endereço, conforme o diagrama de classes.
CREATE TABLE IF NOT EXISTS `addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `city` VARCHAR(255) NOT NULL,
    `zip_code` VARCHAR(10) NOT NULL -- CEP geralmente tem um formato fixo
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Clientes (Detalhes específicos do perfil do cliente)
-- Armazena informações adicionais do perfil do cliente (nome, sobrenome),
-- podendo ser vinculado a um `user` para fins de login ou existir como convidado.
CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE, -- Chave estrangeira para a tabela users (pode ser NULL se for cliente convidado)
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) UNIQUE, -- Email de contato do cliente. Pode ser o mesmo do user_id, ou diferente se for convidado.
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_clients_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Pedidos (Ordens)
-- Registra cada compra realizada no sistema.
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL, -- Referencia o perfil do cliente que realizou a compra
    `status` ENUM('PENDING', 'CONFIRMED', 'SHIPPED', 'DELIVERED', 'CANCELED') DEFAULT 'PENDING' NOT NULL, -- Status do pedido
    `total_amount` DECIMAL(10, 2) NOT NULL, -- Valor total do pedido
    `payment_method` VARCHAR(50),
    `address_id` INT NOT NULL, -- Endereço de entrega para este pedido
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_orders_address_id` FOREIGN KEY (`address_id`) REFERENCES `addresses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Pedido
-- Detalha os produtos incluídos em cada pedido.
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `image_url` VARCHAR(255),
    CONSTRAINT `fk_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Logs (para auditoria)
-- Registra eventos importantes no sistema para fins de rastreamento e depuração.
CREATE TABLE IF NOT EXISTS `logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL, -- e.g., "PURCHASE", "ERROR", "ADMIN_ACTION"
    `user_id` INT, -- ID do usuário relacionado à ação, se houver
    `order_id` INT, -- ID do pedido relacionado à ação, se houver
    `action` VARCHAR(255) NOT NULL, -- Descrição da ação
    `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `details` TEXT, -- Detalhes adicionais, pode ser uma string JSON
    CONSTRAINT `fk_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_logs_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Carrinho
-- Armazena os itens que um cliente (logado ou não) deseja comprar antes de finalizar o pedido.
CREATE TABLE IF NOT EXISTS `carts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNIQUE, -- Referencia o perfil do cliente na tabela clients (se logado)
    `session_id` VARCHAR(255) UNIQUE, -- Para carrinhos de usuários não logados, associado a uma sessão/cookie
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Atualiza automaticamente no UPDATE
    `total` DECIMAL(10, 2) DEFAULT 0.0,
    `coupon_code` VARCHAR(50),
    CONSTRAINT `fk_carts_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Carrinho
-- Detalha os produtos adicionados a cada carrinho.
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cart_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `image_url` VARCHAR(255),
    CONSTRAINT `fk_cart_items_cart_id` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Cupons
-- Gerencia os códigos de desconto/cupom disponíveis no sistema.
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) UNIQUE NOT NULL, -- Código único do cupom
    `discount` DECIMAL(5, 2) NOT NULL, -- Valor do desconto (ex: 10.00 para 10%, ou 50.00 para 50 reais)
    `type` ENUM('percentage', 'fixed') NOT NULL, -- Tipo de desconto
    `expiration_date` DATETIME, -- Data de expiração do cupom
    `min_cart_value` DECIMAL(10, 2) DEFAULT 0.0, -- Valor mínimo do carrinho para aplicar o cupom
    `is_active` BOOLEAN DEFAULT TRUE NOT NULL -- Indica se o cupom está ativo (MySQL usa BOOLEAN)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;