<?php
/**
 * @link https://www.discogs.com/developers/#page:authentication,header:authentication-oauth-flow
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Discogs;

$ENVVAR ??= 'DISCOGS';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Discogs::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth1.php';

exit;
