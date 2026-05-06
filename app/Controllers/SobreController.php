<?php
namespace App\Controllers;

use App\Core\Controller;

class SobreController extends Controller
{
    public function index(): void
    {
        $meta = [
            'title'       => 'Sobre — ' . APP_NAME,
            'description' => 'Conheça a história da Iraná Natural, nossa conexão com as ervas e nosso compromisso com a espiritualidade consciente e a produção artesanal.',
            'url'         => APP_URL . '/sobre',
        ];
        $this->render('sobre/index', compact('meta'));
    }
}
