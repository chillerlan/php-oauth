<?php
/**
 * @link https://developers.pinterest.com/docs/getting-started/authentication/
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Pinterest;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Pinterest::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
