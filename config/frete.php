<?php
// =============================================================================
// Iraná Natural — Configuração de Frete
// =============================================================================

// Melhor Envio
define('ME_TOKEN',      getenv('ME_TOKEN')      ?: 'lzuAGDepUOVwvZByWu1zSgRsc6LMGvOUUjAfEl6m');
define('ME_SANDBOX',    (bool)(getenv('ME_SANDBOX') ?: true));
define('ME_API_URL',    ME_SANDBOX
    ? 'https://sandbox.melhorenvio.com.br/api/v2/me'
    : 'https://melhorenvio.com.br/api/v2/me');

// CEP de origem (loja)
define('ME_CEP_ORIGEM', getenv('ME_CEP_ORIGEM') ?: '92110-060');

// Serviços Melhor Envio a consultar (IDs da API):
// 1=PAC, 2=SEDEX, 9=Jadlog.Package, 10=Jadlog.Com
define('ME_SERVICES', getenv('ME_SERVICES') ?: '1,2,9,10');

// Opções de entrega local (mostradas sempre, após os fretes de transportadora)
define('FRETE_LOCAIS', [
    [
        'id'              => 'retirada',
        'nome'            => 'Retirada em Mãos',
        'transportadora'  => 'Local',
        'valor'           => 0.00,
        'prazo'           => 'A combinar',
        'resp_cliente'    => false,
    ],
    [
        'id'              => 'uber',
        'nome'            => 'Uber/99 (por conta do cliente)',
        'transportadora'  => 'Local',
        'valor'           => 0.00,
        'prazo'           => 'Mesmo dia',
        'resp_cliente'    => true,
    ],
    [
        'id'              => 'motoboy',
        'nome'            => 'Motoboy (por conta do cliente)',
        'transportadora'  => 'Local',
        'valor'           => 0.00,
        'prazo'           => 'Mesmo dia',
        'resp_cliente'    => true,
    ],
]);
