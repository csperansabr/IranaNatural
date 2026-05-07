<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class EnvioController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Envio e Entrega — Iraná Natural',
            'description' => 'Veja as formas de envio disponíveis: Correios para todo o Brasil, entrega local em Porto Alegre e regiões, motoboy e retirada pessoal.',
            'url'         => APP_URL . '/envio',
        ];

        $this->render('envio/index', compact('meta'));
    }
}
