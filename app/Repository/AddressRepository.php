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

    public function save(Address $address): bool
    {
        // Corrigido: Incluindo 'client_id' e 'neighborhood' na query
        $sql = "INSERT INTO addresses (
                    client_id, street, number, complement, neighborhood, city, state, zip_code, country
                ) VALUES (
                    :clientId, :street, :number, :complement, :neighborhood, :city, :state, :zipCode, :country
                )";
        
        $stmt = $this->pdo->prepare($sql);

        // Corrigido: Bind dos novos parâmetros
        $stmt->bindValue(':clientId', $address->getClientId());
        $stmt->bindValue(':street', $address->getStreet());
        $stmt->bindValue(':number', $address->getNumber());
        $stmt->bindValue(':complement', $address->getComplement());
        $stmt->bindValue(':neighborhood', $address->getNeighborhood());
        $stmt->bindValue(':city', $address->getCity());
        $stmt->bindValue(':state', $address->getState());
        $stmt->bindValue(':zipCode', $address->getZipCode());
        $stmt->bindValue(':country', $address->getCountry());

        return $stmt->execute();
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto Address.
     * Este método é público para ser reutilizado por outros repositórios (ex: OrderRepository).
     * @param array $data
     * @return Address
     */
    
    private function mapToAddress(array $data): Address
{
    // Corrigido: A ordem dos argumentos no construtor da classe Address é crucial.
    // A função agora reflete a nova assinatura do construtor:
    // __construct(int $id, int $clientId, string $street, int $number, string $complement, string $neighborhood, string $city, string $state, string $zipCode, string $country, string $recipient)
    
    return new Address(
        (int)$data['id'],
        (int)$data['client_id'], // Adicionado o client_id
        $data['street'] ?? '',
        (int)($data['number'] ?? 0),
        $data['complement'] ?? null,
        $data['neighborhood'] ?? '', // Adicionado o neighborhood
        $data['city'] ?? '',
        $data['state'] ?? '',
        $data['zip_code'] ?? '',
        $data['country'] ?? '',
        $data['recipient'] ?? '' //Adicionado o recipient
    );
}
}
