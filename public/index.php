<?php
/**
 * OAuth test/example entry point
 *
 * @link https://php-oauth.readthedocs.io/en/main/Usage/Using-examples.html
 *
 * @created      22.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

// use this with caution only in case something goes terribly wrong
#error_reporting(E_ALL&~E_DEPRECATED);
#ini_set('display_errors', 1);

// the path to your project's autoloader, e.g. "/var/www/vendor/autoload.php"
$AUTOLOADER = __DIR__.'/../vendor/autoload.php';
// the path to your config directory (writable, 0777), e.g. "/home/web-user/.oauth-config"
$CFGDIR     = __DIR__.'/../.config/';
// the name of the .env file
$ENVFILE    = '.env';
// additional params to pass to getAuthorizationURL()
$PARAMS     = null;
// optional scopes
$SCOPES     = null;

require_once __DIR__.'/../examples/get-token/GitHub.php';
