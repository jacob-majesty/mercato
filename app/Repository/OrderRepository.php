<?php

namespace App\Repository;

use App\Model\Order;
use App\Model\OrderItem;
use App\Model\Address;
use PDO;
use DateTime;
use Exception;
use App\Repository\AddressRepository;

use App\Interfaces\OrderRepositoryInterface;

/**
 * Class OrderRepository
 * @package App\Repository
 *
 * Implementação do OrderRepositoryInterface para persistência de dados de pedidos no banco de dados.
 */
class OrderRepository implements OrderRepositoryInterface
{
    private PDO $pdo;
    private AddressRepository $addressRepository;
    private ProductRepository $productRepository;

    public function __construct(PDO $pdo, AddressRepository $addressRepository, ProductRepository $productRepository)
    {
        $this->pdo = $pdo;
        $this->addressRepository = $addressRepository;
        $this->productRepository = $productRepository;

    }

       
     public function save(Order $order): Order
    {
        $this->pdo->beginTransaction();
        try {
            // Corrigido: Chama o save do AddressRepository, que deve retornar o objeto Address com ID.
            $savedAddress = $this->addressRepository->save($order->getDeliveryAddress());
            
            // Define o objeto Address no Order, garantindo que o tipo está correto.
            $order->setDeliveryAddress($savedAddress);
            
            $sql = "INSERT INTO orders (client_id, status, order_date, total_amount, payment_method, address_id, coupon_code, discount_amount)
                    VALUES (:clientId, :status, :orderDate, :totalAmount, :paymentMethod, :addressId, :couponCode, :discountAmount)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':clientId', $order->getClientId());
            $stmt->bindValue(':status', $order->getStatus());
            $stmt->bindValue(':orderDate', $order->getOrderDate()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':totalAmount', $order->getTotalAmount());
            $stmt->bindValue(':paymentMethod', $order->getPaymentMethod());
            $stmt->bindValue(':addressId', $order->getDeliveryAddress()->getId());
            $stmt->bindValue(':couponCode', $order->getCouponCode());
            $stmt->bindValue(':discountAmount', $order->getDiscountAmount());
            $stmt->execute();
            
            $orderId = $this->pdo->lastInsertId();
            $order->setId((int) $orderId);

            // Salva os itens do pedido
            foreach ($order->getItems() as $item) {
                $item->setOrderId((int) $orderId);
                $this->saveOrderItem($item);
            }
            
            $this->pdo->commit();
            return $order;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao salvar pedido: " . $e->getMessage());
        }
    }

     /**
     * Salva um item do pedido no banco de dados.
     * Corrigido: Este método foi adicionado para resolver o erro "Call to unknown method".
     * @param OrderItem $item
     * @return bool
     */
    private function saveOrderItem(OrderItem $item): bool
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:orderId, :productId, :quantity, :price)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':orderId', $item->getOrderId());
        $stmt->bindValue(':productId', $item->getProductId());
        $stmt->bindValue(':quantity', $item->getQuantity());
        $stmt->bindValue(':price', $item->getUnitPrice());
        return $stmt->execute();
    }
    

    /**
     * @param int $orderId
     * @param string $newStatus
     * @return bool
     */
    public function updateStatus(int $orderId, string $newStatus): bool
    {
        $sql = "UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $newStatus);
        $stmt->bindValue(':id', $orderId);
        return $stmt->execute();
    }

    
    public function findById(int $orderId): ?Order
    {
        $sql = "SELECT 
                    o.*, 
                    a.id AS address_id, a.client_id AS address_client_id, a.street, a.number, a.complement, a.neighborhood, a.city, a.state, a.zip_code, a.country, a.recipient
                FROM orders o
                JOIN addresses a ON o.address_id = a.id
                WHERE o.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $orderId]);
        $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderData) {
            return null;
        }

        $orderItems = $this->findOrderItemsByOrderId($orderId);
        
        return $this->mapToOrder($orderData, $orderItems);
    }
    
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM orders ORDER BY order_date DESC");
        $orders = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $this->hydrateOrder($data);
        }
        return $orders;
    }

    /**
     * @inheritDoc
     */
    public function getOrdersByClientId(int $clientId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE client_id = :client_id ORDER BY order_date DESC");
        $stmt->execute([':client_id' => $clientId]);
        $orders = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $this->hydrateOrder($data);
        }
        return $orders;
    }

    public function update(Order $order): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Atualiza o endereço delegando ao AddressRepository
            $this->addressRepository->save($order->getDeliveryAddress());

            $sql = "UPDATE orders SET status = :status, total_amount = :total_amount, payment_method = :payment_method, address_id = :address_id, coupon_code = :coupon_code, discount_amount = :discount_amount, updated_at = :updated_at WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $now = (new DateTime())->format('Y-m-d H:i:s');

            $stmt->bindValue(':status', $order->getStatus());
            $stmt->bindValue(':total_amount', $order->getTotalAmount());
            $stmt->bindValue(':payment_method', $order->getPaymentMethod());
            $stmt->bindValue(':address_id', $order->getDeliveryAddress()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':coupon_code', $order->getCouponCode());
            $stmt->bindValue(':discount_amount', $order->getDiscountAmount());
            $stmt->bindValue(':updated_at', $now);
            $stmt->bindValue(':id', $order->getId(), PDO::PARAM_INT);

            $result = $stmt->execute();

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Deletar os itens do pedido primeiro para evitar erro de chave estrangeira
            $stmtItems = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = :id");
            $stmtItems->execute([':id' => $id]);

            // Depois, deletar o pedido
            $stmtOrder = $this->pdo->prepare("DELETE FROM orders WHERE id = :id");
            $result = $stmtOrder->execute([':id' => $id]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Hidrata um objeto Order a partir de um array de dados do banco de dados.
     * @param array $data
     * @return Order
     */
    public function hydrateOrder(array $data): Order
    {
        $address = $this->addressRepository->findById($data['address_id']);
        $items = $this->findOrderItemsByOrderId($data['id']);

        return new Order(
            (int)($data['id'] ?? 0),
            (int)($data['client_id'] ?? 0),
            $data['status'] ?? '',
            $data['order_date'] ? new DateTime($data['order_date']) : null,
            (float)($data['total_amount'] ?? 0.0),
            $data['payment_method'] ?? '',
            $address,
            $items,
            $data['coupon_code'] ?? null,
            (float)($data['discount_amount'] ?? 0.0),
            $data['order_created_at'] ? new DateTime($data['order_created_at']) : null,
            $data['order_updated_at'] ? new DateTime($data['order_updated_at']) : null
        );
    }
    
    /**
     * Helper para buscar os itens de um pedido específico.
     * @param int $orderId O ID do pedido.
     * @return OrderItem[]
     */
    
    public function findOrderItemsByOrderId(int $orderId): array
    {
        $sql = "SELECT oi.*, p.name AS product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :orderId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['orderId' => $orderId]);
        $items = [];
        while ($itemData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new OrderItem(
                (int)$itemData['id'],
                (int)$itemData['order_id'],
                (int)$itemData['product_id'],
                $itemData['product_name'], //  Adicionado o nome do produto
                (int)$itemData['quantity'],
                (float)$itemData['price']
            );
        }
        return $items;
    }
    
    /**
     * Mapeia um array de dados do banco de dados para um objeto OrderItem.
     * @param array $data
     * @return OrderItem
     */
    private function hydrateOrderItem(array $data): OrderItem
    {
        return new OrderItem(
            (int)($data['id'] ?? 0),
            (int)($data['order_id'] ?? 0),
            (int)($data['product_id'] ?? 0),
            $data['product_name'] ?? '',
            (int)($data['quantity'] ?? 0),
            (float)($data['unit_price'] ?? 0.0)
        );
    }

    private function mapToOrder(array $data, array $orderItems): Order
    {
        $address = new Address(
            (int)$data['address_id'],
            (int)$data['address_client_id'], // client_id agora vem da tabela 'addresses' (a.*)
            $data['street'] ?? '',
            (int)($data['number'] ?? 0),
            $data['complement'] ?? '',
            $data['neighborhood'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip_code'] ?? '',
            $data['country'] ?? '',
            $data['recipient'] ?? '' // recipient agora vem da tabela 'addresses' (a.*)
        );
        
        return new Order(
            (int)$data['id'],
            (int)$data['client_id'],
            $data['status'],
            new DateTime($data['order_date']),
            (float)$data['total_amount'],
            $data['payment_method'],
            $address,
            $orderItems,
            $data['coupon_code'] ?? null,
            (float)($data['discount_amount'] ?? 0.0)
        );
    }
}