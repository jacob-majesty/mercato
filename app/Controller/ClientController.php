<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\Authenticator;
use App\Service\ClientService;
use App\Service\CartService;
use App\Service\ProductService;
use App\Service\OrderService;
use App\Service\LogService;
use App\DTO\OrderCreateDTO;
use Exception;

class ClientController
{
    private ClientService $clientService;
    private CartService $cartService;
    private ProductService $productService; // Adicionado
    private OrderService $orderService; // Adicionado
    private LogService $logService; // Adicionado

    public function __construct(
        ClientService $clientService,
        CartService $cartService,
        ProductService $productService,
        OrderService $orderService,
        LogService $logService
    ) {
        $this->clientService = $clientService;
        $this->cartService = $cartService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->logService = $logService;
    }

    public function viewCart(Request $request): Response
    {
        $clientId = Authenticator::getUserId(); // Obtém o ID do cliente logado
        // Garante que clientId é int ou null
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            // Se não houver clientId, o usuário não está logado ou a sessão é inválida.
            // O middleware 'auth' já deveria ter lidado com isso, mas é uma boa prática defensiva.
            $this->logService->log('Cart', 'Attempt to view cart without valid client ID', null);
            return Response::redirect('/login'); // Redireciona para o login
        }

        try {
            $cart = $this->cartService->getCart($clientId);
            $this->logService->log('Cart', 'Viewed cart', $clientId, ['cartId' => $cart->getId()]);
            return Response::view('client/cart', ['cart' => $cart]);
        } catch (Exception $e) {
            $this->logService->log('Cart', 'Error viewing cart', $clientId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar seu carrinho: ' . $e->getMessage()], 500);
        }
    }

