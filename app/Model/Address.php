<?php

namespace App\Model;

/**
 * Class Address
 * @package App\Model
 *
 * Representa um endereço de entrega ou faturamento.
 */
class Address
{
    private ?int $id;
    private string $street;
    private string $number;
    private ?string $complement; // Opcional
    private string $neighborhood;
    private string $city;
    private string $state;
    private string $zipCode;
    private string $country;

    /**
     * Construtor da classe Address.
     *
     * @param string $street A rua.
     * @param string $number O número.
     * @param string $neighborhood O bairro.
     * @param string $city A cidade.
     * @param string $state O estado.
     * @param string $zipCode O CEP.
     * @param string $country O país.
     * @param int|null $id O ID do endereço (opcional).
     * @param string|null $complement O complemento (opcional).
     */
    public function __construct(
        string $street,
        string $number,
        string $neighborhood,
        string $city,
        string $state,
        string $zipCode,
        string $country,
        ?int $id = null,
        ?string $complement = null
    ) {
        $this->id = $id;
        $this->street = $street;
        $this->number = $number;
        $this->complement = $complement;
        $this->neighborhood = $neighborhood;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->country = $country;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    // Setters (se o endereço puder ser editado)
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function setComplement(?string $complement): void
    {
        $this->complement = $complement;
    }

    public function setNeighborhood(string $neighborhood): void
    {
        $this->neighborhood = $neighborhood;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}