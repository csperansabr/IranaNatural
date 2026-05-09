<?php
// Permite reset do OPcache somente de localhost — nunca expor em produção.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

if (!function_exists('opcache_reset')) {
    http_response_code(200);
    echo json_encode(['ok' => false, 'msg' => 'OPcache não está habilitado']);
    exit;
}

$ok = opcache_reset();
header('Content-Type: application/json');
echo json_encode(['ok' => $ok, 'msg' => $ok ? 'OPcache resetado com sucesso' : 'Falha ao resetar OPcache']);
