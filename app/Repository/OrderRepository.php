<?php

namespace App\Repository;

use App\Model\Order;
use App\Model\OrderItem;
use App\Model\Address;
use PDO;
use DateTime;
use Exception;

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

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Order $order): Order
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Salvar ou obter ID do Endereço
            $addressId = $this->saveAddress($order->getDeliveryAddress());
            $order->getDeliveryAddress()->setId($addressId);

            // 2. Salvar o Pedido
            // Adicionado 'coupon_code' na query de INSERT
            $sql = "INSERT INTO orders (client_id, status, order_date, total_amount, payment_method, coupon_code, address_id) VALUES (:clientId, :status, :orderDate, :totalAmount, :paymentMethod, :couponCode, :addressId)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':clientId', $order->getClientId(), PDO::PARAM_INT);
            $stmt->bindValue(':status', $order->getStatus());
            $stmt->bindValue(':orderDate', $order->getOrderDate()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':totalAmount', $order->getTotalAmount());
            $stmt->bindValue(':paymentMethod', $order->getPaymentMethod());
            $stmt->bindValue(':couponCode', $order->getCouponCode()); // Novo bindValue
            $stmt->bindValue(':addressId', $order->getDeliveryAddress()->getId(), PDO::PARAM_INT);

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
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zipCode, a.id as address_id
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
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zipCode, a.id as address_id
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
            if ($order->getDeliveryAddress()->getId()) {
                $this->updateAddress($order->getDeliveryAddress());
            } else {
                $addressId = $this->saveAddress($order->getDeliveryAddress());
                $order->getDeliveryAddress()->setId($addressId);
            }

            // Adicionado 'coupon_code' na query de UPDATE
            $sql = "UPDATE orders SET client_id = :clientId, status = :status, order_date = :orderDate, total_amount = :totalAmount, payment_method = :paymentMethod, coupon_code = :couponCode, address_id = :addressId WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':clientId', $order->getClientId(), PDO::PARAM_INT);
            $stmt->bindValue(':status', $order->getStatus());
            $stmt->bindValue(':orderDate', $order->getOrderDate()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':totalAmount', $order->getTotalAmount());
            $stmt->bindValue(':paymentMethod', $order->getPaymentMethod());
            $stmt->bindValue(':couponCode', $order->getCouponCode()); // Novo bindValue
            $stmt->bindValue(':addressId', $order->getDeliveryAddress()->getId(), PDO::PARAM_INT);
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
        $sql = "SELECT o.*, o.coupon_code, a.street, a.number, a.complement, a.state, a.country, a.city, a.zipCode, a.id as address_id
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

    public function saveAddress(Address $address): int
    {
        if ($address->getId()) {
            $this->updateAddress($address);
            return $address->getId();
        }

        $sql = "INSERT INTO addresses (street, number, complement, state, country, city, zipCode) VALUES (:street, :number, :complement, :state, :country, :city, :zipCode)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':street', $address->getStreet());
        $stmt->bindValue(':number', $address->getNumber(), PDO::PARAM_INT);
        $stmt->bindValue(':complement', $address->getComplement());
        $stmt->bindValue(':state', $address->getState());
        $stmt->bindValue(':country', $address->getCountry());
        $stmt->bindValue(':city', $address->getCity());
        $stmt->bindValue(':zipCode', $address->getZipCode());
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function updateAddress(Address $address): bool
    {
        if (!$address->getId()) {
            throw new Exception("Não é possível atualizar um endereço sem ID.");
        }
        $sql = "UPDATE addresses SET street = :street, number = :number, complement = :complement, state = :state, country = :country, city = :city, zipCode = :zipCode WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':street', $address->getStreet());
        $stmt->bindValue(':number', $address->getNumber(), PDO::PARAM_INT);
        $stmt->bindValue(':complement', $address->getComplement());
        $stmt->bindValue(':state', $address->getState());
        $stmt->bindValue(':country', $address->getCountry());
        $stmt->bindValue(':city', $address->getCity());
        $stmt->bindValue(':zipCode', $address->getZipCode());
        $stmt->bindValue(':id', $address->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function hydrateOrder(array $orderData): Order
    {
        $address = new Address(
            $orderData['address_id'],
            $orderData['street'],
            $orderData['number'],
            $orderData['complement'],
            $orderData['state'],
            $orderData['country'],
            $orderData['city'],
            $orderData['zipCode']
        );

        $orderItems = $this->findOrderItemsByOrderId($orderData['id']);

        return new Order(
            $orderData['id'],
            $orderData['client_id'],
            $orderData['status'],
            new DateTime($orderData['order_date'] ?? 'now'),
            (float)$orderData['total_amount'],
            $orderData['payment_method'],
            $address,
            $orderItems,
            $orderData['coupon_code'] ?? null // Novo: Hidrata o código do cupom
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