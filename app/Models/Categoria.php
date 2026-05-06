<?php
namespace App\Models;

use App\Core\Model;

class Categoria extends Model
{
    protected string $table = 'categorias';

    public function allAtivas(): array
    {
        return $this->findAll('ativo = 1', [], 'nome ASC');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne("SELECT * FROM categorias WHERE slug = ? AND ativo = 1", [$slug]);
    }

    public function withProductCount(): array
    {
        return $this->query(
            "SELECT c.*, COUNT(p.id) as total_produtos
             FROM categorias c
             LEFT JOIN produtos p ON p.categoria_id = c.id AND p.ativo = 1
             WHERE c.ativo = 1
             GROUP BY c.id
             ORDER BY c.nome ASC"
        );
    }
}
