<?php

namespace App\Model;

use DateTime;
use InvalidArgumentException;

/**
 * Class User
 * @package App\Model
 *
 * Representa um usuário genérico no sistema.
 * Contém atributos e métodos comuns a todas as funções de usuário (Admin, Seller, Client).
 */
class User
{
    protected ?int $id;
    protected string $email;
    protected string $firstName;
    protected string $lastName;
    protected string $pswd; // Armazena a senha hashada
    protected string $role; // Ex: 'client', 'seller', 'admin'
    protected ?DateTime $createdAt;
    protected ?DateTime $updatedAt; // Adicionado o campo updatedAt para consistência

    /**
     * Construtor da classe User.
     *
     * @param string $email O email do usuário.
     * @param string $firstName O primeiro nome do usuário.
     * @param string $lastName O sobrenome do usuário.
     * @param string $pswd A senha (já criptografada) do usuário.
     * @param string $role O papel do usuário (ex: 'admin', 'seller', 'client').
     * @param int|null $id O ID do usuário (opcional, para usuários existentes).
     * @param DateTime|null $createdAt A data de criação do usuário (opcional, para usuários existentes).
     * @param DateTime|null $updatedAt A data da última atualização do usuário (opcional).
     */
    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $pswd, // Senha já hashada
        string $role,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null // Adicionado aqui
    ) {
        // Validações básicas
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Formato de email inválido.");
        }
        if (empty($firstName) || empty($lastName) || empty($pswd) || empty($role)) {
            throw new InvalidArgumentException("Dados do usuário incompletos.");
        }

        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->pswd = $pswd;
        $this->role = $role;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt; // Atribuído
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPswd(): string
    {
        return $this->pswd;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    // Setters (para permitir atualização de propriedades)
    public function setId(int $id): void
    {
        // Geralmente, o ID só é setado uma vez (após a inserção no DB)
        if ($this->id === null) {
            $this->id = $id;
        } else {
            // Em vez de LogicException, pode-se apenas ignorar ou logar um aviso
            // Se o ID já está definido, não deve ser alterado.
            // throw new \LogicException("ID já definido e não pode ser alterado.");
            error_log("Warning: Attempted to change ID of an already set User object.");
        }
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Formato de email inválido.");
        }
        $this->email = $email;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setPswd(string $pswd): void
    {
        $this->pswd = $pswd;
    }

    public function setRole(string $role): void
    {
        // Você pode adicionar validação de papel aqui (e.g., se o papel é um ENUM válido)
        $this->role = $role;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
