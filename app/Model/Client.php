<?php

namespace App\Model;

use DateTime;
use App\Model\Cart; // Importa a classe Cart
use App\Model\Order; // Importa a classe Order
use App\Model\Address; // Importa a classe Address
use App\Utility\PdfGenerator; 
use App\Service\OrderService;

/**
 * Class Client
 * @package App\Model
 *
 * Representa um usuário com perfil de cliente.
 * Herda de User e adiciona funcionalidades específicas de compra.
 */
class Client extends User
{
    /**
     * Construtor da classe Client.
     * Define o papel como 'client' por padrão.
     *
     * @param string $email O email do cliente.
     * @param string $firstName O primeiro nome do cliente.
     * @param string $lastName O sobrenome do cliente.
     * @param string $pswd A senha (já criptografada) do cliente.
     * @param int|null $id O ID do cliente (opcional).
     * @param DateTime|null $createdAt A data de criação (opcional).
     */
    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $pswd,
        ?int $id = null,
        ?DateTime $createdAt = null
    ) {
        parent::__construct($email, $firstName, $lastName, 'client', $pswd, $id, $createdAt);
    }

    /**
     * Adiciona um produto ao carrinho do cliente.
     * @param int $productId O ID do produto a ser adicionado.
     * @param int $quantity A quantidade do produto.
     * @return void
     */
    public function addToCart(int $productId, int $quantity): void
    {
        // Lógica para adicionar item ao carrinho.
        // O carrinho pode ser gerenciado via sessão ou persistido no DB.
        // (new CartService())->addItem($this->getId(), $productId, $quantity);
        echo "Cliente " . $this->getFirstName() . ": Adicionando " . $quantity . " do produto ID " . $productId . " ao carrinho.\n";
    }

    /**
     * Remove um produto do carrinho do cliente.
     * @param int $productId O ID do produto a ser removido.
     * @return void
     */
    public function removeFromCart(int $productId): void
    {
        // Lógica para remover item do carrinho.
        // (new CartService())->removeItem($this->getId(), $productId);
        echo "Cliente " . $this->getFirstName() . ": Removendo produto ID " . $productId . " do carrinho.\n";
    }

    /**
     * Visualiza o carrinho atual do cliente.
     * @return Cart O objeto Cart do cliente.
     */

    /**
     * Finaliza o processo de compra.
     * Regra: Só podem ser comprados se `quantidade - reservado > 0`.
     * Regra: Se for o último item, ele é reservado por 2 minutos. 
     * Regra: Se o cliente não concluir a compra nesse tempo, a reserva é cancelada. 
     * @param string $paymentMethod O método de pagamento.
     * @param Address $address O endereço de entrega.
     * @param string|null $couponCode Código de desconto opcional.
     * @return Order A ordem de compra criada.
     */
    public function checkout(string $paymentMethod, Address $address, ?string $couponCode = null): Order
    {
        // Lógica para processar o checkout:
        // 1. Obter o carrinho.
        // 2. Verificar estoque e reservas.
        // 3. Aplicar cupom, se houver.
        // 4. Criar a ordem e os OrderItems.
        // 5. Decrementar o estoque dos produtos.
        // 6. Limpar o carrinho.
        // (new CompraService())->processCheckout($this->getId(), $paymentMethod, $address, $couponCode);
        echo "Cliente " . $this->getFirstName() . ": Finalizando compra com método " . $paymentMethod . " e endereço " . $address->getCity() . "...\n";
        return new Order(
            0, // ID temporário
            $this->getId(),
            'PENDING',
            new DateTime(),
            0.0, // Total calculado posteriormente
            $paymentMethod,
            $address,
            [] // Itens da ordem
        );
    }

    /**
     * Visualiza o histórico de compras do cliente.
     * @return array<Order> Um array de objetos Order.
     */
    public function viewOrderHistory(): array
    {
        // Lógica para buscar o histórico de compras do cliente (via OrderRepository)
        echo "Cliente " . $this->getFirstName() . ": Visualizando histórico de compras...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Gera um comprovante de compra em PDF para uma ordem específica.
     * @param int $orderId O ID da ordem.
     * @return string O caminho para o arquivo PDF gerado ou o conteúdo do PDF.
     */
    public function generateReceipt(int $orderId, OrderService $orderService, bool $stream = true): ?string
    {
        // O OrderService agora lida com a lógica de buscar a ordem e gerar o HTML/PDF.
        // Se $stream for true, PdfGenerator::generatePdf já enviará o PDF e chamará exit().
        if ($stream) {
            $orderService->generateOrderReceiptPdf($orderId, true); // O segundo param é $stream para PdfGenerator
            // Não retorna nada, pois a saída já foi enviada e a execução é encerrada.
            return null;
        } else {
            return $orderService->generateOrderReceiptPdf($orderId, false);
        }
    }

    /**
     * Verifica se esta é a primeira compra do cliente.
     * Isso pode ser usado para regras de negócio como cupons de primeira compra.
     * @return bool
     */
    public function isFirstPurchase(): bool
    {
        // Lógica para verificar no banco de dados se o cliente já tem compras anteriores.
        // Ex: return !(new OrderRepository())->hasOrders($this->getId());
        echo "Cliente " . $this->getFirstName() . ": Verificando se é a primeira compra...\n";
        return true; // Retorno de exemplo
    }
}