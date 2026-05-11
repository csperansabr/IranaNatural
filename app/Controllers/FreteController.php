<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Carrinho;
use App\Services\FreteService;

class FreteController extends Controller
{
    /**
     * POST /api/frete/calcular
     *
     * Recebe { cep: "12345678" } e retorna JSON com opções de frete
     * usando os itens do carrinho atual da sessão. As dimensões dos
     * produtos são lidas diretamente do JOIN em Carrinho::getItens().
     */
    public function calcular(): void
    {
        header('Content-Type: application/json');

        // Aceita JSON ou form-data
        $raw   = (string)file_get_contents('php://input');
        $input = $raw ? (json_decode($raw, true) ?? []) : [];
        $cep   = preg_replace('/\D/', '', (string)($input['cep'] ?? $_POST['cep'] ?? ''));

        if (strlen($cep) !== 8) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'CEP inválido.']);
            exit;
        }

        // Carrinho da sessão atual, vinculando cliente se logado
        $sessaoId      = session_id();
        $clienteId     = Session::has('cliente_id') ? (int)Session::get('cliente_id') : null;
        $carrinhoModel = new Carrinho();
        $carrinho      = $carrinhoModel->queryOne("SELECT * FROM carrinhos WHERE sessao_id = ?", [$sessaoId]);

        // Vincula cliente ao carrinho quando logado e ainda não associado
        if ($carrinho && $clienteId && empty($carrinho['cliente_id'])) {
            $carrinhoModel->vincularCliente($sessaoId, $clienteId);
        }

        $itens = $carrinho ? $carrinhoModel->getItens((int)$carrinho['id']) : [];

        if (empty($itens)) {
            echo json_encode(['ok' => true, 'opcoes' => []]);
            exit;
        }

        try {
            $service = new FreteService();
            $opcoes  = $service->calcular($cep, $itens);
            echo json_encode(['ok' => true, 'opcoes' => $opcoes]);
        } catch (\Throwable $e) {
            @file_put_contents(
                ROOT . '/logs/melhorenvio.log',
                json_encode(['ts' => date('c'), 'err' => $e->getMessage()], JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND | LOCK_EX
            );
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Erro ao calcular frete.']);
        }

        exit;
    }
}
