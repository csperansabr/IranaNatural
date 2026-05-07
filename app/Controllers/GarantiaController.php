<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class GarantiaController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Garantia de Qualidade — Iraná Natural',
            'description' => 'Prazo de garantia de 30 dias (CDC) para produtos não duráveis. Saiba o que está coberto, como acionar e os critérios de análise e aprovação.',
            'url'         => APP_URL . '/garantia',
        ];

        $this->render('garantia/index', compact('meta'));
    }
}
