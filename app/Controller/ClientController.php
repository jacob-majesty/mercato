<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\Authenticator;
use App\Service\ClientService;
use App\Service\CartService;
use App\Service\OrderService;
use App\Service\ProductService; 
use App\Service\LogService;
use App\Repository\ClientRepository;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Config\Database;
use App\DTO\OrderCreateDTO; 
use App\DTO\CartAddItemDTO;

class ClientController
{
    private ClientService $clientService;
    private CartService $cartService;
    private OrderService $orderService; // Propriedade tipada
    private ProductService $productService;
    private LogService $logService;

    public function __construct(
        ClientService $clientService,
        CartService $cartService,
        OrderService $orderService, // Argumento do construtor
        ProductService $productService,
        LogService $logService
    ) {
        // Garanta que todas as propriedades são inicializadas aqui
        $this->clientService = $clientService;
        $this->cartService = $cartService;
        $this->orderService = $orderService; // Atribuição da propriedade
        $this->productService = $productService;
        $this->logService = $logService;
    }


    public function viewCart(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::redirect('/login'); // Redireciona se não estiver logado
        }

        try {
            $cart = $this->cartService->getCart($userId);
            return Response::view('client/cart', ['cart' => $cart]);
        } catch (\Exception $e) {
            $this->logService->log('Cart', 'Error viewing cart', $userId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar seu carrinho: ' . $e->getMessage()], 500);
        }
    }

    public function addToCart(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        // Decodifica o corpo da requisição JSON (assumindo que o frontend envia JSON)
        $data = json_decode($request->getBody(), true); 
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        if (!$productId || $quantity <= 0) {
            return Response::json(['success' => false, 'message' => 'Dados inválidos (ID do produto ou quantidade).'], 400);
        }

        try {
            // Cria uma instância de CartAddItemDTO com os dados necessários
            $cartAddItemDTO = new CartAddItemDTO([
                'clientId' => $userId,
                'productId' => $productId,
                'quantity' => $quantity
            ]);

            // Chama o método addItem do CartService, passando o DTO
            $this->cartService->addItem($cartAddItemDTO); 
            $this->logService->log('Cart', 'Product added to cart', $userId, ['productId' => $productId, 'quantity' => $quantity]);
            return Response::json(['success' => true, 'message' => 'Produto adicionado ao carrinho!'], 200);
        } catch (\Exception $e) {
            $this->logService->log('Cart', 'Failed to add product to cart', $userId, ['productId' => $productId, 'quantity' => $quantity, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function removeFromCart(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $productId = (int) $request->post('product_id');

        if (!$productId) {
            return Response::json(['success' => false, 'message' => 'Dados inválidos.'], 400);
        }

        try {
            $this->cartService->removeItem($userId, $productId);
            $this->logService->log('Cart', 'Product removed from cart', $userId, ['productId' => $productId]);
            return Response::json(['success' => true, 'message' => 'Produto removido do carrinho!'], 200);
        } catch (\Exception $e) {
            $this->logService->log('Cart', 'Failed to remove product from cart', $userId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showCheckout(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::redirect('/login');
        }

        try {
            $cart = $this->cartService->getCart($userId);
            if (empty($cart->getItems())) {
                return Response::redirect('/cart')->addHeader('Flash-Message', 'Seu carrinho está vazio.')->addHeader('Flash-Type', 'warning');
            }
            // Você pode carregar endereços salvos do cliente aqui, se houver um AddressService/Repository
            return Response::view('client/checkout', ['cart' => $cart]);
        } catch (\Exception $e) {
            $this->logService->log('Checkout', 'Error showing checkout', $userId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao preparar o checkout: ' . $e->getMessage()], 500);
        }
    }

    public function processCheckout(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $paymentMethod = $request->post('payment_method');
        $addressData = [ // Exemplo, ajuste conforme seu formulário de endereço
            'street' => $request->post('street'),
            'city' => $request->post('city'),
            'state' => $request->post('state'),
            'zip_code' => $request->post('zip_code'),
            'country' => $request->post('country')
        ];
        $couponCode = $request->post('coupon_code'); // Opcional

        if (empty($paymentMethod) || empty($addressData['street'])) { // Validação básica
            return Response::json(['success' => false, 'message' => 'Dados de pagamento ou endereço incompletos.'], 400);
        }

        try {
            // Primeiro, obtenha os itens do carrinho para passar para o DTO da ordem
            $cart = $this->cartService->getCart($userId);
            $cartItemsData = [];
            foreach ($cart->getItems() as $item) {
                $cartItemsData[] = [
                    'productId' => $item->getProductId(),
                    'quantity' => $item->getQuantity()
                ];
            }

            if (empty($cartItemsData)) {
                return Response::json(['success' => false, 'message' => 'Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.'], 400);
            }

            $orderDTO = new OrderCreateDTO([
                'clientId' => $userId,
                'paymentMethod' => $paymentMethod,
                'cartItems' => $cartItemsData,
                'deliveryAddress' => $addressData,
                'couponCode' => $couponCode
            ]);

            $order = $this->orderService->createOrder($orderDTO);
            $this->cartService->clearCart($userId); // Limpa o carrinho após o sucesso do pedido
            $this->logService->log('Order', 'Order created successfully', $userId, ['orderId' => $order->getId(), 'totalAmount' => $order->getTotalAmount()]);

            return Response::json(['success' => true, 'message' => 'Pedido realizado com sucesso!', 'order_id' => $order->getId()], 200);
        } catch (\InvalidArgumentException $e) {
            $this->logService->log('Checkout', 'Invalid checkout data', $userId, ['error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logService->log('Checkout', 'Failed to process checkout', $userId, ['error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao processar seu pedido: ' . $e->getMessage()], 500);
        }
    }

    public function viewOrders(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::redirect('/login');
        }

        try {
            $orders = $this->orderService->getOrdersByClientId($userId);
            $this->logService->log('Order', 'Viewed order history', $userId);
            return Response::view('client/orders', ['orders' => $orders]);
        } catch (\Exception $e) {
            $this->logService->log('Order', 'Error viewing order history', $userId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar seu histórico de pedidos: ' . $e->getMessage()], 500);
        }
    }

    public function generateReceipt(Request $request): Response
    {
        $userId = Authenticator::getUserId();
        if (!$userId) {
            return Response::redirect('/login');
        }

        $orderId = (int) $request->getRouteParam(0); // Assumindo /client/orders/{id}/receipt

        if (!$orderId) {
            return Response::view('errors/400', ['message' => 'ID do pedido não especificado.'], 400);
        }

        try {
            $order = $this->orderService->getOrderById($orderId);

            if (!$order || $order->getClientId() !== $userId) {
                // Garante que o cliente só pode gerar recibo para seus próprios pedidos
                return Response::view('errors/403', ['message' => 'Você não tem permissão para acessar este recibo.'], 403);
            }

            // O OrderService deve ter um método para gerar o PDF e enviar
            $this->orderService->generateOrderReceiptPdf($orderId, true); // O `true` indica para fazer o stream (enviar e encerrar)
            // Nenhum Response::view ou Response::json é retornado aqui, pois o PDF já foi enviado.
            exit(); // Garante que nenhuma outra saída é gerada
        } catch (\Exception $e) {
            $this->logService->log('Order', 'Error generating receipt', $userId, ['orderId' => $orderId, 'error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao gerar o recibo: ' . $e->getMessage()], 500);
        }
    }
}