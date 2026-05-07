<?php
namespace Admin\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\FichaTecnica;
use App\Models\Insumo;
use App\Core\Helper;

class ProdutosAdminController extends AdminController
{
    private Produto $model;

    public function __construct() { $this->model = new Produto(); }

    public function index(): void
    {
        $produtos = $this->model->allForAdmin();
        $flash    = $this->getFlash();
        $this->render('produtos/index', compact('produtos', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar(); return; }
        $produto    = null;
        $imagens    = [];
        $categorias = (new Categoria())->allAtivas();
        $flash      = $this->getFlash();
        $this->render('produtos/form', compact('produto', 'imagens', 'categorias', 'flash'));
    }

    public function editar(int $id): void
    {
        $produto = $this->model->findById($id);
        if (!$produto) { $this->redirect('/admin/produtos'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar($id); return; }
        $categorias = (new Categoria())->allAtivas();
        $imagens    = $this->model->getImagens($id);
        $flash      = $this->getFlash();
        $this->render('produtos/form', compact('produto', 'categorias', 'imagens', 'flash'));
    }

    private function salvar(?int $id = null): void
    {
        $nome  = trim($_POST['nome'] ?? '');
        $catId = (int)($_POST['categoria_id'] ?? 0);

        if (!$nome || !$catId) {
            $this->flash('error', 'Nome e categoria são obrigatórios.');
            $this->redirect($id ? "/admin/produtos/{$id}/editar" : '/admin/produtos/novo');
            return;
        }

        $tagsRaw = trim($_POST['tags'] ?? '');
        $tags    = implode(',', array_filter(array_map('trim', explode(',', $tagsRaw))));

        $data = [
            'categoria_id'       => $catId,
            'nome'               => $nome,
            'slug'               => Helper::slug($nome),
            'descricao_curta'    => trim($_POST['descricao_curta'] ?? ''),
            'descricao_completa' => trim($_POST['descricao_completa'] ?? ''),
            'composicao'         => trim($_POST['composicao'] ?? ''),
            'modo_uso'           => trim($_POST['modo_uso'] ?? ''),
            'cuidados'           => trim($_POST['cuidados'] ?? ''),
            'seo_titulo'         => mb_substr(trim($_POST['seo_titulo'] ?? ''), 0, 70),
            'seo_descricao'      => mb_substr(trim($_POST['seo_descricao'] ?? ''), 0, 160),
            'tags'               => $tags,
            'preco_venda'        => (float)($_POST['preco_venda'] ?? 0),
            'margem_desejada'    => (float)($_POST['margem_desejada'] ?? 0),
            'estoque_minimo'     => (int)($_POST['estoque_minimo'] ?? 0),
            'ativo'              => isset($_POST['ativo']) ? 1 : 0,
        ];

        if ($id) {
            $this->model->update($id, $data);
            $this->model->recalcularCusto($id);
        } else {
            $id = $this->model->insert($data);
            $this->model->recalcularCusto($id);
        }

        // Upload de novas imagens
        if (!empty($_FILES['imagens']['name'][0])) {
            $existentes = $this->model->getImagens($id);
            $principal  = empty($existentes);
            foreach ($_FILES['imagens']['name'] as $i => $name) {
                if (!$name) continue;
                $file = [
                    'name'     => $_FILES['imagens']['name'][$i],
                    'tmp_name' => $_FILES['imagens']['tmp_name'][$i],
                    'size'     => $_FILES['imagens']['size'][$i],
                    'error'    => $_FILES['imagens']['error'][$i],
                ];
                $caminho = Helper::uploadFile($file, 'produtos');
                if ($caminho) {
                    $this->model->addImagem($id, $caminho, $principal);
                    $principal = false;
                }
            }
        }

        $this->flash('success', 'Produto salvo com sucesso.');
        $this->redirect("/admin/produtos/{$id}/editar");
    }

    public function excluir(int $id): void
    {
        $this->model->update($id, ['ativo' => 0]);
        $this->flash('success', 'Produto desativado.');
        $this->redirect('/admin/produtos');
    }

    // Image management

    public function setPrincipalImagem(int $prodId, int $imgId): void
    {
        $this->model->setPrincipalImagem($prodId, $imgId);
        $this->flash('success', 'Imagem principal definida.');
        $this->redirect("/admin/produtos/{$prodId}/editar");
    }

    public function excluirImagemProduto(int $prodId, int $imgId): void
    {
        $caminho = $this->model->deleteImagem($imgId);
        if ($caminho) {
            $file = UPLOAD_DIR . $caminho;
            if (file_exists($file)) @unlink($file);
        }
        // Ensure at least one principal if others remain
        $imgs = $this->model->getImagens($prodId);
        if (!empty($imgs)) {
            $hasPrincipal = array_filter($imgs, fn($i) => (int)$i['principal'] === 1);
            if (empty($hasPrincipal)) {
                $this->model->setPrincipalImagem($prodId, (int)$imgs[0]['id']);
            }
        }
        $this->flash('success', 'Imagem excluída.');
        $this->redirect("/admin/produtos/{$prodId}/editar");
    }

    public function moverImagem(int $prodId, int $imgId): void
    {
        $direction = $_POST['direction'] ?? 'up';
        $this->model->moverImagem($prodId, $imgId, $direction);
        $this->redirect("/admin/produtos/{$prodId}/editar");
    }

    // Ficha técnica

    public function ficha(int $id): void
    {
        $produto = $this->model->findById($id);
        if (!$produto) { $this->redirect('/admin/produtos'); return; }
        $ficha   = (new FichaTecnica())->dosProduto($id);
        $insumos = (new Insumo())->allAtivos();
        $flash   = $this->getFlash();
        $this->render('produtos/ficha', compact('produto', 'ficha', 'insumos', 'flash'));
    }

    public function salvarFicha(int $id): void
    {
        $insumoId   = (int)($_POST['insumo_id'] ?? 0);
        $quantidade = (float)($_POST['quantidade'] ?? 0);
        $unidade    = trim($_POST['unidade'] ?? '');

        if (!$insumoId || $quantidade <= 0 || !$unidade) {
            $this->flash('error', 'Preencha insumo, quantidade e unidade.');
            $this->redirect("/admin/produtos/{$id}/ficha");
            return;
        }

        $fichaModel = new FichaTecnica();
        $fichaModel->exec(
            "INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade), unidade = VALUES(unidade)",
            [$id, $insumoId, $quantidade, $unidade]
        );
        $this->model->recalcularCusto($id);

        $this->flash('success', 'Item adicionado à ficha técnica.');
        $this->redirect("/admin/produtos/{$id}/ficha");
    }

    public function excluirFichaItem(int $prodId, int $itemId): void
    {
        (new FichaTecnica())->delete($itemId);
        $this->model->recalcularCusto($prodId);
        $this->flash('success', 'Item removido da ficha técnica.');
        $this->redirect("/admin/produtos/{$prodId}/ficha");
    }
}
