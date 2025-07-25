<?php

namespace App\DTO;

/**
 * Class UserDTO
 * @package App\DTO
 *
 * Data Transfer Object para dados de usuário.
 * Usado para encapsular dados de entrada (do formulário/requisição)
 * ou dados de saída (para a view).
 */
class UserDTO
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $role = null;
    public ?string $password = null; // Senha em texto puro, apenas para criação/login
    public ?string $newPassword = null; // Para mudança de senha
    public ?string $confirmPassword = null; // Para confirmação de senha

    /**
     * Construtor para popular o DTO.
     * @param array $data Array associativo de dados.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->firstName = $data['firstName'] ?? null;
        $this->lastName = $data['lastName'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->password = $data['password'] ?? null; // For raw password input
        $this->newPassword = $data['newPassword'] ?? null;
        $this->confirmPassword = $data['confirmPassword'] ?? null;
    }

    // Você pode adicionar métodos de validação básicos aqui,
    // mas a validação de regras de negócio mais complexas deve estar no Service.
}