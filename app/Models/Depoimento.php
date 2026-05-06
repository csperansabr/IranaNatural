<?php
namespace App\Models;

use App\Core\Model;

class Depoimento extends Model
{
    protected string $table = 'depoimentos';

    public function ativos(): array
    {
        return $this->findAll('ativo = 1', [], 'ordem ASC');
    }
}
