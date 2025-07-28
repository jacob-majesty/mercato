<?php

namespace App\Repository;

use App\Model\Address;
use PDO;
use Exception;

class AddressRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Salva um novo endereço no banco de dados.
     * @param Address $address
     * @return bool
     */
     /**
     * Salva um novo endereço no banco de dados.
     * Corrigido: O método agora retorna o objeto Address com o ID preenchido.
     * @param Address $address
     * @return Address
     */
    public function save(Address $address): Address
    {
        $sql = "INSERT INTO addresses (
                    client_id, street, number, complement, neighborhood, city, state, zip_code, country, recipient
                ) VALUES (
                    :clientId, :street, :number, :complement, :neighborhood, :city, :state, :zipCode, :country, :recipient
                )";
        
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':clientId', $address->getClientId());
        $stmt->bindValue(':street', $address->getStreet());
        $stmt->bindValue(':number', $address->getNumber());
        $stmt->bindValue(':complement', $address->getComplement());
        $stmt->bindValue(':neighborhood', $address->getNeighborhood());
        $stmt->bindValue(':city', $address->getCity());
        $stmt->bindValue(':state', $address->getState());
        $stmt->bindValue(':zipCode', $address->getZipCode());
        $stmt->bindValue(':country', $address->getCountry());
        $stmt->bindValue(':recipient', $address->getRecipient());

        $stmt->execute();
        
        // Corrigido: Obtém o ID do último registro inserido e o define no objeto Address.
        $address->setId((int) $this->pdo->lastInsertId());
        
        return $address; // Retorna o objeto Address completo com o ID
    }

    /**
     * Busca um endereço pelo seu ID.
     * @param int $id
     * @return Address|null
     */
    public function find(int $id): ?Address
    {
        $stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToAddress($data);
    }
    
    /**
     * Busca todos os endereços.
     * @return Address[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM addresses");
        $addresses = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $addresses[] = $this->mapToAddress($data);
        }
        return $addresses;
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
    
    /**
     * Mapeia um array de dados do banco para um objeto Address.
     * @param array $data
     * @return Address
     */
    private function mapToAddress(array $data): Address
    {
        return new Address(
            (int)$data['id'],
            (int)$data['client_id'],
            $data['street'] ?? '',
            (int)($data['number'] ?? 0),
            $data['complement'] ?? '',
            $data['neighborhood'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip_code'] ?? '',
            $data['country'] ?? '',
            $data['recipient'] ?? ''
        );
    }
}