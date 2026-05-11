<?php
/**
 * Gera um webhook secret criptograficamente seguro (256 bits de entropia).
 *
 * Uso:
 *   php tools/gerar-webhook-secret.php
 *
 * Depois:
 *   1. Copie o valor gerado para INFINITEPAY_WEBHOOK_SECRET no seu .env
 *   2. Atualize a URL do webhook no painel InfinitePay:
 *      https://seu-dominio.com.br/webhook/infinitepay/<secret>
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script só pode ser executado via linha de comando.' . PHP_EOL);
}

$secret = bin2hex(random_bytes(32)); // 32 bytes = 256 bits = 64 hex chars

echo PHP_EOL;
echo 'Secret gerado (256 bits, hex):' . PHP_EOL;
echo $secret . PHP_EOL;
echo PHP_EOL;
echo 'Adicione ao seu .env:' . PHP_EOL;
echo 'INFINITEPAY_WEBHOOK_SECRET=' . $secret . PHP_EOL;
echo PHP_EOL;
echo 'URL do webhook para registrar no painel InfinitePay:' . PHP_EOL;
echo 'https://seu-dominio.com.br/webhook/infinitepay/' . $secret . PHP_EOL;
echo PHP_EOL;
