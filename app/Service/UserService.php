<?php

namespace App\Service;

use App\Interfaces\UserRepositoryInterface;
use App\Model\User;
use App\DTO\UserCreateDTO; // Se você tiver um DTO para criação de usuário
use App\DTO\UserUpdateDTO; // Se você tiver um DTO para atualização de usuário
use Exception;

class UserService
{
    private UserRepositoryInterface $userRepository;
    private LogService $logService;

    public function __construct(UserRepositoryInterface $userRepository, LogService $logService)
    {
        $this->userRepository = $userRepository;
        $this->logService = $logService;
    }

    /**
     * Autentica um usuário com base no email e senha.
     * @param string $email O email do usuário.
     * @param string $password A senha em texto puro.
     * @return User|null O objeto User se a autenticação for bem-sucedida, ou null caso contrário.
     * @throws Exception Se ocorrer um erro inesperado durante a autenticação.
     */
    public function authenticateUser(string $email, string $password): ?User
    {
        error_log("UserService: Tentando autenticar usuário com email: " . $email);
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                error_log("UserService: Usuário com email " . $email . " NÃO ENCONTRADO no banco de dados.");
                $this->logService->log('Auth', 'Authentication failed: User not found', null, ['email' => $email]);
                return null;
            }

            error_log("UserService: Usuário ENCONTRADO (ID: " . $user->getId() . ", Role: " . $user->getRole() . "). Verificando senha.");
            error_log("UserService: Senha fornecida (plain): " . $password);
            error_log("UserService: Senha hashada do DB: " . $user->getPswd());

            // Verifica a senha
            if (password_verify($password, $user->getPswd())) {
                // Senha correta
                error_log("UserService: Senha VERIFICADA com sucesso para ID: " . $user->getId());
                $this->logService->log('Auth', 'User authenticated successfully', $user->getId());
                return $user;
            } else {
                // Senha incorreta
                error_log("UserService: Senha INCORRETA para ID: " . $user->getId());
                $this->logService->log('Auth', 'Authentication failed: Incorrect password', $user->getId());
                return null;
            }
        } catch (Exception $e) {
            // Loga a exceção completa aqui para depuração
            error_log("UserService: ERRO FATAL durante a autenticação para email {$email}: " . $e->getMessage() . " na linha " . $e->getLine() . " do arquivo " . $e->getFile());
            error_log("UserService: Stack trace do erro: " . $e->getTraceAsString());
            $this->logService->log('ERROR', 'Authentication service error', null, ['email' => $email, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            throw new Exception("Erro ao autenticar usuário: " . $e->getMessage());
        }
    }

    /**
     * Registra um novo usuário.
     * @param UserCreateDTO $userDTO
     * @return User
     * @throws Exception Se o email já existe ou a persistência falha.
     */
    public function registerUser(UserCreateDTO $userDTO): User
    {
        // 1. Validação básica do DTO (pode ser mais robusta dentro do próprio DTO)
        if (empty($userDTO->email) || empty($userDTO->firstName) || empty($userDTO->lastName) || empty($userDTO->password) || empty($userDTO->role)) {
            throw new \InvalidArgumentException("Dados de registro incompletos.");
        }

        // 2. Verificar se o email já está em uso
        if ($this->userRepository->findByEmail($userDTO->email)) {
            throw new Exception("Email já cadastrado.");
        }

        // 3. Hash da senha
        $hashedPassword = password_hash($userDTO->password, PASSWORD_BCRYPT);

        // 4. Criar o objeto User Model
        $user = new User(
            $userDTO->email,
            $userDTO->firstName,
            $userDTO->lastName,
            $hashedPassword, // Senha hashada
            $userDTO->role,
            null, // ID será gerado pelo banco de dados
            new \DateTime(),
            null // updatedAt
        );

        // 5. Salvar o usuário via Repository
        try {
            $savedUser = $this->userRepository->save($user);
            $this->logService->log('User', 'User registered', $savedUser->getId());
            return $savedUser;
        } catch (Exception $e) {
            $this->logService->log('User', 'User registration failed', null, ['email' => $userDTO->email, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Obtém todos os usuários.
     * @return User[]
     */
    public function getAllUsers(): array
    {
        try {
            return $this->userRepository->findAll();
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Failed to retrieve all users', null, ['error' => $e->getMessage()]);
            throw new Exception("Erro ao buscar todos os usuários: " . $e->getMessage());
        }
    }

    /**
     * Obtém um usuário pelo ID.
     * @param int $userId
     * @return User|null
     */
    public function getUserById(int $userId): ?User
    {
        try {
            return $this->userRepository->findById($userId);
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Failed to retrieve user by ID', $userId, ['error' => $e->getMessage()]);
            throw new Exception("Erro ao buscar usuário por ID: " . $e->getMessage());
        }
    }

    /**
     * Atualiza os dados de um usuário.
     * @param UserUpdateDTO $userDTO
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     * @throws Exception Se o usuário não for encontrado ou ocorrer um erro.
     */
    public function updateUser(UserUpdateDTO $userDTO): bool
    {
        if (empty($userDTO->id)) {
            throw new \InvalidArgumentException("ID do usuário é necessário para atualização.");
        }

        $user = $this->userRepository->findById($userDTO->id);
        if (!$user) {
            throw new Exception("Usuário com ID {$userDTO->id} não encontrado.");
        }

        // Aplicar atualizações apenas se os dados estiverem presentes no DTO
        if ($userDTO->email !== null) {
            // Verificar se o novo email já está em uso por outro usuário
            $existingUserWithEmail = $this->userRepository->findByEmail($userDTO->email);
            if ($existingUserWithEmail && $existingUserWithEmail->getId() !== $user->getId()) {
                throw new Exception("Email '{$userDTO->email}' já está em uso por outro usuário.");
            }
            $user->setEmail($userDTO->email);
        }
        if ($userDTO->firstName !== null) {
            $user->setFirstName($userDTO->firstName);
        }
        if ($userDTO->lastName !== null) {
            $user->setLastName($userDTO->lastName);
        }
        if ($userDTO->role !== null) {
            // Adicionar validação de papel se necessário (e.g., se o papel é um ENUM válido)
            $user->setRole($userDTO->role);
        }
        if ($userDTO->password !== null && !empty($userDTO->password)) {
            $user->setPswd(password_hash($userDTO->password, PASSWORD_BCRYPT));
        }

        try {
            $success = $this->userRepository->update($user);
            if ($success) {
                $this->logService->log('User', 'User profile updated', $user->getId());
            }
            return $success;
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Failed to update user', $user->getId(), ['error' => $e->getMessage()]);
            throw new Exception("Erro ao atualizar usuário: " . $e->getMessage());
        }
    }

    /**
     * Deleta um usuário pelo ID.
     * @param int $userId
     * @return bool True se a exclusão foi bem-sucedida, false caso contrário.
     * @throws Exception Se o usuário não for encontrado ou ocorrer um erro.
     */
    public function deleteUser(int $userId): bool
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("Usuário com ID {$userId} não encontrado.");
        }

        try {
            $success = $this->userRepository->delete($userId);
            if ($success) {
                $this->logService->log('User', 'User deleted', $userId);
            }
            return $success;
        } catch (Exception $e) {
            $this->logService->log('ERROR', 'Failed to delete user', $userId, ['error' => $e->getMessage()]);
            throw new Exception("Erro ao deletar usuário: " . $e->getMessage());
        }
    }
}
