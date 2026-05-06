<?php
namespace App\Models;

use App\Core\Model;

class Banner extends Model
{
    protected string $table = 'banners';

    public function ativos(): array
    {
        return $this->findAll('ativo = 1', [], 'ordem ASC');
    }
}
