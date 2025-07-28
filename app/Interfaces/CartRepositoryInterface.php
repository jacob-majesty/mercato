<?php

namespace App\Interfaces;

use App\Model\Cart;

/**
 * Interface CartRepositoryInterface
 * @package App\Repository
 *
 * Define o contrato para operações de persistência de carrinhos de compra.
 */
interface CartRepositoryInterface
{
    /**
     * Encontra um carrinho pelo ID do cliente.
     * Um cliente deve ter apenas um carrinho ativo.
     *
     * @param int $clientId
     * @return Cart|null Retorna o objeto Cart se encontrado, ou null.
     */
    public function findByClientId(int $clientId): ?Cart;

    /**
     * Salva um objeto Cart no banco de dados.
     * Isso inclui a criação de um novo carrinho ou a atualização de um existente.
     *
     * @param Cart $cart
     * @return Cart Retorna o objeto Cart salvo, possivelmente com um ID atualizado.
     */
    public function save(Cart $cart): Cart;

    /**
     * Deleta um carrinho pelo ID.
     *
     * @param int $cartId
     * @return bool True se o carrinho foi deletado com sucesso, false caso contrário.
     */
    public function delete(int $cartId): bool;
}