<?php
namespace App\Services;

use App\Models\Configuracao;

class MelhorEnvioService
{
    /**
     * Calcula fretes via Melhor Envio API v2 POST /shipment/calculate.
     *
     * Todos os parâmetros operacionais (tokens sandbox/produção, CEP origem,
     * ambiente, timeout, serviços) são lidos da tabela `configuracoes`.
     * O token correto é selecionado automaticamente conforme `frete_sandbox`.
     *
     * @param  string $cepDestino  CEP numérico (8 dígitos)
     * @param  array  $produtos    [['id','peso','altura','largura','comprimento','quantidade','valor_segurado'],...]
     * @return array  Opções normalizadas ou [] em caso de falha/configuração incompleta
     */
    public function calcular(string $cepDestino, array $produtos): array
    {
        $config  = new Configuracao();
        $sandbox = (bool)(int)$config->get('frete_sandbox', '1');

        // Seleciona o token conforme o ambiente configurado no admin
        $tokenKey = $sandbox ? 'frete_token_sandbox' : 'frete_token_producao';
        $token    = trim($config->get($tokenKey, ''));

        if ($token === '') {
            $this->log($cepDestino, 0,
                'Token ' . ($sandbox ? 'sandbox' : 'produção') . ' não configurado — acesse /admin/configuracoes',
                null
            );
            return [];
        }

        $baseUrl = $sandbox
            ? 'https://sandbox.melhorenvio.com.br/api/v2/me'
            : 'https://melhorenvio.com.br/api/v2/me';
        $endpoint   = $baseUrl . '/shipment/calculate';
        $timeout    = max(5, (int)$config->get('frete_timeout', '15'));
        $services   = $config->get('frete_services', '1,2,9,10') ?: '1,2,9,10';
        $sslVerify  = (bool)(int)$config->get('frete_ssl_verify', '1');
        $cepOrigem  = preg_replace('/\D/', '', $config->get('frete_cep_origem', ''));
        $cepDestino = preg_replace('/\D/', '', $cepDestino);

        if (strlen($cepOrigem) !== 8) {
            $this->log($cepDestino, 0, 'CEP de origem não configurado ou inválido — configure em /admin/configuracoes', null);
            return [];
        }
        if (strlen($cepDestino) !== 8) return [];
        if (empty($produtos)) return [];

        $payload = [
            'from'     => ['postal_code' => $cepOrigem],
            'to'       => ['postal_code' => $cepDestino],
            'products' => $this->buildProducts($produtos),
            'options'  => ['receipt' => false, 'own_hand' => false, 'collect' => false],
            'services' => $services,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Log da requisição antes do envio para facilitar debug
        $pesoTotal = array_sum(array_map(fn($p) => ($p['weight'] ?? 0) * ($p['quantity'] ?? 1), $payload['products']));
        $this->log($cepDestino, 0, null, json_encode([
            'request_preview' => [
                'qtd_produtos' => count($payload['products']),
                'peso_total_kg'=> round($pesoTotal, 3),
                'sandbox'      => $sandbox,
            ],
        ]));

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
                'User-Agent: Iraná Natural (csperansa@gmail.com)',
            ],
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $this->log($cepDestino, $httpCode, $curlErr ?: null, $response);

        if ($curlErr) {
            return [];
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        $data = json_decode($response, true);
        if (!is_array($data)) return [];

        return $this->normalize($data);
    }

    private function buildProducts(array $produtos): array
    {
        $items = [];
        foreach ($produtos as $idx => $p) {
            $peso        = max(0.001, round((float)($p['peso']        ?? 0.1), 3));
            $altura      = max(1,     (int)($p['altura']              ?? 10));
            $largura     = max(1,     (int)($p['largura']             ?? 10));
            $comprimento = max(1,     (int)($p['comprimento']         ?? 15));
            $qtd         = max(1,     (int)($p['quantidade']          ?? 1));
            $seguro      = max(0,     round((float)($p['valor_segurado'] ?? 0), 2));

            // Alerta se produto veio com alguma dimensão nula/zero
            $camposZero = array_filter([
                'peso'        => (float)($p['peso']        ?? 0) <= 0,
                'altura'      => (int)($p['altura']        ?? 0) <= 0,
                'largura'     => (int)($p['largura']       ?? 0) <= 0,
                'comprimento' => (int)($p['comprimento']   ?? 0) <= 0,
            ]);
            if (!empty($camposZero)) {
                $this->log('', 0,
                    'Produto id=' . ($p['id'] ?? '?') . ' campos zerados: ' . implode(', ', array_keys($camposZero)) . ' — usando mínimos da API',
                    null
                );
            }

            $items[] = [
                'id'              => (string)($p['id'] ?? ($idx + 1)),
                'width'           => $largura,
                'height'          => $altura,
                'length'          => $comprimento,
                'weight'          => $peso,
                'insurance_value' => $seguro,
                'quantity'        => $qtd,
            ];
        }
        return $items;
    }

    private function normalize(array $raw): array
    {
        $options = [];
        foreach ($raw as $item) {
            // Ignorar opções com erro
            if (!empty($item['error'])) continue;

            // Usar custom_price conforme documentação — ignorar se preço zero/inválido
            $preco = (float)($item['custom_price'] ?? $item['price'] ?? 0);
            if ($preco <= 0) continue;

            // Usar custom_delivery_time conforme spec
            // Se null ou zero → "Consultar transportadora"
            $deliveryTime = isset($item['custom_delivery_time']) ? (int)$item['custom_delivery_time'] : null;
            $prazo = ($deliveryTime !== null && $deliveryTime > 0)
                ? $deliveryTime . ' dias úteis'
                : 'Consultar transportadora';

            $nomeServico    = trim($item['name'] ?? '');
            $nomeCompanhia  = trim($item['company']['name'] ?? 'Transportadora');
            $nomeCompleto   = $nomeCompanhia . ($nomeServico ? ' — ' . $nomeServico : '');

            // ID único: slug do nome do serviço
            $id = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $nomeServico ?: (string)($item['id'] ?? '')));

            $options[] = [
                'id'             => $id,
                'nome'           => $nomeCompleto,
                'transportadora' => $nomeCompanhia,
                'valor'          => $preco,
                'prazo'          => $prazo,
                'codigo'         => (int)($item['id'] ?? 0),
                'tipo'           => 'transportadora',
                'resp_cliente'   => false,
            ];
        }

        // Ordenar do menor para o maior preço
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
            'resp' => is_string($resp) ? substr($resp, 0, 600) : null,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents(ROOT . '/logs/melhorenvio.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
