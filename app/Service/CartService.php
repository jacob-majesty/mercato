<?php

namespace App\Service;

use App\Model\Cart;
use App\Model\CartItem;
use App\DTO\CartAddItemDTO;
use App\DTO\OrderCreateDTO;
use App\Repository\CartRepositoryInterface;
use App\Service\ProductService; // Para verificar estoque e obter dados do produto
use App\Service\OrderService;   // Para finalizar a compra
use App\Service\LogService;     // Para registrar ações do carrinho
use Exception;

/**
 * Class CartService
 * @package App\Service
 *
 * Gerencia a lógica de negócio relacionada aos carrinhos de compra.
 */
class CartService
{
    private CartRepositoryInterface $cartRepository;
    private ProductService $productService;
    private OrderService $orderService;
    private LogService $logService;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductService $productService,
        OrderService $orderService,
        LogService $logService
    ) {
        $this->cartRepository = $cartRepository;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->logService = $logService;
    }

    /**
     * Obtém o carrinho de compras de um cliente. Se não existir, cria um novo.
     * @param int $clientId
     * @return Cart
     */
    public function getCart(int $clientId): Cart
    {
        $cart = $this->cartRepository->findByClientId($clientId);
        if (!$cart) {
            $cart = new Cart(null, $clientId);
            $cart = $this->cartRepository->save($cart); // Salva o novo carrinho no banco
            $this->logService->log('Cart', 'New cart created', $clientId, ['cartId' => $cart->getId()]);
        }
        return $cart;
    }

    /**
     * Adiciona um produto ao carrinho do cliente, ou atualiza a quantidade se já existir.
     * @param CartAddItemDTO $dto
     * @return Cart O carrinho atualizado.
     * @throws Exception Se o produto não for encontrado ou o estoque for insuficiente.
     */
    public function addItem(CartAddItemDTO $dto): Cart
    {
        $cart = $this->getCart($dto->clientId); // Obtém ou cria o carrinho
        $product = $this->productService->getProductById($dto->productId);

        if (!$product) {
            throw new Exception("Produto com ID {$dto->productId} não encontrado.");
        }

        // Calcula a quantidade atual do item no carrinho para verificar o estoque total necessário
        $existingItemQuantity = 0;
        foreach ($cart->getItems() as $item) {
            if ($item->getProductId() === $dto->productId) {
                $existingItemQuantity = $item->getQuantity();
                break;
            }
        }

        $newTotalQuantityInCart = $existingItemQuantity + $dto->quantity;

        // Verifica o estoque disponível
        if (!$product->checkStock($newTotalQuantityInCart)) { // checkStock verifica (estoque - reservado)
            throw new Exception("Estoque insuficiente para o produto {$product->getName()}. Disponível: " . ($product->getStock() - $product->getReserved()));
        }

        // Cria ou atualiza o CartItem
        $cartItem = new CartItem(
            $product->getId(),
            $product->getName(),
            $product->getPrice(),
            $dto->quantity
        );
        $cart->addItem($cartItem); // O método addItem no Cart lida com atualização de quantidade ou adição

        $this->cartRepository->save($cart); // Persiste as mudanças no carrinho e seus itens
        $this->logService->log('Cart', 'Item added to cart', $dto->clientId, [
            'cartId' => $cart->getId(),
            'productId' => $dto->productId,
            'quantity' => $dto->quantity
        ]);
        return $cart;
    }

    /**
     * Remove um produto do carrinho.
     * @param int $clientId
     * @param int $productId
     * @return Cart O carrinho atualizado.
     * @throws Exception Se o carrinho ou o produto não forem encontrados.
     */
    public function removeItem(int $clientId, int $productId): Cart
    {
        $cart = $this->getCart($clientId);
        $cart->removeItem($productId);
        $this->cartRepository->save($cart);
        $this->logService->log('Cart', 'Item removed from cart', $clientId, [
            'cartId' => $cart->getId(),
            'productId' => $productId
        ]);
        return $cart;
    }

    /**
     * Atualiza a quantidade de um produto no carrinho.
     * @param int $clientId
     * @param int $productId
     * @param int $newQuantity
     * @return Cart O carrinho atualizado.
     * @throws Exception Se o produto não for encontrado, a quantidade for inválida ou o estoque for insuficiente.
     */
    public function updateItemQuantity(int $clientId, int $productId, int $newQuantity): Cart
    {
        if ($newQuantity <= 0) {
            return $this->removeItem($clientId, $productId); // Remove se a quantidade for 0 ou negativa
        }

        $cart = $this->getCart($clientId);
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            throw new Exception("Produto com ID {$productId} não encontrado.");
        }

        // Verifica o estoque para a nova quantidade
        if (!$product->checkStock($newQuantity)) {
            throw new Exception("Estoque insuficiente para o produto {$product->getName()} para a quantidade solicitada. Disponível: " . ($product->getStock() - $product->getReserved()));
        }

        $cart->updateItemQuantity($productId, $newQuantity);
        $this->cartRepository->save($cart);
        $this->logService->log('Cart', 'Cart item quantity updated', $clientId, [
            'cartId' => $cart->getId(),
            'productId' => $productId,
            'newQuantity' => $newQuantity
        ]);
        return $cart;
    }

    /**
     * Limpa completamente o carrinho de um cliente.
     * @param int $clientId
     * @return bool True se o carrinho foi limpo com sucesso.
     * @throws Exception Se o carrinho não for encontrado.
     */
    public function clearCart(int $clientId): bool
    {
        $cart = $this->cartRepository->findByClientId($clientId);
        if (!$cart) {
            return true; // Considera como sucesso se já não há carrinho
        }

        $result = $this->cartRepository->delete($cart->getId());
        if ($result) {
            $this->logService->log('Cart', 'Cart cleared', $clientId, ['cartId' => $cart->getId()]);
        }
        return $result;
    }

    /**
     * Finaliza a compra convertendo o carrinho em um pedido.
     * @param int $clientId
     * @param string $paymentMethod
     * @param array $deliveryAddress Dados do endereço de entrega.
     * @param string|null $couponCode Código do cupom, se houver.
     * @return \App\Model\Order O pedido criado.
     * @throws Exception Se o carrinho estiver vazio, estoque insuficiente, ou erro na criação do pedido.
     */
    public function checkout(int $clientId, string $paymentMethod, array $deliveryAddress, ?string $couponCode = null): \App\Model\Order
    {
        $cart = $this->getCart($clientId);

        if (empty($cart->getItems())) {
            throw new Exception("O carrinho está vazio. Não é possível finalizar a compra.");
        }

        $orderItemsData = [];
        foreach ($cart->getItems() as $cartItem) {
            $product = $this->productService->getProductById($cartItem->getProductId());
            if (!$product) {
                throw new Exception("Produto '{$cartItem->getProductName()}' não encontrado ao finalizar compra.");
            }
            // Verifica o estoque final antes de criar o pedido
            // A regra de reserva do último item é tratada no Product::checkStock() e Product::decrementStock()
            if (!$product->checkStock($cartItem->getQuantity())) {
                throw new Exception("Estoque insuficiente para o produto '{$product->getName()}'. Disponível: " . ($product->getStock() - $product->getReserved()));
            }

            $orderItemsData[] = [
                'productId' => $cartItem->getProductId(),
                'quantity' => $cartItem->getQuantity(),
                // productName e unitPrice virão do ProductService no createOrder, mas aqui passamos para o DTO
                'productName' => $cartItem->getProductName(), // Passa o nome do item do carrinho
                'unitPrice' => $cartItem->getUnitPrice() // Passa o preço do item do carrinho
            ];
        }

        // Cria o DTO para o OrderService
        $orderCreateDTO = new OrderCreateDTO([
            'clientId' => $clientId,
            'paymentMethod' => $paymentMethod,
            'cartItems' => $orderItemsData,
            'deliveryAddress' => $deliveryAddress,
            'couponCode' => $couponCode
        ]);

        // Cria o pedido usando o OrderService
        try {
            $order = $this->orderService->createOrder($orderCreateDTO);

            // Se o pedido for criado com sucesso, limpa o carrinho
            $this->clearCart($clientId);
            $this->logService->log('Order', 'Checkout completed', $clientId, ['orderId' => $order->getId(), 'cartId' => $cart->getId()]);

            return $order;
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Checkout failed', $clientId, [
                'source' => 'OrderService', // ou 'CartService' se o log for sobre o CartService
                'error' => $e->getMessage(),
                'cartId' => $cart->getId()
            ]);
        throw $e; // Re-lança a exceção após logar
}
    }
}