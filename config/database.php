<?php
/*
 * Database credentials — loaded from .env.
 * Never hardcode passwords here — this file is tracked by git.
 *
 * On HostGator: set the variables in .env at the project root.
 * On cPanel:    alternatively use SetEnv in .htaccess (values take precedence
 *               over .env because env.php never overwrites existing env vars).
 */
define('DB_HOST',    env('DB_HOST',    'localhost'));
define('DB_NAME',    env('DB_NAME',    ''));
define('DB_USER',    env('DB_USER',    ''));
define('DB_PASS',    env('DB_PASS',    ''));
define('DB_CHARSET', 'utf8mb4');
