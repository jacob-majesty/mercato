<?php

namespace App\DTO;

/**
 * Class CartAddItemDTO
 * @package App\DTO
 *
 * DTO para encapsular os dados de entrada ao adicionar um item ao carrinho.
 */
class CartAddItemDTO
{
    public int $clientId;
    public int $productId;
    public int $quantity;

    public function __construct(array $data)
    {
        if (empty($data['clientId']) || empty($data['productId']) || empty($data['quantity'])) {
            throw new \InvalidArgumentException("Dados inválidos ou incompletos para adicionar item ao carrinho.");
        }

        $this->clientId = (int)$data['clientId'];
        $this->productId = (int)$data['productId'];
        $this->quantity = (int)$data['quantity'];

        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException("A quantidade deve ser maior que zero.");
        }
    }
}