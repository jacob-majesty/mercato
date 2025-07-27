<?php

namespace App\Repository;

use App\Model\Cart;
use App\Model\CartItem;
use PDO;
use DateTime;
use Exception;
use App\Interfaces\CartRepositoryInterface;

/**
 * Class CartRepository
 * @package App\Repository
 *
 * Implementação do CartRepositoryInterface para persistência de dados de carrinhos no banco de dados.
 */
class CartRepository implements CartRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByClientId(int $clientId): ?Cart
    {
        $sql = "SELECT c.id AS cart_id, c.client_id, c.created_at, c.updated_at
                FROM carts c
                WHERE c.client_id = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        $cartData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartData) {
            return null;
        }

        // Hydrate Cart object
        $cart = new Cart(
            (int)$cartData['cart_id'],
            (int)$cartData['client_id'],
            $this->findCartItemsByCartId((int)$cartData['cart_id'])
        );

        return $cart;
    }

    public function save(Cart $cart): Cart
    {
        $this->pdo->beginTransaction();
        try {
            if ($cart->getId() === null) {
                // Novo carrinho
                $sql = "INSERT INTO carts (client_id, created_at, updated_at) VALUES (:clientId, :createdAt, :updatedAt)";
                $stmt = $this->pdo->prepare($sql);
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $stmt->bindValue(':clientId', $cart->getClientId(), PDO::PARAM_INT);
                $stmt->bindValue(':createdAt', $now);
                $stmt->bindValue(':updatedAt', $now);
                $stmt->execute();

                $cart->setId((int)$this->pdo->lastInsertId());
            } else {
                // Atualizar carrinho existente (apenas updated_at, itens são tratados separadamente)
                $sql = "UPDATE carts SET updated_at = :updatedAt WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $stmt->bindValue(':updatedAt', $now);
                $stmt->bindValue(':id', $cart->getId(), PDO::PARAM_INT);
                $stmt->execute();

                // Deletar itens antigos do carrinho para reinserir os novos/atualizados
                $this->deleteCartItemsByCartId($cart->getId());
            }

            // Salvar (ou reinserir) os itens do carrinho
            $this->saveCartItems($cart->getId(), $cart->getItems());

            $this->pdo->commit();
            return $cart;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao salvar o carrinho: " . $e->getMessage());
        }
    }

    public function delete(int $cartId): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Primeiro, deletar os itens do carrinho
            $this->deleteCartItemsByCartId($cartId);

            // Depois, deletar o carrinho em si
            $sql = "DELETE FROM carts WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $cartId, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao deletar o carrinho: " . $e->getMessage());
        }
    }

    /**
     * Helper para buscar os itens de um carrinho específico.
     * @param int $cartId O ID do carrinho.
     * @return CartItem[]
     */
    private function findCartItemsByCartId(int $cartId): array
    {
       $stmt = $this->pdo->prepare("
            SELECT ci.id, ci.cart_id, ci.product_id, ci.quantity, ci.unit_price, p.name AS product_name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = :cart_id
        ");
        $stmt->execute(['cart_id' => $cartId]);
        $items = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new CartItem(
                (int)$data['id'],          // Argumento 1: id
                (int)$data['cart_id'],     // Argumento 2: cartId (agora um int)
                (int)$data['product_id'],  // Argumento 3: productId
                (int)$data['quantity'],    // Argumento 4: quantity
                (float)$data['unit_price'],// Argumento 5: unitPrice
                $data['product_name']      // Argumento 6: productName
            );
        }
        return $items;
    }

    /**
     * Helper para salvar/reinserir os itens de um carrinho.
     * Assume que itens antigos foram deletados se o carrinho já existia.
     * @param int $cartId O ID do carrinho ao qual os itens pertencem.
     * @param CartItem[] $items Array de objetos CartItem.
     * @return void
     */
   
    public function saveCartItems(int $cartId, array $items): void
    {
        if (empty($items)) {
            return;
        }

        // Removido 'product_name' da lista de colunas
        $sql = "INSERT INTO cart_items (cart_id, product_id, unit_price, quantity)
                VALUES (:cartId, :productId, :unitPrice, :quantity)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($items as $item) {
            $stmt->bindValue(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindValue(':productId', $item->getProductId(), PDO::PARAM_INT);
            // Removida a linha de bind para ':productName'
            $stmt->bindValue(':unitPrice', $item->getUnitPrice(), PDO::PARAM_STR);
            $stmt->bindValue(':quantity', $item->getQuantity(), PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * Helper para deletar todos os itens de um carrinho específico.
     * @param int $cartId
     * @return bool
     */
    private function deleteCartItemsByCartId(int $cartId): bool
    {
        $sql = "DELETE FROM cart_items WHERE cart_id = :cartId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cartId', $cartId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}