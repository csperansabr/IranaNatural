<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class ComoComprarController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Como Comprar — Iraná Natural',
            'description' => 'Saiba como comprar na Iraná Natural. Finalize pelo checkout online com pagamento via PIX ou cartão de crédito (InfinitePay). Entrega em Porto Alegre e envio para todo o Brasil.',
            'url'         => APP_URL . '/como-comprar',
        ];

        $this->render('como-comprar/index', compact('meta'));
    }
}
