<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Banner;
use App\Models\Depoimento;

class HomeController extends Controller
{
    public function index(): void
    {
        $banners     = (new Banner())->ativos();
        $categorias  = (new Categoria())->withProductCount();
        $destaques   = (new Produto())->destaques(6);
        $depoimentos = (new Depoimento())->ativos();

        $meta = [
            'title'       => APP_NAME . ' — ' . APP_SLOGAN,
            'description' => 'Produtos naturais artesanais: incensos, chás, banhos de ervas, escalda pés e tabacos. Feitos com amor e intenção.',
            'url'         => APP_URL,
        ];

        $this->render('home/index', compact('banners', 'categorias', 'destaques', 'depoimentos', 'meta'));
    }
}
