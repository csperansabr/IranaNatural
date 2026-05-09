<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/payment.php';

spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'   => ROOT . '/app/Core/',
        'App\\Models\\' => ROOT . '/app/Models/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) require_once $file;
            return;
        }
    }
});

$rawBody = '{"order_nsu":"IRA-TEST-CLI-DIRETO","paid_amount":1000,"installments":1,"capture_method":"credit_card","receipt_url":"https://comprovante.test/cli","transaction_nsu":"CLI-TEST-NSU-DIRETO","items":[]}';

$pag = new App\Models\Pagamento();
$pag->registrarWebhookLog([
    'source'          => 'infinitepay',
    'order_nsu'       => 'IRA-TEST-CLI-DIRETO',
    'pedido_id'       => null,
    'transaction_nsu' => 'CLI-TEST-NSU-' . time(),
    'status'          => 'approved',
    'payload'         => json_decode($rawBody, true),
    'raw_body'        => $rawBody,
    'paid_amount'     => 10.00,
    'receipt_url'     => 'https://comprovante.test/cli',
    'capture_method'  => 'credit_card',
    'installments'    => 1,
    'ip'              => '127.0.0.1',
    'processado'      => 1,
    'erro'            => null,
]);

// Verifica o que foi gravado
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$row = $pdo->query("SELECT id, status, paid_amount, capture_method, installments, CHAR_LENGTH(raw_body) AS raw_body_bytes, LEFT(raw_body,60) AS raw_body_preview FROM webhook_logs WHERE order_nsu = 'IRA-TEST-CLI-DIRETO' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
