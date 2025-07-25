<?php

namespace App\Repository;

use App\Model\User;
use App\Model\Admin;
use App\Model\Seller;
use App\Model\Client;
use PDO; // Para interação com o banco de dados
use DateTime; // Para manipular datas
use App\Repository\UserRepositoryInterface; // Importa a interface

/**
 * Class UserRepository
 * @package App\Repository
 *
 * Responsável por todas as operações de persistência de dados
 * para a entidade User no banco de dados.
 */
class UserRepository implements UserRepositoryInterface // Implementa a interface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Converte um array de dados do DB em um objeto User (ou subclasse).
     * @param array $userData Dados do usuário do banco de dados.
     * @return User|null
     */
    private function mapUser(array $userData): ?User
    {
        if (empty($userData)) {
            return null;
        }

        $id = $userData['id_usuario'];
        $email = $userData['email'];
        $firstName = $userData['primeiro_nome'];
        $lastName = $userData['ultimo_nome'];
        $role = $userData['papel'];
        $pswd = $userData['senha'];
        // Garante que 'criado_em' exista antes de tentar usar, ou defina um valor padrão
        $createdAt = isset($userData['criado_em']) ? new DateTime($userData['criado_em']) : new DateTime();

        switch ($role) {
            case 'admin':
                return new Admin($email, $firstName, $lastName, $pswd, $id, $createdAt);
            case 'seller':
                return new Seller($email, $firstName, $lastName, $pswd, $id, $createdAt);
            case 'client':
                return new Client($email, $firstName, $lastName, $pswd, $id, $createdAt);
            default:
                // Lidar com um papel desconhecido, talvez lançar uma exceção ou retornar User base
                return new User($email, $firstName, $lastName, $role, $pswd, $id, $createdAt);
        }
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->mapUser($userData);
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->mapUser($userData);
    }

    /**
     * @inheritDoc
     */
    public function save(User $user): User
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (email, primeiro_nome, ultimo_nome, papel, senha, criado_em)
             VALUES (:email, :firstName, :lastName, :role, :pswd, :createdAt)"
        );
        $result = $stmt->execute([
            ':email' => $user->getEmail(),
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':role' => $user->getRole(),
            ':pswd' => $user->getPswd(),
            ':createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        if ($result) {
            // O ideal é que o Model User tenha um setter para o ID
            // ou que o construtor aceite o ID como opcional
            // Para User: public function __construct($email, $firstName, $lastName, $role, $pswd, $id = null, $createdAt = null)
            // Para as subclasses: public function __construct($email, $firstName, $lastName, $pswd, $id = null, $createdAt = null)
            // Se o ID for null no construtor do model, o método setId() será necessário para popula-lo
            // Ou o UserRepository deve retornar uma nova instância do User com o ID.
            // Para o exemplo, vamos assumir que o ID é 'setado' internamente ou que uma nova instância é criada.
            $newId = (int)$this->pdo->lastInsertId();
            // A forma mais limpa é o Model ter um setId protegido e o Repository acessá-lo via Reflection
            // ou o Model ter um método "hydrateId"
            // Por simplicidade, vou criar uma nova instância aqui (menos eficiente mas funciona)
            return $this->findById($newId); // Busca o usuário recém-criado com o ID
        }

        throw new \PDOException("Erro ao salvar o usuário no banco de dados.");
    }

    /**
     * @inheritDoc
     */
    public function update(User $user): bool
    {
        if ($user->getId() === null) {
            throw new \InvalidArgumentException("Não é possível atualizar um usuário sem ID.");
        }

        $stmt = $this->pdo->prepare(
            "UPDATE usuarios SET email = :email, primeiro_nome = :firstName,
             ultimo_nome = :lastName, senha = :pswd WHERE id_usuario = :id"
        );
        return $stmt->execute([
            ':email' => $user->getEmail(),
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':pswd' => $user->getPswd(),
            ':id' => $user->getId()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM usuarios");
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($usersData as $userData) {
            $user = $this->mapUser($userData);
            if ($user) {
                $users[] = $user;
            }
        }
        return $users;
    }
}