<?php

namespace App\DTO;

/**
 * Class AdminDTO
 * @package App\DTO
 *
 * DTO para representar dados de um administrador, se necessário para exibição ou edição
 * de perfil de admin. Para operações de gerenciamento, os parâmetros costumam ser mais diretos.
 */
class AdminDTO extends UserDTO
{
    // Atualmente, não há atributos específicos de Admin que não estejam em User.
    // Este DTO herda de UserDTO e pode ser estendido se houver necessidades futuras.
    // Ex: public ?string $adminSpecificSetting = null;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        // Adicionar lógica de preenchimento para atributos específicos de Admin, se existirem
    }
}