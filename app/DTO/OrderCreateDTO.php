<?php

namespace App\DTO;

use App\Model\Cart; // Assumindo que o OrderCreateDTO pode receber um objeto Cart

/**
 * Class OrderCreateDTO
 * @package App\DTO
 *
 * DTO para encapsular os dados de entrada para a criação de um novo pedido.
 * Ele conterá informações como o carrinho, método de pagamento e, opcionalmente,
 * o endereço de entrega (se não for pré-existente ou escolhido).
 */
class OrderCreateDTO
{
    public int $clientId;
    public string $paymentMethod;
    public array $cartItems; // Array de arrays ou de CartItemDTOs (e.g., [['productId' => 1, 'quantity' => 2]])
    public ?array $deliveryAddress = null; // Detalhes do endereço se forem novos, e.g., ['street' => 'Rua X', ...]
    public ?string $couponCode = null; // Código do cupom, se aplicado

    public function __construct(array $data)
    {
        $this->clientId = (int)($data['clientId'] ?? 0);
        $this->paymentMethod = $data['paymentMethod'] ?? '';
        $this->cartItems = $data['cartItems'] ?? []; // Itens do carrinho para a ordem
        $this->deliveryAddress = $data['deliveryAddress'] ?? null;
        $this->couponCode = $data['couponCode'] ?? null;
    }
}