<?php
namespace Admin\Controllers;

use App\Core\Session;
use App\Models\Usuario;

class AuthController extends AdminController
{
    // ── Login / Logout ────────────────────────────────────────────────────────

    public function login(): void
    {
        if (Session::has(ADMIN_SESSION)) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
                $erro = 'Requisição inválida. Recarregue a página e tente novamente.';
            } else {
                $email = trim($_POST['email'] ?? '');
                $senha = $_POST['senha'] ?? '';

                $user = (new Usuario())->autenticar($email, $senha);
                if ($user) {
                    session_regenerate_id(true);
                    Session::set(ADMIN_SESSION, [
                        'id'    => $user['id'],
                        'nome'  => $user['nome'],
                        'email' => $user['email'],
                    ]);
                    $this->redirect('/admin/dashboard');
                    return;
                }
                $erro = 'E-mail ou senha inválidos.';
            }
        }

        require ROOT . '/admin/Views/login.php';
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: /admin/login');
        exit;
    }

    // ── Recuperação de senha ──────────────────────────────────────────────────

    public function recuperarSenha(): void
    {
        if (Session::has(ADMIN_SESSION)) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $flash = $this->getFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
                $this->flash('error', 'Requisição inválida. Tente novamente.');
                $this->redirect('/admin/recuperar-senha');
                return;
            }

            $email = trim($_POST['email'] ?? '');

            // Processa independentemente de o e-mail existir ou não —
            // nunca revelar se o cadastro existe.
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $model = new Usuario();
                $user  = $model->findByEmail($email);
                if ($user) {
                    $token = $model->criarTokenRecuperacao((int)$user['id']);
                    $this->enviarEmailRecuperacao($email, $user['nome'], $token);
                }
            }

            $this->flash('success', 'Se este e-mail estiver cadastrado, você receberá as instruções em instantes.');
            $this->redirect('/admin/recuperar-senha');
            return;
        }

        require ROOT . '/admin/Views/recuperar-senha.php';
    }

    // ── Redefinição de senha via token ────────────────────────────────────────

    public function novaSenha(string $token = ''): void
    {
        if (Session::has(ADMIN_SESSION)) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $model = new Usuario();
        $dados = ($token !== '') ? $model->findTokenValido($token) : null;

        if (!$dados) {
            $this->flash('error', 'Link de recuperação inválido ou expirado. Solicite um novo.');
            $this->redirect('/admin/recuperar-senha');
            return;
        }

        $flash = $this->getFlash();
        $erro  = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
                $this->flash('error', 'Requisição inválida. Tente novamente.');
                $this->redirect('/admin/nova-senha/' . $token);
                return;
            }

            // Revalidar token no POST para proteger contra condição de corrida
            $dados = $model->findTokenValido($token);
            if (!$dados) {
                $this->flash('error', 'Link de recuperação inválido ou expirado. Solicite um novo.');
                $this->redirect('/admin/recuperar-senha');
                return;
            }

            $nova  = $_POST['senha']             ?? '';
            $conf  = $_POST['senha_confirmacao'] ?? '';
            $erros = $this->validarNovaSenha($nova, $conf);

            if ($erros) {
                $erro = implode(' ', $erros);
            } else {
                $model->alterarSenha((int)$dados['usuario_id'], $nova);
                $model->consumirToken($token); // impede reutilização
                $this->flash('success', 'Senha redefinida com sucesso. Faça login com a nova senha.');
                $this->redirect('/admin/login');
                return;
            }
        }

        require ROOT . '/admin/Views/nova-senha.php';
    }

    // ── Alteração de senha (usuário autenticado) ──────────────────────────────

    public function alterarSenha(): void
    {
        if (!Session::has(ADMIN_SESSION)) {
            $this->redirect('/admin/login');
            return;
        }

        $user  = $this->currentUser();
        $flash = $this->getFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
                $this->flash('error', 'Requisição inválida. Tente novamente.');
                $this->redirect('/admin/alterar-senha');
                return;
            }

            $senhaAtual = $_POST['senha_atual']      ?? '';
            $nova       = $_POST['senha_nova']        ?? '';
            $conf       = $_POST['senha_confirmacao'] ?? '';

            $erros = $this->validarNovaSenha($nova, $conf);

            $model     = new Usuario();
            $usuarioDb = $model->autenticar($user['email'], $senhaAtual);

            if (!$usuarioDb) {
                $erros[] = 'Senha atual incorreta.';
            } elseif (empty($erros) && password_verify($nova, $usuarioDb['senha'])) {
                $erros[] = 'A nova senha não pode ser igual à senha atual.';
            }

            if ($erros) {
                $this->flash('error', implode(' ', $erros));
                $this->redirect('/admin/alterar-senha');
                return;
            }

            $model->alterarSenha((int)$user['id'], $nova);
            $this->flash('success', 'Senha alterada com sucesso.');
            $this->redirect('/admin/alterar-senha');
            return;
        }

        $this->render('perfil/alterar-senha', compact('user', 'flash'));
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Valida nova senha e confirmação. Retorna array de erros (vazio = válido).
     */
    private function validarNovaSenha(string $senha, string $confirmacao): array
    {
        $erros = [];

        if ($senha === '') {
            $erros[] = 'A nova senha é obrigatória.';
        } elseif (strlen($senha) < 8) {
            $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        if ($confirmacao === '') {
            $erros[] = 'A confirmação de senha é obrigatória.';
        } elseif ($senha !== '' && $senha !== $confirmacao) {
            $erros[] = 'As senhas não conferem.';
        }

        return $erros;
    }

    /**
     * Envia e-mail de recuperação via PHP mail() — funciona em hospedagem
     * compartilhada (HostGator) sem dependências externas.
     */
    private function enviarEmailRecuperacao(string $para, string $nome, string $token): void
    {
        $link    = APP_URL . '/admin/nova-senha/' . $token;
        $appName = APP_NAME;
        $assunto = '[' . $appName . '] Recuperação de Senha — Painel Admin';

        $corpo = "Olá, {$nome}.\n\n"
               . "Recebemos uma solicitação de redefinição de senha para o painel administrativo do {$appName}.\n\n"
               . "Acesse o link abaixo para criar uma nova senha (válido por 1 hora):\n\n"
               . "{$link}\n\n"
               . "Se você não fez esta solicitação, ignore este e-mail.\n"
               . "Sua senha permanecerá a mesma e nenhuma ação adicional é necessária.\n\n"
               . "Atenciosamente,\n"
               . "{$appName}";

        $headers = implode("\r\n", [
            'From: ' . $appName . ' <' . EMAIL_NOREPLY . '>',
            'Reply-To: ' . EMAIL_CONTATO,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . PHP_VERSION,
        ]);

        // Assunto codificado para suportar caracteres especiais (RFC 2047)
        @mail($para, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, $headers);
    }
}
