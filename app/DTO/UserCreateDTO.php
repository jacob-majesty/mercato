<?php

namespace App\DTO;

use InvalidArgumentException;

/**
 * Class UserCreateDTO
 * @package App\DTO
 *
 * DTO para criação de um novo usuário.
 * Encapsula e valida os dados de entrada para a criação de qualquer tipo de usuário.
 */
class UserCreateDTO
{
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $password; // Senha em texto puro, será hashada no serviço
    public string $role; // 'client', 'seller', 'admin'

    /**
     * Construtor do DTO.
     * @param array $data Array associativo contendo os dados do usuário.
     * @throws InvalidArgumentException Se algum dado obrigatório estiver faltando ou for inválido.
     */
    public function __construct(array $data)
    {
        // Validação e atribuição dos dados
        if (empty($data['email'])) {
            throw new InvalidArgumentException("Email é obrigatório.");
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Formato de email inválido.");
        }
        $this->email = $data['email'];

        if (empty($data['firstName'])) {
            throw new InvalidArgumentException("Primeiro nome é obrigatório.");
        }
        $this->firstName = $data['firstName'];

        if (empty($data['lastName'])) {
            throw new InvalidArgumentException("Sobrenome é obrigatório.");
        }
        $this->lastName = $data['lastName'];

        if (empty($data['password'])) {
            throw new InvalidArgumentException("Senha é obrigatória.");
        }
        // Validações de complexidade de senha podem ser adicionadas aqui
        $this->password = $data['password'];

        if (empty($data['role'])) {
            throw new InvalidArgumentException("Papel do usuário é obrigatório.");
        }
        $validRoles = ['client', 'seller', 'admin'];
        if (!in_array($data['role'], $validRoles)) {
            throw new InvalidArgumentException("Papel de usuário inválido: " . $data['role']);
        }
        $this->role = $data['role'];
    }
}
