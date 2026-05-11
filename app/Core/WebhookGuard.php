<?php
namespace App\Core;

/**
 * Security gate for the InfinitePay webhook endpoint.
 *
 * Call gate() as the very first thing in the webhook controller.
 * It exits with the appropriate HTTP status on any violation,
 * writing a sanitized audit entry (no secrets, no payload) to
 * logs/webhook_security.log and incrementing the per-IP fail counter.
 */
class WebhookGuard
{
    private const MAX_PAYLOAD_BYTES   = 65_536; // 64 KB — webhooks are small
    private const RATE_WINDOW_SECONDS = 60;
    private const RATE_MAX_FAILS      = 20;     // per IP per window

    /**
     * Validate the incoming webhook request.
     * Returns the client IP on success; exits on failure.
     */
    public function gate(string $urlSecret): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // 1 — HTTP method (belt-and-suspenders; router already enforces POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->reject(405, 'METHOD_NOT_ALLOWED', $ip);
        }

        // 2 — Content-Type must be application/json
        $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (!str_contains(strtolower($ct), 'application/json')) {
            $this->reject(415, 'INVALID_CONTENT_TYPE', $ip);
        }

        // 3 — Payload size guard (reject before reading body)
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > self::MAX_PAYLOAD_BYTES) {
            $this->reject(413, 'PAYLOAD_TOO_LARGE', $ip);
        }

        // 4 — Rate limiting (checked before secret to avoid fail-fast timing oracle)
        if ($this->recentFailCount($ip) >= self::RATE_MAX_FAILS) {
            $this->reject(429, 'RATE_LIMITED', $ip);
        }

        // 5 — Timing-safe secret validation (must be the last auth check)
        $configSecret = defined('INFINITEPAY_WEBHOOK_SECRET') ? INFINITEPAY_WEBHOOK_SECRET : '';
        if ($configSecret === '' || !hash_equals($configSecret, $urlSecret)) {
            $this->recordFail($ip);
            $this->reject(403, 'INVALID_SECRET', $ip);
        }

        return $ip;
    }

    // ── Rate-limit helpers (file-based; no Redis/APCu required) ─────────────

    private function recentFailCount(string $ip): int
    {
        $data = $this->readFailFile($ip);
        $cutoff = time() - self::RATE_WINDOW_SECONDS;
        return count(array_filter($data, fn(int $ts) => $ts > $cutoff));
    }

    public function recordFail(string $ip): void
    {
        $now    = time();
        $cutoff = $now - self::RATE_WINDOW_SECONDS;
        $data   = array_values(
            array_filter($this->readFailFile($ip), fn(int $ts) => $ts > $cutoff)
        );
        $data[] = $now;
        $this->writeFailFile($ip, $data);
    }

    private function readFailFile(string $ip): array
    {
        $path = $this->failFilePath($ip);
        if (!is_file($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        return is_string($raw) ? (json_decode($raw, true) ?? []) : [];
    }

    private function writeFailFile(string $ip, array $timestamps): void
    {
        @file_put_contents($this->failFilePath($ip), json_encode($timestamps), LOCK_EX);
    }

    private function failFilePath(string $ip): string
    {
        // Hash the IP so no PII is written to the filesystem path
        return sys_get_temp_dir() . '/whrle_' . hash('sha256', $ip) . '.dat';
    }

    // ── Rejection ────────────────────────────────────────────────────────────

    /**
     * Write audit log, send HTTP response, exit.
     * Never reveals the secret or internal details in the response body.
     */
    private function reject(int $httpCode, string $reason, string $ip): never
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');

        $entry = json_encode([
            'ts'     => date('c'),
            'ip'     => $ip,
            'code'   => $httpCode,
            'reason' => $reason,
        ], JSON_UNESCAPED_UNICODE) . "\n";

        @file_put_contents(
            ROOT . '/logs/webhook_security.log',
            $entry,
            FILE_APPEND | LOCK_EX
        );

        echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
        exit;
    }
}
