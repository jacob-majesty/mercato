<?php

namespace App\Model;

use DateTime;
use InvalidArgumentException;

/**
 * Class Address
 * @package App\Model
 *
 * Representa um endereço no sistema.
 */
class Address
{
    private ?int $id;
    private string $street;
    private string $number;
    private ?string $complement;
    private string $neighborhood;
    private string $city;
    private string $state;
    private string $zipCode; // Propriedade em camelCase para PHP
    private string $country;
    private ?DateTime $createdAt; // Adicionado
    private ?DateTime $updatedAt; // Adicionado

    public function __construct(
        ?int $id = null, // ID agora é o primeiro argumento e opcional
        string $street,
        string $number,
        string $neighborhood, // Bairro antes do complemento
        string $city,
        string $state,
        string $zipCode, // Recebe em camelCase
        string $country,
        ?string $complement = null, // Complemento opcional, após os obrigatórios
        ?DateTime $createdAt = null, // Adicionado
        ?DateTime $updatedAt = null  // Adicionado
    ) {
        $this->id = $id;
        $this->street = $street;
        $this->number = $number;
        $this->neighborhood = $neighborhood; // Atribuição
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode; // Atribui à propriedade camelCase
        $this->country = $country;
        $this->complement = $complement; // Atribuição
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getStreet(): string { return $this->street; }
    public function getNumber(): string { return $this->number; }
    public function getComplement(): ?string { return $this->complement; }
    public function getNeighborhood(): string { return $this->neighborhood; }
    public function getCity(): string { return $this->city; }
    public function getState(): string { return $this->state; }
    public function getZipCode(): string { return $this->zipCode; } // Getter em camelCase
    public function getCountry(): string { return $this->country; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; } // Getter adicionado
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; } // Getter adicionado

    // Setters
    public function setId(?int $id): void { $this->id = $id; } // ID pode ser null
    public function setStreet(string $street): void { $this->street = $street; }
    public function setNumber(string $number): void { $this->number = $number; }
    public function setComplement(?string $complement): void { $this->complement = $complement; }
    public function setNeighborhood(string $neighborhood): void { $this->neighborhood = $neighborhood; }
    public function setCity(string $city): void { $this->city = $city; }
    public function setState(string $state): void { $this->state = $state; }
    public function setZipCode(string $zipCode): void { $this->zipCode = $zipCode; } // Setter em camelCase
    public function setCountry(string $country): void { $this->country = $country; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; } // Setter adicionado
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; } // Setter adicionado
}
