<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Service\ProductService;
use App\Repository\ProductRepository;
use App\Config\Database;

class ProductController
{
    private ProductService $productService;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $productRepository = new ProductRepository($pdo);
        $this->productService = new ProductService($productRepository);
    }

    public function index(Request $request): Response
    {
        // Lógica de paginação para a lista de todos os produtos
        $currentPage = $request->get('page', 1);
        $itemsPerPage = 12; // Ou um valor diferente para a página de listagem geral

        try {
            $productsData = $this->productService->getAvailableProductsPaginated($currentPage, $itemsPerPage);

            return Response::view('products/index', [
                'products' => $productsData['products'],
                'currentPage' => $productsData['currentPage'],
                'totalPages' => $productsData['totalPages'],
                'placeholderImagePath' => '/products/placeholder.png' // Passar também para a view de produtos
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao listar produtos: " . $e->getMessage());
            return Response::view('errors/500', ['message' => 'Não foi possível carregar a lista de produtos.'], 500);
        }
    }

    public function show(Request $request): Response
    {
        // O ID do produto vem dos parâmetros da rota (ex: /products/123)
        $productId = $request->getRouteParam(0); // O primeiro parâmetro da rota

        if (!$productId) {
            return Response::view('errors/404', ['message' => 'Produto não especificado.'], 404);
        }

        try {
            $product = $this->productService->getProductById((int) $productId);

            if (!$product) {
                return Response::view('errors/404', ['message' => 'Produto não encontrado.'], 404);
            }

            return Response::view('products/show', ['product' => $product, 'placeholderImagePath' => '/products/placeholder.png']);
        } catch (\Exception $e) {
            error_log("Erro ao exibir produto: " . $e->getMessage());
            return Response::view('errors/500', ['message' => 'Ocorreu um erro ao carregar os detalhes do produto.'], 500);
        }
    }
}