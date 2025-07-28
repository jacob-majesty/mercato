<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Service\ProductService;
use App\Repository\ProductRepository;
use App\Config\Database; 

class HomeController
{
    private ProductService $productService;

    public function __construct()
    {
        // Instanciação de dependências: Idealmente, use um container de injeção de dependências
        // Por enquanto, instanciamos aqui, mas é repetitivo.
        $pdo = Database::getConnection();
        $productRepository = new ProductRepository($pdo);
        $this->productService = new ProductService($productRepository);
    }

    public function index(Request $request): Response
    {
        // Lógica de Paginação
        $currentPage = $request->get('page', 1);
        $itemsPerPage = 12;

        try {
            $productsData = $this->productService->getAvailableProductsPaginated($currentPage, $itemsPerPage);
            $products = $productsData['products'];
            $totalPages = $productsData['totalPages'];
            $currentPage = $productsData['currentPage']; // Garante que currentPage é o valor final ajustado

            // Caminho da imagem de placeholder (ajuste conforme sua estrutura pública)
            $placeholderImagePath = '/products/placeholder.png';

            return Response::view('home', [
                'products' => $products,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'placeholderImagePath' => $placeholderImagePath
            ]);

        } catch (\Exception $e) {
            error_log("Erro na Home Controller: " . $e->getMessage());
            return Response::view('errors/500', ['message' => 'Ocorreu um erro ao carregar os produtos.'], 500);
        }
    }
}