<?php
/**
 * @link https://www.opencaching.de/okapi/introduction.html
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\OpenCaching;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$PARAMS ??= ['oauth_callback' => $factory->getEnvVar(OpenCaching::IDENTIFIER.'_CALLBACK_URL')];

$provider = $factory->getProvider(OpenCaching::class);

require_once __DIR__.'/_flow-oauth1.php';

exit;
