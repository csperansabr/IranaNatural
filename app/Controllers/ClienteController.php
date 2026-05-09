<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Mailer;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Carrinho;

class ClienteController extends Controller
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    // ── GET/POST /minha-conta/login ──────────────────────────────
    public function login(): void
    {
        if (Session::has('cliente_id')) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarLogin();
            return;
        }

        $flash = Session::flash('flash_ok');
        $erro  = Session::flash('flash_erro');
        $csrf  = Session::csrfToken();
        $redirect = htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES, 'UTF-8');

        $meta = [
            'title'       => 'Login — ' . APP_NAME,
            'description' => 'Acesse sua conta Iraná Natural.',
            'url'         => APP_URL . '/minha-conta/login',
        ];
        $this->render('cliente/login', compact('meta', 'flash', 'erro', 'csrf', 'redirect'));
    }

    private function processarLogin(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança. Recarregue e tente novamente.');
            $this->redirect(APP_URL . '/minha-conta/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (!$email || !$senha) {
            Session::flash('flash_erro', 'Preencha e-mail e senha.');
            $this->redirect(APP_URL . '/minha-conta/login');
            return;
        }

        $cliente = $this->clienteModel->autenticar($email, $senha);
        if (!$cliente) {
            Session::flash('flash_erro', 'E-mail ou senha incorretos.');
            $this->redirect(APP_URL . '/minha-conta/login');
            return;
        }

        session_regenerate_id(true);
        Session::set('cliente_id',    $cliente['id']);
        Session::set('cliente_nome',  $cliente['nome']);
        Session::set('cliente_email', $cliente['email']);

        // Migrar carrinho anônimo para o cliente logado
        $sessaoId = session_id();
        (new Carrinho())->vincularCliente($sessaoId, $cliente['id']);

        $redirect = trim($_POST['redirect'] ?? '');
        $this->redirect($redirect ?: APP_URL . '/minha-conta');
    }

    // ── GET /minha-conta/logout ──────────────────────────────────
    public function logout(): void
    {
        Session::delete('cliente_id');
        Session::delete('cliente_nome');
        Session::delete('cliente_email');
        Session::flash('flash_ok', 'Você saiu da sua conta.');
        $this->redirect(APP_URL . '/minha-conta/login');
    }

    // ── GET/POST /minha-conta/cadastro ───────────────────────────
    public function cadastro(): void
    {
        if (Session::has('cliente_id')) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarCadastro();
            return;
        }

        $erro          = Session::flash('flash_erro');
        $erroLink      = Session::flash('flash_erro_link');
        $erroLinkTexto = Session::flash('flash_erro_link_texto');
        $csrf          = Session::csrfToken();
        $old           = Session::flash('form_old') ?? [];

        $meta = [
            'title'       => 'Criar Conta — ' . APP_NAME,
            'description' => 'Crie sua conta Iraná Natural e acompanhe seus pedidos.',
            'url'         => APP_URL . '/minha-conta/cadastro',
        ];
        $this->render('cliente/cadastro', compact('meta', 'erro', 'erroLink', 'erroLinkTexto', 'csrf', 'old'));
    }

    private function processarCadastro(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança. Recarregue e tente novamente.');
            $this->redirect(APP_URL . '/minha-conta/cadastro');
            return;
        }

        $campos = ['nome','cpf','email','telefone','data_nascimento','senha','confirmar_senha',
                   'cep','logradouro','numero','bairro','cidade','estado'];
        $data   = [];
        foreach ($campos as $c) {
            $data[$c] = trim($_POST[$c] ?? '');
        }
        $data['complemento'] = trim($_POST['complemento'] ?? '');

        // Preservar dados do formulário (exceto senhas)
        $old = $data;
        unset($old['senha'], $old['confirmar_senha']);

        // Validações
        $erros = [];
        if (mb_strlen($data['nome']) < 3) $erros[] = 'Nome deve ter ao menos 3 caracteres.';
        if (!Cliente::validarCpf($data['cpf'])) $erros[] = 'CPF inválido.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        if (mb_strlen($data['senha']) < 8) $erros[] = 'Senha deve ter ao menos 8 caracteres.';
        if ($data['senha'] !== $data['confirmar_senha']) $erros[] = 'As senhas não conferem.';
        if (!$data['cep'] || !$data['logradouro'] || !$data['numero'] || !$data['bairro'] || !$data['cidade'] || !$data['estado']) {
            $erros[] = 'Preencha todos os campos de endereço.';
        }
        if (strlen(preg_replace('/\D/', '', $data['cep'])) !== 8) $erros[] = 'CEP inválido.';

        if ($erros) {
            Session::flash('flash_erro', implode(' ', $erros));
            Session::flash('form_old', $old);
            $this->redirect(APP_URL . '/minha-conta/cadastro');
            return;
        }

        // Verificar unicidade
        $clienteExistente = $this->clienteModel->findByEmail($data['email']);
        if ($clienteExistente) {
            if (($clienteExistente['origem'] ?? 'online') === 'admin') {
                Session::flash('flash_erro', 'Este e-mail já foi registrado pela nossa equipe. Para criar sua senha e acessar sua conta, clique em:');
                Session::flash('flash_erro_link', APP_URL . '/minha-conta/recuperar-senha');
                Session::flash('flash_erro_link_texto', 'Recuperar / Definir senha');
            } else {
                Session::flash('flash_erro', 'Este e-mail já está cadastrado. Faça login ou clique em:');
                Session::flash('flash_erro_link', APP_URL . '/minha-conta/recuperar-senha');
                Session::flash('flash_erro_link_texto', 'Recuperar senha');
            }
            Session::flash('form_old', $old);
            $this->redirect(APP_URL . '/minha-conta/cadastro');
            return;
        }
        $clienteExistente = $this->clienteModel->findByCpf($data['cpf']);
        if ($clienteExistente) {
            if (($clienteExistente['origem'] ?? 'online') === 'admin') {
                Session::flash('flash_erro', 'Este CPF já foi registrado pela nossa equipe. Para criar sua senha e acessar sua conta, clique em:');
                Session::flash('flash_erro_link', APP_URL . '/minha-conta/recuperar-senha');
                Session::flash('flash_erro_link_texto', 'Recuperar / Definir senha');
            } else {
                Session::flash('flash_erro', 'Este CPF já está cadastrado. Faça login ou clique em:');
                Session::flash('flash_erro_link', APP_URL . '/minha-conta/recuperar-senha');
                Session::flash('flash_erro_link_texto', 'Recuperar senha');
            }
            Session::flash('form_old', $old);
            $this->redirect(APP_URL . '/minha-conta/cadastro');
            return;
        }

        $clienteId = $this->clienteModel->cadastrar($data);
        $this->clienteModel->salvarEndereco($clienteId, $data);

        session_regenerate_id(true);
        Session::set('cliente_id',    $clienteId);
        Session::set('cliente_nome',  $data['nome']);
        Session::set('cliente_email', $data['email']);

        // Migrar carrinho anônimo
        (new Carrinho())->vincularCliente(session_id(), $clienteId);

        Session::flash('flash_ok', 'Bem-vinda, ' . explode(' ', $data['nome'])[0] . '! Sua conta foi criada com sucesso.');
        $this->redirect(APP_URL . '/minha-conta');
    }

    // ── GET/POST /minha-conta/editar ─────────────────────────────
    public function editarPerfil(): void
    {
        self::requerLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarEdicaoPerfil();
            return;
        }

        $clienteId = (int)Session::get('cliente_id');
        $cliente   = $this->clienteModel->findById($clienteId);
        $endereco  = $this->clienteModel->getEndereco($clienteId);

        // Formatar campos mascarados para exibição
        if ($endereco && $endereco['cep']) {
            $cep = preg_replace('/\D/', '', $endereco['cep']);
            if (strlen($cep) === 8) $endereco['cep'] = substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
        if ($cliente['telefone']) {
            $cliente['telefone'] = Cliente::formatarTelefone($cliente['telefone']);
        }

        $erro = Session::flash('flash_erro');
        $old  = Session::flash('form_old') ?? [];
        $csrf = Session::csrfToken();

        $meta = [
            'title'       => 'Editar Dados — ' . APP_NAME,
            'description' => 'Atualize seus dados cadastrais.',
            'url'         => APP_URL . '/minha-conta/editar',
        ];
        $this->render('cliente/editar', compact('meta', 'cliente', 'endereco', 'erro', 'old', 'csrf'));
    }

    private function processarEdicaoPerfil(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança. Recarregue e tente novamente.');
            $this->redirect(APP_URL . '/minha-conta/editar');
            return;
        }

        $clienteId = (int)Session::get('cliente_id');

        $campos = ['nome','cpf','email','telefone','data_nascimento',
                   'cep','logradouro','numero','bairro','cidade','estado'];
        $data   = [];
        foreach ($campos as $c) {
            $data[$c] = trim($_POST[$c] ?? '');
        }
        $data['complemento'] = trim($_POST['complemento'] ?? '');

        $old = $data;

        // Validações centralizadas
        $erros = Cliente::validarDados($data, false);

        // Endereço obrigatório
        foreach (['cep','logradouro','numero','bairro','cidade','estado'] as $f) {
            if (empty($data[$f])) { $erros[] = 'Preencha todos os campos de endereço obrigatórios.'; break; }
        }

        // Unicidade (excluir o próprio cliente)
        if (empty($erros) && !empty($data['email'])) {
            $ex = $this->clienteModel->findByEmail($data['email']);
            if ($ex && (int)$ex['id'] !== $clienteId) {
                $erros[] = 'Este e-mail já está em uso por outra conta.';
            }
        }
        if (empty($erros) && !empty($data['cpf'])) {
            $ex = $this->clienteModel->findByCpf($data['cpf']);
            if ($ex && (int)$ex['id'] !== $clienteId) {
                $erros[] = 'Este CPF já está em uso por outra conta.';
            }
        }

        if ($erros) {
            Session::flash('flash_erro', implode(' ', $erros));
            Session::flash('form_old', $old);
            $this->redirect(APP_URL . '/minha-conta/editar');
            return;
        }

        $this->clienteModel->atualizar($clienteId, $data);
        $this->clienteModel->salvarEndereco($clienteId, $data);

        Session::set('cliente_nome', $data['nome']);
        if (!empty($data['email'])) Session::set('cliente_email', $data['email']);

        Session::flash('flash_ok', 'Dados atualizados com sucesso!');
        $this->redirect(APP_URL . '/minha-conta');
    }

    // ── GET /minha-conta ─────────────────────────────────────────
    public function painel(): void
    {
        self::requerLogin();

        $clienteId = (int)Session::get('cliente_id');
        $cliente   = $this->clienteModel->findById($clienteId);
        $endereco  = $this->clienteModel->getEndereco($clienteId);
        $pedidos   = (new Pedido())->doCliente($clienteId);
        $flash     = Session::flash('flash_ok');
        $erro      = Session::flash('flash_erro');

        $meta = [
            'title'       => 'Minha Conta — ' . APP_NAME,
            'description' => 'Gerencie seus dados e acompanhe seus pedidos.',
            'url'         => APP_URL . '/minha-conta',
        ];
        $this->render('cliente/painel', compact('meta', 'cliente', 'endereco', 'pedidos', 'flash', 'erro'));
    }

    // ── GET/POST /minha-conta/recuperar-senha ────────────────────
    public function recuperarSenha(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarRecuperacao();
            return;
        }

        $flash = Session::flash('flash_ok');
        $erro  = Session::flash('flash_erro');
        $csrf  = Session::csrfToken();

        $meta = [
            'title'       => 'Recuperar Senha — ' . APP_NAME,
            'description' => 'Recupere o acesso à sua conta Iraná Natural.',
            'url'         => APP_URL . '/minha-conta/recuperar-senha',
        ];
        $this->render('cliente/recuperar-senha', compact('meta', 'flash', 'erro', 'csrf'));
    }

    private function processarRecuperacao(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança. Recarregue e tente novamente.');
            $this->redirect(APP_URL . '/minha-conta/recuperar-senha');
            return;
        }

        $email = mb_strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('flash_erro', 'Informe um e-mail válido.');
            $this->redirect(APP_URL . '/minha-conta/recuperar-senha');
            return;
        }

        $cliente = $this->clienteModel->findByEmail($email);
        // Resposta genérica para não revelar se o e-mail existe
        if ($cliente) {
            $token = $this->clienteModel->gerarTokenSenha($cliente['id']);
            $link  = APP_URL . '/minha-conta/nova-senha/' . $token;
            Mailer::enviar(
                $cliente['email'],
                'Recuperação de senha — ' . APP_NAME,
                $this->templateRecuperacao($cliente['nome'], $link)
            );
        }

        Session::flash('flash_ok', 'Se esse e-mail estiver cadastrado, você receberá as instruções em breve.');
        $this->redirect(APP_URL . '/minha-conta/recuperar-senha');
    }

    // ── GET/POST /minha-conta/nova-senha/{token} ─────────────────
    public function novaSenha(string $token): void
    {
        $tokenData = $this->clienteModel->validarTokenSenha($token);
        if (!$tokenData) {
            Session::flash('flash_erro', 'Link inválido ou expirado. Solicite novamente.');
            $this->redirect(APP_URL . '/minha-conta/recuperar-senha');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarNovaSenha($tokenData);
            return;
        }

        $erro = Session::flash('flash_erro');
        $csrf = Session::csrfToken();
        $meta = [
            'title' => 'Nova Senha — ' . APP_NAME,
            'url'   => APP_URL . '/minha-conta/nova-senha/' . $token,
        ];
        $this->render('cliente/nova-senha', compact('meta', 'token', 'erro', 'csrf'));
    }

    private function processarNovaSenha(array $tokenData): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança.');
            $this->redirect(APP_URL . '/minha-conta/nova-senha/' . $tokenData['token']);
            return;
        }

        $senha    = $_POST['senha'] ?? '';
        $confirma = $_POST['confirmar_senha'] ?? '';

        if (mb_strlen($senha) < 8) {
            Session::flash('flash_erro', 'Senha deve ter ao menos 8 caracteres.');
            $this->redirect(APP_URL . '/minha-conta/nova-senha/' . $tokenData['token']);
            return;
        }
        if ($senha !== $confirma) {
            Session::flash('flash_erro', 'As senhas não conferem.');
            $this->redirect(APP_URL . '/minha-conta/nova-senha/' . $tokenData['token']);
            return;
        }

        $this->clienteModel->alterarSenha($tokenData['cliente_id'], $senha);
        $this->clienteModel->consumirTokenSenha($tokenData['id']);

        Session::flash('flash_ok', 'Senha alterada com sucesso! Faça login.');
        $this->redirect(APP_URL . '/minha-conta/login');
    }

    // ── Helpers ──────────────────────────────────────────────────
    public static function requerLogin(?string $redirect = null): void
    {
        if (!Session::has('cliente_id')) {
            $destino = $redirect ?? APP_URL . '/minha-conta';
            Session::flash('flash_erro', 'Faça login para continuar.');
            header('Location: ' . APP_URL . '/minha-conta/login?redirect=' . urlencode($destino));
            exit;
        }
    }

    private function templateRecuperacao(string $nome, string $link): string
    {
        $h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif; background:#F5EFE3; padding:32px 16px;">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; margin:0 auto; background:#FDFAF4; border-radius:12px; overflow:hidden;">
  <tr><td style="background:linear-gradient(135deg,#2C5F2E,#5D7A3A); padding:32px; text-align:center;">
    <h1 style="color:#EDE3CE; margin:0;">' . APP_NAME . '</h1>
  </td></tr>
  <tr><td style="padding:32px 40px;">
    <h2 style="color:#2C5F2E; margin:0 0 16px;">Recuperação de senha</h2>
    <p style="color:#5A4E40;">Olá, <strong>' . $h($nome) . '</strong>!</p>
    <p style="color:#5A4E40;">Recebemos uma solicitação de redefinição de senha. Clique no botão abaixo para criar uma nova senha. O link expira em <strong>2 horas</strong>.</p>
    <p style="text-align:center; margin:32px 0;">
      <a href="' . $h($link) . '" style="background:#2C5F2E; color:#fff; padding:14px 32px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;">Redefinir minha senha</a>
    </p>
    <p style="color:#8A7A6A; font-size:13px;">Se você não solicitou a redefinição, ignore este e-mail. Sua senha permanece a mesma.</p>
  </td></tr>
  <tr><td style="background:#2C5F2E; padding:16px; text-align:center;">
    <p style="color:#B5C99A; font-size:12px; margin:0;">' . APP_NAME . ' &mdash; ' . APP_URL . '</p>
  </td></tr>
</table>
</body></html>';
    }
}
