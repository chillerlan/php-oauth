<?php
/**
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthProviderFactory;
use Psr\Log\LogLevel;

/**
 * allow to use a different autoloader to ease using the examples
 *
 * @var string $AUTOLOADER - path to an alternate autoloader
 */
require_once ($AUTOLOADER ?? __DIR__.'/../vendor/autoload.php');
require_once __DIR__.'/OAuthExampleProviderFactory.php';

/**
 * these vars are supposed to be set before this file is included to ease testing
 *
 * @var string     $CFGDIR   - the directory where configuration is stored (.env, cacert, tokens)
 * @var string     $ENVFILE  - the name of the .env file in case it differs from the default
 * @var string     $LOGLEVEL - log level for the test logger, use 'none' to suppress logging
 * @var array|null $PARAMS   - additional params to pass to getAuthorizationURL()
 * @var array|null $SCOPES   - a set of scopes for the current provider
 */
$CFGDIR   ??= __DIR__.'/../.config';
$ENVFILE  ??= '.env';
$LOGLEVEL ??= LogLevel::INFO;
$PARAMS   ??= null;
$SCOPES   ??= null;

// invoke the PSR-17 and PSR-18 instances
$httpFactory = new \GuzzleHttp\Psr7\HttpFactory;
$http        = new \GuzzleHttp\Client([
	'verify'  => $CFGDIR.'/cacert.pem',
	'headers' => [
		'User-Agent' => OAuthInterface::USER_AGENT,
	],
]);

/*
$httpFactory = new \chillerlan\HTTP\Psr7\HttpFactory;
$http        = new \chillerlan\HTTP\CurlClient($httpFactory, new \chillerlan\HTTP\HTTPOptions([
	'ca_info'    => $CFGDIR.'/cacert.pem',
	'user_agent' => OAuthInterface::USER_AGENT,
]));
*/

$factory = new OAuthExampleProviderFactory(
	new OAuthProviderFactory($http, $httpFactory, $httpFactory, $httpFactory),
	$CFGDIR,
	$ENVFILE,
	$LOGLEVEL,
);
