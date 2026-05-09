<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Carrinho;
use App\Services\FreteService;

class FreteController extends Controller
{
    /**
     * POST /api/frete/calcular
     *
     * Recebe { cep: "12345678" } e retorna JSON com opções de frete
     * usando os itens do carrinho atual da sessão.
     */
    public function calcular(): void
    {
        header('Content-Type: application/json');

        // Aceita JSON ou form-data
        $input = [];
        $raw   = (string)file_get_contents('php://input');
        if ($raw) {
            $input = json_decode($raw, true) ?? [];
        }
        $cep = preg_replace('/\D/', '', (string)($input['cep'] ?? $_POST['cep'] ?? ''));

        if (strlen($cep) !== 8) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'CEP inválido.']);
            exit;
        }

        // Carrinho da sessão atual (sessão já iniciada por index.php)
        $sessaoId     = session_id();
        $carrinhoModel = new Carrinho();
        $carrinho      = $carrinhoModel->queryOne("SELECT * FROM carrinhos WHERE sessao_id = ?", [$sessaoId]);
        $itens         = $carrinho ? $carrinhoModel->getItens($carrinho['id']) : [];

        if (empty($itens)) {
            echo json_encode(['ok' => true, 'opcoes' => []]);
            exit;
        }

        try {
            $service = new FreteService();
            $opcoes  = $service->calcular($cep, $itens);
            echo json_encode(['ok' => true, 'opcoes' => $opcoes]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Erro ao calcular frete.']);
        }

        exit;
    }
}
