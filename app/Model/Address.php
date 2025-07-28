<?php

namespace App\Model;

class Address {
    private int $id;
    private int $clientId; // Adicionado para manter a relação com o cliente
    private string $street;
    private int $number;
    private string $complement;
    private string $neighborhood; // Adicionado com base na tabela SQL
    private string $city;
    private string $state;
    private string $zipCode;
    private string $country;
    private string $recipient; // Adicionado, pois é comum ter um nome para quem recebe o pedido

    // Construtor com 11 argumentos para corresponder à nova estrutura
    public function __construct(
        int $id,
        int $clientId,
        string $street,
        int $number,
        string $complement,
        string $neighborhood,
        string $city,
        string $state,
        string $zipCode,
        string $country,
        string $recipient
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->street = $street;
        $this->number = $number;
        $this->complement = $complement;
        $this->neighborhood = $neighborhood;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->recipient = $recipient;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getClientId(): int {
        return $this->clientId;
    }

    public function getStreet(): string {
        return $this->street;
    }

    public function getNumber(): int {
        return $this->number;
    }

    public function getComplement(): string {
        return $this->complement;
    }
    
    public function getNeighborhood(): string {
        return $this->neighborhood;
    }

    public function getCity(): string {
        return $this->city;
    }

    public function getState(): string {
        return $this->state;
    }

    public function getZipCode(): string {
        return $this->zipCode;
    }

    public function getCountry(): string {
        return $this->country;
    }

    public function getRecipient(): string {
        return $this->recipient;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setClientId(int $clientId): void {
        $this->clientId = $clientId;
    }
    
    // ... outros setters, se necessário
}