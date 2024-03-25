<?php
/**
 * @link https://developers.google.com/identity/protocols/OAuth2WebServer
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\YouTube;

$ENVVAR ??= 'GOOGLE';
$PARAMS ??= ['access_type' => 'online'];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(YouTube::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
