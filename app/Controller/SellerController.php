<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\Authenticator;
use App\Service\ProductService;
use App\Service\OrderService; // Para ver as vendas do vendedor
use App\Service\LogService;
use App\Repository\ProductRepository;

use App\Config\Database;
use App\DTO\ProductCreateDTO; // Para criar produtos
use App\DTO\ProductUpdateDTO; // Para atualizar produtos

class SellerController
{
    private ProductService $productService;
    private OrderService $orderService;
    private LogService $logService;
   
    public function __construct(
        ProductService $productService,
        OrderService $orderService,
        LogService $logService
    ) {
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->logService = $logService;
    }

    public function dashboard(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::redirect('/login'); // Middleware deve pegar isso, mas é um fallback
        }

        try {
            // Exemplo: Obter produtos e vendas do vendedor
            $myProducts = $this->productService->getProductsBySellerId($sellerId);
            // Implementar método getSalesBySellerId no OrderService se necessário
            // Por enquanto, apenas os logs do vendedor.
            $sellerLogs = $this->logService->getUserLogs($sellerId); // Assumindo que logs por userId funcionam para sellers

            $this->logService->log('Seller', 'Accessed dashboard', $sellerId);
            return Response::view('seller/dashboard', [
                'products' => $myProducts,
                'logs' => $sellerLogs,
                'sellerId' => $sellerId
            ]);
        } catch (\Exception $e) {
            $this->logService->log('Seller', 'Error accessing dashboard', $sellerId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar o dashboard do vendedor: ' . $e->getMessage()], 500);
        }
    }

    public function listProducts(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::redirect('/login');
        }

        try {
            $products = $this->productService->getProductsBySellerId($sellerId);
            $this->logService->log('Seller', 'Viewed own products list', $sellerId);
            return Response::view('seller/products/index', ['products' => $products]);
        } catch (\Exception $e) {
            $this->logService->log('Seller', 'Error listing own products', $sellerId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar sua lista de produtos: ' . $e->getMessage()], 500);
        }
    }

    public function createProductForm(Request $request): Response
    {
        Authenticator::getUserId(); // Apenas para garantir que está logado
        return Response::view('seller/products/create');
    }

