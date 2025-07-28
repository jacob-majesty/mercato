<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\Authenticator;
use App\Service\UserService;
use App\Service\ClientService;
use App\Service\LogService;
use App\DTO\ClientCreateDTO; // DTO para registro
use App\Model\User; // Para type hinting

class AuthController
{
    private UserService $userService;
    private ClientService $clientService;
    private LogService $logService;

    public function __construct(
        UserService $userService,
        ClientService $clientService,
        LogService $logService
    ) {
        $this->userService = $userService;
        $this->clientService = $clientService;
        $this->logService = $logService;
    }

    public function showLoginForm(Request $request): Response
    {
        // Se já estiver logado, redireciona para o dashboard ou home
        if (Authenticator::check()) {
            // Redireciona com base no papel do usuário logado
            $userRole = Authenticator::getUserRole();
            switch ($userRole) {
                case 'admin':
                    return Response::redirect('/admin/dashboard');
                case 'seller':
                    return Response::redirect('/seller/dashboard');
                case 'client':
                    return Response::redirect('/');
                default:
                    return Response::redirect('/');
            }
        }
        return Response::view('auth/login');
    }

    public function login(Request $request): Response
    {
        $email = $request->post('email');
        $password = $request->post('password');

        if (empty($email) || empty($password)) {
            return Response::view('auth/login', ['error' => 'Por favor, preencha todos os campos.'], 400);
        }

        try {
            $user = $this->userService->authenticateUser($email, $password);

            if ($user) {
                Authenticator::login($user);
                $this->logService->log('Auth', 'Login successful', $user->getId());
                // Redireciona com base no papel do usuário
                switch ($user->getRole()) {
                    case 'admin':
                        return Response::redirect('/admin/dashboard');
                    case 'seller':
                        return Response::redirect('/seller/dashboard');
                    case 'client':
                        return Response::redirect('/');
                    default:
                        return Response::redirect('/');
                }
            } else {
                $this->logService->log('Auth', 'Login failed: Invalid credentials', null, ['email' => $email]);
                return Response::view('auth/login', ['error' => 'Email ou senha inválidos.', 'old_input' => ['email' => $email]], 401);
            }
        } catch (\Exception $e) {
            $this->logService->log('Auth', 'Login error: ' . $e->getMessage(), null, ['email' => $email]);
            return Response::view('auth/login', ['error' => 'Ocorreu um erro ao tentar logar. Tente novamente.', 'old_input' => ['email' => $email]], 500);
        }
    }

    public function showRegisterForm(Request $request): Response
    {
        if (Authenticator::check()) {
            // Redireciona com base no papel do usuário logado
            $userRole = Authenticator::getUserRole();
            switch ($userRole) {
                case 'admin':
                    return Response::redirect('/admin/dashboard');
                case 'seller':
                    return Response::redirect('/seller/dashboard');
                case 'client':
                    return Response::redirect('/');
                default:
                    return Response::redirect('/');
            }
        }
        return Response::view('auth/register');
    }

    public function register(Request $request): Response
    {
        $data = $request->all(); // Pega todos os dados da requisição POST
        
        // Crie um DTO a partir dos dados da requisição
        $clientDTO = new ClientCreateDTO([
            'email' => $data['email'] ?? '',
            'firstName' => $data['first_name'] ?? '',
            'lastName' => $data['last_name'] ?? '',
            'password' => $data['password'] ?? '',
            'confirmPassword' => $data['confirm_password'] ?? ''
        ]);

        try {
            // Validação de senhas no controlador
            if ($clientDTO->password !== $clientDTO->confirmPassword) {
                return Response::view('auth/register', ['error' => 'As senhas não coincidem.', 'old_input' => $data], 400);
            }

            $newClient = $this->clientService->registerClient($clientDTO);

            // Opcional: Logar o usuário recém-registrado automaticamente
            Authenticator::login($newClient);
            $this->logService->log('User', 'User registered and logged in', $newClient->getId());

            return Response::redirect('/'); // Redireciona para a página inicial após o registro e login
        } catch (\InvalidArgumentException $e) {
            $this->logService->log('User', 'Registration failed: ' . $e->getMessage(), null, ['email' => $clientDTO->email]);
            return Response::view('auth/register', ['error' => $e->getMessage(), 'old_input' => $data], 400);
        } catch (\Exception $e) {
            $this->logService->log('User', 'Registration error: ' . $e->getMessage(), null, ['email' => $clientDTO->email]);
            return Response::view('auth/register', ['error' => 'Ocorreu um erro ao registrar. Tente novamente.', 'old_input' => $data], 500);
        }
    }

    public function logout(Request $request): Response
    {
        if (Authenticator::check()) {
            $userId = Authenticator::getUserId();
            Authenticator::logout();
            $this->logService->log('Auth', 'Logout successful', $userId);
        }
        return Response::redirect('/login');
    }
}
