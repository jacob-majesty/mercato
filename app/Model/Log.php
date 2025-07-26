<?php

namespace App\Model;

use DateTime;

/**
 * Class Log
 * @package App\Model
 *
 * Representa uma entrada de log no sistema.
 */
class Log
{
    private ?int $id;
    private string $type; // Corrigido: 'type' (não 'tipo')
    private string $action;
    private ?int $userId;
    private ?string $details; // JSON string
    private DateTime $timestamp;

    public function __construct(
        string $type, // Corrigido: 'type'
        string $action,
        ?int $userId = null,
        ?string $details = null,
        ?int $id = null,
        ?DateTime $timestamp = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->action = $action;
        $this->userId = $userId;
        $this->details = $details;
        $this->timestamp = $timestamp ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string // Corrigido: 'getType'
    {
        return $this->type;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    // Setters (se necessário, para casos de atualização ou hidratação)
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setType(string $type): void // Corrigido: 'setType'
    {
        $this->type = $type;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}
