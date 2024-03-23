<?php
/**
 * @link https://developer.vimeo.com/api/authentication
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Vimeo;

$ENVVAR ??= 'VIMEO';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Vimeo::class, $ENVVAR);

/*
 * The Vimeo AccessToken instance holds additional values:
 *
 * $app = $token->extraParams['app'];
 */

require_once __DIR__.'/_flow-oauth2.php';

exit;
