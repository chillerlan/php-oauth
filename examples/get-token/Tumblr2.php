<?php
/**
 * @link https://www.tumblr.com/docs/en/api/v2#oauth
 *
 * @created      24.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Tumblr2;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Tumblr2::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
