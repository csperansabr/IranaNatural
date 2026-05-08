<?php
define('APP_NAME',       'Iraná Natural');

// Auto-detect URL from current request (works in dev and production)
if (isset($_SERVER['HTTP_HOST'])) {
    $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('APP_URL', $__scheme . '://' . $_SERVER['HTTP_HOST']);
    unset($__scheme);
} else {
    define('APP_URL', 'https://irananatural.com.br');
}
define('APP_SLOGAN',     'Natureza em cada detalhe');
define('WHATSAPP',       '5551992296036');
define('WHATSAPP_MSG',   'Olá! Gostaria de saber mais sobre os produtos da Iraná Natural.');
define('EMAIL_CONTATO',  'contato@irananatural.com.br');
define('EMAIL_NOREPLY',  'noreply@irananatural.com.br');
define('UPLOAD_DIR',     ROOT . '/uploads/');
define('UPLOAD_URL',     APP_URL . '/uploads/');
define('ADMIN_SESSION',  'iran_admin_user');
define('INSTAGRAM_URL',  'https://instagram.com/irananatural');
define('TIMEZONE',       'America/Sao_Paulo');

date_default_timezone_set(TIMEZONE);
