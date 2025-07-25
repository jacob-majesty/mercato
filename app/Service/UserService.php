<?php

namespace App\Service;

use App\Model\User;
use App\Model\Admin;
use App\Model\Seller;
use App\Model\Client;
use App\Repository\UserRepositoryInterface; // Usar a interface
use App\DTO\UserDTO; // Importar o DTO
use Exception;

/**
 * Class UserService
 * @package App\Service
 *
 * Responsável pela lógica de negócio relacionada a usuários,
 * incluindo criação, edição de perfil, exclusão e autenticação.
 * Orquestra a interação entre Models e Repositories.
 */
class UserService
{
    private UserRepositoryInterface $userRepository; // Dependência na interface

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Cria um novo usuário no sistema a partir de um UserDTO.
     * Esta função lida com o hashing da senha e a persistência no banco de dados.
     *
     * @param UserDTO $userDTO DTO com os dados do novo usuário.
     * @return User A instância do usuário criada e salva.
     * @throws \InvalidArgumentException Se dados obrigatórios estiverem faltando ou inválidos.
     * @throws Exception Se o email já estiver em uso ou falha interna.
     */
    public function createUser(UserDTO $userDTO): User
    {
        // 1. Validação dos dados de entrada do DTO
        if (empty($userDTO->email) || empty($userDTO->firstName) || empty($userDTO->lastName) || empty($userDTO->role) || empty($userDTO->password)) {
            throw new \InvalidArgumentException("Todos os campos obrigatórios para criar usuário (email, nome, sobrenome, papel, senha) devem ser preenchidos.");
        }

        if (!filter_var($userDTO->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Formato de email inválido.");
        }

        // 2. Verificar se o email já existe
        if ($this->userRepository->findByEmail($userDTO->email)) {
            throw new Exception("Este email já está cadastrado no sistema.");
        }

        // 3. Hash da senha
        $hashedPassword = password_hash($userDTO->password, PASSWORD_BCRYPT);
        if ($hashedPassword === false) {
            throw new Exception("Falha ao criar hash da senha.");
        }

        // 4. Instanciar o Model correto baseado na role
        $user = null;
        switch ($userDTO->role) {
            case 'admin':
                $user = new Admin(
                    $userDTO->email,
                    $userDTO->firstName,
                    $userDTO->lastName,
                    $hashedPassword
                );
                break;
            case 'seller':
                $user = new Seller(
                    $userDTO->email,
                    $userDTO->firstName,
                    $userDTO->lastName,
                    $hashedPassword
                );
                break;
            case 'client':
                $user = new Client(
                    $userDTO->email,
                    $userDTO->firstName,
                    $userDTO->lastName,
                    $hashedPassword
                );
                break;
            default:
                throw new \InvalidArgumentException("Papel de usuário inválido: " . $userDTO->role);
        }

        // 5. Persistir o usuário via Repository e retornar o Model salvo
        return $this->userRepository->save($user);
    }

    /**
     * Realiza o login do usuário.
     *
     * @param string $email O email fornecido.
     * @param string $password A senha em texto puro fornecida.
     * @return User|null A instância do usuário logado se as credenciais forem válidas, null caso contrário.
     */
    public function login(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null; // Usuário não encontrado
        }

        if (password_verify($password, $user->getPswd())) {
            // Credenciais válidas, iniciar sessão.
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_role'] = $user->getRole();
            return $user;
        }

        return null; // Senha incorreta
    }

    /**
     * Realiza o logout do usuário.
     * @return void
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Edita o perfil de um usuário a partir de um UserDTO.
     * Restrito para que o próprio usuário edite seu perfil.
     * Para edição por admin, ver AdminService.
     *
     * @param int $userId O ID do usuário cujo perfil será editado.
     * @param UserDTO $userDTO DTO com os dados a serem atualizados.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     * @throws Exception Se o usuário não tiver permissão para editar ou falha na atualização.
     * @throws \InvalidArgumentException Se dados de entrada forem inválidos.
     */
    public function editProfile(int $userId, UserDTO $userDTO): bool
    {
        // 1. Verificar se o usuário logado tem permissão para editar ESTE perfil
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $userId) {
            throw new Exception("Você não tem permissão para editar este perfil.");
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("Usuário não encontrado.");
        }

        // 2. Atualizar os atributos do objeto Model usando os dados do DTO
        if (isset($userDTO->firstName)) {
            $user->setFirstName($userDTO->firstName);
        }
        if (isset($userDTO->lastName)) {
            $user->setLastName($userDTO->lastName);
        }
        if (isset($userDTO->email)) {
            if (!filter_var($userDTO->email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Formato de email inválido.");
            }
            $existingUserWithEmail = $this->userRepository->findByEmail($userDTO->email);
            if ($existingUserWithEmail && $existingUserWithEmail->getId() !== $userId) {
                throw new Exception("Este email já está em uso por outro usuário.");
            }
            $user->setEmail($userDTO->email);
        }
        if (isset($userDTO->newPassword) && !empty($userDTO->newPassword)) {
            if (isset($userDTO->confirmPassword) && $userDTO->newPassword !== $userDTO->confirmPassword) {
                throw new \InvalidArgumentException("A nova senha e a confirmação não coincidem.");
            }
            $hashedPassword = password_hash($userDTO->newPassword, PASSWORD_BCRYPT);
            if ($hashedPassword === false) {
                throw new Exception("Falha ao criptografar nova senha.");
            }
            $user->setPswd($hashedPassword);
        }

        // 3. Persistir as mudanças via Repository
        return $this->userRepository->update($user);
    }

    /**
     * Deleta um usuário.
     * Restrito para que o próprio usuário delete sua conta.
     * Para deleção por admin, ver AdminService.
     *
     * @param int $userId O ID do usuário a ser deletado.
     * @return bool True se a exclusão for bem-sucedida, false caso contrário.
     * @throws Exception Se o usuário não tiver permissão ou falha na exclusão.
     */
    public function deleteUserAccount(int $userId): bool
    {
        // 1. Verificar se o usuário logado tem permissão para deletar ESTA conta
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $userId) {
            throw new Exception("Você não tem permissão para deletar esta conta.");
        }

        // 2. Persistir a exclusão via Repository
        // Lógica de exclusão em cascata ou soft delete deve ser definida no DB ou no Repository.
        return $this->userRepository->delete($userId);
    }

    /**
     * Retorna um usuário pelo ID.
     * @param int $userId
     * @return User|null
     */
    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}