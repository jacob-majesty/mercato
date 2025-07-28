<?php

namespace App\Model;

use DateTime;
use InvalidArgumentException;

/**
 * Class Order
 * @package App\Model
 *
 * Representa uma ordem de compra no sistema.
 */
class Order
{
    private ?int $id;
    private int $clientId;
    private string $status; // Ex: PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELLED
    private DateTime $orderDate;
    private float $totalAmount;
    private string $paymentMethod;
    private ?string $couponCode; // Código do cupom aplicado
    private float $discountAmount; // Adicionado: Valor do desconto aplicado
    private Address $deliveryAddress; // Objeto Address para o endereço de entrega
    private array $items; // Array de OrderItem objects
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        ?int $id,
        int $clientId,
        string $status,
        DateTime $orderDate,
        float $totalAmount,
        string $paymentMethod,
        Address $deliveryAddress, // Recebe um objeto Address
        array $items = [], // Itens da ordem
        ?string $couponCode = null,
        float $discountAmount = 0.0, // Adicionado ao construtor com valor padrão
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->status = $status;
        $this->orderDate = $orderDate;
        $this->totalAmount = $totalAmount;
        $this->paymentMethod = $paymentMethod;
        $this->deliveryAddress = $deliveryAddress;
        $this->items = $items;
        $this->couponCode = $couponCode;
        $this->discountAmount = $discountAmount; // Atribuição
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getClientId(): int { return $this->clientId; }
    public function getStatus(): string { return $this->status; }
    public function getOrderDate(): DateTime { return $this->orderDate; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getCouponCode(): ?string { return $this->couponCode; }
    public function getDiscountAmount(): float { return $this->discountAmount; } // Getter adicionado
    public function getDeliveryAddress(): Address { return $this->deliveryAddress; }
    public function getItems(): array { return $this->items; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }


    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setClientId(int $clientId): void { $this->clientId = $clientId; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setOrderDate(DateTime $orderDate): void { $this->orderDate = $orderDate; }
    public function setTotalAmount(float $totalAmount): void { $this->totalAmount = $totalAmount; }
    public function setPaymentMethod(string $paymentMethod): void { $this->paymentMethod = $paymentMethod; }
    public function setCouponCode(?string $couponCode): void { $this->couponCode = $couponCode; }
    public function setDiscountAmount(float $discountAmount): void { $this->discountAmount = $discountAmount; } // Setter adicionado
    public function setDeliveryAddress(Address $deliveryAddress): void { $this->deliveryAddress = $deliveryAddress; }
    public function setItems(array $items): void { $this->items = $items; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }

    /**
     * Adiciona um item à ordem.
     * @param OrderItem $item O item a ser adicionado.
     * @return void
     */
    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
        $this->calculateTotal(); // Recalcula o total ao adicionar item
    }

    /**
     * Remove um item da ordem.
     * @param OrderItem $item O item a ser removido.
     * @return void
     */
    public function removeItem(OrderItem $item): void
    {
        foreach ($this->items as $key => $existingItem) {
            if ($existingItem->getProductId() === $item->getProductId()) { // Assumindo que productId é único para um OrderItem
                unset($this->items[$key]);
                $this->items = array_values($this->items); // Reindexa o array
                $this->calculateTotal(); // Recalcula o total ao remover item
                return;
            }
        }
    }

    /**
     * Calcula o valor total da ordem com base nos itens.
     * @return float O valor total calculado.
     */
    public function calculateTotal(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity() * $item->getUnitPrice();
        }
        $this->totalAmount = $total;
        return $total;
    }

    /**
     * Marca a ordem como cancelada.
     * A lógica de reverter estoque ou processar reembolsos seria no OrderService.
     * @return void
     */
    public function cancel(): void
    {
        $this->setStatus('CANCELLED');
    }

    public function complete(): void
    {
        $this->setStatus('COMPLETED');
    }
}
