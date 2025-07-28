<?php

namespace App\Model;

/**
 * Class Cart
 * @package App\Model
 *
 * Representa o carrinho de compras de um cliente.
 */
class Cart
{
    private ?int $id; // ID do carrinho no banco de dados (se persistido)
    private int $clientId;
    /**
     * @var CartItem[] $items
     */
    private array $items; // Coleção de objetos CartItem

    public function __construct(?int $id, int $clientId, array $items = [])
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->items = []; // Inicializa como array vazio para garantir tipo
        foreach ($items as $item) {
            $this->addItem($item); // Adiciona os itens usando o método para validação
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @return CartItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(CartItem $newItem): void
    {
        foreach ($this->items as $index => $existingItem) {
            if ($existingItem->getProductId() === $newItem->getProductId()) {
                // Se o produto já existe, atualiza a quantidade
                $existingItem->setQuantity($existingItem->getQuantity() + $newItem->getQuantity());
                return;
            }
        }
        // Se o produto não existe, adiciona como novo item
        $this->items[] = $newItem;
    }

    public function removeItem(int $productId): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getProductId() === $productId) {
                unset($this->items[$index]);
                $this->items = array_values($this->items); // Reindexa o array
                return;
            }
        }
    }

    public function updateItemQuantity(int $productId, int $newQuantity): void
    {
        foreach ($this->items as $item) {
            if ($item->getProductId() === $productId) {
                $item->setQuantity($newQuantity);
                return;
            }
        }
        throw new \InvalidArgumentException("Produto com ID {$productId} não encontrado no carrinho.");
    }

    public function getTotalAmount(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getTotal();
        }
        return $total;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Verifica se o carrinho está vazio.
     * @return bool True se o carrinho não contiver itens, false caso contrário.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}