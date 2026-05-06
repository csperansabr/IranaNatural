<?php
namespace Admin\Controllers;

use App\Core\Session;
use App\Models\Usuario;

class AuthController extends AdminController
{
    public function login(): void
    {
        if (Session::has(ADMIN_SESSION)) {
            $this->redirect('/admin/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            $user = (new Usuario())->autenticar($email, $senha);
            if ($user) {
                session_regenerate_id(true);
                Session::set(ADMIN_SESSION, ['id' => $user['id'], 'nome' => $user['nome'], 'email' => $user['email']]);
                $this->redirect('/admin/dashboard');
                return;
            }
            $erro = 'E-mail ou senha inválidos.';
        }

        $content = '';
        require ROOT . '/admin/Views/login.php';
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: /admin/login');
        exit;
    }
}
