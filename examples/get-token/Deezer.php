<?php
/**
 * @link https://developers.deezer.com/api/oauth
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Deezer;

$ENVVAR ??= 'DEEZER';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Deezer::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
