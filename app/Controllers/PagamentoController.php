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
            'description' => 'Aceitamos PIX e cartão de crédito via InfinitePay no checkout online seguro. Dinheiro aceito exclusivamente na retirada pessoal. Pagamentos processados pela InfinitePay, certificada PCI DSS.',
            'url'         => APP_URL . '/pagamento',
        ];

        $this->render('pagamento/index', compact('meta'));
    }
}
