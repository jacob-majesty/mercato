<?php

namespace App\Repository;

use App\Model\Client;
use PDO;
use DateTime; // Para lidar com as datas de criação

/**
 * Class ClientRepository
 * @package App\Repository
 *
 * Implementação do ClientRepositoryInterface para persistência de dados de clientes no banco de dados.
 */
class ClientRepository implements ClientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Client $client): Client
    {
        $sql = "INSERT INTO users (email, first_name, last_name, role, pswd, created_at) VALUES (:email, :firstName, :lastName, :role, :password, :createdAt)";
        $stmt = $this->pdo->prepare($sql);

        $role = 'client'; // Garante que a role seja 'client'

        $stmt->bindValue(':email', $client->getEmail());
        $stmt->bindValue(':firstName', $client->getFirstName());
        $stmt->bindValue(':lastName', $client->getLastName());
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':password', $client->getPswd()); // Assumindo que a senha já está hashed no modelo
        $stmt->bindValue(':createdAt', $client->getCreatedAt()->format('Y-m-d H:i:s'));

        if ($stmt->execute()) {
            $client->setId((int)$this->pdo->lastInsertId());
            return $client;
        }
        throw new \Exception("Erro ao salvar cliente.");
    }

    public function findById(int $id): ?Client
    {
        $sql = "SELECT id, email, first_name, last_name, role, pswd, created_at FROM users WHERE id = :id AND role = 'client'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return new Client(
                $userData['id'],
                $userData['email'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['pswd'],
                new DateTime($userData['created_at'])
            );
        }
        return null;
    }

    public function findByEmail(string $email): ?Client
    {
        $sql = "SELECT id, email, first_name, last_name, role, pswd, created_at FROM users WHERE email = :email AND role = 'client'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return new Client(
                $userData['id'],
                $userData['email'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['pswd'],
                new DateTime($userData['created_at'])
            );
        }
        return null;
    }

    public function update(Client $client): bool
    {
        $sql = "UPDATE users SET email = :email, first_name = :firstName, last_name = :lastName, pswd = :password WHERE id = :id AND role = 'client'";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':email', $client->getEmail());
        $stmt->bindValue(':firstName', $client->getFirstName());
        $stmt->bindValue(':lastName', $client->getLastName());
        $stmt->bindValue(':password', $client->getPswd()); // A senha deve ser hashed antes de ser setada no modelo
        $stmt->bindValue(':id', $client->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id AND role = 'client'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}