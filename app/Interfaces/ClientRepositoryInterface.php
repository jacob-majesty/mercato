<?php

namespace App\Interfaces;

use App\Model\Client;

/**
 * Interface ClientRepositoryInterface
 * @package App\Repository
 *
 * Define o contrato para operações de persistência relacionadas ao modelo Client.
 */
interface ClientRepositoryInterface
{
    public function save(Client $client): Client;
    public function findById(int $id): ?Client;
    public function update(Client $client): bool;
    public function delete(int $id): bool;
    public function findByEmail(string $email): ?Client; // Útil para login ou verificação de email existente
}