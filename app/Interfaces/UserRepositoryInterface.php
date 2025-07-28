<?php

namespace App\Interfaces;

use App\Model\User;

/**
 * Interface UserRepositoryInterface
 * @package App\Repository
 *
 * Define o contrato para operações de persistência de usuários.
 * Qualquer classe que implementar esta interface deve fornecer a lógica
 * para salvar, buscar, atualizar e deletar usuários.
 */
interface UserRepositoryInterface
{
    /**
     * Salva um novo usuário no banco de dados.
     * O ID do usuário deve ser populado no objeto User após a inserção.
     *
     * @param User $user O objeto User a ser salvo.
     * @return User O objeto User com o ID populado (se aplicável).
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function save(User $user): User;

    /**
     * Atualiza um usuário existente no banco de dados.
     *
     * @param User $user O objeto User com os dados atualizados.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function update(User $user): bool;

    /**
     * Deleta um usuário do banco de dados pelo seu ID.
     *
     * @param int $id O ID do usuário a ser deletado.
     * @return bool True se a exclusão for bem-sucedida, false caso contrário.
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function delete(int $id): bool;

    /**
     * Encontra um usuário pelo seu ID.
     *
     * @param int $id O ID do usuário.
     * @return User|null A instância do User se encontrado, ou null caso contrário.
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function findById(int $id): ?User;

    /**
     * Encontra um usuário pelo seu endereço de e-mail.
     *
     * @param string $email O endereço de e-mail do usuário.
     * @return User|null A instância do User se encontrado, ou null caso contrário.
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Retorna todos os usuários registrados no sistema.
     *
     * @return User[] Um array de objetos User.
     * @throws \PDOException Se ocorrer um erro no banco de dados.
     */
    public function findAll(): array;
}