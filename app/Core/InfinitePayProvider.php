<?php
namespace App\Core;

class InfinitePayProvider
{
    /**
     * Create a hosted checkout link on InfinitePay.
     *
     * Returns the full API response array; important key:
     *   checkout_url — redirect the customer here
     */
    public function criarCheckout(array $pedido, array $itens, array $cliente, array $endereco = []): array
    {
        // Item prices arrive already at their final charged values (PIX discount applied
        // per unit in CheckoutController before order creation).
        $items = [];
        foreach ($itens as $item) {
            $unitCents = (int)round((float)$item['preco_unitario'] * 100);
            if ($unitCents < 1) $unitCents = 1;
            $items[] = [
                'description' => mb_substr((string)$item['nome_produto'], 0, 80),
                'quantity'    => (int)$item['quantidade'],
                'price'       => $unitCents,
            ];
        }

        // Add frete as a separate line item when applicable
        $frete = (float)($pedido['frete'] ?? 0);
        if ($frete > 0) {
            $freteCents = (int)round($frete * 100);
            $tipoFrete  = $pedido['transportadora'] ?? 'Frete';
            $items[] = [
                'description' => 'Frete — ' . mb_substr((string)$tipoFrete, 0, 70),
                'quantity'    => 1,
                'price'       => $freteCents,
            ];
        }

        $webhookUrl  = APP_URL . '/webhook/infinitepay/' . INFINITEPAY_WEBHOOK_SECRET;
        $redirectUrl = INFINITEPAY_SUCCESS_URL;

        $payload = [
            'handle'       => INFINITEPAY_HANDLE,
            'order_nsu'    => $pedido['numero'],
            'redirect_url' => $redirectUrl,
            'webhook_url'  => $webhookUrl,
            'items'        => $items,
            'customer'     => $this->buildCustomer($cliente, $endereco),
        ];

        if (!empty($endereco)) {
            $payload['address'] = $this->buildAddress($endereco);
        }

        $json     = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $endpoint = INFINITEPAY_API_URL . '/links';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $this->log([
            'event'     => 'create_checkout',
            'order_nsu' => $pedido['numero'],
            'http_code' => $httpCode,
            'curl_error'=> $curlErr ?: null,
            'payload'   => $payload,
            'response'  => $response,
        ]);

        if ($curlErr) {
            throw new \RuntimeException('Erro de conexão com InfinitePay: ' . $curlErr);
        }

        $data = json_decode($response, true);

        // InfinitePay returns the checkout URL in the "url" field.
        // Normalize to "checkout_url" so callers use a consistent key.
        if (!empty($data['url']) && empty($data['checkout_url'])) {
            $data['checkout_url'] = $data['url'];
        }

        if ($httpCode < 200 || $httpCode >= 300 || empty($data['checkout_url'])) {
            throw new \RuntimeException(
                'InfinitePay retornou HTTP ' . $httpCode . ': ' . $response
            );
        }

        $data['_request_payload'] = $payload;
        return $data;
    }

    private function buildCustomer(array $cliente, array $endereco): array
    {
        $customer = [
            'name'  => $cliente['nome'],
            'email' => $cliente['email'],
        ];

        if (!empty($cliente['telefone'])) {
            $digits = preg_replace('/\D/', '', (string)$cliente['telefone']);
            // Ensure country code +55 prefix
            if (strlen($digits) === 10 || strlen($digits) === 11) {
                $digits = '55' . $digits;
            }
            $customer['phone_number'] = '+' . $digits;
        }

        return $customer;
    }

    private function buildAddress(array $e): array
    {
        $cepDigits = preg_replace('/\D/', '', $e['cep'] ?? '');
        $cep = strlen($cepDigits) === 8
            ? substr($cepDigits, 0, 5) . '-' . substr($cepDigits, 5)
            : $cepDigits;

        $addr = [
            'cep'          => $cep,
            'street'       => $e['logradouro'] ?? '',
            'neighborhood' => $e['bairro']     ?? '',
            'number'       => $e['numero']     ?? '',
        ];

        if (!empty($e['complemento'])) {
            $addr['complement'] = $e['complemento'];
        }

        return $addr;
    }

    private function log(array $data): void
    {
        $logFile = ROOT . '/logs/infinitepay.log';
        $entry   = json_encode(array_merge(['ts' => date('c')], $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
