<?php
/**
 * @link https://developer.tidal.com/documentation/api-sdk/api-sdk-authorization
 *
 * @created      04.08.2025
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2025 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Tidal;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Tidal::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
