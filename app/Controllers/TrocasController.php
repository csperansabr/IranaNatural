<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class TrocasController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Trocas e Devoluções — Iraná Natural',
            'description' => 'Política completa de trocas e devoluções: arrependimento em 7 dias (CDC art. 49), defeito ou avaria em 30 dias. Reembolso em até 3 dias úteis após aprovação.',
            'url'         => APP_URL . '/trocas',
        ];

        $this->render('trocas/index', compact('meta'));
    }
}
