<?php
namespace App\Services;

class MelhorEnvioService
{
    /**
     * Calcula fretes via Melhor Envio API v2 POST /shipment/calculate.
     *
     * @param  string $cepDestino  CEP numérico (8 dígitos)
     * @param  array  $produtos    [['id','peso','altura','largura','comprimento','quantidade','valor_segurado'],...]
     * @return array  Opções normalizadas ou [] em caso de falha
     */
    public function calcular(string $cepDestino, array $produtos): array
    {
        $cepOrigem  = preg_replace('/\D/', '', ME_CEP_ORIGEM);
        $cepDestino = preg_replace('/\D/', '', $cepDestino);

        if (strlen($cepOrigem) !== 8 || strlen($cepDestino) !== 8) return [];
        if (empty($produtos)) return [];

        $payload = [
            'from'     => ['postal_code' => $cepOrigem],
            'to'       => ['postal_code' => $cepDestino],
            'products' => $this->buildProducts($produtos),
            'options'  => ['receipt' => false, 'own_hand' => false, 'collect' => false],
            'services' => ME_SERVICES,
        ];

        $json     = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $endpoint = ME_API_URL . '/shipment/calculate';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . ME_TOKEN,
                'User-Agent: IranaNatural/1.0 (csperansa@gmail.com)',
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $this->log($cepDestino, $httpCode, $curlErr ?: null, $response);

        if ($curlErr || $httpCode < 200 || $httpCode >= 300) return [];

        $data = json_decode($response, true);
        if (!is_array($data)) return [];

        return $this->normalize($data);
    }

    private function buildProducts(array $produtos): array
    {
        $items = [];
        foreach ($produtos as $idx => $p) {
            $items[] = [
                'id'               => (string)($p['id'] ?? ($idx + 1)),
                'width'            => max(1, (int)($p['largura']    ?? 10)),
                'height'           => max(1, (int)($p['altura']     ?? 10)),
                'length'           => max(1, (int)($p['comprimento'] ?? 15)),
                'weight'           => max(0.001, round((float)($p['peso'] ?? 0.1), 3)),
                'insurance_value'  => max(0, (float)($p['valor_segurado'] ?? 0)),
                'quantity'         => max(1, (int)($p['quantidade'] ?? 1)),
            ];
        }
        return $items;
    }

    private function normalize(array $raw): array
    {
        $options = [];
        foreach ($raw as $item) {
            if (!empty($item['error'])) continue;
            if (empty($item['price']) || (float)$item['price'] <= 0) continue;

            $options[] = [
                'id'             => strtolower(str_replace([' ','.'], ['_',''], $item['name'] ?? '')),
                'nome'           => ($item['company']['name'] ?? '') . ' ' . ($item['name'] ?? ''),
                'transportadora' => $item['company']['name'] ?? 'Transportadora',
                'valor'          => (float)($item['custom_price'] ?? $item['price']),
                'prazo'          => (int)($item['delivery_time'] ?? 0) . ' dias úteis',
                'codigo'         => (int)($item['id'] ?? 0),
                'tipo'           => 'transportadora',
                'resp_cliente'   => false,
            ];
        }

        usort($options, fn($a, $b) => $a['valor'] <=> $b['valor']);
        return $options;
    }

    private function log(string $cep, int $code, ?string $err, mixed $resp): void
    {
        $entry = json_encode([
            'ts'   => date('c'),
            'cep'  => $cep,
            'http' => $code,
            'err'  => $err,
            'resp' => is_string($resp) ? substr($resp, 0, 500) : null,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents(ROOT . '/logs/melhorenvio.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
