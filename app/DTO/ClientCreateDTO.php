<?php

namespace App\DTO;

use InvalidArgumentException;

/**
 * Class ClientCreateDTO
 * @package App\DTO
 *
 * DTO para criação de um novo cliente.
 * Encapsula e valida os dados de entrada do formulário de registro.
 */
class ClientCreateDTO
{
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $password;
    public string $confirmPassword; // Adicionada a propriedade confirmPassword

    /**
     * Construtor do DTO.
     * @param array $data Array associativo contendo os dados do cliente.
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
        // Você pode adicionar validações de complexidade de senha aqui (tamanho mínimo, caracteres especiais, etc.)
        $this->password = $data['password'];

        if (empty($data['confirmPassword'])) {
            throw new InvalidArgumentException("Confirmação de senha é obrigatória.");
        }
        $this->confirmPassword = $data['confirmPassword'];
    }
}
