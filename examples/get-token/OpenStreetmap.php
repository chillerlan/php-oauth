<?php
/**
 * @link https://wiki.openstreetmap.org/wiki/OAuth
 *
 * @created      12.05.2019
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2019 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\OpenStreetmap;

$ENVVAR ??= 'OPENSTREETMAP';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(OpenStreetmap::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth1.php';

exit;
