<?php
namespace Admin\Controllers;

use App\Models\Configuracao;

class ConfiguracoesController extends AdminController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvar();
            return;
        }

        $config = (new Configuracao())->getAll();
        $flash  = $this->getFlash();
        $user   = $this->currentUser();
        $this->render('configuracoes/index', compact('config', 'flash', 'user'));
    }

    private function salvar(): void
    {
        $model = new Configuracao();

        // ── Frete — Melhor Envio ──────────────────────────────────
        $cepRaw  = preg_replace('/\D/', '', trim($_POST['frete_cep_origem'] ?? ''));
        $timeout = max(5, min(60, (int)($_POST['frete_timeout'] ?? 15)));

        // Serviços: valida que são IDs conhecidos
        $servicosPermitidos = [1, 2, 9, 10];
        $servicosSelecionados = array_filter(
            array_map('intval', $_POST['frete_services'] ?? []),
            fn($s) => in_array($s, $servicosPermitidos, true)
        );
        $services = implode(',', $servicosSelecionados) ?: '1,2';

        $campos = [
            'frete_ativo'      => isset($_POST['frete_ativo']) ? '1' : '0',
            'frete_sandbox'    => ($_POST['frete_sandbox'] ?? '1') === '0' ? '0' : '1',
            'frete_cep_origem' => $cepRaw,
            'frete_timeout'    => (string)$timeout,
            'frete_services'   => $services,
            'frete_ssl_verify' => isset($_POST['frete_ssl_verify']) ? '1' : '0',
        ];

        $erros = [];
        if ($cepRaw !== '' && strlen($cepRaw) !== 8) {
            $erros[] = 'CEP de origem inválido — informe os 8 dígitos.';
        }

        if ($erros) {
            $this->flash('error', implode(' ', $erros));
            header('Location: /admin/configuracoes');
            exit;
        }

        foreach ($campos as $chave => $valor) {
            $model->set($chave, $valor);
        }

        // Tokens: só atualiza se o campo não veio vazio (keep-if-blank)
        $tokenSandbox  = trim($_POST['frete_token_sandbox']  ?? '');
        $tokenProducao = trim($_POST['frete_token_producao'] ?? '');
        if ($tokenSandbox  !== '') $model->set('frete_token_sandbox',  $tokenSandbox);
        if ($tokenProducao !== '') $model->set('frete_token_producao', $tokenProducao);

        Configuracao::resetCache();

        $this->flash('success', 'Configurações salvas com sucesso.');
        header('Location: /admin/configuracoes');
        exit;
    }

    /** Formata string de 8 dígitos como XXXXX-XXX para exibição. */
    public function formatarCep(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep);
        if (strlen($digits) === 8) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }
        return $cep;
    }
}
