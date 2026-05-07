<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class PagamentoController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Formas de Pagamento — Iraná Natural',
            'description' => 'Aceitamos PIX, transferência bancária (TED/DOC), cartão de crédito em até 12x com juros e dinheiro na retirada. Pagamento via WhatsApp, com link seguro InfinitePay para cartão.',
            'url'         => APP_URL . '/pagamento',
        ];

        $this->render('pagamento/index', compact('meta'));
    }
}
