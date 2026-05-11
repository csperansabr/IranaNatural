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

    // ── Tokens de recuperação de senha ───────────────────────────────────────

    /**
     * Gera um token seguro (256 bits) para recuperação de senha.
     * Invalida automaticamente qualquer token anterior não utilizado do mesmo usuário.
     */
    public function criarTokenRecuperacao(int $usuarioId): string
    {
        // Invalidar tokens anteriores não usados
        $this->exec(
            "UPDATE tokens_senha_admin SET usado = 1 WHERE usuario_id = ? AND usado = 0",
            [$usuarioId]
        );

        $token    = bin2hex(random_bytes(32)); // 256 bits, 64 hex chars
        $expiraEm = date('Y-m-d H:i:s', time() + 3600); // válido por 1 hora

        $this->exec(
            "INSERT INTO tokens_senha_admin (usuario_id, token, expira_em) VALUES (?, ?, ?)",
            [$usuarioId, $token, $expiraEm]
        );

        return $token;
    }

    /**
     * Busca um token válido (não usado e não expirado) junto com os dados do usuário.
     * Retorna null se o token não existir, tiver sido usado ou estiver expirado.
     */
    public function findTokenValido(string $token): ?array
    {
        return $this->queryOne(
            "SELECT t.id AS token_id, t.usuario_id, t.expira_em,
                    u.nome, u.email
             FROM tokens_senha_admin t
             INNER JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.token = ?
               AND t.usado = 0
               AND t.expira_em > NOW()
               AND u.ativo = 1",
            [$token]
        );
    }

    /**
     * Marca o token como utilizado para impedir reutilização.
     */
    public function consumirToken(string $token): void
    {
        $this->exec(
            "UPDATE tokens_senha_admin SET usado = 1 WHERE token = ?",
            [$token]
        );
    }
}
