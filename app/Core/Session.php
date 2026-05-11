<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('iran_sess');
            session_set_cookie_params([
                'lifetime' => 0,        // cookie de sessão (expira ao fechar o browser)
                'path'     => '/',
                'secure'   => false,    // set true when HTTPS is enforced
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();

            // Verificar expiração por inatividade/absoluta em toda requisição autenticada
            if (isset($_SESSION['cliente_id'])) {
                self::_verificarExpiracaoCliente();
            }
        }
    }

    // ── Expiração de sessão do cliente ───────────────────────────

    /**
     * Inicializa os timestamps de controle de sessão.
     * Deve ser chamado logo após o login ou cadastro bem-sucedido.
     */
    public static function iniciarSessaoCliente(): void
    {
        $agora = time();
        $_SESSION['_cliente_criado_em'] = $agora;
        $_SESSION['_cliente_ativo_em']  = $agora;

        $clienteId = $_SESSION['cliente_id'] ?? '?';
        error_log("[Session] Cliente #{$clienteId} autenticado — sessão iniciada");
    }

    /**
     * Verifica se a sessão do cliente ainda é válida.
     * Chamado automaticamente em start() e disponível para chamada explícita.
     * Retorna false (e encerra a sessão) se expirada.
     */
    public static function verificarExpiracaoCliente(): bool
    {
        if (!isset($_SESSION['cliente_id'])) {
            return false;
        }
        return self::_verificarExpiracaoCliente();
    }

    private static function _verificarExpiracaoCliente(): bool
    {
        $agora  = time();
        $criado = $_SESSION['_cliente_criado_em'] ?? 0;
        $ativo  = $_SESSION['_cliente_ativo_em']  ?? 0;

        // Sessão legada (sem timestamps, ex: antes do deploy) — inicializar agora
        if (!$criado || !$ativo) {
            $_SESSION['_cliente_criado_em'] = $agora;
            $_SESSION['_cliente_ativo_em']  = $agora;
            return true;
        }

        $limiteInatividade = defined('SESSION_CLIENTE_INATIVIDADE') ? SESSION_CLIENTE_INATIVIDADE : 1800;
        $limiteAbsoluto    = defined('SESSION_CLIENTE_ABSOLUTA')    ? SESSION_CLIENTE_ABSOLUTA    : 28800;

        // Expiração absoluta (independentemente de atividade)
        if (($agora - $criado) > $limiteAbsoluto) {
            self::_encerrarSessaoCliente(true);
            return false;
        }

        // Expiração por inatividade
        if (($agora - $ativo) > $limiteInatividade) {
            self::_encerrarSessaoCliente(false);
            return false;
        }

        // Sessão válida — renovar timestamp de atividade
        $_SESSION['_cliente_ativo_em'] = $agora;
        return true;
    }

    private static function _encerrarSessaoCliente(bool $absoluta): void
    {
        $clienteId = $_SESSION['cliente_id'] ?? '?';
        $motivo    = $absoluta ? 'expiração absoluta (8h)' : 'inatividade (30min)';

        error_log("[Session] Cliente #{$clienteId} desconectado automaticamente por {$motivo}");

        // Remover dados de autenticação; carrinho e CSRF são preservados
        unset(
            $_SESSION['cliente_id'],
            $_SESSION['cliente_nome'],
            $_SESSION['cliente_email'],
            $_SESSION['_cliente_criado_em'],
            $_SESSION['_cliente_ativo_em'],
            $_SESSION['_senha_tentativas']
        );

        $msg = $absoluta
            ? 'Por segurança, sua sessão expirou após 8 horas. Faça login novamente.'
            : 'Sua sessão expirou por inatividade (30 min). Faça login novamente.';

        $_SESSION['_flash']['flash_erro'] = $msg;
    }

    // ── Sessão genérica ──────────────────────────────────────────

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── Flash messages ───────────────────────────────────────────

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    /** Verifica se um flash existe sem consumi-lo. */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // ── CSRF ─────────────────────────────────────────────────────

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}
