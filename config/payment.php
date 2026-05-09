<?php
/*
 * InfinitePay Checkout Configuration
 * ────────────────────────────────────
 * InfinitePay Checkout uses only the InfiniteTag (handle) — no API keys or
 * authentication headers are required.
 *
 * Webhook URL to register in InfinitePay dashboard:
 *   https://seu-dominio.com.br/webhook/infinitepay/{INFINITEPAY_WEBHOOK_SECRET}
 */

// InfiniteTag without the "$" symbol (e.g. "$cleiton-speransa" → "cleiton-speransa")
define('INFINITEPAY_HANDLE', 'cleiton-speransa');

// Random secret token for webhook URL path validation
define('INFINITEPAY_WEBHOOK_SECRET', 'change-me-to-a-random-secret');

// API endpoint (do not change unless InfinitePay updates their API)
define('INFINITEPAY_API_URL', 'https://api.checkout.infinitepay.io');

// URL the customer is redirected to after completing payment on InfinitePay
define('INFINITEPAY_SUCCESS_URL', APP_URL . '/checkout/sucesso');
