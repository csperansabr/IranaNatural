<?php
namespace App\Services;

use App\Models\Configuracao;

class FreteService
{
    /**
     * Calcula todas as opções de frete disponíveis para o CEP destino
     * e os itens do carrinho informados.
     *
     * As dimensões (peso, altura, largura, comprimento) devem vir nos próprios
     * itens — retornados por Carrinho::getItens(), que já faz JOIN com produtos.
     * As opções locais (Retirada/Uber/Motoboy) são sempre retornadas,
     * independente do status da integração Melhor Envio.
     *
     * @param  string $cepDestino    CEP numérico ou formatado
     * @param  array  $itensCarrinho Itens com produto_id, quantidade, preco_unitario, peso, altura, largura, comprimento
     * @return array  Opções de frete prontas para exibição no checkout
     */
    public function calcular(string $cepDestino, array $itensCarrinho): array
    {
        $config     = new Configuracao();
        $freteAtivo = (bool)(int)$config->get('frete_ativo', '1');

        $produtosApi  = [];
        $semDimensoes = [];
        $pesoTotal    = 0.0;

        foreach ($itensCarrinho as $item) {
            $produtoId   = (int)($item['produto_id'] ?? 0);
            $qtd         = max(1, (int)($item['quantidade'] ?? 1));
            $peso        = (float)($item['peso']        ?? 0);
            $altura      = (int)($item['altura']        ?? 0);
            $largura     = (int)($item['largura']       ?? 0);
            $comprimento = (int)($item['comprimento']   ?? 0);

            if ($peso <= 0 || $altura <= 0 || $largura <= 0 || $comprimento <= 0) {
                $semDimensoes[] = $produtoId;
            }

            $pesoTotal += $peso * $qtd;

            $produtosApi[] = [
                'id'             => $produtoId,
                'peso'           => $peso,
                'altura'         => $altura,
                'largura'        => $largura,
                'comprimento'    => $comprimento,
                'quantidade'     => $qtd,
                'valor_segurado' => round((float)($item['preco_unitario'] ?? 0) * $qtd, 2),
            ];
        }

        if (!empty($semDimensoes)) {
            @file_put_contents(
                ROOT . '/logs/melhorenvio.log',
                json_encode([
                    'ts'          => date('c'),
                    'alerta'      => 'Produtos sem dimensões cadastradas — frete calculado com valores mínimos',
                    'produto_ids' => $semDimensoes,
                    'cep_destino' => $cepDestino,
                ], JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND | LOCK_EX
            );
        }

        $opcoes = [];

        // Fretes via Melhor Envio — só chama se o módulo estiver ativo
        if ($freteAtivo && !empty($produtosApi)) {
            try {
                $opcoes = (new MelhorEnvioService())->calcular($cepDestino, $produtosApi);
            } catch (\Throwable $e) {
                @file_put_contents(
                    ROOT . '/logs/melhorenvio.log',
                    json_encode([
                        'ts'           => date('c'),
                        'err'          => $e->getMessage(),
                        'cep_destino'  => $cepDestino,
                        'qtd_produtos' => count($produtosApi),
                        'peso_total_kg'=> round($pesoTotal, 3),
                    ], JSON_UNESCAPED_UNICODE) . "\n",
                    FILE_APPEND | LOCK_EX
                );
            }
        }

        // Opções locais — sempre exibidas após as transportadoras
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
     * Valida se a seleção de frete submetida é aceitável.
     *
     * @param  string $tipo   ID da opção (ex: 'pac', 'retirada', 'uber')
     * @param  float  $valor  Valor informado pelo POST
     * @return string|null    Mensagem de erro, ou null se válido
     */
    public function validarSelecao(string $tipo, float $valor): ?string
    {
        if ($tipo === '') {
            return 'Selecione uma opção de entrega antes de continuar.';
        }
        if ($valor < 0) {
            return 'Valor de frete inválido.';
        }
        return null;
    }
}
