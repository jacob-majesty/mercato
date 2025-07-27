<?php

namespace App\Interfaces;

use App\Model\Order;
use App\Model\Address;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findAll(): array;
    public function save(Order $order): Order;
    public function update(Order $order): bool;
    public function delete(int $id): bool;

    /**
     * Busca todos os pedidos para um cliente específico.
     * @param int $clientId O ID do cliente.
     * @return Order[] Uma array de objetos Order.
     */
    public function getOrdersByClientId(int $clientId): array;

    public function hydrateOrder(array $orderData): Order;
    public function findOrderItemsByOrderId(int $orderId): array;
}