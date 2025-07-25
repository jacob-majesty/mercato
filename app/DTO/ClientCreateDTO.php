<?php

namespace App\DTO;

/**
 * Class ClientCreateDTO
 * @package App\DTO
 *
 * DTO para encapsular os dados de entrada necessários para a criação de um novo cliente (registro).
 */
class ClientCreateDTO
{
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $password; // Senha em texto puro, será hashed no Service

    public function __construct(array $data)
    {
        $this->email = $data['email'] ?? '';
        $this->firstName = $data['firstName'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->password = $data['password'] ?? '';
    }
}