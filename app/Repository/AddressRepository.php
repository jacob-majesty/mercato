<?php

namespace App\Repository;

use PDO;
use App\Model\Address;
use DateTime;
use Exception;

/**
 * Class AddressRepository
 * @package App\Repository
 *
 * Repositório para a entidade Address.
 */
class AddressRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Address
    {
        $stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToAddress($data);
    }

    public function save(Address $address): Address
    {
        // SQL para inserir um novo endereço. Inclui created_at e updated_at.
        $sql = "INSERT INTO addresses (street, number, complement, neighborhood, city, state, zip_code, country, created_at, updated_at) VALUES (:street, :number, :complement, :neighborhood, :city, :state, :zip_code, :country, :created_at, :updated_at)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $currentTimestamp = (new DateTime())->format('Y-m-d H:i:s');
            $stmt->execute([
                'street' => $address->getStreet(),
                'number' => $address->getNumber(),
                'complement' => $address->getComplement(),
                'neighborhood' => $address->getNeighborhood(),
                'city' => $address->getCity(),
                'state' => $address->getState(),
                'zip_code' => $address->getZipCode(),
                'country' => $address->getCountry(),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp
            ]);
        } catch (Exception $e) {
            error_log("AddressRepository: Erro ao salvar endereço: " . $e->getMessage());
            throw $e;
        }

        $address->setId((int)$this->pdo->lastInsertId());
        return $address;
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto Address.
     * Este método é público para ser reutilizado por outros repositórios (ex: OrderRepository).
     * @param array $data
     * @return Address
     */
    public function mapToAddress(array $data): Address
    {
        // Mapeia os dados do banco de dados para o objeto Address.
        // Garante que 'created_at' e 'updated_at' sejam tratados como DateTime.
        return new Address(
            (int)$data['id'],
            $data['street'],
            $data['number'],
            $data['complement'],
            $data['neighborhood'],
            $data['city'],
            $data['state'],
            $data['zip_code'],
            $data['country'],
            new DateTime($data['created_at']),
            isset($data['updated_at']) ? new DateTime($data['updated_at']) : null
        );
    }
}
