<?php
/**
 * @link https://github.com/tootsuite/documentation/blob/master/Using-the-API/OAuth-details.md
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Mastodon;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Mastodon::class);
// set the mastodon instance we're about to request data from
$provider->setInstance($factory->getEnvVar(Mastodon::IDENTIFIER.'_INSTANCE'));

require_once __DIR__.'/_flow-oauth2.php';

exit;