    public function storeProduct(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $data = $request->all();

        // Crie o DTO
        $productDTO = new ProductCreateDTO(
        $data['name'] ?? '',
        $data['description'] ?? '',
        (float)($data['price'] ?? 0),
        $data['category'] ?? '',
        $data['image_url'] ?? null,
        (int)($data['stock'] ?? 0),
        $sellerId
        );

        try {
            $newProduct = $this->productService->createProduct($productDTO);
            $this->logService->log('Product', 'Product created', $sellerId, ['productId' => $newProduct->getId(), 'productName' => $newProduct->getName()]);
            return Response::json(['success' => true, 'message' => 'Produto criado com sucesso!', 'product_id' => $newProduct->getId()], 201);
        } catch (\InvalidArgumentException $e) {
            $this->logService->log('Product', 'Product creation failed: Invalid data', $sellerId, ['error' => $e->getMessage(), 'data' => $data]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logService->log('Product', 'Product creation error', $sellerId, ['error' => $e->getMessage(), 'data' => $data]);
            return Response::json(['success' => false, 'message' => 'Erro ao criar produto: ' . $e->getMessage()], 500);
        }
    }

    public function editProductForm(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::redirect('/login');
        }

        $productId = (int) $request->getRouteParam(0);
        if (!$productId) {
            return Response::view('errors/400', ['message' => 'ID do produto não especificado.'], 400);
        }

        try {
            $product = $this->productService->getProductById($productId);

            if (!$product || $product->getSellerId() !== $sellerId) {
                // Garante que o vendedor só edita seus próprios produtos
                return Response::view('errors/403', ['message' => 'Você não tem permissão para editar este produto.'], 403);
            }

            return Response::view('seller/products/edit', ['product' => $product]);
        } catch (\Exception $e) {
            $this->logService->log('Product', 'Error loading product for edit', $sellerId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar o produto para edição: ' . $e->getMessage()], 500);
        }
    }

    public function updateProduct(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $productId = (int) $request->getRouteParam(0);
        if (!$productId) {
            return Response::json(['success' => false, 'message' => 'ID do produto não especificado.'], 400);
        }

        $data = $request->all(); // Obtenha todos os dados da requisição

        // Crie o DTO de atualização.
        // O ProductUpdateDTO deve ser construído com os dados que podem ser atualizados.
        $productDTO = new ProductUpdateDTO(
        $data['name'] ?? null,
        $data['description'] ?? null,
        isset($data['price']) ? (float)$data['price'] : null,
        $data['category'] ?? null,
        $data['image_url'] ?? null,
        isset($data['stock']) ? (int)$data['stock'] : null
       
        );

        try {
            // Verifique se o produto pertence ao vendedor antes de tentar atualizar
            $product = $this->productService->getProductById($productId);
            if (!$product || $product->getSellerId() !== $sellerId) {
                return Response::json(['success' => false, 'message' => 'Você não tem permissão para atualizar este produto.'], 403);
            }

            $success = $this->productService->updateProduct($productId, $productDTO);
            if ($success) {
                $this->logService->log('Product', 'Product updated', $sellerId, ['productId' => $productId]);
                return Response::json(['success' => true, 'message' => 'Produto atualizado com sucesso!'], 200);
            } else {
                $this->logService->log('Product', 'Product update failed', $sellerId, ['productId' => $productId, 'data' => $data]);
                return Response::json(['success' => false, 'message' => 'Falha ao atualizar o produto.'], 500);
            }
        } catch (\InvalidArgumentException $e) {
            $this->logService->log('Product', 'Product update failed: Invalid data', $sellerId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logService->log('Product', 'Product update error', $sellerId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao atualizar produto: ' . $e->getMessage()], 500);
        }
    }

    public function deleteProduct(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $productId = (int) $request->getRouteParam(0); // Assumindo /seller/products/{id}/delete
        if (!$productId) {
            return Response::json(['success' => false, 'message' => 'ID do produto não especificado.'], 400);
        }

        try {
            // Verifique se o produto pertence ao vendedor antes de tentar deletar
            $product = $this->productService->getProductById($productId);
            if (!$product || $product->getSellerId() !== $sellerId) {
                return Response::json(['success' => false, 'message' => 'Você não tem permissão para deletar este produto.'], 403);
            }

            $success = $this->productService->deleteProduct($productId);
            if ($success) {
                $this->logService->log('Product', 'Product deleted', $sellerId, ['productId' => $productId]);
                return Response::json(['success' => true, 'message' => 'Produto deletado com sucesso!'], 200);
            } else {
                $this->logService->log('Product', 'Product deletion failed', $sellerId, ['productId' => $productId]);
                return Response::json(['success' => false, 'message' => 'Falha ao deletar o produto.'], 500);
            }
        } catch (\Exception $e) {
            $this->logService->log('Product', 'Product deletion error', $sellerId, ['productId' => $productId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao deletar produto: ' . $e->getMessage()], 500);
        }
    }

    public function applyDiscount(Request $request): Response
    {
        $sellerId = Authenticator::getUserId();
        if (!$sellerId) {
            return Response::json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $productId = (int) $request->post('product_id');
        $discountPercent = (float) $request->post('discount_percent');

        if (!$productId || $discountPercent <= 0 || $discountPercent >= 1) { // Desconto entre 0 e 1
            return Response::json(['success' => false, 'message' => 'Dados de desconto inválidos.'], 400);
        }

        try {
            // Verifique se o produto pertence ao vendedor antes de aplicar o desconto
            $product = $this->productService->getProductById($productId);
            if (!$product || $product->getSellerId() !== $sellerId) {
                return Response::json(['success' => false, 'message' => 'Você não tem permissão para aplicar desconto neste produto.'], 403);
            }

            $success = $this->productService->applyDiscountToProduct($productId, $discountPercent);
            if ($success) {
                $this->logService->log('Product', 'Discount applied', $sellerId, ['productId' => $productId, 'discount' => $discountPercent]);
                return Response::json(['success' => true, 'message' => 'Desconto aplicado com sucesso!'], 200);
            } else {
                $this->logService->log('Product', 'Discount application failed', $sellerId, ['productId' => $productId, 'discount' => $discountPercent]);
                return Response::json(['success' => false, 'message' => 'Falha ao aplicar desconto.'], 500);
            }
        } catch (\Exception $e) {
            $this->logService->log('Product', 'Discount application error', $sellerId, ['productId' => $productId, 'discount' => $discountPercent, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao aplicar desconto: ' . $e->getMessage()], 500);
        }
    }
}