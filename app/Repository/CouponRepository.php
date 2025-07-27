<?php

namespace App\Repository;

use App\Model\Coupon;
use PDO;
use DateTime;
use Exception;
use App\Interfaces\CouponRepositoryInterface;

class CouponRepository implements CouponRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Coupon
    {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $this->hydrateCoupon($data);
    }

    /**
     * @inheritDoc
     */
    public function findByCode(string $code): ?Coupon
    {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = :code");
        $stmt->execute([':code' => $code]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $this->hydrateCoupon($data);
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM coupons");
        $coupons = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $coupons[] = $this->hydrateCoupon($data);
        }
        return $coupons;
    }

    /**
     * @inheritDoc
     */
    public function save(Coupon $coupon): Coupon
    {
        $sql = "INSERT INTO coupons (code, discount, type, expirationDate, minCartValue, isActive) VALUES (:code, :discount, :type, :expirationDate, :minCartValue, :isActive)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':code', $coupon->getCode());
        $stmt->bindValue(':discount', $coupon->getDiscount());
        $stmt->bindValue(':type', $coupon->getType());
        $stmt->bindValue(':expirationDate', $coupon->getExpirationDate() ? $coupon->getExpirationDate()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':minCartValue', $coupon->getMinCartValue());
        $stmt->bindValue(':isActive', $coupon->isActive(), PDO::PARAM_BOOL);

        $stmt->execute();
        $coupon->setId((int)$this->pdo->lastInsertId());
        return $coupon;
    }

    /**
     * @inheritDoc
     */
    public function update(Coupon $coupon): bool
    {
        $sql = "UPDATE coupons SET code = :code, discount = :discount, type = :type, expirationDate = :expirationDate, minCartValue = :minCartValue, isActive = :isActive WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':code', $coupon->getCode());
        $stmt->bindValue(':discount', $coupon->getDiscount());
        $stmt->bindValue(':type', $coupon->getType());
        $stmt->bindValue(':expirationDate', $coupon->getExpirationDate() ? $coupon->getExpirationDate()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':minCartValue', $coupon->getMinCartValue());
        $stmt->bindValue(':isActive', $coupon->isActive(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $coupon->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM coupons WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Hidrata um objeto Coupon a partir de um array de dados do banco de dados.
     * @param array $data
     * @return Coupon
     */
    private function hydrateCoupon(array $data): Coupon
    {
            return new Coupon(
            $data['code'],
            (float)$data['discount'],
            $data['type'],
            (bool)$data['isActive'],
            $data['id'],
            $data['expirationDate'] ? new DateTime($data['expirationDate']) : null,
            $data['minCartValue'] ? (float)$data['minCartValue'] : null
        );
    }
}