<?php
// =============================================================================
// Iraná Natural — Configuração de Frete
// =============================================================================
// Tokens sandbox e produção são gerenciados pelo painel administrativo:
//   /admin/configuracoes → chaves frete_token_sandbox / frete_token_producao
//
// Este arquivo contém apenas as opções locais de entrega (não via API).
// =============================================================================

// Opções de entrega local — sempre exibidas independente da API.
define('FRETE_LOCAIS', [
    [
        'id'             => 'retirada',
        'nome'           => 'Retirada em Mãos',
        'transportadora' => 'Local',
        'valor'          => 0.00,
        'prazo'          => 'A combinar',
        'resp_cliente'   => false,
    ],
    [
        'id'             => 'uber',
        'nome'           => 'Uber/99 (por conta do cliente)',
        'transportadora' => 'Local',
        'valor'          => 0.00,
        'prazo'          => 'Mesmo dia',
        'resp_cliente'   => true,
    ],
    [
        'id'             => 'motoboy',
        'nome'           => 'Motoboy (por conta do cliente)',
        'transportadora' => 'Local',
        'valor'          => 0.00,
        'prazo'          => 'Mesmo dia',
        'resp_cliente'   => true,
    ],
]);
