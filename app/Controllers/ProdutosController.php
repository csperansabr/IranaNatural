<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\Produto;
use App\Models\Categoria;

class ProdutosController extends Controller
{
    private Produto   $produto;
    private Categoria $categoria;

    public function __construct()
    {
        $this->produto   = new Produto();
        $this->categoria = new Categoria();
    }

    public function index(): void
    {
        $categorias = $this->categoria->withProductCount();
        $produtos   = $this->produto->allAtivos();

        $meta = [
            'title'       => 'Produtos — ' . APP_NAME,
            'description' => 'Conheça nossa linha de produtos naturais artesanais: incensos, chás, banhos de ervas e muito mais.',
            'url'         => APP_URL . '/produtos',
        ];

        $this->render('produtos/index', compact('categorias', 'produtos', 'meta'));
    }

    public function categoria(string $catSlug): void
    {
        $cat = $this->categoria->findBySlug($catSlug);
        if (!$cat) { $this->notFound(); return; }

        $produtos   = $this->produto->allAtivos((int)$cat['id']);
        $categorias = $this->categoria->withProductCount();

        $meta = [
            'title'       => $cat['nome'] . ' — ' . APP_NAME,
            'description' => $cat['descricao'] ?: "Produtos da categoria {$cat['nome']} da Iraná Natural.",
            'url'         => APP_URL . '/produtos/' . $catSlug,
        ];

        $this->render('produtos/index', compact('cat', 'produtos', 'categorias', 'meta'));
    }

    public function show(string $catSlug, string $slug): void
    {
        $produto = $this->produto->findBySlug($slug);
        if (!$produto || $produto['categoria_slug'] !== $catSlug) { $this->notFound(); return; }

        $imagens    = $this->produto->getImagens((int)$produto['id']);
        $relacionados = $this->produto->relacionados((int)$produto['id'], (int)$produto['categoria_id']);

        $imagemUrl = !empty($imagens) ? Helper::upload($imagens[0]['caminho']) : APP_URL . '/assets/images/og-default.jpg';

        $meta = [
            'title'       => ($produto['seo_titulo'] ?: $produto['nome']) . ' — ' . APP_NAME,
            'description' => $produto['seo_descricao'] ?: ($produto['descricao_curta'] ?: APP_SLOGAN),
            'url'         => APP_URL . '/produtos/' . $catSlug . '/' . $slug,
            'image'       => $imagemUrl,
            'og_type'     => 'product',
            'schema'      => json_encode([
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => $produto['nome'],
                'description' => strip_tags($produto['descricao_curta'] ?: ''),
                'image'       => $imagemUrl,
                'url'         => APP_URL . '/produtos/' . $catSlug . '/' . $slug,
                'brand'       => ['@type' => 'Brand', 'name' => APP_NAME],
                'offers'      => [
                    '@type'         => 'Offer',
                    'price'         => number_format((float)$produto['preco_venda'], 2, '.', ''),
                    'priceCurrency' => 'BRL',
                    'availability'  => (int)$produto['estoque_atual'] > 0
                                        ? 'https://schema.org/InStock'
                                        : 'https://schema.org/OutOfStock',
                    'seller'        => ['@type' => 'Organization', 'name' => APP_NAME],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $this->render('produtos/show', compact('produto', 'imagens', 'relacionados', 'meta'));
    }
}
