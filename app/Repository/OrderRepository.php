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

    public function __construct(PDO $pdo, AddressRepository $addressRepository)
    {
        $this->pdo = $pdo;
        $this->addressRepository = $addressRepository;
    }

       
    public function save(Order $order): Order
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Salvar ou obter ID do Endereço delegando ao AddressRepository
            $address = $this->addressRepository->save($order->getDeliveryAddress());
            $order->setDeliveryAddress($address);

            // 2. Salvar o Pedido
            $sql = "INSERT INTO orders (client_id, status, order_date, total_amount, payment_method, address_id, coupon_code, discount_amount, created_at, updated_at) VALUES (:client_id, :status, :order_date, :total_amount, :payment_method, :address_id, :coupon_code, :discount_amount, :created_at, :updated_at)";

            $stmt = $this->pdo->prepare($sql);
            $now = (new DateTime())->format('Y-m-d H:i:s');

            $stmt->bindValue(':client_id', $order->getClientId(), PDO::PARAM_INT);
            $stmt->bindValue(':status', $order->getStatus());
            $stmt->bindValue(':order_date', $order->getOrderDate()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':total_amount', $order->getTotalAmount());
            $stmt->bindValue(':payment_method', $order->getPaymentMethod());
            $stmt->bindValue(':address_id', $order->getDeliveryAddress()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':coupon_code', $order->getCouponCode());
            $stmt->bindValue(':discount_amount', $order->getDiscountAmount());
            $stmt->bindValue(':created_at', $now);
            $stmt->bindValue(':updated_at', $now);

            $stmt->execute();
            $order->setId((int)$this->pdo->lastInsertId());

            // 3. Salvar os Itens do Pedido
            foreach ($order->getItems() as $item) {
                $sqlItem = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (:orderId, :productId, :productName, :quantity, :unitPrice)";
                $stmtItem = $this->pdo->prepare($sqlItem);
                $stmtItem->bindValue(':orderId', $order->getId(), PDO::PARAM_INT);
                $stmtItem->bindValue(':productId', $item->getProductId(), PDO::PARAM_INT);
                $stmtItem->bindValue(':productName', $item->getProductName());
                $stmtItem->bindValue(':quantity', $item->getQuantity(), PDO::PARAM_INT);
                $stmtItem->bindValue(':unitPrice', $item->getUnitPrice());
                $stmtItem->execute();
            }

            $this->pdo->commit();
            return $order;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): ?Order
    {
        // Adicionado 'o.coupon_code' na query de SELECT
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zip_code, a.id as address_id
                FROM orders o
                JOIN addresses a ON o.address_id = a.id
                WHERE o.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderData) {
            return null;
        }

        return $this->hydrateOrder($orderData);
    }

    public function findAll(): array
    {
        // Adicionado 'o.coupon_code' na query de SELECT
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zip_code, a.id as address_id
                FROM orders o
                JOIN addresses a ON o.address_id = a.id";
        $stmt = $this->pdo->query($sql);
        $ordersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($ordersData as $orderData) {
            $orders[] = $this->hydrateOrder($orderData);
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
            $stmtItems = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = :orderId");
            $stmtItems->bindValue(':orderId', $id, PDO::PARAM_INT);
            $stmtItems->execute();

            $stmtOrder = $this->pdo->prepare("DELETE FROM orders WHERE id = :id");
            $stmtOrder->bindValue(':id', $id, PDO::PARAM_INT);
            $result = $stmtOrder->execute();

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getOrdersByClientId(int $clientId): array
    {
        // Adicionado 'o.coupon_code' na query de SELECT
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zip_code, a.id as address_id
                FROM orders o
                JOIN addresses a ON o.address_id = a.id
                WHERE o.client_id = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        $ordersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($ordersData as $orderData) {
            $orders[] = $this->hydrateOrder($orderData);
        }
        return $orders;
    }





     /**
     * Mapeia um array de dados do banco de dados para um objeto Order.
     * @param array $data
     * @return Order
     */
    public function hydrateOrder(array $data): Order
    {
        // Mapear o objeto Address primeiro
        $addressData = [
            'id' => (int)$data['address_id'],
            'street' => $data['street'],
            'number' => $data['number'],
            'complement' => $data['complement'],
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip_code' => $data['zip_code'],
            'country' => $data['country'],
            'created_at' => $data['address_created_at'],
            'updated_at' => $data['address_updated_at']
        ];
        $address = $this->addressRepository->mapToAddress($addressData); // Reutiliza o método de mapeamento do AddressRepository

        // Carregar os itens da ordem
        $items = $this->findOrderItemsByOrderId((int)$data['id']);

        return new Order(
            (int)$data['id'],
            (int)$data['client_id'],
            $data['status'],
            new DateTime($data['order_date']),
            (float)$data['total_amount'],
            $data['payment_method'],
            $address,
            $items,
            $data['coupon_code'],
            (float)$data['discount_amount'], // Mapeia o discount_amount
            new DateTime($data['order_created_at']),
            isset($data['order_updated_at']) ? new DateTime($data['order_updated_at']) : null
        );
    }

    /**
     * Helper para buscar os itens de um pedido específico.
     * @param int $orderId O ID do pedido.
     * @return OrderItem[]
     */
    public function findOrderItemsByOrderId(int $orderId): array
    {
        $sql = "SELECT id, product_id, product_name, quantity, unit_price FROM order_items WHERE order_id = :orderId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orderItems = [];
        foreach ($itemsData as $itemData) {
            $orderItems[] = new OrderItem(
                $itemData['id'],
                $orderId,
                $itemData['product_id'],
                $itemData['product_name'],
                $itemData['quantity'],
                (float)$itemData['unit_price']
            );
        }
        return $orderItems;
    }
}