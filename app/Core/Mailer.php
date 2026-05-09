<?php
namespace App\Core;

class Mailer
{
    public static function enviar(string $para, string $assunto, string $htmlBody): bool
    {
        $boundary = md5(uniqid());
        $headers  = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . APP_NAME . ' <' . EMAIL_NOREPLY . '>',
            'Reply-To: ' . EMAIL_CONTATO,
            'X-Mailer: PHP/' . phpversion(),
        ]);
        return mail($para, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $htmlBody, $headers);
    }

    public static function pedidoLoja(array $pedido, array $itens, array $cliente): bool
    {
        $html = self::templatePedido($pedido, $itens, $cliente, 'loja');
        return self::enviar(
            EMAIL_CONTATO,
            'Novo Pedido #' . $pedido['numero'] . ' — ' . $cliente['nome'],
            $html
        );
    }

    public static function pedidoCliente(array $pedido, array $itens, array $cliente): bool
    {
        $html = self::templatePedido($pedido, $itens, $cliente, 'cliente');
        return self::enviar(
            $cliente['email'],
            'Pedido recebido — #' . $pedido['numero'] . ' — ' . APP_NAME,
            $html
        );
    }

    private static function templatePedido(array $pedido, array $itens, array $cliente, string $tipo): string
    {
        $h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $m = fn(float $v): string  => 'R$ ' . number_format($v, 2, ',', '.');
        $dt = date('d/m/Y \à\s H:i', strtotime($pedido['criado_em']));

        $tituloBanner = $tipo === 'loja'
            ? '📦 Novo Pedido Recebido'
            : 'Pedido Confirmado!';
        $subtituloBanner = $tipo === 'loja'
            ? 'Um novo pedido foi realizado no site.'
            : 'Obrigada pela sua compra, ' . $h($cliente['nome']) . '!';

        // Itens HTML
        $itensHtml = '';
        foreach ($itens as $item) {
            $itensHtml .= '
            <tr>
                <td style="padding:12px 16px; border-bottom:1px solid #f0ebe0; color:#2A2218;">' . $h($item['nome_produto']) . '</td>
                <td style="padding:12px 16px; border-bottom:1px solid #f0ebe0; text-align:center; color:#5A4E40;">' . $item['quantidade'] . '</td>
                <td style="padding:12px 16px; border-bottom:1px solid #f0ebe0; text-align:right; color:#5A4E40;">' . $m((float)$item['preco_unitario']) . '</td>
                <td style="padding:12px 16px; border-bottom:1px solid #f0ebe0; text-align:right; font-weight:bold; color:#2C5F2E;">' . $m((float)$item['subtotal']) . '</td>
            </tr>';
        }

        $enderecoHtml = implode(', ', array_filter([
            $h($pedido['entrega_logradouro'] ?? ''),
            $h($pedido['entrega_numero'] ?? ''),
            $h($pedido['entrega_complemento'] ?? ''),
            $h($pedido['entrega_bairro'] ?? ''),
            $h($pedido['entrega_cidade'] ?? '') . ($pedido['entrega_estado'] ? '/' . $h($pedido['entrega_estado']) : ''),
            'CEP ' . $h($pedido['entrega_cep'] ?? ''),
        ]));

        $formaLabel = \App\Models\Pedido::pagamentoLabel($pedido['forma_pagamento']);
        $statusLabel = \App\Models\Pedido::statusLabel($pedido['status']);

        $freteHtml = (float)$pedido['frete'] > 0
            ? '<tr><td style="color:#5A4E40; padding:6px 0;">Frete</td><td style="text-align:right; color:#5A4E40; padding:6px 0;">' . $m((float)$pedido['frete']) . '</td></tr>'
            : '<tr><td style="color:#5A4E40; padding:6px 0;">Frete</td><td style="text-align:right; color:#2C5F2E; padding:6px 0; font-weight:bold;">A combinar</td></tr>';

        $descontoHtml = (float)$pedido['desconto'] > 0
            ? '<tr><td style="color:#5A4E40; padding:6px 0;">Desconto</td><td style="text-align:right; color:#c0392b; padding:6px 0;">– ' . $m((float)$pedido['desconto']) . '</td></tr>'
            : '';

        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedido #' . $h($pedido['numero']) . '</title>
</head>
<body style="margin:0; padding:0; background:#F5EFE3; font-family: Lato, Arial, sans-serif; color:#2A2218;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5EFE3; padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FDFAF4; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(107,78,55,.12);">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg, #2C5F2E 0%, #5D7A3A 100%); padding:40px 40px 32px; text-align:center;">
      <p style="margin:0 0 16px; font-size:28px; font-weight:bold; color:#EDE3CE; letter-spacing:1px;">' . APP_NAME . '</p>
      <p style="margin:0; font-size:13px; color:#B5C99A; letter-spacing:2px; text-transform:uppercase;">Natureza em cada detalhe</p>
    </td>
  </tr>

  <!-- Banner -->
  <tr>
    <td style="background:#EDE3CE; padding:28px 40px; text-align:center; border-bottom:2px solid #D0BA9A;">
      <h1 style="margin:0 0 8px; font-size:24px; color:#2C5F2E;">' . $tituloBanner . '</h1>
      <p style="margin:0; color:#5A4E40; font-size:15px;">' . $subtituloBanner . '</p>
    </td>
  </tr>

  <!-- Resumo do pedido -->
  <tr>
    <td style="padding:32px 40px 0;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:0 8px 0 0;">
            <div style="background:#F5EFE3; border-radius:8px; padding:16px; text-align:center;">
              <p style="margin:0 0 4px; font-size:11px; color:#8A7A6A; text-transform:uppercase; letter-spacing:1px;">Número do Pedido</p>
              <p style="margin:0; font-size:20px; font-weight:bold; color:#2C5F2E; letter-spacing:1px;">' . $h($pedido['numero']) . '</p>
            </div>
          </td>
          <td style="padding:0 0 0 8px;">
            <div style="background:#F5EFE3; border-radius:8px; padding:16px; text-align:center;">
              <p style="margin:0 0 4px; font-size:11px; color:#8A7A6A; text-transform:uppercase; letter-spacing:1px;">Data e Hora</p>
              <p style="margin:0; font-size:14px; font-weight:bold; color:#2A2218;">' . $dt . '</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Dados do cliente -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 16px; font-size:16px; color:#2C5F2E; border-bottom:2px solid #B5C99A; padding-bottom:8px;">👤 Dados do Cliente</h2>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" style="padding:4px 0; color:#5A4E40; font-size:14px;"><strong>Nome:</strong> ' . $h($cliente['nome']) . '</td>
          <td width="50%" style="padding:4px 0; color:#5A4E40; font-size:14px;"><strong>E-mail:</strong> ' . $h($cliente['email']) . '</td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:#5A4E40; font-size:14px;"><strong>CPF:</strong> ' . $h($cliente['cpf']) . '</td>
          <td style="padding:4px 0; color:#5A4E40; font-size:14px;"><strong>Telefone:</strong> ' . $h($cliente['telefone'] ?? '—') . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Endereço de entrega -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 12px; font-size:16px; color:#2C5F2E; border-bottom:2px solid #B5C99A; padding-bottom:8px;">📍 Endereço de Entrega</h2>
      <p style="margin:0; color:#5A4E40; font-size:14px; line-height:1.6;">' . $enderecoHtml . '</p>
    </td>
  </tr>

  <!-- Forma de pagamento -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 12px; font-size:16px; color:#2C5F2E; border-bottom:2px solid #B5C99A; padding-bottom:8px;">💳 Forma de Pagamento</h2>
      <p style="margin:0; color:#5A4E40; font-size:14px;">
        <strong>' . $formaLabel . '</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Status: <strong style="color:#2C5F2E;">' . $statusLabel . '</strong>
      </p>
    </td>
  </tr>

  <!-- Itens do pedido -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 16px; font-size:16px; color:#2C5F2E; border-bottom:2px solid #B5C99A; padding-bottom:8px;">🛒 Produtos</h2>
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #D0BA9A; border-radius:8px; overflow:hidden;">
        <tr style="background:#EDE3CE;">
          <th style="padding:10px 16px; text-align:left; font-size:13px; color:#5A4E40; font-weight:bold;">Produto</th>
          <th style="padding:10px 16px; text-align:center; font-size:13px; color:#5A4E40; font-weight:bold;">Qtd</th>
          <th style="padding:10px 16px; text-align:right; font-size:13px; color:#5A4E40; font-weight:bold;">Preço Unit.</th>
          <th style="padding:10px 16px; text-align:right; font-size:13px; color:#5A4E40; font-weight:bold;">Subtotal</th>
        </tr>
        ' . $itensHtml . '
      </table>
    </td>
  </tr>

  <!-- Totais -->
  <tr>
    <td style="padding:24px 40px;">
      <table cellpadding="0" cellspacing="0" style="margin-left:auto; width:280px;">
        <tr>
          <td style="color:#5A4E40; padding:6px 0;">Subtotal</td>
          <td style="text-align:right; color:#5A4E40; padding:6px 0;">' . $m((float)$pedido['subtotal']) . '</td>
        </tr>
        ' . $freteHtml . '
        ' . $descontoHtml . '
        <tr>
          <td colspan="2" style="border-top:2px solid #D0BA9A; padding-top:10px;"></td>
        </tr>
        <tr>
          <td style="font-size:18px; font-weight:bold; color:#2A2218; padding:4px 0;">Total</td>
          <td style="text-align:right; font-size:20px; font-weight:bold; color:#2C5F2E; padding:4px 0;">' . $m((float)$pedido['total']) . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#2C5F2E; padding:24px 40px; text-align:center;">
      <p style="margin:0 0 8px; color:#B5C99A; font-size:13px;">' . APP_NAME . ' &mdash; Natureza em cada detalhe</p>
      <p style="margin:0; color:#7D9B59; font-size:12px;">
        <a href="mailto:' . EMAIL_CONTATO . '" style="color:#7D9B59;">' . EMAIL_CONTATO . '</a>
        &nbsp;&nbsp;&middot;&nbsp;&nbsp;
        <a href="' . APP_URL . '" style="color:#7D9B59;">' . APP_URL . '</a>
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }
}
