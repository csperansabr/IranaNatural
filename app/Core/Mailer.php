<?php
namespace App\Core;

class Mailer
{
    // ── Public API ───────────────────────────────────────────────────────────

    public static function enviar(string $para, string $assunto, string $htmlBody): bool
    {
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . APP_NAME . ' <' . EMAIL_NOREPLY . '>',
            'Reply-To: ' . EMAIL_CONTATO,
            'X-Mailer: PHP/' . phpversion(),
        ]);
        return @mail($para, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $htmlBody, $headers);
    }

    /** Pedido criado — notifica o cliente */
    public static function pedidoCliente(array $pedido, array $itens, array $cliente): bool
    {
        if (empty($cliente['email'])) return false;
        $html    = self::templatePedido($pedido, $itens, $cliente, 'cliente');
        $assunto = 'Pedido recebido — #' . $pedido['numero'] . ' — ' . APP_NAME;
        $ok      = self::enviar($cliente['email'], $assunto, $html);
        self::registrarLog('pedido_criado_cliente', (int)($pedido['id'] ?? 0), $cliente['email'], $assunto, $ok);
        return $ok;
    }

    /** Pedido criado — notifica a loja */
    public static function pedidoLoja(array $pedido, array $itens, array $cliente): bool
    {
        $html    = self::templatePedido($pedido, $itens, $cliente, 'loja');
        $assunto = 'Novo Pedido #' . $pedido['numero'] . ' — ' . ($cliente['nome'] ?? '');
        $ok      = self::enviar(EMAIL_CONTATO, $assunto, $html);
        self::registrarLog('pedido_criado_loja', (int)($pedido['id'] ?? 0), EMAIL_CONTATO, $assunto, $ok);
        return $ok;
    }

    /**
     * Status do pedido alterado manualmente pelo admin — notifica o cliente.
     *
     * @param array  $pedido    Array com dados do pedido (numero, id…)
     * @param string $novoStatus Novo status (ex: 'enviado')
     * @param string $obs       Observação do admin (exibida no e-mail se não vazia)
     * @param array  $cliente   Array com nome, email
     */
    public static function statusAtualizado(array $pedido, string $novoStatus, string $obs, array $cliente): bool
    {
        if (empty($cliente['email'])) return false;
        $label   = \App\Models\Pedido::statusLabel($novoStatus);
        $assunto = 'Atualização do seu pedido ' . $pedido['numero'] . ' — ' . $label . ' — ' . APP_NAME;
        $html    = self::templateStatusAtualizado($pedido, $novoStatus, $obs, $cliente);
        $ok      = self::enviar($cliente['email'], $assunto, $html);
        self::registrarLog('status_atualizado', (int)($pedido['id'] ?? 0), $cliente['email'], $assunto, $ok);
        return $ok;
    }

    /** Pagamento aprovado — notifica o cliente */
    public static function pagamentoConfirmado(array $pedido, array $itens, array $cliente): bool
    {
        if (empty($cliente['email'])) return false;
        $html    = self::templatePagamentoConfirmado($pedido, $itens, $cliente);
        $assunto = 'Pagamento aprovado — Pedido #' . $pedido['numero'] . ' — ' . APP_NAME;
        $ok      = self::enviar($cliente['email'], $assunto, $html);
        self::registrarLog('pago_cliente', (int)($pedido['id'] ?? 0), $cliente['email'], $assunto, $ok);
        return $ok;
    }

    /**
     * Grava tentativa de envio em email_logs (não lança exceção).
     * Chamado internamente após cada envio.
     */
    public static function registrarLog(
        string $tipo,
        int    $pedidoId,
        string $dest,
        string $assunto,
        bool   $ok,
        string $erro = ''
    ): void {
        try {
            Database::getInstance()->prepare(
                "INSERT INTO email_logs (tipo, pedido_id, destinatario, assunto, status, erro)
                 VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([
                $tipo,
                $pedidoId,
                $dest,
                $assunto,
                $ok ? 'enviado' : 'falhou',
                $erro ?: null,
            ]);
        } catch (\Throwable $e) {
            error_log('[Mailer::registrarLog] ' . $e->getMessage());
        }
    }

    // ── Templates privados ───────────────────────────────────────────────────

    private static function templatePedido(array $pedido, array $itens, array $cliente, string $tipo): string
    {
        $h  = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $m  = fn(float  $v): string => 'R$&nbsp;' . number_format($v, 2, ',', '.');
        $dt = date('d/m/Y \à\s H:i', strtotime($pedido['criado_em']));

        $tituloBanner    = $tipo === 'loja' ? '📦 Novo Pedido Recebido'  : 'Pedido Recebido!';
        $subtituloBanner = $tipo === 'loja'
            ? 'Um novo pedido foi realizado no site.'
            : 'Obrigada pela sua compra, ' . $h($cliente['nome']) . '! Assim que o pagamento for confirmado, seu pedido entra em preparação.';

        $formaLabel  = $pedido['forma_pagamento'] === 'pendente'
            ? 'InfinitePay (PIX ou Cartão de Crédito)'
            : \App\Models\Pedido::pagamentoLabel($pedido['forma_pagamento']);
        $statusLabel = \App\Models\Pedido::statusLabel($pedido['status']);

        // Itens
        $itensHtml = '';
        foreach ($itens as $item) {
            $itensHtml .= '
            <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #f0ebe0;color:#2A2218;font-size:14px;">' . $h((string)$item['nome_produto']) . '</td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0ebe0;text-align:center;color:#5A4E40;font-size:14px;">' . (int)$item['quantidade'] . '</td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0ebe0;text-align:right;color:#5A4E40;font-size:14px;">' . $m((float)$item['preco_unitario']) . '</td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0ebe0;text-align:right;font-weight:bold;color:#2C5F2E;font-size:14px;">' . $m((float)$item['subtotal']) . '</td>
            </tr>';
        }

        $enderecoHtml = implode(', ', array_filter([
            $h($pedido['entrega_logradouro'] ?? ''),
            $h($pedido['entrega_numero']     ?? ''),
            $h($pedido['entrega_complemento'] ?? ''),
            $h($pedido['entrega_bairro']     ?? ''),
            $h($pedido['entrega_cidade']     ?? '') . ($pedido['entrega_estado'] ? '/' . $h($pedido['entrega_estado']) : ''),
            'CEP ' . $h($pedido['entrega_cep'] ?? ''),
        ]));

        $freteHtml   = (float)$pedido['frete'] > 0
            ? '<tr><td style="color:#5A4E40;padding:6px 0;">Frete</td><td style="text-align:right;color:#5A4E40;padding:6px 0;">' . $m((float)$pedido['frete']) . '</td></tr>'
            : '<tr><td style="color:#5A4E40;padding:6px 0;">Frete</td><td style="text-align:right;color:#2C5F2E;padding:6px 0;font-weight:bold;">A combinar</td></tr>';

        $descontoHtml = (float)$pedido['desconto'] > 0
            ? '<tr><td style="color:#5A4E40;padding:6px 0;">Desconto</td><td style="text-align:right;color:#c0392b;padding:6px 0;">&ndash;&nbsp;' . $m((float)$pedido['desconto']) . '</td></tr>'
            : '';

        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pedido #' . $h($pedido['numero']) . ' &mdash; ' . APP_NAME . '</title>
</head>
<body style="margin:0;padding:0;background:#F5EFE3;font-family:Lato,Arial,sans-serif;color:#2A2218;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5EFE3;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FDFAF4;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(107,78,55,.12);">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#2C5F2E 0%,#5D7A3A 100%);padding:36px 40px;text-align:center;">
      <p style="margin:0 0 8px;font-size:28px;font-weight:bold;color:#EDE3CE;letter-spacing:1px;">' . APP_NAME . '</p>
      <p style="margin:0;font-size:12px;color:#B5C99A;letter-spacing:2px;text-transform:uppercase;">Natureza em cada detalhe</p>
    </td>
  </tr>

  <!-- Banner -->
  <tr>
    <td style="background:#EDE3CE;padding:28px 40px;text-align:center;border-bottom:2px solid #D0BA9A;">
      <h1 style="margin:0 0 8px;font-size:24px;color:#2C5F2E;">' . $tituloBanner . '</h1>
      <p style="margin:0;color:#5A4E40;font-size:14px;line-height:1.5;">' . $subtituloBanner . '</p>
    </td>
  </tr>

  <!-- Resumo (número + data) -->
  <tr>
    <td style="padding:28px 40px 0;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:0 8px 0 0;">
            <div style="background:#F5EFE3;border-radius:8px;padding:16px;text-align:center;">
              <p style="margin:0 0 4px;font-size:11px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Número do Pedido</p>
              <p style="margin:0;font-size:18px;font-weight:bold;color:#2C5F2E;letter-spacing:1px;">' . $h($pedido['numero']) . '</p>
            </div>
          </td>
          <td style="padding:0 0 0 8px;">
            <div style="background:#F5EFE3;border-radius:8px;padding:16px;text-align:center;">
              <p style="margin:0 0 4px;font-size:11px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Data e Hora</p>
              <p style="margin:0;font-size:13px;font-weight:bold;color:#2A2218;">' . $dt . '</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Dados do cliente -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 14px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">👤 Dados do Cliente</h2>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" style="padding:4px 8px 4px 0;color:#5A4E40;font-size:13px;"><strong>Nome:</strong> ' . $h($cliente['nome']) . '</td>
          <td width="50%" style="padding:4px 0;color:#5A4E40;font-size:13px;"><strong>E-mail:</strong> ' . $h($cliente['email']) . '</td>
        </tr>
        <tr>
          <td style="padding:4px 8px 4px 0;color:#5A4E40;font-size:13px;"><strong>CPF:</strong> ' . $h($cliente['cpf'] ?? '') . '</td>
          <td style="padding:4px 0;color:#5A4E40;font-size:13px;"><strong>Telefone:</strong> ' . $h($cliente['telefone'] ?? '—') . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Endereço de entrega -->
  <tr>
    <td style="padding:20px 40px 0;">
      <h2 style="margin:0 0 10px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">📍 Endereço de Entrega</h2>
      <p style="margin:0;color:#5A4E40;font-size:13px;line-height:1.6;">' . $enderecoHtml . '</p>
    </td>
  </tr>

  <!-- Forma de pagamento -->
  <tr>
    <td style="padding:20px 40px 0;">
      <h2 style="margin:0 0 10px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">💳 Pagamento</h2>
      <p style="margin:0;color:#5A4E40;font-size:13px;">
        <strong>' . $formaLabel . '</strong>
        &nbsp;&middot;&nbsp;
        Status: <strong style="color:#2C5F2E;">' . $statusLabel . '</strong>
      </p>
    </td>
  </tr>

  <!-- Itens do pedido -->
  <tr>
    <td style="padding:20px 40px 0;">
      <h2 style="margin:0 0 14px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">🛒 Produtos</h2>
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #D0BA9A;border-radius:8px;overflow:hidden;">
        <tr style="background:#EDE3CE;">
          <th style="padding:10px 16px;text-align:left;font-size:12px;color:#5A4E40;">Produto</th>
          <th style="padding:10px 16px;text-align:center;font-size:12px;color:#5A4E40;">Qtd</th>
          <th style="padding:10px 16px;text-align:right;font-size:12px;color:#5A4E40;">Unit.</th>
          <th style="padding:10px 16px;text-align:right;font-size:12px;color:#5A4E40;">Subtotal</th>
        </tr>
        ' . $itensHtml . '
      </table>
    </td>
  </tr>

  <!-- Totais -->
  <tr>
    <td style="padding:20px 40px 32px;">
      <table cellpadding="0" cellspacing="0" style="margin-left:auto;width:260px;">
        <tr>
          <td style="color:#5A4E40;padding:5px 0;font-size:13px;">Subtotal</td>
          <td style="text-align:right;color:#5A4E40;padding:5px 0;font-size:13px;">' . $m((float)$pedido['subtotal']) . '</td>
        </tr>
        ' . $freteHtml . '
        ' . $descontoHtml . '
        <tr>
          <td colspan="2" style="border-top:2px solid #D0BA9A;padding-top:8px;"></td>
        </tr>
        <tr>
          <td style="font-size:17px;font-weight:bold;color:#2A2218;padding:3px 0;">Total</td>
          <td style="text-align:right;font-size:19px;font-weight:bold;color:#2C5F2E;padding:3px 0;">' . $m((float)$pedido['total']) . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#2C5F2E;padding:24px 40px;text-align:center;">
      <p style="margin:0 0 6px;color:#B5C99A;font-size:13px;">' . APP_NAME . ' &mdash; Natureza em cada detalhe</p>
      <p style="margin:0;color:#7D9B59;font-size:12px;">
        <a href="mailto:' . EMAIL_CONTATO . '" style="color:#7D9B59;">' . EMAIL_CONTATO . '</a>
        &nbsp;&middot;&nbsp;
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

    private static function templateStatusAtualizado(array $pedido, string $novoStatus, string $obs, array $cliente): string
    {
        $h     = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $label = \App\Models\Pedido::statusLabel($novoStatus);

        // Mapa de ícone/mensagem por status — sem informações técnicas internas
        [$icone, $mensagem] = match($novoStatus) {
            'separando' => ['📦', 'Seu pedido está sendo separado e embalado com cuidado.'],
            'enviado'   => ['🚚', 'Seu pedido foi despachado! Fique de olho no código de rastreamento que envolveremos em breve.'],
            'entregue'  => ['✅', 'Seu pedido foi entregue. Esperamos que você adore os produtos!'],
            'cancelado' => ['❌', 'Seu pedido foi cancelado. Em caso de dúvidas, entre em contato conosco.'],
            'pago'      => ['💚', 'Seu pagamento foi confirmado e o pedido está em processamento.'],
            default     => ['📋', 'O status do seu pedido foi atualizado.'],
        };

        $obsHtml = '';
        if ($obs !== '') {
            $obsHtml = '
  <!-- Observação do admin -->
  <tr>
    <td style="padding:0 40px 24px;">
      <div style="background:#EDE3CE;border-left:4px solid #2C5F2E;border-radius:0 8px 8px 0;padding:16px 20px;">
        <p style="margin:0 0 4px;font-size:12px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Observação</p>
        <p style="margin:0;color:#2A2218;font-size:14px;line-height:1.6;">' . $h($obs) . '</p>
      </div>
    </td>
  </tr>';
        }

        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Atualização do Pedido #' . $h($pedido['numero']) . ' &mdash; ' . APP_NAME . '</title>
</head>
<body style="margin:0;padding:0;background:#F5EFE3;font-family:Lato,Arial,sans-serif;color:#2A2218;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5EFE3;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FDFAF4;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(107,78,55,.12);">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#2C5F2E 0%,#5D7A3A 100%);padding:36px 40px;text-align:center;">
      <p style="margin:0 0 8px;font-size:28px;font-weight:bold;color:#EDE3CE;letter-spacing:1px;">' . APP_NAME . '</p>
      <p style="margin:0;font-size:12px;color:#B5C99A;letter-spacing:2px;text-transform:uppercase;">Natureza em cada detalhe</p>
    </td>
  </tr>

  <!-- Banner: novo status -->
  <tr>
    <td style="background:#EDE3CE;padding:32px 40px;text-align:center;border-bottom:2px solid #D0BA9A;">
      <p style="font-size:48px;margin:0 0 12px;line-height:1;">' . $icone . '</p>
      <h1 style="margin:0 0 10px;font-size:22px;color:#2C5F2E;font-weight:bold;">' . $h($label) . '</h1>
      <p style="margin:0;color:#5A4E40;font-size:14px;line-height:1.6;">Olá, ' . $h($cliente['nome']) . '! ' . $mensagem . '</p>
    </td>
  </tr>

  <!-- Número do pedido -->
  <tr>
    <td style="padding:28px 40px 24px;">
      <div style="background:#F5EFE3;border-radius:8px;padding:16px;text-align:center;max-width:240px;margin:0 auto;">
        <p style="margin:0 0 4px;font-size:11px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Número do Pedido</p>
        <p style="margin:0;font-size:20px;font-weight:bold;color:#2C5F2E;letter-spacing:1px;">' . $h($pedido['numero']) . '</p>
      </div>
    </td>
  </tr>
' . $obsHtml . '

  <!-- Suporte -->
  <tr>
    <td style="padding:0 40px 32px;text-align:center;">
      <p style="margin:0;color:#5A4E40;font-size:13px;line-height:1.7;">
        Dúvidas? Fale conosco pelo WhatsApp ou por<br>
        <a href="mailto:' . EMAIL_CONTATO . '" style="color:#2C5F2E;font-weight:bold;">' . EMAIL_CONTATO . '</a>
      </p>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#2C5F2E;padding:24px 40px;text-align:center;">
      <p style="margin:0 0 6px;color:#B5C99A;font-size:13px;">' . APP_NAME . ' &mdash; Natureza em cada detalhe</p>
      <p style="margin:0;color:#7D9B59;font-size:12px;">
        <a href="mailto:' . EMAIL_CONTATO . '" style="color:#7D9B59;">' . EMAIL_CONTATO . '</a>
        &nbsp;&middot;&nbsp;
        <a href="' . APP_URL . '" style="color:#7D9B59;">' . APP_URL . '</a>
      </p>
      <p style="margin:8px 0 0;color:#5A8040;font-size:11px;">Você recebeu este e-mail porque realizou uma compra na Iraná Natural.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }

    private static function templatePagamentoConfirmado(array $pedido, array $itens, array $cliente): string
    {
        $h  = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $m  = fn(float  $v): string => 'R$&nbsp;' . number_format($v, 2, ',', '.');
        $dt = date('d/m/Y \à\s H:i', strtotime($pedido['criado_em']));

        $formaLabel = \App\Models\Pedido::pagamentoLabel($pedido['forma_pagamento']);

        $itensHtml = '';
        foreach ($itens as $item) {
            $itensHtml .= '
            <tr>
                <td style="padding:10px 16px;border-bottom:1px solid #f0ebe0;color:#2A2218;font-size:14px;">' . $h((string)$item['nome_produto']) . '</td>
                <td style="padding:10px 16px;border-bottom:1px solid #f0ebe0;text-align:center;color:#5A4E40;font-size:14px;">' . (int)$item['quantidade'] . '</td>
                <td style="padding:10px 16px;border-bottom:1px solid #f0ebe0;text-align:right;font-weight:bold;color:#2C5F2E;font-size:14px;">' . $m((float)$item['subtotal']) . '</td>
            </tr>';
        }

        $freteHtml = (float)$pedido['frete'] > 0
            ? '<tr><td style="color:#5A4E40;padding:5px 0;font-size:13px;">Frete</td><td style="text-align:right;color:#5A4E40;padding:5px 0;font-size:13px;">' . $m((float)$pedido['frete']) . '</td></tr>'
            : '';

        $descontoHtml = (float)$pedido['desconto'] > 0
            ? '<tr><td style="color:#5A4E40;padding:5px 0;font-size:13px;">Desconto</td><td style="text-align:right;color:#c0392b;padding:5px 0;font-size:13px;">&ndash;&nbsp;' . $m((float)$pedido['desconto']) . '</td></tr>'
            : '';

        $transportadoraInfo = '';
        if (!empty($pedido['transportadora']) && !empty($pedido['prazo_entrega'])) {
            $transportadoraInfo = '<p style="margin:8px 0 0;font-size:13px;color:#5A4E40;">Transportadora: <strong>' . $h($pedido['transportadora']) . '</strong> &middot; Prazo: <strong>' . $h($pedido['prazo_entrega']) . '</strong></p>';
        }

        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pagamento Aprovado &mdash; Pedido #' . $h($pedido['numero']) . '</title>
</head>
<body style="margin:0;padding:0;background:#F5EFE3;font-family:Lato,Arial,sans-serif;color:#2A2218;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5EFE3;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FDFAF4;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(107,78,55,.12);">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#2C5F2E 0%,#5D7A3A 100%);padding:36px 40px;text-align:center;">
      <p style="margin:0 0 8px;font-size:28px;font-weight:bold;color:#EDE3CE;letter-spacing:1px;">' . APP_NAME . '</p>
      <p style="margin:0;font-size:12px;color:#B5C99A;letter-spacing:2px;text-transform:uppercase;">Natureza em cada detalhe</p>
    </td>
  </tr>

  <!-- Banner: Aprovado -->
  <tr>
    <td style="background:#2C5F2E;padding:32px 40px;text-align:center;">
      <p style="font-size:48px;margin:0 0 12px;line-height:1;">&#x2705;</p>
      <h1 style="margin:0 0 10px;font-size:26px;color:#EDE3CE;font-weight:bold;">Pagamento Aprovado!</h1>
      <p style="margin:0;color:#B5C99A;font-size:15px;line-height:1.5;">
        Olá, ' . $h($cliente['nome']) . '! Seu pagamento foi confirmado e<br>seu pedido já está sendo preparado com carinho.
      </p>
    </td>
  </tr>

  <!-- Info boxes: Pedido | Pagamento | Data -->
  <tr>
    <td style="padding:28px 40px 0;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="33%" style="padding:0 6px 0 0;text-align:center;">
            <div style="background:#F5EFE3;border-radius:8px;padding:14px 8px;">
              <p style="margin:0 0 4px;font-size:10px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Pedido</p>
              <p style="margin:0;font-size:14px;font-weight:bold;color:#2C5F2E;">' . $h($pedido['numero']) . '</p>
            </div>
          </td>
          <td width="33%" style="padding:0 3px;text-align:center;">
            <div style="background:#F5EFE3;border-radius:8px;padding:14px 8px;">
              <p style="margin:0 0 4px;font-size:10px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Forma</p>
              <p style="margin:0;font-size:13px;font-weight:bold;color:#2A2218;">' . $formaLabel . '</p>
            </div>
          </td>
          <td width="33%" style="padding:0 0 0 6px;text-align:center;">
            <div style="background:#F5EFE3;border-radius:8px;padding:14px 8px;">
              <p style="margin:0 0 4px;font-size:10px;color:#8A7A6A;text-transform:uppercase;letter-spacing:1px;">Data</p>
              <p style="margin:0;font-size:12px;font-weight:bold;color:#2A2218;">' . $dt . '</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Próximos passos -->
  <tr>
    <td style="padding:24px 40px 0;">
      <h2 style="margin:0 0 16px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">📋 Próximos Passos</h2>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:0 12px 12px 0;vertical-align:top;width:28px;font-size:22px;line-height:1.3;">🧴</td>
          <td style="padding:0 0 12px;vertical-align:top;">
            <p style="margin:0 0 3px;font-size:14px;font-weight:bold;color:#2A2218;">Preparação artesanal</p>
            <p style="margin:0;font-size:13px;color:#5A4E40;line-height:1.5;">Seu pedido está na fila de produção. Cuidamos de cada detalhe para entregar a melhor qualidade.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:0 12px 12px 0;vertical-align:top;font-size:22px;line-height:1.3;">📦</td>
          <td style="padding:0 0 12px;vertical-align:top;">
            <p style="margin:0 0 3px;font-size:14px;font-weight:bold;color:#2A2218;">Envio</p>
            <p style="margin:0;font-size:13px;color:#5A4E40;line-height:1.5;">Quando seu pedido for despachado, você receberá o código de rastreamento.</p>
            ' . $transportadoraInfo . '
          </td>
        </tr>
        <tr>
          <td style="padding:0 12px 0 0;vertical-align:top;font-size:22px;line-height:1.3;">💬</td>
          <td style="padding:0;vertical-align:top;">
            <p style="margin:0 0 3px;font-size:14px;font-weight:bold;color:#2A2218;">Dúvidas?</p>
            <p style="margin:0;font-size:13px;color:#5A4E40;line-height:1.5;">Fale conosco pelo WhatsApp ou pelo e-mail <a href="mailto:' . EMAIL_CONTATO . '" style="color:#2C5F2E;">' . EMAIL_CONTATO . '</a>.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Produtos -->
  <tr>
    <td style="padding:20px 40px 0;">
      <h2 style="margin:0 0 14px;font-size:15px;color:#2C5F2E;border-bottom:2px solid #B5C99A;padding-bottom:8px;">🛒 Resumo do Pedido</h2>
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #D0BA9A;border-radius:8px;overflow:hidden;">
        <tr style="background:#EDE3CE;">
          <th style="padding:10px 16px;text-align:left;font-size:12px;color:#5A4E40;">Produto</th>
          <th style="padding:10px 16px;text-align:center;font-size:12px;color:#5A4E40;">Qtd</th>
          <th style="padding:10px 16px;text-align:right;font-size:12px;color:#5A4E40;">Total</th>
        </tr>
        ' . $itensHtml . '
      </table>
    </td>
  </tr>

  <!-- Totais -->
  <tr>
    <td style="padding:16px 40px 32px;">
      <table cellpadding="0" cellspacing="0" style="margin-left:auto;width:240px;">
        <tr>
          <td style="color:#5A4E40;padding:5px 0;font-size:13px;">Subtotal</td>
          <td style="text-align:right;color:#5A4E40;padding:5px 0;font-size:13px;">' . $m((float)$pedido['subtotal']) . '</td>
        </tr>
        ' . $freteHtml . '
        ' . $descontoHtml . '
        <tr>
          <td colspan="2" style="border-top:2px solid #D0BA9A;padding-top:8px;"></td>
        </tr>
        <tr>
          <td style="font-size:17px;font-weight:bold;color:#2A2218;padding:3px 0;">Total Pago</td>
          <td style="text-align:right;font-size:19px;font-weight:bold;color:#2C5F2E;padding:3px 0;">' . $m((float)$pedido['total']) . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#2C5F2E;padding:24px 40px;text-align:center;">
      <p style="margin:0 0 6px;color:#B5C99A;font-size:13px;">' . APP_NAME . ' &mdash; Natureza em cada detalhe</p>
      <p style="margin:0;color:#7D9B59;font-size:12px;">
        <a href="mailto:' . EMAIL_CONTATO . '" style="color:#7D9B59;">' . EMAIL_CONTATO . '</a>
        &nbsp;&middot;&nbsp;
        <a href="' . APP_URL . '" style="color:#7D9B59;">' . APP_URL . '</a>
      </p>
      <p style="margin:8px 0 0;color:#5A8040;font-size:11px;">Você recebeu este e-mail porque realizou uma compra na Iraná Natural.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }
}
