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
            'description' => 'Saiba como adquirir seus produtos Iraná Natural pelo WhatsApp. Processo simples, pagamento via PIX, transferência ou cartão, com entrega em Porto Alegre e envio para todo o Brasil.',
            'url'         => APP_URL . '/como-comprar',
        ];

        $this->render('como-comprar/index', compact('meta'));
    }
}
