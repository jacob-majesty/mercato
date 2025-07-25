<?php

namespace App\Service;

use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Repository\CouponRepositoryInterface;
use App\Model\Order;
use App\Model\OrderItem;
use App\Model\Address;
use App\DTO\OrderCreateDTO;
use App\Utility\PdfGenerator;
use Exception;
use DateTime;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ProductService $productService;
    private CouponRepositoryInterface $couponRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductService $productService,
        CouponRepositoryInterface $couponRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productService = $productService;
        $this->couponRepository = $couponRepository;
    }

    public function getOrderById(int $orderId): ?Order
    {
        return $this->orderRepository->findById($orderId);
    }

    public function createOrder(OrderCreateDTO $orderDTO): Order
    {
        if (empty($orderDTO->clientId) || empty($orderDTO->paymentMethod) || empty($orderDTO->cartItems)) {
            throw new \InvalidArgumentException("Dados inválidos ou incompletos para criar pedido.");
        }

        $totalAmount = 0.0;
        $orderItems = [];
        $appliedCouponCode = null; // Variável para armazenar o código do cupom aplicado

        foreach ($orderDTO->cartItems as $itemData) {
            $productId = $itemData['productId'] ?? null;
            $quantity = $itemData['quantity'] ?? null;

            if (!$productId || !$quantity || $quantity <= 0) {
                throw new \InvalidArgumentException("Item do carrinho inválido: ID do produto ou quantidade ausente/inválida.");
            }

            $product = $this->productService->getProductById($productId);
            if (!$product) {
                throw new Exception("Produto com ID {$productId} não encontrado.");
            }

            if (!$this->productService->checkProductStock($productId, $quantity)) {
                throw new Exception("Estoque insuficiente para o produto: {$product->getName()}.");
            }

            $this->productService->decrementStock($productId, $quantity);

            $orderItem = new OrderItem(
                null,           // ID (null para novo item)
                null,           // orderId (null inicialmente, será definido pelo repositório)
                $productId,     // productId
                $product->getName(), // productName
                $quantity,      // quantity
                $product->getPrice() // unitPrice
            );
            $orderItems[] = $orderItem;
            $totalAmount += $orderItem->getQuantity() * $orderItem->getUnitPrice();
        }

        if ($orderDTO->couponCode) {
            $coupon = $this->couponRepository->findByCode($orderDTO->couponCode);
            if ($coupon) {
                if ($coupon->isExpired() || !$coupon->isActive() || ($coupon->getMinCartValue() !== null && $totalAmount < $coupon->getMinCartValue())) {
                    // Cupom inválido ou não aplicável, ignora ou lança exceção
                    // Para este cenário, não definiremos appliedCouponCode
                } else {
                    if ($coupon->getType() === 'percentage') {
                        $totalAmount -= $totalAmount * $coupon->getDiscount();
                    } elseif ($coupon->getType() === 'fixed') {
                        $totalAmount -= $coupon->getDiscount();
                    }
                    $totalAmount = max(0, $totalAmount); // Garante que o total não seja negativo
                    $appliedCouponCode = $coupon->getCode(); // Define o código do cupom aplicado
                }
            }
        }

        $deliveryAddress = null;
        if ($orderDTO->deliveryAddress) {
            $deliveryAddress = new Address(
                $orderDTO->deliveryAddress['street'] ?? '',
                $orderDTO->deliveryAddress['number'] ?? 0,
                $orderDTO->deliveryAddress['complement'] ?? null,
                $orderDTO->deliveryAddress['state'] ?? '',
                $orderDTO->deliveryAddress['country'] ?? '',
                $orderDTO->deliveryAddress['neighborhood'] ?? '',
                $orderDTO->deliveryAddress['city'] ?? '',
                $orderDTO->deliveryAddress['zipCode'] ?? ''
            );
        } else {
            throw new \InvalidArgumentException("Endereço de entrega é obrigatório.");
        }

        $order = new Order(
            null,
            $orderDTO->clientId,
            'PENDING',
            new DateTime(),
            $totalAmount,
            $orderDTO->paymentMethod,
            $deliveryAddress,
            $orderItems,
            $appliedCouponCode // Passa o código do cupom para o construtor do Order
        );

        return $this->orderRepository->save($order);
    }

    public function updateOrderStatus(int $orderId, string $newStatus): bool
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw new Exception("Pedido não encontrado.");
        }
        $order->setStatus($newStatus);
        return $this->orderRepository->update($order);
    }

    public function cancelOrder(int $orderId): bool
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw new Exception("Pedido não encontrado para cancelamento.");
        }
        if ($order->getStatus() === 'COMPLETED' || $order->getStatus() === 'CANCELLED') {
            throw new Exception("Não é possível cancelar um pedido com status 'COMPLETED' ou 'CANCELLED'.");
        }
        $order->cancel();

        foreach ($order->getItems() as $item) {
            $this->productService->releaseStock($item->getProductId(), $item->getQuantity());
        }

        return $this->orderRepository->update($order);
    }

    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    public function getOrdersByClientId(int $clientId): array
    {
        return $this->orderRepository->getOrdersByClientId($clientId);
    }

     /**
     * Gera um comprovante de pedido em PDF.
     * @param int $orderId O ID da ordem.
     * @param bool $stream Define se o PDF deve ser enviado diretamente para o navegador (true) ou retornado como string (false).
     * @return string|void Retorna o conteúdo binário do PDF se $stream for false, caso contrário, envia para o navegador e encerra a execução.
     * @throws Exception Se a ordem não for encontrada ou houver erro na geração.
     */
    public function generateOrderReceiptPdf(int $orderId, bool $stream = true): ?string
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new Exception("Ordem com ID {$orderId} não encontrada para gerar comprovante.");
        }

        $htmlContent = $this->buildReceiptHtml($order);

        // Chame PdfGenerator e passe o parâmetro $stream corretamente
        if ($stream) {
            PdfGenerator::generatePdf($htmlContent, "comprovante_pedido_{$orderId}", true);
            // Se o PDF foi streamado, o script será encerrado por PdfGenerator::generatePdf
            return null; // Retorna null para satisfazer o tipo de retorno ?string
        } else {
            return PdfGenerator::generatePdf($htmlContent, "comprovante_pedido_{$orderId}", false);
        }
    }

    /**
     * Constrói o conteúdo HTML para o comprovante do pedido.
     * @param Order $order
     * @return string
     */
    private function buildReceiptHtml(Order $order): string
    {
        $itemsHtml = '';
        foreach ($order->getItems() as $item) {
            $itemsHtml .= "<tr>
                                <td style='border: 1px solid #ddd; padding: 8px;'>{$item->getProductName()}</td>
                                <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>{$item->getQuantity()}</td>
                                <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>R$ " . number_format($item->getUnitPrice(), 2, ',', '.') . "</td>
                                <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>R$ " . number_format($item->getTotal(), 2, ',', '.') . "</td>
                            </tr>";
        }

        $address = $order->getDeliveryAddress();
        $addressHtml = "
            <p><strong>Endereço de Entrega:</strong></p>
            <p>{$address->getStreet()}, {$address->getNumber()}" . ($address->getComplement() ? " - {$address->getComplement()}" : "") . "</p>
            <p>{$address->getCity()} - {$address->getState()}, {$address->getZipCode()}</p>
            <p>{$address->getCountry()}</p>
        ";

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Comprovante de Pedido #{$order->getId()}</title>
            <style>
                body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; }
                .container { width: 100%; max-width: 800px; margin: auto; border: 1px solid #eee; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                h1 { text-align: center; color: #333; }
                .header, .footer { text-align: center; margin-top: 20px; font-size: 0.9em; color: #555; }
                .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .details-table th, .details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details-table th { background-color: #f2f2f2; }
                .total { text-align: right; margin-top: 20px; font-size: 1.2em; font-weight: bold; }
                .section-title { margin-top: 30px; margin-bottom: 10px; font-size: 1.1em; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Mercato - Comprovante de Pedido</h1>
                <p><strong>Número do Pedido:</strong> #{$order->getId()}</p>
                <p><strong>Cliente ID:</strong> {$order->getClientId()}</p>
                <p><strong>Data do Pedido:</strong> {$order->getOrderDate()->format('d/m/Y H:i:s')}</p>
                <p><strong>Método de Pagamento:</strong> {$order->getPaymentMethod()}</p>
                <p><strong>Status:</strong> {$order->getStatus()}</p>
                " . ($order->getCouponCode() ? "<p><strong>Cupom Aplicado:</strong> {$order->getCouponCode()}</p>" : "") . "

                <div class='section-title'>Itens do Pedido:</div>
                <table class='details-table'>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style='text-align: center;'>Quantidade</th>
                            <th style='text-align: right;'>Preço Unitário</th>
                            <th style='text-align: right;'>Total Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHtml}
                    </tbody>
                </table>

                <p class='total'>Total do Pedido: R$ " . number_format($order->getTotalAmount(), 2, ',', '.') . "</p>

                <div class='section-title'>Dados de Entrega:</div>
                {$addressHtml}

                <div class='footer'>
                    Obrigado pela sua compra!
                </div>
            </div>
        </body>
        </html>";
    }
}