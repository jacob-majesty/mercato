<?php

namespace App\DTO;

use InvalidArgumentException;

/**
 * Class UserUpdateDTO
 * @package App\DTO
 *
 * DTO para atualização de um usuário existente.
 * Permite a atualização parcial de campos.
 */
class UserUpdateDTO
{
    public ?int $id; // O ID do usuário é obrigatório para identificar qual usuário será atualizado
    public ?string $email;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $role;
    public ?string $password; // Senha em texto puro, será hashed no serviço

    /**
     * Construtor do DTO.
     * @param array $data Array associativo contendo os dados do usuário a serem atualizados.
     * Campos podem ser null se não forem ser atualizados.
     * @throws InvalidArgumentException Se o ID do usuário estiver faltando.
     */
    public function __construct(array $data)
    {
        if (!isset($data['id'])) {
            throw new InvalidArgumentException("ID do usuário é obrigatório para atualização.");
        }
        $this->id = (int) $data['id'];

        // Atribui os valores, permitindo que sejam null se não fornecidos no array $data
        $this->email = $data['email'] ?? null;
        $this->firstName = $data['firstName'] ?? null;
        $this->lastName = $data['lastName'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->password = $data['password'] ?? null; // A validação de senha (e.g., complexidade) deve ser feita no serviço ou em um validador.

        // Opcional: Adicionar validações de formato aqui (ex: email válido, papel válido)
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Formato de email inválido.");
        }
        // Exemplo de validação para o papel (role)
        $validRoles = ['admin', 'seller', 'client'];
        if ($this->role !== null && !in_array($this->role, $validRoles)) {
            throw new InvalidArgumentException("Papel de usuário inválido.");
        }
    }
}
