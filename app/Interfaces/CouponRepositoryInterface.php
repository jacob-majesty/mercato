<?php

namespace App\Repository;

use App\Model\Coupon;

interface CouponRepositoryInterface
{
    /**
     * Encontra um cupom pelo seu ID.
     * @param int $id
     * @return Coupon|null
     */
    public function findById(int $id): ?Coupon;

    /**
     * Encontra um cupom pelo seu código.
     * @param string $code
     * @return Coupon|null
     */
    public function findByCode(string $code): ?Coupon;

    /**
     * Retorna todos os cupons.
     * @return Coupon[]
     */
    public function findAll(): array;

    /**
     * Salva um novo cupom ou atualiza um existente.
     * @param Coupon $coupon
     * @return Coupon
     */
    public function save(Coupon $coupon): Coupon;

    /**
     * Atualiza um cupom existente no banco de dados.
     * @param Coupon $coupon
     * @return bool
     */
    public function update(Coupon $coupon): bool;

    /**
     * Deleta um cupom pelo seu ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}