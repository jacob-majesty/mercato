<?php

namespace App\Model;

use DateTime;
use App\Model\Address;
use App\Model\OrderItem;

/**
 * Class Order
 * @package App\Model
 *
 * Representa uma ordem de compra realizada por um cliente.
 */
class Order
{
    private ?int $id;
    private int $clientId;
    private string $status; // Ex: 'PENDING', 'COMPLETED', 'CANCELLED', 'PROCESSING'
    private DateTime $orderDate;
    private float $totalAmount;
    private string $paymentMethod;
    private Address $deliveryAddress; // O endereço de entrega da ordem
    private ?string $couponCode; // Novo: Código do cupom aplicado
    /**
     * @var OrderItem[] $items
     */
    private array $items; // Coleção de OrderItem

    /**
     * Construtor da classe Order.
     *
     * @param int|null $id O ID da ordem.
     * @param int $clientId O ID do cliente que fez a ordem.
     * @param string $status O status atual da ordem.
     * @param DateTime $orderDate A data e hora em que a ordem foi criada.
     * @param float $totalAmount O valor total da ordem.
     * @param string $paymentMethod O método de pagamento utilizado.
     * @param Address $deliveryAddress O endereço de entrega associado a esta ordem.
     * @param array $items Um array de objetos OrderItem.
     * @param string|null $couponCode O código do cupom aplicado, se houver.
     */
    public function __construct(
        ?int $id,
        int $clientId,
        string $status,
        DateTime $orderDate,
        float $totalAmount,
        string $paymentMethod,
        Address $deliveryAddress,
        array $items,
        ?string $couponCode = null // Adicionado novo parâmetro
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->status = $status;
        $this->orderDate = $orderDate;
        $this->totalAmount = $totalAmount;
        $this->paymentMethod = $paymentMethod;
        $this->deliveryAddress = $deliveryAddress;
        $this->items = $items;
        $this->couponCode = $couponCode; // Atribuição
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCouponCode(): ?string 
    {
        return $this->couponCode;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setStatus(string $status): void
    {
        // Adicionar validação de status se necessário
        $this->status = $status;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function setCouponCode(?string $couponCode): void // Novo Setter
    {
        $this->couponCode = $couponCode;
    }

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