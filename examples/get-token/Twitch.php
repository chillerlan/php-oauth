<?php
/**
 * @link https://dev.twitch.tv/docs/authentication/
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Twitch;

$ENVVAR ??= 'TWITCH';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Twitch::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
