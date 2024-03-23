<?php
/**
 * @link https://developers.google.com/identity/protocols/OAuth2WebServer
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Google;

$ENVVAR ??= 'GOOGLE';
$PARAMS ??= ['access_type' => 'online'];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Google::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
