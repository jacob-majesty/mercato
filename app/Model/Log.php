<?php

namespace App\Model;

use DateTime;

/**
 * Class Log
 * @package App\Model
 *
 * Representa um registro de evento no sistema.
 */
class Log
{
    private ?int $id;
    private string $type; // Ex: 'INFO', 'WARNING', 'ERROR', 'PURCHASE', 'LOGIN'
    private ?int $userId; // ID do usuário associado ao log (opcional)
    private string $action; // Descrição da ação (ex: 'Produto Adicionado', 'Compra Finalizada')
    private DateTime $timestamp; // Data e hora do evento
    private ?string $details; // Detalhes adicionais em formato JSON ou texto (opcional)

    /**
     * Construtor da classe Log.
     *
     * @param string $type O tipo de log (INFO, WARNING, ERROR, etc.).
     * @param string $action A descrição da ação que gerou o log.
     * @param int|null $userId O ID do usuário associado ao log (nulo se não houver).
     * @param string|null $details Detalhes adicionais do log.
     * @param int|null $id O ID do log (nulo para novos logs).
     * @param DateTime|null $timestamp A data e hora do log (nulo para usar o tempo atual).
     */
    public function __construct(
        string $type,
        string $action,
        ?int $userId = null,
        ?string $details = null,
        ?int $id = null,
        ?DateTime $timestamp = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->userId = $userId;
        $this->action = $action;
        $this->timestamp = $timestamp ?? new DateTime();
        $this->details = $details;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }
}