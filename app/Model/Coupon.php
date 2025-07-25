<?php

namespace App\Model;

use DateTime;

/**
 * Class Coupon
 * @package App\Model
 *
 * Representa um código de desconto.
 */
class Coupon
{
    private ?int $id;
    private string $code;
    private float $discount; // Valor do desconto (ex: 0.10 para 10%, ou 10.00 para 10 reais)
    private string $type; // 'percentage' ou 'fixed'
    private ?DateTime $expirationDate; // Data de expiração (nulo se não expira)
    private ?float $minCartValue; // Valor mínimo do carrinho para aplicar o cupom (nulo se não houver)
    private bool $isActive; // Se o cupom está ativo ou desativado

    /**
     * Construtor da classe Coupon.
     *
     * @param string $code O código do cupom.
     * @param float $discount O valor ou percentual do desconto.
     * @param string $type O tipo de desconto ('percentage' ou 'fixed').
     * @param bool $isActive Se o cupom está ativo.
     * @param int|null $id O ID do cupom (opcional).
     * @param DateTime|null $expirationDate A data de expiração (opcional).
     * @param float|null $minCartValue O valor mínimo do carrinho para aplicação (opcional).
     */
    public function __construct(
        string $code,
        float $discount,
        string $type,
        bool $isActive,
        ?int $id = null,
        ?DateTime $expirationDate = null,
        ?float $minCartValue = null
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->discount = $discount;
        $this->type = $type;
        $this->expirationDate = $expirationDate;
        $this->minCartValue = $minCartValue;
        $this->isActive = $isActive;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExpirationDate(): ?DateTime
    {
        return $this->expirationDate;
    }

    public function getMinCartValue(): ?float
    {
        return $this->minCartValue;
    }

    public function isActive(): bool
    {
        return $this->isActive && !$this->isExpired();
    }

    // Setters (para atributos que podem ser alterados)
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

     public function setId(int $id): void 
    {
        $this->id = $id;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, ['percentage', 'fixed'])) {
            throw new \InvalidArgumentException("Tipo de cupom inválido.");
        }
        $this->type = $type;
    }

    public function setExpirationDate(?DateTime $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function setMinCartValue(?float $minCartValue): void
    {
        $this->minCartValue = $minCartValue;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * Verifica se o cupom está expirado.
     * @return bool True se expirado ou se não estiver ativo, false caso contrário.
     */
    public function isExpired(): bool
    {
        if (!$this->isActive) {
            return true; // Um cupom inativo é como se estivesse "expirado" para fins de uso.
        }
        return $this->expirationDate !== null && $this->expirationDate < new DateTime();
    }
}