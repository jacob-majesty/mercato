-- database/schema.sql

-- Desativa as verificações de chave estrangeira temporariamente para evitar erros durante a criação/limpeza
SET FOREIGN_KEY_CHECKS = 0;

-- Drop Tables (se existirem) para garantir um estado limpo a cada execução
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS addresses; -- Adicionado para garantir que a tabela de endereços seja dropada também


-- Tabela de Usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    pswd VARCHAR(255) NOT NULL, -- 'pswd' para evitar conflito com 'password' em alguns sistemas
    role ENUM('client', 'seller', 'admin') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Adicionado updated_at para consistência
);

-- Tabela de Produtos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    reserved INT NOT NULL DEFAULT 0, -- Nova coluna para estoque reservado
    reserved_at TIMESTAMP NULL, -- Nova coluna para data de reserva
    image_url VARCHAR(255), -- Caminho para a imagem do produto (ex: /products/book1.jpg)
    category VARCHAR(100),
    seller_id INT, -- ID do usuário vendedor que cadastrou o produto
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabela de Carrinhos
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL UNIQUE,
    status ENUM('active', 'completed', 'abandoned') NOT NULL DEFAULT 'active', -- Adicionado status para o carrinho
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Itens do Carrinho
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL, -- CORRIGIDO: Adicionada a coluna unit_price
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (cart_id, product_id) -- Garante que um produto só aparece uma vez no carrinho
);

-- Tabela de Endereços
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    street VARCHAR(255) NOT NULL,
    number VARCHAR(50) NOT NULL,
    complement VARCHAR(255) DEFAULT NULL,
    neighborhood VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    state VARCHAR(255) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Brasil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Pedidos
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    address_id INT NOT NULL, -- Agora é uma chave estrangeira para a tabela addresses
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'COMPLETED', 'CANCELLED', 'REFUNDED') NOT NULL DEFAULT 'PENDING',
    payment_method VARCHAR(50) NOT NULL,
    coupon_code VARCHAR(50) NULL, -- Código do cupom aplicado
    discount_amount DECIMAL(10, 2) DEFAULT 0.00, -- Valor do desconto aplicado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE RESTRICT ON UPDATE CASCADE -- Adiciona a chave estrangeira para addresses
);

-- Tabela de Itens do Pedido
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL, -- Armazena o nome para histórico (se o produto for deletado)
    unit_price DECIMAL(10, 2) NOT NULL, -- Preço no momento da compra
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT -- Não deleta o produto se houver pedidos
);

-- Tabela de Logs do Sistema
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, -- Ex: 'Auth', 'Product', 'Order', 'Error'
    action VARCHAR(255) NOT NULL, -- Descrição da ação
    user_id INT NULL, -- ID do usuário que realizou a ação (pode ser NULL para ações do sistema/erros)
    details JSON NULL, -- Detalhes adicionais em formato JSON (ex: { "orderId": 123, "newStatus": "SHIPPED" })
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabela de Cupons de Desconto
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('PERCENTAGE', 'FIXED') NOT NULL, -- Tipo de desconto: PERCENTAGE ou FIXED
    value DECIMAL(10, 2) NOT NULL, -- Valor do desconto (ex: 0.10 para 10%, ou 20.00 para R$20 fixo)
    min_purchase_amount DECIMAL(10, 2) DEFAULT 0.00, -- Valor mínimo do pedido para aplicar o cupom
    usage_limit INT DEFAULT NULL, -- Limite de usos (NULL para ilimitado)
    used_count INT DEFAULT 0, -- Contador de usos
    expires_at TIMESTAMP NULL, -- Data de expiração (NULL para nunca expirar)
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reativa as verificações de chave estrangeira
SET FOREIGN_KEY_CHECKS = 1;

-- Inserção de Dados Iniciais (Seeds)

-- Usuários de Exemplo (senhas: password123)
-- As senhas devem ser hashadas na aplicação real ou antes de inserir em produção
INSERT INTO users (email, first_name, last_name, pswd, role) VALUES
('admin@mercato.com', 'Admin', 'User', '$2y$10$ULlr1Z.uX3OoU72iRNPDTugmfkV3iolJ1M61yRGx1xURuPDwSRr3.', 'admin'), -- Senha hashada para 'password123'
('seller@mercato.com', 'Seller', 'User', '$2y$10$ULlr1Z.uX3OoU72iRNPDTugmfkV3iolJ1M61yRGx1xURuPDwSRr3.', 'seller'),
('client@mercato.com', 'Client', 'User', '$2y$10$ULlr1Z.uX3OoU72iRNPDTugmfkV3iolJ1M61yRGx1xURuPDwSRr3.', 'client');

