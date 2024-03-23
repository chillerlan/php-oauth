<?php
/**
 * @link https://www.deviantart.com/developers/authentication
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\DeviantArt;

$ENVVAR ??= 'DEVIANTART';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(DeviantArt::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
