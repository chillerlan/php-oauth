<?php
/**
 * @link https://beta.developer.spotify.com/documentation/general/guides/authorization-guide/
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Spotify;

$ENVVAR ??= 'SPOTIFY';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Spotify::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
