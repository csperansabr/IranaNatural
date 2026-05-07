<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class PoliticaPrivacidadeController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Política de Privacidade e Segurança — Iraná Natural',
            'description' => 'Saiba como a Iraná Natural coleta, usa e protege seus dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).',
            'url'         => APP_URL . '/politica-privacidade',
        ];

        $this->render('politica-privacidade/index', compact('meta'));
    }
}
