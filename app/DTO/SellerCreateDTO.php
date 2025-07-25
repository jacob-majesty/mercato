<?php

namespace App\DTO;

/**
 * Class SellerCreateDTO
 * @package App\DTO
 *
 * Data Transfer Object para a criação de um novo vendedor.
 */
class SellerCreateDTO
{
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $password; // Senha em texto puro para ser hashed no serviço/repositório

    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $password
    ) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
    }
}