-- Produtos de Exemplo (do seu assets.sql, ajustado para sua tabela sem category_id)
INSERT IGNORE INTO products (name, price, category, description, image_url, stock, seller_id) VALUES
-- Books (25 produtos)
('Crash Course in Python', 14.99, 'Books', 'Learn Python at your own pace.', 'products/books/book-luv2code-1000.png', 100, 1),
('Become a Guru in JavaScript', 20.99, 'Books', 'Learn JavaScript at your own pace.', 'products/books/book-luv2code-1001.png', 100, 1),
('Exploring Vue.js', 14.99, 'Books', 'Learn Vue.js at your own pace.', 'products/books/book-luv2code-1002.png', 100, 1),
('Advanced Techniques in Big Data', 13.99, 'Books', 'Learn Big Data at your own pace.', 'products/books/book-luv2code-1003.png', 100, 1),
('Crash Course in Big Data', 18.99, 'Books', 'Learn Big Data at your own pace.', 'products/books/book-luv2code-1004.png', 100, 1),
('JavaScript Cookbook', 23.99, 'Books', 'Learn JavaScript at your own pace.', 'products/books/book-luv2code-1005.png', 100, 1),
('Beginners Guide to SQL', 14.99, 'Books', 'Learn SQL at your own pace.', 'products/books/book-luv2code-1006.png', 100, 1),
('Advanced Techniques in JavaScript', 16.99, 'Books', 'Learn JavaScript at your own pace.', 'products/books/book-luv2code-1007.png', 100, 1),
('Introduction to Spring Boot', 25.99, 'Books', 'Learn Spring Boot at your own pace.', 'products/books/book-luv2code-1008.png', 100, 1),
('Become a Guru in React.js', 23.99, 'Books', 'Learn React.js at your own pace.', 'products/books/book-luv2code-1009.png', 100, 1),
('Beginners Guide to Data Science', 24.99, 'Books', 'Learn Data Science at your own pace.', 'products/books/book-luv2code-1010.png', 100, 1),
('Advanced Techniques in Java', 19.99, 'Books', 'Learn Java at your own pace.', 'products/books/book-luv2code-1011.png', 100, 1),
('Exploring DevOps', 24.99, 'Books', 'Learn DevOps at your own pace.', 'products/books/book-luv2code-1012.png', 100, 1),
('The Expert Guide to SQL', 19.99, 'Books', 'Learn SQL at your own pace.', 'products/books/book-luv2code-1013.png', 100, 1),
('Introduction to SQL', 22.99, 'Books', 'Learn SQL at your own pace.', 'products/books/book-luv2code-1014.png', 100, 1),
('The Expert Guide to JavaScript', 22.99, 'Books', 'Learn JavaScript at your own pace.', 'products/books/book-luv2code-1015.png', 100, 1),
('Exploring React.js', 27.99, 'Books', 'Learn React.js at your own pace.', 'products/books/book-luv2code-1016.png', 100, 1),
('Advanced Techniques in React.js', 13.99, 'Books', 'Learn React.js at your own pace.', 'products/books/book-luv2code-1017.png', 100, 1),
('Introduction to C#', 26.99, 'Books', 'Learn C# at your own pace.', 'products/books/book-luv2code-1018.png', 100, 1),
('Crash Course in JavaScript', 13.99, 'Books', 'Learn JavaScript at your own pace.', 'products/books/book-luv2code-1019.png', 100, 1),
('Introduction to Machine Learning', 19.99, 'Books', 'Learn Machine Learning at your own pace.', 'products/books/book-luv2code-1020.png', 100, 1),
('Become a Guru in Java', 18.99, 'Books', 'Learn Java at your own pace.', 'products/books/book-luv2code-1021.png', 100, 1),
('Introduction to Python', 26.99, 'Books', 'Learn Python at your own pace.', 'products/books/book-luv2code-1022.png', 100, 1),
('Advanced Techniques in C#', 22.99, 'Books', 'Learn C# at your own pace.', 'products/books/book-luv2code-1023.png', 100, 1),
('The Expert Guide to Machine Learning', 16.99, 'Books', 'Learn Machine Learning at your own pace.', 'products/books/book-luv2code-1024.png', 100, 1),

