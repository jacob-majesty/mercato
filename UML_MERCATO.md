````mermaid

classDiagram
    class User {
        -id: int (auto increment)
        -email: string
        -firstName: string
        -lastName: string
        -role %% admin seller client
        -pswd: string
        -createdAt: Date

        +createUser()
        +editProfile()
        +deleteUser()
        +login()
        +logout()
    }

    class Admin {
        +getAllUsers(): User[]
        +getAllProducts(): Product[]
        +getAllOrders(): Order[]
        +getAllLogs(): Log[]
        +getLogOfUser(userId: int): Log[]
        +manageUser(userId: int, action: string)
        +manageProduct(productId: int, action: string)
        +manageOrder(orderId: int, action: string)
    }

    class Seller {
        +getMyProducts(): Product[]
        +updateProduct(productId: int, data: any)
        +addProduct(productData: any)
        +deleteProduct(productId: int)
        +getProductStock(productId: number): number
        +applyDiscount(productId: number, discount: number)
        +getMySales(): Order[]
        +getSellerLogs(): Log[]
    }

    class Client {
        +addToCart(productId: number, quantity: number)
        +removeFromCart(productId: number)
        +viewCart(): Cart
        +checkout(paymentMethod: string, address: Address): Order
        +viewOrderHistory(): Order[]
        +generateReceipt(orderId: int): PDF
        +isFirstPurchase(): bool
    }

    class Product {
        -id: int
        -name: string
        -price: float
        -category: string
        -description: string
        -imageUrl: string
        -stock: int
        -sellerId: int %% Chave estrangeira para o Seller
        -reserved: int
        -reservedAt: ?DateTime

        +checkStock(quantity: int): bool
        +reserveStock(quantity: int): void
        +releaseStock(quantity: int): void
        +decrementStock(quantity: int): void
    }

    class Order {
        -id: int
        -clientId: int
        -status: string %% PENDING, CONFIRMED, SHIPPED, DELIVERED, CANCELED
        -orderDate: DateTime
        -totalAmount: float
        -paymentMethod: string
        -address: Address
        -items: OrderItem[]

        +addItem(OrderItem $item): void
        +removeItem(OrderItem $item): void
        +processOrder(): void
        +cancelOrder(): void
        +calculateTotal(): float
    }

    class OrderItem {
        -id: int
        -orderId: int
        -productId: int
        -productName: string
        -quantity: int
        -unitPrice: float
    }

    class Log {
        -id: int
        -type: string %% e.g., "PURCHASE", "ERROR", "ADMIN_ACTION"
        -userId: int
        -action: string
        -timestamp: DateTime
        -details: JSON  %% Detalhes adicionais em formato JSON
    }

    class Cart {
        -id: string %% ID do carrinho
        -clientId: int (session/cookie ou id do cliente)
        -coupon: Coupon?
        -items: CartItem[]

        +addItem(product: Product, quantity: int): void
        +removeItem(productId: int): void
        +clear(): void
        +getTotal(): float
        +checkAllItemsStock(): bool
        +convertToOrder(): Order
        +applyCoupon(couponCode: string): void
    }

    class CartItem {
        -productId: int
        -productName: string
        -quantity: int
        -unitPrice: float
        -imageUrl: string
    }

    class Coupon {
        -code: string
        -discount: float
        -type: string %% e.g., "first_purchase", "big_spender", "percentage", "fixed"
        -expirationDate: DateTime?
        -minCartValue: float?
        -isActive: bool

        +isExpired: bool
    }

    class Address {
        -id: int
        -street: string
        -number: int
        -complement: string
        -state: string
        -country: string
        -city: string
        -zipCode: string
    }

    %% Relacionamentos
    User <|-- Admin
    User <|-- Seller
    User <|-- Client

    Seller "1" -- "0..*" Product : "publica"
    Client "1" -- "0..1" Cart : "possui"

    Cart "1" *-- "0..*" CartItem : "contém"
    Product "1" -- "0..*" CartItem : "é composto por"

    Client "1" -- "0..*" Order : "realiza"
    Order "1" *-- "0..*" OrderItem : "contém"
    Product "1" -- "0..*" OrderItem : "é composto por"

    Order "1" -- "1" Address : "entrega em"
    Client "1" -- "0..*" Address : "tem"

    Product "1" -- "1" OrderItem : "refers to"
    Product "1" -- "1" CartItem : "refers to"

    User "1" -- "0..*" Log : "realiza" %% Um usuário realiza/gera logs

    Cart "1" --> Coupon : "aplica"
    Admin "1" -- "0..*" Coupon : "gerencia"
