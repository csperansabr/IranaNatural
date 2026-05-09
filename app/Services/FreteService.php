<?php
namespace App\Services;

use App\Models\Produto;

class FreteService
{
    /**
     * Calcula todas as opções de frete para o CEP destino e os itens do carrinho.
     *
     * @param  string $cepDestino  CEP numérico ou formatado
     * @param  array  $itensCarrinho  Itens com produto_id, quantidade, preco_unitario
     * @return array  Lista de opções de frete prontas para exibição
     */
    public function calcular(string $cepDestino, array $itensCarrinho): array
    {
        $produtoModel = new Produto();
        $produtosApi  = [];

        foreach ($itensCarrinho as $item) {
            $p = $produtoModel->findById((int)$item['produto_id']);
            if (!$p) continue;

            $produtosApi[] = [
                'id'              => $p['id'],
                'peso'            => max(0.001, (float)($p['peso']        ?? 0.1)),
                'altura'          => max(1,     (int)($p['altura']        ?? 10)),
                'largura'         => max(1,     (int)($p['largura']       ?? 10)),
                'comprimento'     => max(1,     (int)($p['comprimento']   ?? 15)),
                'quantidade'      => max(1,     (int)$item['quantidade']),
                'valor_segurado'  => round((float)$item['preco_unitario'] * (int)$item['quantidade'], 2),
            ];
        }

        $opcoes = [];

        // Fretes de transportadora via Melhor Envio
        if (!empty($produtosApi) && ME_TOKEN !== 'SEU_TOKEN_MELHOR_ENVIO_AQUI') {
            $meService = new MelhorEnvioService();
            $opcoes    = $meService->calcular($cepDestino, $produtosApi);
        }

        // Opções locais (sempre exibidas)
        foreach (FRETE_LOCAIS as $local) {
            $opcoes[] = [
                'id'             => $local['id'],
                'nome'           => $local['nome'],
                'transportadora' => $local['transportadora'],
                'valor'          => (float)$local['valor'],
                'prazo'          => $local['prazo'],
                'codigo'         => 0,
                'tipo'           => 'local',
                'resp_cliente'   => (bool)$local['resp_cliente'],
            ];
        }

        return $opcoes;
    }

    /**
     * Valida se a seleção de frete é aceitável.
     * Carriers devem ter valor > 0; opções locais são sempre aceitas.
     *
     * @param  string $tipo     ID da opção (ex: 'pac', 'retirada', 'uber')
     * @param  float  $valor    Valor enviado pelo POST
     * @return string|null      Mensagem de erro, ou null se válido
     */
    public function validarSelecao(string $tipo, float $valor): ?string
    {
        if ($tipo === '') return 'Selecione uma opção de frete antes de continuar.';

        $locais = array_column(FRETE_LOCAIS, 'id');
        if (!in_array($tipo, $locais, true) && $valor < 0) {
            return 'Valor de frete inválido.';
        }

        return null;
    }
}
