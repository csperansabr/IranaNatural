<?php
namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne("SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]);
    }

    public function autenticar(string $email, string $senha): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($senha, $user['senha'])) return null;
        return $user;
    }

    public function alterarSenha(int $id, string $novaSenha): bool
    {
        return $this->update($id, ['senha' => password_hash($novaSenha, PASSWORD_BCRYPT)]);
    }
}
