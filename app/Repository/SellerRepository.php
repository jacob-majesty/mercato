<?php

namespace App\Repository;

use App\Model\Seller;
use PDO;
use DateTime;
use Exception;
use App\Interfaces\SellerRepositoryInterface;

/**
 * Class SellerRepository
 * @package App\Repository
 *
 * Implementação do SellerRepositoryInterface para persistência de dados de vendedores no banco de dados.
 */
class SellerRepository implements SellerRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Seller $seller): Seller
    {
        // A senha deve ser hashed ANTES de salvar no DB.
        // Assumimos que a senha no objeto Seller já está hashed ou será hashed aqui.
        // Pelo DTO, ela vem em texto puro, então o Service ou o próprio save faria o hash.
        // Por simplicidade aqui, vamos hash a senha no save.
        $hashedPassword = password_hash($seller->getPswd(), PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (email, first_name, last_name, role, password, created_at) VALUES (:email, :firstName, :lastName, :role, :password, :createdAt)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':email', $seller->getEmail());
        $stmt->bindValue(':firstName', $seller->getFirstName());
        $stmt->bindValue(':lastName', $seller->getLastName());
        $stmt->bindValue(':role', 'seller'); // Força o papel como 'seller'
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':createdAt', $seller->getCreatedAt()->format('Y-m-d H:i:s'));

        if ($stmt->execute()) {
            $seller->setId((int)$this->pdo->lastInsertId());
            return $seller;
        }
        throw new Exception("Erro ao salvar o vendedor.");
    }

    public function findById(int $id): ?Seller
    {
        $sql = "SELECT id, email, first_name, last_name, role, password, created_at FROM users WHERE id = :id AND role = 'seller'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->hydrateSeller($userData);
    }

    public function update(Seller $seller): bool
    {
        $sql = "UPDATE users SET email = :email, first_name = :firstName, last_name = :lastName, password = :password WHERE id = :id AND role = 'seller'";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':email', $seller->getEmail());
        $stmt->bindValue(':firstName', $seller->getFirstName());
        $stmt->bindValue(':lastName', $seller->getLastName());
        // Se a senha não foi alterada, pode-se re-hashar a mesma senha ou ter lógica para não atualizar se não for fornecida.
        // Aqui, re-hashamos a senha que está no objeto Seller.
        $stmt->bindValue(':password', password_hash($seller->getPswd(), PASSWORD_BCRYPT));
        $stmt->bindValue(':id', $seller->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        // Ao deletar um vendedor, deve-se considerar o que acontece com seus produtos e vendas.
        // Por simplicidade, faremos apenas a exclusão do usuário.
        // Em um sistema real, isso exigiria lógica para órfãos ou exclusão em cascata.
        $sql = "DELETE FROM users WHERE id = :id AND role = 'seller'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT id, email, first_name, last_name, role, password, created_at FROM users WHERE role = 'seller'";
        $stmt = $this->pdo->query($sql);
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sellers = [];
        foreach ($usersData as $userData) {
            $sellers[] = $this->hydrateSeller($userData);
        }
        return $sellers;
    }

    public function findByEmail(string $email): ?Seller
    {
        $sql = "SELECT id, email, first_name, last_name, role, password, created_at FROM users WHERE email = :email AND role = 'seller'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->hydrateSeller($userData);
    }

    /**
     * Cria um objeto Seller a partir dos dados do banco de dados.
     * @param array $data Dados do usuário.
     * @return Seller
     */
    private function hydrateSeller(array $data): Seller
    {
        return new Seller(
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['password'], // A senha já deve estar hashed no DB
            $data['id'],
            new DateTime($data['created_at'])
        );
    }
}