-- Coffee Mugs (25 produtos)
('Coffee Mug - Express', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1000.png', 100, 1),
('Coffee Mug - Cherokee', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1001.png', 100, 1),
('Coffee Mug - Sweeper', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1002.png', 100, 1),
('Coffee Mug - Aspire', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1003.png', 100, 1),
('Coffee Mug - Dorian', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1004.png', 100, 1),
('Coffee Mug - Columbia', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1005.png', 100, 1),
('Coffee Mug - Worthing', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1006.png', 100, 1),
('Coffee Mug - Oak Cliff', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1007.png', 100, 1),
('Coffee Mug - Tachyon', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1008.png', 100, 1),
('Coffee Mug - Pan', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1009.png', 100, 1),
('Coffee Mug - Phase', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1010.png', 100, 1),
('Coffee Mug - Falling', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1011.png', 100, 1),
('Coffee Mug - Wispy', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1012.png', 100, 1),
('Coffee Mug - Arlington', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1013.png', 100, 1),
('Coffee Mug - Gazing', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1014.png', 100, 1),
('Coffee Mug - Azura', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1015.png', 100, 1),
('Coffee Mug - Quantum Leap', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1016.png', 100, 1),
('Coffee Mug - Light Years', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1017.png', 100, 1),
('Coffee Mug - Taylor', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1018.png', 100, 1),
('Coffee Mug - Gracia', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1019.png', 100, 1),
('Coffee Mug - Relax', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1020.png', 100, 1),
('Coffee Mug - Windermere', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1021.png', 100, 1),
('Coffee Mug - Prancer', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1022.png', 100, 1),
('Coffee Mug - Recursion', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1023.png', 100, 1),
('Coffee Mug - Treasure', 18.99, 'Coffee Mugs', 'Elegant coffee mug with fractal design.', 'products/coffeemugs/coffeemug-luv2code-1024.png', 100, 1),

-- Mouse Pads (25 produtos)
('Mouse Pad - Express', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1000.png', 100, 1),
('Mouse Pad - Cherokee', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1001.png', 100, 1),
('Mouse Pad - Sweeper', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1002.png', 100, 1),
('Mouse Pad - Aspire', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1003.png', 100, 1),
('Mouse Pad - Dorian', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1004.png', 100, 1),
('Mouse Pad - Columbia', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1005.png', 100, 1),
('Mouse Pad - Worthing', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1006.png', 100, 1),
('Mouse Pad - Oak Cliff', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1007.png', 100, 1),
('Mouse Pad - Tachyon', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1008.png', 100, 1),
('Mouse Pad - Pan', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1009.png', 100, 1),
('Mouse Pad - Phase', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1010.png', 100, 1),
('Mouse Pad - Falling', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1011.png', 100, 1),
('Mouse Pad - Wispy', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1012.png', 100, 1),
('Mouse Pad - Arlington', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1013.png', 100, 1),
('Mouse Pad - Gazing', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1014.png', 100, 1),
('Mouse Pad - Azura', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1015.png', 100, 1),
('Mouse Pad - Quantum Leap', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1016.png', 100, 1),
('Mouse Pad - Light Years', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1017.png', 100, 1),
('Mouse Pad - Taylor', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1018.png', 100, 1),
('Mouse Pad - Gracia', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1019.png', 100, 1),
('Mouse Pad - Relax', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1020.png', 100, 1),
('Mouse Pad - Windermere', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1021.png', 100, 1),
('Mouse Pad - Prancer', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1022.png', 100, 1),
('Mouse Pad - Recursion', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1023.png', 100, 1),
('Mouse Pad - Treasure', 17.99, 'Mouse Pads', 'Mouse pad with unique fractal design.', 'products/mousepads/mousepad-luv2code-1024.png', 100, 1),

-- Luggage Tags (25 produtos)
('Luggage Tag - Cherish', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1000.png', 100, 1),
('Luggage Tag - Adventure', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1001.png', 100, 1),
('Luggage Tag - Skyline', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1002.png', 100, 1),
('Luggage Tag - River', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1003.png', 100, 1),
('Luggage Tag - Trail Steps', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1004.png', 100, 1),
('Luggage Tag - Blooming', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1005.png', 100, 1),
('Luggage Tag - Park', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1006.png', 100, 1),
('Luggage Tag - Beauty', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1007.png', 100, 1),
('Luggage Tag - Water Fall', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1008.png', 100, 1),
('Luggage Tag - Trail', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1009.png', 100, 1),
('Luggage Tag - Skyscraper', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1010.png', 100, 1),
('Luggage Tag - Leaf', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1011.png', 100, 1),
('Luggage Tag - Jungle', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1012.png', 100, 1),
('Luggage Tag - Shoreline', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1013.png', 100, 1),
('Luggage Tag - Blossom', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1014.png', 100, 1),
('Luggage Tag - Lock', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1015.png', 100, 1),
('Luggage Tag - Cafe', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1016.png', 100, 1),
('Luggage Tag - Darling', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1017.png', 100, 1),
('Luggage Tag - Full Stack', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1018.png', 100, 1),
('Luggage Tag - Courtyard', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1019.png', 100, 1),
('Luggage Tag - Coaster', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1020.png', 100, 1),
('Luggage Tag - Bridge', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1021.png', 100, 1),
('Luggage Tag - Sunset', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1022.png', 100, 1),
('Luggage Tag - Flames', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1023.png', 100, 1),
('Luggage Tag - Countryside', 16.99, 'Luggage Tags', 'Unique luggage tag for identification.', 'products/luggagetags/luggagetag-luv2code-1024.png', 100, 1);

-- Cupons de Exemplo
INSERT INTO coupons (code, type, value, min_purchase_amount, usage_limit, expires_at, is_active) VALUES
('PRIMEIRACOMPRA', 'PERCENTAGE', 0.15, 50.00, 100, '2025-12-31 23:59:59', TRUE), -- 15% de desconto, min R$50, 100 usos
('FRETEGRATIS', 'FIXED', 10.00, 100.00, NULL, NULL, TRUE); -- R$10 de desconto (simula frete grátis), min R$100, ilimitado
