<?php
/**
 * Simulador de Webhook InfinitePay — uso exclusivo em desenvolvimento local.
 *
 * Execução:
 *   php tests/simular_webhook.php [order_nsu] [cenario] [base_url]
 *
 * Cenários disponíveis:
 *   aprovado   (padrão) — pagamento aprovado, sem campo "status"
 *   cartao     — cartão de crédito aprovado
 *   pix        — PIX aprovado
 *   recusado   — pagamento recusado
 *   cancelado  — pedido cancelado
 *   expirado   — link/PIX expirado
 *   duplicado  — repete o mesmo transaction_nsu (deve retornar "Already processed")
 *
 * Exemplos:
 *   php tests/simular_webhook.php IRA-20260508-97E95
 *   php tests/simular_webhook.php IRA-20260508-97E95 pix
 *   php tests/simular_webhook.php IRA-20260508-97E95 recusado
 *   php tests/simular_webhook.php IRA-20260508-97E95 aprovado https://irananatural.test
 *
 * Nota: em ambiente CLI o APP_URL cai para o fallback de produção.
 * Use o 3º argumento para forçar a URL local (padrão: https://irananatural.test).
 */

define('ROOT', dirname(__DIR__));

// Força o host local antes de carregar config/app.php, para que APP_URL
// não caia no fallback de produção (https://irananatural.com.br) ao rodar via CLI.
$_SERVER['HTTP_HOST'] = 'irananatural.test';
$_SERVER['HTTPS']     = 'on';

require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/payment.php';

// ── Parâmetros ──────────────────────────────────────────────────────────────

$orderNsu = $argv[1] ?? null;
$cenario  = strtolower($argv[2] ?? 'aprovado');

// O 3º argumento permite sobrescrever a URL base (útil para testar via ngrok etc.)
$baseUrl = rtrim($argv[3] ?? APP_URL, '/');

if (!$orderNsu) {
    // Tenta buscar o pedido mais recente no banco
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $row = $pdo->query("SELECT numero FROM pedidos ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "[ERRO] Nenhum pedido encontrado no banco. Crie um pedido antes de testar.\n";
        exit(1);
    }
    $orderNsu = $row['numero'];
    echo "[INFO] order_nsu não informado — usando o pedido mais recente: {$orderNsu}\n";
}

// ── Monta o payload conforme o cenário ─────────────────────────────────────

$transactionNsu = sprintf(
    '%08x-%04x-%04x-%04x-%012x',
    random_int(0, 0xFFFFFFFF),
    random_int(0, 0xFFFF),
    random_int(0x4000, 0x4FFF),
    random_int(0x8000, 0xBFFF),
    random_int(0, 0xFFFFFFFFFFFF)
);

$base = [
    'invoice_slug'    => 'test-' . substr(md5($orderNsu . time()), 0, 8),
    'amount'          => 1000,
    'paid_amount'     => 1000,
    'installments'    => 1,
    'capture_method'  => 'credit_card',
    'transaction_nsu' => $transactionNsu,
    'order_nsu'       => $orderNsu,
    'receipt_url'     => 'https://comprovante.test/' . $transactionNsu,
    'items'           => [],
];

switch ($cenario) {
    case 'aprovado':
        // Pagamento aprovado — sem campo "status" (formato real da InfinitePay)
        $payload = $base;
        break;

    case 'cartao':
        $payload = array_merge($base, [
            'capture_method' => 'credit_card',
            'installments'   => 3,
        ]);
        break;

    case 'pix':
        $payload = array_merge($base, [
            'capture_method' => 'pix',
        ]);
        break;

    case 'recusado':
        $payload = array_merge($base, [
            'paid_amount' => 0,
            'status'      => 'declined',
        ]);
        break;

    case 'cancelado':
        $payload = array_merge($base, [
            'paid_amount' => 0,
            'status'      => 'canceled',
        ]);
        break;

    case 'expirado':
        $payload = array_merge($base, [
            'paid_amount' => 0,
            'status'      => 'expired',
        ]);
        break;

    case 'duplicado':
        // Usa um transaction_nsu fixo para forçar duplicata
        $payload = array_merge($base, [
            'transaction_nsu' => 'DUPLICATE-TEST-NSU-0001',
        ]);
        break;

    default:
        echo "[ERRO] Cenário desconhecido: {$cenario}\n";
        echo "Cenários válidos: aprovado, cartao, pix, recusado, cancelado, expirado, duplicado\n";
        exit(1);
}

