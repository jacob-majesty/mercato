<?php

namespace App\Service;

use App\Model\Client;
use App\Model\Cart;
use App\Model\Order;
use App\DTO\ClientCreateDTO;
use App\DTO\OrderCreateDTO;
use App\DTO\CartAddItemDTO; // Importar o CartAddItemDTO
use App\Interfaces\ClientRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface; // Usar a interface, não a implementação direta
use Exception;
use DateTime;

/**
 * Class ClientService
 * @package App\Service
 *
 * Gerencia a lógica de negócios relacionada aos clientes.
 */
class ClientService
{
    private ClientRepositoryInterface $clientRepository;
    private CartService $cartService;
    private OrderService $orderService;
    private LogService $logService;
    private OrderRepositoryInterface $orderRepository; // Usar a interface

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        CartService $cartService,
        OrderService $orderService,
        LogService $logService,
        OrderRepositoryInterface $orderRepository // Injetar OrderRepositoryInterface
    ) {
        $this->clientRepository = $clientRepository;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->logService = $logService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Registra um novo cliente.
     * @param ClientCreateDTO $clientDTO
     * @return Client
     * @throws Exception Se o email já existe ou a persistência falha.
     */
    public function registerClient(ClientCreateDTO $clientDTO): Client
    {
        // 1. Validação básica do DTO
        if (empty($clientDTO->email) || empty($clientDTO->firstName) || empty($clientDTO->lastName) || empty($clientDTO->password)) {
            throw new \InvalidArgumentException("Dados de registro incompletos.");
        }

        // 2. Verificar se o email já está em uso (usando ClientRepository, que deve ter findByEmail)
        if ($this->clientRepository->findByEmail($clientDTO->email)) {
            throw new Exception("Email já cadastrado.");
        }

        // 3. Hash da senha
        $hashedPassword = password_hash($clientDTO->password, PASSWORD_BCRYPT);

        // 4. Criar o objeto Client Model
       $client = new Client(
            $clientDTO->email,
            $clientDTO->firstName,
            $clientDTO->lastName,
            $hashedPassword, // Senha já hashada
            null, // ID (será definido após salvar no DB)
            'client', // Role fixo como 'client'
            new DateTime(), // createdAt
            null // updatedAt
        );

        // 5. Salvar o cliente via Repository
        try {
            $savedClient = $this->clientRepository->save($client);
            $this->logService->log('Client', 'Client registered', $savedClient->getId());
            return $savedClient;
        } catch (Exception $e) {
            $this->logService->log('Client', 'Client registration failed', null, ['email' => $clientDTO->email, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getClientById(int $clientId): ?Client
    {
        return $this->clientRepository->findById($clientId);
    }

    /**
     * Adiciona um produto ao carrinho do cliente.
     * @param int $clientId
     * @param int $productId
     * @param int $quantity
     * @return Cart O carrinho atualizado.
     * @throws Exception Se o cliente ou produto não for encontrado, ou falha ao adicionar.
     */
    public function addToCart(int $clientId, int $productId, int $quantity): Cart
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        // Criar o DTO para o CartService
        $cartAddItemDTO = new CartAddItemDTO([
            'clientId' => $clientId,
            'productId' => $productId,
            'quantity' => $quantity
        ]);

        // Chamar o CartService com o DTO
        $updatedCart = $this->cartService->addItem($cartAddItemDTO);
        $this->logService->log('Cart', 'Item added to cart', $clientId, ['productId' => $productId, 'quantity' => $quantity]);
        return $updatedCart; // Retorna o objeto Cart
    }

    /**
     * Remove um produto do carrinho do cliente.
     * @param int $clientId
     * @param int $productId
     * @return Cart O carrinho atualizado.
     * @throws Exception Se o cliente não for encontrado ou falha ao remover.
     */
    public function removeFromCart(int $clientId, int $productId): Cart
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }
        $updatedCart = $this->cartService->removeItem($clientId, $productId);
        if ($updatedCart) { // removeItem do CartService retorna Cart
            $this->logService->log('Cart', 'Item removed from cart', $clientId, ['productId' => $productId]);
        }
        return $updatedCart;
    }

    /**
     * Permite que um cliente visualize seu carrinho de compras.
     * @param int $clientId O ID do cliente.
     * @return Cart|null O objeto Cart do cliente ou null se não houver carrinho.
     * @throws Exception Se o cliente não for encontrado.
     */
    public function viewCart(int $clientId): ?Cart
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente com ID {$clientId} não encontrado.");
        }
        $this->logService->log('Cart', 'Client viewed cart', $clientId);
        return $this->cartService->getCart($clientId);
    }

    /**
     * Finaliza a compra do cliente, transformando o carrinho em um pedido.
     * @param int $clientId
     * @param OrderCreateDTO $orderDTO
     * @return Order O objeto Order criado.
     * @throws Exception Se o carrinho estiver vazio, dados inválidos ou falha na criação do pedido.
     */
    public function checkout(int $clientId, OrderCreateDTO $orderDTO): Order
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        // O CartService::checkout já lida com a validação do carrinho vazio e a preparação dos itens
        try {
            $order = $this->cartService->checkout(
                $clientId, // Passa o ID do cliente
                $orderDTO->paymentMethod,
                $orderDTO->deliveryAddress,
                $orderDTO->couponCode
            );
            $this->logService->log('Purchase', 'Checkout successful', $clientId, ['orderId' => $order->getId()]);
            return $order;
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Checkout failed', $clientId, [
                'source' => 'ClientService',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retorna o histórico de pedidos para um cliente específico.
     * @param int $clientId O ID do cliente.
     * @return array<App\Model\Order> Uma array de objetos Order.
     */
    public function viewOrderHistory(int $clientId): array
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }
        $this->logService->log('Order', 'Client viewed order history', $clientId);
        // Usar getOrdersByClientId da interface OrderRepositoryInterface
        return $this->orderRepository->getOrdersByClientId($clientId);
    }

    /**
     * Atualiza o perfil do cliente.
     * @param int $clientId
     * @param array $data Um array associativo com os campos a serem atualizados (email, firstName, lastName, password).
     * @return bool
     * @throws Exception Se o cliente não for encontrado ou falha na atualização.
     */
    public function updateClientProfile(int $clientId, array $data): bool
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        // Aplicar atualizações (apenas se os dados estiverem presentes)
        if (isset($data['email']) && $data['email'] !== $client->getEmail()) {
            if ($this->clientRepository->findByEmail($data['email'])) { // Isso só verifica clientes, melhor usar UserRepositoryInterface
                throw new Exception("Novo email já cadastrado.");
            }
            $client->setEmail($data['email']);
        }
        if (isset($data['firstName'])) {
            $client->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $client->setLastName($data['lastName']);
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $client->setPswd(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        $result = $this->clientRepository->update($client);
        if ($result) {
            $this->logService->log('Client', 'Client profile updated', $clientId);
        }
        return $result;
    }

    /**
     * Deleta a conta de um cliente.
     * @param int $clientId
     * @return bool
     * @throws Exception Se o cliente não for encontrado ou falha na exclusão.
     */
    public function deleteClientAccount(int $clientId): bool
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        // Antes de deletar o cliente, considere o que fazer com seus pedidos e carrinhos
        // Ex: Deletar o carrinho, manter pedidos mas anonimizar o clientId, etc.

        $result = $this->clientRepository->delete($clientId);
        if ($result) {
            $this->logService->log('Client', 'Client account deleted', $clientId);
        }
        return $result;
    }

    /**
     * Gera um comprovante de compra em PDF para uma ordem específica.
     * @param int $clientId O ID do cliente.
     * @param int $orderId O ID da ordem.
     * @return string O caminho para o arquivo PDF gerado ou o conteúdo do PDF.
     * @throws Exception Se o cliente ou a ordem não for encontrada, ou a ordem não pertencer ao cliente.
     */
    public function generateReceipt(int $clientId, int $orderId): string
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw new Exception("Pedido não encontrado.");
        }

        if ($order->getClientId() !== $clientId) {
            throw new Exception("O pedido não pertence a este cliente.");
        }

        // Assumindo que OrderService tem generateOrderReceiptPdf
        // E que ele retorna o conteúdo do PDF ou stream.
        return $this->orderService->generateOrderReceiptPdf($orderId, false); // Retorna o conteúdo como string
    }

    /**
     * Verifica se esta é a primeira compra do cliente.
     * @param int $clientId O ID do cliente.
     * @return bool True se for a primeira compra, false caso contrário.
     * @throws Exception Se o cliente não for encontrado.
     */
    public function isFirstPurchase(int $clientId): bool
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Cliente não encontrado.");
        }

        // Usar o método getOrdersByClientId da interface OrderRepositoryInterface
        $orders = $this->orderRepository->getOrdersByClientId($clientId);
        $isFirst = empty($orders);
        $this->logService->log('Client', 'Checked first purchase status', $clientId, ['isFirstPurchase' => $isFirst]);
        return $isFirst;
    }
}