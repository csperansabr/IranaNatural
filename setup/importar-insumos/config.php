<?php
/**
 * Iraná Natural — Importador CLI de Insumos
 * Configuração
 */
declare(strict_types=1);

define('SCRIPT_ROOT',           __DIR__);
define('PROJECT_ROOT',          dirname(dirname(__DIR__)));
define('LOG_DIR',               SCRIPT_ROOT . '/logs');
define('LOG_FILE',              LOG_DIR . '/importacao_insumos.log');
define('FORNECEDOR_PADRAO',     'Iraná Natural - Produção Própria');
define('ESTOQUE_MINIMO_PADRAO', 0);
define('CSV_DELIMITER',         ';');

/** Unidades aceitas — inclui 'min' para registros de Feitio */
define('VALID_UNITS', ['kg', 'g', 'mg', 'l', 'ml', 'un', 'pct', 'cx', 'min']);

// Carrega credenciais do projeto (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET)
require_once PROJECT_ROOT . '/config/database.php';