// ── Envia o POST para o webhook local ──────────────────────────────────────

$webhookUrl = $baseUrl . '/webhook/infinitepay/' . INFINITEPAY_WEBHOOK_SECRET;
$json       = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

echo "═══════════════════════════════════════════════════════════\n";
echo " Simulador de Webhook InfinitePay\n";
echo "═══════════════════════════════════════════════════════════\n";
echo " URL     : {$webhookUrl}\n";
echo " Cenário : {$cenario}\n";
echo " Order   : {$orderNsu}\n";
echo " Txn NSU : {$transactionNsu}\n";
echo "───────────────────────────────────────────────────────────\n";
echo " Payload enviado:\n{$json}\n";
echo "───────────────────────────────────────────────────────────\n";

$ch = curl_init($webhookUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false, // local dev — certificado autoassinado
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo "[ERRO CURL] {$curlErr}\n";
    echo "Verifique se o servidor local está rodando em " . APP_URL . "\n";
    exit(1);
}

$decoded = json_decode($response, true);

echo " HTTP    : {$httpCode}\n";
echo " Resposta: " . ($decoded ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : $response) . "\n";
echo "───────────────────────────────────────────────────────────\n";

// ── Verifica resultado no banco ─────────────────────────────────────────────

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pedido = $pdo->prepare("SELECT status, transaction_id FROM pedidos WHERE numero = ?");
$pedido->execute([$orderNsu]);
$row = $pedido->fetch(PDO::FETCH_ASSOC);

$wlog = $pdo->prepare(
    "SELECT processado, erro, criado_em FROM webhook_logs WHERE order_nsu = ? ORDER BY id DESC LIMIT 1"
);
$wlog->execute([$orderNsu]);
$log = $wlog->fetch(PDO::FETCH_ASSOC);

echo " Pedido status : " . ($row['status']         ?? 'não encontrado') . "\n";
echo " Transaction ID: " . ($row['transaction_id'] ?? '(vazio)') . "\n";

if ($log) {
    echo " Webhook log   : processado=" . $log['processado']
        . ($log['erro'] ? " | erro=" . $log['erro'] : ' | sem erro')
        . " | em=" . $log['criado_em'] . "\n";
}

echo "═══════════════════════════════════════════════════════════\n";

// Resultado esperado por cenário
$esperado = [
    'aprovado'  => ['http' => 200, 'status' => 'pago'],
    'cartao'    => ['http' => 200, 'status' => 'pago'],
    'pix'       => ['http' => 200, 'status' => 'pago'],
    'recusado'  => ['http' => 200, 'status' => 'pagamento_recusado'],
    'cancelado' => ['http' => 200, 'status' => 'cancelado'],
    'expirado'  => ['http' => 200, 'status' => 'pagamento_expirado'],
    'duplicado' => ['http' => 200, 'status' => null], // status não muda
];

if (isset($esperado[$cenario])) {
    $exp     = $esperado[$cenario];
    $okHttp  = ($httpCode === $exp['http']);
    $okStatus = ($exp['status'] === null) || ($row && $row['status'] === $exp['status']);

    echo ($okHttp && $okStatus ? " ✓ TESTE PASSOU\n" : " ✗ TESTE FALHOU\n");
    if (!$okHttp)   echo "   HTTP esperado {$exp['http']}, recebido {$httpCode}\n";
    if (!$okStatus) echo "   Status esperado '{$exp['status']}', atual '" . ($row['status'] ?? '?') . "'\n";
    echo "═══════════════════════════════════════════════════════════\n";
}
