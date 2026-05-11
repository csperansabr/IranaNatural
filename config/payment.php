<?php
/*
 * InfinitePay Checkout Configuration
 * ────────────────────────────────────
 * All secrets are loaded from the .env file.
 * Never hardcode credentials here — this file is tracked by git.
 *
 * Webhook URL to register in InfinitePay dashboard:
 *   https://seu-dominio.com.br/webhook/infinitepay/{INFINITEPAY_WEBHOOK_SECRET}
 *
 * To generate a secure secret:
 *   php tools/gerar-webhook-secret.php
 */

// InfiniteTag without the "$" symbol (e.g. "$irananatural" → "irananatural")
define('INFINITEPAY_HANDLE', env('INFINITEPAY_HANDLE', 'irananatural'));

// API endpoint (do not change unless InfinitePay updates their API)
define('INFINITEPAY_API_URL', 'https://api.checkout.infinitepay.io');

// URL the customer is redirected to after completing payment on InfinitePay
define('INFINITEPAY_SUCCESS_URL', APP_URL . '/checkout/sucesso');

// Webhook secret — loaded from .env only; never hardcoded
(function (): void {
    $secret = env('INFINITEPAY_WEBHOOK_SECRET', '');
    if ($secret === '') {
        // Fail loudly in the server log; the WebhookGuard will reject all
        // incoming webhooks until the variable is set.
        error_log('[SECURITY] INFINITEPAY_WEBHOOK_SECRET is not set in .env'
            . ' — all webhook requests will be rejected.');
    }
    define('INFINITEPAY_WEBHOOK_SECRET', $secret);
})();
