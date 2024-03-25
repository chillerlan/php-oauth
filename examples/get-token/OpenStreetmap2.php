<?php
/**
 * @link https://wiki.openstreetmap.org/wiki/OAuth
 *
 * @created      05.03.2024
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2024 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\OpenStreetmap2;

$ENVVAR ??= 'OPENSTREETMAP2';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(OpenStreetmap2::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