    public function addToCart(Request $request): Response
    {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null; // Garante que clientId é int ou null

        if (!$clientId) {
            return Response::json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        $data = json_decode($request->getBody(), true);
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            return Response::json(['success' => false, 'message' => 'Dados de produto ou quantidade inválidos.'], 400);
        }

        try {
            $this->cartService->addItem($clientId, $productId, $quantity);
            return Response::json(['success' => true, 'message' => 'Produto adicionado ao carrinho!'], 200);
        } catch (Exception $e) {
            $this->logService->log('Cart', 'Failed to add item to cart', $clientId, ['productId' => $productId, 'quantity' => $quantity, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao adicionar ao carrinho: ' . $e->getMessage()], 500);
        }
    }

    public function removeFromCart(Request $request): Response
    {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            return Response::json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        $data = json_decode($request->getBody(), true);
        $productId = (int)($data['product_id'] ?? 0);

        if ($productId <= 0) {
            return Response::json(['success' => false, 'message' => 'ID do produto inválido.'], 400);
        }

        try {
            $this->cartService->removeItem($clientId, $productId);
            return Response::json(['success' => true, 'message' => 'Produto removido do carrinho!'], 200);
        } catch (Exception $e) {
            $this->logService->log('Cart', 'Failed to remove item from cart', $clientId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao remover do carrinho: ' . $e->getMessage()], 500);
        }
    }

    public function updateItemQuantity(Request $request): Response
    {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            return Response::json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        $data = json_decode($request->getBody(), true);
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 0);

        if ($productId <= 0 || $quantity < 0) { // Quantidade pode ser 0 para remover
            return Response::json(['success' => false, 'message' => 'Dados de produto ou quantidade inválidos.'], 400);
        }

        try {
            if ($quantity === 0) {
                $this->cartService->removeItem($clientId, $productId);
                return Response::json(['success' => true, 'message' => 'Produto removido do carrinho!'], 200);
            } else {
                $this->cartService->updateItemQuantity($clientId, $productId, $quantity);
                return Response::json(['success' => true, 'message' => 'Quantidade atualizada!'], 200);
            }
        } catch (Exception $e) {
            $this->logService->log('Cart', 'Failed to update item quantity', $clientId, ['productId' => $productId, 'quantity' => $quantity, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao atualizar quantidade: ' . $e->getMessage()], 500);
        }
    }

    public function showCheckout(Request $request): Response
    {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            return Response::redirect('/login');
        }

        try {
            $cart = $this->cartService->getCart($clientId);
            if (!$cart || empty($cart->getItems())) {
                return Response::view('client/checkout', ['error' => 'Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.']);
            }
            return Response::view('client/checkout', ['cart' => $cart]);
        } catch (Exception $e) {
            $this->logService->log('Checkout', 'Error showing checkout form', $clientId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar a página de checkout: ' . $e->getMessage()], 500);
        }
    }

    public function processCheckout(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $data = json_decode($request->getBody(), true);

        // Validação básica dos dados do formulário
        if (empty($data['street']) || empty($data['number']) || empty($data['neighborhood']) ||
            empty($data['city']) || empty($data['state']) || empty($data['zip_code']) ||
            empty($data['payment_method'])) {
            return Response::json(['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios de endereço e pagamento.'], 400);
        }

        try {
            $cart = $this->cartService->getCart($userId); // Usar getCart()
            if (!$cart || empty($cart->getItems())) {
                return Response::json(['success' => false, 'message' => 'Seu carrinho está vazio. Não é possível finalizar a compra.'], 400);
            }

            // Mapear dados do carrinho e do request para o DTO
            $orderItemsData = [];
            foreach ($cart->getItems() as $cartItem) {
                $orderItemsData[] = [
                    'productId' => $cartItem->getProductId(),
                    'quantity' => $cartItem->getQuantity(),
                    'unitPrice' => $cartItem->getUnitPrice(),
                    'productName' => $cartItem->getProductName() // Garante que o nome do produto está no DTO
                ];
            }

            $orderCreateDTO = new OrderCreateDTO([
                'clientId' => $userId,
                'cartItems' => $orderItemsData, // Passa os itens do carrinho mapeados
                'deliveryAddress' => [
                    'street' => $data['street'],
                    'number' => $data['number'],
                    'complement' => $data['complement'] ?? null,
                    'neighborhood' => $data['neighborhood'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'zip_code' => $data['zip_code'],
                    'country' => $data['country'] ?? 'Brasil',
                ],
                'paymentMethod' => $data['payment_method'],
                'couponCode' => $data['coupon_code'] ?? null,
            ]);

            // Agora, passe o DTO completo para o clientService->checkout
            $order = $this->clientService->checkout($orderCreateDTO);

            $this->logService->log('Order', 'Order placed successfully', $userId, ['orderId' => $order->getId(), 'total' => $order->getTotalAmount()]);
            return Response::json(['success' => true, 'message' => 'Pedido realizado com sucesso!', 'order_id' => $order->getId()], 200);

        } catch (\InvalidArgumentException $e) {
            $this->logService->log('Order', 'Order checkout failed: Invalid data', $userId, ['error' => $e->getMessage(), 'data' => $data]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logService->log('Order', 'Order checkout error', $userId, ['error' => $e->getMessage(), 'data' => $data]);
            return Response::json(['success' => false, 'message' => 'Erro ao processar o pedido: ' . $e->getMessage()], 500);
        }
    }

        public function viewOrders(Request $request): Response
        {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            return Response::redirect('/login');
        }

        try {
            $orders = $this->clientService->viewOrderHistory($clientId);
            $this->logService->log('Order', 'Viewed order history', $clientId);
            return Response::view('client/orders', ['orders' => $orders]);
        } catch (Exception $e) {
            $this->logService->log('Order', 'Error viewing order history', $clientId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar seu histórico de pedidos: ' . $e->getMessage()], 500);
        }
    }

    public function generateReceipt(Request $request): Response
    {
        $clientId = Authenticator::getUserId();
        $clientId = is_numeric($clientId) ? (int)$clientId : null;

        if (!$clientId) {
            return Response::redirect('/login');
        }

        $orderId = (int) $request->getRouteParam(0);

        try {
            // O generateOrderReceiptPdf do OrderService já retorna o HTML do PDF
            $pdfContent = $this->clientService->generateReceipt($clientId, $orderId);

            $response = new Response($pdfContent, 200);
            $response->addHeader('Content-Type', 'application/pdf');
            $response->addHeader('Content-Disposition', 'inline; filename="comprovante_pedido_' . $orderId . '.pdf"');
            return $response;
        } catch (Exception $e) {
            $this->logService->log('Order', 'Error generating receipt', $clientId, ['orderId' => $orderId, 'error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao gerar comprovante: ' . $e->getMessage()], 500);
        }
    }

    public function showProduct(Request $request): Response
    {
        $productId = (int) $request->getRouteParam(0);
        if (!$productId) {
            return Response::view('errors/400', ['message' => 'ID do produto não especificado.'], 400);
        }

        try {
            $product = $this->productService->getProductById($productId);
            if (!$product) {
                return Response::view('errors/404', ['message' => 'Produto não encontrado.'], 404);
            }
            // Caminho da imagem de placeholder (ajuste conforme sua estrutura pública)
            $placeholderImagePath = '/products/placeholder.png';
            return Response::view('products/show', ['product' => $product, 'placeholderImagePath' => $placeholderImagePath]);
        } catch (Exception $e) {
            $this->logService->log('Product', 'Error showing product details', Authenticator::getUserId(), ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar detalhes do produto: ' . $e->getMessage()], 500);
        }
    }
}


 