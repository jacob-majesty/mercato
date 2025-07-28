<?php

namespace App\Repository;

use PDO;
use App\Model\Client; // Certifique-se de que o namespace está correto
use App\Model\User; // Se Client estende User, pode ser necessário
use App\Interfaces\ClientRepositoryInterface; // Importa a interface
use DateTime;
use Exception;

/**
 * Class ClientRepository
 * @package App\Repository
 *
 * Implementação concreta de ClientRepositoryInterface para MySQL.
 */
class ClientRepository implements ClientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Client
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'client'");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToClient($data);
    }

    public function findByEmail(string $email): ?Client
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'client'");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToClient($data);
    }

    public function save(Client $client): Client
    {
        $sql = "INSERT INTO users (email, first_name, last_name, pswd, role, created_at, updated_at) VALUES (:email, :first_name, :last_name, :pswd, :role, :created_at, :updated_at)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'email' => $client->getEmail(),
            'first_name' => $client->getFirstName(),
            'last_name' => $client->getLastName(),
            'pswd' => $client->getPswd(), // Assumindo que o password já está hashado
            'role' => $client->getRole(),
            'created_at' => $client->getCreatedAt() ? $client->getCreatedAt()->format('Y-m-d H:i:s') : (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => $client->getUpdatedAt() ? $client->getUpdatedAt()->format('Y-m-d H:i:s') : null
        ]);

        $client->setId((int)$this->pdo->lastInsertId());
        return $client;
    }

    public function update(Client $client): bool
    {
        $sql = "UPDATE users SET email = :email, first_name = :first_name, last_name = :last_name, pswd = :pswd, role = :role, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $client->getId(),
            'email' => $client->getEmail(),
            'first_name' => $client->getFirstName(),
            'last_name' => $client->getLastName(),
            'pswd' => $client->getPswd(), // Usar getPswd()
            'role' => $client->getRole(),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'client'");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto Client.
     * @param array $data
     * @return Client
     */
    private function mapToClient(array $data): Client
    {
        //  Garante que 'id' é um int ou null, e que 'role' tem um fallback
        $id = isset($data['id']) ? (int)$data['id'] : null;
        $createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : null;
        $updatedAt = isset($data['updated_at']) ? new DateTime($data['updated_at']) : null;

        return new Client(
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['pswd'],
            $id, // Passa o ID como o quinto argumento (para o construtor de Client)
            $data['role'] ?? 'client', // Garante que role seja string, com fallback
            $createdAt,
            $updatedAt
        );
    }
}
