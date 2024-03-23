<?php
/**
 * @link https://www.opencaching.de/okapi/introduction.html
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\OpenCaching;

$ENVVAR ??= 'OKAPI';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$PARAMS ??= ['oauth_callback' => $factory->getEnvVar($ENVVAR.'_CALLBACK_URL')];

$provider = $factory->getProvider(OpenCaching::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth1.php';

exit;
