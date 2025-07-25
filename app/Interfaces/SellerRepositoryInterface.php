<?php

namespace App\Repository;

use App\Model\Seller;

/**
 * Interface SellerRepositoryInterface
 * @package App\Repository
 *
 * Define o contrato para operações de persistência relacionadas ao modelo Seller.
 */
interface SellerRepositoryInterface
{
    /**
     * Salva um novo vendedor no banco de dados.
     * @param Seller $seller O objeto Seller a ser salvo.
     * @return Seller O objeto Seller com o ID atribuído após a inserção.
     */
    public function save(Seller $seller): Seller;

    /**
     * Busca um vendedor pelo seu ID.
     * @param int $id O ID do vendedor.
     * @return Seller|null O objeto Seller se encontrado, ou null caso contrário.
     */
    public function findById(int $id): ?Seller;

    /**
     * Atualiza os dados de um vendedor existente.
     * @param Seller $seller O objeto Seller com os dados atualizados.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     */
    public function update(Seller $seller): bool;

    /**
     * Deleta um vendedor pelo seu ID.
     * @param int $id O ID do vendedor a ser deletado.
     * @return bool True se a exclusão for bem-sucedida, false caso contrário.
     */
    public function delete(int $id): bool;

    /**
     * Busca todos os vendedores.
     * @return Seller[] Uma array de objetos Seller.
     */
    public function findAll(): array;

    /**
     * Busca um vendedor pelo seu email.
     * @param string $email O email do vendedor.
     * @return Seller|null O objeto Seller se encontrado, ou null caso contrário.
     */
    public function findByEmail(string $email): ?Seller;
}