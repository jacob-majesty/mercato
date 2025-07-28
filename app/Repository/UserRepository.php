<?php

namespace App\Repository;

use PDO;
use App\Model\User;
use DateTime;
use Exception;
use App\Interfaces\UserRepositoryInterface;

/**
 * Class UserRepository
 * @package App\Repository
 *
 * Implementação concreta de UserRepositoryInterface para MySQL.
 */
class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?User
    {
        error_log("UserRepository: findById - Buscando usuário com ID: " . $id);
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            error_log("UserRepository: findById - Usuário com ID {$id} não encontrado no DB.");
            return null;
        }

        error_log("UserRepository: findById - Dados brutos do DB para ID {$id}: " . json_encode($data));
        return $this->mapToUser($data);
    }

    public function findByEmail(string $email): ?User
    {
        error_log("UserRepository: findByEmail - Buscando usuário com email: " . $email);
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            error_log("UserRepository: findByEmail - Usuário com email {$email} NÃO ENCONTRADO no DB.");
            return null;
        }

        error_log("UserRepository: findByEmail - Dados brutos do DB para email {$email}: " . json_encode($data));
        return $this->mapToUser($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->mapToUser($data);
        }
        return $users;
    }

    public function save(User $user): User
    {
        $sql = "INSERT INTO users (email, first_name, last_name, pswd, role, created_at, updated_at) VALUES (:email, :first_name, :last_name, :pswd, :role, :created_at, :updated_at)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'pswd' => $user->getPswd(),
                'role' => $user->getRole(),
                'created_at' => $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i:s') : (new DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null
            ]);
        } catch (Exception $e) {
            error_log("UserRepository: Erro ao salvar usuário: " . $e->getMessage());
            throw $e;
        }

        $user->setId((int)$this->pdo->lastInsertId());
        return $user;
    }

    public function update(User $user): bool
    {
        $sql = "UPDATE users SET email = :email, first_name = :first_name, last_name = :last_name, pswd = :pswd, role = :role, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'pswd' => $user->getPswd(),
                'role' => $user->getRole(),
                'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("UserRepository: Erro ao atualizar usuário: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        try {
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("UserRepository: Erro ao deletar usuário: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto User.
     * @param array $data
     * @return User
     */
    private function mapToUser(array $data): User
    {
        error_log("UserRepository: mapToUser - Dados recebidos para mapeamento: " . json_encode($data));

        // Verificação e cast explícito de cada argumento
        $id = isset($data['id']) ? (int)$data['id'] : null;
        $email = $data['email'];
        $firstName = $data['first_name'];
        $lastName = $data['last_name'];
        $pswd = $data['pswd'];
        $role = $data['role'];
        $createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : null;
        $updatedAt = isset($data['updated_at']) ? new DateTime($data['updated_at']) : null;

        error_log("UserRepository: mapToUser - Tipos de dados após cast/verificação:");
        error_log("  id: " . gettype($id) . " - " . ($id ?? 'NULL'));
        error_log("  email: " . gettype($email) . " - " . $email);
        error_log("  firstName: " . gettype($firstName) . " - " . $firstName);
        error_log("  lastName: " . gettype($lastName) . " - " . $lastName);
        error_log("  pswd: " . gettype($pswd) . " - " . (is_string($pswd) ? substr($pswd, 0, 10) . '...' : ''));
        error_log("  role: " . gettype($role) . " - " . $role);
        error_log("  createdAt: " . gettype($createdAt) . " - " . ($createdAt ? $createdAt->format('Y-m-d H:i:s') : 'NULL'));
        error_log("  updatedAt: " . gettype($updatedAt) . " - " . ($updatedAt ? $updatedAt->format('Y-m-d H:i:s') : 'NULL'));

        // Assinatura do construtor de User:
        // public function __construct(string $email, string $firstName, string $lastName, string $pswd, string $role, ?int $id = null, ?DateTime $createdAt = null, ?DateTime $updatedAt = null)
        return new User(
            $email,
            $firstName,
            $lastName,
            $pswd,
            $role,
            $id,
            $createdAt,
            $updatedAt
        );
    }
}
